<?php
/**
 * Tender Notices Frontend Display
 */

if (!defined('ABSPATH')) {
    exit;
}

class TenderNotices_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('template_redirect', array($this, 'template_redirect'));
        add_filter('template_include', array($this, 'template_include'));
        add_action('wp_head', array($this, 'add_meta_tags'));
    }
    
    /**
     * Template redirect for single tender notices
     */
    public function template_redirect() {
        if (is_singular('tender_notice')) {
            // Add custom CSS for single tender notice pages
            add_action('wp_head', array($this, 'single_tender_notice_styles'));
        }
    }
    
    /**
     * Template include for custom templates
     */
    public function template_include($template) {
        if (is_singular('tender_notice')) {
            $custom_template = TENDER_NOTICES_PLUGIN_PATH . 'templates/single-tender-notice.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        if (is_post_type_archive('tender_notice')) {
            $custom_template = TENDER_NOTICES_PLUGIN_PATH . 'templates/archive-tender-notice.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Add meta tags for SEO
     */
    public function add_meta_tags() {
        if (is_singular('tender_notice')) {
            global $post;
            $closing_date = get_post_meta($post->ID, '_tender_closing_date', true);
            $issue_date = get_post_meta($post->ID, '_tender_issue_date', true);
            
            if ($closing_date) {
                echo '<meta name="tender-closing-date" content="' . esc_attr($closing_date) . '">' . "\n";
            }
            
            if ($issue_date) {
                echo '<meta name="tender-issue-date" content="' . esc_attr($issue_date) . '">' . "\n";
            }
        }
    }
    
    /**
     * Single tender notice styles
     */
    public function single_tender_notice_styles() {
        ?>
        <style>
        .tender-notice-single {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .tender-notice-single .entry-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-gray, #E5E7EB);
        }
        
        .tender-notice-single .entry-title {
            color: var(--primary-navy, #003366);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .tender-notice-meta {
            background: var(--bg-gray, #F9FAFB);
            padding: 1.5rem;
            border-radius: var(--border-radius, 0.5rem);
            margin-bottom: 2rem;
        }
        
        .tender-notice-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .tender-notice-meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .tender-notice-meta-label {
            font-weight: 600;
            color: var(--text-gray, #6B7280);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        
        .tender-notice-meta-value {
            color: var(--primary-navy, #003366);
            font-weight: 500;
        }
        
        .tender-notice-actions {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-gray, #E5E7EB);
        }
        
        .tender-notice-actions .btn-primary {
            background: var(--accent-teal, #4DB6AC);
            color: white;
            padding: 0.75rem 2rem;
            border-radius: var(--border-radius, 0.5rem);
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: background-color 0.2s ease;
        }
        
        .tender-notice-actions .btn-primary:hover {
            background: var(--primary-navy, #003366);
        }
        
        @media (max-width: 768px) {
            .tender-notice-single {
                padding: 1rem;
            }
            
            .tender-notice-meta-grid {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }
    
    /**
     * Get tender notice data for display
     */
    public static function get_tender_notice_data($post_id) {
        $data = array(
            'title' => get_the_title($post_id),
            'content' => get_the_content(null, false, $post_id),
            'excerpt' => get_the_excerpt($post_id),
            'issue_date' => get_post_meta($post_id, '_tender_issue_date', true),
            'closing_date' => get_post_meta($post_id, '_tender_closing_date', true),
            'contact_name' => get_post_meta($post_id, '_tender_contact_name', true),
            'contact_email' => get_post_meta($post_id, '_tender_contact_email', true),
            'contact_phone' => get_post_meta($post_id, '_tender_contact_phone', true),
            'tender_value' => get_post_meta($post_id, '_tender_value', true),
            'tender_reference' => get_post_meta($post_id, '_tender_reference', true),
            'tender_number' => get_post_meta($post_id, '_tender_number', true),
            'pre_bid_meeting_date' => get_post_meta($post_id, '_pre_bid_meeting_date', true),
            'pre_bid_meeting_mandatory' => get_post_meta($post_id, '_pre_bid_meeting_mandatory', true),
            'pre_bid_meeting_online' => get_post_meta($post_id, '_pre_bid_meeting_online', true),
            'site_visit_date' => get_post_meta($post_id, '_site_visit_date', true),
            'site_visit_mandatory' => get_post_meta($post_id, '_site_visit_mandatory', true),
            'pdf_id' => get_post_meta($post_id, '_tender_pdf_id', true),
            'categories' => get_the_terms($post_id, 'tender_category'),
            'status' => get_the_terms($post_id, 'tender_status'),
        );
        
        // Get PDF URL if exists
        if ($data['pdf_id']) {
            $data['pdf_url'] = wp_get_attachment_url($data['pdf_id']);
            $data['pdf_filename'] = basename(get_attached_file($data['pdf_id']));
        }
        
        return $data;
    }
    
    /**
     * Format date for display
     */
    public static function format_date($date, $format = 'M j, Y') {
        if (!$date) {
            return '';
        }
        
        return date($format, strtotime($date));
    }
    
    /**
     * Check if tender is expired
     */
    public static function is_tender_expired($closing_date) {
        if (!$closing_date) {
            return false;
        }
        
        $now = new DateTime();
        $closing = new DateTime($closing_date);
        
        return $closing < $now;
    }
    
    /**
     * Get tender status class
     */
    public static function get_tender_status_class($closing_date) {
        if (self::is_tender_expired($closing_date)) {
            return 'tender-expired';
        }
        
        $now = new DateTime();
        $closing = new DateTime($closing_date);
        $days_remaining = $now->diff($closing)->days;
        
        if ($days_remaining <= 7) {
            return 'tender-closing-soon';
        }
        
        return 'tender-active';
    }
    
    /**
     * Get days remaining until closing
     */
    public static function get_days_remaining($closing_date) {
        if (!$closing_date) {
            return null;
        }
        
        $now = new DateTime();
        $closing = new DateTime($closing_date);
        
        if ($closing < $now) {
            return 0;
        }
        
        return $now->diff($closing)->days;
    }
}
