<?php
class WC_EInvoice_Test_Case extends WP_UnitTestCase {
    protected $einvoice;
    protected $validation;
    protected $logger;

    public function setUp(): void {
        parent::setUp();
        
        // Initialize main plugin class
        $this->einvoice = new WC_EInvoice();
        $this->validation = new WC_EInvoice_Validation();
        $this->logger = new WC_EInvoice_Logger();

        // Create test customer
        $this->customer_id = $this->factory->user->create(array(
            'role' => 'customer'
        ));
    }

    public function tearDown(): void {
        parent::tearDown();
    }

    /**
     * Test basic plugin initialization
     */
    public function test_plugin_initialization() {
        $this->assertTrue(class_exists('WC_EInvoice'));
        $this->assertNotNull($this->einvoice);
        
        // Check if required hooks are registered
        $this->assertTrue(has_action('plugins_loaded'));
        $this->assertTrue(has_action('admin_menu'));
        $this->assertTrue(has_action('init'));
    }

    /**
     * Test validation rules
     */
    public function test_field_validation() {
        $test_data = array(
            'billing_tin' => '12345678',
            'billing_sst_registration' => 'SST123456',
            'billing_registration_number' => 'REG123456'
        );

        // Test valid data
        $validated_data = $this->validation->validate_checkout_fields($test_data);
        $this->assertEquals($test_data, $validated_data);

        // Test invalid TIN
        $invalid_data = $test_data;
        $invalid_data['billing_tin'] = '123'; // Too short
        try {
            $this->validation->validate_checkout_fields($invalid_data);
            $this->fail('Validation should fail for invalid TIN');
        } catch (Exception $e) {
            $this->assertStringContainsString('must be at least', $e->getMessage());
        }

        // Test required fields
        $missing_data = $test_data;
        unset($missing_data['billing_sst_registration']);
        try {
            $this->validation->validate_checkout_fields($missing_data);
            $this->fail('Validation should fail for missing required field');
        } catch (Exception $e) {
            $this->assertStringContainsString('is required', $e->getMessage());
        }
    }

    /**
     * Test logging functionality
     */
    public function test_logging() {
        $test_message = 'Test log message';
        $test_context = array('test_key' => 'test_value');
        
        // Test info logging
        $this->logger->log($test_message, $test_context, 'info');
        $logs = $this->logger->get_logs(1);
        $this->assertNotEmpty($logs);
        $this->assertStringContainsString($test_message, $logs[0]['message']);
        
        // Test error logging
        $error_message = 'Test error message';
        $test_exception = new Exception('Test exception');
        $this->logger->log_error($error_message, $test_exception);
        $logs = $this->logger->get_logs(1);
        $this->assertStringContainsString($error_message, $logs[0]['message']);
        $this->assertEquals('ERROR', $logs[0]['level']);
    }

    /**
     * Test database operations
     */
    public function test_database_operations() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_einvoice_data';
        
        // Test data insertion
        $test_data = array(
            'user_id' => $this->customer_id,
            'tin_number' => '12345678',
            'sst_registration' => 'SST123456',
            'tax_type' => 'sst',
            'tax_exemption_details' => 'Test exemption'
        );
        
        $wpdb->insert($table_name, $test_data);
        $this->assertEquals(1, $wpdb->rows_affected);
        
        // Test data retrieval
        $retrieved = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $this->customer_id)
        );
        $this->assertNotNull($retrieved);
        $this->assertEquals($test_data['tin_number'], $retrieved->tin_number);
    }

    /**
     * Test settings management
     */
    public function test_settings() {
        $test_settings = array(
            'enable_einvoice' => 1,
            'company_info' => 'Test Company',
            'auto_generate' => 'order_created',
            'invoice_prefix' => 'TEST',
            'next_invoice_number' => '1000'
        );
        
        // Test settings save
        update_option('wc_einvoice_settings', $test_settings);
        
        // Test settings retrieval
        $saved_settings = get_option('wc_einvoice_settings');
        $this->assertEquals($test_settings, $saved_settings);
    }

    /**
     * Test integration hooks
     */
    public function test_integration_hooks() {
        $test_data = array('test_key' => 'test_value');
        $modified_data = array();
        
        // Test pre-save filter
        add_filter('wc_einvoice_pre_save_data', function($data) use (&$modified_data) {
            $modified_data = $data;
            return $data;
        });
        
        do_action('wc_einvoice_before_save_data', $test_data);
        $this->assertEquals($test_data, $modified_data);
        
        // Test post-save action
        $action_triggered = false;
        add_action('wc_einvoice_data_saved', function($data) use (&$action_triggered) {
            $action_triggered = true;
        });
        
        do_action('wc_einvoice_after_save_data', $test_data);
        $this->assertTrue($action_triggered);
    }

    /**
     * Test error handling
     */
    public function test_error_handling() {
        // Test admin notice display
        ob_start();
        do_action('admin_notices');
        $output = ob_get_clean();
        
        // Add test error
        add_settings_error(
            'wc_einvoice_notices',
            'test_error',
            'Test error message'
        );
        
        ob_start();
        do_action('admin_notices');
        $output_with_error = ob_get_clean();
        
        $this->assertStringContainsString('Test error message', $output_with_error);
    }

    /**
     * Test checkout field generation
     */
    public function test_checkout_fields() {
        $fields = apply_filters('woocommerce_checkout_fields', array());
        
        // Check if our custom fields are added
        $this->assertArrayHasKey('billing_tin', $fields['billing']);
        $this->assertArrayHasKey('billing_sst_registration', $fields['billing']);
        $this->assertArrayHasKey('billing_registration_number', $fields['billing']);
        
        // Check field properties
        $this->assertTrue($fields['billing']['billing_tin']['required']);
        $this->assertEquals('text', $fields['billing']['billing_tin']['type']);
    }
}

/**
 * Mock WooCommerce order for testing
 */
class WC_EInvoice_Mock_Order {
    private $meta_data = array();
    
    public function update_meta_data($key, $value) {
        $this->meta_data[$key] = $value;
    }
    
    public function get_meta($key, $single = true) {
        return isset($this->meta_data[$key]) ? $this->meta_data[$key] : null;
    }
}
