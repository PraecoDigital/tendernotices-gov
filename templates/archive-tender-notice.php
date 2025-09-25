<?php
/**
 * Archive Tender Notice Template
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header(); ?>

<div class="tender-notices-archive">
    <header class="page-header">
        <h1 class="page-title"><?php _e('Tender Notices', 'tender-notices'); ?></h1>
        <?php if (get_the_archive_description()): ?>
            <div class="archive-description">
                <?php the_archive_description(); ?>
            </div>
        <?php endif; ?>
    </header>
    
    <?php if (have_posts()): ?>
        <div class="tender-notices-filters">
            <form method="get" class="tender-filters-form">
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'tender_category',
                    'hide_empty' => true,
                ));
                
                if ($categories && !is_wp_error($categories)):
                ?>
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
                
                <?php
                $statuses = get_terms(array(
                    'taxonomy' => 'tender_status',
                    'hide_empty' => true,
                ));
                
                if ($statuses && !is_wp_error($statuses)):
                ?>
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
        
        <div class="tender-notices-container tender-notices-two-column">
            <?php while (have_posts()): the_post(); ?>
                <?php
                $data = TenderNotices_Frontend::get_tender_notice_data(get_the_ID());
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
                            <a href="<?php the_permalink(); ?>">
                                <?php the_title(); ?>
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
                        
                        <div class="tender-notice-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
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
                
            <?php endwhile; ?>
        </div>
        
        <?php
        // Pagination
        the_posts_pagination(array(
            'prev_text' => __('Previous', 'tender-notices'),
            'next_text' => __('Next', 'tender-notices'),
        ));
        ?>
        
    <?php else: ?>
        <div class="tender-notices-empty">
            <h2><?php _e('No Tender Notices Found', 'tender-notices'); ?></h2>
            <p><?php _e('There are currently no tender notices available.', 'tender-notices'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
