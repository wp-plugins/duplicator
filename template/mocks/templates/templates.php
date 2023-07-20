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
<form class="mock-blur" style="margin-top: 20px;" action="#" method="post">
    <!-- ====================
    TOOL-BAR -->
    <table style="margin-bottom: 10px; width: 100%;">
        <tbody>
        <tr>
            <td>
                <select id="bulk_action">
                    <option value="-1" selected="selected">Bulk Actions</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="button" class="button action" value="Apply">
            </td>
            <td style="text-align: right;">
                <div class="btnnav">
                    <a href="#" class="button dup-add-template-btn">Add New</a>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <!-- ====================
    LIST ALL SCHEDULES -->
    <table class="widefat dup-template-list-tbl striped">
        <thead>
        <tr>
            <th class="col-check"><input type="checkbox" title="Select all packages"></th>
            <th class="col-name">Name</th>
            <th class="col-recover">Recovery</th>
            <th class="col-empty"></th>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td class="col-check">
                    <input type="checkbox" disabled="">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Default </a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="col-check">
                    <input name="selected_id[]" type="checkbox" value="16" class="item-chk">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Full site backup</a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td class="col-check">
                    <input name="selected_id[]" type="checkbox" value="16" class="item-chk">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Database only backup</a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        <a href="#" id="dup-template-recoveable-info-2" class="dup-template-recoveable-info"><u>Disabled</u></a>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td class="col-check">
                    <input name="selected_id[]" type="checkbox" value="16" class="item-chk">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Uploads folder filtered</a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td class="col-check">
                    <input name="selected_id[]" type="checkbox" value="16" class="item-chk">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Non-WP tables filtered</a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        <a href="#" id="dup-template-recoveable-info-2" class="dup-template-recoveable-info"><u>Disabled</u></a>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="col-check">
                    <input name="selected_id[]" type="checkbox" value="16" class="item-chk">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Full site backup</a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td class="col-check">
                    <input name="selected_id[]" type="checkbox" value="16" class="item-chk">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Database only backup</a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        <a href="#" id="dup-template-recoveable-info-2" class="dup-template-recoveable-info"><u>Disabled</u></a>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td class="col-check">
                    <input name="selected_id[]" type="checkbox" value="16" class="item-chk">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Uploads folder filtered</a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        Available<sup><i class="fas fa-undo-alt fa-fw fa-sm"></i></sup>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <td class="col-check">
                    <input name="selected_id[]" type="checkbox" value="16" class="item-chk">
                </td>
                <td class="col-name">
                    <a href="#" class="name">Non-WP tables filtered</a>
                </td>
                <td class="col-recover">
                    <span class="dup-template-recoveable-info-wrapper">
                        <a href="#" id="dup-template-recoveable-info-2" class="dup-template-recoveable-info"><u>Disabled</u></a>
                    </span>
                </td>
                <td>&nbsp;</td>
            </tr>
        </tbody>
        <tfoot>
        <tr>
            <th colspan="8" style="text-align:right; font-size:12px">
                Total: 2
            </th>
        </tr>
        </tfoot>
    </table>
</form>
<?php TplMng::getInstance()->render(
    'parts/Education/static-popup',
    array(
        'title'        => __('Easily customize your backups with templates!', 'duplicator'),
        'warning-text' => __('Templates are not available in Duplicator Lite!', 'duplicator'),
        'content-tpl'  => 'mocks/templates/content-popup',
        'upsell-url'   => Upsell::getCampaignUrl('blurred-mocks', 'Templates')
    ),
    true
); ?>