<?php

use Duplicator\Controllers\StorageController;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
    div.panel {padding: 20px 5px 10px 10px;}
    div.area {font-size:16px; text-align: center; line-height: 30px; width:500px; margin:auto}
    ul.li {padding:2px}
</style>

<div class="panel">
    <?php
    $action_updated  = null;
    $action_response = esc_html__("Storage Settings Saved", 'duplicator');

    //SAVE RESULTS
    if (filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW) === 'save') {
        //Nonce Check
        if (!wp_verify_nonce(filter_input(INPUT_POST, 'dup_storage_settings_save_nonce_field', FILTER_UNSAFE_RAW), 'dup_settings_save')) {
            die('Invalid token permissions to perform this request.');
        }

        DUP_Settings::Set('storage_htaccess_off', filter_input(INPUT_POST, 'storage_htaccess_off', FILTER_VALIDATE_BOOLEAN));

        switch (filter_input(INPUT_POST, 'storage_position', FILTER_DEFAULT)) {
            case DUP_Settings::STORAGE_POSITION_LEGACY:
                $setPostion = DUP_Settings::STORAGE_POSITION_LEGACY;
                break;
            case DUP_Settings::STORAGE_POSITION_WP_CONTENT:
            default:
                $setPostion = DUP_Settings::STORAGE_POSITION_WP_CONTENT;
                break;
        }

        if (DUP_Settings::setStoragePosition($setPostion) != true) {
            $targetFolder = ($setPostion === DUP_Settings::STORAGE_POSITION_WP_CONTENT) ? DUP_Settings::getSsdirPathWpCont() : DUP_Settings::getSsdirPathLegacy();
            ?>
            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <b><?php esc_html_e('Storage folder move problem'); ?></b>
                </p>
                <p>
                    <?php echo sprintf(__('Duplicator can\'t change the storage folder to <i>%s</i>', 'duplicator'), esc_html($targetFolder)); ?><br>
                    <?php echo sprintf(__('Check the parent folder permissions. ( <i>%s</i> )', 'duplicator'), esc_html(dirname($targetFolder))); ?>
                </p>
            </div>
            <?php
        }
        DUP_Settings::Save();
        $action_updated = true;
    }
    ?>

    <?php
    $storage_position     = DUP_Settings::Get('storage_position');
    $storage_htaccess_off = DUP_Settings::Get('storage_htaccess_off');
    ?>
    <form id="dup-settings-form" action="<?php echo admin_url('admin.php?page=duplicator-settings&tab=storage'); ?>" method="post">
        <?php wp_nonce_field('dup_settings_save', 'dup_storage_settings_save_nonce_field', false); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="page"   value="duplicator-settings">

        <?php if ($action_updated) : ?>
            <div id="message" class="notice notice-success is-dismissible dup-wpnotice-box"><p><?php echo esc_html($action_response); ?></p></div>
        <?php endif; ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Location", 'duplicator'); ?></label></th>
                <td>
                    <p>
                        <label>
                            <input type="radio" name="storage_position" 
                                   value="<?php echo DUP_Settings::STORAGE_POSITION_LEGACY; ?>"
                                   <?php checked($storage_position === DUP_Settings::STORAGE_POSITION_LEGACY); ?> >
                            <span class="storage_pos_fixed_label"><?php esc_html_e('Legacy Path:', 'duplicator'); ?></span>
                            <i><?php echo DUP_Settings::getSsdirPathLegacy(); ?></i>
                        </label>
                    </p>
                    <p>
                        <label>
                            <input type="radio" name="storage_position"
                                   value="<?php echo DUP_Settings::STORAGE_POSITION_WP_CONTENT; ?>"
                                   <?php checked($storage_position === DUP_Settings::STORAGE_POSITION_WP_CONTENT); ?> >
                            <span class="storage_pos_fixed_label" ><?php esc_html_e('Contents Path:', 'duplicator'); ?></span>
                            <i><?php echo DUP_Settings::getSsdirPathWpCont(); ?></i>
                        </label>
                    </p>
                    <p class="description">
                        <?php
                        esc_html_e("The storage location is where all package files are stored to disk. If your host has troubles writing content to the 'Legacy Path' then use "
                            . "the 'Contents Path'.  Upon clicking the save button all files are moved to the new location and the previous path is removed.", 'duplicator');
                        ?><br/>

                        <i class="fas fa-server fa-sm"></i>&nbsp;
                        <span id="duplicator_advanced_storage_text" class="link-style">[<?php esc_html_e("More Advanced Storage Options...", 'duplicator'); ?>]</span>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php esc_html_e("Apache .htaccess", 'duplicator'); ?></label></th>
                <td>
                    <input type="checkbox" name="storage_htaccess_off" id="storage_htaccess_off" <?php echo ($storage_htaccess_off) ? 'checked="checked"' : ''; ?> />
                    <label for="storage_htaccess_off"><?php esc_html_e("Disable .htaccess file in storage directory", 'duplicator') ?> </label>
                    <p class="description">
                        <?php
                            esc_html_e("When checked this setting will prevent Duplicator from laying down an .htaccess file in the storage location above.", 'duplicator');
                            esc_html_e("Only disable this option if issues occur when downloading either the installer/archive files.", 'duplicator');
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <p class="submit" style="margin: 20px 0px 0xp 5px;">
            <br/>
            <input type="submit" name="submit" id="submit" class="button-primary" value="<?php esc_attr_e("Save Storage Settings", 'duplicator') ?>" style="display: inline-block;" />
        </p>
    </form>
    <br/>
</div>
<!-- ==========================================
THICK-BOX DIALOGS: -->
<?php
$storageAlert = StorageController::getDialogBox('settings-storage-tab');
?>
<script>
    jQuery(document).ready(function ($) {
        $("#duplicator_advanced_storage_text").click(function () {
<?php $storageAlert->showAlert(); ?>
        });
    });
</script>