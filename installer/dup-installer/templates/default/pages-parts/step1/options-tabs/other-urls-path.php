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
<div class="hdr-sub3">
    Secondary URLs and paths 
    <sup
        class="pro-flag pro-flag-close"
        data-tooltip-title="Upgrade Features"
        data-tooltip="<?php echo DUPX_U::esc_attr(
            '<p>Enhancements for full customization of all WordPress paths and URLs are available in Duplicator Pro.</p>' .
            Utils::getCampainUrlHtml('custom_urls_path_options')
        ); ?>">*
    </sup>
</div>

<div id="other-path-url-options">
    <small>*All of these options are configurable with Duplicator Pro.</small>
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_WP_CORE_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_SITE_URL);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_CONTENT_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_CONTENT_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_UPLOADS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_UPLOADS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_PLUGINS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_PLUGINS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_PATH_MUPLUGINS_NEW);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_URL_MUPLUGINS_NEW);
    ?>
</div>