<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/head/header-main', array(
    'htmlTitle'         => 'Step <span class="step">4</span> of 4: Import Finished',
    'showInstallerMode' => false,
    'showSwitchView'    => false,
    'showInstallerLog'  => true
));
