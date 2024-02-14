<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="installer-switch-wrapper">
    <span class="btn-group">
        <button 
            type="button" 
            id="s1-switch-template-btn-basic" 
            class="s1-switch-template-btn" 
            data-template="<?php echo DUPX_Template::TEMPLATE_BASE; ?>"  
            title="Enable basic installer mode"
        >
            Basic
        </button>
        <button type="button" id="s1-switch-template-btn-advanced" class="s1-switch-template-btn active" title="Enable advanced installer mode">
            Advanced
        </button>
    </span>
</div>