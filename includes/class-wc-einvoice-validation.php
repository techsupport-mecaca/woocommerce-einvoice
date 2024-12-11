<?php
defined('ABSPATH') || exit;

class WC_EInvoice_Validation {
    private $errors = array();
    
    public function __construct() {
        // Register validation hooks
        add_filter('woocommerce_checkout_posted_data', array($this, 'validate_checkout_fields'));
        add_action('woocommerce_checkout_process', array($this, 'process_checkout_validation'));
        
        // Register integration hooks
        add_action('wc_einvoice_before_save_data', array($this, 'before_save_action'));
        add_action('wc_einvoice_after_save_data', array($this, 'after_save_action'));
        add_filter('wc_einvoice_data', array($this, 'filter_einvoice_data'), 10, 2);
        
        // Error handling
        add_action('admin_notices', array($this, 'display_admin_errors'));
        add_action('woocommerce_notices', array($this, 'display_frontend_errors'));
    }

    /**
     * Validate checkout fields
     */
    public function validate_checkout_fields($posted_data) {
        try {
            // Allow third-party validation rules
            $validation_rules = apply_filters('wc_einvoice_validation_rules', array(
                'billing_tin' => array(
                    'required' => true,
                    'type' => 'string',
                    'min_length' => 5,
                    'max_length' => 20
                ),
                'billing_sst_registration' => array(
                    'required' => true,
                    'type' => 'string',
                    'min_length' => 5,
                    'max_length' => 20
                ),
                'billing_registration_number' => array(
                    'required' => true,
                    'type' => 'string',
                    'min_length' => 5,
                    'max_length' => 30
                )
            ));

            foreach ($validation_rules as $field => $rules) {
                if (isset($posted_data[$field])) {
                    $this->validate_field($field, $posted_data[$field], $rules);
                }
            }

            // Allow third-party validation
            do_action('wc_einvoice_custom_validation', $posted_data, $this);

            return $posted_data;

        } catch (Exception $e) {
            wc_add_notice($e->getMessage(), 'error');
            return $posted_data;
        }
    }

    /**
     * Validate individual field
     */
    private function validate_field($field, $value, $rules) {
        // Required field validation
        if (!empty($rules['required']) && empty($value)) {
            throw new Exception(sprintf(
                __('Field %s is required.', 'wc-einvoice'),
                $this->get_field_label($field)
            ));
        }

        // Type validation
        if (!empty($rules['type'])) {
            switch ($rules['type']) {
                case 'number':
                    if (!is_numeric($value)) {
                        throw new Exception(sprintf(
                            __('Field %s must be a number.', 'wc-einvoice'),
                            $this->get_field_label($field)
                        ));
                    }
                    break;
                case 'email':
                    if (!is_email($value)) {
                        throw new Exception(sprintf(
                            __('Field %s must be a valid email address.', 'wc-einvoice'),
                            $this->get_field_label($field)
                        ));
                    }
                    break;
            }
        }

        // Length validation
        if (!empty($rules['min_length']) && strlen($value) < $rules['min_length']) {
            throw new Exception(sprintf(
                __('Field %s must be at least %d characters.', 'wc-einvoice'),
                $this->get_field_label($field),
                $rules['min_length']
            ));
        }

        if (!empty($rules['max_length']) && strlen($value) > $rules['max_length']) {
            throw new Exception(sprintf(
                __('Field %s must not exceed %d characters.', 'wc-einvoice'),
                $this->get_field_label($field),
                $rules['max_length']
            ));
        }

        // Allow custom validation per field
        do_action('wc_einvoice_field_validation', $field, $value, $rules);
    }

    /**
     * Process checkout validation
     */
    public function process_checkout_validation() {
        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                wc_add_notice($error, 'error');
            }
        }
    }

    /**
     * Integration hooks for before saving data
     */
    public function before_save_action($data) {
        do_action('wc_einvoice_before_process_data', $data);
        
        // Allow data modification before save
        $data = apply_filters('wc_einvoice_pre_save_data', $data);
        
        return $data;
    }

    /**
     * Integration hooks for after saving data
     */
    public function after_save_action($data) {
        do_action('wc_einvoice_after_process_data', $data);
        
        // Notify third-party integrations
        do_action('wc_einvoice_data_saved', $data);
    }

    /**
     * Filter e-invoice data
     */
    public function filter_einvoice_data($data, $context = '') {
        // Allow third-party modifications
        return apply_filters('wc_einvoice_filtered_data', $data, $context);
    }

    /**
     * Display admin errors
     */
    public function display_admin_errors() {
        $screen = get_current_screen();
        
        if ($screen && strpos($screen->id, 'wc-einvoice') !== false) {
            settings_errors('wc_einvoice_notices');
        }
    }

    /**
     * Display frontend errors
     */
    public function display_frontend_errors() {
        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                wc_print_notice($error, 'error');
            }
        }
    }

    /**
     * Get field label for error messages
     */
    private function get_field_label($field) {
        $labels = array(
            'billing_tin' => __('Tax Identification Number', 'wc-einvoice'),
            'billing_sst_registration' => __('SST Registration', 'wc-einvoice'),
            'billing_registration_number' => __('Registration Number', 'wc-einvoice')
        );

        return isset($labels[$field]) ? $labels[$field] : $field;
    }
}

// Initialize validation
new WC_EInvoice_Validation();
