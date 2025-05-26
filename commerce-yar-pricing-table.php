<?php
/*
Plugin Name: Commerce Yar Pricing Table
Plugin URI: https://yourdomain.com/plugins/commerce-yar-pricing-table
Description: Adds a customizable pricing table to WooCommerce for both admin and clients.
Version: 1.0.0
Author: Your Name
Author URI: https://yourdomain.com
Text Domain: commerce-yar-pricing-table
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('COMMERCE_YAR_VERSION', '1.0.0');
define('COMMERCE_YAR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COMMERCE_YAR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Enqueue Scripts & Styles
function commerce_yar_enqueue_assets() {
    // Enqueue styles
    wp_enqueue_style('commerce-yar-style', plugins_url('css/style.css', __FILE__));
    
    // Enqueue Modernizr first
    wp_enqueue_script(
        'modernizr', 
        plugins_url('js/modernizr.min.js', __FILE__),
        array(),
        COMMERCE_YAR_VERSION,
        false // Load in header
    );
    
    // Enqueue main script
    wp_enqueue_script(
        'commerce-yar-script',
        plugins_url('js/pricing-table.js', __FILE__),
        array('jquery', 'modernizr'),
        COMMERCE_YAR_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'commerce_yar_enqueue_assets');

// Load required files
require_once COMMERCE_YAR_PLUGIN_DIR . 'includes/class-shortcode-manager.php';

// Initialize shortcode manager
function commerce_yar_init() {
    new Commerce_Yar_Shortcode_Manager();
}
add_action('plugins_loaded', 'commerce_yar_init');

// Admin Section
if (is_admin()) {
    require_once COMMERCE_YAR_PLUGIN_DIR . 'admin/admin-settings.php';
}

// Client Display
require_once COMMERCE_YAR_PLUGIN_DIR . 'public/display-table.php';
