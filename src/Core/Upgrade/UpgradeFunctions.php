<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core\Upgrade;

use DUP_Settings;
use Duplicator\Utils\Email\EmailSummary;

/**
 * Utility class managing actions when the plugin is updated
 */
class UpgradeFunctions
{
    const LAST_VERSION_EMAIL_SUMMARY_WRONG_KEY = '1.5.6.1';
    const FIRST_VERSION_NEW_STORAGE_POSITION   = '1.3.35';

    /**
    * This function is executed when the plugin is activated and
    * every time the version saved in the wp_options is different from the plugin version both in upgrade and downgrade.
    *
    * @param false|string $currentVersion current Duplicator version, false if is first installation
    * @param string       $newVersion     new Duplicator Version
    *
    * @return void
    */
    public static function performUpgrade($currentVersion, $newVersion)
    {
        self::updateStoragePostition($currentVersion);
        self::emailSummaryOptKeyUpdate($currentVersion);
    }

    /**
     * Update email summary option key seperator from '-' to '_'
     *
     * @param false|string $currentVersion current Duplicator version, false if is first installation
     *
     * @return void
     */
    private static function emailSummaryOptKeyUpdate($currentVersion)
    {
        if ($currentVersion == false || version_compare($currentVersion, self::LAST_VERSION_EMAIL_SUMMARY_WRONG_KEY, '>')) {
            return;
        }

        if (($data = get_option(EmailSummary::INFO_OPT_OLD_KEY)) !== false) {
            update_option(EmailSummary::INFO_OPT_KEY, $data);
            delete_option(EmailSummary::INFO_OPT_OLD_KEY);
        }
    }

    /**
     * Update storage position option
     *
     * @param false|string $currentVersion current Duplicator version, false if is first installation
     *
     * @return void
     */
    private static function updateStoragePostition($currentVersion)
    {
        //PRE 1.3.35
        //Do not update to new wp-content storage till after
        if ($currentVersion !== false && version_compare($currentVersion, self::FIRST_VERSION_NEW_STORAGE_POSITION, '<')) {
            DUP_Settings::Set('storage_position', DUP_Settings::STORAGE_POSITION_LEGACY);
        }
    }
}
