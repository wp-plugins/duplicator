<?php
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
    if (filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING) === 'save') {
        //Nonce Check
        if (!wp_verify_nonce(filter_input(INPUT_POST, 'dup_storage_settings_save_nonce_field', FILTER_SANITIZE_STRING), 'dup_settings_save')) {
            die('Invalid token permissions to perform this request.');
        }

        DUP_Settings::Set('storage_htaccess_off', filter_input(INPUT_POST, 'storage_htaccess_off', FILTER_VALIDATE_BOOLEAN));

        switch (filter_input(INPUT_POST, 'storage_position', FILTER_DEFAULT)) {
            case DUP_Settings::STORAGE_POSITION_LECAGY:
                $setPostion = DUP_Settings::STORAGE_POSITION_LECAGY;
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
                                   value="<?php echo DUP_Settings::STORAGE_POSITION_LECAGY; ?>" 
                                   <?php checked($storage_position === DUP_Settings::STORAGE_POSITION_LECAGY); ?> >
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
                    <p class="description" style="max-width:800px">
                        <?php
                        esc_html_e("The storage location is where all package files are stored to disk. If your host has troubles writing content to the 'Legacy Path' then use "
                            . "the 'Contents Path'.  Upon clicking the save button all files are moved to the new location and the previous path is removed.", 'duplicator');
                        ?><br/>


                        <i class="fas fa-database fa-sm"></i>&nbsp; <span id="duplicator_advanced_storage_text" class="link-style">[<?php esc_html_e("More Advanced Storage Options...", 'duplicator'); ?>]</span>
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
                            echo '<br/>';
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

function dup_lite_storage_advanced_pro_content()
{
    ob_start();
    ?>
    <div style="text-align: center">
        <img src="<?php echo esc_url(DUPLICATOR_PLUGIN_URL."assets/img/logo-dpro-300x50.png"); ?>" style="height:50px; width:250px" /><br/>
        <?php 
                esc_html_e('Store &amp; Automate to Multiple Endpoints', 'duplicator');
                echo '<br/>';
                esc_html_e('with Duplicator Pro', 'duplicator');
        ?>
  
        <div style="text-align: left; margin:auto; width:175px">
            <ul>
                <li><i class="fab fa-amazon"></i> <?php esc_html_e('Amazon S3', 'duplicator'); ?></li>
                <li><i class="fab fa-dropbox"></i> <?php esc_html_e(' Dropbox', 'duplicator'); ?></li>
                <li><i class="fab fa-google-drive"></i> <?php esc_html_e('Google Drive', 'duplicator'); ?></li>
                <li><i class="fa fa-cloud fa-sm"></i> <?php esc_html_e('One Drive', 'duplicator'); ?></li>
                <li><i class="fa fa-upload"></i> <?php esc_html_e('FTP &amp; SFTP', 'duplicator'); ?></li>
                <li><i class="far fa-folder-open"></i> <?php esc_html_e('Custom Directory', 'duplicator'); ?></li>
            </ul>
        </div>
        <i>
        <?php esc_html_e('Set up one-time storage locations and automatically', 'duplicator'); ?><br>
        <?php esc_html_e('push the package to your destination.', 'duplicator'); ?>
        </i>
    </div>
    <p style="text-align: center">
        <a href="https://snapcreek.com/duplicator/?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_content=free_settings_storage_popup&utm_campaign=duplicator_pro" target="_blank" class="button button-primary button-large dup-check-it-btn" >
            <?php esc_html_e('Learn More', 'duplicator'); ?>
        </a>
    </p>
    <?php
    return ob_get_clean();
}
$storageAlert          = new DUP_UI_Dialog();
$storageAlert->title   = __('Advanced Storage', 'duplicator');
$storageAlert->height  = 525;
$storageAlert->width   = 400;
$storageAlert->okText  = esc_html__('Close', 'duplicator');
$storageAlert->message = dup_lite_storage_advanced_pro_content();
$storageAlert->initAlert();
?>
<script>
    jQuery(document).ready(function ($) {
        $("#duplicator_advanced_storage_text").click(function () {
<?php $storageAlert->showAlert(); ?>
        });
    });
</script>