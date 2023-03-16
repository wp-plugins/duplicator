<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="important-final-step-warning">
    <b><i class="fa fa-exclamation-triangle"></i> FINAL STEPS:</b> 
    Login into the WordPress Admin to remove all <?php DUPX_View_Funcs::helpLink('step4', 'installation files'); ?> and finalize the install process.
    This install is <u>NOT</u> complete until all installer files have been completely removed.  Leaving installer files on this server can
    lead to security issues.
</div>