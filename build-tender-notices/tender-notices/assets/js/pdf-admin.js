/**
 * Tender Notices PDF Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        TenderNoticesPDF.init();
    });

    // Main TenderNoticesPDF object
    window.TenderNoticesPDF = {
        
        /**
         * Initialize the PDF admin interface
         */
        init: function() {
            this.bindEvents();
            this.initMediaUploader();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // PDF upload button
            $(document).on('click', '#upload-pdf', this.handlePdfUpload);
            
            // PDF remove button
            $(document).on('click', '#remove-pdf', this.handlePdfRemove);
            
            // File input change
            $(document).on('change', '#pdf-file-input', this.handleFileSelect);
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
         * Handle PDF upload
         */
        handlePdfUpload: function(e) {
            e.preventDefault();
            
            if (TenderNoticesPDF.mediaUploader) {
                TenderNoticesPDF.mediaUploader.open();
                
                TenderNoticesPDF.mediaUploader.on('select', function() {
                    var attachment = TenderNoticesPDF.mediaUploader.state().get('selection').first().toJSON();
                    TenderNoticesPDF.selectPdf(attachment);
                });
            } else {
                // Fallback to file input
                TenderNoticesPDF.showFileInput();
            }
        },

        /**
         * Handle PDF removal
         */
        handlePdfRemove: function(e) {
            e.preventDefault();
            
            if (confirm(tenderNoticesAjax.strings.remove_confirm)) {
                var pdfId = $('#tender_pdf_id').val();
                
                if (pdfId) {
                    TenderNoticesPDF.removePdf(pdfId);
                } else {
                    TenderNoticesPDF.clearPdfPreview();
                }
            }
        },

        /**
         * Handle file selection
         */
        handleFileSelect: function(e) {
            var file = e.target.files[0];
            if (file) {
                TenderNoticesPDF.uploadFile(file);
            }
        },

        /**
         * Show file input
         */
        showFileInput: function() {
            var input = $('<input type="file" id="pdf-file-input" accept=".pdf" style="display: none;" />');
            $('body').append(input);
            input.click();
        },

        /**
         * Select PDF from media library
         */
        selectPdf: function(attachment) {
            // Update form field
            $('#tender_pdf_id').val(attachment.id);
            
            // Update preview
            var previewHtml = '<p><strong>Selected PDF:</strong></p>' +
                             '<p><a href="' + attachment.url + '" target="_blank">' + attachment.filename + '</a></p>' +
                             '<p><small>Size: ' + TenderNoticesPDF.formatFileSize(attachment.filesizeInBytes) + '</small></p>' +
                             '<button type="button" id="remove-pdf" class="button">Remove PDF</button>';
            
            $('#tender-pdf-preview').html(previewHtml);
            
            TenderNoticesPDF.showSuccess('PDF selected successfully.');
        },

        /**
         * Upload file via AJAX
         */
        uploadFile: function(file) {
            // Validate file type
            if (file.type !== 'application/pdf') {
                TenderNoticesPDF.showError(tenderNoticesAjax.strings.upload_error + ' Only PDF files are allowed.');
                return;
            }
            
            // Validate file size (10MB limit)
            var maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                TenderNoticesPDF.showError('File size exceeds the maximum allowed size (10MB).');
                return;
            }
            
            // Show loading state
            TenderNoticesPDF.showLoading();
            
            // Create FormData
            var formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'tender_notices_upload_pdf');
            formData.append('nonce', tenderNoticesAjax.nonce);
            
            // Upload file
            $.ajax({
                url: tenderNoticesAjax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    TenderNoticesPDF.hideLoading();
                    
                    if (response.success) {
                        // Update form field
                        $('#tender_pdf_id').val(response.data.attachment_id);
                        
                        // Update preview
                        var previewHtml = '<p><strong>Uploaded PDF:</strong></p>' +
                                         '<p><a href="' + response.data.url + '" target="_blank">' + response.data.filename + '</a></p>' +
                                         '<button type="button" id="remove-pdf" class="button">Remove PDF</button>';
                        
                        $('#tender-pdf-preview').html(previewHtml);
                        TenderNoticesPDF.showSuccess('PDF uploaded successfully.');
                    } else {
                        TenderNoticesPDF.showError('Upload failed: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    TenderNoticesPDF.hideLoading();
                    TenderNoticesPDF.showError('Upload failed: ' + error);
                }
            });
        },

        /**
         * Remove PDF
         */
        removePdf: function(pdfId) {
            TenderNoticesPDF.showLoading();
            
            $.ajax({
                url: tenderNoticesAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'tender_notices_remove_pdf',
                    attachment_id: pdfId,
                    nonce: tenderNoticesAjax.remove_nonce
                },
                success: function(response) {
                    TenderNoticesPDF.hideLoading();
                    
                    if (response.success) {
                        TenderNoticesPDF.clearPdfPreview();
                        TenderNoticesPDF.showSuccess('PDF removed successfully.');
                    } else {
                        TenderNoticesPDF.showError('Failed to remove PDF: ' + response.data);
                    }
                },
                error: function() {
                    TenderNoticesPDF.hideLoading();
                    TenderNoticesPDF.showError('Failed to remove PDF. Please try again.');
                }
            });
        },

        /**
         * Clear PDF preview
         */
        clearPdfPreview: function() {
            $('#tender_pdf_id').val('');
            $('#tender-pdf-preview').html('<p>No PDF uploaded</p>');
        },

        /**
         * Format file size
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            $('#tender-pdf-upload').addClass('loading');
            $('#upload-pdf').prop('disabled', true).text('Uploading...');
        },

        /**
         * Hide loading state
         */
        hideLoading: function() {
            $('#tender-pdf-upload').removeClass('loading');
            $('#upload-pdf').prop('disabled', false).text('Upload PDF');
        },

        /**
         * Show success message
         */
        showSuccess: function(message) {
            TenderNoticesPDF.showMessage(message, 'success');
        },

        /**
         * Show error message
         */
        showError: function(message) {
            TenderNoticesPDF.showMessage(message, 'error');
        },

        /**
         * Show message
         */
        showMessage: function(message, type) {
            // Remove existing messages
            $('.tender-pdf-message').remove();
            
            var messageClass = 'notice-' + type;
            var messageHtml = '<div class="tender-pdf-message notice ' + messageClass + ' is-dismissible"><p>' + message + '</p></div>';
            
            $('#tender-pdf-upload').after(messageHtml);
            
            // Auto-hide after 3 seconds
            setTimeout(function() {
                $('.tender-pdf-message').fadeOut();
            }, 3000);
        }
    };

})(jQuery);
