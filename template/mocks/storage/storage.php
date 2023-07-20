<?php

use Duplicator\Controllers\StorageController;
use Duplicator\Utils\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<style>
    /*Detail Tables */
    table.storage-tbl td {
        height: 45px
    }

    table.storage-tbl input[type='checkbox'] {
        margin-left: 5px
    }

    table tr.storage-detail td {
        padding: 3px 0 5px 20px
    }

    table tr.storage-detail div {
        line-height: 20px;
        padding: 2px 2px 2px 15px
    }

    table tr.storage-detail td button {
        margin: 5px 0 5px 0 !important;
        display: block
    }

    tr.storage-detail label {
        min-width: 150px;
        display: inline-block;
        font-weight: bold
    }

    table.storage-tbl input[type='checkbox'].item-chk {
        opacity: 0.7;
        background: rgba(255,255,255,.5);
        border-color: rgba(220,220,222,.75);
        box-shadow: inset 0 1px 2px rgb(0 0 0 / 4%);
        color: rgba(44,51,56,.5);
    }
</style>
<div class="wrap"><h1><?php _e('Storage', 'duplicator');?></h1>
    <div class="notice notice-error">
        <p><strong><?php echo __('Remote Cloud Backups is a PRO feature', 'duplicator'); ?></strong></p>
        <p><?php echo __('Back up to Dropbox, FTP, Google Drive, OneDrive, Amazon S3 or Amazon S3 compatible for safe off-site storage.', 'duplicator'); ?></p>
        <p>
            <a href="<?php echo esc_url(Upsell::getCampaignUrl('storage-page', 'Notice Upgrade Now')); ?>"
               class="dup-btn-green dup-btn-md dup-btn"
               target="_blank"
               rel="noopener noreferrer">
                <?php echo __('Upgrade Now', 'duplicator'); ?>
            </a>
        </p>
    </div>
    <!-- ====================
    TOOL-BAR -->
    <table class="dpro-edit-toolbar">
        <tbody>
        <tr>
            <td>
                <select id="bulk_action">
                    <option value="-1"><?php _e('Bulk Actions', 'duplicator');?></option>
                    <option value="1" title="Delete selected storage endpoint(s)">
                        <?php _e('Delete', 'duplicator');?>
                    </option>
                </select>
                <input type="button" class="button action" value="Apply">
                <span class="btn-separator"></span>
                <a href="#" class="button grey-icon"
                   title="Settings">
                    <i class="fas fa-sliders-h fa-fw"></i>
                </a>
            </td>
            <td>
                <div id="new_storage" class="btnnav">
                    <a href="#" id="duplicator-pro-add-new-storage" class="button"><?php _e('Add New', 'duplicator'); ?></a>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <form id="dup-storage-form" action="#"
          method="post">
        <!-- ====================
        LIST ALL STORAGE -->
        <table class="widefat storage-tbl">
            <thead>
            <tr>
                <th style="width:10px;"><input type="checkbox" id="dpro-chk-all" title="Select all storage endpoints"></th>
                <th style="width:275px;"><?php _e('Name', 'duplicator'); ?></th>
                <th><?php _e('Type', 'duplicator'); ?></th>
            </tr>
            </thead>
            <tbody>
            <tr id="main-view--2" class="storage-row" data-id="-2" data-name="Default" data-typeid="0"
                data-typename="Local">
                <td>
                    <input type="checkbox" onclick="return false" checked="checked">
                </td>
                <td>
                    <a href="#"><b>Default</b></a>
                </td>
                <td>
                    <i class="far fa-hdd fa-fw"></i>&nbsp;Local
                </td>
            </tr>
            <?php foreach ($tplData['storages'] as $storage) : ?>
            <tr class="storage-row">
                <td>
                    <input class="item-chk" type="checkbox">
                </td>
                <td>
                    <a href="#"><b><?php echo $storage['title']; ?></b></a>
                </td>
                <td>
                    <i class="<?php echo $storage['fa-class']; ?> fa-fw"></i>&nbsp;<?php echo $storage['label']; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="8" style="text-align:right; font-size:12px"><?php printf(__('Total: %s', 'duplicator'), count($tplData['storages'])); ?></th>
            </tr>
            </tfoot>
        </table>
    </form>
</div>
<?php
$storageAlert = StorageController::getDialogBox('storage-page');
?>
<script>
    jQuery(document).ready(function ($) {
        $(".storage-tbl tr a, .item-chk, #new_storage").click(function (e) {
            e.preventDefault();
            console.log('triggered')
            <?php $storageAlert->showAlert(); ?>
        });
    });
</script>