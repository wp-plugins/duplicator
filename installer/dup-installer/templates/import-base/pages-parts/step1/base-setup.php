<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="base-setup-area-header" class="hdr-sub1 toggle-hdr close" data-type="toggle" data-target="#base-setup-area">
    <a href="javascript:void(0)"><i class="fa fa-minus-square"></i>Setup</a>
</div>
<div id="base-setup-area" class="hdr-sub1-area dupx-opts" >
    <?php dupxTplRender('pages-parts/step1/options-tabs/settings'); ?>
</div>