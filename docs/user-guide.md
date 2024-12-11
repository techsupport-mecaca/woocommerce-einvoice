# WooCommerce E-Invoice Plugin Documentation

## Overview

The WooCommerce E-Invoice plugin is designed to help Malaysian businesses comply with e-invoicing requirements. This documentation provides comprehensive information about installation, configuration, and usage of the plugin.

## Installation

The plugin requires WordPress 5.8 or higher and WooCommerce 5.0 or higher. To install the plugin:

1. Download the plugin files and upload them to your `/wp-content/plugins/woocommerce-einvoice` directory.
2. Activate the plugin through the WordPress admin panel under "Plugins."
3. Navigate to WooCommerce > E-Invoice to configure the settings.

## Configuration

### General Settings

The general settings section allows you to configure basic plugin functionality:

**Company Information**
Enter your company details that will appear on e-invoices:
- Company Name
- Company Registration Number
- Tax Registration Number
- Business Address
- Contact Information

**Document Settings**
Configure how your e-invoices are generated:
- Invoice Number Prefix
- Starting Invoice Number
- Date Format
- Number Format Pattern

**Tax Settings**
Set up your tax configuration:
- Default Tax Type (SST/GST)
- Tax Registration Details
- Enable/Disable Tax Exemption Features

### Checkout Configuration

The plugin adds several fields to the WooCommerce checkout process. These fields collect necessary information for e-invoice generation:
- Tax Identification Number (TIN)
- SST Registration Number
- Business Registration Number
- Tax Type Selection
- Tax Exemption Details (if applicable)

## Usage Guide

### For Store Owners

**Managing E-Invoice Settings**
1. Navigate to WooCommerce > E-Invoice > Settings
2. Configure your company information and preferences
3. Save your settings

**Viewing Customer E-Invoice Data**
1. Go to WooCommerce > E-Invoice > Customers
2. Search for specific customers using the search function
3. View detailed e-invoice information for each customer

**Bulk Operations**
1. Select multiple records using the checkboxes
2. Choose an action from the bulk actions dropdown
3. Click "Apply" to perform the selected action

### For Developers

**Available Hooks and Filters**

The plugin provides several hooks for customization:

```php
// Modify e-invoice data before save
add_filter('wc_einvoice_before_save_data', function($data) {
    // Modify $data as needed
    return $data;
});

// Execute actions after e-invoice data is saved
add_action('wc_einvoice_after_save_data', function($data) {
    // Perform additional operations
});

// Customize validation rules
add_filter('wc_einvoice_validation_rules', function($rules) {
    // Add or modify validation rules
    return $rules;
});
```

**Database Structure**

The plugin creates two main tables:

1. `{prefix}wc_einvoice_data`: Stores primary e-invoice information
2. `{prefix}wc_einvoice_meta`: Stores additional meta data

**API Integration**

To integrate with external systems, use the provided REST API endpoints:

```php
// Example API endpoint usage
$api_url = rest_url('wc-einvoice/v1/invoices');
$response = wp_remote_get($api_url, [
    'headers' => [
        'Authorization' => 'Bearer ' . $your_api_key
    ]
]);
```

## Troubleshooting

Common issues and their solutions:

**Plugin Activation Issues**
- Verify WordPress and WooCommerce versions meet minimum requirements
- Check PHP version compatibility
- Ensure proper file permissions on the server

**Data Not Saving**
- Verify database permissions
- Check error logs for specific issues
- Confirm form submission security nonces

**Export Problems**
- Ensure proper directory permissions for temporary files
- Check PHP memory limits
- Verify CSV export settings

## Security Considerations

The plugin implements several security measures:

1. Data Validation and Sanitization
2. Nonce Verification
3. Capability Checking
4. Secure Data Storage
5. XSS Prevention
6. SQL Injection Protection

## Support and Updates

For support inquiries:
1. Create an issue on the GitHub repository
2. Contact plugin support at support@example.com
3. Check the changelog for recent updates

## Best Practices

To ensure optimal plugin performance:

1. Regularly backup your database
2. Keep the plugin updated to the latest version
3. Monitor error logs for potential issues
4. Perform regular data exports for backup
5. Review and update security settings periodically

## Changelog

Version 1.0.0 (2024-12-11)
- Initial release with core functionality
- Basic e-invoice features
- Admin interface implementation
- Database structure setup
- Security measures implementation
