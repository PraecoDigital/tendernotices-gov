/**
 * Tender Notices Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        TenderNoticesAdmin.init();
    });

    // Main TenderNoticesAdmin object
    window.TenderNoticesAdmin = {
        
        /**
         * Initialize the admin interface
         */
        init: function() {
            this.bindEvents();
            this.initMediaUploader();
            this.initDatePickers();
            this.initFormValidation();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // PDF upload button
            $(document).on('click', '#upload-pdf', this.handlePdfUpload);
            
            // PDF remove button
            $(document).on('click', '#remove-pdf', this.handlePdfRemove);
            
            // Form submission
            $(document).on('submit', '#post', this.handleFormSubmit);
            
            // Auto-save functionality
            $(document).on('change', 'input, select, textarea', this.handleAutoSave);
        },

        /**
         * Initialize media uploader
         */
        initMediaUploader: function() {
            if (typeof wp !== 'undefined' && wp.media) {
                this.mediaUploader = wp.media({
                    title: 'Select PDF Document',
                    button: {
                        text: 'Use this PDF'
                    },
                    multiple: false,
                    library: {
                        type: 'application/pdf'
                    }
                });
            }
        },

        /**
         * Initialize date pickers
         */
        initDatePickers: function() {
            if ($.fn.datepicker) {
                $('input[type="date"]').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true
                });
            }
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            // Add validation rules
            this.addValidationRules();
        },

        /**
         * Handle PDF upload
         */
        handlePdfUpload: function(e) {
            e.preventDefault();
            
            if (TenderNoticesAdmin.mediaUploader) {
                TenderNoticesAdmin.mediaUploader.open();
                
                TenderNoticesAdmin.mediaUploader.on('select', function() {
                    var attachment = TenderNoticesAdmin.mediaUploader.state().get('selection').first().toJSON();
                    
                    // Update form fields
                    $('#tender_pdf_id').val(attachment.id);
                    
                    // Update preview
                    var previewHtml = '<p><strong>Selected PDF:</strong></p>' +
                                     '<p><a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a></p>' +
                                     '<button type="button" id="remove-pdf" class="button">Remove PDF</button>';
                    
                    $('#tender-pdf-preview').html(previewHtml);
                });
            } else {
                // Fallback to file input
                TenderNoticesAdmin.showFileInput();
            }
        },

        /**
         * Handle PDF removal
         */
        handlePdfRemove: function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to remove this PDF?')) {
                var pdfId = $('#tender_pdf_id').val();
                
                if (pdfId) {
                    // Send AJAX request to remove PDF
                    $.ajax({
                        url: tenderNoticesAjax.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'tender_notices_remove_pdf',
                            attachment_id: pdfId,
                            nonce: tenderNoticesAjax.remove_nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#tender_pdf_id').val('');
                                $('#tender-pdf-preview').html('<p>No PDF uploaded</p>');
                                TenderNoticesAdmin.showSuccess('PDF removed successfully.');
                            } else {
                                TenderNoticesAdmin.showError('Failed to remove PDF: ' + response.data);
                            }
                        },
                        error: function() {
                            TenderNoticesAdmin.showError('Failed to remove PDF. Please try again.');
                        }
                    });
                } else {
                    // Just clear the preview
                    $('#tender_pdf_id').val('');
                    $('#tender-pdf-preview').html('<p>No PDF uploaded</p>');
                }
            }
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e) {
            // Validate required fields
            if (!TenderNoticesAdmin.validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show loading state
            TenderNoticesAdmin.showLoading();
        },

        /**
         * Handle auto-save
         */
        handleAutoSave: function() {
            // Implement auto-save functionality if needed
            clearTimeout(TenderNoticesAdmin.autoSaveTimeout);
            TenderNoticesAdmin.autoSaveTimeout = setTimeout(function() {
                // Auto-save logic here
            }, 2000);
        },

        /**
         * Show file input fallback
         */
        showFileInput: function() {
            var input = $('<input type="file" accept=".pdf" />');
            input.on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    TenderNoticesAdmin.uploadFile(file);
                }
            });
            input.click();
        },

        /**
         * Upload file via AJAX
         */
        uploadFile: function(file) {
            var formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'tender_notices_upload_pdf');
            formData.append('nonce', tenderNoticesAjax.nonce);
            
            $.ajax({
                url: tenderNoticesAjax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $('#tender_pdf_id').val(response.data.attachment_id);
                        
                        var previewHtml = '<p><strong>Uploaded PDF:</strong></p>' +
                                         '<p><a href="' + response.data.url + '" target="_blank">' + response.data.filename + '</a></p>' +
                                         '<button type="button" id="remove-pdf" class="button">Remove PDF</button>';
                        
                        $('#tender-pdf-preview').html(previewHtml);
                        TenderNoticesAdmin.showSuccess('PDF uploaded successfully.');
                    } else {
                        TenderNoticesAdmin.showError('Upload failed: ' + response.data);
                    }
                },
                error: function() {
                    TenderNoticesAdmin.showError('Upload failed. Please try again.');
                }
            });
        },

        /**
         * Validate form
         */
        validateForm: function() {
            var isValid = true;
            var errors = [];
            
            // Check required fields
            $('input[required], select[required], textarea[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    errors.push($(this).attr('name') + ' is required');
                }
            });
            
            // Check date fields
            var issueDate = $('#tender_issue_date').val();
            var closingDate = $('#tender_closing_date').val();
            
            if (issueDate && closingDate) {
                var issue = new Date(issueDate);
                var closing = new Date(closingDate);
                
                if (closing <= issue) {
                    isValid = false;
                    errors.push('Closing date must be after issue date');
                }
            }
            
            // Check email format
            var email = $('#tender_contact_email').val();
            if (email && !TenderNoticesAdmin.isValidEmail(email)) {
                isValid = false;
                errors.push('Invalid email format');
            }
            
            // Show errors
            if (!isValid) {
                TenderNoticesAdmin.showError('Please fix the following errors:<br>' + errors.join('<br>'));
            }
            
            return isValid;
        },

        /**
         * Add validation rules
         */
        addValidationRules: function() {
            // Email validation
            $('#tender_contact_email').on('blur', function() {
                var email = $(this).val();
                if (email && !TenderNoticesAdmin.isValidEmail(email)) {
                    $(this).addClass('error');
                    TenderNoticesAdmin.showFieldError($(this), 'Invalid email format');
                } else {
                    $(this).removeClass('error');
                    TenderNoticesAdmin.hideFieldError($(this));
                }
            });
            
            // Date validation
            $('#tender_closing_date').on('change', function() {
                var issueDate = $('#tender_issue_date').val();
                var closingDate = $(this).val();
                
                if (issueDate && closingDate) {
                    var issue = new Date(issueDate);
                    var closing = new Date(closingDate);
                    
                    if (closing <= issue) {
                        $(this).addClass('error');
                        TenderNoticesAdmin.showFieldError($(this), 'Closing date must be after issue date');
                    } else {
                        $(this).removeClass('error');
                        TenderNoticesAdmin.hideFieldError($(this));
                    }
                }
            });
        },

        /**
         * Validate email format
         */
        isValidEmail: function(email) {
            var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return regex.test(email);
        },

        /**
         * Show field error
         */
        showFieldError: function(field, message) {
            field.siblings('.error').remove();
            field.after('<div class="error">' + message + '</div>');
        },

        /**
         * Hide field error
         */
        hideFieldError: function(field) {
            field.siblings('.error').remove();
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            $('body').addClass('loading');
            $('.button-primary').prop('disabled', true);
        },

        /**
         * Hide loading state
         */
        hideLoading: function() {
            $('body').removeClass('loading');
            $('.button-primary').prop('disabled', false);
        },

        /**
         * Show success message
         */
        showSuccess: function(message) {
            this.showMessage(message, 'success');
        },

        /**
         * Show error message
         */
        showError: function(message) {
            this.showMessage(message, 'error');
        },

        /**
         * Show message
         */
        showMessage: function(message, type) {
            var messageClass = 'notice-' + type;
            var messageHtml = '<div class="notice ' + messageClass + ' is-dismissible"><p>' + message + '</p></div>';
            
            $('.wrap h1').after(messageHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.notice').fadeOut();
            }, 5000);
        },

        /**
         * Initialize dashboard widget
         */
        initDashboardWidget: function() {
            // Add refresh button to dashboard widget
            $('.tender-notices-dashboard').append('<button type="button" class="button button-small" id="refresh-tender-stats">Refresh</button>');
            
            $('#refresh-tender-stats').on('click', function() {
                TenderNoticesAdmin.refreshDashboardStats();
            });
        },

        /**
         * Refresh dashboard statistics
         */
        refreshDashboardStats: function() {
            $.ajax({
                url: tenderNoticesAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_tender_stats',
                    nonce: tenderNoticesAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update dashboard widget with new stats
                        $('.tender-notices-dashboard').html(response.data.html);
                    }
                }
            });
        }
    };

})(jQuery);
