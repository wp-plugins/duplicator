<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

/** Variables
 * @var int $tableCount
 */
$paramsManager = PrmMng::getInstance();
$recoveryLink  = PrmMng::getInstance()->getValue(PrmMng::PARAM_RECOVERY_LINK);
$txtTable      = $tableCount . ' table' . ($tableCount == 1 ? '' : 's');
?>
<div id="db-install-dialog-confirm" title="Install Confirmation" style="display:none">
    <p>
        <i>Run installer with these settings?</i>
    </p>

    <div class="hdr-sub3">
        Site Settings
    </div>
   
    <table class="margin-bottom-1 margin-left-1  dup-s1-confirm-dlg">
        <tr>
            <td>Install Type: &nbsp; </td>
            <td><?php echo DUPX_InstallerState::installTypeToString(); ?></td>
        </tr>
        <tr>
            <td>New URL:</td>
            <td><i id="dlg-url-new"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_URL_NEW)); ?></i></td>
        </tr>
        <tr>
            <td>New Path:</td>
            <td><i id="dlg-path-new"><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_PATH_NEW)); ?></i></td>
        </tr>
    </table> 

    <div class="hdr-sub3">
       Database Settings
    </div>
    <table class="margin-left-1 margin-bottom-1 dup-s1-confirm-dlg">
        <tr>
            <td>Server:</td>
            <td><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_HOST)); ?></td>
        </tr>
        <tr>
            <td>Name:</td>
            <td><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_NAME)); ?></td>
        </tr>
        <tr>
            <td>User:</td>
            <td><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_DB_USER)); ?></td>
        </tr>
        <tr>
            <td>Data:</td>
            <?php if ($tableCount > 0) : ?>
                <td class="maroon">
                    <?php echo $tableCount . " existing table" . ($tableCount == 1 ? '' : 's') . " will be overwritten or modified in the database"; ?>
                </td>
            <?php else : ?>
                <td>
                    No existing tables will be overwritten in the database
                </td>
            <?php endif; ?>
        </tr>
    </table>

    <?php if ($tableCount > 0) { ?>
        <div class="margn-bottom-1" >
            <small class="maroon">
                <i class="fas fa-exclamation-circle"></i>
                NOTICE: Be sure the database parameters are correct! This database contains <b><u><?php echo $txtTable; ?></u></b> that will be modified
                and/or removed! Only proceed if the data is no longer needed. Entering the wrong information WILL overwrite an existing database.
                Make sure to have backups of all your data before proceeding.
            </small>
        </div>
    <?php } ?>
</div>