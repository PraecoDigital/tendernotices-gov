<?php
/**
 * Tender Notices Admin Interface
 */

if (!defined('ABSPATH')) {
    exit;
}

class TenderNotices_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_notices', array($this, 'admin_notices'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        add_action('wp_ajax_tender_notices_upload_pdf', array($this, 'handle_pdf_upload'));
        add_action('wp_ajax_tender_notices_remove_pdf', array($this, 'handle_pdf_removal'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=tender_notice',
            __('Settings', 'tender-notices'),
            __('Settings', 'tender-notices'),
            'manage_options',
            'tender-notices-settings',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        wp_add_dashboard_widget(
            'tender_notices_dashboard',
            __('Tender Notices Overview', 'tender-notices'),
            array($this, 'dashboard_widget')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('tender_notices_settings', 'tender_notices_options');
        
        add_settings_section(
            'tender_notices_display',
            __('Display Settings', 'tender-notices'),
            array($this, 'display_section_callback'),
            'tender_notices_settings'
        );
        
        add_settings_field(
            'default_layout',
            __('Default Layout', 'tender-notices'),
            array($this, 'layout_field_callback'),
            'tender_notices_settings',
            'tender_notices_display'
        );
        
        add_settings_field(
            'posts_per_page',
            __('Posts Per Page', 'tender-notices'),
            array($this, 'posts_per_page_field_callback'),
            'tender_notices_settings',
            'tender_notices_display'
        );
        
        add_settings_field(
            'show_excerpt',
            __('Show Excerpt', 'tender-notices'),
            array($this, 'show_excerpt_field_callback'),
            'tender_notices_settings',
            'tender_notices_display'
        );
        
        add_settings_field(
            'excerpt_length',
            __('Excerpt Length', 'tender-notices'),
            array($this, 'excerpt_length_field_callback'),
            'tender_notices_settings',
            'tender_notices_display'
        );
    }
    
    /**
     * Display section callback
     */
    public function display_section_callback() {
        echo '<p>' . __('Configure how tender notices are displayed on the frontend.', 'tender-notices') . '</p>';
    }
    
    /**
     * Layout field callback
     */
    public function layout_field_callback() {
        $options = get_option('tender_notices_options');
        $layout = isset($options['default_layout']) ? $options['default_layout'] : 'single';
        ?>
        <select name="tender_notices_options[default_layout]">
            <option value="single" <?php selected($layout, 'single'); ?>><?php _e('Single Column', 'tender-notices'); ?></option>
            <option value="two" <?php selected($layout, 'two'); ?>><?php _e('Two Column', 'tender-notices'); ?></option>
        </select>
        <p class="description"><?php _e('Choose the default layout for displaying tender notices.', 'tender-notices'); ?></p>
        <?php
    }
    
    /**
     * Posts per page field callback
     */
    public function posts_per_page_field_callback() {
        $options = get_option('tender_notices_options');
        $posts_per_page = isset($options['posts_per_page']) ? $options['posts_per_page'] : 10;
        ?>
        <input type="number" name="tender_notices_options[posts_per_page]" value="<?php echo esc_attr($posts_per_page); ?>" min="1" max="100" />
        <p class="description"><?php _e('Number of tender notices to display per page.', 'tender-notices'); ?></p>
        <?php
    }
    
    /**
     * Show excerpt field callback
     */
    public function show_excerpt_field_callback() {
        $options = get_option('tender_notices_options');
        $show_excerpt = isset($options['show_excerpt']) ? $options['show_excerpt'] : true;
        ?>
        <input type="checkbox" name="tender_notices_options[show_excerpt]" value="1" <?php checked($show_excerpt, 1); ?> />
        <p class="description"><?php _e('Show excerpt/summary for each tender notice.', 'tender-notices'); ?></p>
        <?php
    }
    
    /**
     * Excerpt length field callback
     */
    public function excerpt_length_field_callback() {
        $options = get_option('tender_notices_options');
        $excerpt_length = isset($options['excerpt_length']) ? $options['excerpt_length'] : 25;
        ?>
        <input type="number" name="tender_notices_options[excerpt_length]" value="<?php echo esc_attr($excerpt_length); ?>" min="10" max="100" />
        <p class="description"><?php _e('Number of words in the excerpt.', 'tender-notices'); ?></p>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Tender Notices Settings', 'tender-notices'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('tender_notices_settings');
                do_settings_sections('tender_notices_settings');
                submit_button();
                ?>
            </form>
            
            <div class="tender-notices-info">
                <h2><?php _e('Usage', 'tender-notices'); ?></h2>
                <p><?php _e('Use the following shortcode to display tender notices on any page or post:', 'tender-notices'); ?></p>
                <code>[tender_notices]</code>
                
                <h3><?php _e('Shortcode Parameters', 'tender-notices'); ?></h3>
                <ul>
                    <li><code>columns</code> - Number of columns (1 or 2)</li>
                    <li><code>posts_per_page</code> - Number of notices to display</li>
                    <li><code>category</code> - Filter by category slug</li>
                    <li><code>status</code> - Filter by status slug</li>
                    <li><code>show_excerpt</code> - Show/hide excerpt (true/false)</li>
                    <li><code>excerpt_length</code> - Number of words in excerpt</li>
                </ul>
                
                <h3><?php _e('Example Usage', 'tender-notices'); ?></h3>
                <code>[tender_notices columns="2" posts_per_page="6" category="construction"]</code>
            </div>
        </div>
        <?php
    }
    
    /**
     * Dashboard widget
     */
    public function dashboard_widget() {
        $total_notices = wp_count_posts('tender_notice');
        $recent_notices = get_posts(array(
            'post_type' => 'tender_notice',
            'posts_per_page' => 5,
            'post_status' => 'publish'
        ));
        
        echo '<div class="tender-notices-dashboard">';
        echo '<p><strong>' . __('Total Tender Notices:', 'tender-notices') . '</strong> ' . $total_notices->publish . '</p>';
        
        if ($recent_notices) {
            echo '<h4>' . __('Recent Notices:', 'tender-notices') . '</h4>';
            echo '<ul>';
            foreach ($recent_notices as $notice) {
                $closing_date = get_post_meta($notice->ID, '_tender_closing_date', true);
                $closing_text = $closing_date ? ' (' . date('M j, Y', strtotime($closing_date)) . ')' : '';
                echo '<li><a href="' . get_edit_post_link($notice->ID) . '">' . $notice->post_title . '</a>' . $closing_text . '</li>';
            }
            echo '</ul>';
        }
        
        echo '<p><a href="' . admin_url('edit.php?post_type=tender_notice') . '" class="button">' . __('Manage Tender Notices', 'tender-notices') . '</a></p>';
        echo '</div>';
    }
    
    /**
     * Admin notices
     */
    public function admin_notices() {
        if (isset($_GET['tender_notices_message'])) {
            $message = sanitize_text_field($_GET['tender_notices_message']);
            switch ($message) {
                case 'settings_saved':
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'tender-notices') . '</p></div>';
                    break;
            }
        }
    }
    
    /**
     * Handle PDF upload via AJAX
     */
    public function handle_pdf_upload() {
        if (!current_user_can('upload_files')) {
            wp_die(__('You do not have permission to upload files.', 'tender-notices'));
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'tender_notices_upload')) {
            wp_die(__('Security check failed.', 'tender-notices'));
        }
        
        $file = $_FILES['file'];
        
        // Validate file type
        $allowed_types = array('application/pdf');
        $file_type = wp_check_filetype($file['name']);
        
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(__('Only PDF files are allowed.', 'tender-notices'));
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
            'url' => $upload['url']
        ));
    }
    
    /**
     * Handle PDF removal via AJAX
     */
    public function handle_pdf_removal() {
        if (!current_user_can('delete_posts')) {
            wp_die(__('You do not have permission to delete files.', 'tender-notices'));
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'tender_notices_remove')) {
            wp_die(__('Security check failed.', 'tender-notices'));
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        
        if (wp_delete_attachment($attachment_id, true)) {
            wp_send_json_success(__('PDF removed successfully.', 'tender-notices'));
        } else {
            wp_send_json_error(__('Failed to remove PDF.', 'tender-notices'));
        }
    }
}
