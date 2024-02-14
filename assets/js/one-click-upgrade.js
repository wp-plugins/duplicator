var oneClickUpgradeRemoteEndpoint = "https://connect.duplicator.com/upgrade-free-to-pro";

jQuery(document).ready(function ($) {
    if ($('#dup-settings-upgrade-license-key').length) {
        function addErrorMessage(text) {
            let msg = $('<div class="notice notice-error is-dismissible">' + 
                '<p>' + 
                    text + 
                '</p>' + 
                '<button type="button" class="notice-dismiss">' + 
                    '<span class="screen-reader-text"></span>' + 
                '</button>' + 
                '</div>'
            );
            msg.insertAfter( ".dup-settings-pages > h1" );
            msg.find('.notice-dismiss').on('click', function (event) {
                event.stopPropagation();
                msg.remove();
            });
        }
        
        // Client-side redirect to oneClickUpgradeRemoteEndpoint
        function redirectToRemoteEndpoint(data) {
            if (data["success"] != true) {
                addErrorMessage(data["error_msg"]); 
                return;
            }
            
            $("#redirect-to-remote-upgrade-endpoint").attr("action", oneClickUpgradeRemoteEndpoint);
            $("#form-oth").attr("value", data["oth"]);
            $("#form-key").attr("value", data["license_key"]);
            $("#form-version").attr("value", data["version"]);
            $("#form-redirect").attr("value", data["redirect"]);
            $("#form-endpoint").attr("value", data["endpoint"]);
            $("#form-siteurl").attr("value", data["siteurl"]);
            $("#form-homeurl").attr("value", data["homeurl"]);
            $("#form-file").attr("value", data["file"]);
            $("#redirect-to-remote-upgrade-endpoint").submit();
        }
        
        $('#dup-settings-connect-btn').on('click', function (event) {
            event.stopPropagation();
            var license_key = $('#dup-settings-upgrade-license-key').eq(0).val();
            jQuery.ajax({
                type: "POST",
                url: dup_one_click_upgrade_script_data.ajaxurl,
                dataType: "json",
                data: {
                    action: 'duplicator_one_click_upgrade_prepare',
                    nonce: dup_one_click_upgrade_script_data.nonce_one_click_upgrade,
                    license_key: license_key
                },
                success: function (result, textStatus, jqXHR) {
                    if (result.success) {
                        redirectToRemoteEndpoint(result.data.funcData);                        
                    } else {
                        addErrorMessage(result.data.message);
                    }
                },
                error: function (result, textStatus, error) {
                    console.log(result);
                }
            });
        });
    }
});
