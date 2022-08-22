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
<div class="help-target">
    <?php //DUPX_View_Funcs::helpIconLink('step3'); ?>
</div>
<div class="hdr-sub3"> <b>Activate Plugins Settings</b></div>
<?php
if (DUPX_InstallerState::isRestoreBackup()) {
    dupxTplRender('parts/restore-backup-mode-notice');
}

$paramsManager->getHtmlFormParam(PrmMng::PARAM_PLUGINS);
