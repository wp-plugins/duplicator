<?php

use Duplicator\Libs\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */
?>

<div class="advanced-storages-popup-content">
    <img src="<?php echo esc_url(DUPLICATOR_PLUGIN_URL . "assets/img/duplicator-header-logo.svg"); ?>" />
    <?php esc_html_e('Store to Multiple Endpoints with Duplicator Pro', 'duplicator'); ?>
    <ul>
        <?php foreach ($tplData['storages'] as $storage) : ?>
            <li><i class="fab <?php echo esc_attr($storage['fa-class']); ?> fa-fw"></i>&nbsp;<?php echo esc_html($storage['title']); ?></li>
        <?php endforeach; ?>
    </ul>
    <i>
        <?php esc_html_e('Set up one-time storage locations and automatically push the package to your destination.', 'duplicator'); ?><br>
    </i>
    <p>
        <a href="<?php echo esc_url(Upsell::getCampaignUrl($tplData['utm_medium'], 'Popup Upgrade Now')); ?>"
           target="_blank"
           class="dup-btn dup-btn-green dup-btn-lg" style="padding: 10px 28px;">
            <?php esc_html_e('Upgrade Now', 'duplicator'); ?>
        </a>
    </p>
</div>

