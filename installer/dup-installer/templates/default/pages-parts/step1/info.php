<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="overview-area-header" class="hdr-sub1 toggle-hdr close" data-type="toggle" data-target="#s1-area-archive-file">
    <a id="s1-area-archive-file-link"><i class="fa fa-minus-square"></i>Overview</a>
</div>
<div id="s1-area-archive-file" class="hdr-sub1-area tabs-area dupx-opts" >
    <div class="tabs">
        <ul>
            <li>
                <a href="#tabs-1">Installation</a>
            </li>
            <li>
                <a href="#tabs-2">Archive</a>
            </li>
        </ul>
        <?php
        dupxTplRender('pages-parts/step1/info-tabs/general');
        dupxTplRender('pages-parts/step1/info-tabs/archive');
        ?>
    </div>
</div>