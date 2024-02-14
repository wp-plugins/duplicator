<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div class="help-target">
    <?php //DUPX_View_Funcs::helpIconLink('step2'); ?>
</div> 
<div  class="dupx-opts">
    <?php
    if (DUPX_InstallerState::isRestoreBackup()) {
        dupxTplRender('parts/restore-backup-mode-notice');
    } else {
        ?>
    <div class="hdr-sub3">
        Charset & Collation
    </div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_CHARSET);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_COLLATE);
    }
    ?>
</div>
