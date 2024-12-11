<?php
// admin/views/html-bulk-page.php
defined('ABSPATH') || exit;

global $wpdb;
$table_name = $wpdb->prefix . 'wc_einvoice_data';
$records = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY created_at DESC");
?>
<div class="wc-einvoice-bulk-wrap">
    <div class="tablenav top">
        <div class="alignleft actions bulkactions">
            <label for="bulk-action-selector-top" class="screen-reader-text">
                <?php echo esc_html__('Select bulk action', 'wc-einvoice'); ?>
            </label>
            <select name="action" id="bulk-action-selector-top">
                <option value="-1"><?php echo esc_html__('Bulk Actions', 'wc-einvoice'); ?></option>
                <option value="export"><?php echo esc_html__('Export', 'wc-einvoice'); ?></option>
                <option value="delete"><?php echo esc_html__('Delete', 'wc-einvoice'); ?></option>
            </select>
            <input type="submit" id="doaction" class="button action" value="<?php echo esc_attr__('Apply', 'wc-einvoice'); ?>">
        </div>

        <div class="alignright">
            <input type="submit" id="export-all" class="button" value="<?php echo esc_attr__('Export All', 'wc-einvoice'); ?>">
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <input type="checkbox" id="cb-select-all-1">
                </td>
                <th scope="col" class="manage-column column-customer">
                    <?php echo esc_html__('Customer', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-tin">
                    <?php echo esc_html__('TIN', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-sst">
                    <?php echo esc_html__('SST Registration', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-tax-type">
                    <?php echo esc_html__('Tax Type', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-date">
                    <?php echo esc_html__('Created Date', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-actions">
                    <?php echo esc_html__('Actions', 'wc-einvoice'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): 
                $user = get_user_by('id', $record->user_id);
                ?>
                <tr data-id="<?php echo esc_attr($record->id); ?>">
                    <th scope="row" class="check-column">
                        <input type="checkbox" name="record[]" value="<?php echo esc_attr($record->id); ?>">
                    </th>
                    <td class="column-customer">
                        <?php echo esc_html($user ? $user->display_name : __('Unknown User', 'wc-einvoice')); ?>
                    </td>
                    <td class="column-tin">
                        <span class="view-mode"><?php echo esc_html($record->tin_number); ?></span>
                        <input type="text" class="edit-mode hidden" value="<?php echo esc_attr($record->tin_number); ?>">
                    </td>
                    <td class="column-sst">
                        <span class="view-mode"><?php echo esc_html($record->sst_registration); ?></span>
                        <input type="text" class="edit-mode hidden" value="<?php echo esc_attr($record->sst_registration); ?>">
                    </td>
                    <td class="column-tax-type">
                        <span class="view-mode"><?php echo esc_html($record->tax_type); ?></span>
                        <select class="edit-mode hidden">
                            <option value="sst" <?php selected($record->tax_type, 'sst'); ?>>SST</option>
                            <option value="gst" <?php selected($record->tax_type, 'gst'); ?>>GST</option>
                            <option value="none" <?php selected($record->tax_type, 'none'); ?>>None</option>
                        </select>
                    </td>
<td class="column-date">
                        <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($record->created_at))); ?>
                    </td>
                    <td class="column-actions">
                        <div class="view-mode">
                            <button type="button" class="button edit-record" data-id="<?php echo esc_attr($record->id); ?>">
                                <?php echo esc_html__('Edit', 'wc-einvoice'); ?>
                            </button>
                            <button type="button" class="button delete-record" data-id="<?php echo esc_attr($record->id); ?>">
                                <?php echo esc_html__('Delete', 'wc-einvoice'); ?>
                            </button>
                        </div>
                        <div class="edit-mode hidden">
                            <button type="button" class="button button-primary save-record" data-id="<?php echo esc_attr($record->id); ?>">
                                <?php echo esc_html__('Save', 'wc-einvoice'); ?>
                            </button>
                            <button type="button" class="button cancel-edit">
                                <?php echo esc_html__('Cancel', 'wc-einvoice'); ?>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="7" class="no-items">
                        <?php echo esc_html__('No records found.', 'wc-einvoice'); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="manage-column column-cb check-column">
                    <input type="checkbox" id="cb-select-all-2">
                </td>
                <th scope="col" class="manage-column column-customer">
                    <?php echo esc_html__('Customer', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-tin">
                    <?php echo esc_html__('TIN', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-sst">
                    <?php echo esc_html__('SST Registration', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-tax-type">
                    <?php echo esc_html__('Tax Type', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-date">
                    <?php echo esc_html__('Created Date', 'wc-einvoice'); ?>
                </th>
                <th scope="col" class="manage-column column-actions">
                    <?php echo esc_html__('Actions', 'wc-einvoice'); ?>
                </th>
            </tr>
        </tfoot>
    </table>

    <div class="tablenav bottom">
        <?php
        // Add pagination if needed
        if ($total_pages > 1) {
            echo '<div class="tablenav-pages">';
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $current_page
            ));
            echo '</div>';
        }
        ?>
    </div>
</div>

<!-- Bulk Edit Modal -->
<div id="bulk-edit" style="display:none;" class="wc-einvoice-modal">
    <div class="wc-einvoice-modal-content">
        <h2><?php echo esc_html__('Bulk Edit Records', 'wc-einvoice'); ?></h2>
        <form id="bulk-edit-form">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="bulk-tax-type">
                            <?php echo esc_html__('Tax Type', 'wc-einvoice'); ?>
                        </label>
                    </th>
                    <td>
                        <select id="bulk-tax-type" name="tax_type">
                            <option value=""><?php echo esc_html__('— No Change —', 'wc-einvoice'); ?></option>
                            <option value="sst"><?php echo esc_html__('SST', 'wc-einvoice'); ?></option>
                            <option value="gst"><?php echo esc_html__('GST', 'wc-einvoice'); ?></option>
                            <option value="none"><?php echo esc_html__('None', 'wc-einvoice'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="bulk-edit-actions">
                <button type="submit" class="button button-primary">
                    <?php echo esc_html__('Update', 'wc-einvoice'); ?>
                </button>
                <button type="button" class="button cancel-bulk-edit">
                    <?php echo esc_html__('Cancel', 'wc-einvoice'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
