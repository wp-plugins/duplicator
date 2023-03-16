<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
?>
<p>
    <?php
    if ($isOk) {
        ?><span class="green">
            The prefix of the existing WordPress configuration table is equal of the prefix of the table of the source site where the package was created.
        </span><?php
    } else {
        ?><span class="maroon">
            The prefix of the existing WordPress configuration table does not match the prefix of the table of the source site where the package was created, 
            so the prefix will be changed to the managed hosting prefix.
        </span><?php
    }
    ?>
</p>