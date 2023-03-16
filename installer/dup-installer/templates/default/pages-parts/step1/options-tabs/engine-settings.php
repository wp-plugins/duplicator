<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div class="hdr-sub3 margin-top-2">Engine Settings</div>
<?php
$paramsManager->getHtmlFormParam(PrmMng::PARAM_ARCHIVE_ENGINE);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_ENGINE);
$paramsManager->getHtmlFormParam(PrmMng::PARAM_ZIP_THROTTLING);
