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
<?php
if (DUPX_InstallerState::isRestoreBackup()) {
    ?>
    <div class="hdr-sub3">User settings</div>
    <?php
    dupxTplRender('parts/restore-backup-mode-notice');
} else {
    dupxTplRender('pages-parts/step3/usersParts/usersPwdReset');
    dupxTplRender('pages-parts/step3/usersParts/newAdminUser');
}
