<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Utils;

$paramsManager = PrmMng::getInstance();
?>
<div class="help-target">
    <?php //DUPX_View_Funcs::helpIconLink('step3'); ?>
</div>
<div class="hdr-sub3">
    WP-Config File Setup
    <sup
        class="pro-flag pro-flag-close"
        data-tooltip-title="Upgrade Features"
        data-tooltip="<?php echo DUPX_U::esc_attr(
            '<p>Quickly and easily edit all your WordPress wp-config.php settings directly from the installer with Duplicator Pro</p>' .
            Utils::getCampainUrlHtml('wpconfig')
        ); ?>"
        aria-expanded="false">*
    </sup>
</div>
<div  class="dupx-opts">
    <?php
    if (DUPX_InstallerState::isRestoreBackup()) {
        dupxTplRender('parts/restore-backup-mode-notice');
    } else {
        ?>

        <small>
            See the <a href="https://wordpress.org/support/article/editing-wp-config-php/" target="_blank">WordPress documentation</a>
            for more information and specifications.  All items are fully editable in Duplicator Pro.
        </small>

        <div class="hdr-sub3 margin-top-2">CONTENT <small class="silver">Posts/Pages</small></div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISALLOW_FILE_EDIT);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISALLOW_FILE_MODS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_AUTOSAVE_INTERVAL);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_POST_REVISIONS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_EMPTY_TRASH_DAYS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE);
        ?>
        <div class="hdr-sub3 margin-top-2">SECURITY</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_FORCE_SSL_ADMIN);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_GEN_WP_AUTH_KEY);
        ?>
        <div class="hdr-sub3 margin-top-2">CRON</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_ALTERNATE_WP_CRON);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_DISABLE_WP_CRON);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT);
        ?>
        <div class="hdr-sub3 margin-top-2">DEBUG</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG_LOG);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DEBUG_DISPLAY);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_SCRIPT_DEBUG);
        ?>
        <div class="hdr-sub3 margin-top-2">SYSTEM</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_CACHE);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WPCACHEHOME);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_MEMORY_LIMIT);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT);
        ?>
        <div class="hdr-sub3 margin-top-2">GENERAL</div>
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_MYSQL_CLIENT_FLAGS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_CONCATENATE_SCRIPTS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_SAVEQUERIES);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_WP_CONF_WP_TEMP_DIR);
    }
    ?>
</div>