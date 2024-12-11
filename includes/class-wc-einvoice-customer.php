<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Customer {
    private $customer_id;
    private $customer_data;

    public function __construct() {
        add_action('woocommerce_created_customer', array($this, 'save_customer_einvoice_data'), 10, 1);
        add_action('woocommerce_update_customer', array($this, 'update_customer_einvoice_data'), 10, 1);
        add_filter('woocommerce_customer_meta_fields', array($this, 'add_customer_meta_fields'), 10, 1);
    }

    public function get_customer_einvoice_data($customer_id) {
        global $wpdb;
        
        if (!$customer_id) {
            return false;
        }

        // Get data from custom table
        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        $data = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY updated_at DESC LIMIT 1",
            $customer_id
        ));

        if (!$data) {
            // Fall back to user meta if no data in custom table
            $data = array(
                'tin_number' => get_user_meta($customer_id, 'billing_tin', true),
                'sst_registration' => get_user_meta($customer_id, 'billing_sst_registration', true),
                'tax_type' => get_user_meta($customer_id, 'billing_tax_type', true),
                'registration_number' => get_user_meta($customer_id, 'billing_registration_number', true)
            );
        }

        return $data;
    }

    public function save_customer_einvoice_data($customer_id) {
        if (!$customer_id) {
            return;
        }

        // Get posted data
        $tin = isset($_POST['billing_tin']) ? sanitize_text_field($_POST['billing_tin']) : '';
        $sst = isset($_POST['billing_sst_registration']) ? sanitize_text_field($_POST['billing_sst_registration']) : '';
        $tax_type = isset($_POST['billing_tax_type']) ? sanitize_text_field($_POST['billing_tax_type']) : '';
        $reg_number = isset($_POST['billing_registration_number']) ? sanitize_text_field($_POST['billing_registration_number']) : '';

        // Save to user meta
        update_user_meta($customer_id, 'billing_tin', $tin);
        update_user_meta($customer_id, 'billing_sst_registration', $sst);
        update_user_meta($customer_id, 'billing_tax_type', $tax_type);
        update_user_meta($customer_id, 'billing_registration_number', $reg_number);

        // Save to custom table
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        
        $wpdb->insert(
            $table_name,
            array(
                'user_id' => $customer_id,
                'tin_number' => $tin,
                'sst_registration' => $sst,
                'tax_type' => $tax_type,
                'registration_number' => $reg_number
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );

        do_action('wc_einvoice_after_save_customer_data', $customer_id);
    }

    public function update_customer_einvoice_data($customer_id) {
        $this->save_customer_einvoice_data($customer_id);
    }

    public function add_customer_meta_fields($fields) {
        $fields['einvoice'] = array(
            'title' => __('E-Invoice Information', 'wc-einvoice'),
            'fields' => array(
                'billing_tin' => array(
                    'label' => __('Tax Identification Number', 'wc-einvoice'),
                    'description' => ''
                ),
                'billing_sst_registration' => array(
                    'label' => __('SST Registration', 'wc-einvoice'),
                    'description' => ''
                ),
                'billing_tax_type' => array(
                    'label' => __('Tax Type', 'wc-einvoice'),
                    'description' => '',
                    'type' => 'select',
                    'options' => array(
                        'sst' => __('SST', 'wc-einvoice'),
                        'gst' => __('GST', 'wc-einvoice'),
                        'none' => __('None', 'wc-einvoice')
                    )
                ),
                'billing_registration_number' => array(
                    'label' => __('Registration Number', 'wc-einvoice'),
                    'description' => ''
                )
            )
        );

        return $fields;
    }

    public function validate_customer_data($customer_id, $data) {
        try {
            if (empty($data['billing_tin'])) {
                throw new Exception(__('Tax Identification Number is required.', 'wc-einvoice'));
            }

            if (empty($data['billing_sst_registration'])) {
                throw new Exception(__('SST Registration is required.', 'wc-einvoice'));
            }

            if (empty($data['billing_registration_number'])) {
                throw new Exception(__('Registration Number is required.', 'wc-einvoice'));
            }

            // Allow additional validation through filters
            do_action('wc_einvoice_validate_customer_data', $customer_id, $data);

            return true;
        } catch (Exception $e) {
            return new WP_Error('validation-error', $e->getMessage());
        }
    }

    public function get_customer_invoices($customer_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY created_at DESC",
            $customer_id
        ));
    }
}
