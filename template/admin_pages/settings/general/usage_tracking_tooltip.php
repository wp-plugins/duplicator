<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div>
    <b>
        <?php _e('All information sent to the server is anonymous.', 'duplicator'); ?><br>
        <?php _e('No information about storage or package\'s content are sent.', 'duplicator'); ?>
    </b>
</div>
<br>
<div>
    <?php
        _e(
            'Usage tracking for Duplicator helps us better understand our users and their website needs by looking 
            at a range of server and website environments.',
            'duplicator'
        );
        ?>
    <b>
        <?php _e('This allows us to continuously improve our product as well as our Q&A / testing process.', 'duplicator'); ?>
    </b>
    <?php _e('Below is the list of information that Duplicator collects as part of the usage tracking:', 'duplicator'); ?>
</div>
<ul>
    <li>
        <?php
        _e(
            '<b>PHP Version:</b> so we know which PHP versions we have to test against (no one likes whitescreens or log files full of errors).',
            'duplicator'
        );
        ?>
    </li>
    <li>
        <?php
        _e(
            '<b>WordPress Version:</b> so we know which WordPress versions to support and test against.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        <?php
        _e(
            '<b>MySQL Version:</b> so we know which versions of MySQL to support and test against for our custom tables.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        <?php
        _e(
            '<b>Duplicator Version:</b> so we know which versions of Duplicator are potentially responsible for issues when we get bug reports, 
            allowing us to identify issues and release solutions much faster.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        <?php
        _e(
            '<b>Plugins and Themes infos:</b> so we can figure out which ones I can generate compatibility errors with Duplicator.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        <?php
        _e(
            '<b>Site info:</b> General information about the site such as database, file size, number of users, and sites in case it is a multisite. 
            This is useful for us to understand the critical issues of package creation.',
            'duplicator'
        );
        ?>
    </li>
    <li>
        <?php
        _e(
            '<b>Packages infos:</b> Information about the packages created and the type of components included.',
            'duplicator'
        );
        ?>
    </li>
</ul>