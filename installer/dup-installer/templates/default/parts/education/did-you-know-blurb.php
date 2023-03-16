<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Upsell;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string[] $features
 */
$feature = $features[array_rand($features)];
?>
<div id="duplicator-did-you-know" class="no-display">
    <i class="fas fa-info-circle"></i>Did you know Duplicator Pro has: <?php echo $feature; ?>?
    <a href="<?php echo Upsell::getCampaignUrl('scan_did-you-know', $feature);?>" target="_blank">
        Upgrade To Pro
    </a>
    <?php PrmMng::getInstance()->getHtmlFormParam(PrmMng::PARAM_SUBSCRIBE_EMAIL); ?>
</div>