<?php

use Duplicator\Core\MigrationMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$safeMsg = MigrationMng::getSaveModeWarning();
$nonce   = wp_create_nonce('duplicator_cleanup_page');
$url     = DUP_CTRL_Tools::getDiagnosticURL();
?>
<div class="dup-notice-success notice notice-success duplicator-pro-admin-notice dup-migration-pass-wrapper" >
    <p>
        <b><?php
        if (MigrationMng::getMigrationData('restoreBackupMode')) {
            _e('Restore Backup Almost Complete!', 'duplicator');
        } else {
            _e('Migration Almost Complete!', 'duplicator');
        }
        ?></b>
    </p>
    <p>
        <?php
        esc_html_e(
            'Reserved Duplicator installation files have been detected in the root directory.  '
            . 'Please delete these installation files to avoid security issues.',
            'duplicator'
        );
        ?>
        <br/>
        <?php
        esc_html_e('Go to: Tools > General > Information  > Stored Data > and click the "Remove Installation Files" button', 'duplicator'); ?><br>
        <a id="dpro-notice-action-general-site-page" href="<?php echo $url; ?>">
            <?php esc_html_e('Take me there now!', 'duplicator'); ?>
        </a>
    </p>
    <?php if (strlen($safeMsg) > 0) { ?>
        <div class="notice-safemode">
            <?php echo esc_html($safeMsg); ?>
        </div>
    <?php } ?>
    <p class="sub-note">
        <i><?php
            _e(
                'If an archive.zip/daf file was intentially added to the root '
                . 'directory to perform an overwrite install of this site then you can ignore this message.',
                'duplicator'
            );
            ?>
        </i>
    </p>

    <?php echo apply_filters(MigrationMng::HOOK_BOTTOM_MIGRATION_MESSAGE, ''); ?>
</div>