<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$versionDup = DUPX_ArchiveConfig::getInstance()->version_dup;

$cssList =  array(
    'assets/normalize.css',
    'assets/font-awesome/css/all.min.css',
    'assets/fonts/dots/dots-font.css',
    'assets/js/password-strength/password.css',
    'assets/js/tippy/dup-pro-tippy.css',
    'vendor/select2/css/select2.css'
);

$jsList = array(
    'assets/inc.libs.js',
    'assets/js/popper/popper.min.js',
    'assets/js/tippy/tippy-bundle.umd.min.js',
    'assets/js/duplicator-tooltip.js',
    'vendor/select2/js/select2.js'
);

/** CSS */
foreach ($cssList as $css) {
    ?>
    <link rel="stylesheet" href="<?php echo DUPX_INIT_URL . '/' . $css . '?ver=' . $versionDup; ?>" type="text/css" media="all" >
    <?php
}
require(DUPX_INIT . '/assets/inc.libs.css.php');
require(DUPX_INIT . '/assets/inc.css.php');

/** JAVASCRIPT */
foreach ($jsList as $js) {
    ?>
    <script src="<?php echo DUPX_INIT_URL . '/' . $js . '?ver=' . $versionDup; ?>" ></script>
    <?php
}
require(DUPX_INIT . '/assets/inc.js.php');
dupxTplRender('scripts/dupx-functions');
?>
<script type="text/javascript" src="assets/js/password-strength/password.js"></script>