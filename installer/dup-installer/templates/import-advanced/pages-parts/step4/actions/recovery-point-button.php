<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
$recoveryLink  = $paramsManager->getValue(PrmMng::PARAM_RECOVERY_LINK);

if (empty($recoveryLink)) {
    return;
}
?>
<div class="margin-top-2 margin-bottom-2" >
<hr class="separator dotted">
<div class="sub-title">Restore Options</div>
<div class="flex-final-button-wrapper" >
    <div class="button-wrapper" >
        <a href="<?php echo DUPX_U::esc_url($recoveryLink); ?>" class="secondary-btn" target="_blank">
            <i class="fas fa-undo-alt"></i> Run Recovery Wizard
        </a> 
    </div>
    <div class="content-wrapper" >
        The new import has finished. 
        Optionally this site can be restored to its previous recovery point by running the recovery wizard.
        If no recovery is needed then this option can be ignored.
    </div>
</div>
<hr class="separator dotted">
</div>