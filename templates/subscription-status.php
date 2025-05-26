<?php
if (!defined('ABSPATH')) {
    exit;
}

$status = isset($_GET['payment_status']) ? $_GET['payment_status'] : '';
$token = isset($_GET['token']) ? $_GET['token'] : '';
?>

<div class="subscription-status-page">
    <?php if ($status === 'success'): ?>
        <div class="status-box success">
            <div class="icon-container">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h2>پرداخت با موفقیت انجام شد</h2>
            <p>اشتراک شما با موفقیت فعال شد.</p>
            <?php if ($token): ?>
                <div class="token-box">
                    <p>توکن دسترسی شما:</p>
                    <code><?php echo esc_html($token); ?></code>
                    <button class="copy-token" data-token="<?php echo esc_attr($token); ?>">کپی توکن</button>
                </div>
            <?php endif; ?>
            <div class="action-buttons">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="button">بازگشت به صفحه اصلی</a>
                <a href="<?php echo esc_url(home_url('/my-account/subscriptions/')); ?>" class="button">مشاهده اشتراک‌ها</a>
            </div>
        </div>
    <?php elseif ($status === 'failed'): ?>
        <div class="status-box error">
            <div class="icon-container">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <h2>خطا در پرداخت</h2>
            <p>متأسفانه پرداخت شما با مشکل مواجه شد.</p>
            <div class="action-buttons">
                <a href="<?php echo wp_get_referer(); ?>" class="button">تلاش مجدد</a>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="button">بازگشت به صفحه اصلی</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.subscription-status-page {
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
}

.status-box {
    text-align: center;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.status-box.success {
    background-color: #f0fff4;
    border: 1px solid #68d391;
}

.status-box.error {
    background-color: #fff5f5;
    border: 1px solid #fc8181;
}

.icon-container {
    margin-bottom: 20px;
}

.icon-container .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
}

.status-box.success .dashicons {
    color: #38a169;
}

.status-box.error .dashicons {
    color: #e53e3e;
}

.token-box {
    background: #f7fafc;
    padding: 15px;
    border-radius: 4px;
    margin: 20px 0;
}

.token-box code {
    display: block;
    padding: 10px;
    background: #edf2f7;
    border-radius: 4px;
    margin: 10px 0;
    direction: ltr;
    text-align: left;
}

.copy-token {
    background: #4299e1;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.3s;
}

.copy-token:hover {
    background: #3182ce;
}

.action-buttons {
    margin-top: 30px;
}

.action-buttons .button {
    display: inline-block;
    padding: 10px 20px;
    margin: 0 10px;
    border-radius: 4px;
    text-decoration: none;
    transition: background 0.3s;
}

.action-buttons .button:first-child {
    background: #4299e1;
    color: white;
}

.action-buttons .button:last-child {
    background: #edf2f7;
    color: #4a5568;
}

.action-buttons .button:hover {
    opacity: 0.9;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.copy-token').on('click', function() {
        const token = $(this).data('token');
        const tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(token).select();
        document.execCommand('copy');
        tempInput.remove();
        
        const originalText = $(this).text();
        $(this).text('کپی شد!');
        setTimeout(() => {
            $(this).text(originalText);
        }, 2000);
    });
});
</script> 