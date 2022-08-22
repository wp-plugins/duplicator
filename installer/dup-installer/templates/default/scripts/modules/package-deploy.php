<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<script>
    DUPX.multipleStepDeploy = function (formObj, nextStepPrams) {
        DUPX.sendParamsStep1(formObj, function () {
            DUPX.startAjaxExtraction(true, function (data) {
                DUPX.redirect(DUPX.dupInstallerUrl, 'post', nextStepPrams);
            });
        });
    };

    DUPX.oneStepDeploy = function (formObj, nextStepPrams) {
        DUPX.sendParamsStep1(formObj, function () {
            DUPX.startAjaxExtraction(true, function () {
                DUPX.startAjaxDbInstall(true, function () {
                    DUPX.siteProcessingReplaceData(true, function () {
                        DUPX.finalTests.test(function () {
                            DUPX.redirect(DUPX.dupInstallerUrl, 'post', nextStepPrams);
                        });
                    });
                });
            });
        });
    };
</script>