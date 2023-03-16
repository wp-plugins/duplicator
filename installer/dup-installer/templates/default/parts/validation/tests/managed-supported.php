<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $managedHosting string */
/* @var $failMessage string */
/* @var $isOk bool */
?><p>
    Managed hosting <b><?php echo DUPX_u::esc_html($managedHosting); ?></b> detected. <br> 
    <?php if ($isOk) {
        ?><i class='green'>This managed hosting is supported. </i><?php
    } else {
        ?><i class='red'><?php echo DUPX_U::esc_html($failMessage); ?></i><?php
    }
    ?>
</p>