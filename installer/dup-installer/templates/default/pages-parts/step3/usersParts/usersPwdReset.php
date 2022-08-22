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
<div class="hdr-sub3">Admin Password Reset</div>
<div class="dupx-opts">
    <?php
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_USERS_PWD_RESET);
    ?>
</div>