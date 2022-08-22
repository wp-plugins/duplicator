<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/head/header-main', array(
    'htmlTitle'         => 'Step <span class="step">1</span> of 4: Extract Uploaded Package <div class="sub-header">This step will extract package.</div>',
    'showInstallerMode' => false,
    'showSwitchView'    => true,
    'showInstallerLog'  => true
));
