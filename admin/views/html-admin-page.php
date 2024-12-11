<?php
defined('ABSPATH') || exit;

// Get current settings
$settings = get_option('wc_einvoice_settings', array());
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
?>
<div class="wrap wc-einvoice-wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html__('E-Invoice Management', 'wc-einvoice'); ?></h1>
    
    <?php settings_errors(); ?>

    <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="?page=wc-einvoice&tab=dashboard" 
           class="nav-tab <?php echo $current_tab === 'dashboard' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Dashboard', 'wc-einvoice'); ?>
        </a>
        <a href="?page=wc-einvoice&tab=settings" 
           class="nav-tab <?php echo $current_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Settings', 'wc-einvoice'); ?>
        </a>
        <a href="?page=wc-einvoice&tab=payment" 
           class="nav-tab <?php echo $current_tab === 'payment' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Payment Settings', 'wc-einvoice'); ?>
        </a>
        <a href="?page=wc-einvoice&tab=bulk" 
           class="nav-tab <?php echo $current_tab === 'bulk' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Bulk Management', 'wc-einvoice'); ?>
        </a>
    </nav>

    <div class="wc-einvoice-content">
        <?php
        switch ($current_tab) {
            case 'dashboard':
                ?>
                <div class="wc-einvoice-dashboard">
                    <div class="wc-einvoice-stats">
                        <div class="stat-box">
                            <h3><?php echo esc_html__('Total E-Invoices', 'wc-einvoice'); ?></h3>
                            <p class="stat-number"><?php echo esc_html($this->get_total_einvoices()); ?></p>
                        </div>
                        <div class="stat-box">
                            <h3><?php echo esc_html__('This Month', 'wc-einvoice'); ?></h3>
                            <p class="stat-number"><?php echo esc_html($this->get_monthly_einvoices()); ?></p>
                        </div>
                        <div class="stat-box">
                            <h3><?php echo esc_html__('Pending Data', 'wc-einvoice'); ?></h3>
                            <p class="stat-number"><?php echo esc_html($this->get_pending_count()); ?></p>
                        </div>
                    </div>

                    <div class="wc-einvoice-recent">
                        <h2><?php echo esc_html__('Recent E-Invoices', 'wc-einvoice'); ?></h2>
                        <?php $this->display_recent_einvoices(); ?>
                    </div>
                </div>
                <?php
                break;

            case 'settings':
                if (file_exists(WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-settings-page.php')) {
                    require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-settings-page.php';
                }
                break;

            case 'payment':
                if (file_exists(WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-payment-page.php')) {
                    require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-payment-page.php';
                }
                break;

            case 'bulk':
                if (file_exists(WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-bulk-page.php')) {
                    require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-bulk-page.php';
                }
                break;
        }
        ?>
    </div>
</div>

<?php
// Add help tab if needed
$screen = get_current_screen();
if ($screen) {
    $screen->add_help_tab(array(
        'id'      => 'wc_einvoice_help',
        'title'   => __('E-Invoice Help', 'wc-einvoice'),
        'content' => '<h2>' . __('E-Invoice Management', 'wc-einvoice') . '</h2>' .
                    '<p>' . __('This section allows you to manage your e-invoice settings and view invoice data.', 'wc-einvoice') . '</p>' .
                    '<ul>' .
                    '<li>' . __('Dashboard: View summary and recent invoices', 'wc-einvoice') . '</li>' .
                    '<li>' . __('Settings: Configure general e-invoice settings', 'wc-einvoice') . '</li>' .
                    '<li>' . __('Payment Settings: Manage payment-related configurations', 'wc-einvoice') . '</li>' .
                    '<li>' . __('Bulk Management: Handle multiple invoices at once', 'wc-einvoice') . '</li>' .
                    '</ul>'
    ));
}
