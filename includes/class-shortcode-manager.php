<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ShortcodeManager
 * 
 * Handles all shortcode registrations and implementations for the Commerce Yar Pricing Table plugin.
 */
class Commerce_Yar_Shortcode_Manager {
    
    /**
     * Initialize the shortcode manager
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize shortcodes
     */
    public function init() {
        add_shortcode('commerce_yar_pricing_table', array($this, 'render_pricing_table'));
    }

    /**
     * Render the pricing table
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function render_pricing_table($atts = array()) {
        // Merge default attributes with user provided ones
        $atts = shortcode_atts(array(
            'theme' => 'default',
            'columns' => '3'
        ), $atts);

        // Start output buffering
        ob_start();

        // Include the template file
        $template_path = plugin_dir_path(dirname(__FILE__)) . 'templates/pricing-table-template.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            return '<p>Error: Pricing table template not found!</p>';
        }

        // Return the buffered content
        return ob_get_clean();
    }
} 