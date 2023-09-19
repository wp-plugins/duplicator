<?php

use Duplicator\Utils\Upsell;

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
            <li>
                <?php if (isset($storage['iconUrl'])) : ?>
                    <img src="<?php echo esc_url($storage['iconUrl']); ?>" class="storage-icon"/>
                <?php elseif (isset($storage['fa-class'])) : ?>
                    <i class="fab <?php echo esc_attr($storage['fa-class']); ?> fa-fw"></i>&nbsp;
                <?php endif; ?>
                <?php echo esc_html($storage['title']); ?></li>
        <?php endforeach; ?>
    </ul>
    <i>
        <?php esc_html_e('Set up one-time storage locations and automatically push the package to your destination.', 'duplicator'); ?><br>
    </i>
    <p>
        <a href="<?php echo esc_url(Upsell::getCampaignUrl($tplData['utm_medium'], 'Popup Upgrade Now')); ?>"
           target="_blank"
           id="dup-storage-upgrade-btn"
           class="dup-btn dup-btn-green dup-btn-lg" style="padding: 12px 40px;">
            <?php esc_html_e('Upgrade Now', 'duplicator'); ?>
        </a>
    </p>
</div>

