<?php

/**
 *
 * @package templates/default
 */

/* @var $isOk bool */
/* @var $meessage string */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<p class="maroon">
    The installer will not perform replacements on database PATHs but only on URLs.
</p>

<div class="sub-title">DETAILS</div>

<?php if (!empty($message)) : ?>
    <p>
        <?php echo $message; ?>
    </p>
<?php endif; ?>

<p>
    Usually the database does not contain significant references to paths, so you can continue with the installation,
    but some plugins may write absolute paths in the database and there may be some malfunctions.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<p>
    If you experience any issues after the install you will have to manually replace paths in the database using phpMyAdmin or similar tools.
</p>