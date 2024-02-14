<?php

/**
 * @package Duplicator
 */

use Duplicator\Libs\Snap\SnapIO;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="filter-files-tab-content">
    <?php $tplMng->render('parts/filters/package_components', array(
        'package' => $tplData['package'],
    )); ?>
</div>
