<?php

use Duplicator\Installer\Utils\LinkManager;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Utils\Upsell;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
$view_state     = DUP_UI_ViewState::getArray();
$ui_css_general = (isset($view_state['dup-package-dtl-general-panel']) && $view_state['dup-package-dtl-general-panel']) ? 'display:block' : 'display:none';
$ui_css_storage = (isset($view_state['dup-package-dtl-storage-panel']) && $view_state['dup-package-dtl-storage-panel']) ? 'display:block' : 'display:none';
$ui_css_archive = (isset($view_state['dup-package-dtl-archive-panel']) && $view_state['dup-package-dtl-archive-panel']) ? 'display:block' : 'display:none';
$ui_css_install = (isset($view_state['dup-package-dtl-install-panel']) && $view_state['dup-package-dtl-install-panel']) ? 'display:block' : 'display:none';

$archiveDownloadInfo       = $package->getPackageFileDownloadInfo(DUP_PackageFileType::Archive);
$logDownloadInfo           = $package->getPackageFileDownloadInfo(DUP_PackageFileType::Log);
$installerDownloadInfo     = $package->getInstallerDownloadInfo();
$archiveDownloadInfoJson   = SnapJson::jsonEncodeEscAttr($archiveDownloadInfo);
$logDownloadInfoJson       = SnapJson::jsonEncodeEscAttr($logDownloadInfo);
$installerDownloadInfoJson = SnapJson::jsonEncodeEscAttr($installerDownloadInfo);
$showLinksDialogJson       = SnapJson::jsonEncodeEscAttr(array(
    "archive" => $archiveDownloadInfo["url"],
    "log"     => $logDownloadInfo["url"],
));

$debug_on                = DUP_Settings::Get('package_debug');
$mysqldump_on            = DUP_Settings::Get('package_mysqldump') && DUP_DB::getMySqlDumpPath();
$mysqlcompat_on          = isset($Package->Database->Compatible) && strlen($Package->Database->Compatible);
$mysqlcompat_on          = ($mysqldump_on && $mysqlcompat_on) ? true : false;
$dbbuild_mode            = ($mysqldump_on) ? 'mysqldump' : 'PHP';
$archive_build_mode      = ($package->Archive->Format === 'ZIP') ? 'ZipArchive (zip)' : 'DupArchive (daf)';
$dup_install_secure_on   = isset($package->Installer->OptsSecureOn) ? $package->Installer->OptsSecureOn : 0;
$dup_install_secure_pass = isset($package->Installer->OptsSecurePass) ? DUP_Util::installerUnscramble($package->Installer->OptsSecurePass) : '';
$installerNameMode       = DUP_Settings::Get('installer_name_mode');

$currentStoreURLPath = DUP_Settings::getSsdirUrl();
$installerSecureName = $package->getInstDownloadName(true);
$installerDirectLink = "{$currentStoreURLPath}/" . pathinfo($installerSecureName, PATHINFO_FILENAME) . DUP_Installer::INSTALLER_SERVER_EXTENSION;
?>

