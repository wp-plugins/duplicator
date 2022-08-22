<?php

/**
 * Utility class managing th emigration data
 *
 */

namespace Duplicator\Core;

use DUP_Archive;
use DUP_CTRL_Tools;
use DUP_Settings;
use DUP_UI_Notice;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapWP;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Utils\CachesPurge\CachesPurge;
use Error;
use Exception;

class MigrationMng
{
    const HOOK_FIRST_LOGIN_AFTER_INSTALL = 'duplicator_first_login_after_install';
    const HOOK_BOTTOM_MIGRATION_MESSAGE  = 'duplicator_bottom_migration_message';
    const FIRST_LOGIN_OPTION             = 'duplicator_first_login_after_install';
    const MIGRATION_DATA_OPTION          = 'duplicator_migration_data';
    const CLEAN_INSTALL_REPORT_OPTION    = 'duplicator_clean_install_report';

    /**
     * messages to be displayed in the successful migration box
     *
     * @var array
     */
    protected static $migrationCleanupReport = array(
        'removed' => array(),
        'stored'  => array(),
        'instFile' => array()
    );

    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'adminInit'));
        add_action(self::HOOK_FIRST_LOGIN_AFTER_INSTALL, array(__CLASS__, 'removeFirstLoginOption'));
        add_action(self::HOOK_FIRST_LOGIN_AFTER_INSTALL, array(__CLASS__, 'renameInstallersPhpFiles'));
        add_action(self::HOOK_FIRST_LOGIN_AFTER_INSTALL, array(__CLASS__, 'storeMigrationFiles'));
        add_action(self::HOOK_FIRST_LOGIN_AFTER_INSTALL, array(__CLASS__, 'setDupSettingsAfterInstall'));
        // save cleanup report after actions
        add_action(self::HOOK_FIRST_LOGIN_AFTER_INSTALL, array(__CLASS__, 'saveCleanupReport'), 100);

        // LAST BEACAUSE MAKE A WP_REDIRECT
        add_action(self::HOOK_FIRST_LOGIN_AFTER_INSTALL, array(__CLASS__, 'autoCleanFileAfterInstall'), 99999);
    }

    public static function adminInit()
    {
        if (self::isFirstLoginAfterInstall()) {
            add_action('current_screen', array(__CLASS__, 'wpAdminHook'), 99999);
            update_option(DUP_UI_Notice::OPTION_KEY_MIGRATION_SUCCESS_NOTICE, true);
            do_action(self::HOOK_FIRST_LOGIN_AFTER_INSTALL, self::getMigrationData());
        }
    }

    /**
     *
     */
    public static function wpAdminHook()
    {
        if (!DUP_CTRL_Tools::isToolPage()) {
            wp_redirect(
                ControllersManager::getMenuLink(
                    'duplicator-tools',
                    'diagnostics',
                    null,
                    array(),
                    false
                )
            );
            exit;
        }
    }

    /**
     *
     * @return boolean
     */
    public static function isFirstLoginAfterInstall()
    {
        if (is_user_logged_in() && get_option(self::FIRST_LOGIN_OPTION, false)) {
            if (is_multisite()) {
                if (is_super_admin()) {
                    return true;
                }
            } else {
                if (current_user_can('manage_options')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Purge all caches
     *
     * @return string[] // messages
     */
    public static function purgeCaches()
    {
        if (
            self::getMigrationData('restoreBackupMode') ||
            in_array(self::getMigrationData('installType'), array(4,5,6,7)) //update with define when installerstat will be in namespace
        ) {
            return array();
        }

        return CachesPurge::purgeAll();
    }

    /**
     *
     * @param array $migrationData
     * @return void
     */
    public static function autoCleanFileAfterInstall($migrationData)
    {
        if ($migrationData == false || $migrationData['cleanInstallerFiles'] == false) {
            return;
        }

        wp_redirect(DUP_CTRL_Tools::getCleanFilesAcrtionUrl(false));
        exit;
    }

    /**
     *
     * @param array $migrationData
     * @return void
     */
    public static function setDupSettingsAfterInstall($migrationData)
    {
        flush_rewrite_rules(true);
    }

    /**
     * return cleanup report
     *
     * @return array
     */
    public static function getCleanupReport()
    {
        $option = get_option(self::CLEAN_INSTALL_REPORT_OPTION);
        if (is_array($option)) {
            self::$migrationCleanupReport = array_merge(self::$migrationCleanupReport, $option);
        }

        return self::$migrationCleanupReport;
    }

    /**
     * save clean up report in wordpress options
     *
     * @return boolean
     */
    public static function saveCleanupReport()
    {
        return add_option(self::CLEAN_INSTALL_REPORT_OPTION, self::$migrationCleanupReport, '', 'no');
    }

    /**
     *
     * @param array $migrationData
     * @return void
     */
    public static function removeFirstLoginOption($migrationData)
    {
        delete_option(self::FIRST_LOGIN_OPTION);
    }

    /**
     *
     * @staticvar array $migrationData
     *
     * @param string|null $key
     * @return mixed
     */
    public static function getMigrationData($key = null)
    {
        static $migrationData = null;
        if (is_null($migrationData)) {
            $migrationData = get_option(self::MIGRATION_DATA_OPTION, false);
            if (is_string($migrationData)) {
                $migrationData = (array) json_decode($migrationData);
            }
        }

        if (is_null($key)) {
            return $migrationData;
        } elseif (isset($migrationData[$key])) {
            return $migrationData[$key];
        } else {
            return false;
        }
    }

    /**
     *
     * @return string
     */
    public static function getSaveModeWarning()
    {
        switch (self::getMigrationData('safeMode')) {
            case 1:
                //safe_mode basic
                return __('NOTICE: Safe mode (Basic) was enabled during install, be sure to re-enable all your plugins.', 'duplicator');
            case 2:
                //safe_mode advance
                return __('NOTICE: Safe mode (Advanced) was enabled during install, be sure to re-enable all your plugins.', 'duplicator');
            case 0:
            default:
                return '';
        }
    }

    /**
     * Check the root path and in case there are installer files without hashes rename them.
     *
     * @param integer $fileTimeDelay If the file is younger than $fileTimeDelay seconds then it is not renamed.
     *
     * @return void
     */
    public static function renameInstallersPhpFiles($fileTimeDelay = 0)
    {
        $fileTimeDelay = is_numeric($fileTimeDelay) ? (int) $fileTimeDelay : 0;

        $pathsTocheck = array(
            SnapIO::safePathTrailingslashit(ABSPATH),
            SnapIO::safePathTrailingslashit(SnapWP::getHomePath()),
            SnapIO::safePathTrailingslashit(WP_CONTENT_DIR)
        );

        $migrationData = self::getMigrationData();
        if (isset($migrationData['installerPath'])) {
            $pathsTocheck[] = SnapIO::safePathTrailingslashit(dirname($migrationData['installerPath']));
        }
        if (isset($migrationData['dupInstallerPath'])) {
            $pathsTocheck[] = SnapIO::safePathTrailingslashit(dirname($migrationData['dupInstallerPath']));
        }
        $pathsTocheck = array_unique($pathsTocheck);

        $filesToCheck = array();
        foreach ($pathsTocheck as $cFolder) {
            if (
                !is_dir($cFolder) ||
                !is_writable($cFolder) // rename permissions
            ) {
                continue;
            }
            $cFile = $cFolder . 'installer.php';
            if (
                !is_file($cFile) ||
                !SnapIO::chmod($cFile, 'u+rw') ||
                !is_readable($cFile)
            ) {
                continue;
            }
            $filesToCheck[] = $cFile;
        }

        $installerTplCheck = '/const\s+ARCHIVE_FILENAME\s*=\s*[\'"](.+?)[\'"]\s*;.*const\s+PACKAGE_HASH\s*=\s*[\'"](.+?)[\'"]\s*;/s';

        foreach ($filesToCheck as $file) {
            $fileName = basename($file);

            if ($fileTimeDelay > 0  && (time() - filemtime($file)) < $fileTimeDelay) {
                continue;
            }

            if (($content = @file_get_contents($file, false, null)) === false) {
                continue;
            }
            $matches = null;
            if (preg_match($installerTplCheck, $content, $matches) !== 1) {
                continue;
            }

            $archiveName = $matches[1];
            $hash        = $matches[2];
            $matches     = null;


            if (preg_match(DUPLICATOR_ARCHIVE_REGEX_PATTERN, $archiveName, $matches) !== 1) {
                if (SnapIO::unlink($file)) {
                    self::$migrationCleanupReport['instFile'][] = "<div class='failure'>"
                        . "<i class='fa fa-check green'></i> "
                        . sprintf(__('Installer file <b>%s</b> removed for security reasons', 'duplicator'), esc_html($fileName))
                        . "</div>";
                } else {
                    self::$migrationCleanupReport['instFile'][] = "<div class='success'>"
                        . '<i class="fa fa-exclamation-triangle red"></i> '
                        . sprintf(__('Can\'t remove installer file <b>%s</b>, please remove it for security reasons', 'duplicator'), esc_html($fileName))
                        . '</div>';
                }
                continue;
            }

            $archiveHash =  $matches[1];
            if (strpos($file, $archiveHash) === false) {
                if (SnapIO::rename($file, dirname($file) . '/' . $archiveHash . '_installer.php', true)) {
                    self::$migrationCleanupReport['instFile'][] = "<div class='failure'>"
                        . "<i class='fa fa-check green'></i> "
                        . sprintf(__('Installer file <b>%s</b> renamed with HASH', 'duplicator'), esc_html($fileName))
                        . "</div>";
                } else {
                    self::$migrationCleanupReport['instFile'][] = "<div class='success'>"
                        . '<i class="fa fa-exclamation-triangle red"></i> '
                        . sprintf(__('Can\'t rename installer file <b>%s</b> with HASH, please remove it for security reasons', 'duplicator'), esc_html($fileName))
                        . '</div>';
                }
            }
        }
    }

    /**
     *
     * @param array $migrationData
     */
    public static function storeMigrationFiles($migrationData)
    {
        $ssdInstallerPath = DUP_Settings::getSsdirInstallerPath();
        wp_mkdir_p($ssdInstallerPath);
        SnapIO::emptyDir($ssdInstallerPath);

        $filesToMove = array(
            $migrationData['installerLog'],
            $migrationData['installerBootLog'],
            $migrationData['origFileFolderPath']
        );

        foreach ($filesToMove as $path) {
            if (file_exists($path)) {
                if (SnapIO::rcopy($path, $ssdInstallerPath . '/' . basename($path), true)) {
                    self::$migrationCleanupReport['stored'] = "<div class='success'>"
                        . "<i class='fa fa-check'></i> "
                        . __('Original files folder moved in installer backup directory', 'duplicator') . " - " . esc_html($path) .
                        "</div>";
                } else {
                    self::$migrationCleanupReport['stored'] = "<div class='success'>"
                        . '<i class="fa fa-exclamation-triangle"></i> '
                        . sprintf(__('Can\'t move %s to %s', 'duplicagtor'), esc_html($path), $ssdInstallerPath)
                        . '</div>';
                }
            }
        }
    }

    /**
     *
     * @return array
     */
    public static function getStoredMigrationLists()
    {
        if (($migrationData = self::getMigrationData()) == false) {
            $filesToCheck = array();
        } else {
            $filesToCheck = array(
                $migrationData['installerLog']       => __('Installer log', 'duplicator'),
                $migrationData['installerBootLog']   => __('Installer boot log', 'duplicator'),
                $migrationData['origFileFolderPath'] => __('Original files folder', 'duplicator')
            );
        }

        $result = array();

        foreach ($filesToCheck as $path => $label) {
            $storedPath = DUP_Settings::getSsdirInstallerPath() . '/' . basename($path);
            if (!file_exists($storedPath)) {
                continue;
            }
            $result[$storedPath] = $label;
        }

        return $result;
    }

    /**
     *
     * @return bool
     */
    public static function haveFileToClean()
    {
        return count(self::checkInstallerFilesList()) > 0;
    }

    /**
     * Gets a list of all the installer files and directory by name and full path
     *
     * @remarks
     *  FILES:      installer.php, installer-backup.php, dup-installer-bootlog__[HASH].txt
     *  DIRS:       dup-installer
     *  Last set is for lazy developer cleanup files that a developer may have
     *  accidentally left around lets be proactive for the user just in case.
     *
     * @return [string] // [file_name]
     */
    public static function getGenericInstallerFiles()
    {
        return array(
            'installer.php',
            '[HASH]installer-backup.php',
            'dup-installer',
            'dup-installer[HASH]',
            'dup-installer-bootlog__[HASH].txt',
            '[HASH]_archive.zip|daf'
        );
    }

    /**
     *
     * @return string[]
     * @throws Exception
     */
    public static function checkInstallerFilesList()
    {
        $migrationData = self::getMigrationData();

        $foldersToChkeck = array(
            SnapIO::safePathTrailingslashit(ABSPATH),
            SnapWP::getHomePath(),
        );

        $result = array();

        if (!empty($migrationData)) {
            if (
                file_exists($migrationData['archivePath']) &&
                !DUP_Archive::isBackupPathChild($migrationData['archivePath'])
            ) {
                $result[] = $migrationData['archivePath'];
            }
            if (
                self::isInstallerFile($migrationData['installerPath']) &&
                !DUP_Archive::isBackupPathChild($migrationData['archivePath'])
            ) {
                $result[] = $migrationData['installerPath'];
            }
            if (file_exists($migrationData['installerBootLog'])) {
                $result[] = $migrationData['installerBootLog'];
            }
            if (file_exists($migrationData['dupInstallerPath'])) {
                $result[] = $migrationData['dupInstallerPath'];
            }
        }

        foreach ($foldersToChkeck as $folder) {
            $result = array_merge($result, SnapIO::regexGlob($folder, array(
                'regexFile'   => array(
                    DUPLICATOR_ARCHIVE_REGEX_PATTERN,
                    DUPLICATOR_INSTALLER_REGEX_PATTERN,
                    DUPLICATOR_DUP_INSTALLER_BOOTLOG_REGEX_PATTERN,
                    DUPLICATOR_DUP_INSTALLER_OWRPARAM_REGEX_PATTERN
                ),
                'regexFolder' => array(
                    DUPLICATOR_DUP_INSTALLER_FOLDER_REGEX_PATTERN
                )
            )));
        }

        $result = array_map(array('\\Duplicator\\Libs\\Snap\\SnapIO', 'safePathUntrailingslashit'), $result);
        return array_unique($result);
    }

    /**
     * @param $path string Path to check
     * @return bool true if the file at current path is the installer file
     * @throws Exception
     */
    public static function isInstallerFile($path)
    {
        if (!is_file($path) || !is_array($last5Lines = SnapIO::getLastLinesOfFile($path, 5)) || empty($last5Lines)) {
            return false;
        }

        return strpos(implode("", $last5Lines), "DUPLICATOR_INSTALLER_EOF") !== false;
    }

    public static function cleanMigrationFiles()
    {
        $cleanList = self::checkInstallerFilesList();

        $result = array();

        foreach ($cleanList as $path) {
            try {
                $success = (SnapIO::rrmdir($path) !== false);
            } catch (Exception $ex) {
                $success = false;
            } catch (Error $ex) {
                $success = false;
            }

            $result[$path] = $success;
        }

        delete_option(self::CLEAN_INSTALL_REPORT_OPTION);

        return $result;
    }
}
