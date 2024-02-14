<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$archiveConfig = DUPX_ArchiveConfig::getInstance();

/* Variables */
/* @var $paramView string */
/* @var $bodyId string */
/* @var $bodyClasses string */
?><!DOCTYPE html>
<html>
    <head>
        <?php dupxTplRender('pages-parts/head/meta'); ?>
        <title>Duplicator</title>
        <?php dupxTplRender('pages-parts/head/css-scripts'); ?>
        <?php dupxTplRender('pages-parts/head/css-template-custom'); ?>
    </head>
    <?php
    dupxTplRender('pages-parts/body/body-tag', array(
        'bodyId'      => $bodyId,
        'bodyClasses' => $bodyClasses
    ));
    ?>
    <div id="content">
        <?php
        dupxTplRender('parts/top-header.php', array(
            'paramView' => $paramView
        ));
        if (!isset($skipTopMessages) || $skipTopMessages !== true) {
            dupxTplRender('parts/top-messages.php');
        }
