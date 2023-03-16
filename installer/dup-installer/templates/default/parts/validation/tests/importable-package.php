<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var int    $testResult  validation rest result enum
 * @var string $failMessage fail message
 */

$statusClass = ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING ? 'green' : 'red' );
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING) { ?>
        The package has all the elements to be imported.
    <?php } else { ?>
        The package can't be imported.
    <?php } ?>
</p>

<?php if (strlen($failMessage) > 0) { ?>
    <div class="sub-title">DETAILS</div>
    <p>
        <?php echo $failMessage; ?>
    </p>
<?php } ?>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        A package with filtered elements cannot be imported to avoid a malfunction of the current site.<br>
        Create a new package in the site you want to import deactivating the filters on tables and/or files.
    </li>
</ul>
