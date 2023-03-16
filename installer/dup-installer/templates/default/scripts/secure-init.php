<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$nextStepPrams = array(
    PrmMng::PARAM_CTRL_ACTION => 'ctrl-step1',
    DUPX_Security::CTRL_TOKEN               => DUPX_CSRF::generate('ctrl-step1'),
    PrmMng::PARAM_STEP_ACTION => DUPX_CTRL::ACTION_STEP_INIZIALIZED
);
?>
<script>
    $(document).ready(function () {
        const secureAction = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_PWD_CHECK); ?>;
        const secureToken = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_PWD_CHECK)); ?>;
        const passForm = $('#i1-pass-form');
        /**
         * Submits the password for validation
         */
        DUPX.checkPassword = function ()
        {
            passForm.parsley().validate();
            if (!passForm.parsley().isValid()) {
                return;
            }
            var formData = passForm.serializeForm();

            DUPX.StandardJsonAjaxWrapper(
                    secureAction,
                    secureToken,
                    formData,
                    function (data) {
                        if (data.actionData) {
                            DUPX.redirect(DUPX.dupInstallerUrl, 'post', <?php echo SnapJson::jsonEncode($nextStepPrams); ?>);
                        } else {
                            $('#pwd-check-fail').show();
                        }
                    },
                    DUPX.ajaxErrorDisplayHideError
                    );
        };

        passForm.submit(function (event) {
            event.preventDefault();
            DUPX.checkPassword();
        });
    });
</script>