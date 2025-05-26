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

    // Add AJAX URL and nonce for payment processing
    wp_localize_script('commerce-yar-script', 'commerceYarAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('commerce_yar_pricing_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'commerce_yar_enqueue_assets');

// Load required files
require_once COMMERCE_YAR_PLUGIN_DIR . 'includes/class-shortcode-manager.php';
require_once COMMERCE_YAR_PLUGIN_DIR . 'includes/class-payment-handler.php';

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

// Create subscription status page on plugin activation
function commerce_yar_create_subscription_status_page() {
    // Check if the page already exists
    $page = get_page_by_path('subscription-status');
    
    if (!$page) {
        $page_data = array(
            'post_title'    => 'وضعیت اشتراک',
            'post_content'  => '[commerce_yar_subscription_status]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'subscription-status'
        );
        
        wp_insert_post($page_data);
    }
}
register_activation_hook(__FILE__, 'commerce_yar_create_subscription_status_page');

// Register subscription status shortcode
function commerce_yar_subscription_status_shortcode() {
    ob_start();
    include COMMERCE_YAR_PLUGIN_DIR . 'templates/subscription-status.php';
    return ob_get_clean();
}
add_shortcode('commerce_yar_subscription_status', 'commerce_yar_subscription_status_shortcode');

// Add subscription status page to menu
function commerce_yar_add_subscription_status_menu() {
    add_submenu_page(
        'commerce-yar-pricing',
        'وضعیت اشتراک‌ها',
        'وضعیت اشتراک‌ها',
        'manage_options',
        'commerce-yar-subscriptions',
        'commerce_yar_render_subscription_status_page'
    );
}
add_action('admin_menu', 'commerce_yar_add_subscription_status_menu');

// Render subscription status admin page
function commerce_yar_render_subscription_status_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'commerce_yar_subscriptions';
    
    $subscriptions = $wpdb->get_results("
        SELECT s.*, u.display_name as user_name 
        FROM {$table_name} s 
        LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID 
        ORDER BY s.created_at DESC
    ");
    
    ?>
    <div class="wrap">
        <h1>وضعیت اشتراک‌ها</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>کاربر</th>
                    <th>طرح</th>
                    <th>نوع</th>
                    <th>قیمت</th>
                    <th>وضعیت پرداخت</th>
                    <th>تاریخ شروع</th>
                    <th>تاریخ پایان</th>
                    <th>توکن</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $sub): ?>
                    <tr>
                        <td><?php echo esc_html($sub->user_name); ?></td>
                        <td><?php echo esc_html($sub->plan_title); ?></td>
                        <td><?php echo $sub->plan_type === 'monthly' ? 'ماهانه' : 'سالانه'; ?></td>
                        <td><?php echo number_format($sub->price) . ' تومان'; ?></td>
                        <td><?php echo $sub->payment_status === 'completed' ? 'موفق' : 'ناموفق'; ?></td>
                        <td><?php echo wp_date('Y/m/d H:i', strtotime($sub->created_at)); ?></td>
                        <td><?php echo wp_date('Y/m/d H:i', strtotime($sub->expires_at)); ?></td>
                        <td>
                            <code style="font-size: 12px;"><?php echo esc_html($sub->token); ?></code>
                            <button class="button button-small copy-token" data-token="<?php echo esc_attr($sub->token); ?>">کپی</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.copy-token').on('click', function() {
            const token = $(this).data('token');
            const tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(token).select();
            document.execCommand('copy');
            tempInput.remove();
            
            const $button = $(this);
            $button.text('کپی شد!');
            setTimeout(() => {
                $button.text('کپی');
            }, 2000);
        });
    });
    </script>
    <?php
}
