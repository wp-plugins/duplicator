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
<div class="hdr-sub3">Extraction Settings</div>
<div class="help-target">
    <?php // DUPX_View_Funcs::helpIconLink('step2');  ?>
</div>
<?php
if (DUPX_InstallerState::isRestoreBackup()) {
    dupxTplRender('parts/restore-backup-mode-notice');
}
if (DUPX_Custom_Host_Manager::getInstance()->isManaged()) {
    $paramsManager->setFormNote(PrmMng::PARAM_DB_TABLE_PREFIX, 'The table prefix must be set according to the managed hosting where you install the site.');
}
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_TABLE_PREFIX);
?>
<div class="param-wrapper" >
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_MYSQL_MODE);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_MYSQL_MODE_OPTS);
    ?>
</div>
<?php
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_ENGINE);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_SPLIT_CREATES);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_VIEW_CREATION);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_PROC_CREATION);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_FUNC_CREATION);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_REMOVE_DEFINER);
