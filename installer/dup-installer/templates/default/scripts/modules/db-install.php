<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();
?>
<script>
    DUPX.startAjaxDbInstall = function (isTheFirstCall, successCallback)
    {
        if (isTheFirstCall) {
            DUPX.pageComponents.resetTopMessages().showProgress({
                'title': 'Installing Database',
                'perc': '0%',
                'secondary': 'Bytes processed: 0',
                'bottomText':
                        '<i>Keep this window open during the creation process.</i><br/>' +
                        '<i>This can take several minutes.</i>'
            });
        }

        let dbInstallAction = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_DBINSTALL); ?>;
        let dbInstallToken = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_DBINSTALL)); ?>;

        let retryAttemp = 0;

        DUPX.StandardJsonAjaxWrapper(
                dbInstallAction,
                dbInstallToken,
                {},
                function (data) {
                    DUPX.progress.update({
                        'perc': data.actionData.perc,
                        'secondary': data.actionData.queryOffset
                    });
                    console.log('dbinstall data', data);

                    if (data.actionData.is_error) {
                        const result = {
                            'success': false,
                            'message': 'DB INSTALL ERROR: ' + data.actionData.error_msg,
                            'errorContent': {
                                'pre': '',
                                'html': ''
                            },
                            'actionData': null
                        };
                        DUPX.ajaxErrorDisplayHideError(result, null, null);
                    } else if (data.actionData.continue_chunking) {
                        DUPX.startAjaxDbInstall(false, successCallback);
                    } else if (data.actionData.pass) {
                        if (typeof successCallback === "function") {
                            successCallback(data);
                        } else {
                            alert('db install complete');
                        }
                    } else {
                        const result = {
                            'success': false,
                            'message': 'DB INSTALL ERROR: not passed',
                            'errorContent': {
                                'pre': '',
                                'html': ''
                            },
                            'actionData': null
                        };
                        DUPX.ajaxErrorDisplayHideError(result, null, null);
                    }
                },
                function (result, textStatus, jqXHR) {
                    let default_timeout_message = '';
                    let status = "<b>Server Code:</b> " + jqXHR.status + "<br/>";
                    status += "<b>Status:</b> " + jqXHR.statusText + "<br/>";
                    status += "<b>Response:</b> " + jqXHR.responseText + "<hr/>";

                    if (textStatus && textStatus.toLowerCase() == "timeout" || textStatus.toLowerCase() == "service unavailable") {
                        status += "<b>Recommendation:</b><br/>";
                        status += "To resolve this problem please follow the instructions showing " + 
                            "<a target='_blank' href='https://duplicator.com/knowledge-base/how-to-fix-database-connection-issues'>in the FAQ</a>.<br/><br/>";
                    } else if ((jqXHR.status == 403) || (jqXHR.status == 500)) {
                        status += "<b>Recommendation</b><br/>";
                        status += "See <a target='_blank' href='https://duplicator.com/knowledge-base/how-to-resolve-403-500-timeout-issues-on-step-3'>" +
                            "this section</a> of the Technical FAQ for possible resolutions.<br/><br/>"
                    } else if (jqXHR.status == 0) {
                        status += "<b>Recommendation</b><br/>";
                        status += "This may be a server timeout and performing a 'Manual Extract' install can avoid timeouts. " +
                            "See <a target='_blank' href='https://duplicator.com/knowledge-base/how-to-fix-installer-archive-extraction-issues/'>" +
                            "this section</a> of the FAQ for a description of how to do that.<br/><br/>"
                    }

                    result.errorContent.html += status;
                    DUPX.ajaxErrorDisplayHideError(result, textStatus, jqXHR);
                },
                {
                    retryOnFailure: true,
                    numberOfAttempts: 2,
                    delayRetryOnFailure: 5000,
                    callbackOnRetry: function (data, textStatus, jqXHR, options) {
                        retryAttemp++;

                        DUPX.progress.update({
                            'notice': 'Db install failed: ' + data.message + ',<br>' +
                                    'wait ' + (options.delayRetryOnFailure / 1000) + ' seconds and retry.<br>' +
                                    '<b>' + DUPX.stringifyNumber(retryAttemp) + ' attempt</b>'
                        });
                        console.log('Callback on retry', data);
                    }
                }
        );

    };

</script>
