<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (DUPX_InstallerState::instTypeAvaiable(DUPX_InstallerState::INSTALL_SINGLE_SITE)) {
    $instTypeClass = 'install-type-' . DUPX_InstallerState::INSTALL_SINGLE_SITE;
} else {
    return;
}

$overwriteMode = (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL);
$display       = DUPX_InstallerState::getInstance()->isInstType(DUPX_InstallerState::INSTALL_SINGLE_SITE);
?>
<div class="overview-description <?php echo $instTypeClass . ($display ? '' : ' no-display'); ?>">
    <div class="details">
        <div class="help-icon">
            <i><?php DUPX_View_Funcs::helpLink('step1', '<i class="far fa-question-circle"></i>'); ?></i>
        </div>
        <table>
            <tr>
                <td>View:</td>
                <td>
                    Try
                    <span class="link-style" onclick="DUPX.blinkAnimation('s1-switch-template-btn-basic', 400, 3)">Basic</span>
                    <sup class="hlp-new-lbl">new</sup> or
                    <span class="link-style" onclick="DUPX.blinkAnimation('s1-switch-template-btn-advanced', 400, 3)">Advanced</span> views
                </td>
            </tr>
            <tr>
                <td>Status:</td>
                <td>Standard Single Site Setup</td>
            </tr>
            <tr>
                <td>Mode:</td>
                <td>
                    <?php 
                        echo $overwriteMode ? '<i class="fas fa-exclamation-triangle"></i>&nbsp;' : '';
                        echo DUPX_InstallerState::getInstance()->getHtmlModeHeader();
                        if ($overwriteMode) {
                            echo '<div class="overwrite">
                                     This will clear all site data and the current archive will be installed. This process cannot be undone!
                                  </div>';
                        }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div>