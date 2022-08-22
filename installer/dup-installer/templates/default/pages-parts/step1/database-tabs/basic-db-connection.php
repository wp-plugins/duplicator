<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$state         = DUPX_InstallerState::getInstance();
$paramsManager = PrmMng::getInstance();

if ($state->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL && $paramsManager->getValue(PrmMng::PARAM_DB_DISPLAY_OVERWIRE_WARNING)) {
    $displayOverwrite = true;
} else {
    $displayOverwrite = false;
}
?>
<div id="s2-db-basic">
    <div class="hdr-sub3 database-setup-title">
        <i class="fas fa-database"></i> Database Connection
    </div>
    <?php if ($displayOverwrite) : ?>
        <div id="s2-db-basic-overwrite">
            <b style='color:maroon'>Ready to connect to existing sites database? </b><br/>
            <div class="warn-text">
                The existing site's database settings are ready to be applied below. 
                If you want to connect to this database and replace all its data then
                click the 'Apply button' to set the placeholder values. 
                To use different database settings click the 'Reset button' to clear and set new values.
                <br/><br/>

                <i>
                    <i class="fas fa-exclamation-triangle fa-sm"></i> 
                    Warning: Please note that reusing an existing site's database will <u>overwrite</u> all of its data. If you're not 100% sure about
                    using these database settings, then create a new database and use the new credentials instead.
                </i>
            </div>

            <div class="btn-area">
                <input type="button" value="Apply" class="secondary-btn small" onclick="DUPX.checkOverwriteParameters()">
                <input type="button" value="Reset" class="secondary-btn small" onclick="DUPX.resetParameters()">
            </div>
        </div>
        <?php
    endif;

    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_ACTION);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_HOST);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_NAME);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_USER);
    $paramsManager->getHtmlFormParam(PrmMng::PARAM_DB_PASS);
    ?>
</div>