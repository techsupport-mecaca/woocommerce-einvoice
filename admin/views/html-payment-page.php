<?php
// admin/views/html-payment-page.php
defined('ABSPATH') || exit;

$payment_settings = get_option('wc_einvoice_payment_settings', array());
?>
<div class="wc-einvoice-payment-wrap">
    <form method="post" action="options.php" class="wc-einvoice-payment-form">
        <?php settings_fields('wc_einvoice_payment_settings'); ?>
        
        <div class="wc-einvoice-settings-section">
            <h2><?php echo esc_html__('Default Payment Settings', 'wc-einvoice'); ?></h2>
            <p class="description">
                <?php echo esc_html__('Configure default payment settings for e-invoices. These settings will be applied to new invoices but can be modified per invoice.', 'wc-einvoice'); ?>
            </p>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="payment_mode">
                            <?php echo esc_html__('Payment Mode', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <select id="payment_mode" 
                                name="wc_einvoice_payment_settings[payment_mode]">
                            <option value="bank_transfer" <?php selected($payment_settings['payment_mode'] ?? '', 'bank_transfer'); ?>>
                                <?php echo esc_html__('Bank Transfer', 'wc-einvoice'); ?>
                            </option>
                            <option value="credit_card" <?php selected($payment_settings['payment_mode'] ?? '', 'credit_card'); ?>>
                                <?php echo esc_html__('Credit Card', 'wc-einvoice'); ?>
                            </option>
                            <option value="fpx" <?php selected($payment_settings['payment_mode'] ?? '', 'fpx'); ?>>
                                <?php echo esc_html__('FPX', 'wc-einvoice'); ?>
                            </option>
                            <option value="other" <?php selected($payment_settings['payment_mode'] ?? '', 'other'); ?>>
                                <?php echo esc_html__('Other', 'wc-einvoice'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="bank_details">
                            <?php echo esc_html__('Bank Account Details', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="bank_details" 
                                  name="wc_einvoice_payment_settings[bank_details]" 
                                  rows="4" 
                                  class="large-text"><?php echo esc_textarea($payment_settings['bank_details'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php echo esc_html__('Enter your company bank account details for bank transfer payments.', 'wc-einvoice'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="payment_terms">
                            <?php echo esc_html__('Payment Terms', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="payment_terms" 
                                  name="wc_einvoice_payment_settings[payment_terms]" 
                                  rows="4" 
                                  class="large-text"><?php echo esc_textarea($payment_settings['payment_terms'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php echo esc_html__('Specify your default payment terms and conditions.', 'wc-einvoice'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wc-einvoice-settings-section">
            <h2><?php echo esc_html__('Prepayment Settings', 'wc-einvoice'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="enable_prepayment">
                            <?php echo esc_html__('Enable Prepayment', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" 
                               id="enable_prepayment" 
                               name="wc_einvoice_payment_settings[enable_prepayment]" 
                               value="1" 
                               <?php checked(isset($payment_settings['enable_prepayment'])); ?>>
                        <p class="description">
                            <?php echo esc_html__('Allow customers to make prepayments on invoices.', 'wc-einvoice'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="prepayment_percentage">
                            <?php echo esc_html__('Default Prepayment Percentage', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" 
                               id="prepayment_percentage" 
                               name="wc_einvoice_payment_settings[prepayment_percentage]" 
                               value="<?php echo esc_attr($payment_settings['prepayment_percentage'] ?? '50'); ?>" 
                               min="0" 
                               max="100" 
                               step="0.01" 
                               class="small-text">
                        <span>%</span>
                        <p class="description">
                            <?php echo esc_html__('Set the default prepayment percentage for invoices.', 'wc-einvoice'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="wc-einvoice-settings-section">
            <h2><?php echo esc_html__('Additional Payment Information', 'wc-einvoice'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="payment_instructions">
                            <?php echo esc_html__('Payment Instructions', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="payment_instructions" 
                                  name="wc_einvoice_payment_settings[payment_instructions]" 
                                  rows="4" 
                                  class="large-text"><?php echo esc_textarea($payment_settings['payment_instructions'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php echo esc_html__('Additional payment instructions to be displayed on the invoice.', 'wc-einvoice'); ?>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="late_payment_terms">
                            <?php echo esc_html__('Late Payment Terms', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <textarea id="late_payment_terms" 
                                  name="wc_einvoice_payment_settings[late_payment_terms]" 
                                  rows="4" 
                                  class="large-text"><?php echo esc_textarea($payment_settings['late_payment_terms'] ?? ''); ?></textarea>
                        <p class="description">
                            <?php echo esc_html__('Terms and conditions for late payments.', 'wc-einvoice'); ?>
                        </p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(); ?>
    </form>
</div>
