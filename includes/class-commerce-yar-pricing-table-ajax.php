<?php

class Commerce_Yar_Pricing_Table_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_commerce_yar_get_pricing', array($this, 'get_pricing'));
        add_action('wp_ajax_nopriv_commerce_yar_get_pricing', array($this, 'get_pricing'));
        add_action('wp_ajax_commerce_yar_save_pricing', array($this, 'save_pricing'));
    }

    public function get_pricing() {
        check_ajax_referer('commerce_yar_nonce', 'nonce');
        
        $type = sanitize_text_field($_POST['type']);
        
        // Try to get cached data
        $cache_key = 'commerce_yar_pricing_data_' . $type;
        $pricing_data = wp_cache_get($cache_key);
        
        if (false === $pricing_data) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'commerce_yar_token';
            $pricing_data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table_name WHERE pricing_type = %s",
                    $type
                ),
                ARRAY_A
            );
            
            // Cache the results for 1 hour
            wp_cache_set($cache_key, $pricing_data, '', 3600);
        }
        
        ob_start();
        foreach ($pricing_data as $price) : ?>
            <div class="swiper-slide">
                <div class="pricing-column">
                    <div class="pricing-header">
                        <h3><?php echo esc_html($price['title']); ?></h3>
                        <div class="price">
                            <?php echo number_format_i18n($price['price']); ?>
                            <span class="currency">تومان</span>
                            
                        </div>
                    </div>
                    <div class="pricing-features">
                        <ul>
                            <?php
                            $features = explode("\n", $price['features']);
                            foreach ($features as $feature) :
                                if (!empty(trim($feature))) :
                            ?>
                                <li><?php echo esc_html(trim($feature)); ?></li>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </ul>
                    </div>
                    <div class="pricing-footer">
                        <a href="<?php echo esc_url($price['button_link']); ?>" 
                           class="pricing-button"
                           data-price-code="<?php echo esc_attr($price['price_code']); ?>">
                            <?php echo esc_html($price['button_text']); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach;
        
        $html = ob_get_clean();
        
        wp_send_json_success(array(
            'html' => $html
        ));
    }
    
    public function save_pricing() {
        check_ajax_referer('commerce_yar_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        $pricing_data = json_decode(stripslashes($_POST['pricing_data']), true);
        
        if (!is_array($pricing_data)) {
            wp_send_json_error(array('message' => 'Invalid data format'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'commerce_yar_token';
        
        // Begin transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Delete all existing prices
            $wpdb->query("DELETE FROM $table_name");
            
            // Insert new prices
            foreach ($pricing_data as $price) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'pricing_type' => sanitize_text_field($price['pricing_type']),
                        'title' => sanitize_text_field($price['title']),
                        'price' => floatval($price['price']),
                        'features' => sanitize_textarea_field($price['features']),
                        'button_text' => sanitize_text_field($price['button_text']),
                        'button_link' => esc_url_raw($price['button_link']),
                        'price_code' => sanitize_text_field($price['price_code'])
                    ),
                    array('%s', '%s', '%f', '%s', '%s', '%s', '%s')
                );
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Clear all pricing caches
            wp_cache_delete('commerce_yar_pricing_data_monthly');
            wp_cache_delete('commerce_yar_pricing_data_quarterly');
            wp_cache_delete('commerce_yar_pricing_data_biannual');
            wp_cache_delete('commerce_yar_pricing_data_yearly');
            
            wp_send_json_success(array('message' => 'Pricing data updated successfully'));
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => 'Error updating pricing data'));
        }
    }
} 