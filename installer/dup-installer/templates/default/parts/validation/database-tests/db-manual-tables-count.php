<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $dbname string */
/* @var $numTables int */
/* @var $minNumTables int */
/* @var $errorMessage string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($isOk) { ?>
        This test passes.  A WordPress database looks to be setup.
    <?php } else { ?>
        The database [<?php echo htmlentities($dbname); ?>] has <?php echo $numTables; ?> tables. This does not look to be a valid WordPress database. 
        The base WordPress install has 12 tables. Please validate that this database is indeed pre-populated with a valid WordPress database. 
        The "Skip Database Extraction" mode requires that you have a valid WordPress database already installed.
    <?php } ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks if the database looks to represents a base WordPress install. Since this option is advanced it is left upto the user to
    have the correct database tables installed.
</p>



