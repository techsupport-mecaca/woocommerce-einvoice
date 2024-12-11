<?php
/**
 * Plugin Name: WooCommerce E-Invoice Extension
 * Plugin URI: 
 * Description: Extends WooCommerce checkout with additional fields required for Malaysian e-invoicing
 * Version: 1.0.0
 * Author: MECACA GLOBAL NETWORK SDN BHD
 * Author URI: https://mecaca.com
 * Text Domain: wc-einvoice
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 */

defined('ABSPATH') || exit;

/**
 * Main WC_EInvoice Class
 */
final class WC_EInvoice {
    /**
     * Plugin version
     */
    public const VERSION = '1.0.0';

    /**
     * Single instance of the plugin
     */
    private static $instance = null;

    /**
     * Main WC_EInvoice Instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * WC_EInvoice Constructor
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Define plugin constants
     */
    private function define_constants() {
        define('WC_EINVOICE_VERSION', self::VERSION);
        define('WC_EINVOICE_ABSPATH', dirname(__FILE__) . '/');
        define('WC_EINVOICE_PLUGIN_BASENAME', plugin_basename(__FILE__));
        define('WC_EINVOICE_PLUGIN_URL', plugins_url('/', __FILE__));
    }

    /**
     * Include required core files
     */
    private function includes() {
        // Include core files only when WooCommerce is active
        if ($this->is_woocommerce_active()) {
            require_once WC_EINVOICE_ABSPATH . 'includes/class-wc-einvoice-install.php';
            require_once WC_EINVOICE_ABSPATH . 'includes/class-wc-einvoice-checkout-fields.php';
            require_once WC_EINVOICE_ABSPATH . 'includes/class-wc-einvoice-admin.php';
            require_once WC_EINVOICE_ABSPATH . 'includes/class-wc-einvoice-customer.php';
        }
    }

    /**
     * Initialize plugin hooks
     */
    private function init_hooks() {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }

        // Plugin activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'), 0);

        // Load text domain
        add_action('init', array($this, 'load_plugin_textdomain'));

        // Add settings link to plugins page
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_plugin_links'));
    }

    /**
     * Check if WooCommerce is active
     */
    private function is_woocommerce_active() {
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array('woocommerce/woocommerce.php', $active_plugins, true) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
    }

    /**
     * Initialize plugin when WooCommerce is active
     */
    public function init() {
        if ($this->is_woocommerce_active()) {
            // Initialize admin
            if (is_admin()) {
                new WC_EInvoice_Admin();
            }

            // Initialize checkout fields
            new WC_EInvoice_Checkout_Fields();

            // Initialize customer handler
            new WC_EInvoice_Customer();
        }
    }

    /**
     * Activate plugin
     */
    public function activate() {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(__('WooCommerce E-Invoice Extension requires WooCommerce to be installed and active.', 'wc-einvoice'));
        }

        // Install database tables and default settings
        WC_EInvoice_Install::install();

        // Clear the permalinks
        flush_rewrite_rules();

        do_action('wc_einvoice_activated');
    }

    /**
     * Deactivate plugin
     */
    public function deactivate() {
        flush_rewrite_rules();
        do_action('wc_einvoice_deactivated');
    }

    /**
     * Load plugin text domain
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wc-einvoice',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-einvoice-settings') . '">' . __('Settings', 'wc-einvoice') . '</a>',
            '<a href="' . admin_url('admin.php?page=wc-einvoice-docs') . '">' . __('Documentation', 'wc-einvoice') . '</a>'
        );
        return array_merge($plugin_links, $links);
    }

    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        if (current_user_can('activate_plugins')) {
            echo '<div class="error"><p>';
            printf(
                /* translators: %s: WooCommerce installation URL */
                __('WooCommerce E-Invoice Extension requires WooCommerce to be installed and activated. You can download %s here.', 'wc-einvoice'),
                '<a href="' . esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce')) . '">WooCommerce</a>'
            );
            echo '</p></div>';
        }
    }

    /**
     * Get plugin URL
     */
    public function plugin_url() {
        return untrailingslashit(plugins_url('/', __FILE__));
    }

    /**
     * Get plugin path
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(__FILE__));
    }

    /**
     * Get template path
     */
    public function template_path() {
        return apply_filters('wc_einvoice_template_path', 'woocommerce-einvoice/');
    }
}

/**
 * Main instance of WC_EInvoice
 */
function WC_EInvoice() {
    return WC_EInvoice::instance();
}

// Global for backwards compatibility
$GLOBALS['wc_einvoice'] = WC_EInvoice();
