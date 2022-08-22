<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?>
<div id="tabs-1">
    <?php dupxTplRender('pages-parts/step1/info-tabs/overview-description'); ?>
    <div class="margin-top-1" >
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_INST_TYPE);
        ?>
    </div>
</div>