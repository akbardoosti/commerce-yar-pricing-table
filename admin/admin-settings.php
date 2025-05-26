<?php
if (!defined('ABSPATH')) {
    exit;
}

class Commerce_Yar_Admin_Settings {
    private $pricing_options;
    private $style_options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'init_settings'));
    }

    public function add_admin_menu() {
        add_menu_page(
            'Pricing Table Settings',
            'Pricing Table',
            'manage_options',
            'commerce-yar-pricing',
            array($this, 'render_settings_page'),
            'dashicons-grid-view'
        );
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

        $this->pricing_options = get_option('commerce_yar_pricing_data', array());
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

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h2>تنظیمات جدول قیمت‌گذاری</h2>
            
            <div class="nav-tab-wrapper">
                <a href="#pricing-settings" class="nav-tab nav-tab-active">تنظیمات قیمت‌گذاری</a>
                <a href="#style-settings" class="nav-tab">تنظیمات ظاهری</a>
            </div>

            <div id="pricing-settings" class="tab-content">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('commerce_yar_pricing_options');
                    do_settings_sections('commerce_yar_pricing_options');
                    ?>
                    <div class="pricing-tables-container">
                        <h3>قیمت‌های ماهانه</h3>
                        <div class="monthly-prices">
                            <?php $this->render_pricing_fields('monthly'); ?>
                            <button type="button" class="add-price-button" data-type="monthly">افزودن قیمت ماهانه</button>
                        </div>

                        <h3>قیمت‌های سالانه</h3>
                        <div class="yearly-prices">
                            <?php $this->render_pricing_fields('yearly'); ?>
                            <button type="button" class="add-price-button" data-type="yearly">افزودن قیمت سالانه</button>
                        </div>
                    </div>
                    <?php submit_button('ذخیره تنظیمات'); ?>
                </form>
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

    private function render_pricing_fields($type) {
        if (!empty($this->pricing_options[$type])) {
            foreach ($this->pricing_options[$type] as $index => $price) {
                ?>
                <div class="pricing-field">
                    <input type="text" name="commerce_yar_pricing_data[<?php echo $type; ?>][<?php echo $index; ?>][title]" 
                           value="<?php echo esc_attr($price['title']); ?>" placeholder="عنوان">
                    <input type="number" name="commerce_yar_pricing_data[<?php echo $type; ?>][<?php echo $index; ?>][price]" 
                           value="<?php echo esc_attr($price['price']); ?>" placeholder="قیمت">
                    <textarea name="commerce_yar_pricing_data[<?php echo $type; ?>][<?php echo $index; ?>][features]" 
                              placeholder="ویژگی‌ها (هر خط یک ویژگی)"><?php echo esc_textarea($price['features']); ?></textarea>
                    <input type="text" name="commerce_yar_pricing_data[<?php echo $type; ?>][<?php echo $index; ?>][button_text]" 
                           value="<?php echo esc_attr($price['button_text']); ?>" placeholder="متن دکمه">
                    <input type="text" name="commerce_yar_pricing_data[<?php echo $type; ?>][<?php echo $index; ?>][button_link]" 
                           value="<?php echo esc_attr($price['button_link']); ?>" placeholder="لینک دکمه">
                    <button type="button" class="remove-price-button">حذف</button>
                </div>
                <?php
            }
        }
    }
}

new Commerce_Yar_Admin_Settings();
