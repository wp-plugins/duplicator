<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $packageDays int */
/* @var $maxPackageDays int */
?><p>
    This package is <?php echo $packageDays; ?> day(s) old. 
    Packages older than <?php echo $maxPackageDays; ?> days might be considered stale.  It is recommended to build a new
    package unless your aware of the content and its data.  This is message is simply a recommendation.
</p>