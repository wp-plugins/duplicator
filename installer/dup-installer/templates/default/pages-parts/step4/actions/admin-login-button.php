<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
$safe_mode     = $paramsManager->getValue(PrmMng::PARAM_SAFE_MODE);
?>
<div class="flex-final-button-wrapper" >
    <div class="button-wrapper" >
        <a id="s4-final-btn" class="default-btn" href="<?php echo htmlspecialchars(DUPX_InstallerState::getAdminLogin()); ?>" target="_blank">
            <i class="fab fa-wordpress"></i> Admin Login
        </a>
    </div>
    <div class="content-wrapper" >
        Click the Admin Login button to login and finalize this install.<br />
        <?php $paramsManager->getHtmlFormParam(PrmMng::PARAM_AUTO_CLEAN_INSTALLER_FILES); ?>
    </div>
</div>

<!-- WARN: SAFE MODE MESSAGES -->
<div class="s4-warn final-step-warn-item" style="display:<?php echo ($safe_mode > 0 ? 'block' : 'none') ?>">
    <b><i class="fas fa-exclamation-triangle"></i> SAFE MODE:</b>
    Safe mode has <u>deactivated</u> all plugins. Please be sure to enable your plugins after logging in.
    <i>
        If you notice that problems arise when activating
        the plugins then active them one-by-one to isolate the plugin that could be causing the issue.
    </i>
</div>