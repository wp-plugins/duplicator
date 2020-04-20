jQuery(document).ready(function($) {
    $('.duplicator-plugin-activation-admin-notice .notice-dismiss').on('click', function (event) {
        event.preventDefault();
        $.post(ajaxurl, {
            action: 'duplicator_dismiss_plugin_activation_admin_notice',
            nonce: dup_global_script_data.dismiss_plugin_activation_admin_notice_nonce
        });
    });
});
