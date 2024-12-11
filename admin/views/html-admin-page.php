<?php
// admin/views/html-admin-page.php
defined('ABSPATH') || exit;
?>
<div class="wrap wc-einvoice-admin">
    <h1 class="wp-heading-inline"><?php echo esc_html__('E-Invoice Management', 'wc-einvoice'); ?></h1>
    
    <hr class="wp-header-end">

    <?php
    // Display any admin notices
    settings_errors('wc_einvoice_notices');
    ?>

    <nav class="nav-tab-wrapper woo-nav-tab-wrapper">
        <a href="?page=wc-einvoice&tab=dashboard" class="nav-tab <?php echo empty($_GET['tab']) || $_GET['tab'] === 'dashboard' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Dashboard', 'wc-einvoice'); ?>
        </a>
        <a href="?page=wc-einvoice&tab=settings" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'settings' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Settings', 'wc-einvoice'); ?>
        </a>
        <a href="?page=wc-einvoice&tab=bulk" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'bulk' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Bulk Management', 'wc-einvoice'); ?>
        </a>
        <a href="?page=wc-einvoice&tab=payment" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] === 'payment' ? 'nav-tab-active' : ''; ?>">
            <?php echo esc_html__('Payment Settings', 'wc-einvoice'); ?>
        </a>
    </nav>

    <div class="wc-einvoice-content">
        <?php
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';
        
        switch ($tab) {
            case 'settings':
                require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-settings-page.php';
                break;
            case 'bulk':
                require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-bulk-page.php';
                break;
            case 'payment':
                require_once WC_EINVOICE_PLUGIN_DIR . 'admin/views/html-payment-page.php';
                break;
            default:
                // Dashboard tab
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
        }
        ?>
    </div>
</div>
