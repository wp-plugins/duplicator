<?php

/**
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * In this class all the utility functions related to the wordpress configuration and the package are defined.
 *
 */
class DUPX_Conf_Utils
{
    /**
     *
     * @staticvar null|bool $present
     * @return bool
     */
    public static function isConfArkPresent()
    {
        static $present = null;
        if (is_null($present)) {
            $present = file_exists(DUPX_Package::getWpconfigArkPath());
        }
        return $present;
    }

    /**
     *
     * @staticvar bool $present
     * @return bool
     */
    public static function isManualExtractFilePresent()
    {
        static $present = null;
        if (is_null($present)) {
            $present = file_exists(DUPX_Package::getManualExtractFile());
        }
        return $present;
    }

    /**
     *
     * @staticvar null|bool $enable
     * @return bool
     */
    public static function shellExecUnzipEnable()
    {
        static $enable = null;
        if (is_null($enable)) {
            $enable = DUPX_Server::get_unzip_filepath() != null;
        }
        return $enable;
    }

    /**
     *
     * @return bool
     */
    public static function classZipArchiveEnable()
    {
        return class_exists('ZipArchive');
    }

    /**
     *
     * @staticvar bool $exists
     * @return bool
     */
    public static function archiveExists()
    {
        static $exists = null;
        if (is_null($exists)) {
            $exists = file_exists(DUPX_Security::getInstance()->getArchivePath());
        }
        return $exists;
    }

    /**
     *
     * @staticvar bool $arcSize
     * @return bool
     */
    public static function archiveSize()
    {
        static $arcSize = null;
        if (is_null($arcSize)) {
            $archivePath = DUPX_Security::getInstance()->getArchivePath();
            $arcSize     = file_exists($archivePath) ? (int) @filesize($archivePath) : 0;
        }
        return $arcSize;
    }
}
