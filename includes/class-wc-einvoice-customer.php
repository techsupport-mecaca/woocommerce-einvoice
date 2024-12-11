<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Customer {
    private $user_id;
    private $customer_data;
    
    public function __construct() {
        add_action('woocommerce_created_customer', array($this, 'save_customer_einvoice_data'));
        add_action('woocommerce_checkout_update_user_meta', array($this, 'update_customer_einvoice_data'));
        add_action('woocommerce_customer_save_address', array($this, 'sync_address_data'));
        
        // Admin profile fields
        add_action('show_user_profile', array($this, 'add_customer_einvoice_fields'));
        add_action('edit_user_profile', array($this, 'add_customer_einvoice_fields'));
        add_action('personal_options_update', array($this, 'save_customer_einvoice_fields'));
        add_action('edit_user_profile_update', array($this, 'save_customer_einvoice_fields'));
    }

    public function get_customer_data($user_id = null) {
        if (null === $user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return false;
        }

        global $wpdb;
        
        // Try to get from cache first
        $cache_key = 'wc_einvoice_customer_' . $user_id;
        $customer_data = wp_cache_get($cache_key);

        if (false === $customer_data) {
            // Get from database
            $customer_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}wc_einvoice_data WHERE user_id = %d",
                $user_id
            ));

            if ($customer_data) {
                wp_cache_set($cache_key, $customer_data);
            }
        }

        return $customer_data;
    }

    public function save_customer_einvoice_data($user_id) {
        if (!$user_id) {
            return false;
        }

        global $wpdb;

        $data = array(
            'user_id' => $user_id,
            'tin_number' => isset($_POST['billing_tin']) ? sanitize_text_field($_POST['billing_tin']) : '',
            'sst_registration' => isset($_POST['billing_sst_registration']) ? sanitize_text_field($_POST['billing_sst_registration']) : '',
            'registration_number' => isset($_POST['billing_registration_number']) ? sanitize_text_field($_POST['billing_registration_number']) : '',
            'tax_type' => isset($_POST['billing_tax_type']) ? sanitize_text_field($_POST['billing_tax_type']) : 'sst',
            'tax_exemption_details' => isset($_POST['billing_tax_exemption']) ? sanitize_textarea_field($_POST['billing_tax_exemption']) : ''
        );

        $format = array(
            '%d',  // user_id
            '%s',  // tin_number
            '%s',  // sst_registration
            '%s',  // registration_number
            '%s',  // tax_type
            '%s'   // tax_exemption_details
        );

        // Allow modification of data before save
        $data = apply_filters('wc_einvoice_before_save_customer_data', $data, $user_id);

        $result = $wpdb->replace(
            $wpdb->prefix . 'wc_einvoice_data',
            $data,
            $format
        );

        if ($result) {
            // Clear cache
            wp_cache_delete('wc_einvoice_customer_' . $user_id);
            
            // Store backup in user meta
            foreach ($data as $key => $value) {
                if ($key !== 'user_id') {
                    update_user_meta($user_id, '_einvoice_' . $key, $value);
                }
            }

            do_action('wc_einvoice_after_save_customer_data', $data, $user_id);
            return true;
        }

        return false;
    }

    public function update_customer_einvoice_data($user_id) {
        if (!$user_id) {
            return;
        }

        $current_data = $this->get_customer_data($user_id);
        
        if (!$current_data) {
            $this->save_customer_einvoice_data($user_id);
            return;
        }

        $data = array(
            'user_id' => $user_id
        );

        $fields = array(
            'tin_number',
            'sst_registration',
            'registration_number',
            'tax_type',
            'tax_exemption_details'
        );

        foreach ($fields as $field) {
            $post_key = 'billing_' . $field;
            if (isset($_POST[$post_key])) {
                $data[$field] = sanitize_text_field($_POST[$post_key]);
            }
        }

        global $wpdb;
        
        $wpdb->update(
            $wpdb->prefix . 'wc_einvoice_data',
            $data,
            array('user_id' => $user_id)
        );

        wp_cache_delete('wc_einvoice_customer_' . $user_id);
    }

    public function sync_address_data($user_id) {
        $customer_data = $this->get_customer_data($user_id);
        
        if (!$customer_data) {
            return;
        }

        $address_fields = array(
            'billing_address_1',
            'billing_address_2',
            'billing_city',
            'billing_state',
            'billing_postcode',
            'billing_country'
        );

        $address = array();
        foreach ($address_fields as $field) {
            $address[$field] = get_user_meta($user_id, $field, true);
        }

        do_action('wc_einvoice_address_synced', $user_id, $address);
    }

    public function add_customer_einvoice_fields($user) {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }

        $customer_data = $this->get_customer_data($user->ID);
        
        include WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-user-einvoice-fields.php';
    }

    public function save_customer_einvoice_fields($user_id) {
        if (!current_user_can('manage_woocommerce')) {
            return false;
        }

        if (!wp_verify_nonce($_POST['_wpnonce'], 'update-user_' . $user_id)) {
            return false;
        }

        $this->save_customer_einvoice_data($user_id);
    }
}
