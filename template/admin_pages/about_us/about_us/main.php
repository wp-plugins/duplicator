<?php

/**
 * Template for About Us page
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>

<div class="wrap" id="dup-admin-about">
<?php
$tplMng->render('admin_pages/about_us/about_us/info');
$tplMng->render('admin_pages/about_us/about_us/extra_plugins');
?>
</div>
