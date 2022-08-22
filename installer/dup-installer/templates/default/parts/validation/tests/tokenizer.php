<?php

/**
 *
 * @package templates/default
 *
 */

/* @var $isOk bool */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div class="sub-title">STATUS</div>
<?php if ($isOk) : ?>
    <p class="green">
        The function <b>[token_get_all]</b> exists on your server and can be used.
    </p>
<?php else : ?>
    <p class="maroon">
        The function <b>[token_get_all]</b> is disabled or not present on your server.
    </p>
<?php endif; ?>

<div class="sub-title">DETAILS</div>

<p>
    The function <a href="https://www.php.net/manual/en/function.token-get-all.php" target="_blank">[token_get_all]</a> 
    is part of the <a href="https://www.php.net/manual/en/book.tokenizer.php" target="_blank">tokenizer module</a>
    and is required for parsing the contents of the wp-config.php file. 
    To avoid problems during the installation the handling of the wp-config.php file has been disabled (the setting 'WordPress wp-config.php'
    under Advanced Mode > Options > Advanced > Configuration files has been set to 'Do nothing'.)
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        Continue with the install and <a href="https://wordpress.org/support/article/editing-wp-config-php" target="_blank">
            manually configure the wp-config.php file</a> after finishing the installation process.
        If you are doing an overwrite install a wp-config.php file might already present which you can edit, otherwise you can use the
        <a href="https://github.com/WordPress/WordPress/blob/master/wp-config-sample.php" target="_blank">wp-config-sample.php as a guide</a>.
    </li>
    <li>
        Contact your hosting provider and ask them to <a href="https://www.php.net/manual/en/tokenizer.installation.php" target="_blank">
            enable the function
        </a>.
    </li>
</ul>