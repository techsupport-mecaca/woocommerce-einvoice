<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Install {
    private static $db_version = '1.0.0';
    
    public static function init() {
        add_action('init', array(__CLASS__, 'check_version'), 5);
        add_action('admin_notices', array(__CLASS__, 'update_notices'));
    }

    public static function check_version() {
        if (!defined('IFRAME_REQUEST') && get_option('wc_einvoice_version') !== WC_EINVOICE_VERSION) {
            self::install();
            do_action('wc_einvoice_updated');
        }
    }

    public static function install() {
        if (!is_blog_installed()) {
            return;
        }

        // Check if we are not already running this routine
        if ('yes' === get_transient('wc_einvoice_installing')) {
            return;
        }

        // Set transient to prevent concurrent installations
        set_transient('wc_einvoice_installing', 'yes', MINUTE_IN_SECONDS * 10);
        
        self::create_tables();
        self::create_options();
        self::create_roles();
        
        delete_transient('wc_einvoice_installing');
        
        // Update version
        delete_option('wc_einvoice_version');
        add_option('wc_einvoice_version', WC_EINVOICE_VERSION);
        
        // Trigger action
        do_action('wc_einvoice_installed');
    }

    private static function create_tables() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        $collate = $wpdb->has_cap('collation') ? $wpdb->get_charset_collate() : '';

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            order_id bigint(20) NOT NULL,
            tin_number varchar(50) NOT NULL,
            sst_registration varchar(50) NOT NULL,
            tax_type varchar(20) NOT NULL,
            tax_exemption_details text,
            registration_number varchar(100) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY order_id (order_id)
        ) $collate;";

        dbDelta($sql);
    }

    private static function create_options() {
        // Add default options
        $options = array(
            'wc_einvoice_settings' => array(
                'enable_einvoice' => 'yes',
                'auto_generate' => 'order_created',
                'invoice_prefix' => 'INV',
                'next_invoice_number' => '1000',
                'default_tax_type' => 'sst',
                'enable_logging' => 'yes'
            )
        );

        foreach ($options as $option_name => $option_value) {
            if (get_option($option_name) === false) {
                add_option($option_name, $option_value);
            }
        }
    }

    private static function create_roles() {
        global $wp_roles;

        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        // Shop manager role
        $wp_roles->add_cap('shop_manager', 'manage_einvoice');
        $wp_roles->add_cap('administrator', 'manage_einvoice');
    }

    public static function update_notices() {
        if (!current_user_can('update_plugins')) {
            return;
        }

        $update_version = get_option('wc_einvoice_version_to_update');

        if ($update_version) {
            include dirname(__FILE__) . '/admin/views/html-notice-update.php';
        }
    }

    public static function deactivate() {
        // Clean up if needed
    }

    public static function uninstall() {
        global $wpdb;

        // Delete options
        delete_option('wc_einvoice_version');
        delete_option('wc_einvoice_settings');

        // Delete tables
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wc_einvoice_data");

        // Clear any cached data
        wp_cache_flush();
    }
}

WC_EInvoice_Install::init();
