<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Utils\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div id="duplicator-did-you-know">
    <i class="fas fa-info-circle"></i>
    <?php printf(__('Did you know Duplicator Pro has: %s?', 'duplicator'), $tplData['feature']);?>
    <a href="<?php echo Upsell::getCampaignUrl('scan_did-you-know', $tplData['feature']) ?>" target="_blank">
        <?php esc_html_e('Upgrade To Pro', 'duplicator'); ?>
    </a>
</div>