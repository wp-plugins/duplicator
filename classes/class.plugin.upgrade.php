<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Upgrade/Install logic of plugin resides here
 *
 */
class DUP_LITE_Plugin_Upgrade
{
    const DUP_VERSION_OPT_KEY = 'duplicator_version_plugin';

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

        self::setActivatedTime();

        //Init Database & Backup Directories
        self::updateDatabase();
        DUP_Util::initSnapshotDirectory();
    }

    /**
     * Set time of plugin activation in wp-options
     *
     * @return void
     */
    public static function setActivatedTime()
    {
        if (get_option(self::DUP_ACTIVATED_OPT_KEY, false) !== false) {
            return;
        }

        update_option(self::DUP_ACTIVATED_OPT_KEY, array('lite' => time()));
    }

     /**
     * Runs only on new installs
     *
     * @return void
     */
    protected static function newInstallation()
    {
        //WordPress Options Hooks
        update_option(self::DUP_VERSION_OPT_KEY, DUPLICATOR_VERSION);
    }

    /**
     * Run only on update installs
     *
     * @param string $oldVersion  The last/previous installed version
     *
     * @return void
     */
    protected static function updateInstallation($oldVersion)
    {
        //PRE 1.3.35
        //Do not update to new wp-content storage till after
        if (version_compare($oldVersion, '1.3.35', '<')) {
            DUP_Settings::Set('storage_position', DUP_Settings::STORAGE_POSITION_LEGACY);
            DUP_Settings::Save();
        }

        //WordPress Options Hooks
        update_option(self::DUP_VERSION_OPT_KEY, DUPLICATOR_VERSION);
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
