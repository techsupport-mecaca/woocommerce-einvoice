<?php
defined('ABSPATH') || exit;

/**
 * WC_EInvoice_Admin Class
 * Handles all admin functionality for the E-Invoice plugin
 */
class WC_EInvoice_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Admin menu
        add_action('admin_menu', array($this, 'add_menu_items'));
        
        // Admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Settings registration
        add_action('admin_init', array($this, 'register_settings'));
        
        // AJAX handlers
        add_action('wp_ajax_wc_einvoice_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_wc_einvoice_bulk_action', array($this, 'ajax_bulk_action'));
    }

    /**
     * Register admin menu items
     */
    public function add_menu_items() {
        add_menu_page(
            __('E-Invoice', 'wc-einvoice'),
            __('E-Invoice', 'wc-einvoice'),
            'manage_options',
            'wc-einvoice',
            array($this, 'render_admin_page'),
            'dashicons-media-spreadsheet',
            56
        );
    }

    /**
     * Register plugin settings
     */
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

    /**
     * Enqueue admin assets
     */
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
            'nonce' => wp_create_nonce('wc-einvoice-admin'),
            'i18n' => array(
                'save_success' => __('Settings saved successfully.', 'wc-einvoice'),
                'save_error' => __('Error saving settings.', 'wc-einvoice'),
                'confirm_delete' => __('Are you sure you want to delete this?', 'wc-einvoice')
            )
        ));
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'wc-einvoice'));
        }

        require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-admin-page.php';
    }

    /**
     * Get total number of e-invoices
     */
    public function get_total_einvoices() {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}wc_einvoice_data");
    }

    /**
     * Get monthly e-invoice count
     */
    public function get_monthly_einvoices() {
        global $wpdb;
        $first_day = date('Y-m-01 00:00:00');
        $last_day = date('Y-m-t 23:59:59');
        
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wc_einvoice_data 
            WHERE created_at BETWEEN %s AND %s",
            $first_day,
            $last_day
        ));
    }

    /**
     * Get pending data count
     */
    public function get_pending_count() {
        global $wpdb;
        return $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wc_einvoice_data 
            WHERE tin_number = '' OR sst_registration = ''"
        );
    }

    /**
     * Display recent e-invoices
     */
    public function display_recent_einvoices() {
        global $wpdb;
        $recent_invoices = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}wc_einvoice_data 
            ORDER BY created_at DESC LIMIT 5"
        );

        if (empty($recent_invoices)) {
            echo '<p>' . esc_html__('No recent e-invoices found.', 'wc-einvoice') . '</p>';
            return;
        }

        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__('Customer', 'wc-einvoice') . '</th>';
        echo '<th>' . esc_html__('TIN', 'wc-einvoice') . '</th>';
        echo '<th>' . esc_html__('SST Registration', 'wc-einvoice') . '</th>';
        echo '<th>' . esc_html__('Date', 'wc-einvoice') . '</th>';
        echo '</tr></thead>';
        
        foreach ($recent_invoices as $invoice) {
            $user = get_user_by('id', $invoice->user_id);
            echo '<tr>';
            echo '<td>' . esc_html($user ? $user->display_name : __('Unknown', 'wc-einvoice')) . '</td>';
            echo '<td>' . esc_html($invoice->tin_number) . '</td>';
            echo '<td>' . esc_html($invoice->sst_registration) . '</td>';
            echo '<td>' . esc_html(date_i18n(get_option('date_format'), strtotime($invoice->created_at))) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    }

    /**
     * Sanitize settings
     */
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

    /**
     * Sanitize payment settings
     */
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

    /**
     * AJAX handler for saving settings
     */
    public function ajax_save_settings() {
        check_ajax_referer('wc-einvoice-admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'wc-einvoice'));
        }

        $settings = isset($_POST['settings']) ? wp_unslash($_POST['settings']) : array();
        $sanitized = $this->sanitize_settings($settings);
        
        update_option('wc_einvoice_settings', $sanitized);
        wp_send_json_success(__('Settings saved successfully', 'wc-einvoice'));
    }

    /**
     * AJAX handler for bulk actions
     */
    public function ajax_bulk_action() {
        check_ajax_referer('wc-einvoice-admin', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'wc-einvoice'));
        }

        $action = isset($_POST['bulk_action']) ? sanitize_text_field($_POST['bulk_action']) : '';
        $ids = isset($_POST['ids']) ? array_map('absint', $_POST['ids']) : array();

        if (empty($action) || empty($ids)) {
            wp_send_json_error(__('Invalid request', 'wc-einvoice'));
        }

        switch ($action) {
            case 'delete':
                $this->bulk_delete($ids);
                break;
            case 'export':
                $this->bulk_export($ids);
                break;
            default:
                wp_send_json_error(__('Invalid action', 'wc-einvoice'));
        }
    }

    /**
     * Bulk delete handler
     */
    private function bulk_delete($ids) {
        global $wpdb;
        $table = $wpdb->prefix . 'wc_einvoice_data';
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")",
                $ids
            )
        );

        wp_send_json_success(__('Records deleted successfully', 'wc-einvoice'));
    }

    /**
     * Bulk export handler
     */
    private function bulk_export($ids) {
        global $wpdb;
        $table = $wpdb->prefix . 'wc_einvoice_data';
        
        $records = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")",
                $ids
            )
        );

        if (empty($records)) {
            wp_send_json_error(__('No records found', 'wc-einvoice'));
        }

        $csv_data = $this->generate_csv($records);
        wp_send_json_success(array('csv' => $csv_data));
    }

    /**
     * Generate CSV from records
     */
    private function generate_csv($records) {
        $headers = array(
            'Customer',
            'TIN',
            'SST Registration',
            'Tax Type',
            'Created Date'
        );

        $csv_data = array();
        $csv_data[] = implode(',', $headers);

        foreach ($records as $record) {
            $user = get_user_by('id', $record->user_id);
            $csv_data[] = implode(',', array(
                $user ? $user->display_name : 'Unknown',
                $record->tin_number,
                $record->sst_registration,
                $record->tax_type,
                date('Y-m-d', strtotime($record->created_at))
            ));
        }

        return implode("\n", $csv_data);
    }
}
