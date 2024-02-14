<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?><b>Deployment Path:</b> <i><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_PATH_NEW)); ?></i>
<br/><br/>

The installer has detected that a WordPress site does not exist at the deployment path above. 
This installer is currently in 'Database Only' mode because that is how the archive was created.  
If core WordPress site files do not exist at the path above then they will need to be placed there in order for a WordPress site
to properly work.  To continue choose one of these options:

<ol>
    <li>Place this installer and archive at a path where core WordPress files already exist to hide this message. </li>
    <li>Create a new package that includes both the database and the core WordPress files.</li>
    <li>Ignore this message and install only the database (for advanced users only).</li>
</ol>

<small>
    Note: This test simply looks for the directories <?php echo implode(', ', DUPX_Server::$wpCoreDirsList); ?> and a wp-config.php file.  
    If they are not found in the deployment path above then this notice is shown.
</small>
