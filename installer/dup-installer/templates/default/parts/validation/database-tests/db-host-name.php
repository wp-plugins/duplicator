<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $host string */
/* @var $fixedHost string */
?>
<p>
    <b>Database host:</b> 
    <?php
    if ($isOk) {
        ?><i class='green'>
            <b>[<?php echo htmlentities($host); ?>]</b> is valid.
        </i><?php
    } else {
        ?><i class='red'>
            <b>[<?php echo htmlentities($host); ?>]</b> is not a valid. Try using <b>[<?php echo htmlentities($fixedHost); ?>]</b> instead.
        </i>
        <?php
    }
    ?>
</p>