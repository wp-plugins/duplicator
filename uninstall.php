<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * Maintain PHP 5.2 compatibility, don't use namespace and don't include Duplicator Libs
 */

// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Uninstall class
 * Maintain PHP 5.2 compatibility, don't use namespace and don't include Duplicator Libs.
 * This is a standalone class.
 */
class DuplicatorLiteUninstall // phpcs:ignore
{
    const PACKAGES_TABLE_NAME           = 'duplicator_packages';
    const VERSION_OPTION_KEY            = 'duplicator_version_plugin';
    const UNINSTALL_PACKAGE_OPTION_KEY  = 'duplicator_uninstall_package';
    const UNINSTALL_SETTINGS_OPTION_KEY = 'duplicator_uninstall_settings';
    const SSDIR_NAME_LEGACY             = 'wp-snapshots';
    const SSDIR_NAME_NEW                = 'backups-dup-lite';

    /**
     * Uninstall plugin
     *
     * @return void
     */
    public static function uninstall()
    {
        try {
            do_action('duplicator_unistall');
            self::removePackages();
            self::removeSettings();
            self::removePluginVersion();
        } catch (Exception $e) {
            // Prevent error on uninstall
        } catch (Error $e) {
            // Prevent error on uninstall
        }
    }

    /**
     * Remove plugin option version
     *
     * @return void
     */
    private static function removePluginVersion()
    {
        delete_option(self::VERSION_OPTION_KEY);
    }

    /**
     * Return duplicator PRO backup path legacy
     *
     * @return string
     */
    private static function getSsdirPathLegacy()
    {
        return trailingslashit(wp_normalize_path(realpath(ABSPATH))) . self::SSDIR_NAME_LEGACY;
    }

    /**
     * Return duplicator PRO backup path
     *
     * @return string
     */
    private static function getSsdirPathWpCont()
    {
        return trailingslashit(wp_normalize_path(realpath(WP_CONTENT_DIR))) . self::SSDIR_NAME_NEW;
    }

    /**
     * Remove all packages
     *
     * @return void
     */
    private static function removePackages()
    {
        if (get_option(self::UNINSTALL_PACKAGE_OPTION_KEY) != true) {
            return;
        }

        $tableName = $GLOBALS['wpdb']->base_prefix . self::PACKAGES_TABLE_NAME;
        $GLOBALS['wpdb']->query('DROP TABLE IF EXISTS ' . $tableName);

        $fsystem = new WP_Filesystem_Direct(true);
        $fsystem->rmdir(self::getSsdirPathWpCont(), true);
        $fsystem->rmdir(self::getSsdirPathLegacy(), true);
    }

    /**
     * Remove plugins settings
     *
     * @return void
     */
    private static function removeSettings()
    {
        if (get_option(self::UNINSTALL_SETTINGS_OPTION_KEY) != true) {
            return;
        }

        self::deleteUserMetaKeys();
        self::deleteOptions();
        self::deleteTransients();
    }

    /**
     * Delete all users meta key
     *
     * @return void
     */
    private static function deleteUserMetaKeys()
    {
        /** @var wpdb */
        global $wpdb;

        $wpdb->query("DELETE FROM " . $wpdb->usermeta . " WHERE meta_key REGEXP '^duplicator_(?!pro_)'");
    }

    /**
     * Delete all options
     *
     * @return void
     */
    private static function deleteOptions()
    {
        $optionsTableName = $GLOBALS['wpdb']->base_prefix . "options";
        $dupOptionNames   = $GLOBALS['wpdb']->get_col(
            "SELECT `option_name` FROM `{$optionsTableName}` WHERE `option_name` REGEXP '^duplicator_(?!pro_|expire_)'"
        );

        foreach ($dupOptionNames as $dupOptionName) {
            delete_option($dupOptionName);
        }
    }

    /**
     * Delete all transients
     *
     * @return void
     */
    private static function deleteTransients()
    {
        $optionsTableName        = $GLOBALS['wpdb']->base_prefix . "options";
        $dupOptionTransientNames = $GLOBALS['wpdb']->get_col(
            "SELECT `option_name` FROM `{$optionsTableName}` WHERE `option_name` REGEXP '^_transient_duplicator_(?!pro_)'"
        );

        foreach ($dupOptionTransientNames as $dupOptionTransientName) {
            delete_transient(str_replace("_transient_", "", $dupOptionTransientName));
        }
    }
}

DuplicatorLiteUninstall::uninstall();
