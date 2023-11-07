<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Controllers\WelcomeController;
use Duplicator\Core\Upgrade\UpgradeFunctions;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Upgrade/Install logic of plugin resides here
 */
class DUP_LITE_Plugin_Upgrade
{
    const DUP_VERSION_OPT_KEY         = 'duplicator_version_plugin';
    const PLUGIN_INSTALL_INFO_OPT_KEY = 'duplicator_install_info';

    /**
     * version starting from which the welcome page is shown
     */
    const DUP_WELCOME_PAGE_VERSION = '1.5.3';

    /**
     * wp_options key containing info about when the plugin was activated
     */
    const DUP_ACTIVATED_OPT_KEY = 'duplicator_activated';

    /**
     * Called as part of WordPress register_activation_hook
     *
     * @return void
     */
    public static function onActivationAction()
    {
        //NEW VS UPDATE
        if (($oldDupVersion = get_option(self::DUP_VERSION_OPT_KEY, false)) === false) {
            self::newInstallation();
        } else {
            self::updateInstallation($oldDupVersion);
        }

        DUP_Settings::Save();

        //Init Database & Backup Directories
        self::updateDatabase();
        DUP_Util::initSnapshotDirectory();

        do_action('duplicator_after_activation');
    }

    /**
     * Update install info.
     *
     * @param string $oldVersion The last/previous installed version, is empty for new installs
     *
     * @return array{version:string,time:int,updateTime:int}
     */
    protected static function setInstallInfo($oldVersion = '')
    {
        if (empty($oldVersion) || ($installInfo = get_option(self::PLUGIN_INSTALL_INFO_OPT_KEY, false)) === false) {
            // If is new installation or install info is not set generate new install info
            $installInfo = array(
                'version'    => DUPLICATOR_VERSION,
                'time'       => time(),
                'updateTime' => time(),
            );
        } else {
            $installInfo['updateTime'] = time();
        }

        if (($oldInfos = get_option(self::DUP_ACTIVATED_OPT_KEY, false)) !== false) {
            // Migrate the previously used option to install info and remove old option if exists
            $installInfo['version'] = $oldVersion;
            $installInfo['time']    = $oldInfos['lite'];
            delete_option(self::DUP_ACTIVATED_OPT_KEY);
        }

        delete_option(self::PLUGIN_INSTALL_INFO_OPT_KEY);
        update_option(self::PLUGIN_INSTALL_INFO_OPT_KEY, $installInfo, false);
        return $installInfo;
    }

    /**
     * Get install info.
     *
     * @return array{version:string,time:int,updateTime:int}
     */
    public static function getInstallInfo()
    {
        if (($installInfo = get_option(self::PLUGIN_INSTALL_INFO_OPT_KEY, false)) === false) {
            $installInfo = self::setInstallInfo();
        }
        return $installInfo;
    }

    /**
     * Runs only on new installs
     *
     * @return void
     */
    protected static function newInstallation()
    {
        UpgradeFunctions::performUpgrade(false, DUPLICATOR_VERSION);

        //WordPress Options Hooks
        update_option(self::DUP_VERSION_OPT_KEY, DUPLICATOR_VERSION);
        update_option(WelcomeController::REDIRECT_OPT_KEY, true);
        self::setInstallInfo();
    }

    /**
     * Run only on update installs
     *
     * @param string $oldVersion The last/previous installed version
     *
     * @return void
     */
    protected static function updateInstallation($oldVersion)
    {
        UpgradeFunctions::performUpgrade($oldVersion, DUPLICATOR_VERSION);

        //WordPress Options Hooks
        update_option(self::DUP_VERSION_OPT_KEY, DUPLICATOR_VERSION);
        self::setInstallInfo($oldVersion);
    }

     /**
     * Runs for both new and update installs and creates the database tables
     *
     * @return void
     */
    protected static function updateDatabase()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . "duplicator_packages";

        //PRIMARY KEY must have 2 spaces before for dbDelta to work
        //see: https://codex.wordpress.org/Creating_Tables_with_Plugins
        $sql = "CREATE TABLE `{$table_name}` (
			   id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			   name VARCHAR(250) NOT NULL,
			   hash VARCHAR(50) NOT NULL,
			   status INT(11) NOT NULL,
			   created DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			   owner VARCHAR(60) NOT NULL,
			   package LONGTEXT NOT NULL,
			   PRIMARY KEY  (id),
			   KEY hash (hash))";

        $abs_path = duplicator_get_abs_path();
        require_once($abs_path . '/wp-admin/includes/upgrade.php');
        @dbDelta($sql);
    }
}
