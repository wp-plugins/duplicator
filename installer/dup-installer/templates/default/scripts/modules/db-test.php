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

if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) {
    $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
    $ovr_dbhost    = $overwriteData['dbhost'];
    $ovr_dbname    = $overwriteData['dbname'];
    $ovr_dbuser    = $overwriteData['dbuser'];
    $ovr_dbpass    = $overwriteData['dbpass'];
} else {
    $ovr_dbhost = '';
    $ovr_dbname = '';
    $ovr_dbuser = '';
    $ovr_dbpass = '';
}
?>
<script>
    const dbViewModeInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_DB_VIEW_MODE)); ?>;
    const dbHostInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_DB_HOST)); ?>;
    const dbNameInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_DB_NAME)); ?>;
    const dbUserInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_DB_USER)); ?>;
    const dbPassInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_DB_PASS)); ?>;
    const dbActionInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_DB_ACTION)); ?>;

    DUPX.basicDBActionChange = function ()
    {
        var action = $('#' + dbActionInputId).val();
        $('.s2-basic-pane .s2-warning-manualdb').hide();
        $('.s2-basic-pane .s2-warning-emptydb').hide();
        $('.s2-basic-pane .s2-warning-renamedb').hide();
        switch (action)
        {
            case 'create'  :
                break;
            case 'empty'   :
                $('.s2-basic-pane .s2-warning-emptydb').show(300);
                break;
            case 'rename'  :
                $('.s2-basic-pane .s2-warning-renamedb').show(300);
                break;
            case 'manual'  :
                $('.s2-basic-pane .s2-warning-manualdb').show(300);
                break;
        }
    };

    //DOCUMENT INIT
    $(document).ready(function ()
    {
        $("#" + dbActionInputId).on("change", DUPX.basicDBActionChange);
        DUPX.basicDBActionChange();

        DUPX.checkOverwriteParameters = function (dbhost, dbname, dbuser, dbpass)
        {
            $("#" + dbHostInputId).val(<?php echo SnapJson::jsonEncode($ovr_dbhost); ?>).prop('readonly', true);
            $("#" + dbNameInputId).val(<?php echo SnapJson::jsonEncode($ovr_dbname); ?>).prop('readonly', true);
            $("#" + dbUserInputId).val(<?php echo SnapJson::jsonEncode($ovr_dbuser); ?>).prop('readonly', true);
            $("#" + dbPassInputId).val(<?php echo SnapJson::jsonEncode($ovr_dbpass); ?>).prop('readonly', true);
            $("#s2-db-basic-setup").show();
        };

        DUPX.fillInPlaceHolders = function ()
        {
            $("#" + dbHostInputId).attr('placeholder', <?php echo SnapJson::jsonEncode($ovr_dbhost); ?>).prop('readonly', false);
            $("#" + dbNameInputId).attr('placeholder', <?php echo SnapJson::jsonEncode($ovr_dbname); ?>).prop('readonly', false);
            $("#" + dbUserInputId).attr('placeholder', <?php echo SnapJson::jsonEncode($ovr_dbuser); ?>).prop('readonly', false);
            $("#" + dbPassInputId).attr('placeholder', <?php echo SnapJson::jsonEncode($ovr_dbpass); ?>).prop('readonly', false);
        };

        DUPX.resetParameters = function ()
        {
            $("#" + dbHostInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbNameInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbUserInputId).val('').attr('placeholder', '').prop('readonly', false);
            $("#" + dbPassInputId).val('').attr('placeholder', '').prop('readonly', false);
        };

        <?php if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) : ?>
            DUPX.fillInPlaceHolders();
        <?php endif; ?>
    
        $('#' + dbViewModeInputId).change(function () {
            switch ($(this).val()) {
                case 'cpnl':
                    $('.s2-cpnl-pane').removeClass('no-display');
                    $('.s2-basic-pane').addClass('no-display');
                    $('#validate-button').attr('disabled', 'true');
                    break;
                case 'basic':
                default:
                    $('.s2-cpnl-pane').addClass('no-display');
                    $('.s2-basic-pane').removeClass('no-display');
                    $('#validate-button').removeAttr('disabled');
                    break;
            }
        });
    });
</script>
