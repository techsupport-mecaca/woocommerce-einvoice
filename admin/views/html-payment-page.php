<?php
defined('ABSPATH') || exit;
?>
<div class="wrap wc-einvoice-payment">
    <h1><?php echo esc_html__('Payment Information Settings', 'wc-einvoice'); ?></h1>
    
    <form method="post" action="options.php">
        <?php settings_fields('wc_einvoice_payment_settings'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="payment_mode"><?php echo esc_html__('Payment Mode', 'wc-einvoice'); ?></label>
                </th>
                <td>
                    <select name="wc_einvoice_payment_settings[payment_mode]" id="payment_mode">
                        <option value=""><?php echo esc_html__('Select Payment Mode', 'wc-einvoice'); ?></option>
                        <option value="bank_transfer" <?php selected($payment_settings['payment_mode'] ?? '', 'bank_transfer'); ?>>
                            <?php echo esc_html__('Bank Transfer', 'wc-einvoice'); ?>
                        </option>
                        <option value="credit_card" <?php selected($payment_settings['payment_mode'] ?? '', 'credit_card'); ?>>
                            <?php echo esc_html__('Credit Card', 'wc-einvoice'); ?>
                        </option>
                        <option value="other" <?php selected($payment_settings['payment_mode'] ?? '', 'other'); ?>>
                            <?php echo esc_html__('Other', 'wc-einvoice'); ?>
                        </option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="bank_account"><?php echo esc_html__('Bank Account Details', 'wc-einvoice'); ?></label>
                </th>
                <td>
                    <textarea name="wc_einvoice_payment_settings[bank_account]" id="bank_account" rows="3" class="large-text">
                        <?php echo esc_textarea($payment_settings['bank_account'] ?? ''); ?>
                    </textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="payment_terms"><?php echo esc_html__('Payment Terms', 'wc-einvoice'); ?></label>
                </th>
                <td>
                    <textarea name="wc_einvoice_payment_settings[payment_terms]" id="payment_terms" rows="3" class="large-text">
                        <?php echo esc_textarea($payment_settings['payment_terms'] ?? ''); ?>
                    </textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="prepayment_amount"><?php echo esc_html__('Default Prepayment Amount (%)', 'wc-einvoice'); ?></label>
                </th>
                <td>
                    <input type="number" name="wc_einvoice_payment_settings[prepayment_amount]" id="prepayment_amount" 
                           value="<?php echo esc_attr($payment_settings['prepayment_amount'] ?? ''); ?>" 
                           class="small-text" min="0" max="100" step="0.01">
                </td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
