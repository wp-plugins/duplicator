<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/head/header-main', array(
    'htmlTitle'      => 'Step <span class="step">1</span> of 2: Deployment ' .
        '<div class="sub-header">This step will extract the archive file, install & update the database.</div>',
    'showInstallerMode' => true,
    'showSwitchView'    => true,
    'showInstallerLog'  => false
));
