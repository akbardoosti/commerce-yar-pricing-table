jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab-wrapper a').click(function(e) {
        e.preventDefault();
        $('.nav-tab-wrapper a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });

    // Initialize first tab
    $('.tab-content').hide();
    $('#pricing-settings').show();

    // Add new plan
    $('.add-plan').click(function() {
        const period = $(this).data('period');
        const planHtml = `
            <div class="plan-item">
                <input type="hidden" name="id[]" value="">
                <input type="hidden" name="pricing_type[]" value="${period}">
                
                <div class="form-group">
                    <label>عنوان:</label>
                    <input type="text" name="title[]" required>
                </div>
                
                <div class="form-group">
                    <label>قیمت (تومان):</label>
                    <input type="number" name="price[]" required>
                </div>
                
                <div class="form-group">
                    <label>قیمت با تخفیف (تومان):</label>
                    <input type="number" name="sale_price[]">
                    <small>اگر تخفیف ندارد خالی بگذارید</small>
                </div>
                
                <div class="form-group">
                    <label>ویژگی‌ها:</label>
                    <textarea name="features[]" required></textarea>
                    <small>هر ویژگی را در یک خط جدید وارد کنید</small>
                </div>
                
                <div class="form-group">
                    <label>متن دکمه:</label>
                    <input type="text" name="button_text[]" value="خرید" required>
                </div>
                
                <div class="form-group">
                    <label>لینک دکمه:</label>
                    <input type="text" name="button_link[]" value="#" required>
                </div>
                
                <div class="form-group">
                    <label>کد قیمت:</label>
                    <input type="text" name="price_code[]" required>
                </div>
                
                <button type="button" class="button delete-plan">حذف پلن</button>
            </div>
        `;
        
        $(this).before(planHtml);
    });

    // Delete plan
    $(document).on('click', '.delete-plan', function() {
        $(this).closest('.plan-item').remove();
    });

    // Save pricing data
    $('#commerce-yar-pricing-form').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const originalButtonText = $submitButton.text();
        
        $submitButton.text('در حال ذخیره...').prop('disabled', true);
        
        $.ajax({
            url: commerceYarAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'commerce_yar_save_pricing',
                nonce: commerceYarAdmin.nonce,
                ...$(this).serializeArray()
            },
            success: function(response) {
                if (response.success) {
                    alert('تنظیمات با موفقیت ذخیره شد.');
                } else {
                    alert('خطا در ذخیره تنظیمات: ' + response.data);
                }
            },
            error: function() {
                alert('خطا در برقراری ارتباط با سرور');
            },
            complete: function() {
                $submitButton.text(originalButtonText).prop('disabled', false);
            }
        });
    });

    // Copy subscription token
    $('.copy-token').click(function() {
        const token = $(this).data('token');
        const tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(token).select();
        document.execCommand('copy');
        tempInput.remove();
        
        const $button = $(this);
        const originalText = $button.text();
        $button.text('کپی شد!');
        setTimeout(() => {
            $button.text(originalText);
        }, 2000);
    });
}); 