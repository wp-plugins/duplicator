<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $errorMessage string */
/* @var $tooLongNewTableNames array */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($isOk) { ?>
        There are no table names whose length exceeds limit of 64 characters after adding prefix.
    <?php } else { ?>
        Some table names exceed limit of 64 characters after adding prefix.
    <?php } ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>


<div class="sub-title">DETAILS</div>
<p>
    This test checks if there are any table names that would be too long after adding prefix to them.
    MySQL accepts length of table names with maximum of 64 characters 
    (see <a href="https://dev.mysql.com/doc/refman/8.0/en/identifier-length.html" target="_blank">length limits</a>).
    With a too long prefix, tables can exceed this limit.    
</p>

<?php if (!$isOk) { ?>
    <b>List of database tables that are too long after adding prefix</b><br/>
    <div class="s1-validate-flagged-tbl-list">
        <ul>
            <?php foreach ($tooLongNewTableNames as $table) : ?>
            <li><?php echo htmlentities($table); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php } ?>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Choose a shorter prefix in Options ❯ Database Settings ❯ Table Prefix.</li>
</ul>