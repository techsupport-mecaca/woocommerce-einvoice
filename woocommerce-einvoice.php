<?php
/**
 * Plugin Name: WooCommerce E-Invoice
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
        $this->define_constants();

        // Initialize plugin after WooCommerce is loaded
        add_action('plugins_loaded', array($this, 'init'), 0);

        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Load text domain
        add_action('init', array($this, 'load_plugin_textdomain'));
    }

    private function define_constants() {
        if (!defined('WC_EINVOICE_VERSION')) {
            define('WC_EINVOICE_VERSION', self::VERSION);
        }
        if (!defined('WC_EINVOICE_PLUGIN_DIR')) {
            define('WC_EINVOICE_PLUGIN_DIR', plugin_dir_path(__FILE__));
        }
        if (!defined('WC_EINVOICE_PLUGIN_URL')) {
            define('WC_EINVOICE_PLUGIN_URL', plugin_dir_url(__FILE__));
        }
        if (!defined('WC_EINVOICE_PLUGIN_BASENAME')) {
            define('WC_EINVOICE_PLUGIN_BASENAME', plugin_basename(__FILE__));
        }
        if (!defined('WC_EINVOICE_TEMPLATE_PATH')) {
            define('WC_EINVOICE_TEMPLATE_PATH', 'woocommerce-einvoice/');
        }
    }

    public function init() {
        if (!$this->check_dependencies()) {
            return;
        }

        $this->includes();
        $this->init_hooks();

        do_action('wc_einvoice_loaded');
    }

    private function check_dependencies() {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return false;
        }
        return true;
    }

    private function includes() {
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-install.php';
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-admin.php';
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-checkout-fields.php';
        require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-customer.php';

        if (is_admin()) {
            new WC_EInvoice_Admin();
        }

        new WC_EInvoice_Checkout_Fields();
        new WC_EInvoice_Customer();
    }

    private function init_hooks() {
        add_filter(
            'plugin_action_links_' . WC_EINVOICE_PLUGIN_BASENAME,
            array($this, 'plugin_action_links')
        );
    }

    public function plugin_action_links($links) {
        $plugin_links = array(
            '<a href="' . admin_url('admin.php?page=wc-einvoice-settings') . '">' . 
            __('Settings', 'wc-einvoice') . '</a>'
        );
        return array_merge($plugin_links, $links);
    }

    public function activate() {
        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('WooCommerce E-Invoice Extension requires WooCommerce to be installed and active.', 'wc-einvoice'),
                'Plugin dependency check',
                array('back_link' => true)
            );
        }

        if (!class_exists('WC_EInvoice_Install')) {
            require_once WC_EINVOICE_PLUGIN_DIR . 'includes/class-wc-einvoice-install.php';
        }

        WC_EInvoice_Install::install();

        flush_rewrite_rules();
        do_action('wc_einvoice_activated');
    }

    public function deactivate() {
        flush_rewrite_rules();
        do_action('wc_einvoice_deactivated');
    }

    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wc-einvoice',
            false,
            dirname(WC_EINVOICE_PLUGIN_BASENAME) . '/languages'
        );
    }

    public function woocommerce_missing_notice() {
        if (current_user_can('activate_plugins')) {
            printf(
                '<div class="error"><p>%s</p></div>',
                sprintf(
                    /* translators: %s: WooCommerce plugin link */
                    esc_html__('WooCommerce E-Invoice Extension requires WooCommerce to be installed and active. You can download %s here.', 'wc-einvoice'),
                    '<a href="' . esc_url(admin_url('plugin-install.php?tab=plugin-information&plugin=woocommerce')) . '">WooCommerce</a>'
                )
            );
        }
    }

    public function get_template_path() {
        return WC_EINVOICE_TEMPLATE_PATH;
    }
}

function WC_EInvoice() {
    return WC_EInvoice::instance();
}

$GLOBALS['wc_einvoice'] = WC_EInvoice();
