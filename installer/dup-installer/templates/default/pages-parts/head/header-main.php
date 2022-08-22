<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

// @var $showInstallerMode bool
// @var $showSwitchView bool

//$showInstallerMode = !isset($showInstallerMode) ? true : $showInstallerMode;
$showInstallerMode = false;
$showSwitchView    = !isset($showSwitchView) ? false : $showSwitchView;
$showInstallerLog  = !isset($showInstallerLog) ? false : $showInstallerLog;
?>
<div id="header-main-wrapper" >
    <div class="hdr-main">
        <?php echo $htmlTitle; ?>
    </div>
    <div class="hdr-secodary">
        <?php if ($showInstallerMode) { ?>
            <div class="dupx-modes">
                <?php echo DUPX_InstallerState::getInstance()->getHtmlModeHeader(); ?>
            </div>
            <?php
        }
        if ($showInstallerLog) {
            ?>
            <div class="installer-log" >
                <?php DUPX_View_Funcs::installerLogLink(); ?>
            </div>
            <?php
        }
        if ($showSwitchView) {
            dupxTplRender('pages-parts/step1/actions/switch-template');
        }
        ?>
    </div>
</div>