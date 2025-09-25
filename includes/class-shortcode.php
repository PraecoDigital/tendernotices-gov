<?php
/**
 * Tender Notices Shortcode
 */

if (!defined('ABSPATH')) {
    exit;
}

class TenderNotices_Shortcode {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_shortcode('tender_notices', array($this, 'tender_notices_shortcode'));
        add_shortcode('tender_notice', array($this, 'single_tender_notice_shortcode'));
    }
    
    /**
     * Main tender notices shortcode
     */
    public function tender_notices_shortcode($atts) {
        $atts = shortcode_atts(array(
            'columns' => 'single',
            'posts_per_page' => 10,
            'category' => '',
            'status' => '',
            'show_excerpt' => true,
            'excerpt_length' => 25,
            'orderby' => 'date',
            'order' => 'DESC',
            'show_pagination' => true,
            'show_filters' => false,
        ), $atts, 'tender_notices');
        
        // Convert string boolean values
        $atts['show_excerpt'] = filter_var($atts['show_excerpt'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_pagination'] = filter_var($atts['show_pagination'], FILTER_VALIDATE_BOOLEAN);
        $atts['show_filters'] = filter_var($atts['show_filters'], FILTER_VALIDATE_BOOLEAN);
        
        // Get options from settings
        $options = get_option('tender_notices_options', array());
        $default_layout = isset($options['default_layout']) ? $options['default_layout'] : 'single';
        $default_posts_per_page = isset($options['posts_per_page']) ? $options['posts_per_page'] : 10;
        $default_show_excerpt = isset($options['show_excerpt']) ? $options['show_excerpt'] : true;
        $default_excerpt_length = isset($options['excerpt_length']) ? $options['excerpt_length'] : 25;
        
        // Use defaults if not specified in shortcode
        if ($atts['columns'] === 'single') {
            $atts['columns'] = $default_layout;
        }
        if ($atts['posts_per_page'] == 10) {
            $atts['posts_per_page'] = $default_posts_per_page;
        }
        if ($atts['show_excerpt'] === true) {
            $atts['show_excerpt'] = $default_show_excerpt;
        }
        if ($atts['excerpt_length'] == 25) {
            $atts['excerpt_length'] = $default_excerpt_length;
        }
        
        // Build query args
        $query_args = array(
            'post_type' => 'tender_notice',
            'posts_per_page' => intval($atts['posts_per_page']),
            'post_status' => 'publish',
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );
        
        // Add taxonomy filters
        if (!empty($atts['category'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'tender_category',
                'field' => 'slug',
                'terms' => $atts['category'],
            );
        }
        
        if (!empty($atts['status'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'tender_status',
                'field' => 'slug',
                'terms' => $atts['status'],
            );
        }
        
        // Handle pagination
        if ($atts['show_pagination']) {
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $query_args['paged'] = $paged;
        }
        
        $query = new WP_Query($query_args);
        
        if (!$query->have_posts()) {
            return '<p>' . __('No tender notices found.', 'tender-notices') . '</p>';
        }
        
        ob_start();
        
        // Add filters if enabled
        if ($atts['show_filters']) {
            $this->render_filters();
        }
        
        // Render tender notices
        $this->render_tender_notices($query, $atts);
        
        // Add pagination if enabled
        if ($atts['show_pagination'] && $query->max_num_pages > 1) {
            $this->render_pagination($query);
        }
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Single tender notice shortcode
     */
    public function single_tender_notice_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
            'slug' => '',
        ), $atts, 'tender_notice');
        
        if (empty($atts['id']) && empty($atts['slug'])) {
            return '<p>' . __('Please specify a tender notice ID or slug.', 'tender-notices') . '</p>';
        }
        
        $args = array(
            'post_type' => 'tender_notice',
            'post_status' => 'publish',
            'posts_per_page' => 1,
        );
        
        if (!empty($atts['id'])) {
            $args['p'] = intval($atts['id']);
        } else {
            $args['name'] = $atts['slug'];
        }
        
        $query = new WP_Query($args);
        
        if (!$query->have_posts()) {
            return '<p>' . __('Tender notice not found.', 'tender-notices') . '</p>';
        }
        
        ob_start();
        
        while ($query->have_posts()) {
            $query->the_post();
            $this->render_single_tender_notice(get_the_ID());
        }
        
        wp_reset_postdata();
        
        return ob_get_clean();
    }
    
    /**
     * Render tender notices
     */
    private function render_tender_notices($query, $atts) {
        $columns = $atts['columns'];
        $columns_class = $columns === 'two' ? 'tender-notices-two-column' : 'tender-notices-single-column';
        
        echo '<div class="tender-notices-container ' . esc_attr($columns_class) . '">';
        
        while ($query->have_posts()) {
            $query->the_post();
            $this->render_tender_notice_card(get_the_ID(), $atts);
        }
        
        echo '</div>';
    }
    
    /**
     * Render single tender notice card
     */
    private function render_tender_notice_card($post_id, $atts) {
        $data = TenderNotices_Frontend::get_tender_notice_data($post_id);
        $is_expired = TenderNotices_Frontend::is_tender_expired($data['closing_date']);
        $status_class = TenderNotices_Frontend::get_tender_status_class($data['closing_date']);
        $days_remaining = TenderNotices_Frontend::get_days_remaining($data['closing_date']);
        
        ?>
        <div class="tender-notice-card <?php echo esc_attr($status_class); ?>">
            <div class="tender-notice-header">
                <div class="tender-notice-meta">
                    <?php if ($data['categories'] && !is_wp_error($data['categories'])): ?>
                        <span class="badge badge-primary">
                            <?php echo esc_html($data['categories'][0]->name); ?>
                        </span>
                    <?php endif; ?>
                    <span class="tender-notice-date">
                        <?php echo esc_html(TenderNotices_Frontend::format_date($data['issue_date'])); ?>
                    </span>
                </div>
                <h3 class="tender-notice-title">
                    <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                        <?php echo esc_html($data['title']); ?>
                    </a>
                </h3>
                <?php if ($data['tender_reference']): ?>
                    <div class="tender-notice-reference">
                        <strong><?php _e('Reference:', 'tender-notices'); ?></strong> <?php echo esc_html($data['tender_reference']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($data['tender_number']): ?>
                    <div class="tender-notice-number">
                        <strong><?php _e('Tender Number:', 'tender-notices'); ?></strong> <?php echo esc_html($data['tender_number']); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tender-notice-content">
                <?php if ($data['tender_value']): ?>
                    <div class="tender-notice-value">
                        <strong><?php _e('Value:', 'tender-notices'); ?></strong> <?php echo esc_html($data['tender_value']); ?>
                    </div>
                <?php endif; ?>
                
                        <div class="tender-notice-dates">
                            <div class="tender-notice-issue-date">
                                <strong><?php _e('Issue Date:', 'tender-notices'); ?></strong> 
                                <?php echo esc_html(TenderNotices_Frontend::format_date($data['issue_date'])); ?>
                            </div>
                            <div class="tender-notice-closing-date">
                                <strong><?php _e('Closing Date:', 'tender-notices'); ?></strong> 
                                <span class="<?php echo $is_expired ? 'tender-expired' : ''; ?>">
                                    <?php echo esc_html(TenderNotices_Frontend::format_date($data['closing_date'])); ?>
                                </span>
                                <?php if ($days_remaining !== null && !$is_expired): ?>
                                    <span class="days-remaining">
                                        (<?php printf(_n('%d day remaining', '%d days remaining', $days_remaining, 'tender-notices'), $days_remaining); ?>)
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($data['pre_bid_meeting_date']): ?>
                            <div class="tender-notice-pre-bid-meeting">
                                <strong><?php _e('Pre-Bid Meeting:', 'tender-notices'); ?></strong> 
                                <?php echo esc_html(TenderNotices_Frontend::format_date($data['pre_bid_meeting_date'], 'M j, Y H:i')); ?>
                                <?php if ($data['pre_bid_meeting_mandatory']): ?>
                                    <span class="badge badge-mandatory"><?php _e('Mandatory', 'tender-notices'); ?></span>
                                <?php endif; ?>
                                <?php if ($data['pre_bid_meeting_online']): ?>
                                    <span class="badge badge-online"><?php _e('Online', 'tender-notices'); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($data['site_visit_date']): ?>
                            <div class="tender-notice-site-visit">
                                <strong><?php _e('Site Visit:', 'tender-notices'); ?></strong> 
                                <?php echo esc_html(TenderNotices_Frontend::format_date($data['site_visit_date'], 'M j, Y H:i')); ?>
                                <?php if ($data['site_visit_mandatory']): ?>
                                    <span class="badge badge-mandatory"><?php _e('Mandatory', 'tender-notices'); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                
                <?php if ($atts['show_excerpt'] && $data['excerpt']): ?>
                    <div class="tender-notice-excerpt">
                        <?php echo wp_trim_words($data['excerpt'], $atts['excerpt_length'], '...'); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tender-notice-actions">
                <a href="<?php echo esc_url($data['pdf_url']); ?>" class="btn-primary" target="_blank">
                    <?php _e('View Details', 'tender-notices'); ?>
                </a>
            </div>
            
            <?php if ($data['pdf_url']): ?>
                <div class="tender-notice-download-section">
                    <a href="<?php echo esc_url($data['pdf_url']); ?>" class="btn-download" target="_blank" download>
                        <span class="download-icon">📄</span>
                        <?php _e('Download Tender Document', 'tender-notices'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render single tender notice (detailed view)
     */
    private function render_single_tender_notice($post_id) {
        $data = TenderNotices_Frontend::get_tender_notice_data($post_id);
        $is_expired = TenderNotices_Frontend::is_tender_expired($data['closing_date']);
        $status_class = TenderNotices_Frontend::get_tender_status_class($data['closing_date']);
        
        ?>
        <div class="tender-notice-single">
            <header class="entry-header">
                <h1 class="entry-title"><?php echo esc_html($data['title']); ?></h1>
            </header>
            
            <div class="tender-notice-meta">
                <div class="tender-notice-meta-grid">
                    <?php if ($data['tender_reference']): ?>
                        <div class="tender-notice-meta-item">
                            <div class="tender-notice-meta-label"><?php _e('Reference', 'tender-notices'); ?></div>
                            <div class="tender-notice-meta-value"><?php echo esc_html($data['tender_reference']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['tender_number']): ?>
                        <div class="tender-notice-meta-item">
                            <div class="tender-notice-meta-label"><?php _e('Tender Number', 'tender-notices'); ?></div>
                            <div class="tender-notice-meta-value"><?php echo esc_html($data['tender_number']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="tender-notice-meta-item">
                        <div class="tender-notice-meta-label"><?php _e('Issue Date', 'tender-notices'); ?></div>
                        <div class="tender-notice-meta-value"><?php echo esc_html(TenderNotices_Frontend::format_date($data['issue_date'])); ?></div>
                    </div>
                    
                    <div class="tender-notice-meta-item">
                        <div class="tender-notice-meta-label"><?php _e('Closing Date', 'tender-notices'); ?></div>
                        <div class="tender-notice-meta-value <?php echo $is_expired ? 'tender-expired' : ''; ?>">
                            <?php echo esc_html(TenderNotices_Frontend::format_date($data['closing_date'])); ?>
                        </div>
                    </div>
                    
                    <?php if ($data['tender_value']): ?>
                        <div class="tender-notice-meta-item">
                            <div class="tender-notice-meta-label"><?php _e('Value', 'tender-notices'); ?></div>
                            <div class="tender-notice-meta-value"><?php echo esc_html($data['tender_value']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['contact_name']): ?>
                        <div class="tender-notice-meta-item">
                            <div class="tender-notice-meta-label"><?php _e('Contact', 'tender-notices'); ?></div>
                            <div class="tender-notice-meta-value"><?php echo esc_html($data['contact_name']); ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['contact_email']): ?>
                        <div class="tender-notice-meta-item">
                            <div class="tender-notice-meta-label"><?php _e('Email', 'tender-notices'); ?></div>
                            <div class="tender-notice-meta-value">
                                <a href="mailto:<?php echo esc_attr($data['contact_email']); ?>">
                                    <?php echo esc_html($data['contact_email']); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['contact_phone']): ?>
                        <div class="tender-notice-meta-item">
                            <div class="tender-notice-meta-label"><?php _e('Phone', 'tender-notices'); ?></div>
                            <div class="tender-notice-meta-value">
                                <a href="tel:<?php echo esc_attr($data['contact_phone']); ?>">
                                    <?php echo esc_html($data['contact_phone']); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['pre_bid_meeting_date']): ?>
                        <div class="tender-notice-meta-item">
                            <div class="tender-notice-meta-label"><?php _e('Pre-Bid Meeting', 'tender-notices'); ?></div>
                            <div class="tender-notice-meta-value">
                                <?php echo esc_html(TenderNotices_Frontend::format_date($data['pre_bid_meeting_date'], 'M j, Y H:i')); ?>
                                <?php if ($data['pre_bid_meeting_mandatory']): ?>
                                    <span class="badge badge-mandatory"><?php _e('Mandatory', 'tender-notices'); ?></span>
                                <?php endif; ?>
                                <?php if ($data['pre_bid_meeting_online']): ?>
                                    <span class="badge badge-online"><?php _e('Online', 'tender-notices'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($data['site_visit_date']): ?>
                        <div class="tender-notice-meta-item">
                            <div class="tender-notice-meta-label"><?php _e('Site Visit', 'tender-notices'); ?></div>
                            <div class="tender-notice-meta-value">
                                <?php echo esc_html(TenderNotices_Frontend::format_date($data['site_visit_date'], 'M j, Y H:i')); ?>
                                <?php if ($data['site_visit_mandatory']): ?>
                                    <span class="badge badge-mandatory"><?php _e('Mandatory', 'tender-notices'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="entry-content">
                <?php echo wp_kses_post($data['content']); ?>
            </div>
            
            <?php if ($data['pdf_url']): ?>
                <div class="tender-notice-actions">
                    <a href="<?php echo esc_url($data['pdf_url']); ?>" class="btn-primary" target="_blank" download>
                        <?php _e('Download PDF', 'tender-notices'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render filters
     */
    private function render_filters() {
        $categories = get_terms(array(
            'taxonomy' => 'tender_category',
            'hide_empty' => true,
        ));
        
        $statuses = get_terms(array(
            'taxonomy' => 'tender_status',
            'hide_empty' => true,
        ));
        
        ?>
        <div class="tender-notices-filters">
            <form method="get" class="tender-filters-form">
                <?php if ($categories && !is_wp_error($categories)): ?>
                    <select name="tender_category" class="tender-filter-select">
                        <option value=""><?php _e('All Categories', 'tender-notices'); ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->slug); ?>" 
                                    <?php selected(isset($_GET['tender_category']) ? $_GET['tender_category'] : '', $category->slug); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                
                <?php if ($statuses && !is_wp_error($statuses)): ?>
                    <select name="tender_status" class="tender-filter-select">
                        <option value=""><?php _e('All Status', 'tender-notices'); ?></option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo esc_attr($status->slug); ?>" 
                                    <?php selected(isset($_GET['tender_status']) ? $_GET['tender_status'] : '', $status->slug); ?>>
                                <?php echo esc_html($status->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                
                <button type="submit" class="btn-primary"><?php _e('Filter', 'tender-notices'); ?></button>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render pagination
     */
    private function render_pagination($query) {
        $pagination = paginate_links(array(
            'total' => $query->max_num_pages,
            'current' => max(1, get_query_var('paged')),
            'format' => '?paged=%#%',
            'show_all' => false,
            'type' => 'array',
            'end_size' => 2,
            'mid_size' => 1,
            'prev_next' => true,
            'prev_text' => __('Previous', 'tender-notices'),
            'next_text' => __('Next', 'tender-notices'),
        ));
        
        if ($pagination) {
            echo '<div class="tender-notices-pagination">';
            echo '<nav class="pagination-nav">';
            echo implode('', $pagination);
            echo '</nav>';
            echo '</div>';
        }
    }
}
