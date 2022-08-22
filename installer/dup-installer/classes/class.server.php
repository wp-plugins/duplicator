<?php

defined("DUPXABSPATH") or die("");

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapWP;

/**
 * DUPX_cPanel
 * Wrapper Class for cPanel API  */
class DUPX_Server
{
    /**
     * A list of the core WordPress directories
     */
    public static $wpCoreDirsList = array(
        'wp-admin',
        'wp-includes'
    );

    public static function phpSafeModeOn()
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            // safe_mode  has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0.
            return false;
        } else {
            return filter_var(
                ini_get('safe_mode'),
                FILTER_VALIDATE_BOOLEAN,
                array(
                    'options' => array(
                        'default' => false
                    )
                )
            );
        }
    }

    /**
     * Check given path prefixed with path array
     *
     * @param string $checkPath Path to check
     * @param array $pathsArr check against
     * @return boolean
     */
    private static function isPathPrefixedWithArrayPath($checkPath, $pathsArr)
    {
        foreach ($pathsArr as $path) {
            if (0 === strpos($checkPath, $path)) {
                return true;
            }
        }
        return false;
    }

    /**
     *  Can this server process in shell_exec mode
     *
     *  @return bool
     */
    public static function is_shell_exec_available()
    {
        if (array_intersect(array('shell_exec', 'escapeshellarg', 'escapeshellcmd', 'extension_loaded'), array_map('trim', explode(',', @ini_get('disable_functions'))))) {
            return false;
        }

        //Suhosin: http://www.hardened-php.net/suhosin/
        //Will cause PHP to silently fail.
        if (extension_loaded('suhosin')) {
            return false;
        }

        if (! function_exists('shell_exec')) {
            return false;
        }

        // Can we issue a simple echo command?
        if (!@shell_exec('echo duplicator')) {
            return false;
        }

        return true;
    }

    /**
     *  Returns the path this this server where the zip command can be called
     *
     *  @return null|string     // null if can't find unzip
     */
    public static function get_unzip_filepath()
    {
        $filepath = null;
        if (self::is_shell_exec_available()) {
            if (shell_exec('hash unzip 2>&1') == null) {
                $filepath = 'unzip';
            } else {
                $possible_paths = array('/usr/bin/unzip', '/opt/local/bin/unzip');
                foreach ($possible_paths as $path) {
                    if (file_exists($path)) {
                        $filepath = $path;
                        break;
                    }
                }
            }
        }
        return $filepath;
    }

    /**
     *
     * @return string[]
     */
    public static function getWpAddonsSiteLists()
    {
        $addonsSites  = array();
        $pathsToCheck = DUPX_ArchiveConfig::getInstance()->getPathsMapping();

        if (is_scalar($pathsToCheck)) {
            $pathsToCheck = array($pathsToCheck);
        }

        foreach ($pathsToCheck as $mainPath) {
            SnapIO::regexGlobCallback($mainPath, function ($path) use (&$addonsSites) {
                if (SnapWP::isWpHomeFolder($path)) {
                    $addonsSites[] = $path;
                }
            }, array(
                'regexFile' => false,
                'recursive' => true
            ));
        }

        return $addonsSites;
    }

    /**
     * Does the site look to be a WordPress site
     *
     * @return bool     Returns true if the site looks like a WP site
     */
    public static function isWordPress()
    {
        $absPathNew = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
        if (!is_dir($absPathNew)) {
            return false;
        }
        if (($root_files = scandir($absPathNew)) == false) {
            return false;
        }
        $file_count = 0;
        foreach ($root_files as $file) {
            if (in_array($file, self::$wpCoreDirsList)) {
                $file_count++;
            }
        }
        return (count(self::$wpCoreDirsList) == $file_count);
    }
}
