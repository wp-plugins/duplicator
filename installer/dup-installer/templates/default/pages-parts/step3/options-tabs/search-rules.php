<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Upsell;

$paramsManager = PrmMng::getInstance();
if (DUPX_InstallerState::isRestoreBackup()) {
    ?>
    <div class="hdr-sub3">Search & replace settings</div>
    <?php
    dupxTplRender('parts/restore-backup-mode-notice');
    return;
}
?>
<div class="help-target">
    <?php //DUPX_View_Funcs::helpIconLink('step3'); ?>
</div>

<div class="hdr-sub3">
    Custom Search and Replace
    <sup class="pro-flag pro-flag-close"
        data-tooltip-title="Upgrade Features"
        data-tooltip="<?php echo DUPX_U::esc_attr(
            '<p>Enhance the install experiance with custom search and replace features.</p>' .
            Upsell::getCampaignTooltipHTML(array('utm_medium' => 'installer', 'utm_content' => "Custom Search and Replace"))
        ); ?>">*
    </sup>
</div>
<p style="text-align: center;" >
    Add additional search and replace URLs to replace additional data.<br/>
    <i>This option is available in Duplicator Pro.</i>
</p>

<div class="hdr-sub3 margin-top-2">Database Scan Options</div>
<div  class="dupx-opts">
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_SKIP_PATH_REPLACE);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_EMAIL_REPLACE);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_FULL_SEARCH);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_POSTGUID);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_MAX_SERIALIZE_CHECK);
    ?>
</div>




