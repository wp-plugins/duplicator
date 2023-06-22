<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Controllers\ToolsPageController;
use Duplicator\Core\Controllers\ControllersManager;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

$templatesURL = ControllersManager::getMenuLink(
    ControllersManager::TOOLS_SUBMENU_SLUG,
    'templates'
);
$recoveryURl  = ControllersManager::getMenuLink(
    ControllersManager::TOOLS_SUBMENU_SLUG,
    'recovery'
);

?>
<div class="dup-section-sections">
    <ul>
        <li class="dup-flex-content">
            <span class="dup-section-label-fixed-width" >
                <span class="dashicons dashicons-update gary"></span>
                <a href="<?php echo esc_url(ControllersManager::getMenuLink(ControllersManager::SCHEDULES_SUBMENU_SLUG)); ?>"><?php
                    echo esc_html(sprintf(
                        _n(
                            '%s Schedule',
                            '%s Schedules',
                            $tplData['numSchedules'],
                            'duplicator'
                        ),
                        $tplData['numSchedules']
                    ));
                    ?></a>
            </span>
            <span>
                <?php _e('Enabled', 'duplicator'); ?>: 
                <b class="<?php echo ($tplData['numSchedulesEnabled'] ? 'green' : 'maroon'); ?>">
                    <?php echo $tplData['numSchedulesEnabled']; ?>
                </b>
                <?php if (strlen($tplData['nextScheduleString'])) { ?>
                    - <?php _e('Next', 'duplicator'); ?>: <b><?php echo $tplData['nextScheduleString']; ?></b>
                <?php } ?>
            </span>
        </li>
        <li>
            <span class="dup-section-label-fixed-width" >
                <span class="dashicons dashicons-database gary"></span>
                <a href="<?php echo esc_url(ControllersManager::getMenuLink(ControllersManager::STORAGE_SUBMENU_SLUG)); ?>"><?php
                    echo esc_html(sprintf(
                        _n(
                            '%s Storage',
                            '%s Storages',
                            $tplData['numStorages'],
                            'duplicator'
                        ),
                        $tplData['numStorages']
                    ));
                    ?>
                </a>
            </span>
        </li>
        <li>
            <span class="dup-section-label-fixed-width" >
                <span class="dashicons dashicons-admin-settings gary"></span>
                <a href="<?php echo esc_url($templatesURL); ?>"><?php
                    echo esc_html(sprintf(
                        _n(
                            '%s Template',
                            '%s Templates',
                            $tplData['numTemplates'],
                            'duplicator'
                        ),
                        $tplData['numTemplates']
                    ));
                    ?>
                </a>
            </span>
        </li>
        <li  class="dup-flex-content">
            <span class="dup-section-label-fixed-width" >
                <span class="dashicons dashicons-image-rotate gary"></span>
                <a href="<?php echo esc_url($recoveryURl); ?>" ><?php
                    esc_html_e('Recovery Point', 'duplicator');
                ?> 
                </a>
            </span>
            <span>
                <span class="maroon"><b><?php esc_html_e('Not set', 'duplicator'); ?></b></span>
            </span>
        </li>
    </ul>
</div>
