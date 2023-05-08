<?php

use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<h3 class="title"><?php _e("Roles and Permissions", "duplicator"); ?></h3>
<hr size="1">
<div class="access-mock mock-blur">
    <p>
        <?php _e("Select the user roles and/or users that are allowed to manage different aspects of Duplicator.", "duplicator"); ?> <br>
        <?php _e("By default, all permissions are provided only to administrator users.", "duplicator") ?> <br>
        <?php _e("Some capabilities depend on others so If you select for example storage capability automatically the package " .
                "read and package edit capabilities are assigned", "duplicator") ?><br>
        <b><?php _e("It is not possible to self remove the manage settings capabilities.", "duplicator"); ?></b>
    </p>
    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row"><?php _e("Package Read ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e(" - Package Edit ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e(" - - Manage Schedules ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e(" - - Manage Storages ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e(" - Restore Backup ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e(" - - Package Import ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e(" - Package Export ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e(" - Manage Settings ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e(" -  - Manage License Settings ", "duplicator") ?><i class="fas fa-question-circle fa-sm"></i></th>
                <td>
                    <div class="mock-select2">
                        <div class="select2-option">
                            <?php _e("Administrator", "duplicator") ?>
                        </div>
                    </div> 
                </td>
            </tr>
        </tbody>
    </table>
</div> 
<?php TplMng::getInstance()->render(
    'parts/Education/static-popup',
    array(
        'title'        => __('Advanced Backup Permissions', 'duplicator'),
        'warning-text' => __('Advanced Backup Permissions are not available in Duplicator Lite!', 'duplicator'),
        'content-tpl'  => 'mocks/settings/access/content-popup',
        'upsell-url'   => Upsell::getCampaignUrl('blurred-mocks', 'Settings Access Tab')
    ),
    true
); ?>
