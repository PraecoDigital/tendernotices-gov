<?php
/**
 * Tender Notices Custom Post Type
 */

if (!defined('ABSPATH')) {
    exit;
}

class TenderNotices_Post_Type {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('init', array($this, 'register_taxonomies'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        add_filter('manage_tender_notice_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_tender_notice_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
    }
    
    /**
     * Register custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x('Tender Notices', 'Post type general name', 'tender-notices'),
            'singular_name'         => _x('Tender Notice', 'Post type singular name', 'tender-notices'),
            'menu_name'             => _x('Tender Notices', 'Admin Menu text', 'tender-notices'),
            'name_admin_bar'        => _x('Tender Notice', 'Add New on Toolbar', 'tender-notices'),
            'add_new'               => __('Add New', 'tender-notices'),
            'add_new_item'          => __('Add New Tender Notice', 'tender-notices'),
            'new_item'              => __('New Tender Notice', 'tender-notices'),
            'edit_item'             => __('Edit Tender Notice', 'tender-notices'),
            'view_item'             => __('View Tender Notice', 'tender-notices'),
            'all_items'             => __('All Tender Notices', 'tender-notices'),
            'search_items'          => __('Search Tender Notices', 'tender-notices'),
            'parent_item_colon'     => __('Parent Tender Notices:', 'tender-notices'),
            'not_found'             => __('No tender notices found.', 'tender-notices'),
            'not_found_in_trash'    => __('No tender notices found in Trash.', 'tender-notices'),
            'featured_image'        => _x('Tender Notice Featured Image', 'Overrides the "Featured Image" phrase', 'tender-notices'),
            'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'tender-notices'),
            'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'tender-notices'),
            'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'tender-notices'),
            'archives'              => _x('Tender Notice archives', 'The post type archive label', 'tender-notices'),
            'insert_into_item'      => _x('Insert into tender notice', 'Overrides the "Insert into post" phrase', 'tender-notices'),
            'uploaded_to_this_item' => _x('Uploaded to this tender notice', 'Overrides the "Uploaded to this post" phrase', 'tender-notices'),
            'filter_items_list'     => _x('Filter tender notices list', 'Screen reader text for the filter links', 'tender-notices'),
            'items_list_navigation' => _x('Tender notices list navigation', 'Screen reader text for the pagination', 'tender-notices'),
            'items_list'            => _x('Tender notices list', 'Screen reader text for the items list', 'tender-notices'),
        );
        
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'tender-notices'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-clipboard',
            'supports'           => array('title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'),
            'show_in_rest'       => true,
        );
        
        register_post_type('tender_notice', $args);
    }
    
    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Tender Categories
        $category_labels = array(
            'name'              => _x('Tender Categories', 'taxonomy general name', 'tender-notices'),
            'singular_name'     => _x('Tender Category', 'taxonomy singular name', 'tender-notices'),
            'search_items'      => __('Search Categories', 'tender-notices'),
            'all_items'         => __('All Categories', 'tender-notices'),
            'parent_item'       => __('Parent Category', 'tender-notices'),
            'parent_item_colon' => __('Parent Category:', 'tender-notices'),
            'edit_item'         => __('Edit Category', 'tender-notices'),
            'update_item'        => __('Update Category', 'tender-notices'),
            'add_new_item'       => __('Add New Category', 'tender-notices'),
            'new_item_name'      => __('New Category Name', 'tender-notices'),
            'menu_name'          => __('Categories', 'tender-notices'),
        );
        
        register_taxonomy('tender_category', array('tender_notice'), array(
            'hierarchical'      => true,
            'labels'            => $category_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'tender-category'),
            'show_in_rest'      => true,
        ));
        
        // Tender Status
        $status_labels = array(
            'name'              => _x('Tender Status', 'taxonomy general name', 'tender-notices'),
            'singular_name'     => _x('Tender Status', 'taxonomy singular name', 'tender-notices'),
            'search_items'      => __('Search Status', 'tender-notices'),
            'all_items'         => __('All Status', 'tender-notices'),
            'edit_item'         => __('Edit Status', 'tender-notices'),
            'update_item'        => __('Update Status', 'tender-notices'),
            'add_new_item'       => __('Add New Status', 'tender-notices'),
            'new_item_name'      => __('New Status Name', 'tender-notices'),
            'menu_name'          => __('Status', 'tender-notices'),
        );
        
        register_taxonomy('tender_status', array('tender_notice'), array(
            'hierarchical'      => false,
            'labels'            => $status_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'tender-status'),
            'show_in_rest'      => true,
        ));
    }
    
    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'tender_notice_details',
            __('Tender Notice Details', 'tender-notices'),
            array($this, 'tender_details_meta_box'),
            'tender_notice',
            'normal',
            'high'
        );
        
        add_meta_box(
            'tender_notice_pdf',
            __('PDF Document', 'tender-notices'),
            array($this, 'tender_pdf_meta_box'),
            'tender_notice',
            'side',
            'high'
        );
    }
    
    /**
     * Tender details meta box
     */
    public function tender_details_meta_box($post) {
        wp_nonce_field('tender_notice_meta_box', 'tender_notice_meta_box_nonce');
        
        $closing_date = get_post_meta($post->ID, '_tender_closing_date', true);
        $tender_number = get_post_meta($post->ID, '_tender_number', true);
        $pre_bid_meeting_date = get_post_meta($post->ID, '_pre_bid_meeting_date', true);
        $pre_bid_meeting_mandatory = get_post_meta($post->ID, '_pre_bid_meeting_mandatory', true);
        $pre_bid_meeting_online = get_post_meta($post->ID, '_pre_bid_meeting_online', true);
        $site_visit_date = get_post_meta($post->ID, '_site_visit_date', true);
        $site_visit_mandatory = get_post_meta($post->ID, '_site_visit_mandatory', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="tender_closing_date"><?php _e('Closing Date', 'tender-notices'); ?></label>
                </th>
                <td>
                    <input type="date" id="tender_closing_date" name="tender_closing_date" value="<?php echo esc_attr($closing_date); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="tender_number"><?php _e('Tender Number', 'tender-notices'); ?></label>
                </th>
                <td>
                    <input type="text" id="tender_number" name="tender_number" value="<?php echo esc_attr($tender_number); ?>" class="regular-text" />
                </td>
            </tr>
        </table>
        
        <h3><?php _e('Pre-Bid Meeting Information', 'tender-notices'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="pre_bid_meeting_date"><?php _e('Pre-Bid Meeting Date', 'tender-notices'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" id="pre_bid_meeting_date" name="pre_bid_meeting_date" value="<?php echo esc_attr($pre_bid_meeting_date); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="pre_bid_meeting_mandatory"><?php _e('Mandatory Meeting', 'tender-notices'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="pre_bid_meeting_mandatory" name="pre_bid_meeting_mandatory" value="1" <?php checked($pre_bid_meeting_mandatory, 1); ?> />
                    <label for="pre_bid_meeting_mandatory"><?php _e('This pre-bid meeting is mandatory', 'tender-notices'); ?></label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="pre_bid_meeting_online"><?php _e('Online Meeting', 'tender-notices'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="pre_bid_meeting_online" name="pre_bid_meeting_online" value="1" <?php checked($pre_bid_meeting_online, 1); ?> />
                    <label for="pre_bid_meeting_online"><?php _e('This pre-bid meeting will be held online', 'tender-notices'); ?></label>
                </td>
            </tr>
        </table>
        
        <h3><?php _e('Site Visit Information', 'tender-notices'); ?></h3>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="site_visit_date"><?php _e('Site Visit Date', 'tender-notices'); ?></label>
                </th>
                <td>
                    <input type="datetime-local" id="site_visit_date" name="site_visit_date" value="<?php echo esc_attr($site_visit_date); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="site_visit_mandatory"><?php _e('Mandatory Site Visit', 'tender-notices'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="site_visit_mandatory" name="site_visit_mandatory" value="1" <?php checked($site_visit_mandatory, 1); ?> />
                    <label for="site_visit_mandatory"><?php _e('This site visit is mandatory', 'tender-notices'); ?></label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Tender PDF meta box
     */
    public function tender_pdf_meta_box($post) {
        $pdf_id = get_post_meta($post->ID, '_tender_pdf_id', true);
        $pdf_url = '';
        if ($pdf_id) {
            $pdf_url = wp_get_attachment_url($pdf_id);
        }
        ?>
        <div id="tender-pdf-upload">
            <p><strong style="color: #d63638;"><?php _e('* PDF Upload Required', 'tender-notices'); ?></strong></p>
            <p class="description"><?php _e('A PDF document is mandatory for all tender notices.', 'tender-notices'); ?></p>
            <input type="hidden" id="tender_pdf_id" name="tender_pdf_id" value="<?php echo esc_attr($pdf_id); ?>" />
            <div id="tender-pdf-preview">
                <?php if ($pdf_url): ?>
                    <p><strong><?php _e('Current PDF:', 'tender-notices'); ?></strong></p>
                    <p><a href="<?php echo esc_url($pdf_url); ?>" target="_blank"><?php _e('View PDF', 'tender-notices'); ?></a></p>
                    <button type="button" id="remove-pdf" class="button"><?php _e('Remove PDF', 'tender-notices'); ?></button>
                <?php else: ?>
                    <p style="color: #d63638;"><strong><?php _e('No PDF uploaded - Required!', 'tender-notices'); ?></strong></p>
                <?php endif; ?>
            </div>
            <button type="button" id="upload-pdf" class="button button-primary"><?php _e('Upload PDF', 'tender-notices'); ?></button>
        </div>
        <?php
    }
    
    /**
     * Save meta boxes
     */
    public function save_meta_boxes($post_id) {
        if (!isset($_POST['tender_notice_meta_box_nonce']) || !wp_verify_nonce($_POST['tender_notice_meta_box_nonce'], 'tender_notice_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $fields = array(
            'tender_closing_date',
            'tender_number',
            'pre_bid_meeting_date',
            'pre_bid_meeting_mandatory',
            'pre_bid_meeting_online',
            'site_visit_date',
            'site_visit_mandatory',
            'tender_pdf_id'
        );
        
        // Validate PDF upload is mandatory
        $pdf_id = isset($_POST['tender_pdf_id']) ? intval($_POST['tender_pdf_id']) : 0;
        if (!$pdf_id) {
            add_filter('redirect_post_location', function($location) {
                return add_query_arg('tender_notices_message', 'pdf_required', $location);
            });
            return;
        }
        
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
    }
    
    /**
     * Add custom columns to admin list
     */
    public function add_custom_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['tender_category'] = __('Category', 'tender-notices');
        $new_columns['tender_status'] = __('Status', 'tender-notices');
        $new_columns['tender_number'] = __('Tender Number', 'tender-notices');
        $new_columns['closing_date'] = __('Closing Date', 'tender-notices');
        $new_columns['pre_bid_meeting'] = __('Pre-Bid Meeting', 'tender-notices');
        $new_columns['site_visit'] = __('Site Visit', 'tender-notices');
        $new_columns['pdf'] = __('PDF', 'tender-notices');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Custom column content
     */
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'tender_category':
                $terms = get_the_terms($post_id, 'tender_category');
                if ($terms && !is_wp_error($terms)) {
                    $term_names = array();
                    foreach ($terms as $term) {
                        $term_names[] = $term->name;
                    }
                    echo implode(', ', $term_names);
                } else {
                    echo '—';
                }
                break;
                
            case 'tender_status':
                $terms = get_the_terms($post_id, 'tender_status');
                if ($terms && !is_wp_error($terms)) {
                    $term_names = array();
                    foreach ($terms as $term) {
                        $term_names[] = $term->name;
                    }
                    echo implode(', ', $term_names);
                } else {
                    echo '—';
                }
                break;
                
            case 'tender_number':
                $tender_number = get_post_meta($post_id, '_tender_number', true);
                echo $tender_number ? esc_html($tender_number) : '—';
                break;
                
            case 'closing_date':
                $closing_date = get_post_meta($post_id, '_tender_closing_date', true);
                if ($closing_date) {
                    $date = date('M j, Y', strtotime($closing_date));
                    $now = new DateTime();
                    $closing = new DateTime($closing_date);
                    if ($closing < $now) {
                        echo '<span style="color: #d63638;">' . $date . '</span>';
                    } else {
                        echo $date;
                    }
                } else {
                    echo '—';
                }
                break;
                
            case 'pre_bid_meeting':
                $meeting_date = get_post_meta($post_id, '_pre_bid_meeting_date', true);
                $mandatory = get_post_meta($post_id, '_pre_bid_meeting_mandatory', true);
                $online = get_post_meta($post_id, '_pre_bid_meeting_online', true);
                
                if ($meeting_date) {
                    $date = date('M j, Y H:i', strtotime($meeting_date));
                    $badges = array();
                    if ($mandatory) {
                        $badges[] = '<span style="color: #d63638; font-weight: bold;">Mandatory</span>';
                    }
                    if ($online) {
                        $badges[] = '<span style="color: #0073aa;">Online</span>';
                    }
                    echo $date;
                    if (!empty($badges)) {
                        echo '<br><small>' . implode(' | ', $badges) . '</small>';
                    }
                } else {
                    echo '—';
                }
                break;
                
            case 'site_visit':
                $visit_date = get_post_meta($post_id, '_site_visit_date', true);
                $mandatory = get_post_meta($post_id, '_site_visit_mandatory', true);
                
                if ($visit_date) {
                    $date = date('M j, Y H:i', strtotime($visit_date));
                    if ($mandatory) {
                        echo $date . '<br><small><span style="color: #d63638; font-weight: bold;">Mandatory</span></small>';
                    } else {
                        echo $date;
                    }
                } else {
                    echo '—';
                }
                break;
                
            case 'pdf':
                $pdf_id = get_post_meta($post_id, '_tender_pdf_id', true);
                if ($pdf_id) {
                    $pdf_url = wp_get_attachment_url($pdf_id);
                    echo '<a href="' . esc_url($pdf_url) . '" target="_blank" class="button button-small">' . __('View', 'tender-notices') . '</a>';
                } else {
                    echo '—';
                }
                break;
        }
    }
}
