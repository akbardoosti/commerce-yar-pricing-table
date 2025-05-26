<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template for displaying the pricing table
 * 
 * @var array $atts Shortcode attributes
 */
?>

<div class="commerce-yar-pricing-table theme-<?php echo esc_attr($atts['theme']); ?>">
    <div class="pricing-columns columns-<?php echo esc_attr($atts['columns']); ?>">
        <!-- Pricing table content will be dynamically populated -->
        <div class="pricing-column">
            <div class="pricing-header">
                <h3>Basic Plan</h3>
                <div class="price">$19<span>/month</span></div>
            </div>
            <div class="pricing-features">
                <ul>
                    <li>Feature 1</li>
                    <li>Feature 2</li>
                    <li>Feature 3</li>
                </ul>
            </div>
            <div class="pricing-footer">
                <a href="#" class="button">Get Started</a>
            </div>
        </div>
    </div>
</div> 