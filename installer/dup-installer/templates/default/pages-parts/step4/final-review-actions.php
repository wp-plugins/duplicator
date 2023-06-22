<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapURL;

$paramsManager = PrmMng::getInstance();
$nManager      = DUPX_NOTICE_MANAGER::getInstance();
$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>

<div class="sub-title">
    <b>Review</b>
</div>
<ul class="final-review-actions" >
    <li>
        Review this site's <a href="<?php echo DUPX_U::esc_url($paramsManager->getValue(PrmMng::PARAM_URL_NEW)); ?>" target="_blank">front-end</a>
        <!--or re-run the installer and
        <span class="link-style" data-go-step-one-url="<?php echo SnapURL::urlEncodeAll(DUPX_CSRF::getVal('installerOrigCall')); ?>" >
            go back to step 1
        </span> -->
    </li>
    <li>
        <?php
        $wpconfigNotice = $nManager->getFinalReporNoticeById('wp-config-changes');
        $htaccessNorice = $nManager->getFinalReporNoticeById('htaccess-changes');
        ?>
        Review the <?php echo $wpconfigNotice->longMsg; ?> and <?php echo $htaccessNorice->longMsg; ?>
    </li>
    <li>
        For additional help visit the <a href='<?php echo DUPX_Constants::FAQ_URL; ?>' target='_blank'>online FAQs</a>
    </li>
</ul>