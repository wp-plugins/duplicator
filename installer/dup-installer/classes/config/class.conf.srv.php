<?php

/**
 * Class used to update and edit web server configuration files
 * for .htaccess, web.config and user.ini
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\ServerConfig
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapIO;

class DUPX_ServerConfig
{
    const INSTALLER_HOST_ENTITY_PREFIX                 = 'installer_host_';
    const CONFIG_ORIG_FILE_USERINI_ID                  = 'userini';
    const CONFIG_ORIG_FILE_HTACCESS_ID                 = 'htaccess';
    const CONFIG_ORIG_FILE_WPCONFIG_ID                 = 'wpconfig';
    const CONFIG_ORIG_FILE_PHPINI_ID                   = 'phpini';
    const CONFIG_ORIG_FILE_WEBCONFIG_ID                = 'webconfig';
    const CONFIG_ORIG_FILE_USERINI_ID_OVERWRITE_SITE   = 'installer_host_userini';
    const CONFIG_ORIG_FILE_HTACCESS_ID_OVERWRITE_SITE  = 'installer_host_htaccess';
    const CONFIG_ORIG_FILE_WPCONFIG_ID_OVERWRITE_SITE  = 'installer_host_wpconfig';
    const CONFIG_ORIG_FILE_PHPINI_ID_OVERWRITE_SITE    = 'installer_host_phpini';
    const CONFIG_ORIG_FILE_WEBCONFIG_ID_OVERWRITE_SITE = 'installer_host_webconfig';

    /**
     * Common timestamp of all members of this class
     *
     * @staticvar type $time
     * @return type
     */
    public static function getFixedTimestamp()
    {
        static $time = null;

        if (is_null($time)) {
            $time = date("ymdHis");
        }

        return $time;
    }

    /**
     * Creates a copy of the original server config file and resets the original to blank
     *
     * @param string $rootPath The root path to the location of the server config files
     *
     * @return null
     * @throws Exception
     */
    public static function reset($rootPath)
    {
        $rootPath      = SnapIO::trailingslashit($rootPath);
        $paramsManager = PrmMng::getInstance();

        Log::info("\n*** RESET CONFIG FILES IN CURRENT HOSTING >>> START");

        switch ($paramsManager->getValue(PrmMng::PARAM_WP_CONFIG)) {
            case 'modify':
            case 'new':
                if (self::runReset($rootPath . 'wp-config.php', self::CONFIG_ORIG_FILE_WPCONFIG_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_WP_CONFIG, 'nothing');
                }
                break;
            case 'nothing':
                break;
        }

        switch ($paramsManager->getValue(PrmMng::PARAM_HTACCESS_CONFIG)) {
            case 'new':
            case 'original':
                if (self::runReset($rootPath . '.htaccess', self::CONFIG_ORIG_FILE_HTACCESS_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_HTACCESS_CONFIG, 'nothing');
                }
                break;
            case 'nothing':
                break;
        }

        switch ($paramsManager->getValue(PrmMng::PARAM_OTHER_CONFIG)) {
            case 'new':
            case 'original':
                if (self::runReset($rootPath . 'web.config', self::CONFIG_ORIG_FILE_WEBCONFIG_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
                }
                if (self::runReset($rootPath . '.user.ini', self::CONFIG_ORIG_FILE_USERINI_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
                }
                if (self::runReset($rootPath . 'php.ini', self::CONFIG_ORIG_FILE_PHPINI_ID) === false) {
                    $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
                }
                break;
            case 'nothing':
                break;
        }

        $paramsManager->save();
        Log::info("\n*** RESET CONFIG FILES IN CURRENT HOSTING >>> END");
    }

    public static function setFiles($rootPath)
    {
        $paramsManager = PrmMng::getInstance();
        $origFiles     = DUPX_Orig_File_Manager::getInstance();
        Log::info("SET CONFIG FILES");

        $entryKey = self::CONFIG_ORIG_FILE_WPCONFIG_ID;
        switch ($paramsManager->getValue(PrmMng::PARAM_WP_CONFIG)) {
            case 'new':
                if (SnapIO::copy(DUPX_Package::getWpconfigSamplePath(), DUPX_WPConfig::getWpConfigPath()) === false) {
                    DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                        'shortMsg'    => 'Can\' reset wp-config to wp-config-sample',
                        'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                        'longMsg'     => 'Target file entry ' . Log::v2str(DUPX_WPConfig::getWpConfigPath()),
                        'sections'    => 'general'
                    ));
                } else {
                    Log::info("Copy wp-config-sample.php to target:" . DUPX_WPConfig::getWpConfigPath());
                }
                break;
            case 'modify':
                if (SnapIO::copy($origFiles->getEntryStoredPath($entryKey), DUPX_WPConfig::getWpConfigPath()) === false) {
                    DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                        'shortMsg'    => 'Can\' restore oirg file entry ' . $entryKey,
                        'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                        'longMsg'     => 'Target file entry ' . Log::v2str(DUPX_WPConfig::getWpConfigPath()),
                        'sections'    => 'general'
                    ));
                } else {
                    Log::info("Retained original entry " . $entryKey . " target:" . DUPX_WPConfig::getWpConfigPath());
                }
                break;
            case 'nothing':
                break;
        }

        $entryKey = self::CONFIG_ORIG_FILE_HTACCESS_ID;
        switch ($paramsManager->getValue(PrmMng::PARAM_HTACCESS_CONFIG)) {
            case 'new':
                $targetHtaccess = self::getHtaccessTargetPath();
                if (SnapIO::touch($targetHtaccess) === false) {
                    DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                        'shortMsg'    => 'Can\'t create new htaccess file',
                        'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                        'longMsg'     => 'Target file entry ' . $targetHtaccess,
                        'sections'    => 'general'
                    ));
                } else {
                    Log::info("New htaccess file created:" . $targetHtaccess);
                }
                break;
            case 'original':
                if (($storedHtaccess = $origFiles->getEntryStoredPath($entryKey)) === false) {
                    Log::info("Retained original entry. htaccess doesn\'t exist in original site");
                    break;
                }

                $targetHtaccess = self::getHtaccessTargetPath();
                if (SnapIO::copy($storedHtaccess, $targetHtaccess) === false) {
                    DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                        'shortMsg'    => 'Can\' restore oirg file entry ' . $entryKey,
                        'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                        'longMsg'     => 'Target file entry ' . Log::v2str($targetHtaccess),
                        'sections'    => 'general'
                    ));
                } else {
                    Log::info("Retained original entry " . $entryKey . " target:" . $targetHtaccess);
                }
                break;
            case 'nothing':
                break;
        }

        switch ($paramsManager->getValue(PrmMng::PARAM_OTHER_CONFIG)) {
            case 'new':
                if ($origFiles->getEntry(self::CONFIG_ORIG_FILE_WEBCONFIG_ID_OVERWRITE_SITE)) {
                    //IIS: This is reset because on some instances of IIS having old values cause issues
                    //Recommended fix for users who want it because errors are triggered is to have
                    //them check the box for ignoring the web.config files on step 1 of installer
                    $xml_contents  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
                    $xml_contents .= "<!-- Reset by Duplicator Installer.  Original can be found in the original_files_ folder-->\n";
                    $xml_contents .= "<configuration></configuration>\n";
                    if (file_put_contents($rootPath . "/web.config", $xml_contents) === false) {
                        Log::info('RESET: can\'t create a new empty web.config');
                    }
                }
                break;
            case 'original':
                $entries = array(
                    self::CONFIG_ORIG_FILE_USERINI_ID,
                    self::CONFIG_ORIG_FILE_WEBCONFIG_ID,
                    self::CONFIG_ORIG_FILE_PHPINI_ID
                );
                foreach ($entries as $entryKey) {
                    if ($origFiles->getEntry($entryKey) !== false) {
                        if (SnapIO::copy($origFiles->getEntryStoredPath($entryKey), $origFiles->getEntryTargetPath($entryKey, false)) === false) {
                            DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                                'shortMsg'    => 'Notice: Cannot restore original file entry ' . $entryKey,
                                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                                'longMsg'     => 'Target file entry ' . Log::v2str($origFiles->getEntryTargetPath($entryKey, false)),
                                'sections'    => 'general'
                            ));
                        } else {
                            Log::info("Retained original entry " . $entryKey . " target:" . $origFiles->getEntryTargetPath($entryKey, false));
                        }
                    }
                }
                break;
            case 'nothing':
                break;
        }

        DUPX_NOTICE_MANAGER::getInstance()->saveNotices();
    }

    public static function getHtaccessTargetPath()
    {
        if (($targetEnty = DUPX_Orig_File_Manager::getInstance()->getEntryTargetPath(self::CONFIG_ORIG_FILE_HTACCESS_ID, false)) !== false) {
            return $targetEnty;
        } else {
            return PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . '/.htaccess';
        }
    }

    /**
     * Moves the configuration file to the dup_installer/original_files_[hash] folder
     *
     * @param string $filePath file path to store
     * @param string if not false rename
     * @return bool        Returns true if the file was backed-up and reset or there was no file to reset
     * @throws Exception
     */
    private static function runReset($filePath, $storedName)
    {
        $fileName = basename($filePath);

        try {
            if (file_exists($filePath)) {
                if (!SnapIO::chmod($filePath, 'u+rw') || !is_readable($filePath) || !is_writable($filePath)) {
                    throw new Exception("RESET CONFIG FILES: permissions error on file config path " . $filePath);
                }

                $origFiles = DUPX_Orig_File_Manager::getInstance();
                $filePath  = SnapIO::safePathUntrailingslashit($filePath);

                Log::info("RESET CONFIG FILES: I'M GOING TO MOVE CONFIG FILE " . Log::v2str($fileName) . " IN ORIGINAL FOLDER");

                if ($origFiles->addEntry(self::INSTALLER_HOST_ENTITY_PREFIX . $storedName, 
                                         $filePath,
                                         DUPX_Orig_File_Manager::MODE_MOVE,
                                         self::INSTALLER_HOST_ENTITY_PREFIX . $storedName))
                {
                    Log::info("\tCONFIG FILE HAS BEEN RESET");
                } else {
                    throw new Exception("cannot store file " . Log::v2str($fileName) . " in orginal file folder");
                }
            } else {
                Log::info("RESET CONFIG FILES: " . Log::v2str($fileName) . " does not exist, no need for reset", Log::LV_DETAILED);
            }
        } catch (Exception $e) {
            Log::logException($e, Log::LV_DEFAULT, 'RESET CONFIG FILES ERROR: ');
            DUPX_NOTICE_MANAGER::getInstance()->addBothNextAndFinalReportNotice(array(
                'shortMsg'    => 'Can\'t reset config file ' . Log::v2str($fileName) . ' so it will not be modified.',
                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'longMsg'     => 'Message: ' . $e->getMessage(),
                'sections'    => 'general'
            ));
            return false;
        } catch (Error $e) {
            Log::logException($e, Log::LV_DEFAULT, 'RESET CONFIG FILES ERROR: ');
            DUPX_NOTICE_MANAGER::getInstance()->addBothNextAndFinalReportNotice(array(
                'shortMsg'    => 'Can\'t reset config file ' . Log::v2str($fileName) . ' so it will not be modified.',
                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'longMsg'     => 'Message: ' . $e->getMessage(),
                'sections'    => 'general'
            ));
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean|string false if loca config don't exists or path of store local config
     */
    public static function getWpConfigLocalStoredPath()
    {
        $origFiles = DUPX_Orig_File_Manager::getInstance();
        $entry     = self::CONFIG_ORIG_FILE_WPCONFIG_ID_OVERWRITE_SITE;
        if ($origFiles->getEntry($entry)) {
            return $origFiles->getEntryStoredPath($entry);
        } else {
            return false;
        }
    }

    /**
     * Get AddHandler line from existing WP .htaccess file
     *
     * @return string
     * @throws Exception
     */
    private static function getOldHtaccessAddhandlerLine()
    {
        $origFiles          = DUPX_Orig_File_Manager::getInstance();
        $backupHtaccessPath = $origFiles->getEntryStoredPath(self::CONFIG_ORIG_FILE_HTACCESS_ID_OVERWRITE_SITE);
        Log::info("Installer Host Htaccess path: " . $backupHtaccessPath, Log::LV_DEBUG);

        if ($backupHtaccessPath !== false && file_exists($backupHtaccessPath)) {
            $htaccessContent = file_get_contents($backupHtaccessPath);
            if (!empty($htaccessContent)) {
                // match and trim non commented line  "AddHandler application/x-httpd-XXXX .php" case insenstive
                $re      = '/^[\s\t]*[^#]?[\s\t]*(AddHandler[\s\t]+.+\.php[ \t]?.*?)[\s\t]*$/mi';
                $matches = array();
                if (preg_match($re, $htaccessContent, $matches)) {
                    return "\n" . $matches[1];
                }
            }
        }
        return '';
    }

    /**
     * Sets up the web config file based on the inputs from the installer forms.
     *
     * @param int $mu_mode      Is this site a specific multi-site mode
     * @param object $dbh       The database connection handle for this request
     * @param string $path      The path to the config file
     *
     * @return null
     */
    public static function setup($dbh, $path)
    {
        Log::info("\nWEB SERVER CONFIGURATION FILE UPDATED:");

        $paramsManager = PrmMng::getInstance();
        $htAccessPath  = "{$path}/.htaccess";
        $mu_generation = DUPX_ArchiveConfig::getInstance()->mu_generation;

        // SKIP HTACCESS
        $skipHtaccessConfigVals = array('nothing', 'original');
        if (in_array($paramsManager->getValue(PrmMng::PARAM_HTACCESS_CONFIG), $skipHtaccessConfigVals)) {
            if (!DUPX_InstallerState::isRestoreBackup()) {
                // on restore packup mode no warning needed
                $longMsg = 'Retaining the original .htaccess file from the old site or not creating a new one may cause issues with the initial setup '
                    . 'of this site. If you encounter any issues, validate the contents of the .htaccess file or reinstall the site again using the '
                    . 'Step 1 ❯ Options ❯ Advanced ❯ Configuration Files ❯ Apache .htaccess ❯ Create New option.  If your site works as expected this '
                    . 'message can be ignored.';

                DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                    'shortMsg'    => 'Notice: A new .htaccess file was not created',
                    'level'       => DUPX_NOTICE_ITEM::NOTICE,
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                    'longMsg'     => $longMsg,
                    'sections'    => 'general'
                ));
            }
            return;
        }

        $timestamp    = date("Y-m-d H:i:s");
        $post_url_new = $paramsManager->getValue(PrmMng::PARAM_URL_NEW);
        $newdata      = parse_url($post_url_new);
        $newpath      = DUPX_U::addSlash(isset($newdata['path']) ? $newdata['path'] : "");
        $update_msg   = "# This file was updated by Duplicator on {$timestamp}.\n";
        $update_msg  .= "# See the original_files_ folder for the original source_site_htaccess file.";
        $update_msg  .= self::getOldHtaccessAddhandlerLine();

        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
                $tmp_htaccess = self::htAcccessNoMultisite($update_msg, $newpath, $dbh);
                Log::info("- Preparing .htaccess file with basic setup.");
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
            case DUPX_InstallerState::INSTALL_NOT_SET:
                throw new Exception('Cannot change setup with current installation type [' . DUPX_InstallerState::getInstType() . ']');
            default:
                throw new Exception('Unknown mode');
        }

        if (file_exists($htAccessPath) && SnapIO::chmod($htAccessPath, 'u+rw') === false) {
            Log::info("WARNING: Unable to update htaccess file permessition.");
            DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                'shortMsg'    => 'Notice: Unable to update new .htaccess file',
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'longMsg'     => 'Unable to update the .htaccess file! Please check the permission on the root directory and make sure the .htaccess exists.',
                'sections'    => 'general'
            ));
        } elseif (file_put_contents($htAccessPath, $tmp_htaccess) === false) {
            Log::info("WARNING: Unable to update the .htaccess file! Please check the permission on the root directory and make sure the .htaccess exists.");
            DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                'shortMsg'    => 'Noitice: Unable to update new .htaccess file',
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'longMsg'     => 'Unable to update the .htaccess file! Please check the permission on the root directory and make sure the .htaccess exists.',
                'sections'    => 'general'
            ));
        } else {
            DUP_Extraction::setPermsFromParams($htAccessPath);
            Log::info("HTACCESS FILE - Successfully updated the .htaccess file setting.");
        }
    }

    private static function htAcccessNoMultisite($update_msg, $newpath, $dbh)
    {
        $result = '';
        // no multisite
        $empty_htaccess = false;
        $optonsTable    = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getOptionsTableName());
        $query_result   = DUPX_DB::mysqli_query($dbh, "SELECT option_value FROM `" . $optonsTable . "` WHERE option_name = 'permalink_structure' ");

        if ($query_result) {
            $row = @mysqli_fetch_array($query_result);
            if ($row != null) {
                $permalink_structure = trim($row[0]);
                $empty_htaccess      = empty($permalink_structure);
            }
        }

        if ($empty_htaccess) {
            Log::info('NO PERMALINK STRUCTURE FOUND: set htaccess without directives');
            $result = <<<EMPTYHTACCESS
{$update_msg}
# BEGIN WordPress
# The directives (lines) between `BEGIN WordPress` and `END WordPress` are
# dynamically generated, and should only be modified via WordPress filters.
# Any changes to the directives between these markers will be overwritten.

# END WordPress
EMPTYHTACCESS;
        } else {
            $result = <<<HTACCESS
{$update_msg}
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . {$newpath}index.php [L]
</IfModule>
# END WordPress
HTACCESS;
        }

        return $result;
    }
}
