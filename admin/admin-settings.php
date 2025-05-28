<?php
if (!defined('ABSPATH')) {
    exit;
}

class Commerce_Yar_Admin_Settings {
    private $style_options;
    private $pricing_table;

    public function __construct() {
        global $wpdb;
        $this->pricing_table = $wpdb->prefix . 'commerce_yar_token';
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_commerce_yar_save_pricing', array($this, 'save_pricing_data'));
        add_action('admin_init', array($this, 'init_settings'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'تنظیمات جدول قیمت',
            'جدول قیمت',
            'manage_options',
            'commerce-yar-pricing',
            array($this, 'render_settings_page'),
            'dashicons-grid-view',
            30
        );

        add_submenu_page(
            'commerce-yar-pricing',
            'مدیریت قیمت‌ها',
            'مدیریت قیمت‌ها',
            'manage_options',
            'commerce-yar-pricing',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'commerce-yar-pricing',
            'اشتراک‌ها',
            'اشتراک‌ها',
            'manage_options',
            'commerce-yar-subscriptions',
            array($this, 'render_subscriptions_page')
        );
    }
    public function enqueue_admin_scripts($hook) {
        if ('toplevel_page_commerce-yar-pricing' !== $hook && 'commerce-yar-pricing_page_commerce-yar-subscriptions' !== $hook) {
            return;
        }

        wp_enqueue_style('commerce-yar-varzir-matn-admin', COMMERCE_YAR_PRICING_PLUGIN_URL . 'assets/css/vazir-matn-font-face.css', array(), COMMERCE_YAR_PRICING_VERSION);
        wp_enqueue_style('commerce-yar-admin-style', COMMERCE_YAR_PRICING_PLUGIN_URL . 'assets/css/admin.css', array(), COMMERCE_YAR_PRICING_VERSION);
        wp_enqueue_script('commerce-yar-admin-script', COMMERCE_YAR_PRICING_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), COMMERCE_YAR_PRICING_VERSION, true);
        
        wp_localize_script('commerce-yar-admin-script', 'commerceYarAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('commerce_yar_nonce')
        ));
    }

    public function init_settings() {
        register_setting('commerce_yar_pricing_options', 'commerce_yar_pricing_data');
        register_setting('commerce_yar_style_options', 'commerce_yar_style_data');

        // Pricing Settings Section
        add_settings_section(
            'pricing_section',
            'تنظیمات قیمت‌گذاری',
            array($this, 'pricing_section_callback'),
            'commerce_yar_pricing_options'
        );

        // Style Settings Section
        add_settings_section(
            'style_section',
            'تنظیمات ظاهری',
            array($this, 'style_section_callback'),
            'commerce_yar_style_options'
        );

        $this->style_options = get_option('commerce_yar_style_data', array(
            'border_size' => '1',
            'border_color' => '#e0e0e0',
            'border_radius' => '8',
            'box_shadow' => '0 2px 4px rgba(0,0,0,0.1)',
            'button_color' => '#007bff',
            'font_size' => '16',
            'font_weight' => '400',
            'text_color' => '#333333'
        ));
    }

    /**
     * Callback for pricing section
     */
    public function pricing_section_callback() {
        echo '<p>در این بخش می‌توانید قیمت‌های ماهانه و سالانه را تنظیم کنید. برای هر طرح قیمت‌گذاری می‌توانید عنوان، قیمت، ویژگی‌ها و متن و لینک دکمه را مشخص کنید.</p>';
    }

    /**
     * Callback for style section
     */
    public function style_section_callback() {
        echo '<p>در این بخش می‌توانید ظاهر جدول قیمت‌گذاری را سفارشی کنید. تنظیمات شامل حاشیه، رنگ‌ها، سایه و فونت می‌باشد.</p>';
    }

    public function render_subscriptions_page() {
        global $wpdb;
        $subscriptions_table = $wpdb->prefix . 'commerce_yar_subscriptions';
        
        $subscriptions = $wpdb->get_results("
            SELECT s.*, u.display_name as user_name 
            FROM {$subscriptions_table} s 
            LEFT JOIN {$wpdb->users} u ON s.user_id = u.ID 
            ORDER BY s.created_at DESC
        ");
        
        ?>
        <div class="wrap">
            <h1>مدیریت اشتراک‌ها</h1>
            
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
                        <th>شناسه تراکنش</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscriptions as $sub): ?>
                        <tr>
                            <td><?php echo esc_html($sub->user_name); ?></td>
                            <td><?php echo esc_html($sub->plan_title); ?></td>
                            <td>
                                <?php
                                switch ($sub->plan_type) {
                                    case 'monthly':
                                        echo 'ماهانه';
                                        break;
                                    case 'quarterly':
                                        echo 'سه ماهه';
                                        break;
                                    case 'biannual':
                                        echo 'شش ماهه';
                                        break;
                                    case 'yearly':
                                        echo 'سالانه';
                                        break;
                                }
                                ?>
                            </td>
                            <td><?php echo number_format($sub->price) . ' تومان'; ?></td>
                            <td><?php echo $sub->payment_status === 'completed' ? 'موفق' : 'ناموفق'; ?></td>
                            <td><?php echo wp_date('Y/m/d H:i', strtotime($sub->created_at)); ?></td>
                            <td><?php echo wp_date('Y/m/d H:i', strtotime($sub->expires_at)); ?></td>
                            <td>
                                <code class="subscription-token"><?php echo esc_html($sub->token); ?></code>
                                <button class="button button-small copy-token" data-token="<?php echo esc_attr($sub->token); ?>">کپی</button>
                            </td>
                            <td><?php echo esc_html($sub->transaction_id); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    public function render_settings_page() {
        ?>
        <div class="wrap commerce-yar-admin-container">
            <h2>تنظیمات جدول قیمت‌گذاری</h2>
            
            <div class="nav-tab-wrapper">
                <a href="#pricing-settings" class="nav-tab nav-tab-active">تنظیمات قیمت‌گذاری</a>
                <a href="#style-settings" class="nav-tab">تنظیمات ظاهری</a>
            </div>

            <div id="pricing-settings" class="tab-content">
               
                <?php
                global $wpdb;
                $pricing_data = $wpdb->get_results("SELECT * FROM {$this->pricing_table} ORDER BY pricing_type, price", ARRAY_A);
                ?>
                <div class="wrap">
                    <h1>تنظیمات جدول قیمت</h1>
                    
                    <div class="commerce-yar-admin-container">
                        <form id="commerce-yar-pricing-form">
                            <div class="pricing-periods">
                                <h2>مدیریت قیمت‌ها</h2>
                                <?php
                                $periods = array('monthly' => 'ماهانه', 'quarterly' => 'سه ماهه', 'biannual' => 'شش ماهه', 'yearly' => 'سالانه');
                                foreach ($periods as $period_key => $period_label):
                                    $period_plans = array_filter($pricing_data, function($plan) use ($period_key) {
                                        return $plan['pricing_type'] === $period_key;
                                    });
                                ?>
                                    <div class="pricing-period">
                                        <h3><?php echo esc_html($period_label); ?></h3>
                                        <div class="plans" data-period="<?php echo esc_attr($period_key); ?>">
                                            <?php foreach ($period_plans as $plan): ?>
                                                <div class="plan-item">
                                                    <input type="hidden" name="id[]" value="<?php echo esc_attr($plan['id']); ?>">
                                                    <input type="hidden" name="pricing_type[]" value="<?php echo esc_attr($period_key); ?>">
                                                    
                                                    <div class="form-group">
                                                        <label>عنوان:</label>
                                                        <input type="text" name="title[]" value="<?php echo esc_attr($plan['title']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>قیمت (تومان):</label>
                                                        <input type="number" name="price[]" value="<?php echo esc_attr($plan['price']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>ویژگی‌ها:</label>
                                                        <textarea name="features[]" required><?php echo esc_textarea($plan['features']); ?></textarea>
                                                        <small>هر ویژگی را در یک خط جدید وارد کنید</small>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>متن دکمه:</label>
                                                        <input type="text" name="button_text[]" value="<?php echo esc_attr($plan['button_text']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>لینک دکمه:</label>
                                                        <input type="text" name="button_link[]" value="<?php echo esc_attr($plan['button_link']); ?>" required>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>کد قیمت:</label>
                                                        <input type="text" name="price_code[]" value="<?php echo esc_attr($plan['price_code']); ?>" required>
                                                    </div>
                                                    
                                                    <button type="button" class="button delete-plan">حذف پلن</button>
                                                </div>
                                            <?php endforeach; ?>
                                            <button type="button" class="button add-plan" data-period="<?php echo esc_attr($period_key); ?>">افزودن پلن جدید</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="submit-container">
                                    <button type="submit" class="button button-primary">ذخیره تغییرات</button>
                                    <span class="spinner"></span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div id="style-settings" class="tab-content" style="display: none;">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('commerce_yar_style_options');
                    do_settings_sections('commerce_yar_style_options');
                    ?>
                    <table class="form-table">
                        <tr>
                            <th>اندازه حاشیه (px)</th>
                            <td>
                                <input type="number" name="commerce_yar_style_data[border_size]" 
                                       value="<?php echo esc_attr($this->style_options['border_size']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>رنگ حاشیه</th>
                            <td>
                                <input type="color" name="commerce_yar_style_data[border_color]" 
                                       value="<?php echo esc_attr($this->style_options['border_color']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>گردی گوشه‌ها (px)</th>
                            <td>
                                <input type="number" name="commerce_yar_style_data[border_radius]" 
                                       value="<?php echo esc_attr($this->style_options['border_radius']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>سایه جعبه</th>
                            <td>
                                <input type="text" name="commerce_yar_style_data[box_shadow]" 
                                       value="<?php echo esc_attr($this->style_options['box_shadow']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>رنگ دکمه</th>
                            <td>
                                <input type="color" name="commerce_yar_style_data[button_color]" 
                                       value="<?php echo esc_attr($this->style_options['button_color']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>اندازه فونت (px)</th>
                            <td>
                                <input type="number" name="commerce_yar_style_data[font_size]" 
                                       value="<?php echo esc_attr($this->style_options['font_size']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th>وزن فونت</th>
                            <td>
                                <select name="commerce_yar_style_data[font_weight]">
                                    <?php
                                    $weights = array('300' => 'Light', '400' => 'Regular', '500' => 'Medium', '600' => 'Semi Bold', '700' => 'Bold');
                                    foreach ($weights as $value => $label) {
                                        printf(
                                            '<option value="%s" %s>%s</option>',
                                            esc_attr($value),
                                            selected($this->style_options['font_weight'], $value, false),
                                            esc_html($label)
                                        );
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>رنگ متن</th>
                            <td>
                                <input type="color" name="commerce_yar_style_data[text_color]" 
                                       value="<?php echo esc_attr($this->style_options['text_color']); ?>">
                            </td>
                        </tr>
                    </table>
                    <?php submit_button('ذخیره تنظیمات ظاهری'); ?>
                </form>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            // Tab switching
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                $('.tab-content').hide();
                $($(this).attr('href')).show();
            });

            // Add new pricing field
            $('.add-price-button').on('click', function() {
                const type = $(this).data('type');
                const container = $(this).closest('div');
                const index = container.find('.pricing-field').length;
                
                const template = `
                    <div class="pricing-field">
                        <input type="text" name="commerce_yar_pricing_data[${type}][${index}][title]" placeholder="عنوان">
                        <input type="number" name="commerce_yar_pricing_data[${type}][${index}][price]" placeholder="قیمت">
                        <textarea name="commerce_yar_pricing_data[${type}][${index}][features]" placeholder="ویژگی‌ها (هر خط یک ویژگی)"></textarea>
                        <input type="text" name="commerce_yar_pricing_data[${type}][${index}][button_text]" placeholder="متن دکمه">
                        <input type="text" name="commerce_yar_pricing_data[${type}][${index}][button_link]" placeholder="لینک دکمه">
                        <button type="button" class="remove-price-button">حذف</button>
                    </div>
                `;
                
                container.find('.add-price-button').before(template);
            });

            // Remove pricing field
            $(document).on('click', '.remove-price-button', function() {
                $(this).closest('.pricing-field').remove();
            });
        });
        </script>

        <style>
        .pricing-field {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 15px;
            background: #f9f9f9;
        }
        .pricing-field input,
        .pricing-field textarea {
            width: 100%;
            margin-bottom: 10px;
        }
        .pricing-field textarea {
            height: 100px;
        }
        .remove-price-button {
            color: #dc3545;
            border-color: #dc3545;
        }
        </style>
        <?php
    }


    public function save_pricing_data() {
        check_ajax_referer('commerce_yar_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        global $wpdb;
        
        $titles = $_POST['title'];
        $prices = $_POST['price'];
        $features = $_POST['features'];
        $button_texts = $_POST['button_text'];
        $button_links = $_POST['button_link'];
        $price_codes = $_POST['price_code'];
        $pricing_types = $_POST['pricing_type'];
        
        // Begin transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Clear existing data
            $wpdb->query("DELETE FROM {$this->pricing_table}");
            
            // Insert new data
            for ($i = 0; $i < count($titles); $i++) {
                $wpdb->insert(
                    $this->pricing_table,
                    array(
                        'pricing_type' => sanitize_text_field($pricing_types[$i]),
                        'title' => sanitize_text_field($titles[$i]),
                        'price' => floatval($prices[$i]),
                        'features' => sanitize_textarea_field($features[$i]),
                        'button_text' => sanitize_text_field($button_texts[$i]),
                        'button_link' => esc_url_raw($button_links[$i]),
                        'price_code' => sanitize_text_field($price_codes[$i])
                    ),
                    array('%s', '%s', '%f', '%s', '%s', '%s', '%s')
                );
            }
            // die(json_encode( $titles));
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Clear caches
            wp_cache_delete('commerce_yar_pricing_data_monthly');
            wp_cache_delete('commerce_yar_pricing_data_quarterly');
            wp_cache_delete('commerce_yar_pricing_data_biannual');
            wp_cache_delete('commerce_yar_pricing_data_yearly');
            
            wp_send_json_success(array('message' => 'تغییرات با موفقیت ذخیره شد.'));
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => 'خطا در ذخیره تغییرات.'));
        }
    }
}

new Commerce_Yar_Admin_Settings();
