<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $fromPhp string */
/* @var $toPhp string */
/* @var $isOk bool */
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $isOk ? 'green' : 'red'; ?>" >
    <b style=''>You are migrating site from PHP <?php echo $fromPhp; ?> to PHP <?php echo $toPhp; ?></b>
</p>

<div class="sub-title">DETAILS</div>
<p>
    If the PHP version of your website is different than the PHP version of your package it <i>may</i> cause problems with the
    functioning of your website.
</p>
<?php if (intval($toPhp) === 8) : ?>
<p>
    In case you are migrating your website from an older version of PHP to
    <a href="https://www.php.net/releases/8.0/en.php" target="blank">PHP 8.x</a> there is a relatively high probability
    that some plugins or themes will not be compatible and may cause the overall website to not work.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        We suggest proceeding in the "Advanced" mode and at Step 3 of the installation process under the "Plugins" tab
        uncheck all plugins. In this way all plugins will be deactivated after the migration and you can then activate them
        one-by-one to make sure everything works properly and be able to isolate offending plugins.
    </li>
    <li>
        In case you are still experiencing issues after applying the fix above, the most likely cause of that is that
        the active theme is not compatible with the new version of PHP. In this case too, it is suggested to
        <a href="https://mediatemple.net/community/products/grid/360022440131/how-to-deactivate-a-wordpress-theme-for-troubleshooting"
           target="_blank">deactivate the theme</a> to debug the issue.
    </li>
</ul>
<?php endif; ?>