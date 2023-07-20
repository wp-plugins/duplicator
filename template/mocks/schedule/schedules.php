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
<div class="wrap">
    <h1>Schedules</h1>
    <div class="mock-blur">
        <!-- ====================
        TOOL-BAR -->
        <table class="dpro-edit-toolbar">
            <tbody>
            <tr>
                <td>
                    <select id="bulk_action">
                        <option selected="selected">Bulk Actions</option>
                        <option>Activate</option>
                        <option>Deactivate</option>
                        <option>Delete</option>
                    </select>
                    <input type="button" id="dup-schedule-bulk-apply" class="button action" value="Apply">
                    <span class="btn-separator"></span>
                    <a href="#" class="button grey-icon dup-schedule-settings"><i class="fas fa-sliders-h fa-fw"></i></a>
                    <a href="#" id="btn-logs-dialog" class="button dup-schedule-templates"><i class="far fa-clone"></i></a>
                </td>
                <td>
                    <div class="btnnav">
                        <a href="#" class="button dup-schedule-add-new">Add New</a>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>

        <form id="dup-schedule-form" action="#" method="post">
            <!-- ====================
            LIST ALL SCHEDULES -->
            <table class="widefat schedule-tbl">
                <thead>
                <tr>
                    <th style="width:10px;"><input type="checkbox" id="dpro-chk-all"></th>
                    <th style="width:255px;">Name</th>
                    <th>Storage</th>
                    <th>Runs Next</th>
                    <th>Last Ran</th>
                    <th>Active</th>
                    <th class="dup-col-recovery">Recovery</th>
                </tr>
                </thead>
                <tbody>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Daily Schedule - Default Local</a>
                        </td>
                        <td>Default</td>
                        <td>January 1, 2023 0:00 - Daily</td>
                        <td>
                            December 31, 2022 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Weekly Schedule - DropBox</a>
                        </td>
                        <td>DropBox</td>
                        <td>January 8, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Monthly Schedule - GDrive</a>
                        </td>
                        <td>Google Drive</td>
                        <td>February 1, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Monthly Schedule - All Storages</a>
                        </td>
                        <td>Local, Google Drive, FTP, SFTP,</br>S3, OneDrive, DropBox</td>
                        <td>February 1, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Daily Schedule - Default Local</a>
                        </td>
                        <td>Default</td>
                        <td>January 1, 2023 0:00 - Daily</td>
                        <td>
                            December 31, 2022 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Weekly Schedule - DropBox</a>
                        </td>
                        <td>DropBox</td>
                        <td>January 8, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Monthly Schedule - GDrive</a>
                        </td>
                        <td>Google Drive</td>
                        <td>February 1, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Monthly Schedule - All Storages</a>
                        </td>
                        <td>Local, Google Drive, FTP, SFTP,</br>S3, OneDrive, DropBox</td>
                        <td>February 1, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Daily Schedule - Default Local</a>
                        </td>
                        <td>Default</td>
                        <td>January 1, 2023 0:00 - Daily</td>
                        <td>
                            December 31, 2022 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Weekly Schedule - DropBox</a>
                        </td>
                        <td>DropBox</td>
                        <td>January 8, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Monthly Schedule - GDrive</a>
                        </td>
                        <td>Google Drive</td>
                        <td>February 1, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                    <tr class="schedule-row">
                        <td>
                            <input type="checkbox" class="item-chk">
                        </td>
                        <td>
                            <a id="text-51" href="#" class="name">Monthly Schedule - All Storages</a>
                        </td>
                        <td>Local, Google Drive, FTP, SFTP,</br>S3, OneDrive, DropBox</td>
                        <td>February 1, 2023 0:00 - Weekly</td>
                        <td>
                            January 1, 2023 0:00
                        </td>
                        <td><b><span class="green">Yes</span></b></td>
                        <td class="dup-col-recovery">
                            <span class="dup-template-recoveable-info-wrapper">Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup></span>
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="7" style="text-align:right; white-space: nowrap; font-size:12px">
                        Total: 12 | Active: 12 | Time: <span id="dpro-clock-container">00:00:01</span></th>
                </tr>
                </tfoot>
            </table>
        </form>
    </div>
</div>
<?php
TplMng::getInstance()->render(
    'parts/Education/static-popup',
    array(
        'title'        => __('Automate your workflow with scheduled backups!', 'duplicator'),
        'warning-text' => __('Duplicator Lite does not support scheduled backups!', 'duplicator'),
        'content-tpl'  => 'mocks/schedule/content-popup',
        'upsell-url'   => Upsell::getCampaignUrl('blurred-mocks', 'Schedules')
    )
);
?>
