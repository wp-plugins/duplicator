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
    var siteProcessingReplaceLastChunkPos = 0;

    DUPX.siteProcessingReplaceData = function (isTheFirstCall, successCallback) {
        if (isTheFirstCall) {
            
            DUPX.pageComponents.resetTopMessages().showProgress({
                'title': 'Processing Data Replacement',
                'perc': '0%',
                'secondary': '',
                'bottomText':
                        '<i>Keep this window open during the replacement process.</i><br/>' +
                        '<i>This can take several minutes.</i>'
            });
        }

        let action = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_WEBSITE_UPDATE); ?>;
        let token = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_WEBSITE_UPDATE)); ?>;

        let retryAttemp = 0;

        DUPX.StandardJsonAjaxWrapper(
                action,
                token,
                {},
                function (data, textStatus, jqXHR) {
                    DUPX.progress.update({
                        'perc': data.actionData.step3.progress_perc + '%',
                        'secondary': ''
                    });

                    if (data.actionData.step3.chunk == 1) {
                        if (JSON.stringify(siteProcessingReplaceLastChunkPos) !== JSON.stringify(data.actionData.step3.chunkPos)) {
                            var siteProcessingReplaceLastChunkPos = data.actionData.step3.chunkPos;
                            DUPX.siteProcessingReplaceData(false, successCallback);
                        } else {
                            console.error('Chunk is stuck: ' + data.actionData);
                            let status = "<b>Server Code:</b> " + jqXHR.status + "<br/>";
                            status += "<b>Status:</b> " + jqXHR.statusText + "<br/>";
                            status += "<b>Response:</b> " + jqXHR.responseText + "<hr/>";
                            status += "Chunking is stuck<br>";

                            const result = {
                                'success': false,
                                'message': 'DB INSTALL ERROR: ' + data.actionData.error_msg,
                                'errorContent': {
                                    'pre': '',
                                    'html': status
                                },
                                'actionData': null
                            };
                            DUPX.ajaxErrorDisplayHideError(result, null, null);
                            return false;
                        }
                    } else if (data.actionData.step3.pass == 1) {
                        if (typeof successCallback === "function") {
                            successCallback(data.actionData);
                        } else {
                            alert('Processing complete');
                        }
                    } else {
                        DUPX.hideProgressBar();
                    }
                },
                DUPX.ajaxErrorDisplayHideError,
                {
                    retryOnFailure: true,
                    numberOfAttempts: 2,
                    delayRetryOnFailure: 5000,
                    callbackOnRetry: function (data, textStatus, jqXHR, options) {
                        retryAttemp++;

                        DUPX.progress.update({
                            'notice': 'failed processing data replacement: ' + data.message + ',<br>' +
                                    'wait ' + (options.delayRetryOnFailure / 1000) + ' seconds and retry.<br>' +
                                    '<b>' + DUPX.stringifyNumber(retryAttemp) + ' attempt</b>'
                        });
                        console.log('Callback on retry', data);
                    }
                }
        );
    };
</script>
