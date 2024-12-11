<?php
class WC_EInvoice_Advanced_Validation extends WC_EInvoice_Validation {
    protected $custom_rules = array();

    public function __construct() {
        parent::__construct();
        $this->init_custom_rules();
        
        add_filter('wc_einvoice_validation_rules', array($this, 'add_custom_validation_rules'));
    }

    protected function init_custom_rules() {
        $this->custom_rules = array(
            'tin_format' => array(
                'callback' => array($this, 'validate_tin_format'),
                'error_message' => __('Invalid TIN format. Please check and try again.', 'wc-einvoice')
            ),
            'sst_format' => array(
                'callback' => array($this, 'validate_sst_format'),
                'error_message' => __('Invalid SST registration format. Please check and try again.', 'wc-einvoice')
            ),
            'registration_format' => array(
                'callback' => array($this, 'validate_registration_format'),
                'error_message' => __('Invalid registration number format. Please check and try again.', 'wc-einvoice')
            )
        );
    }

    public function add_custom_validation_rules($rules) {
        return array_merge($rules, array(
            'billing_tin' => array(
                'required' => true,
                'type' => 'string',
                'min_length' => 5,
                'max_length' => 20,
                'custom_rules' => array('tin_format')
            ),
            'billing_sst_registration' => array(
                'required' => true,
                'type' => 'string',
                'min_length' => 5,
                'max_length' => 20,
                'custom_rules' => array('sst_format')
            ),
            'billing_registration_number' => array(
                'required' => true,
                'type' => 'string',
                'min_length' => 5,
                'max_length' => 30,
                'custom_rules' => array('registration_format')
            )
        ));
    }

    protected function validate_tin_format($value) {
        // Implement specific TIN format validation for Malaysia
        $pattern = '/^[0-9]{9,12}$/'; // Example pattern
        return preg_match($pattern, $value);
    }

    protected function validate_sst_format($value) {
        // Implement specific SST registration format validation
        $pattern = '/^SST-[0-9]{6,10}$/i'; // Example pattern
        return preg_match($pattern, $value);
    }

    protected function validate_registration_format($value) {
        // Implement specific registration number format validation
        $pattern = '/^[A-Z0-9]{5,15}$/'; // Example pattern
        return preg_match($pattern, $value);
    }

    public function validate_field($field, $value, $rules) {
        parent::validate_field($field, $value, $rules);

        // Apply custom validation rules
        if (!empty($rules['custom_rules'])) {
            foreach ($rules['custom_rules'] as $rule_name) {
                if (isset($this->custom_rules[$rule_name])) {
                    $rule = $this->custom_rules[$rule_name];
                    if (!call_user_func($rule['callback'], $value)) {
                        throw new Exception($rule['error_message']);
                    }
                }
            }
        }
    }

    public function validate_dependent_fields($data) {
        // Example of dependent field validation
        if (!empty($data['billing_tax_type']) && $data['billing_tax_type'] === 'sst') {
            if (empty($data['billing_sst_registration'])) {
                throw new Exception(__('SST Registration Number is required when tax type is SST.', 'wc-einvoice'));
            }
        }
    }

    public function sanitize_field($value, $field) {
        // Add specific sanitization rules per field
        switch ($field) {
            case 'billing_tin':
                return preg_replace('/[^0-9]/', '', $value);
            
            case 'billing_sst_registration':
                return strtoupper(trim($value));
            
            case 'billing_registration_number':
                return strtoupper(trim($value));
            
            default:
                return sanitize_text_field($value);
        }
    }
}
