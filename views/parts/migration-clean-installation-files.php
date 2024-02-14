<?php


defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\MigrationMng;
use Duplicator\Installer\Utils\LinkManager;
use Duplicator\Views\AdminNotices;

?>
<div class="dpro-diagnostic-action-installer">
    <p>
        <b><?php echo __('Installation cleanup ran!', 'duplicator'); ?></b>
    </p>
    <?php
    $fileRemoved = MigrationMng::cleanMigrationFiles();
    $removeError = false;
    if (count($fileRemoved) === 0) {
        ?>
        <p>
            <b><?php _e('No Duplicator files were found on this WordPress Site.', 'duplicator'); ?></b>
        </p> <?php
    } else {
        foreach ($fileRemoved as $path => $success) {
            if ($success) {
                ?><div class="success">
                    <i class="fa fa-check"></i> <?php _e("Removed", 'duplicator'); ?> - <?php echo esc_html($path); ?>
                </div><?php
            } else {
                ?><div class="failed">
                    <i class='fa fa-exclamation-triangle'></i> <?php _e("Found", 'duplicator'); ?> - <?php echo esc_html($path); ?>
                </div><?php
                $removeError = true;
            }
        }
    }
    foreach (MigrationMng::purgeCaches() as $message) {
        ?><div class="success">
            <i class="fa fa-check"></i> <?php echo $message; ?>
        </div>
        <?php
    }

    if ($removeError) {
        ?>
        <p>
        <?php _e('Some of the installer files did not get removed, ', 'duplicator'); ?>
            <span class="link-style" onclick="Duplicator.Tools.deleteInstallerFiles();">
        <?php _e('please retry the installer cleanup process', 'duplicator'); ?>
            </span><br>
        <?php _e(' If this process continues please see the previous FAQ link.', 'duplicator'); ?>
        </p>
        <?php
    } else {
        delete_option(AdminNotices::OPTION_KEY_MIGRATION_SUCCESS_NOTICE);
    }
    ?>
    <div style="font-style: italic; max-width:900px; padding:10px 0 25px 0;">
        <p>
            <b><i class="fa fa-shield-alt"></i> <?php esc_html_e('Security Notes', 'duplicator'); ?>:</b>
            <?php
            _e(
                ' If the installer files do not successfully get removed with this action, '
                . 'then they WILL need to be removed manually through your hosts control panel '
                . 'or FTP.  Please remove all installer files to avoid any security issues on this site.',
                'duplicator'
            );
            ?><br>
            <?php
            printf(
                _x(
                    'For more details please visit the FAQ link %1$sWhich files need to be removed after an install?%2$s',
                    '%1$s and %2$s are <a> tags',
                    'duplicator'
                ),
                '<a href="' . esc_url(LinkManager::getDocUrl('which-files-need-to-be-removed-after-an-install', 'migration-notice')) . '" target="_blank">',
                '</a>'
            );
            ?>
        </p>
        <p>
            <b><i class="fa fa-thumbs-up"></i> <?php esc_html_e('Help Support Duplicator', 'duplicator'); ?>:</b>
            <?php
            _e('The Duplicator team has worked many years to make moving a WordPress site a much easier process. ', 'duplicator');
            echo '<br/>';
            _e(
                'Show your support with a '
                . '<a href="' . esc_url(\Duplicator\Core\Notifications\Review::getReviewUrl()) . '" '
                . 'target="_blank">5 star review</a>! We would be thrilled if you could!',
                'duplicator'
            );
            ?>
        </p>
    </div>
</div>
