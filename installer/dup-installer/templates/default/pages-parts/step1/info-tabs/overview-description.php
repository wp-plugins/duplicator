<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$hostManager   = DUPX_Custom_Host_Manager::getInstance();
$overwriteMode = (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL);
?>
<div id="overview-description-wrapper" class="box <?php echo $overwriteMode ? 'warning-easy' : 'info'; ?>" >
    <?php
    dupxTplRender('pages-parts/step1/info-tabs/overviews/restore-backup');
    dupxTplRender('pages-parts/step1/info-tabs/overviews/type-single-site');

    if (($identifier = $hostManager->isManaged()) !== false) {
        $hostObj = $hostManager->getHosting($identifier);
        ?>
        <hr class="separator">
        <h3>
            <b><?php echo $hostObj->getLabel(); ?></b> managed hosting detected
        </h3>
        <p>
            The installation is occurring on a WordPress managed host. 
            Managed hosts are more restrictive than standard shared hosts so some installer settings cannot be changed. 
            These settings include new path, new URL, database connection data, and wp-config settings.
        </p>
    <?php } ?>
</div>