<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Install {
    private static $db_version = '1.0.0';
    private static $minimum_wc_version = '5.0.0';
    
    public static function init() {
        add_action('init', array(__CLASS__, 'check_version'), 5);
        add_action('admin_init', array(__CLASS__, 'install_actions'));
    }

    public static function check_version() {
        if (get_option('wc_einvoice_version') !== WC_EINVOICE_VERSION) {
            self::install();
            do_action('wc_einvoice_updated');
        }
    }

    public static function install_actions() {
        if (!empty($_GET['do_einvoice_update'])) {
            check_admin_referer('wc_einvoice_db_update', 'wc_einvoice_db_update_nonce');
            self::update();
            WC_Admin_Notices::add_notice('wc_einvoice_update_success');
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

        // If we made it till here nothing is running yet, lets set the transient now
        set_transient('wc_einvoice_installing', 'yes', MINUTE_IN_SECONDS * 10);

        self::create_tables();
        self::create_options();
        self::create_roles();
        self::setup_environment();
        
        delete_transient('wc_einvoice_installing');

        do_action('wc_einvoice_installed');
    }

    private static function create_tables() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $collate = $wpdb->has_cap('collation') ? $wpdb->get_charset_collate() : '';

        $tables = "
        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_einvoice_data (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            tin_number varchar(50) NOT NULL,
            sst_registration varchar(50) NOT NULL,
            registration_number varchar(100) NOT NULL,
            tax_type varchar(20) NOT NULL,
            tax_exemption_details text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY tin_number (tin_number),
            KEY sst_registration (sst_registration)
        ) $collate;

        CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wc_einvoice_meta (
            meta_id bigint(20) NOT NULL AUTO_INCREMENT,
            einvoice_id bigint(20) NOT NULL,
            meta_key varchar(255) DEFAULT NULL,
            meta_value longtext,
            PRIMARY KEY  (meta_id),
            KEY einvoice_id (einvoice_id),
            KEY meta_key (meta_key(191))
        ) $collate;
        ";

        dbDelta($tables);
    }

    private static function create_options() {
        $settings = array(
            'version' => WC_EINVOICE_VERSION,
            'db_version' => self::$db_version,
            'enable_einvoice' => 'yes',
            'company_details' => array(
                'name' => get_bloginfo('name'),
                'address' => '',
                'tax_registration' => '',
                'sst_registration' => ''
            ),
            'document_settings' => array(
                'invoice_prefix' => 'INV',
                'starting_number' => '1000',
                'number_format' => '{prefix}{number}',
                'date_format' => 'Y-m-d'
            ),
            'tax_settings' => array(
                'default_tax_type' => 'sst',
                'enable_tax_exemption' => 'yes'
            )
        );

        foreach ($settings as $option => $value) {
            update_option('wc_einvoice_' . $option, $value);
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

        $capabilities = self::get_core_capabilities();

        foreach ($capabilities as $cap_group) {
            foreach ($cap_group as $cap) {
                $wp_roles->add_cap('administrator', $cap);
                $wp_roles->add_cap('shop_manager', $cap);
            }
        }
    }

    private static function get_core_capabilities() {
        return array(
            'core' => array(
                'manage_einvoice_settings',
                'view_einvoice_reports',
                'export_einvoice_data',
                'edit_einvoice_data'
            )
        );
    }

    private static function setup_environment() {
        // Register post types and taxonomies if needed
        
        // Schedule cron events
        if (!wp_next_scheduled('wc_einvoice_daily_tasks')) {
            wp_schedule_event(time(), 'daily', 'wc_einvoice_daily_tasks');
        }

        // Clear any unwanted data
        delete_transient('wc_einvoice_activation_redirect');
    }

    public static function deactivate() {
        // Clear scheduled hooks
        wp_clear_scheduled_hook('wc_einvoice_daily_tasks');
    }

    public static function uninstall() {
        global $wpdb;

        // Delete options
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'wc_einvoice_%'");

        // Drop custom tables
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wc_einvoice_data");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}wc_einvoice_meta");

        // Remove capabilities
        $capabilities = self::get_core_capabilities();
        
        global $wp_roles;
        
        if (!class_exists('WP_Roles')) {
            return;
        }

        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }

        foreach ($capabilities as $cap_group) {
            foreach ($cap_group as $cap) {
                $wp_roles->remove_cap('administrator', $cap);
                $wp_roles->remove_cap('shop_manager', $cap);
            }
        }

        // Clear any cached data
        wp_cache_flush();
    }
}

WC_EInvoice_Install::init();
