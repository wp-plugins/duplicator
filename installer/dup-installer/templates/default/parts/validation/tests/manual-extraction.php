<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();
?><p>
    <b>Deployment Path:</b> <i><?php echo DUPX_U::esc_html($paramsManager->getValue(PrmMng::PARAM_PATH_NEW)); ?></i>
</p>
The installer has detected that the archive file has been extracted to the deployment path above.  To continue choose one of these options:

<ol>
    <li>Skip the extraction process by <a href="javascript:void(0)" onclick="DUPX.getManaualArchiveOpt()">[enabling manual archive extraction]</a> </li>
    <li>Ignore this message and continue with the install process to re-extract the archive file.</li>
</ol>

<small>
    Note: This test looks for a file named <i>dup-manual-extract__[HASH]</i> in the <?php echo DUPX_U::esc_html(DUPX_INIT); ?> directory. 
    If the file exists then this notice is shown.
    The <i>dup-manual-extract__[HASH]</i> file is created with every archive and removed once the install is complete.  For more details on this process see the
    <a href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q" target="_blank">manual extraction FAQ</a>.
</small>
