<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template for displaying the pricing table
 * 
 * @var array $atts Shortcode attributes
 */

$pricing_data = get_option('commerce_yar_pricing_data', array());
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

// Enqueue Swiper if not already enqueued
if (!wp_script_is('swiper', 'enqueued')) {
    wp_enqueue_style('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.css');
    wp_enqueue_script('swiper', 'https://unpkg.com/swiper/swiper-bundle.min.js', array(), null, true);
}

// Enqueue SweetAlert2 for beautiful dialogs
wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css');
wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js', array('jquery'), null, true);

$pricing_type = isset($atts['type']) ? $atts['type'] : 'monthly';
$prices = isset($pricing_data[$pricing_type]) ? $pricing_data[$pricing_type] : array();

// Generate dynamic styles
$dynamic_styles = "
<style>
    .commerce-yar-pricing-table .pricing-column {
        border: {$style_data['border_size']}px solid {$style_data['border_color']};
        border-radius: {$style_data['border_radius']}px;
        box-shadow: {$style_data['box_shadow']};
        font-size: {$style_data['font_size']}px;
        font-weight: {$style_data['font_weight']};
        color: {$style_data['text_color']};
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
    }
    .commerce-yar-pricing-table .pricing-button:hover {
        opacity: 0.9;
    }
    .swiper-button-next, .swiper-button-prev {
        color: {$style_data['button_color']};
    }
</style>
";
echo $dynamic_styles;
?>

<div class="commerce-yar-pricing-table theme-<?php echo esc_attr($atts['theme']); ?>">
    <div class="pricing-type-switcher">
        <label>
            <input type="radio" name="pricing_type" value="monthly" <?php checked($pricing_type, 'monthly'); ?>>
            ماهانه
        </label>
        <label>
            <input type="radio" name="pricing_type" value="yearly" <?php checked($pricing_type, 'yearly'); ?>>
            سالانه
        </label>
    </div>

    <div class="swiper pricing-swiper">
        <div class="swiper-wrapper">
            <?php foreach ($prices as $price) : ?>
                <div class="swiper-slide">
                    <div class="pricing-column">
                        <div class="pricing-header">
                            <h3><?php echo esc_html($price['title']); ?></h3>
                            <div class="price">
                                <?php echo number_format_i18n($price['price']); ?>
                                <span class="currency">تومان</span>
                                <span class="period">/<?php echo $pricing_type === 'monthly' ? 'ماه' : 'سال'; ?></span>
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
                            <a href="<?php echo esc_url($price['button_link']); ?>" class="pricing-button">
                                <?php echo esc_html($price['button_text']); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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

<style>
.commerce-yar-pricing-table {
    direction: rtl;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.pricing-type-switcher {
    text-align: center;
    margin-bottom: 30px;
}

.pricing-type-switcher label {
    margin: 0 10px;
    cursor: pointer;
}

.pricing-column {
    height: 100%;
    background: #ffffff;
    padding: 20px;
    text-align: center;
    transition: transform 0.3s;
}

.pricing-column:hover {
    transform: translateY(-5px);
}

.pricing-header {
    margin-bottom: 20px;
}

.price {
    font-size: 2em;
    font-weight: bold;
    margin: 10px 0;
}

.currency, .period {
    font-size: 0.5em;
    font-weight: normal;
}

.pricing-features ul {
    list-style: none;
    padding: 0;
    margin: 0 0 20px 0;
}

.pricing-features li {
    padding: 10px 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.pricing-footer {
    padding-top: 20px;
}

/* Swiper customization */
.swiper {
    padding: 20px 40px;
}

.swiper-button-next,
.swiper-button-prev {
    transform: scale(0.7);
}

.swiper-pagination {
    position: relative;
    margin-top: 20px;
}

@media (max-width: 640px) {
    .pricing-column {
        margin-bottom: 20px;
    }
}

/* Add to existing styles */
.payment-processing-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.payment-processing-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Customize SweetAlert */
.swal2-popup {
    font-family: inherit;
    direction: rtl;
}

.swal2-title, .swal2-content {
    text-align: right;
}

.swal2-actions {
    flex-direction: row-reverse;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize Swiper
    const initSwiper = () => {
        const swiper = new Swiper('.pricing-swiper', {
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

    // Initialize Swiper on page load
    initSwiper();

    // Handle pricing type switch
    $('input[name="pricing_type"]').on('change', function() {
        const type = $(this).val();
        // Reload shortcode with new type
        // You'll need to implement AJAX here to reload the pricing table
    });

    // Handle plan selection
    $(document).on('click', '.pricing-button', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const planTitle = button.closest('.pricing-column').find('h3').text();
        const planPrice = button.closest('.pricing-column').find('.price').text();
        const planType = button.closest('.commerce-yar-pricing-table').find('input[name="pricing_type"]:checked').val();
        const planData = {
            title: planTitle,
            price: planPrice,
            type: planType,
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
                        <li><strong>نوع اشتراک:</strong> ${planType === 'monthly' ? 'ماهانه' : 'سالانه'}</li>
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