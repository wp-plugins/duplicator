<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

$tooltipTitle   = esc_attr__('Package creation', 'duplicator-pro');
$tooltipContent = esc_attr__(
    'This will create a new package. If a package is currently running then this button will be disabled.',
    'duplicator-pro'
);

?>
<div class="dup-section-package-create dup-flex-content">
    <span>
        <?php esc_html_e('Last backup:', 'duplicator-pro'); ?>
        <span class="dup-last-backup-info">
            <?php echo $tplData['lastBackupString']; ?>
        </span>
    </span>
    <span
        class="dup-new-package-wrapper"
        data-tooltip-title="<?php echo $tooltipTitle; ?>"
        data-tooltip="<?php echo $tooltipContent; ?>"
    >
        <a  
            id="dup-pro-create-new" 
            class="button button-primary <?php echo DUP_Package::isPackageRunning() ? 'disabled' : ''; ?>"
            href="<?php echo esc_url(ControllersManager::getPackageBuildUrl()); ?>"
        >
            <?php esc_html_e('Create New', 'duplicator-pro'); ?>
        </a>
    </span>
</div>