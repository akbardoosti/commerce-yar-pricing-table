<?php
/*
Plugin Name: Commerce Yar Pricing Table
Description: A beautiful and responsive pricing table plugin with multiple pricing periods
Version: 1.0.0
Author: Commerce Yar
*/

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('COMMERCE_YAR_PRICING_VERSION', '1.0.0');
define('COMMERCE_YAR_PRICING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('COMMERCE_YAR_PRICING_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once COMMERCE_YAR_PRICING_PLUGIN_DIR . 'includes/class-commerce-yar-pricing-table-activator.php';
require_once COMMERCE_YAR_PRICING_PLUGIN_DIR . 'includes/class-commerce-yar-pricing-table-ajax.php';
require_once COMMERCE_YAR_PRICING_PLUGIN_DIR . 'includes/class-payment-handler.php';

// Include admin files if in admin area
if (is_admin()) {
    require_once COMMERCE_YAR_PRICING_PLUGIN_DIR . 'admin/admin-settings.php';
}

// Register activation hook
register_activation_hook(__FILE__, array('Commerce_Yar_Pricing_Table_Activator', 'activate'));

class Commerce_Yar_Pricing_Table {
    private static $instance = null;
    private $ajax_handler;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Initialize AJAX handler
        $this->ajax_handler = new Commerce_Yar_Pricing_Table_Ajax();
    }

    public function init() {
        add_shortcode('commerce_yar_pricing', array($this, 'render_pricing_table'));
    }

    public function enqueue_scripts() {
        // Localize the script with new data
        wp_localize_script('jquery', 'commerceYarAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('commerce_yar_nonce')
        ));
    }

    public function render_pricing_table($atts) {
        $atts = shortcode_atts(array(
            'type' => 'monthly',
            'theme' => 'default'
        ), $atts);

        ob_start();
        include COMMERCE_YAR_PRICING_PLUGIN_DIR . 'templates/pricing-table.php';
        return ob_get_clean();
    }
}

// Initialize the plugin
function commerce_yar_pricing_init() {
    return Commerce_Yar_Pricing_Table::get_instance();
}

commerce_yar_pricing_init();
