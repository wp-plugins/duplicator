<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/** @var bool $display */

if (!isset($display)) {
    $display = false;
}
?><div id="progress-area" class="<?php echo $display ? '' : 'no-display'; ?>">
    <div style="width:500px; margin:auto">
        <div class="progress-text"><i class="fas fa-circle-notch fa-spin"></i> <span id="progress-title"></span> <span id="progress-pct"></span></div>
        <div id="secondary-progress-text"></div>
        <div id="progress-notice"></div>
        <div id="progress-bar"></div>
        <h3>Please Wait...</h3><br/><br/>
        <div id="progress-bottom-text"></div>
    </div>
</div>