<?php
/**
 * Single Tender Notice Template
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="tender-notice-single">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $data = TenderNotices_Frontend::get_tender_notice_data(get_the_ID());
        $is_expired = TenderNotices_Frontend::is_tender_expired($data['closing_date']);
        $status_class = TenderNotices_Frontend::get_tender_status_class($data['closing_date']);
        $days_remaining = TenderNotices_Frontend::get_days_remaining($data['closing_date']);
        ?>
        
        <header class="entry-header">
            <h1 class="entry-title"><?php the_title(); ?></h1>
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
                        <?php if ($days_remaining !== null && !$is_expired): ?>
                            <span class="days-remaining">
                                (<?php printf(_n('%d day remaining', '%d days remaining', $days_remaining, 'tender-notices'), $days_remaining); ?>)
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($data['tender_value']): ?>
                    <div class="tender-notice-meta-item">
                        <div class="tender-notice-meta-label"><?php _e('Value', 'tender-notices'); ?></div>
                        <div class="tender-notice-meta-value"><?php echo esc_html($data['tender_value']); ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if ($data['categories'] && !is_wp_error($data['categories'])): ?>
                    <div class="tender-notice-meta-item">
                        <div class="tender-notice-meta-label"><?php _e('Category', 'tender-notices'); ?></div>
                        <div class="tender-notice-meta-value">
                            <?php
                            $category_names = array();
                            foreach ($data['categories'] as $category) {
                                $category_names[] = $category->name;
                            }
                            echo esc_html(implode(', ', $category_names));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($data['status'] && !is_wp_error($data['status'])): ?>
                    <div class="tender-notice-meta-item">
                        <div class="tender-notice-meta-label"><?php _e('Status', 'tender-notices'); ?></div>
                        <div class="tender-notice-meta-value">
                            <?php
                            $status_names = array();
                            foreach ($data['status'] as $status) {
                                $status_names[] = $status->name;
                            }
                            echo esc_html(implode(', ', $status_names));
                            ?>
                        </div>
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
            <?php the_content(); ?>
        </div>
        
        <?php if ($data['pdf_url']): ?>
            <div class="tender-notice-actions">
                <a href="<?php echo esc_url($data['pdf_url']); ?>" class="btn-primary" target="_blank" download>
                    <?php _e('Download PDF', 'tender-notices'); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <div class="tender-notice-navigation">
            <div class="nav-previous">
                <?php
                $prev_post = get_previous_post(true, '', 'tender_category');
                if ($prev_post) {
                    echo '<a href="' . get_permalink($prev_post->ID) . '">';
                    echo '<span class="nav-subtitle">' . __('Previous:', 'tender-notices') . '</span>';
                    echo '<span class="nav-title">' . get_the_title($prev_post->ID) . '</span>';
                    echo '</a>';
                }
                ?>
            </div>
            
            <div class="nav-next">
                <?php
                $next_post = get_next_post(true, '', 'tender_category');
                if ($next_post) {
                    echo '<a href="' . get_permalink($next_post->ID) . '">';
                    echo '<span class="nav-subtitle">' . __('Next:', 'tender-notices') . '</span>';
                    echo '<span class="nav-title">' . get_the_title($next_post->ID) . '</span>';
                    echo '</a>';
                }
                ?>
            </div>
        </div>
        
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
