jQuery(document).ready(function($) {
    // Plan template for adding new plans
    function getPlanTemplate(periodType) {
        return `
            <div class="plan-item">
                <input type="hidden" name="id[]" value="">
                <input type="hidden" name="pricing_type[]" value="${periodType}">
                
                <div class="form-group">
                    <label>عنوان:</label>
                    <input type="text" name="title[]" required>
                </div>
                
                <div class="form-group">
                    <label>قیمت (تومان):</label>
                    <input type="number" name="price[]" required>
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
    }

    // Add new plan
    $('.add-plan').on('click', function() {
        const periodType = $(this).data('period');
        const template = getPlanTemplate(periodType);
        $(this).before(template);
    });

    // Delete plan
    $(document).on('click', '.delete-plan', function() {
        $(this).closest('.plan-item').fadeOut(300, function() {
            $(this).remove();
        });
    });

    // Copy token
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

    // Form submission
    $('#commerce-yar-pricing-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitButton = $form.find('button[type="submit"]');
        const $spinner = $form.find('.spinner');
        
        // Show loading state
        $submitButton.prop('disabled', true);
        $spinner.addClass('is-active');
        
        // Collect form data
        const formData = new FormData(this);
        formData.append('action', 'commerce_yar_save_pricing');
        formData.append('nonce', commerceYarAdmin.nonce);
        
        // Send AJAX request
        $.ajax({
            url: commerceYarAdmin.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('تغییرات با موفقیت ذخیره شد.');
                } else {
                    alert(response.data.message || 'خطا در ذخیره تغییرات.');
                }
            },
            error: function() {
                alert('خطا در برقراری ارتباط با سرور.');
            },
            complete: function() {
                $submitButton.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
}); 