<?php

/**
 * Auloader calsses
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Utils;

/**
 * Autoloader calss, dont user Duplicator library here
 */
final class Autoloader
{
    const ROOT_NAMESPACE           = 'Duplicator\\';
    const ROOT_INSTALLER_NAMESPACE = 'Duplicator\\Installer\\';

    protected static $nameSpacesMapping = null;

    /**
     * Register autoloader function
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register(array(__CLASS__, 'load'));
    }

    /**
     * Load class
     *
     * @param string $className class name
     *
     * @return bool return true if class is loaded
     */
    public static function load($className)
    {
        // @todo remove legacy logic in autoloading when duplicator is fully converted.
        if (strpos($className, self::ROOT_NAMESPACE) !== 0) {
            $legacyMappging = self::customLegacyMapping();
            $legacyClass    = strtolower(ltrim($className, '\\'));
            if (array_key_exists($legacyClass, $legacyMappging)) {
                if (file_exists($legacyMappging[$legacyClass])) {
                    include_once($legacyMappging[$legacyClass]);
                    return true;
                }
            }

            if (self::externalLibs($className)) {
                return true;
            }
        } else {
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
        }

        return false;
    }

    /**
     * Load external libs
     *
     * @param string $className class name
     *
     * @return bool return true if class is loaded
     */
    protected static function externalLibs($className)
    {
        switch (strtolower(ltrim($className, '\\'))) {
            default:
                return false;
        }
    }

    /**
     * mappgin of some legacy classes
     *
     * @return array
     */
    protected static function customLegacyMapping()
    {
        return array();
    }

    /**
     * Return namespace mapping
     *
     * @return string[]
     */
    protected static function getNamespacesMapping()
    {
        // the order is important, it is necessary to insert the longest namespaces first
        return array(
            self::ROOT_INSTALLER_NAMESPACE => DUPLICATOR_LITE_PATH . '/installer/dup-installer/src/',
            self::ROOT_NAMESPACE           => DUPLICATOR_LITE_PATH . '/src/'
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
