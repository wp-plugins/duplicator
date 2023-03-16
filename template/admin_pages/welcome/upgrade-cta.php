<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="upgrade-cta upgrade">
    <div class="block dup-clearfix">
        <div class="">
            <h2><?php esc_html_e('Upgrade to PRO', 'duplicator'); ?></h2>
            <ul>
                <?php foreach (\Duplicator\Libs\Upsell::getCalloutCTAFeatureList() as $feature) : ?>
                    <li>
                        <span class="dashicons dashicons-yes"></span> <?php echo esc_html($feature); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="">
            <a href="<?php echo esc_url(\Duplicator\Libs\Upsell::getCampaignUrl('welcome-page', 'Upgrade Now')); ?>"
               rel="noopener noreferrer"
               target="_blank"
               class="dup-btn dup-btn-block dup-btn-lg dup-btn-green">
                <?php esc_html_e('Upgrade Now', 'duplicator'); ?>
            </a>
        </div>
    </div>
</div>