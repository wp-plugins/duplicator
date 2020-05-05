jQuery(document).ready(function ($) {
    $('.duplicator-admin-notice[data-to-dismiss]').each(function () {
        var notice = $(this);
        var notice_to_dismiss = notice.data('to-dismiss');

        notice.find('.notice-dismiss').on('click', function (event) {
            event.preventDefault();
            $.post(ajaxurl, {
                action: 'duplicator_admin_notice_to_dismiss',
                notice: notice_to_dismiss,
                nonce: dup_global_script_data.duplicator_admin_notice_to_dismiss
            });
        });
    });
});
