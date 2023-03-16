<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $uniquePrefixes array */

?>
<div class="sub-title">STATUS</div>

<?php if ($isOk) : ?>
<p class="green">
    The selected database action does not affect other WordPress installations.
</p>
<?php else : ?>
<p class="red">
    The selected database action affects <b><?php echo count($uniquePrefixes); ?></b> WordPress installations.
</p>
<?php endif; ?>

<div class="sub-title">DETAILS</div>
<p>
    This test makes sure that the selected database action affects at most one WordPress installation. Please make sure that the
    chosen database action will not cause unwanted consequences for tables of other sites residing on the same database. In case
    you want to avoid removing the tables of the second WordPress installation we recommend switching the Database action to
    "Overwrite Existing Tables".
</p>
<?php if (count($uniquePrefixes) > 0) : ?>
<p>WordPress tables with the following table prefixes will be affected by the chosen database action:</p>
<ul>
    <?php foreach ($uniquePrefixes as $prefix) : ?>
    <li><b><?php echo DUPX_U::esc_html($prefix); ?></b></li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>




