<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/head/header-main', array(
    'htmlTitle'         => 'Step <span class="step">2</span> of 4: ' .
        'Install Database <div class="sub-header">This step will install the database from the archive.</div>',
    'showInstallerMode' => false,
    'showSwitchView'    => false,
    'showInstallerLog'  => true
));
