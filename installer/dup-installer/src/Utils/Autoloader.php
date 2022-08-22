<?php

/**
 * Auloader calsses
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Installer\Utils;

final class Autoloader
{
    const ROOT_NAMESPACE                 = 'Duplicator\\';
    const ROOT_INSTALLER_NAMESPACE       = 'Duplicator\\Installer\\';
    const ROOT_ADDON_INSTALLER_NAMESPACE = 'Duplicator\\Installer\\Addons\\';
    const ROOT_LIBS_NAMESPACE            = 'Duplicator\\Libs\\';

    protected static $nameSpacesMapping = null;

    /**
     * register autooader
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'load'));
    }

    /**
     *
     * @param string $className class name
     *
     * @return boolean
     */
    public static function load($className)
    {
        // @todo remove legacy logic in autoloading when duplicator is fully converted.
        if (strpos($className, self::ROOT_NAMESPACE) !== 0) {
            return false;
        }

        if (($filepath = self::getAddonFile($className)) === false) {
            foreach (self::getNamespacesMapping() as $namespace => $mappedPath) {
                if (strpos($className, $namespace) !== 0) {
                    continue;
                }

                $filepath = $mappedPath . str_replace('\\', '/', substr($className, strlen($namespace))) . '.php';
                if (file_exists($filepath)) {
                    include_once($filepath);
                    return true;
                }
            }
        } else {
            if (file_exists($filepath)) {
                include_once($filepath);
                return true;
            }
        }

        return false;
    }

    /**
     *
     * @param string $class class name
     *
     * @return boolean|string
     */
    protected static function getAddonFile($class)
    {
        $matches = null;
        if (preg_match('/^\\\\?Duplicator\\\\Installer\\\\Addons\\\\(.+?)\\\\(.+)$/', $class, $matches) !== 1) {
            return false;
        }

        $addonName = $matches[1];
        $subClass  = $matches[2];
        $basePath  = DUPX_INIT . '/addons/' . strtolower($addonName) . '/';

        if (self::endsWith($class, $addonName) === false) {
            $basePath .= 'src/';
        }

        return $basePath . str_replace('\\', '/', $subClass) . '.php';
    }

    /**
     *
     * @staticvar [string] $mapping
     * @return [string]
     */
    protected static function getNamespacesMapping()
    {
        // the order is important, it is necessary to insert the longest namespaces first
        return array(
            self::ROOT_ADDON_INSTALLER_NAMESPACE => DUPX_INIT . '/addons/',
            self::ROOT_INSTALLER_NAMESPACE       => DUPX_INIT . '/src/',
            self::ROOT_LIBS_NAMESPACE            => DUPX_INIT . '/libs/'
        );
    }

    /**
     * Returns true if the $haystack string end with the $needle, only for internal use
     *
     * @param string $haystack The full string to search in
     * @param string $needle   The string to for
     *
     * @return bool Returns true if the $haystack string starts with the $needle
     */
    protected static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }
}
