jQuery(document).ready(function ($) {
    $('.duplicator-admin-notice[data-to-dismiss]').each(function () {
        var notice = $(this);
        var notice_to_dismiss = notice.data('to-dismiss');

        notice.find('.notice-dismiss').on('click', function (event) {
            event.preventDefault();
            $.post(ajaxurl, {
                action: 'duplicator_admin_notice_to_dismiss',
                notice: notice_to_dismiss,
                nonce: dup_global_script_data.nonce_admin_notice_to_dismiss
            });
        });
    });

    $('.dup-settings-lite-cta .dismiss').on('click', function (event) {
        event.preventDefault();
        $.post(
            ajaxurl,
            {
                action: 'duplicator_settings_callout_cta_dismiss',
                nonce: dup_global_script_data.nonce_settings_callout_to_dismiss
            },
            function (response) {
                if (response.success) {
                    $('.dup-settings-lite-cta').fadeOut(300);
                }
            }
        );
    });

    $('#dup-packages-bottom-bar-dismiss').on('click', function (event) {
        event.preventDefault();

        $.post(
            ajaxurl,
            {
                action: 'duplicator_packages_bottom_bar_dismiss',
                nonce: dup_global_script_data.nonce_packages_bottom_bar_dismiss
            },
            function (response) {
                if (response.success) {
                    $('#dup-packages-bottom-bar').closest('tr').fadeOut(300);
                }
            }
        );
    });

    $('.dup-subscribe-form button').on('click', function (event) {
        event.preventDefault();
        var button  = $('.dup-subscribe-form button');
        var wrapper = $('.dup-subscribe-form');
        var input   = $('.dup-subscribe-form input[name="email"]');

        button.html('Subscribing...');
        input.attr('disabled', 'disabled');
        $.post(
            ajaxurl,
            {
                action: 'duplicator_email_subscribe',
                email: input.val(),
                nonce: dup_global_script_data.nonce_email_subscribe
            },
            function (response) {
                if (response.success) {
                    wrapper.fadeOut(300);
                    button.html('Subscribed &#10003');
                    wrapper.fadeIn(300);

                    setTimeout(function () {
                        wrapper.fadeOut(300);
                    }, 3000);
                } else {
                    console.log("Email subscription failed with message: " + response.message);
                    button.html('Failed &#10007');

                    setTimeout(function () {
                        button.html('Subscribe');
                        input.removeAttr('disabled');
                    }, 3000);
                }
            }
        );
    });

    function dupDashboardUpdate() {
        jQuery.ajax({
            type: "POST",
            url: dup_global_script_data.ajaxurl,
            dataType: "json",
            data: {
                action: 'duplicator_dashboad_widget_info',
                nonce: dup_global_script_data.nonce_dashboard_widged_info
            },
            success: function (result, textStatus, jqXHR) {
                if (result.success) {
                    $('#duplicator_dashboard_widget .dup-last-backup-info').html(result.data.funcData.lastBackupInfo);

                    if (result.data.funcData.isRunning) {
                        $('#duplicator_dashboard_widget #dup-pro-create-new').addClass('disabled');
                    } else {
                        $('#duplicator_dashboard_widget #dup-pro-create-new').removeClass('disabled');
                    }
                }
            },
            complete: function() {
                setTimeout(
                    function(){
                        dupDashboardUpdate();
                    }, 
                    5000
                );
            }
        });
    }
    
    if ($('#duplicator_dashboard_widget').length) {
        dupDashboardUpdate();

        $('#duplicator_dashboard_widget #dup-dash-widget-section-recommended').on('click', function (event) {
            event.stopPropagation();
            
            $(this).closest('.dup-section-recommended').fadeOut();

            jQuery.ajax({
                type: "POST",
                url: dup_global_script_data.ajaxurl,
                dataType: "json",
                data: {
                    action: 'duplicator_dismiss_recommended_plugin',
                    nonce: dup_global_script_data.nonce_dashboard_widged_dismiss_recommended
                },
                success: function (result, textStatus, jqXHR) {
                    // do nothing
                }
            });
        });
    }
});
