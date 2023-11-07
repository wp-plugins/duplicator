<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Core\MigrationMng;
use Duplicator\Libs\Snap\SnapUtil;

$safeMsg         = MigrationMng::getSaveModeWarning();
$cleanupReport   = MigrationMng::getCleanupReport();
$cleanFileAction = (SnapUtil::filterInputRequest('action', FILTER_DEFAULT) === 'installer');
?>
<div class="dup-notice-success notice notice-success duplicator-pro-admin-notice dup-migration-pass-wrapper">
    <div class="dup-migration-pass-title">
        <i class="fa fa-check-circle"></i> <?php
        if (MigrationMng::getMigrationData('restoreBackupMode')) {
            _e('This site has been successfully restored!', 'duplicator');
        } else {
            _e('This site has been successfully migrated!', 'duplicator');
        }
        ?>
    </div>
    <p>
        <?php printf(__('The following installation files are stored in the folder <b>%s</b>', 'duplicator'), DUP_Settings::getSsdirPath()); ?>
    </p>
    <ul class="dup-stored-minstallation-files">
        <?php foreach (MigrationMng::getStoredMigrationLists() as $path => $label) { ?>
            <li>
                - <?php echo esc_html($label); ?>
            </li>
        <?php } ?>
    </ul>

    <?php
    if ($cleanFileAction) {
        require DUPLICATOR_LITE_PATH . '/views/parts/migration-clean-installation-files.php';
    } else {
        if (count($cleanupReport['instFile']) > 0) { ?>
            <p>
                <?php _e('Security actions:', 'duplicator'); ?>
            </p>
            <ul class="dup-stored-minstallation-files">
                <?php
                foreach ($cleanupReport['instFile'] as $html) { ?>
                    <li>
                        <?php echo $html; ?>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>
        <p>
            <b><?php _e('Final step:', 'duplicator'); ?></b><br>
            <span id="dpro-notice-action-remove-installer-files" class="link-style" onclick="Duplicator.Tools.deleteInstallerFiles();" >
                <?php esc_html_e('Remove Installation Files Now!', 'duplicator'); ?>
            </span>
        </p>
        <?php if (strlen($safeMsg) > 0) { ?>
            <div class="notice-safemode">
                <?php echo esc_html($safeMsg); ?>
            </div>
        <?php } ?>

        <p class="sub-note">
            <i><?php
                _e(
                    'Note: This message will be removed after all installer files are removed.'
                    . ' Installer files must be removed to maintain a secure site.'
                    . ' Click the link above to remove all installer files and complete the migration.',
                    'duplicator'
                );
                ?><br>
                <i class="fas fa-info-circle"></i>
                <?php
                _e(
                    'If an archive.zip/daf file was intentially added to the root directory to '
                    . 'perform an overwrite install of this site then you can ignore this message.',
                    'duplicator'
                )
                ?>
            </i>
        </p>
        <?php
    }

    echo apply_filters(MigrationMng::HOOK_BOTTOM_MIGRATION_MESSAGE, '');
    ?>
</div>
