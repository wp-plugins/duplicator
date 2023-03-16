<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var array{name: string,slug: string,more: string,pro: array{file: string}} $plugin
 */
$plugin = $tplData['plugin'];

/** @var string */
$installUrl = $tplData['installUrl'];
$moreUrl    = $plugin['more'] . '?' . http_build_query(array(
    'utm_medium' => 'link',
    'utm_source'   => 'duplicatorplugin',
    'utm_campaign' => 'duplicatordashboardwidget'
));

?>
<div class="dup-section-recommended">
    <hr>
    <div class="dup-flex-content" >
        <div>
            <span class="dup-recommended-label">
                <?php esc_html_e('Recommended Plugin:', 'duplicator-pro'); ?>
            </span>
            <b><?php echo esc_html($plugin['name']); ?></b>
            -
            <span class="action-links">
                <?php if (current_user_can('install_plugins') && current_user_can('activate_plugins')) { ?>
                    <a href="<?php echo esc_url($installUrl); ?>"><?php esc_html_e('Install', 'wpforms-lite'); ?></a>
                <?php } ?>
                <a href="<?php echo esc_url($moreUrl); ?>" target="_blank" ><?php
                    esc_html_e('Learn More', 'duplicator-pro');
                ?></a>
            </span>
        </div>
        <div>
            <button type="button" id="dup-dash-widget-section-recommended" title="<?php esc_html_e('Dismiss recommended plugin', 'duplicator-pro'); ?>">
                <span class="dashicons dashicons-no-alt"></span>
            </button>
        </div>
    </div>
</div>