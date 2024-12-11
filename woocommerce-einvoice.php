<?php
/**
 * Plugin Name: WooCommerce E-Invoice Extension
 * Plugin URI: 
 * Description: Extends WooCommerce checkout with additional fields required for Malaysian e-invoicing
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: MECACA GLOBAL NETWORK SDN BHD
 * Author URI: https://mecaca.com
 * Text Domain: wc-einvoice
 * Domain Path: /languages
 * WC requires at least: 5.0
 * WC tested up to: 8.4
 * License: GPL v2 or later
 */

defined('ABSPATH') || exit;

final class WC_EInvoice {
    private static $instance = null;
    public const VERSION = '1.0.0';

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        if (self::$instance !== null) {
            return;
        }

        $this->define_constants();

        // Load plugin after WooCommerce
        add_action('plugins_loaded', array($this, 'init'), 15);

        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Add settings link in plugins page
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
    }

    private function define_constants() {
        define('WC_EINVOICE_VERSION', self::VERSION);
        define('WC_EINVOICE_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('WC_EINVOICE_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('WC_EINVOICE_PLUGIN_BASENAME', plugin_basename(__FILE__));
    }

    public function init() {
        // Check if WooCommerce is active
        if (!$this->check_woocommerce()) {
            return;
        }

        // Load translations
        load_plugin_textdomain('wc-einvoice', false, dirname(WC_EINVOICE_PLUGIN_BASENAME) . '/languages');

        // Include required files
        $this->includes();

        // Initialize hooks
        $this->init_hooks();

        do_action('wc_einvoice_loaded');
    }

    private function check_woocommerce() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return false;
        }
        return true;
    }

    public function woocommerce_missing_notice() {
        if (current_user_can('activate_plugins')) {
            echo '<div class="error"><p>';
            printf(
                /* translators: %s: WooCommerce plugin URL */
                esc_html__('WooCommerce E-Invoice Extension requires WooCommerce to be installed and active. You can download %s here.', 'wc-einvoice'),
                '<a href="' . esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce')) . '">WooCommerce</a>'
            );
            echo '</p></div>';
        }
    }

    private function includes() {
        // Core files
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-install.php';
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-checkout-fields.php';
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-customer.php';

        // Admin includes
        if (is_admin()) {
            require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-admin.php';
            new WC_EInvoice_Admin();
        }
    }

    private function init_hooks() {
        add_action('init', array($this, 'init_handlers'), 0);
        add_action('widgets_init', array($this, 'init_widgets'));
        add_filter('woocommerce_get_settings_pages', array($this, 'init_settings'));
    }

    public function init_handlers() {
        // Initialize core handlers
        new WC_EInvoice_Checkout_Fields();
        new WC_EInvoice_Customer();
    }

    public function init_widgets() {
        // Register widgets if needed
    }

    public function init_settings($settings) {
        // Add settings if needed
        return $settings;
    }

    public function activate() {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('WooCommerce E-Invoice Extension requires WooCommerce to be installed and active.', 'wc-einvoice'),
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }

        // Run installation
        if (class_exists('WC_EInvoice_Install')) {
            WC_EInvoice_Install::install();
        }

        // Clear permalinks
        flush_rewrite_rules();

        do_action('wc_einvoice_activated');
    }

    public function deactivate() {
        flush_rewrite_rules();
        do_action('wc_einvoice_deactivated');
    }

    public function add_settings_link($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=wc-einvoice') . '">' . 
                        esc_html__('Settings', 'wc-einvoice') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function plugin_url() {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }

    public function template_path() {
        return apply_filters('wc_einvoice_template_path', 'woocommerce-einvoice/');
    }
}

function WC_EInvoice() {
    return WC_EInvoice::instance();
}

$GLOBALS['wc_einvoice'] = WC_EInvoice();
