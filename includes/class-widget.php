<?php
/**
 * Tender Notices Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class TenderNotices_Widget extends WP_Widget {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'tender_notices_widget',
            __('Tender Notices Widget', 'tender-notices'),
            array(
                'description' => __('Display recent tender notices in a widget.', 'tender-notices'),
                'classname' => 'tender-notices-widget',
            )
        );
    }
    
    /**
     * Widget output
     */
    public function widget($args, $instance) {
        $title = !empty($instance['title']) ? $instance['title'] : __('Recent Tender Notices', 'tender-notices');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_excerpt = !empty($instance['show_excerpt']);
        $show_closing_date = !empty($instance['show_closing_date']);
        
        echo $args['before_widget'];
        
        if ($title) {
            echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
        }
        
        // Build query args
        $query_args = array(
            'post_type' => 'tender_notice',
            'posts_per_page' => $number,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        // Taxonomy filters removed
        
        $query = new WP_Query($query_args);
        
        if ($query->have_posts()) {
            echo '<div class="tender-notices-widget-list">';
            
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_widget_item(get_the_ID(), $instance);
            }
            
            echo '</div>';
            
            // Show "View All" link
            $archive_url = get_post_type_archive_link('tender_notice');
            if ($archive_url) {
                echo '<div class="tender-notices-widget-footer">';
                echo '<a href="' . esc_url($archive_url) . '" class="tender-notices-widget-link">';
                echo __('View All Tender Notices', 'tender-notices');
                echo '</a>';
                echo '</div>';
            }
        } else {
            echo '<p>' . __('No tender notices found.', 'tender-notices') . '</p>';
        }
        
        wp_reset_postdata();
        echo $args['after_widget'];
    }
    
    /**
     * Render widget item
     */
    private function render_widget_item($post_id, $instance) {
        $data = TenderNotices_Frontend::get_tender_notice_data($post_id);
        $is_expired = TenderNotices_Frontend::is_tender_expired($data['closing_date']);
        $status_class = TenderNotices_Frontend::get_tender_status_class($data['closing_date']);
        
        ?>
        <div class="tender-notices-widget-item <?php echo esc_attr($status_class); ?>">
            <h4 class="tender-notices-widget-title">
                <a href="<?php echo esc_url(get_permalink($post_id)); ?>">
                    <?php echo esc_html($data['title']); ?>
                </a>
            </h4>
            
            <?php if (!empty($instance['show_closing_date']) && $data['closing_date']): ?>
                <div class="tender-notices-widget-closing-date">
                    <strong><?php _e('Closing Date:', 'tender-notices'); ?></strong> 
                    <span class="<?php echo $is_expired ? 'tender-expired' : ''; ?>">
                        <?php echo esc_html(TenderNotices_Frontend::format_date($data['closing_date'])); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($instance['show_excerpt']) && $data['excerpt']): ?>
                <div class="tender-notices-widget-excerpt">
                    <?php echo wp_trim_words($data['excerpt'], 20, '...'); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($data['pdf_url']): ?>
                <div class="tender-notices-widget-actions">
                    <a href="<?php echo esc_url($data['pdf_url']); ?>" class="tender-notices-widget-download" target="_blank">
                        <?php _e('Download PDF', 'tender-notices'); ?>
                    </a>
                </div>
                
                <div class="tender-notices-widget-download-section">
                    <a href="<?php echo esc_url($data['pdf_url']); ?>" class="tender-notices-widget-download-btn" target="_blank" download>
                        <span class="download-icon">📄</span>
                        <?php _e('Download Document', 'tender-notices'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Widget form
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        $show_excerpt = !empty($instance['show_excerpt']);
        $show_closing_date = !empty($instance['show_closing_date']);
        
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'tender-notices'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of notices to show:', 'tender-notices'); ?></label>
            <input class="tiny-text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_excerpt); ?> id="<?php echo $this->get_field_id('show_excerpt'); ?>" name="<?php echo $this->get_field_name('show_excerpt'); ?>">
            <label for="<?php echo $this->get_field_id('show_excerpt'); ?>"><?php _e('Show excerpt', 'tender-notices'); ?></label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_closing_date); ?> id="<?php echo $this->get_field_id('show_closing_date'); ?>" name="<?php echo $this->get_field_name('show_closing_date'); ?>">
            <label for="<?php echo $this->get_field_id('show_closing_date'); ?>"><?php _e('Show closing date', 'tender-notices'); ?></label>
        </p>
        <?php
    }
    
    /**
     * Update widget settings
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        $instance['show_excerpt'] = !empty($new_instance['show_excerpt']);
        $instance['show_closing_date'] = !empty($new_instance['show_closing_date']);
        
        return $instance;
    }
}
