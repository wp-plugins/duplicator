<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/page-header', array(
    'paramView'   => 'help',
    'bodyId'      => 'page-help',
    'bodyClasses' => $bodyClasses
));
?>
<div id="content-inner">
    <div id="main-content-wrapper" >
        <?php dupxTplRender('pages-parts/help/main'); ?>
    </div>
</div>
<?php
dupxTplRender('pages-parts/page-footer');
