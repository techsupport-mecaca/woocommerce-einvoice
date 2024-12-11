<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Checkout_Fields {
    public function __construct() {
        // Add checkout fields
        add_filter('woocommerce_checkout_fields', array($this, 'add_checkout_fields'));
        
        // Save custom checkout fields
        add_action('woocommerce_checkout_create_order', array($this, 'save_checkout_fields'), 10, 2);
        
        // Display saved fields data for returning customers
        add_action('woocommerce_checkout_get_value', array($this, 'get_checkout_saved_values'), 10, 2);
    }

    public function add_checkout_fields($fields) {
        // Add business identification fields
        $fields['billing']['billing_tin'] = array(
            'type'        => 'text',
            'label'       => __('Tax Identification Number (TIN)', 'wc-einvoice'),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 25,
        );

        $fields['billing']['billing_sst_registration'] = array(
            'type'        => 'text',
            'label'       => __('SST Registration Number', 'wc-einvoice'),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 26,
        );

        $fields['billing']['billing_registration_number'] = array(
            'type'        => 'text',
            'label'       => __('Registration/Identification Number', 'wc-einvoice'),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 27,
        );

        $fields['billing']['billing_tax_type'] = array(
            'type'        => 'select',
            'label'       => __('Tax Type', 'wc-einvoice'),
            'required'    => true,
            'class'       => array('form-row-wide'),
            'priority'    => 28,
            'options'     => array(
                ''      => __('Select tax type', 'wc-einvoice'),
                'sst'   => __('Sales and Service Tax (SST)', 'wc-einvoice'),
                'gst'   => __('Goods and Services Tax (GST)', 'wc-einvoice'),
                'none'  => __('No Tax', 'wc-einvoice'),
            ),
        );

        $fields['billing']['billing_tax_exemption'] = array(
            'type'        => 'textarea',
            'label'       => __('Tax Exemption Details', 'wc-einvoice'),
            'required'    => false,
            'class'       => array('form-row-wide'),
            'priority'    => 29,
        );

        return $fields;
    }

    public function save_checkout_fields($order, $data) {
        global $wpdb;
        
        // Get user ID
        $user_id = get_current_user_id();
        
        if ($user_id > 0) {
            // Save to user meta
            $meta_fields = array(
                'billing_tin',
                'billing_sst_registration',
                'billing_registration_number',
                'billing_tax_type',
                'billing_tax_exemption'
            );

            foreach ($meta_fields as $field) {
                if (!empty($_POST[$field])) {
                    update_user_meta($user_id, $field, sanitize_text_field($_POST[$field]));
                }
            }

            // Save to custom table
            $table_name = $wpdb->prefix . 'wc_einvoice_data';
            
            $wpdb->replace(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'tin_number' => sanitize_text_field($_POST['billing_tin']),
                    'sst_registration' => sanitize_text_field($_POST['billing_sst_registration']),
                    'registration_number' => sanitize_text_field($_POST['billing_registration_number']),
                    'tax_type' => sanitize_text_field($_POST['billing_tax_type']),
                    'tax_exemption_details' => sanitize_textarea_field($_POST['billing_tax_exemption'])
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s')
            );
        }

        // Save to order meta
        $order->update_meta_data('_billing_tin', sanitize_text_field($_POST['billing_tin']));
        $order->update_meta_data('_billing_sst_registration', sanitize_text_field($_POST['billing_sst_registration']));
        $order->update_meta_data('_billing_registration_number', sanitize_text_field($_POST['billing_registration_number']));
        $order->update_meta_data('_billing_tax_type', sanitize_text_field($_POST['billing_tax_type']));
        $order->update_meta_data('_billing_tax_exemption', sanitize_textarea_field($_POST['billing_tax_exemption']));
    }

    public function get_checkout_saved_values($value, $input) {
        // Only proceed if user is logged in
        if (!is_user_logged_in()) {
            return $value;
        }

        $user_id = get_current_user_id();
        
        // Map checkout fields to user meta
        $meta_mapping = array(
            'billing_tin',
            'billing_sst_registration',
            'billing_registration_number',
            'billing_tax_type',
            'billing_tax_exemption'
        );

        if (in_array($input, $meta_mapping)) {
            $meta_value = get_user_meta($user_id, $input, true);
            return !empty($meta_value) ? $meta_value : $value;
        }

        return $value;
    }
}
