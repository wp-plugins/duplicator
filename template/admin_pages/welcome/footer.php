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

<div class="footer">
    <div class="block dup-clearfix">
        <div class="button-wrap dup-clearfix">
            <div class="left">
                <a href="<?php echo esc_url($tplData['packageNonceUrl']); ?>"
                   class="dup-btn dup-btn-block dup-btn-lg dup-btn-orange">
                    <?php esc_html_e('Create Your First Package', 'duplicator'); ?>
                </a>
            </div>
            <div class="right">
                <a href="<?php echo esc_url(\Duplicator\Utils\Upsell::getCampaignUrl('welcome-page', 'Upgrade to Duplicator Pro')); ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="dup-btn dup-btn-block dup-btn-lg dup-btn-trans-green">
                    <span class="underline">
                        <?php esc_html_e('Upgrade to Duplicator Pro', 'duplicator'); ?> <span
                            class="dashicons dashicons-arrow-right"></span>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>