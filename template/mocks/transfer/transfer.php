<?php

use Duplicator\Core\Views\TplMng;
use Duplicator\Utils\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>

<div class="transfer-panel mock-blur">

    <div class="transfer-hdr">
        <h2 class="title">
            <i class="fas fa-exchange-alt"></i> Manual Transfer
        </h2>
        <hr>
    </div>
    <!-- ===================
    STEP 1 -->
    <div id="step2-section">
        <div style="margin:0px 0 0px 0">
            <h3>Step 1: Choose Location</h3>
            <input style="display:none" type="radio" name="location" id="location-storage" checked="checked" onclick="DupPro.Pack.Transfer.ToggleLocation()">
            <label style="display:none" for="location-storage">Storage</label>
            <input style="display:none" type="radio" name="location" id="location-quick" onclick="DupPro.Pack.Transfer.ToggleLocation()">
            <label style="display:none" for="location-quick">Quick FTP Connect</label>
        </div>

        <!-- STEP 1: STORAGE -->
        <table id="location-storage-opts" class="widefat">
            <thead>
            <tr>
                <th style="white-space: nowrap; width:10px;"></th>
                <th style="width:125px">Type</th>
                <th style="width:275px">Name</th>
                <th style="white-space: nowrap">Location</th>
            </tr>
            </thead>
            <tbody>
            <tr class="package-row alternate">
                <td>
                    <input name="edit_id" type="hidden" value="1">
                    <input class="duppro-storage-input" id="dup-chkbox-50" name="_storage_ids[]" 
                        data-parsley-errors-container="#storage_error_container" data-parsley-mincheck="1" 
                        data-parsley-required="true" type="checkbox" value="50">
                </td>
                <td>
                    <label for="dup-chkbox-50" class="dup-store-lbl">
                        <i class="fab fa-dropbox fa-fw"></i>&nbsp;Dropbox                                </label>
                </td>
                <td>
                    <a href="?page=duplicator-pro-storage&amp;tab=storage&amp;inner_page=edit&amp;storage_id=50" target="_blank">
                        &nbsp;DropBox                                </a>
                </td>
                <td>
                    <a href="https://dropbox.com/home" target="_blank">https://dropbox.com/home</a>                            </td>
            </tr>

            <tr class="package-row ">
                <td>
                    <input name="edit_id" type="hidden" value="2">
                    <input class="duppro-storage-input" id="dup-chkbox-53" 
                        name="_storage_ids[]" data-parsley-errors-container="#storage_error_container" type="checkbox" value="53">
                </td>
                <td>
                    <label for="dup-chkbox-53" class="dup-store-lbl">
                        <i class="fab fa-google-drive fa-fw"></i>&nbsp;Google Drive                                </label>
                </td>
                <td>
                    <a href="?page=duplicator-pro-storage&amp;tab=storage&amp;inner_page=edit&amp;storage_id=53" target="_blank">
                        &nbsp;Google Drive                                </a>
                </td>
                <td>
                    <a href="https://drive.google.com/drive/" target="_blank">google://Duplicator Backups/duplicator.com</a>                            </td>
            </tr>

            <tr class="package-row alternate">
                <td>
                    <input name="edit_id" type="hidden" value="3">
                    <input class="duppro-storage-input" id="dup-chkbox-54" 
                        name="_storage_ids[]" data-parsley-errors-container="#storage_error_container" type="checkbox" value="54">
                </td>
                <td>
                    <label for="dup-chkbox-54" class="dup-store-lbl">
                        <i class="fas fa-cloud fa-fw"></i>&nbsp;OneDrive                                </label>
                </td>
                <td>
                    <a href="?page=duplicator-pro-storage&amp;tab=storage&amp;inner_page=edit&amp;storage_id=54" target="_blank">
                        &nbsp;OneDrive                                </a>
                </td>
                <td>
                    Not Authenticated                            </td>
            </tr>

            <tr class="package-row ">
                <td>
                    <input name="edit_id" type="hidden" value="4">
                    <input class="duppro-storage-input" id="dup-chkbox-55" 
                        name="_storage_ids[]" data-parsley-errors-container="#storage_error_container" type="checkbox" value="55">
                </td>
                <td>
                    <label for="dup-chkbox-55" class="dup-store-lbl">
                        <i class="fas fa-network-wired fa-fw"></i>&nbsp;FTP                                </label>
                </td>
                <td>
                    <a href="?page=duplicator-pro-storage&amp;tab=storage&amp;inner_page=edit&amp;storage_id=55" target="_blank">
                        &nbsp;FTP                                </a>
                </td>
                <td>
                    <a href="ftp://a:21//mduplicator.test" target="_blank">ftp://duplicator.com:21/duplicator.com</a>                            </td>
            </tr>

            <tr class="package-row alternate">
                <td>
                    <input name="edit_id" type="hidden" value="5">
                    <input class="duppro-storage-input" id="dup-chkbox-56"
                        name="_storage_ids[]" data-parsley-errors-container="#storage_error_container" type="checkbox" value="56">
                </td>
                <td>
                    <label for="dup-chkbox-56" class="dup-store-lbl">
                        <i class="fas fa-network-wired fa-fw"></i>&nbsp;SFTP                                </label>
                </td>
                <td>
                    <a href="?page=duplicator-pro-storage&amp;tab=storage&amp;inner_page=edit&amp;storage_id=56" target="_blank">
                        &nbsp;SFTP                                </a>
                </td>
                <td>
                    <a href=":22" target="_blank">duplicator.com:22</a>                            </td>
            </tr>


            </tbody>
            <tbody><tr class="dup-choose-loc-new-pack">
                <td colspan="4">
                    <a href="admin.php?page=duplicator-pro-storage&amp;tab=storage&amp;inner_page=edit" target="_blank">
                        [Create New Storage]
                    </a>
                </td>
            </tr>
            </tbody></table>

    </div>

    <!-- ===================
    STEP 2 -->
    <div id="step3-section">
        <h3>
            Step 2: Transfer Files  
            <button 
                id="dup-pro-transfer-btn" 
                type="button" class="button button-large button-primary" 
            >
                Start Transfer &nbsp; <i class="fas fa-upload"></i>

            </button>
        </h3>

        <div style="width: 700px; text-align: center; margin-left: auto; margin-right: auto; display: none;" class="dpro-active-status-area">
            <div style="display:none; font-size:20px; font-weight:bold" id="dpro-progress-bar-percent"></div>
            <div style="font-size:14px" id="dpro-progress-bar-text">Processing</div>
            <div id="dpro-progress-bar-percent-help">
                <small>Full package percentage shown on packages screen</small>
            </div>
        </div>

        <div class="dpro-progress-bar-container">
            <div id="dpro-progress-bar-area" class="dpro-active-status-area" style="display: none;">
                <div class="dup-pro-meter-wrapper">
                    <div class="dup-pro-meter blue dup-pro-fullsize">
                        <span></span>
                    </div>
                    <span class="text"></span>
                </div>
                <button 
                    disabled="" 
                    id="dup-pro-stop-transfer-btn" 
                    type="button" 
                    class="button button-large button-primarybutton dpro-btn-stop" 
                    value="" onclick="DupPro.Pack.Transfer.StopBuild();"
                >
                    <i class="fa fa-times fa-sm"></i> &nbsp; Stop Transfer               
                </button>
            </div>
        </div>
    </div>

    <!-- ===============================
    TRANSFER LOG -->
    <div class="dup-box">
        <div class="dup-box-title">
            <i class="fas fa-file-contract fa-fw fa-sm"></i>
            Transfer Log
        </div>
        <div class="dup-box-panel" id="dup-transfer-transfer-log" style="display:block">
            <table class="widefat package-tbl">
                <thead>
                <tr>
                    <th style="width:150px">Started</th>
                    <th style="width:150px">Stopped</th>
                    <th style="white-space: nowrap">Status</th>
                    <th style="white-space: nowrap">Type</th>
                    <th style="width: 60%; white-space: nowrap">Description</th>
                </tr>
                </thead>
                <tbody><tr class="package-row  status-normal">
                    <td>Sun, 01 Jan 00:00:00</td>
                    <td>Sun, 01 Jan 00:01:00</td>
                    <td>Succeeded</td>
                    <td>Dropbox</td>
                    <td>Transferred package to Dropbox folder duplicator.com</td>
                </tr></tbody>
                <tfoot>
                <tr>
                    <td colspan="5" id="dup-pack-details-trans-log-count">Log Items: 1</td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
<?php TplMng::getInstance()->render(
    'parts/Education/static-popup',
    array(
    'title'        => __('Manually transfer backups to remote storages!', 'duplicator'),
    'warning-text' => __('Remote storages are not available in Duplicator Lite!', 'duplicator'),
    'content-tpl'  => 'mocks/transfer/content-popup',
    'upsell-url'   => Upsell::getCampaignUrl('blurred-mocks', 'Details Transfer')
    ),
    true
); ?>
