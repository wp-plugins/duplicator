<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="error_action" class="bottom-step-action margin-top-2 no-display" >   
    <div class="s1-err-msg" >
        <i>
            This installation will not be able to proceed until the archive and validation sections both pass. 
            Please adjust your servers settings click validate buttom or contact your server administrator, 
            hosting provider or visit the resources below for additional help.
        </i>
        <div style="padding:10px">
            <?php $url = LinkManager::getCategoryUrl(LinkManager::TROUBLESHOOTING_CAT, 'install', 'Technical FAQs'); ?>
            &raquo; <a href="<?php echo DUPX_U::esc_attr($url); ?>" target="_blank">Technical FAQs</a> <br/>
            &raquo; <a href="<?php echo LinkManager::getDocUrl('', 'install', 'Online Documentation'); ?>" target="_blank">Online Documentation</a> <br/>
        </div>
    </div>
</div>
