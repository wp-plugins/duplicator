<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var array $extensionTests */
?>
<div class="sub-title">DETAILS</div>
<p>
    PHP extensions are compiled libraries which enable specific functions to be used in your PHP code. 
    This test checks if some of the most widely used extensions are
    installed on your server. Extensions with an asterisk (<i class='red'>*</i>) are required for the installer to work.
</p>

<ul class="tbl-list">
    <?php foreach ($extensionTests as $extensionName => $extensionTest) : ?>
    <li>
        <b>
            <?php echo $extensionName; ?>
            <?php if ($extensionTest["failLevel"] < DUPX_Validation_abstract_item::LV_GOOD) : ?>
                <i class='red'>*</i>
            <?php endif; ?>:
        </b>
        <?php if ($extensionTest["pass"]) :?>
        <i class='green'>Enabled</i>
        <?php else : ?>
            <i class='red'>Disabled</i>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ul>

<div class="sub-title">TROUBLESHOOT</div>
<p>
    In case this test failed you have to install the required extension on your server or ask your hosting provider to
    install it for you.
</p>

<ul>
    <li>
        <a href="https://www.webhostinghub.com/help/learn/website/how-tos/installing-php-extensions-pear">Installing PHP Extensions through the Cpanel</a>
    </li>
    <li>
        <a href="https://linuxize.com/post/how-to-install-php-on-ubuntu-18-04/#installing-php-extensions">Installing PHP extensions on Linux</a>
    </li>
</ul>