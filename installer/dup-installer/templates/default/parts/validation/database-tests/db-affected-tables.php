<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $isNewSubSite bool */
/* @var $message string */
/* @var $affectedTableCount array */
/* @var $affectedTables array */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>

<?php if ($isOk) : ?>
    <?php if ($isNewSubSite) : ?>
        <p class="green">
            Adding a new subsite into WordPress does not require removing or renaming any tables.
        </p>
    <?php else : ?>
        <p class="green">
            The chosen Database Action does not affect any tables in the selected database.
        </p>
    <?php endif; ?>
<?php else : ?>
    <p class="red">
        The chosen Database Action will result in the modification of <b><?php echo $affectedTableCount; ?></b>
        table(s).
    </p>

    <div class="sub-title">DETAILS</div>
    <p><?php echo $message; ?></p>

    <div class="s1-validate-flagged-tbl-list">
        <ul>
            <?php foreach ($affectedTables as $table) : ?>
            <li><?php echo htmlentities($table); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
