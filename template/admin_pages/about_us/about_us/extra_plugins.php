<?php

use Duplicator\Utils\ExtraPlugins\ExtraItem;
use Duplicator\Utils\ExtraPlugins\ExtraPluginsMng;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

defined('ABSPATH') || die();

if (!current_user_can('install_plugins')) {
    return;
}
?>
<div id="dup-admin-addons">
    <div id="dup-admin-addons-list">
        <div class="list">
            <?php
                ExtraPluginsMng::getInstance()->foreachCallback(function (ExtraItem $plugin) use ($tplMng) {
                    $tplMng->render(
                        'admin_pages/about_us/about_us/extra_plugin_item',
                        array('plugin' => $plugin->skipLite() ? $plugin->getPro() : $plugin)
                    );
                });
                ?>
        </div>
    </div>
</div>
