/**
 * Tender Notices Frontend JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        TenderNotices.init();
    });

    // Main TenderNotices object
    window.TenderNotices = {
        
        /**
         * Initialize the plugin
         */
        init: function() {
            this.bindEvents();
            this.initPagination();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // PDF download tracking
            $(document).on('click', 'a[href*="download_tender_pdf"]', this.trackDownload);
            
            // Card hover effects
            $(document).on('mouseenter', '.tender-notice-card', this.handleCardHover);
            $(document).on('mouseleave', '.tender-notice-card', this.handleCardLeave);
        },

        

        /**
         * Initialize pagination
         */
        initPagination: function() {
            // Add loading state to pagination links
            $('.pagination-nav a').on('click', function() {
                $('.tender-notices-container').addClass('loading');
            });
        },

        

        /**
         * Track PDF downloads
         */
        trackDownload: function(e) {
            var link = $(this);
            var url = link.attr('href');
            
            // Extract tender ID from URL
            var tenderId = this.extractTenderIdFromUrl(url);
            
            if (tenderId) {
                // Send tracking request
                $.ajax({
                    url: tenderNoticesAjax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'track_tender_download',
                        tender_id: tenderId,
                        nonce: tenderNoticesAjax.nonce
                    },
                    success: function(response) {
                        console.log('Download tracked:', response);
                    },
                    error: function() {
                        console.log('Failed to track download');
                    }
                });
            }
        },

        /**
         * Extract tender ID from download URL
         */
        extractTenderIdFromUrl: function(url) {
            var match = url.match(/tender_id=(\d+)/);
            return match ? match[1] : null;
        },

        /**
         * Handle card hover
         */
        handleCardHover: function() {
            $(this).addClass('hover');
        },

        /**
         * Handle card leave
         */
        handleCardLeave: function() {
            $(this).removeClass('hover');
        },

        /**
         * Show loading state
         */
        showLoading: function(container) {
            container.addClass('loading');
            container.append('<div class="tender-notices-loading">Loading...</div>');
        },

        /**
         * Hide loading state
         */
        hideLoading: function(container) {
            container.removeClass('loading');
            container.find('.tender-notices-loading').remove();
        },

        /**
         * Show error message
         */
        showError: function(message, container) {
            var errorHtml = '<div class="tender-notices-error">' + message + '</div>';
            container.prepend(errorHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                container.find('.tender-notices-error').fadeOut();
            }, 5000);
        },

        /**
         * Show success message
         */
        showSuccess: function(message, container) {
            var successHtml = '<div class="tender-notices-success">' + message + '</div>';
            container.prepend(successHtml);
            
            // Auto-hide after 3 seconds
            setTimeout(function() {
                container.find('.tender-notices-success').fadeOut();
            }, 3000);
        },

        /**
         * Format date for display
         */
        formatDate: function(dateString) {
            var date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        },

        /**
         * Check if tender is expired
         */
        isTenderExpired: function(closingDate) {
            var now = new Date();
            var closing = new Date(closingDate);
            return closing < now;
        },

        /**
         * Get days remaining until closing
         */
        getDaysRemaining: function(closingDate) {
            var now = new Date();
            var closing = new Date(closingDate);
            var diffTime = closing - now;
            var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            return Math.max(0, diffDays);
        },

        /**
         * Update tender status indicators
         */
        updateStatusIndicators: function() {
            $('.tender-notice-card').each(function() {
                var card = $(this);
                var closingDate = card.data('closing-date');
                
                if (closingDate) {
                    if (TenderNotices.isTenderExpired(closingDate)) {
                        card.addClass('tender-expired');
                    } else {
                        var daysRemaining = TenderNotices.getDaysRemaining(closingDate);
                        if (daysRemaining <= 7) {
                            card.addClass('tender-closing-soon');
                        } else {
                            card.addClass('tender-active');
                        }
                    }
                }
            });
        },

        /**
         * Initialize status indicators
         */
        initStatusIndicators: function() {
            this.updateStatusIndicators();
            
            // Update every hour
            setInterval(this.updateStatusIndicators, 3600000);
        }
    };

    // Initialize status indicators
    TenderNotices.initStatusIndicators();

})(jQuery);
