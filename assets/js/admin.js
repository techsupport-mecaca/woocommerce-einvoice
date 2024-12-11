/**
 * WooCommerce E-Invoice Admin JavaScript
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    // Main admin handler
    const WCEInvoiceAdmin = {
        init: function() {
            this.initTabs();
            this.initBulkActions();
            this.initFormValidation();
            this.initNotifications();
        },

        // Tab navigation handling
        initTabs: function() {
            $('.wc-einvoice-tabs .nav-tab').on('click', function(e) {
                e.preventDefault();
                const target = $(this).attr('href');

                // Update URL without page reload
                window.history.pushState({}, '', target);

                // Update active tab
                $('.wc-einvoice-tabs .nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');

                // Show relevant content
                $('.wc-einvoice-tab-content').hide();
                $(target.replace('#', '#tab-')).show();
            });

            // Handle initial tab on page load
            const hash = window.location.hash;
            if (hash) {
                $(`.wc-einvoice-tabs .nav-tab[href="${hash}"]`).trigger('click');
            }
        },

        // Bulk actions handling
        initBulkActions: function() {
            const self = this;

            // Bulk selection
            $('#wc-einvoice-select-all').on('change', function() {
                $('.wc-einvoice-record-select').prop('checked', $(this).prop('checked'));
            });

            // Bulk action handler
            $('#wc-einvoice-bulk-action').on('click', function(e) {
                e.preventDefault();
                const action = $('#wc-einvoice-bulk-action-select').val();
                const selectedIds = [];

                $('.wc-einvoice-record-select:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    self.showNotification('Please select at least one record.', 'warning');
                    return;
                }

                switch (action) {
                    case 'export':
                        self.bulkExport(selectedIds);
                        break;
                    case 'delete':
                        self.bulkDelete(selectedIds);
                        break;
                    default:
                        self.showNotification('Please select an action.', 'warning');
                }
            });
        },

        // Form validation
        initFormValidation: function() {
            $('.wc-einvoice-settings-form').on('submit', function(e) {
                const requiredFields = $(this).find('[required]');
                let isValid = true;

                requiredFields.each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('wc-einvoice-error');
                    } else {
                        $(this).removeClass('wc-einvoice-error');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    WCEInvoiceAdmin.showNotification('Please fill in all required fields.', 'error');
                }
            });
        },

        // Notification system
        initNotifications: function() {
            // Clear existing notifications after 5 seconds
            setTimeout(function() {
                $('.wc-einvoice-notice').fadeOut();
            }, 5000);
        },

        // Show notification message
        showNotification: function(message, type = 'success') {
            const notice = $('<div>')
                .addClass('wc-einvoice-notice')
                .addClass(`wc-einvoice-notice-${type}`)
                .text(message);

            $('.wc-einvoice-container').prepend(notice);

            setTimeout(function() {
                notice.fadeOut();
            }, 5000);
        },

        // Bulk export handler
        bulkExport: function(ids) {
            const self = this;
            
            $.ajax({
                url: wcEinvoiceAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wc_einvoice_bulk_export',
                    nonce: wcEinvoiceAdmin.nonce,
                    ids: ids
                },
                beforeSend: function() {
                    $('.wc-einvoice-table').addClass('wc-einvoice-loading');
                },
                success: function(response) {
                    if (response.success) {
                        // Trigger download
                        const blob = new Blob([response.data], { type: 'text/csv' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        
                        a.style.display = 'none';
                        a.href = url;
                        a.download = 'einvoice-export.csv';
                        
                        document.body.appendChild(a);
                        a.click();
                        
                        window.URL.revokeObjectURL(url);
                        a.remove();

                        self.showNotification('Export completed successfully.', 'success');
                    } else {
                        self.showNotification('Export failed. Please try again.', 'error');
                    }
                },
                error: function() {
                    self.showNotification('Export failed. Please try again.', 'error');
                },
                complete: function() {
                    $('.wc-einvoice-table').removeClass('wc-einvoice-loading');
                }
            });
        },

        // Bulk delete handler
        bulkDelete: function(ids) {
            const self = this;
            
            if (!confirm('Are you sure you want to delete the selected records?')) {
                return;
            }

            $.ajax({
                url: wcEinvoiceAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'wc_einvoice_bulk_delete',
                    nonce: wcEinvoiceAdmin.nonce,
                    ids: ids
                },
                beforeSend: function() {
                    $('.wc-einvoice-table').addClass('wc-einvoice-loading');
                },
                success: function(response) {
                    if (response.success) {
                        ids.forEach(function(id) {
                            $(`tr[data-record-id="${id}"]`).fadeOut();
                        });
                        self.showNotification('Records deleted successfully.', 'success');
                    } else {
                        self.showNotification('Delete failed. Please try again.', 'error');
                    }
                },
                error: function() {
                    self.showNotification('Delete failed. Please try again.', 'error');
                },
                complete: function() {
                    $('.wc-einvoice-table').removeClass('wc-einvoice-loading');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WCEInvoiceAdmin.init();
    });

})(jQuery);
