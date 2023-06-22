<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();
$processedStr  = DUP_Extraction::getInitialFileProcessedString();
?>
<script>
    DUPX.startAjaxExtraction = function (isTheFirstCall, successCallback)
    {
        if (isTheFirstCall) {
            DUPX.pageComponents.resetTopMessages().showProgress({
                'title': 'Extracting Archive Files',
                'perc': '0%',
                'secondary': <?php echo json_encode($processedStr); ?>,
                'bottomText':
                        '<i>Keep this window open during the extraction process.</i><br/>' +
                        '<i>This can take several minutes.</i>'
            });
        }

        let extractionAction = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_EXTRACTION); ?>;
        let extractionToken = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_EXTRACTION)); ?>;

        let retryAttemp = 0;

        DUPX.StandardJsonAjaxWrapper(
                extractionAction,
                extractionToken,
                {},
                function (data, textStatus, jqXHR) {
                    DUPX.progress.update({
                        'perc': data.actionData.perc,
                        'secondary': data.actionData.processedFiles,
                        'notice': ''
                    });

                    switch (data.actionData.pass) {
                        case 1:
                            if (typeof successCallback === "function") {
                                successCallback(data);
                            } else {
                                alert('extraction complete');
                            }
                            break;
                        case - 1:
                            DUPX.startAjaxExtraction(false, successCallback);
                            break;
                        default:
                            const result = {
                                'success': false,
                                'message': 'Unknow pass value :' + data.actionData.pass,
                                'errorContent': {
                                    'pre': '',
                                    'html': ''
                                },
                                'actionData': null
                            };
                            DUPX.ajaxErrorDisplayHideError(result, textStatus, jqXHR);
                            return false;
                    }

                    return true;
                },
                function (result, textStatus, jqXHR) {
                    let default_timeout_message = '';
                    let status = "<b>Server Code:</b> " + jqXHR.status + "<br/>";

                    status += "<b>Status:</b> " + jqXHR.statusText + "<br/>";

                    if (textStatus && textStatus.toLowerCase() == "timeout" || textStatus.toLowerCase() == "service unavailable") {
                        default_timeout_message = "<b>Recommendation:</b><br/>";
                        default_timeout_message += "See <a target='_blank' href='" + 
                            "https://duplicator.com/knowledge-base/how-to-handle-server-timeout-issues/'>";
                        default_timeout_message += "this FAQ item</a> for possible resolutions.";
                        default_timeout_message += "<hr>";
                        default_timeout_message += "<b>Additional Resources...</b><br/>";
                        default_timeout_message += "With thousands of different permutations it's difficult to try and debug/diagnose a server. " + 
                            "If you're running into timeout issues and need help we suggest you follow these steps:<br/><br/>";
                        default_timeout_message += "<ol>";
                        default_timeout_message += "<li><strong>Contact Host:</strong> Tell your host that you're running into PHP/Web " + 
                            "Server timeout issues and ask them if they have any recommendations</li>";
                        default_timeout_message += "<li><strong>Dedicated Help:</strong> " + 
                            "If you're in a time-crunch we suggest that you contact " + 
                            "<a target='_blank' href='https://duplicator.com/knowledge-base/how-should-i-get-help-for-each-duplicator-product/'>" +
                            "professional server administrator</a>. A dedicated resource like this will be able to work with you around " + 
                            "the clock to the solve the issue much faster than we can in most cases.</li>";
                        default_timeout_message += "<li><strong>Consider Upgrading:</strong> If you're on a budget host then you may run into constraints. " + 
                            "If you're running a larger or more complex site it might be worth upgrading to a " + 
                            "<a target='_blank' href='https://duplicator.com/knowledge-base/how-to-handle-server-timeout-issues/'>" + 
                            "managed VPS server</a>. These systems will pretty much give you full control to use the software without constraints and " + 
                            "come with excellent support from the hosting company.</li>";
                        default_timeout_message += "<li><strong>Contact SnapCreek:</strong> We will try our best to help configure and " + 
                            "point users in the right direction, however these types of issues can be time-consuming and " + 
                            "can take time from our support staff.</li>";
                        default_timeout_message += "</ol>";

                        if (page)
                        {
                            switch (page)
                            {
                                default:
                                    status += default_timeout_message;
                                    break;
                                case 'extract':
                                    status += "<b>Recommendation:</b><br/>";
                                    status += "See <a target='_blank' href='https://duplicator.com/knowledge-base/how-to-handle-various-install-scenarios'>" + 
                                        "this FAQ item</a> for possible resolutions.<br/><br/>";
                                    break;
                                case 'ping':
                                    status += "<b>Recommendation:</b><br/>";
                                    status += "See " + 
                                        "<a target='_blank' href='https://duplicator.com/knowledge-base/how-should-i-get-help-for-each-duplicator-product/'>" + 
                                        "this FAQ item</a> for possible resolutions.<br/><br/>";
                                    break;
                                case 'delete-site':
                                    status += "<b>Recommendation:</b><br/>";
                                    status += "See " + 
                                        "<a target='_blank' href='https://duplicator.com/knowledge-base/how-to-resolve-403-500-timeout-issues-on-step-3/'>" + 
                                        "this FAQ item</a> for possible resolutions.<br/><br/>";
                                    break;
                            }
                        } else
                        {
                            status += default_timeout_message;
                        }

                    } else if ((jqXHR.status == 403) || (jqXHR.status == 500)) {
                        status += "<b>Recommendation:</b><br/>";
                        status += "See " + 
                            "<a target='_blank' href='https://duplicator.com/knowledge-base/how-to-resolve-403-500-timeout-issues-on-step-3'>" + 
                            "this FAQ item</a> for possible resolutions.<br/><br/>"
                    } else if ((jqXHR.status == 0) || (jqXHR.status == 200)) {
                        status += "<b>Recommendation:</b><br/>";
                        status += "Possible server timeout! Performing a 'Manual Extraction' can avoid timeouts.";
                        status += "See " + 
                            "<a target='_blank' href='https://duplicator.com/knowledge-base/how-to-handle-various-install-scenarios'>" + 
                            "this FAQ item</a> for a complete overview.<br/><br/>"
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
                            'notice': 'Extraction failed: ' + data.message + ',<br>' +
                                    'wait ' + (options.delayRetryOnFailure / 1000) + ' seconds and retry.<br>' +
                                    '<b>' + DUPX.stringifyNumber(retryAttemp) + ' attempt</b>'
                        });
                        console.log('Callback on retry', data);
                    }
                }
        );
    };
</script>
