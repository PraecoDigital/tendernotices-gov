<?php
/**
 * Plugin Name: TenderNotices
 * Plugin URI: https://github.com/PraecoDigital/tendernotices-gov
 * Description: A comprehensive solution for displaying tender notices and procurement opportunities on WordPress websites.
 * Version: 1.0.0
 * Author: Jarod Mottley
 * Author URI: https://www.linkedin.com/in/jmottley/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: tender-notices
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TENDER_NOTICES_VERSION', '1.0.0');
define('TENDER_NOTICES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TENDER_NOTICES_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TENDER_NOTICES_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main TenderNotices Plugin Class
 */
class TenderNotices {
    
    /**
     * Single instance of the plugin
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Ensure post type is registered early
        add_action('init', array($this, 'register_post_type_early'), 5);
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once TENDER_NOTICES_PLUGIN_PATH . 'includes/class-post-type.php';
        require_once TENDER_NOTICES_PLUGIN_PATH . 'includes/class-admin.php';
        require_once TENDER_NOTICES_PLUGIN_PATH . 'includes/class-frontend.php';
        require_once TENDER_NOTICES_PLUGIN_PATH . 'includes/class-shortcode.php';
        require_once TENDER_NOTICES_PLUGIN_PATH . 'includes/class-widget.php';
        require_once TENDER_NOTICES_PLUGIN_PATH . 'includes/class-pdf-manager.php';
    }
    
    /**
     * Register post type early
     */
    public function register_post_type_early() {
        // Load text domain
        load_plugin_textdomain('tender-notices', false, dirname(plugin_basename(__FILE__)) . '/languages');
        
        // Initialize post type first
        new TenderNotices_Post_Type();
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Initialize remaining components
        new TenderNotices_Admin();
        new TenderNotices_Frontend();
        new TenderNotices_Shortcode();
        new TenderNotices_PDF_Manager();
        
        // Register widget
        add_action('widgets_init', array($this, 'register_widget'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'tender-notices-style',
            TENDER_NOTICES_PLUGIN_URL . 'assets/css/tender-notices.css',
            array(),
            TENDER_NOTICES_VERSION
        );
        
        wp_enqueue_script(
            'tender-notices-script',
            TENDER_NOTICES_PLUGIN_URL . 'assets/js/tender-notices.js',
            array('jquery'),
            TENDER_NOTICES_VERSION,
            true
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        if (strpos($hook, 'tender-notices') !== false || $hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_style(
                'tender-notices-admin-style',
                TENDER_NOTICES_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                TENDER_NOTICES_VERSION
            );
            
            wp_enqueue_script(
                'tender-notices-admin-script',
                TENDER_NOTICES_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                TENDER_NOTICES_VERSION,
                true
            );
        }
    }
    
    /**
     * Register widget
     */
    public function register_widget() {
        register_widget('TenderNotices_Widget');
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create custom post type
        $this->init();
        flush_rewrite_rules();
        
        // Create upload directory
        $upload_dir = wp_upload_dir();
        $tender_dir = $upload_dir['basedir'] . '/tender-notices';
        if (!file_exists($tender_dir)) {
            wp_mkdir_p($tender_dir);
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        flush_rewrite_rules();
    }
}

// Initialize the plugin
function tender_notices() {
    return TenderNotices::get_instance();
}

// Start the plugin
tender_notices();
