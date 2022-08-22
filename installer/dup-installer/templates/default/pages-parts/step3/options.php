<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
$wpConfig      = $paramsManager->getValue(PrmMng::PARAM_WP_CONFIG);
$skipWpConfig  = ($wpConfig == 'nothing' || $wpConfig == 'original');
?>
<!-- ==========================
OPTIONS -->
<div class="hdr-sub1" >
    Options
</div>
<!-- START TABS -->
<div class="hdr-sub1-area tabs-area">
    <div id="tabs" class="no-display">
        <ul>
            <li><a href="#tabs-search-rules">Engine</a></li>
            <li><a href="#tabs-admin-account">Admin Account</a></li>
            <li><a href="#tabs-plugins">Plugins</a></li>
            <?php if (!$skipWpConfig) { ?>
                <li><a href="#tabs-wp-config-file">WP-Config</a></li>
            <?php } ?>
        </ul>

        <!-- =====================
        SEARCH RULES TAB -->
        <div id="tabs-search-rules">
            <?php dupxTplRender('pages-parts/step3/options-tabs/search-rules'); ?>
        </div>

        <!-- =====================
        ADMIN TAB -->
        <div id="tabs-admin-account">
            <?php dupxTplRender('pages-parts/step3/options-tabs/users'); ?>
        </div>

        <!-- =====================
        PLUGINS  TAB -->
        <div id="tabs-plugins">
            <?php dupxTplRender('pages-parts/step3/options-tabs/plugins'); ?>
        </div>
        <?php if (!$skipWpConfig) { ?>
            <!-- =====================
            WP-CONFIG TAB -->
            <div id="tabs-wp-config-file">
                <?php dupxTplRender('pages-parts/step3/options-tabs/wp-config'); ?>
            </div>
        <?php } ?>
    </div>
</div>
