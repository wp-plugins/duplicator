<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Tests\WP\TestsExecuter;
use Duplicator\Libs\Snap\SnapJson;


?>
<script>
    DUPX.finalTests = {
        frontendTest: <?php echo json_encode(TestsExecuter::getFrontendUrl()); ?>,
        backendTest: <?php echo json_encode(TestsExecuter::getBackendUrl()); ?>,
        afterCallback: null,
        testsResults: {
            'wp_frontend': {
                'success': false
            },
            'wp_backend': {
                'success': false
            }
        },
        test: function (doneCallback) {
            this.doneCallback = doneCallback;
            DUPX.finalTests.prepareTest();
        },
        prepareTest: function () {
            DUPX.pageComponents.showProgress({
                'title': 'Final WordPress Tests',
                'bottomText':
                        '<i>Keep this window open.</i><br/>' +
                        '<i>This can take several minutes.</i>'
            });

            let action = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_FINAL_TESTS_PREPARE); ?>;
            let token = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_FINAL_TESTS_PREPARE)); ?>;

            DUPX.StandardJsonAjaxWrapper(
                    action,
                    token,
                    {},
                    function (data) {
                        console.log(data);
                        console.log('link frontend', DUPX.finalTests.frontendTest);
                        console.log('link backend', DUPX.finalTests.backendTest);

                        DUPX.finalTests.executeTest(DUPX.finalTests.frontendTest, DUPX.finalTests.testsResults.wp_frontend, function () {
                            DUPX.finalTests.executeTest(DUPX.finalTests.backendTest, DUPX.finalTests.testsResults.wp_backend, DUPX.finalTests.cleanTest);
                        });
                    },
                    DUPX.ajaxErrorDisplayHideError,
                    {
                        timeOut: 10000
                    }
            );
        },
        executeTest: function (urlTest, resultData, doneCallback) {
            jQuery.ajax({
                type: "GET",
                url: urlTest,
                dataType: "json",
                timeout: 10000,
                success: function (result, textStatus, jqXHR) {
                    resultData.success = result;

                    if (typeof doneCallback === "function") {
                        doneCallback(resultData);
                    } else {
                        alert('test complete');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    resultData.success = false;

                    if (typeof doneCallback === "function") {
                        doneCallback(resultData);
                    } else {
                        alert('test complete');
                    }
                }
            });
        },
        cleanTest: function () {
            console.log('tests results', DUPX.finalTests.testsResults);
            DUPX.pageComponents.showProgress({
                'title': 'Cleanup Tests',
                'bottomText':
                        '<i>Keep this window open.</i><br/>' +
                        '<i>This can take several minutes.</i>'
            });

            let action = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_FINAL_TESTS_AFTER); ?>;
            let token = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_FINAL_TESTS_AFTER)); ?>;

            DUPX.StandardJsonAjaxWrapper(
                    action,
                    token,
                    {},
                    function (data) {
                        if (typeof DUPX.finalTests.doneCallback === "function") {
                            DUPX.finalTests.doneCallback();
                        }
                    },
                    DUPX.ajaxErrorDisplayHideError,
                    {
                        timeOut: 10000
                    }
            );
        }
    };
</script>
