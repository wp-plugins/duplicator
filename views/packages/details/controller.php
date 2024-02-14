<?php

use Duplicator\Installer\Utils\LinkManager;
use Duplicator\Core\Bootstrap;
use Duplicator\Core\Views\TplMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
DUP_Util::hasCapability('manage_options');
global $wpdb;

//COMMON HEADER DISPLAY
$current_tab = isset($_REQUEST['tab']) ? sanitize_text_field($_REQUEST['tab']) : 'detail';
$package_id  = isset($_REQUEST["id"])  ? sanitize_text_field($_REQUEST["id"]) : 0;
$package     = DUP_Package::getByID($package_id);
$err_found   = ($package == null || $package->Status < 100);
?>

<style>
    .narrow-input { width: 80px; }
    .wide-input {width: 400px; }
     table.form-table tr td { padding-top: 25px; }
     div.all-packages {float:right; margin-top: -35px; }
</style>

<div class="wrap">
    <?php
        duplicator_header(__("Package Details &raquo; {$package->Name}", 'duplicator'));
    ?>

    <?php if ($err_found) :?>
    <div class="error">
        <p>
        <?php printf(
            _x(
                'This package contains an error. Please review the %1$spackage log%2$s for details.',
                '%1 and %2 are replaced with <a> and </a> respectively',
                'duplicator'
            ),
            '<a href="' . DUP_Settings::getSsdirUrl() . '/{$package->NameHash}.log" target="_blank">',
            '</a>'
        );
        ?>
        <?php printf(
            _x(
                'For help visit the %1$sFAQ%2$s and %3$sresources page%4$s.',
                '%1, %3 and %2, %4 are replaced with <a> and </a> respectively',
                'duplicator'
            ),
            '<a href="' . esc_url(LinkManager::getCategoryUrl(LinkManager::TROUBLESHOOTING_CAT, 'failed_package_details_notice', 'FAQ')) . '" target="_blank">',
            '</a>',
            '<a href="' . esc_url(LinkManager::getCategoryUrl(LinkManager::RESOURCES_CAT, 'failed_package_details_notice', 'resources page')) . '" target="_blank">',
            '</a>'
        );
        ?>
        </p>
    </div>
    <?php endif; ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=duplicator&action=detail&tab=detail&id=<?php echo absint($package_id); ?>" class="nav-tab <?php echo ($current_tab == 'detail') ? 'nav-tab-active' : '' ?>">
            <?php esc_html_e('Details', 'duplicator'); ?>
        </a>
        <a href="?page=duplicator&action=detail&tab=transfer&id=<?php echo absint($package_id); ?>" class="nav-tab <?php echo ($current_tab == 'transfer') ? 'nav-tab-active' : '' ?>">
            <?php esc_html_e('Transfer', 'duplicator'); ?>
        </a>
    </h2>
    <div class="all-packages"><a href="?page=duplicator" class="button"><i class="fa fa-archive fa-sm"></i> <?php esc_html_e('Packages', 'duplicator'); ?></a></div>

    <?php
    switch ($current_tab) {
        case 'detail':
            include(DUPLICATOR_PLUGIN_PATH . 'views/packages/details/detail.php');
            break;
        case 'transfer':
            Bootstrap::mocksStyles();
            TplMng::getInstance()->render('mocks/transfer/transfer', array(), true);
            break;
    }
    ?>
</div>
