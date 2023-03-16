<?php

/**
 * Template for getting started page
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Upsell;

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
TplMng::getInstance()->render('admin_pages/about_us/getting_started/first_package');

TplMng::getInstance()->render('admin_pages/about_us/getting_started/get_pro');
