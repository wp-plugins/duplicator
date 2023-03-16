<?php

use Duplicator\Controllers\AboutUsController;
use Duplicator\Core\Controllers\ControllersManager;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>

<h2 class="nav-tab-wrapper">
    <a href="<?php echo ControllersManager::getMenuLink(ControllersManager::ABOUT_US_SUBMENU_SLUG, AboutUsController::ABOUT_US_TAB);?>"
       class="nav-tab <?php echo ($tplData['active_tab'] === AboutUsController::ABOUT_US_TAB) ? 'nav-tab-active' : '' ?>">
        <?php esc_html_e('About Us', 'duplicator'); ?>
    </a>
    <a href="<?php echo ControllersManager::getMenuLink(ControllersManager::ABOUT_US_SUBMENU_SLUG, AboutUsController::GETTING_STARTED);?>"
       class="nav-tab <?php echo ($tplData['active_tab'] === AboutUsController::GETTING_STARTED) ? 'nav-tab-active' : '' ?>">
        <?php esc_html_e('Getting Started', 'duplicator'); ?>
    </a>
    <a href="<?php echo ControllersManager::getMenuLink(ControllersManager::ABOUT_US_SUBMENU_SLUG, AboutUsController::LITE_VS_PRO);?>"
       class="nav-tab <?php echo ($tplData['active_tab'] === AboutUsController::LITE_VS_PRO) ? 'nav-tab-active' : '' ?>">
        <?php esc_html_e('Lite vs Pro', 'duplicator'); ?>
    </a>
</h2>