<style>
    /*COMMON*/
    div.toggle-box {float:right; margin: 5px 5px 5px 0}
    div.dup-box {margin-top: 15px; font-size:14px; clear: both}
    table.dup-dtl-data-tbl {width:100%}
    table.dup-dtl-data-tbl tr {vertical-align: top}
    table.dup-dtl-data-tbl tr:first-child td {margin:0; padding-top:0 !important;}
    table.dup-dtl-data-tbl td {padding:0 5px 0 0; padding-top:10px !important;}
    table.dup-dtl-data-tbl td:first-child {font-weight: bold; width:130px}
    table.dup-sub-list td:first-child {white-space: nowrap; vertical-align: middle; width:100px !important;}
    table.dup-sub-list td {white-space: nowrap; vertical-align:top; padding:2px !important;}
    div.dup-box-panel-hdr {font-size:14px; display:block; border-bottom: 1px solid #efefef; margin:5px 0 5px 0; font-weight: bold; padding: 0 0 5px 0}
    td.sub-notes {font-weight: normal !important; font-style: italic; color:#999; padding-top:10px;}
    div.sub-notes {font-weight: normal !important; font-style: italic; color:#999;}

    /*STORAGE*/
    div.dup-store-pro {font-size:12px; font-style:italic;}
    div.dup-store-pro img {height:14px; width:14px; vertical-align: text-top}
    div.dup-store-pro a {text-decoration: underline}

    /*GENERAL*/
    div#dup-name-info, div#dup-version-info {display: none; line-height:20px; margin:4px 0 0 0}
    table.dup-sub-info td {padding: 1px !important}
    table.dup-sub-info td:first-child {font-weight: bold; width:100px; padding-left:10px}

    div#dup-downloads-area {padding: 5px 0 5px 0; }
    div#dup-downloads-area i.fa-shield-alt {display: block; float:right; margin-top:8px}
    div#dup-downloads-area i.fa-bolt {display: inline-block; border:0 solid red}
    div#dup-downloads-msg {margin-bottom:-5px; font-style: italic}
    div.sub-section {padding:7px 0 0 0}
    textarea.file-info {width:100%; height:100px; font-size:12px }

    /*INSTALLER*/
    div#dup-pass-toggle {position: relative; margin:0; width:273px}
    input#secure-pass {border-radius:4px 0 0 4px; width:250px; height: 23px; margin:0}
    button#secure-btn {height:30px; width:30px; position:absolute; top:0px; right:0px;border:1px solid silver;  border-radius:0 4px 4px 0; cursor:pointer}
    div.dup-install-hdr-2 {font-weight:bold; border-bottom:1px solid #dfdfdf; padding-bottom:2px; width:100%}
</style>

<?php if ($package_id == 0) :?>
    <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Invalid Package ID request.  Please try again!', 'duplicator'); ?></p></div>
<?php endif; ?>

<div class="toggle-box">
    <span class="link-style" onclick="Duplicator.Pack.OpenAll()">[open all]</span> &nbsp;
    <span class="link-style" onclick="Duplicator.Pack.CloseAll()">[close all]</span>
</div>

<!-- ===============================
GENERAL -->
<div class="dup-box">
<div class="dup-box-title">
    <i class="fa fa-archive fa-sm"></i> <?php esc_html_e('General', 'duplicator') ?>
    <div class="dup-box-arrow"></div>
</div>
<div class="dup-box-panel" id="dup-package-dtl-general-panel" style="<?php echo esc_attr($ui_css_general); ?>">
    <table class='dup-dtl-data-tbl'>
        <tr>
            <td><?php esc_html_e('Name', 'duplicator') ?></td>
            <td>
                <span class="link-style" onclick="jQuery('#dup-name-info').toggle()">
                    <?php echo esc_js($package->Name); ?>
                </span>
                <div id="dup-name-info">
                    <table class="dup-sub-info">
                        <tr>
                            <td><?php esc_html_e('ID', 'duplicator') ?></td>
                            <td><?php echo absint($package->ID); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Hash', 'duplicator') ?></td>
                            <td><?php echo esc_html($package->Hash); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Full Name', 'duplicator') ?></td>
                            <td><?php echo esc_html($package->NameHash); ?></td>
                        </tr>                        
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php esc_html_e('Notes', 'duplicator') ?></td>
            <td><?php echo strlen($package->Notes) ? $package->Notes : esc_html__('- no notes -', 'duplicator') ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('Created', 'duplicator') ?></td>
            <td><?php echo get_date_from_gmt($package->Created) ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('Version', 'duplicator') ?></td>
            <td>
                <span class="link-style" onclick="jQuery('#dup-version-info').toggle()">
                    <?php echo esc_html($package->Version); ?>
                </span>
                <div id="dup-version-info">
                    <table class="dup-sub-info">
                        <tr>
                            <td><?php esc_html_e('WordPress', 'duplicator') ?></td>
                            <td><?php echo strlen($package->VersionWP) ? esc_html($package->VersionWP) : esc_html__('- unknown -', 'duplicator') ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('PHP', 'duplicator') ?> </td>
                            <td><?php echo strlen($package->VersionPHP) ? esc_html($package->VersionPHP) : esc_html__('- unknown -', 'duplicator') ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e('Mysql', 'duplicator') ?></td>
                            <td>
                                <?php echo strlen($package->VersionDB) ? esc_html($package->VersionDB) : esc_html__('- unknown -', 'duplicator') ?> |
                                <?php echo strlen($package->Database->Comments) ? esc_html($package->Database->Comments) : esc_html__('- unknown -', 'duplicator') ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
        <tr>
            <td><?php esc_html_e('Runtime', 'duplicator') ?></td>
            <td><?php echo strlen($package->Runtime) ? esc_html($package->Runtime) : esc_html__("error running", 'duplicator'); ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('Status', 'duplicator') ?></td>
            <td><?php echo ($package->Status >= 100) ? esc_html__('completed', 'duplicator')  : esc_html__('in-complete', 'duplicator') ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('User', 'duplicator') ?></td>
            <td><?php echo strlen($package->WPUser) ? esc_html($package->WPUser) : esc_html__('- unknown -', 'duplicator') ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('Files', 'duplicator') ?> </td>
            <td>
                <div id="dup-downloads-area">
                    <?php if (!$err_found) :?>
                        <?php
                        if ($installerNameMode === DUP_Settings::INSTALLER_NAME_MODE_WITH_HASH) {
                            $installBtnTooltip = __('Download hashed installer ([name]_[hash]_[time]_installer.php)', 'duplicator');
                            $installBtnIcon    = '<i class="fas fa-shield-alt fa-sm fa-fw shield-on"></i>';
                        } else {
                            $installBtnTooltip = __('Download basic installer (installer.php)', 'duplicator');
                            $installBtnIcon    = '<i class="fas fa-shield-alt fa-sm fa-fw shield-off"></i>';
                        }
                        ?>
                        <div class="sub-notes">
                            <i class="fas fa-download fa-fw"></i>
                             <?php _e("Click buttons or links to download.", 'duplicator') ?>
                            <br/><br/>
                        </div>
                        <button class="button"
                                title="<?php echo $installBtnTooltip; ?>"
                                onclick="Duplicator.Pack.DownloadInstaller(<?php echo $installerDownloadInfoJson; ?>);">
                            <i class="fas fa-bolt fa-sm fa-fw"></i>&nbsp; Installer &nbsp; <?php echo $installBtnIcon; ?>
                        </button>
                        <button class="button" onclick="Duplicator.Pack.DownloadFile(<?php echo $archiveDownloadInfoJson; ?>);return false;">
                            <i class="far fa-file-archive"></i>&nbsp; Archive - <?php echo esc_html($package->ZipSize); ?>
                        </button>
                        <button class="button" onclick="Duplicator.Pack.ShowLinksDialog(<?php echo $showLinksDialogJson;?>);" class="thickbox">
                            <i class="fas fa-share-alt"></i>&nbsp; <?php esc_html_e("Share File Links", 'duplicator')?>
                        </button>
                    <?php else : ?>
                        <button class="button" onclick="Duplicator.Pack.DownloadFile(<?php echo $logDownloadInfoJson; ?>);return false;">
                            <i class="fas fa-file-contract fa-sm"></i>&nbsp; Log
                        </button>
                    <?php endif; ?>
                </div>
                <?php if (!$err_found) :?>
                <table class="dup-sub-list">
                    <tr>
                        <td><?php esc_html_e('Archive', 'duplicator') ?> </td>
                        <td>
                            <a href="<?php echo esc_url($archiveDownloadInfo["url"]); ?>" class="link-style">
                                <?php echo esc_html($package->Archive->File); ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e("Build Log", 'duplicator') ?> </td>
                        <td>
                            <a href="<?php echo $logDownloadInfo["url"] ?>" target="file_results" class="link-style">
                                <?php echo $logDownloadInfo["filename"]; ?>
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td><?php esc_html_e('Installer', 'duplicator') ?> </td>
                        <td><?php  echo "{$installerSecureName}"; ?></td>
                    </tr>
                    <tr>
                        <td class="sub-notes" colspan="2">
                            <?php _e("The installer is also available inside the archive file.", 'duplicator') ?>
                        </td>
                    </tr>
                </table>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>
</div>

<!-- ==========================================
DIALOG: QUICK PATH -->
<?php add_thickbox(); ?>
<div id="dup-dlg-quick-path" title="<?php esc_attr_e('Download Links', 'duplicator'); ?>" style="display:none">
    <p style="color:maroon">
        <i class="fa fa-lock fa-xs"></i>
        <?php esc_html_e("The following links contain sensitive data. Share with caution!", 'duplicator');  ?>
    </p>

    <div style="padding: 0px 5px 5px 5px;">
        <a href="javascript:void(0)" style="display:inline-block; text-align:right" onclick="Duplicator.Pack.GetLinksText()">[Select All]</a> <br/>
        <textarea id="dup-dlg-quick-path-data" style='border:1px solid silver; border-radius:2px; width:100%; height:175px; font-size:11px'></textarea><br/>
        <i style='font-size:11px'>
            <?php
                printf(
                    esc_html_x(
                        "A copy of the database.sql and installer.php files can both be found inside of the archive.zip/daf file.  "
                        . "Download and extract the archive file to get a copy of the installer which will be named 'installer-backup.php'. "
                        . "For details on how to extract a archive.daf file please see: "
                        . '%1$sHow to work with DAF files and the DupArchive extraction tool?%2$s',
                        '%1$s and %2$s are opening and closing <a> tags',
                        'duplicator'
                    ),
                    '<a href="' . esc_url(LinkManager::getDocUrl('how-to-work-with-daf-files-and-the-duparchive-extraction-tool', 'package-deatils')) . '" '
                    . 'target="_blank">',
                    '</a>'
                );
                ?>
        </i>
    </div>
</div>

<!-- ===============================
STORAGE -->
<div class="dup-box">
<div class="dup-box-title">
    <i class="fas fa-server fa-sm"></i>
    <?php esc_html_e('Storage', 'duplicator') ?>
    <div class="dup-box-arrow"></div>
</div>
<div class="dup-box-panel" id="dup-package-dtl-storage-panel" style="<?php echo esc_attr($ui_css_storage); ?>">

    <table class="widefat package-tbl" style="margin-bottom:15px" >
        <thead>
            <tr>
                <th style='width:200px'><?php esc_html_e("Name", 'duplicator'); ?></th>
                <th style='width:100px'><?php esc_html_e("Type", 'duplicator'); ?></th>
                <th style="white-space:nowrap"><?php esc_html_e("Locations", 'duplicator'); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr class="dup-store-path">
                <td>
                    <?php  esc_html_e('Default', 'duplicator');?>
                    <i>
                        <?php
                        if ($storage_position === DUP_Settings::STORAGE_POSITION_LEGACY) {
                            esc_html_e("(Legacy Path)", 'duplicator');
                        } else {
                            esc_html_e("(Contents Path)", 'duplicator');
                        }
                        ?>
                    </i>
                </td>
                <td>
                    <i class="far fa-hdd fa-fw"></i>
                    <?php esc_html_e("Local", 'duplicator'); ?>
                </td>
                <td>
                    <?php
                        echo DUP_Settings::getSsdirPath();
                        echo '<br/>';
                        echo DUP_Settings:: getSsdirUrl();
                    ?>
                </td>
            </tr>
            <tr>
                <td colspan="5" class="dup-store-promo-area">
                    <div class="dup-store-pro">
                        <span class="dup-pro-text">
                            <?php echo sprintf(
                                __('Back up this site to %1$s, %2$s, %3$s, %4$s, %5$s and other locations with ', 'duplicator'),
                                '<i class="fab fa-aws  fa-fw"></i>&nbsp;' . 'Amazon',
                                '<i class="fab fa-dropbox fa-fw"></i>&nbsp;' . 'Dropbox',
                                '<i class="fab fa-google-drive  fa-fw"></i>&nbsp;' . 'Google Drive',
                                '<i class="fas fa-cloud  fa-fw"></i>&nbsp;' . 'OneDrive',
                                '<i class="fas fa-network-wired fa-fw"></i>&nbsp;' . 'FTP/SFTP'
                            ); ?>
                            <a 
                                href="<?php echo esc_url(Upsell::getCampaignUrl('details-storage')); ?>"
                                target="_blank"
                                class="link-style">
                                <?php esc_html_e('Duplicator Pro', 'duplicator');?>
                            </a>
                            <i class="fas fa-question-circle"
                                data-tooltip-title="<?php esc_attr_e("Additional Storage:", 'duplicator'); ?>"
                                data-tooltip="<?php esc_attr_e('Duplicator Pro allows you to create a package and store it at a custom location on this server or to a remote '
                                        . 'cloud location such as Google Drive, Amazon, Dropbox and many more.', 'duplicator'); ?>">
                             </i>
                        </span>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    </div>
</div>

<!-- ===============================
ARCHIVE -->
<div class="dup-box">
<div class="dup-box-title">
    <i class="far fa-file-archive"></i> <?php esc_html_e('Archive', 'duplicator') ?>
    <div class="dup-box-arrow"></div>
</div>
<div class="dup-box-panel" id="dup-package-dtl-archive-panel" style="<?php echo esc_attr($ui_css_archive); ?>">

    <!-- FILES -->
    <div class="dup-box-panel-hdr">
        <i class="fas fa-folder-open fa-sm"></i>
        <?php esc_html_e('FILES', 'duplicator'); ?>
    </div>
    <table class='dup-dtl-data-tbl'>
        <tr>
            <td><?php esc_html_e('Build Mode', 'duplicator') ?> </td>

            <td><?php echo esc_html($archive_build_mode); ?></td>
        </tr>

        <?php if ($package->Archive->ExportOnlyDB) : ?>
            <tr>
                <td><?php esc_html_e('Database Mode', 'duplicator') ?> </td>
                <td><?php esc_html_e('Archive Database Only Enabled', 'duplicator') ?></td>
            </tr>
        <?php else : ?>
            <tr>
                <td><?php esc_html_e('Filters', 'duplicator') ?> </td>
                <td>
                    <?php echo $package->Archive->FilterOn == 1 ? 'On' : 'Off'; ?>
                    <div class="sub-section">
                        <b><?php esc_html_e('Directories', 'duplicator') ?></b> <br/>
                        <?php
                            $txt = strlen($package->Archive->FilterDirs)
                                ? str_replace(';', ";\n", $package->Archive->FilterDirs)
                                : esc_html__('- no filters -', 'duplicator');
                        ?>
                        <textarea class='file-info' readonly="true"><?php echo esc_textarea($txt); ?></textarea>
                    </div>

                    <div class="sub-section">
                        <b><?php esc_html_e('Extensions', 'duplicator') ?> </b><br/>
                        <?php
                        echo isset($package->Archive->FilterExts) && strlen($package->Archive->FilterExts)
                            ? esc_html($package->Archive->FilterExts)
                            : esc_html__('- no filters -', 'duplicator');
                        ?>
                    </div>

                    <div class="sub-section">
                        <b><?php esc_html_e('Files', 'duplicator') ?></b><br/>
                        <?php
                            $txt = strlen($package->Archive->FilterFiles)
                                ? str_replace(';', ";\n", $package->Archive->FilterFiles)
                                : esc_html__('- no filters -', 'duplicator');
                        ?>
                        <textarea class='file-info' readonly="true"><?php echo esc_html($txt); ?></textarea>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
    </table>
    <br/><br/>

    <!-- DATABASE -->
    <div class="dup-box-panel-hdr">
        <i class="fas fa-database fa-sm"></i>
        <?php esc_html_e('DATABASE', 'duplicator'); ?>
    </div>
    <table class='dup-dtl-data-tbl'>
        <tr>
            <td><?php esc_html_e('Name', 'duplicator') ?> </td>
            <td><?php echo esc_html($package->Database->info->name); ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('Type', 'duplicator') ?> </td>
            <td><?php echo esc_html($package->Database->Type); ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('SQL Mode', 'duplicator') ?> </td>
            <td>
                <a href="?page=duplicator-settings&tab=package" target="_blank" class="link-style"><?php echo esc_html($dbbuild_mode); ?></a>
                <?php if ($mysqlcompat_on) : ?>
                    <br/>
                    <small style="font-style:italic; color:maroon">
                        <i class="fa fa-exclamation-circle"></i> <?php esc_html_e('MySQL Compatibility Mode Enabled', 'duplicator'); ?>
                        <a href="https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html#option_mysqldump_compatible" target="_blank">[<?php esc_html_e('details', 'duplicator'); ?>]</a>
                    </small>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td><?php esc_html_e('Filters', 'duplicator') ?> </td>
            <td><?php echo $package->Database->FilterOn == 1 ? 'On' : 'Off'; ?></td>
        </tr>
        <tr class="sub-section">
            <td>&nbsp;</td>
            <td>
                <b><?php esc_html_e('Tables', 'duplicator') ?></b><br/>
                <?php
                    echo isset($package->Database->FilterTables) && strlen($package->Database->FilterTables)
                        ? str_replace(',', "<br>\n", $package->Database->FilterTables)
                        : esc_html__('- no filters -', 'duplicator');
                ?>
            </td>
        </tr>
    </table>
</div>
</div>


<!-- ===============================
INSTALLER -->
<div class="dup-box" style="margin-bottom: 50px">
<div class="dup-box-title">
    <i class="fa fa-bolt fa-sm"></i> <?php esc_html_e('Installer', 'duplicator') ?>
    <div class="dup-box-arrow"></div>
</div>
<div class="dup-box-panel" id="dup-package-dtl-install-panel" style="<?php echo esc_html($ui_css_install); ?>">

    <table class='dup-dtl-data-tbl'>
        <tr>
            <td colspan="2">
                <div class="dup-install-hdr-2"><?php esc_html_e("Setup", 'duplicator') ?></div>
            </td>
        </tr>
        <tr>
            <td>
                <?php esc_html_e("Security", 'duplicator');?>
            </td>
            <td>
                <?php
                if ($dup_install_secure_on) {
                    _e('Password Protection Enabled', 'duplicator');
                } else {
                    _e('Password Protection Disabled', 'duplicator');
                }
                ?>
            </td>
        </tr>
        <?php if ($dup_install_secure_on) :?>
            <tr>
                <td></td>
                <td>
                    <div id="dup-pass-toggle">
                        <input type="password" name="secure-pass" id="secure-pass" readonly="true" value="<?php echo esc_attr($dup_install_secure_pass); ?>" />
                        <button type="button" id="secure-btn" onclick="Duplicator.Pack.TogglePassword()" title="<?php esc_attr_e('Show/Hide Password', 'duplicator'); ?>">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </td>
            </tr>
        <?php endif; ?>
    </table>
    <br/><br/>

    <table class='dup-dtl-data-tbl'>
        <tr>
            <td colspan="2">
                <div class="dup-install-hdr-2"><?php esc_html_e(" MySQL Server", 'duplicator') ?></div>
            </td>
        </tr>
        <tr>
            <td><?php esc_html_e('Host', 'duplicator') ?></td>
            <td><?php echo strlen($package->Installer->OptsDBHost) ? esc_html($package->Installer->OptsDBHost) : esc_html__('- not set -', 'duplicator') ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('Database', 'duplicator') ?></td>
            <td><?php echo strlen($package->Installer->OptsDBName) ? esc_html($package->Installer->OptsDBName) : esc_html__('- not set -', 'duplicator') ?></td>
        </tr>
        <tr>
            <td><?php esc_html_e('User', 'duplicator') ?></td>
            <td><?php echo strlen($package->Installer->OptsDBUser) ? esc_html($package->Installer->OptsDBUser) : esc_html__('- not set -', 'duplicator') ?></td>
        </tr>
    </table>
</div>
</div>

<?php if ($debug_on) : ?>
    <div style="margin:0">
        <a href="javascript:void(0)" onclick="jQuery(this).parent().find('.dup-pack-debug').toggle()">[<?php esc_html_e('View Package Object', 'duplicator') ?>]</a><br/>
        <pre class="dup-pack-debug" style="display:none"><?php @print_r($package); ?> </pre>
    </div>
<?php endif; ?>


<script>
jQuery(document).ready(function($)
{

    /*  Shows the 'Download Links' dialog
     *  @param db       The path to the sql file
     *  @param install  The path to the install file
     *  @param pack     The path to the package file */
    Duplicator.Pack.ShowLinksDialog = function(json)
    {
        var url = '#TB_inline?width=650&height=325&inlineId=dup-dlg-quick-path';
        tb_show("<?php esc_html_e('Package File Links', 'duplicator') ?>", url);

        var msg = <?php printf(
            '"%s" + "\n\n%s:\n" + json.archive + "\n\n%s:\n" + json.log + "\n\n%s";',
            '=========== SENSITIVE INFORMATION START ===========',
            esc_html__("ARCHIVE", 'duplicator'),
            esc_html__("LOG", 'duplicator'),
            '=========== SENSITIVE INFORMATION END ==========='
        );
                    ?>
        $("#dup-dlg-quick-path-data").val(msg);
        return false;
    }

    //LOAD: 'Download Links' Dialog and other misc setup
    Duplicator.Pack.GetLinksText = function() {$('#dup-dlg-quick-path-data').select();};

    Duplicator.Pack.OpenAll = function () {
        Duplicator.UI.IsSaveViewState = false;
        var states = [];
        $("div.dup-box").each(function() {
            var pan = $(this).find('div.dup-box-panel');
            var panel_open = pan.is(':visible');
            if (! panel_open)
                $( this ).find('div.dup-box-title').trigger("click");
            states.push({
                key: pan.attr('id'),
                value: 1
            });
        });
        Duplicator.UI.SaveMulViewStates(states);
        Duplicator.UI.IsSaveViewState = true;
    };

    Duplicator.Pack.CloseAll = function () {
        Duplicator.UI.IsSaveViewState = false;
        var states = [];
        $("div.dup-box").each(function() {
            var pan = $(this).find('div.dup-box-panel');
            var panel_open = pan.is(':visible');
            if (panel_open)
                $( this ).find('div.dup-box-title').trigger("click");
            states.push({
                key: pan.attr('id'),
                value: 0
            });
        });
        Duplicator.UI.SaveMulViewStates(states);
        Duplicator.UI.IsSaveViewState = true;
    };

    Duplicator.Pack.TogglePassword = function()
    {
        var $input  = $('#secure-pass');
        var $button =  $('#secure-btn');
        if (($input).attr('type') == 'text') {
            $input.attr('type', 'password');
            $button.html('<i class="fas fa-eye fa-xs"></i>');
        } else {
            $input.attr('type', 'text');
            $button.html('<i class="fas fa-eye-slash fa-xs"></i>');
        }
    }
});
</script>
