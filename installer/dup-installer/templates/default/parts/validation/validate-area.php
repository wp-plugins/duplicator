<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="validate-area-header" class="hdr-sub1 toggle-hdr open" data-type="toggle" data-target="#validate-area">
    <a id="validate-area-link">
        <i class="fa fa-plus-square"></i>Validation
    </a>
    <span id="validate-global-badge-status" class="status-badge right wait" ></span>
</div>
<div id="validate-area" class="hdr-sub1-area show-warnings no-display" >
    <div id="validation-result" >
        <?php dupxTplRender('parts/validation/validate-noresult'); ?>
    </div>
    <div class='info'>
        <i class="fas fa-exclamation-circle fa-sm"></i> The system validation checks help to make sure the system is ready for install. <br/>
         During installation the website will be in maintenance mode and not accessible to users.
    </div>
    <?php dupxTplRender('pages-parts/step1/actions/hwarn-accept'); ?>
</div>