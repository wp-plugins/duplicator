<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$importSiteInfo = PrmMng::getInstance()->getValue(PrmMng::PARAM_FROM_SITE_IMPORT_INFO);
$importPage     = isset($importSiteInfo['import_page']) ? $importSiteInfo['import_page'] : false;

?>
<div id="next_action" class="bottom-step-action no-display" >           
    <div class="footer-buttons margin-top-2">
        <div class="content-left">
            <?php
            dupxTplRender('pages-parts/step1/terms-and-conditions');
            PrmMng::getInstance()->getHtmlFormParam(PrmMng::PARAM_ACCEPT_TERM_COND);
            ?>
        </div>
        <div class="content-right" >
            <a class="secondary-btn" href="<?php echo DUPX_U::esc_attr($importPage) ?>" target="_parent" >Cancel</a>
            <button 
                id="s1-deploy-btn" 
                type="button" 
                title="<?php echo DUPX_U::esc_attr('To enable this button the checkbox above under the "Terms & Notices" must be checked.'); ?>" 
                class="default-btn">
                Next <i class="fa fa-caret-right"></i>
            </button>
        </div>
    </div>
</div>