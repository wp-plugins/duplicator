<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();

$nextStepPrams = array(
    PrmMng::PARAM_CTRL_ACTION => 'ctrl-step3',
    DUPX_Security::CTRL_TOKEN               => DUPX_CSRF::generate('ctrl-step3')
);
?><script>
    $("#tabs").tabs({
        create: function (event, ui) {
            $("#tabs").removeClass('no-display');
        }
    });

    DUPX.beforeUnloadCheck(true);

    DUPX.runDeployment = function () {
        //Validate input data
        var formInput = $('#s2-input-form');

        DUPX.sendParamsStep2(formInput, function () {
            DUPX.startAjaxDbInstall(true, function () {
                DUPX.redirect(DUPX.dupInstallerUrl, 'post', <?php echo SnapJson::jsonEncode($nextStepPrams); ?>);
            });
        });
    };
</script>
