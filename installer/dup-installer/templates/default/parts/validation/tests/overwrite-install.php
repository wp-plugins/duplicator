<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?><b>Deployment Path:</b> <i><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_PATH_NEW)); ?></i>
<br/><br/>

Duplicator is in "Overwrite Install" mode because it has detected an existing WordPress site at the deployment path above.  This mode allows for the installer
to be dropped directly into an existing WordPress site and overwrite its contents.   Any content inside of the archive file
will <u>overwrite</u> the contents from the deployment path.  To continue choose one of these options:

<ol>
    <li>Ignore this notice and continue with the install if you want to overwrite this sites files.</li>
    <li>Move this installer and archive to another empty directory path to keep this sites files.</li>
</ol>

<small style="color:maroon">
    <b>Notice:</b> Existing content such as plugin/themes/images will still show-up after the install is complete if they did not already exist in
    the archive file. For example if you have an SEO plugin in the current site but that same SEO plugin <u>does not exist</u> in the archive file
    then that plugin will display as a disabled plugin after the install is completed. The same concept with themes and images applies.  This will
    not impact the sites operation, and the behavior is expected.
</small>
<br/><br/>
<small style="color:#025d02">
    <b>Recommendation:</b> It is recommended you only overwrite WordPress sites that have a minimal setup (plugins/themes).  Typically a fresh install or a
    cPanel 'one click' install is the best baseline to work from when using this mode but is not required.
</small>