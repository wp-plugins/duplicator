<?php

/**
 * @package Duplicator
 */

defined("ABSPATH") || exit;

use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Upsell;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="static-popup">
    <div class="static-popup-content-top-notice">
        <i class="fas fa-exclamation-triangle"></i><?php echo $tplData['warning-text']; ?>
    </div>
    <div class="static-popup-content">
        <h2>
            <?php echo $tplData['title']; ?>
        </h2>
        <?php echo TplMng::getInstance()->render($tplData['content-tpl']); ?>
    </div>
    <div class="static-popup-button">
        <a href="<?php echo esc_url($tplData['upsell-url']); ?>" class="dup-btn-green dup-btn-lg dup-btn" target="_blank" rel="noopener noreferrer">
            <?php echo __('Upgrade to Duplicator Pro Now', 'duplicator'); ?>
        </a>
    </div>
</div>
