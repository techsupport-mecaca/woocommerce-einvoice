<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Admin {
    public function __construct() {
        // Add admin menu items
        add_action('admin_menu', array($this, 'add_submenu_pages'));
        
        // Register admin scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Ajax handlers for bulk operations
        add_action('wp_ajax_bulk_export_einvoice_data', array($this, 'handle_bulk_export'));
        add_action('wp_ajax_bulk_update_einvoice_data', array($this, 'handle_bulk_update'));
        
        // Add custom columns to orders list
        add_filter('manage_edit-shop_order_columns', array($this, 'add_order_columns'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'add_order_column_content'));
    }

    public function add_submenu_pages() {
        // Main settings page
        add_submenu_page(
            'wc-einvoice',
            __('E-Invoice Settings', 'wc-einvoice'),
            __('Settings', 'wc-einvoice'),
            'manage_woocommerce',
            'wc-einvoice-settings',
            array($this, 'render_settings_page')
        );

        // Payment information management
        add_submenu_page(
            'wc-einvoice',
            __('Payment Information', 'wc-einvoice'),
            __('Payment Info', 'wc-einvoice'),
            'manage_woocommerce',
            'wc-einvoice-payment',
            array($this, 'render_payment_page')
        );

        // Bulk management page
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
        // Only load on our plugin pages
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
            'nonce' => wp_create_nonce('wc-einvoice-admin'),
            'bulk_export_error' => __('Error exporting data. Please try again.', 'wc-einvoice'),
            'bulk_update_error' => __('Error updating data. Please try again.', 'wc-einvoice')
        ));
    }

    public function render_settings_page() {
        include WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-settings-page.php';
    }

    public function render_payment_page() {
        // Get saved payment settings
        $payment_settings = get_option('wc_einvoice_payment_settings', array());
        include WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-payment-page.php';
    }

    public function render_bulk_page() {
        global $wpdb;
        
        // Get all e-invoice data
        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        $records = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY updated_at DESC");
        
        include WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-bulk-page.php';
    }

    public function handle_bulk_export() {
        check_ajax_referer('wc-einvoice-admin', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_die(-1);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        
        // Get all records
        $records = $wpdb->get_results("SELECT * FROM {$table_name}", ARRAY_A);
        
        // Generate CSV
        $filename = 'einvoice-export-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, array_keys($records[0]));
        
        // Add data
        foreach ($records as $record) {
            fputcsv($output, $record);
        }
        
        fclose($output);
        wp_die();
    }

    public function handle_bulk_update() {
        check_ajax_referer('wc-einvoice-admin', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permission denied');
        }

        $data = $_POST['data'];
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        
        foreach ($data as $record) {
            $wpdb->update(
                $table_name,
                array(
                    'tin_number' => sanitize_text_field($record['tin_number']),
                    'sst_registration' => sanitize_text_field($record['sst_registration']),
                    'tax_type' => sanitize_text_field($record['tax_type']),
                    'tax_exemption_details' => sanitize_textarea_field($record['tax_exemption_details'])
                ),
                array('id' => intval($record['id'])),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );
        }
        
        wp_send_json_success();
    }

    public function add_order_columns($columns) {
        $new_columns = array();
        
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            if ($key === 'order_status') {
                $new_columns['einvoice_status'] = __('E-Invoice Status', 'wc-einvoice');
            }
        }
        
        return $new_columns;
    }

    public function add_order_column_content($column) {
        global $post;
        
        if ($column === 'einvoice_status') {
            $order = wc_get_order($post->ID);
            $has_einvoice_data = $order->get_meta('_billing_tin') ? true : false;
            
            echo $has_einvoice_data 
                ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' 
                : '<mark class="no"><span class="dashicons dashicons-no"></span></mark>';
        }
    }
}
