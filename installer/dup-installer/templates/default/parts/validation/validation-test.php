<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Core\Params\PrmMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $test DUPX_Validation_abstract_item */

if (!$test->display()) {
    return;
}

$validationLevel = PrmMng::getInstance()->getValue(PrmMng::PARAM_VALIDATION_LEVEL);
$open            = ($test->test() <= DUPX_Validation_abstract_item::LV_HARD_WARNING && $test->test() <= $validationLevel);
$icon            = $open ? 'fa-caret-down' : 'fa-caret-right';
?>
<div class="test-wrapper <?php echo $test->getUniqueSelector(); ?> <?php echo $test->getBadgeClass(); ?>" >
    <div class="test-title" >
        <i class="fa <?php echo $icon; ?>"></i> <?php echo DUPX_U::esc_html($test->getTitle()); ?> 
        <span class="status-badge right <?php echo $test->getBadgeClass(); ?>"></span>
    </div>
    <div class="test-content <?php echo $open ? '' : 'no-display'; ?>" >
        <?php echo $test->getContent(); ?>
    </div>
</div>