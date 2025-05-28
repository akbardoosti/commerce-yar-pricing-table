<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template for displaying the pricing table
 * 
 * @var array $atts Shortcode attributes
 */

// Get cached pricing data
$cache_key = 'commerce_yar_pricing_data_' . $atts['type'];
$pricing_data = wp_cache_get($cache_key);

if (false === $pricing_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'commerce_yar_token';
    $pricing_data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    wp_cache_set($cache_key, $pricing_data, '', 3600); // Cache for 1 hour
}

$style_data = get_option('commerce_yar_style_data', array());

// Default style values
$style_data = wp_parse_args($style_data, array(
    'border_size' => '1',
    'border_color' => '#e0e0e0',
    'border_radius' => '8',
    'box_shadow' => '0 2px 4px rgba(0,0,0,0.1)',
    'button_color' => '#007bff',
    'font_size' => '16',
    'font_weight' => '400',
    'text_color' => '#333333'
));

// Enqueue necessary scripts and styles
wp_enqueue_style('varzir-matn-css', COMMERCE_YAR_PRICING_PLUGIN_URL . 'assets/css/vazir-matn-font-face.css');
wp_enqueue_style('commerce-yar-style', COMMERCE_YAR_PRICING_PLUGIN_URL . 'css/style.css');
wp_enqueue_style('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.css');
wp_enqueue_script('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);
wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css');
wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js', array('jquery'), null, true);

$pricing_type = isset($atts['type']) ? $atts['type'] : 'monthly';

// Generate dynamic styles
$dynamic_styles = "
<style>
    .commerce-yar-pricing-table {
        position: relative;
    }
    .commerce-yar-pricing-table .pricing-column {
        border: {$style_data['border_size']}px solid {$style_data['border_color']};
        border-radius: {$style_data['border_radius']}px;
        box-shadow: {$style_data['box_shadow']};
        font-size: {$style_data['font_size']}px;
        font-weight: {$style_data['font_weight']};
        color: {$style_data['text_color']};
        padding: 20px;
        font-family: 'Vazirmatn', sans-serif;
    }
    
    .commerce-yar-pricing-table .pricing-button {
        background-color: {$style_data['button_color']};
        color: #ffffff;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        text-decoration: none;
        display: inline-block;
        transition: opacity 0.3s;
        min-width: 150px;
    }
    .commerce-yar-pricing-table .pricing-button:hover {
        opacity: 0.9;
    }
    .swiper-button-next, .swiper-button-prev {
        color: {$style_data['button_color']};
    }
    .pricing-type-switcher {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .pricing-type-switcher label {
        cursor: pointer;
        border: 1px solid {$style_data['button_color']};
        border-radius: 4px;
        transition: all 0.3s;
    }
    
    .pricing-type-switcher input:checked + span {
        background-color: {$style_data['button_color']};
        color: white;
    }
    
    .pricing-type-switcher input {
        display: none;
    }
    
    .pricing-type-switcher span {
        display: block;
        padding: 8px 16px;
        font-family: 'Vazirmatn', sans-serif;
    }
    
    .commerce-yar-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    .commerce-yar-loading .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid {$style_data['button_color']};
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
</style>
";
echo $dynamic_styles;
?>

<div class="commerce-yar-pricing-table theme-<?php echo esc_attr($atts['theme']); ?>">
    <div class="pricing-type-switcher">
        <label>
            <input type="radio" name="pricing_type" value="monthly" <?php checked($pricing_type, 'monthly'); ?>>
            <span>ماهانه</span>
        </label>
        <label>
            <input type="radio" name="pricing_type" value="quarterly" <?php checked($pricing_type, 'quarterly'); ?>>
            <span>سه ماهه</span>
        </label>
        <label>
            <input type="radio" name="pricing_type" value="biannual" <?php checked($pricing_type, 'biannual'); ?>>
            <span>شش ماهه</span>
        </label>
        <label>
            <input type="radio" name="pricing_type" value="yearly" <?php checked($pricing_type, 'yearly'); ?>>
            <span>سالانه</span>
        </label>
    </div>

    <div class="commerce-yar-loading">
        <div class="spinner"></div>
    </div>

    <div class="swiper pricing-swiper">
        <div class="swiper-wrapper">
            <?php foreach ($pricing_data as $price) : 
                if ($price['pricing_type'] === $pricing_type) : ?>
                <div class="swiper-slide">
                    <div class="pricing-column" style="text-align: center;">
                        <div class="pricing-header">
                            <h3><?php echo esc_html($price['title']); ?></h3>
                            <div class="price">
                                <?php echo number_format_i18n($price['price']); ?>
                                <span class="currency">تومان</span>
                            </div>
                        </div>
                        <div class="pricing-features">
                            <ul style="line-height: 30px;list-style: none;
    display: flex;flex-direction: column;align-items: center;">
                                <?php
                                $features = explode("\n", $price['features']);
                                foreach ($features as $feature) :
                                    if (!empty(trim($feature))) :
                                ?>
                                    <li style="border-bottom: solid 1px #eee;"><?php echo esc_html(trim($feature)); ?></li>
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
            <?php endif; endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>

<!-- Add this before closing </div> of commerce-yar-pricing-table -->
<div id="payment-processing-modal" style="display: none;">
    <div class="payment-processing-content">
        <div class="spinner"></div>
        <p>در حال انتقال به درگاه پرداخت...</p>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let swiper = null;
    
    const initSwiper = () => {
        if (swiper) {
            swiper.destroy();
        }
        
        swiper = new Swiper('.pricing-swiper', {
            slidesPerView: 1,
            spaceBetween: 30,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                }
            }
        });
    };

    initSwiper();

    // Handle pricing type switch with loading state
    $('input[name="pricing_type"]').on('change', function() {
        const type = $(this).val();
        const $container = $('.commerce-yar-pricing-table');
        const $loading = $('.commerce-yar-loading');
        
        $loading.fadeIn();
        $loading.css('display', 'flex')
        
        $.ajax({
            url: commerceYarAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'commerce_yar_get_pricing',
                type: type,
                nonce: commerceYarAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.pricing-swiper .swiper-wrapper').html(response.data.html);
                    initSwiper();
                } else {
                    Swal.fire({
                        title: 'خطا',
                        text: response.data.message || 'خطا در بارگذاری اطلاعات',
                        icon: 'error',
                        confirmButtonText: 'باشه'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'خطا',
                    text: 'خطا در برقراری ارتباط با سرور',
                    icon: 'error',
                    confirmButtonText: 'باشه'
                });
            },
            complete: function() {
                $loading.fadeOut();
            }
        });
    });

    // Handle plan selection
    $(document).on('click', '.pricing-button', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const planTitle = button.closest('.pricing-column').find('h3').text();
        const planPrice = button.closest('.pricing-column').find('.price').text();
        const planType = $('input[name="pricing_type"]:checked').val();
        const priceCode = button.data('price-code');
        const planData = {
            title: planTitle,
            price: planPrice,
            type: planType,
            price_code: priceCode,
            button_link: button.attr('href')
        };

        Swal.fire({
            title: 'تأیید انتخاب پلن',
            html: `
                <div style="text-align: right;">
                    <p>شما در حال انتخاب پلن زیر هستید:</p>
                    <ul style="list-style: none; padding: 0;">
                        <li><strong>عنوان:</strong> ${planTitle}</li>
                        <li><strong>قیمت:</strong> ${planPrice}</li>
                        <li><strong>نوع اشتراک:</strong> ${planType === 'monthly' ? 'ماهانه' : planType === 'yearly' ? 'سالانه' : planType === 'quarterly' ? 'سه ماهه' : 'شش ماهه'}</li>
                    </ul>
                    <p>آیا مایل به ادامه و پرداخت هستید؟</p>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'بله، ادامه',
            cancelButtonText: 'انصراف',
            reverseButtons: true,
            customClass: {
                popup: 'commerce-yar-swal-rtl'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show processing modal
                $('#payment-processing-modal').show();

                // Make AJAX call to initiate payment
                $.ajax({
                    url: commerceYarAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'commerce_yar_initiate_payment',
                        plan_data: planData,
                        nonce: commerceYarAjax.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data.payment_url) {
                            // Redirect to payment gateway
                            window.location.href = response.data.payment_url;
                        } else {
                            Swal.fire({
                                title: 'خطا',
                                text: response.data.message || 'خطا در اتصال به درگاه پرداخت',
                                icon: 'error',
                                confirmButtonText: 'باشه'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'خطا',
                            text: 'خطا در برقراری ارتباط با سرور',
                            icon: 'error',
                            confirmButtonText: 'باشه'
                        });
                    },
                    complete: function() {
                        $('#payment-processing-modal').hide();
                    }
                });
            }
        });
    });
});
</script> 