<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

dupxTplRender('pages-parts/page-header', array(
    'paramView'   => 'step1',
    'bodyId'      => 'page-step1',
    'bodyClasses' => $bodyClasses
));
?>
<div id="content-inner">
    <?php dupxTplRender('pages-parts/step1/step-title'); ?>
    <div id="main-content-wrapper" class="<?php echo DUPX_Validation_manager::validateOnLoad() ? 'no-display' : ''; ?>">
        <?php dupxTplRender('pages-parts/step1/main'); ?>
    </div>
    <?php
    dupxTplRender('parts/ajax-error');
    dupxTplRender('parts/progress-bar', array(
        'display' => DUPX_Validation_manager::validateOnLoad()
    ));
    ?>
</div>
<?php
dupxTplRender('scripts/step1-init');
dupxTplRender('pages-parts/page-footer');
