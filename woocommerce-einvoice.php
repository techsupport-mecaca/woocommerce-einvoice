<?php
/**
 * Plugin Name: WooCommerce E-Invoice Extension
 * Plugin URI: 
 * Description: Extends WooCommerce checkout with additional fields required for Malaysian e-invoicing
 * Version: 1.0.0
 * Author: 
 * Author URI: 
 * Text Domain: wc-einvoice
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

defined('ABSPATH') || exit;

class WC_EInvoice {
    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->define_constants();
        $this->init_hooks();

        // Load required files
        add_action('plugins_loaded', array($this, 'load_plugin_files'));
    }

    private function define_constants() {
        define('WC_EINVOICE_VERSION', '1.0.0');
        define('WC_EINVOICE_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('WC_EINVOICE_PLUGIN_URL', plugin_dir_url(__FILE__));
    }

    private function init_hooks() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        // Initialize the plugin
        add_action('init', array($this, 'init'), 0);
        
        // Add menu items
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Database installation
        register_activation_hook(__FILE__, array($this, 'install'));
    }

    public function load_plugin_files() {
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-install.php';
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-checkout-fields.php';
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-admin.php';
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-customer.php';
    }

    public function install() {
        global $wpdb;

        // Create custom table
        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            tin_number varchar(50),
            sst_registration varchar(50),
            tax_type varchar(50),
            tax_exemption_details text,
            registration_number varchar(100),
            msic_code varchar(50),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function init() {
        // Initialize admin
        if (is_admin()) {
            new WC_EInvoice_Admin();
        }

        // Initialize customer data handler
        new WC_EInvoice_Customer();

        // Initialize checkout fields
        new WC_EInvoice_Checkout_Fields();
    }

    public function add_admin_menu() {
        add_menu_page(
            __('E-Invoice Settings', 'wc-einvoice'),
            __('E-Invoice', 'wc-einvoice'),
            'manage_woocommerce',
            'wc-einvoice',
            array($this, 'admin_page'),
            'dashicons-media-spreadsheet',
            56
        );
    }

    public function admin_page() {
        require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-admin-page.php';
    }

    public function woocommerce_missing_notice() {
        echo '<div class="error"><p>' . 
             __('WooCommerce E-Invoice Extension requires WooCommerce to be installed and active.', 'wc-einvoice') . 
             '</p></div>';
    }
}

// Initialize the plugin
function WC_EInvoice() {
    return WC_EInvoice::instance();
}

// Start the plugin
WC_EInvoice();
