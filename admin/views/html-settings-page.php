<?php
// admin/views/html-settings-page.php
defined('ABSPATH') || exit;

$settings = get_option('wc_einvoice_settings', array());
?>
<div class="wc-einvoice-settings-wrap">
    <form method="post" action="options.php" class="wc-einvoice-settings-form">
        <?php settings_fields('wc_einvoice_settings'); ?>
        
        <div class="wc-einvoice-settings-section">
            <h2><?php echo esc_html__('Company Information', 'wc-einvoice'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wc_einvoice_company_name">
                            <?php echo esc_html__('Company Name', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="wc_einvoice_company_name" 
                               name="wc_einvoice_settings[company_name]" 
                               value="<?php echo esc_attr($settings['company_name'] ?? ''); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wc_einvoice_tax_registration">
                            <?php echo esc_html__('Tax Registration Number', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="wc_einvoice_tax_registration" 
                               name="wc_einvoice_settings[tax_registration]" 
                               value="<?php echo esc_attr($settings['tax_registration'] ?? ''); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wc_einvoice_company_address">
                            <?php echo esc_html__('Company Address', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="wc_einvoice_company_address" 
                                  name="wc_einvoice_settings[company_address]" 
                                  rows="4" 
                                  class="large-text"><?php echo esc_textarea($settings['company_address'] ?? ''); ?></textarea>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wc-einvoice-settings-section">
            <h2><?php echo esc_html__('E-Invoice Settings', 'wc-einvoice'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wc_einvoice_prefix">
                            <?php echo esc_html__('Invoice Prefix', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="wc_einvoice_prefix" 
                               name="wc_einvoice_settings[invoice_prefix]" 
                               value="<?php echo esc_attr($settings['invoice_prefix'] ?? 'INV'); ?>" 
                               class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="wc_einvoice_next_number">
                            <?php echo esc_html__('Next Invoice Number', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" 
                               id="wc_einvoice_next_number" 
                               name="wc_einvoice_settings[next_invoice_number]" 
                               value="<?php echo esc_attr($settings['next_invoice_number'] ?? '1000'); ?>" 
                               min="1" 
                               step="1" 
                               class="regular-text">
                    </td>
                </tr>
            </table>
        </div>

        <div class="wc-einvoice-settings-section">
            <h2><?php echo esc_html__('Tax Settings', 'wc-einvoice'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="wc_einvoice_tax_type">
                            <?php echo esc_html__('Default Tax Type', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <select id="wc_einvoice_tax_type" 
                                name="wc_einvoice_settings[default_tax_type]">
                            <option value="sst" <?php selected($settings['default_tax_type'] ?? 'sst', 'sst'); ?>>
                                <?php echo esc_html__('SST', 'wc-einvoice'); ?>
                            </option>
                            <option value="gst" <?php selected($settings['default_tax_type'] ?? 'sst', 'gst'); ?>>
                                <?php echo esc_html__('GST', 'wc-einvoice'); ?>
                            </option>
                            <option value="none" <?php selected($settings['default_tax_type'] ?? 'sst', 'none'); ?>>
                                <?php echo esc_html__('None', 'wc-einvoice'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>
</div>
