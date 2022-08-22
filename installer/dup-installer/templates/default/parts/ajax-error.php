<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$recoveryLink = PrmMng::getInstance()->getValue(PrmMng::PARAM_RECOVERY_LINK);
?>
<div id="ajaxerr-area" class="no-display">
    <p>
        <b>ERROR:</b> <div class="message"></div>
    <i>Please try again an issue has occurred.</i>
</p>
<div>Please see the <?php DUPX_View_Funcs::installerLogLink(); ?> file for more details.</div>
<div id="ajaxerr-data">
    <div class="html-content" ></div>
    <pre class="pre-content"></pre>
</div>
<p>
    <b>Additional Resources:</b><br/>
    &raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/'>Help Resources</a><br/>
    &raquo; <a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/'>Technical FAQ</a>
</p>
<p class="text-center">
    <input id="ajax-error-try-again" type="button" class="default-btn" value="&laquo; Try Again" />
    <?php if (!empty($recoveryLink)) { ?>
        <a href="<?php echo DUPX_U::esc_url($recoveryLink); ?>" class="default-btn" target="_parent">
            <i class="fas fa-undo-alt"></i> Restore Recovery Point
        </a> 
    <?php } ?>
</p>
<p class="text-center">
    <i style='font-size:11px'>See online help for more details at <a href='https://snapcreek.com/ticket' target='_blank'>snapcreek.com</a></i>
</p>
</div>