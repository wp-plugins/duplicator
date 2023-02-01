<?php

/**
 * Fired when the plugin is uninstalled.
 */

use Duplicator\Libs\Snap\SnapIO;

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// CHECK PHP VERSION
define('DUPLICATOR_LITE_PATH', dirname(__FILE__));
define('DUPLICATOR_LITE_PHP_MINIMUM_VERSION', '5.3.8');
define('DUPLICATOR_LITE_PHP_SUGGESTED_VERSION', '5.6.20');
require_once(dirname(__FILE__) . "/src/Utils/DuplicatorPhpVersionCheck.php");
if (DuplicatorPhpVersionCheck::check(DUPLICATOR_LITE_PHP_MINIMUM_VERSION, DUPLICATOR_LITE_PHP_SUGGESTED_VERSION) === false) {
    return;
}

require_once(DUPLICATOR_LITE_PATH . '/src/Utils/Autoloader.php');
\Duplicator\Utils\Autoloader::register();

require_once 'helper.php';
require_once 'define.php';
require_once 'classes/class.settings.php';
require_once 'classes/utilities/class.u.php';
require_once 'classes/class.plugin.upgrade.php';
global $wpdb;
DUP_Settings::init();

$wpdb->query("DELETE FROM " . $wpdb->usermeta . " WHERE meta_key='" . DUPLICATOR_ADMIN_NOTICES_USER_META_KEY . "'");
delete_option(DUP_LITE_Plugin_Upgrade::DUP_VERSION_OPT_KEY);
delete_option('duplicator_usage_id');
//Remove entire storage directory

if (DUP_Settings::Get('uninstall_files')) {
    $table_name = $wpdb->prefix . "duplicator_packages";
    $wpdb->query("DROP TABLE IF EXISTS `{$table_name}`");

    if (file_exists(DUP_Settings::getSsdirPathLegacy())) {
        SnapIO::rrmdir(DUP_Settings::getSsdirPathLegacy());
    }

    if (file_exists(DUP_Settings::getSsdirPathWpCont())) {
        SnapIO::rrmdir(DUP_Settings::getSsdirPathWpCont());
    }
}

//Remove all Settings
if (DUP_Settings::Get('uninstall_settings')) {
    DUP_Settings::Delete();
    \Duplicator\Core\Notifications\Notice::deleteOption();
    \Duplicator\Core\Notifications\NoticeBar::deleteOption();
    delete_option(DUP_LITE_Plugin_Upgrade::DUP_ACTIVATED_OPT_KEY);
    delete_option('duplicator_ui_view_state');
    delete_option('duplicator_package_active');
    delete_option("duplicator_exe_safe_mode");
    delete_option('duplicator_lite_inst_hash_notice');
    foreach ($GLOBALS['DUPLICATOR_OPTS_DELETE'] as $optionKey) {
        delete_option($optionKey);
    }
}
