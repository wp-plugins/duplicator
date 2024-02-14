<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var bool $pass */
?>
<div class="sub-title">STATUS</div>
<?php if ($pass) : ?>
<p class="green">
    Successfully read database variables.
</p>
<?php else : ?>
<p class="maroon">
    Error reading database variables.
</p>
<?php endif; ?>
<div class="sub-title">DETAILS</div>
<p>
    Query executed: <i>SHOW VARIABLES like 'version'</i><br/><br/>
    The "<a href="https://dev.mysql.com/doc/refman/5.7/en/show-variables.html" target="_blank">SHOW VARIABLES</a>" query statement is required to obtain
    necessary information about the database and safely execute the installation process. There is not a single setting that will make this query work
    for all hosting providers.  Please contact your hosting provider or server admin with
    <a href="https://dev.mysql.com/doc/refman/5.7/en/show-variables.html" target="_blank">this link</a> and ask for them to provide support for the
    "SHOW VARIABLES" when called from PHP.  <br/><br/>

    Additional FAQ resources for this issue can be found here:<br/>
    <a href="<?php echo LinkManager::getDocUrl('digital-ocean-digitalocean-com', 'install', 'validation digital ocean'); ?>" target="_blank">
        Digital Ocean --  digitalocean.com
    </a>
</p>
