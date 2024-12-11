<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Admin {
    public function __construct() {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_menu_items'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function register_settings() {
        register_setting(
            'wc_einvoice_settings',
            'wc_einvoice_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings')
            )
        );

        register_setting(
            'wc_einvoice_payment_settings',
            'wc_einvoice_payment_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_payment_settings')
            )
        );
    }

    public function add_menu_items() {
        add_menu_page(
            __('E-Invoice Settings', 'wc-einvoice'),
            __('E-Invoice', 'wc-einvoice'),
            'manage_woocommerce',
            'wc-einvoice',
            array($this, 'render_settings_page'),
            'dashicons-media-spreadsheet',
            56
        );

        add_submenu_page(
            'wc-einvoice',
            __('Settings', 'wc-einvoice'),
            __('Settings', 'wc-einvoice'),
            'manage_woocommerce',
            'wc-einvoice',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'wc-einvoice',
            __('Payment Settings', 'wc-einvoice'),
            __('Payment Settings', 'wc-einvoice'),
            'manage_woocommerce',
            'wc-einvoice-payment',
            array($this, 'render_payment_page')
        );

        add_submenu_page(
            'wc-einvoice',
            __('Bulk Management', 'wc-einvoice'),
            __('Bulk Management', 'wc-einvoice'),
            'manage_woocommerce',
            'wc-einvoice-bulk',
            array($this, 'render_bulk_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'wc-einvoice') === false) {
            return;
        }

        wp_enqueue_style(
            'wc-einvoice-admin',
            WC_EINVOICE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            WC_EINVOICE_VERSION
        );

        wp_enqueue_script(
            'wc-einvoice-admin',
            WC_EINVOICE_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            WC_EINVOICE_VERSION,
            true
        );

        wp_localize_script('wc-einvoice-admin', 'wcEinvoiceAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc-einvoice-admin')
        ));
    }

    public function render_settings_page() {
        require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-settings-page.php';
    }

    public function render_payment_page() {
        require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-payment-page.php';
    }

    public function render_bulk_page() {
        require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-bulk-page.php';
    }

    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['company_name'])) {
            $sanitized['company_name'] = sanitize_text_field($input['company_name']);
        }
        
        if (isset($input['tax_registration'])) {
            $sanitized['tax_registration'] = sanitize_text_field($input['tax_registration']);
        }
        
        if (isset($input['company_address'])) {
            $sanitized['company_address'] = sanitize_textarea_field($input['company_address']);
        }
        
        if (isset($input['invoice_prefix'])) {
            $sanitized['invoice_prefix'] = sanitize_text_field($input['invoice_prefix']);
        }
        
        if (isset($input['next_invoice_number'])) {
            $sanitized['next_invoice_number'] = absint($input['next_invoice_number']);
        }
        
        if (isset($input['default_tax_type'])) {
            $sanitized['default_tax_type'] = sanitize_text_field($input['default_tax_type']);
        }

        return $sanitized;
    }

    public function sanitize_payment_settings($input) {
        $sanitized = array();
        
        if (isset($input['payment_mode'])) {
            $sanitized['payment_mode'] = sanitize_text_field($input['payment_mode']);
        }
        
        if (isset($input['bank_details'])) {
            $sanitized['bank_details'] = sanitize_textarea_field($input['bank_details']);
        }
        
        if (isset($input['payment_terms'])) {
            $sanitized['payment_terms'] = sanitize_textarea_field($input['payment_terms']);
        }
        
        if (isset($input['enable_prepayment'])) {
            $sanitized['enable_prepayment'] = (bool) $input['enable_prepayment'];
        }
        
        if (isset($input['prepayment_percentage'])) {
            $sanitized['prepayment_percentage'] = min(100, max(0, floatval($input['prepayment_percentage'])));
        }

        return $sanitized;
    }
    public function add_menu_items() {
    // Main menu
    add_menu_page(
        __('E-Invoice', 'wc-einvoice'),
        __('E-Invoice', 'wc-einvoice'),
        'manage_options',  // Changed from manage_woocommerce to manage_options
        'wc-einvoice',
        array($this, 'render_admin_page'),
        'dashicons-media-spreadsheet',
        56
    );

    // Settings submenu
    add_submenu_page(
        'wc-einvoice',
        __('E-Invoice Settings', 'wc-einvoice'),
        __('Settings', 'wc-einvoice'),
        'manage_options',  // Changed from manage_woocommerce to manage_options
        'wc-einvoice-settings',
        array($this, 'render_settings_page')
    );

    // Payment settings submenu
    add_submenu_page(
        'wc-einvoice',
        __('Payment Settings', 'wc-einvoice'),
        __('Payment', 'wc-einvoice'),
        'manage_options',  // Changed from manage_woocommerce to manage_options
        'wc-einvoice-payment',
        array($this, 'render_payment_page')
    );

    // Bulk management submenu
    add_submenu_page(
        'wc-einvoice',
        __('Bulk Management', 'wc-einvoice'),
        __('Bulk Management', 'wc-einvoice'),
        'manage_options',  // Changed from manage_woocommerce to manage_options
        'wc-einvoice-bulk',
        array($this, 'render_bulk_page')
    );
}

public function render_admin_page() {
    require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-admin-page.php';
}
}
