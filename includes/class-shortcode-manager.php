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
        add_action('wp_ajax_commerce_yar_load_pricing', array($this, 'ajax_load_pricing'));
        add_action('wp_ajax_nopriv_commerce_yar_load_pricing', array($this, 'ajax_load_pricing'));
    }

    /**
     * Initialize shortcodes
     */
    public function init() {
        add_shortcode('commerce_yar_pricing_table', array($this, 'render_pricing_table'));
    }
    /**
     * Handle AJAX request to load pricing table
     */
    public function ajax_load_pricing() {
        if (!isset($_POST['type']) || !in_array($_POST['type'], array('monthly', 'yearly'))) {
            wp_send_json_error('Invalid pricing type');
        }

        $atts = array(
            'type' => sanitize_text_field($_POST['type']),
            'theme' => isset($_POST['theme']) ? sanitize_text_field($_POST['theme']) : 'default'
        );

        $html = $this->render_pricing_table($atts);
        wp_send_json_success(array('html' => $html));
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
            'type' => 'monthly'
        ), $atts);

        // Enqueue required assets
        wp_enqueue_style('commerce-yar-style');
        wp_enqueue_script('commerce-yar-script');

        // Add AJAX URL for dynamic loading
        wp_localize_script('commerce-yar-script', 'commerceYarAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('commerce_yar_pricing_nonce')
        ));

        // Start output buffering
        ob_start();

        // Include the template file
        $template_path = COMMERCE_YAR_PLUGIN_DIR . 'templates/pricing-table.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            return '<p>Error: Pricing table template not found!</p>';
        }

        // Return the buffered content
        return ob_get_clean();
    }
} 