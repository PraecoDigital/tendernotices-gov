<?php
/**
 * Tender Notices PDF Manager
 */

if (!defined('ABSPATH')) {
    exit;
}

class TenderNotices_PDF_Manager {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_download_tender_pdf', array($this, 'handle_pdf_download'));
        add_action('wp_ajax_nopriv_download_tender_pdf', array($this, 'handle_pdf_download'));
        add_action('wp_ajax_tender_notices_upload_pdf', array($this, 'handle_pdf_upload'));
        add_action('wp_ajax_tender_notices_remove_pdf', array($this, 'handle_pdf_removal'));
        add_filter('upload_mimes', array($this, 'allow_pdf_uploads'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_pdf_scripts'));
    }
    
    /**
     * Handle PDF download
     */
    public function handle_pdf_download() {
        if (!isset($_GET['tender_id']) || !isset($_GET['pdf_id'])) {
            wp_die(__('Invalid request.', 'tender-notices'));
        }
        
        $tender_id = intval($_GET['tender_id']);
        $pdf_id = intval($_GET['pdf_id']);
        
        // Verify the PDF belongs to this tender
        $tender_pdf_id = get_post_meta($tender_id, '_tender_pdf_id', true);
        if ($tender_pdf_id != $pdf_id) {
            wp_die(__('Invalid PDF for this tender.', 'tender-notices'));
        }
        
        // Check if tender notice is published
        $tender_post = get_post($tender_id);
        if (!$tender_post || $tender_post->post_status !== 'publish') {
            wp_die(__('Tender notice not found or not published.', 'tender-notices'));
        }
        
        // Get PDF file path
        $file_path = get_attached_file($pdf_id);
        if (!$file_path || !file_exists($file_path)) {
            wp_die(__('PDF file not found.', 'tender-notices'));
        }
        
        // Track download (optional)
        $this->track_download($tender_id, $pdf_id);
        
        // Serve the file
        $this->serve_file($file_path, $pdf_id);
    }
    
    /**
     * Handle PDF upload via AJAX
     */
    public function handle_pdf_upload() {
        if (!current_user_can('upload_files')) {
            wp_send_json_error(__('You do not have permission to upload files.', 'tender-notices'));
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'tender_notices_upload')) {
            wp_send_json_error(__('Security check failed.', 'tender-notices'));
        }
        
        $file = $_FILES['file'];
        
        // Validate file type
        $allowed_types = array('application/pdf');
        $file_type = wp_check_filetype($file['name']);
        
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(__('Only PDF files are allowed.', 'tender-notices'));
        }
        
        // Check file size (default 10MB limit)
        $max_size = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $max_size) {
            wp_send_json_error(__('File size exceeds the maximum allowed size.', 'tender-notices'));
        }
        
        // Upload file
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error($upload['error']);
        }
        
        // Create attachment
        $attachment = array(
            'post_mime_type' => $upload['type'],
            'post_title' => sanitize_file_name($file['name']),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attachment_id = wp_insert_attachment($attachment, $upload['file']);
        
        if (is_wp_error($attachment_id)) {
            wp_send_json_error(__('Failed to create attachment.', 'tender-notices'));
        }
        
        // Generate attachment metadata
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
        wp_update_attachment_metadata($attachment_id, $attachment_data);
        
        wp_send_json_success(array(
            'attachment_id' => $attachment_id,
            'url' => $upload['url'],
            'filename' => basename($upload['file'])
        ));
    }
    
    /**
     * Handle PDF removal via AJAX
     */
    public function handle_pdf_removal() {
        if (!current_user_can('delete_posts')) {
            wp_send_json_error(__('You do not have permission to delete files.', 'tender-notices'));
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'tender_notices_remove')) {
            wp_send_json_error(__('Security check failed.', 'tender-notices'));
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        
        if (wp_delete_attachment($attachment_id, true)) {
            wp_send_json_success(__('PDF removed successfully.', 'tender-notices'));
        } else {
            wp_send_json_error(__('Failed to remove PDF.', 'tender-notices'));
        }
    }
    
    /**
     * Allow PDF uploads
     */
    public function allow_pdf_uploads($mimes) {
        $mimes['pdf'] = 'application/pdf';
        return $mimes;
    }
    
    /**
     * Enqueue PDF-related scripts
     */
    public function enqueue_pdf_scripts() {
        if (is_admin()) {
            wp_enqueue_media();
            wp_enqueue_script('tender-notices-pdf-admin', TENDER_NOTICES_PLUGIN_URL . 'assets/js/pdf-admin.js', array('jquery'), TENDER_NOTICES_VERSION, true);
            wp_localize_script('tender-notices-pdf-admin', 'tenderNoticesAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tender_notices_upload'),
                'remove_nonce' => wp_create_nonce('tender_notices_remove'),
                'strings' => array(
                    'upload_error' => __('Error uploading file.', 'tender-notices'),
                    'remove_confirm' => __('Are you sure you want to remove this PDF?', 'tender-notices'),
                )
            ));
        }
    }
    
    /**
     * Track download statistics
     */
    private function track_download($tender_id, $pdf_id) {
        $downloads = get_post_meta($tender_id, '_tender_downloads', true);
        if (!$downloads) {
            $downloads = 0;
        }
        $downloads++;
        update_post_meta($tender_id, '_tender_downloads', $downloads);
        
        // Also track by PDF ID
        $pdf_downloads = get_post_meta($pdf_id, '_pdf_downloads', true);
        if (!$pdf_downloads) {
            $pdf_downloads = 0;
        }
        $pdf_downloads++;
        update_post_meta($pdf_id, '_pdf_downloads', $pdf_downloads);
    }
    
    /**
     * Serve file with proper headers
     */
    private function serve_file($file_path, $attachment_id) {
        $filename = basename($file_path);
        $file_size = filesize($file_path);
        
        // Set headers
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file_size);
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Read and output file
        readfile($file_path);
        exit;
    }
    
    /**
     * Get download statistics
     */
    public static function get_download_stats($tender_id) {
        return get_post_meta($tender_id, '_tender_downloads', true) ?: 0;
    }
    
    /**
     * Get PDF file info
     */
    public static function get_pdf_info($pdf_id) {
        if (!$pdf_id) {
            return null;
        }
        
        $file_path = get_attached_file($pdf_id);
        if (!$file_path || !file_exists($file_path)) {
            return null;
        }
        
        return array(
            'id' => $pdf_id,
            'url' => wp_get_attachment_url($pdf_id),
            'filename' => basename($file_path),
            'size' => filesize($file_path),
            'downloads' => get_post_meta($pdf_id, '_pdf_downloads', true) ?: 0,
        );
    }
    
    /**
     * Generate secure download URL
     */
    public static function get_download_url($tender_id, $pdf_id) {
        return add_query_arg(array(
            'action' => 'download_tender_pdf',
            'tender_id' => $tender_id,
            'pdf_id' => $pdf_id,
        ), admin_url('admin-ajax.php'));
    }
    
    /**
     * Check if PDF exists and is accessible
     */
    public static function is_pdf_accessible($pdf_id) {
        if (!$pdf_id) {
            return false;
        }
        
        $file_path = get_attached_file($pdf_id);
        return $file_path && file_exists($file_path);
    }
}
