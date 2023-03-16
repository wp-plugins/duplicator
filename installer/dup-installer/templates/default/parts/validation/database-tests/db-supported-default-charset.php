<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...] */
/* @var $charsetOk bool */
/* @var $collateOk bool */
/* @var $sourceCharset string */
/* @var $sourceCollate string */
/* @var $usedCharset string */
/* @var $usedCollate string */
/* @var $errorMessage string */

$statusClass = ($testResult === DUPX_Validation_abstract_item::LV_FAIL || !$charsetOk || !$collateOk) ? 'red' : 'green';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($testResult === DUPX_Validation_abstract_item::LV_FAIL) { ?>
        It is not possible to read the list of available charsets in the database.<br>
        Message: <?php echo $errorMessage; ?>
    <?php } elseif (!$charsetOk) { ?>
        This server's database does not support the source site's character set [<b><?php echo $sourceCharset; ?></b>], 
        so the installer is going to use default character [<b><?php echo $usedCharset; ?></b>].
    <?php } elseif (!$collateOk) { ?>
        This server's database does not support the source site's collate [<b><?php echo $sourceCollate; ?></b>], 
        so the installer is going to use default collate of current charset [<b><?php echo $usedCollate; ?></b>].
    <?php } else { ?>
        The current server supports the source site's charset [<b><?php echo $sourceCharset; ?></b>] 
        and Collate [<b><?php echo empty($sourceCollate) ? 'default' : $sourceCollate; ?></b>] 
        (set in the wp-config file).<br>
    <?php } ?>
</p>

<div class="sub-title">DETAILS</div>
<p>
    <i>Settings used in the current installation</i><br>
    <i>DB_CHARSET = <b><?php echo $usedCharset; ?></b></i><br>
    <i>DB_COLLATE = <b><?php echo $usedCollate; ?></b></i>
<p>
<p>
    DB_CHARSET and DB_COLLATE are set in wp-config.php 
    (see: <a href="https://wordpress.org/support/article/editing-wp-config-php/#database-character-set" target="_blank">Editing wp-config.php</a> ).<br>
    When the charset or collate of the source site is not supported in the database of the target site, the default is automatically set.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>In case the default charset/collates are not the desired ones you can <b>change the setting</b> in the <b>advanced installation mode</b>.</li>
</ul>
