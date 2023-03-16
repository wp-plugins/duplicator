<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Libs\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<tr>
    <th colspan="11">
        <div id="dup-packages-bottom-bar">
            <div class="icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="feature">
                <p><strong><?php _e('Upgrade to Pro to Unlock...', 'duplicator'); ?></strong></p>
                <p>
                    <?php echo $tplData['feature'];?>
                </p>
            </div>
            <div class="upsell">
                <a href="<?php echo Upsell::getCampaignUrl('packages_bottom-bar', $tplData['feature']) ?>"
                   class="dup-btn dup-btn-md dup-btn-green"
                   target="_blank">
                    <?php esc_html_e('Upgrade Now & Save!', 'duplicator'); ?>
                </a>
            </div>
            <div>
                <button type="button" id="dup-packages-bottom-bar-dismiss" title="Dismiss recommended plugin">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
        </div>
    </th>
</tr>
