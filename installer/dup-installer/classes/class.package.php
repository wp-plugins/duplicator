<?php

/**
 * Class used to update and edit web server configuration files
 * for .htaccess, web.config and user.ini
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Crypt
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Bootstrap;

/**
 * Package related functions
 */
final class DUPX_Package
{
    /**
     *
     * @staticvar bool|string $packageHash
     * @return    bool|string false if fail
     * @throws    Exception
     */
    public static function getPackageHash()
    {
        static $packageHash = null;
        if (is_null($packageHash)) {
            if (($packageHash = Bootstrap::getPackageHash()) === false) {
                throw new Exception('PACKAGE ERROR: can\'t find package hash');
            }
        }
        return $packageHash;
    }

    /**
     *
     * @staticvar string $fileHash
     * @return    string
     */
    public static function getArchiveFileHash()
    {
        static $fileHash = null;

        if (is_null($fileHash)) {
            $fileHash = preg_replace('/^.+_([a-z0-9]+)_[0-9]{14}_archive\.(?:daf|zip)$/', '$1', DUPX_Security::getInstance()->getArchivePath());
        }

        return $fileHash;
    }

    /**
     *
     * @staticvar string $archivePath
     * @return    bool|string false if fail
     * @throws    Exception
     */
    public static function getPackageArchivePath()
    {
        static $archivePath = null;
        if (is_null($archivePath)) {
            $path = DUPX_INIT . '/' . Bootstrap::ARCHIVE_PREFIX . self::getPackageHash() . Bootstrap::ARCHIVE_EXTENSION;
            if (!file_exists($path)) {
                throw new Exception('PACKAGE ERROR: can\'t read package path: ' . $path);
            } else {
                $archivePath = $path;
            }
        }
        return $archivePath;
    }

    /**
     * Returns a save-to-edit wp-config file
     *
     * @return string
     * @throws Exception
     */
    public static function getWpconfigArkPath()
    {
        return DUPX_Orig_File_Manager::getInstance()->getEntryStoredPath(DUPX_ServerConfig::CONFIG_ORIG_FILE_WPCONFIG_ID);
    }

    /**
     *
     * @return string
     * @throws Exception
     */
    public static function getManualExtractFile()
    {
        return DUPX_INIT . '/dup-manual-extract__' . self::getPackageHash();
    }

    /**
     *
     * @staticvar type $path
     * @return    string
     */
    public static function getWpconfigSamplePath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = DUPX_INIT . '/assets/wp-config-sample.php';
        }
        return $path;
    }

    /**
     * Get sql file relative path
     *
     * @return string
     */
    public static function getSqlFilePathInArchive()
    {
        return 'dup-installer/dup-database__' . self::getPackageHash() . '.sql';
    }

    /**
     *
     * @staticvar string $path
     * @return    string
     */
    public static function getSqlFilePath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = DUPX_INIT . '/dup-database__' . self::getPackageHash() . '.sql';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $dirsPath
     * @return    string
     */
    public static function getDirsListPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = DUPX_INIT . '/dup-scanned-dirs__' . self::getPackageHash() . '.txt';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $dirsPath
     * @return    string
     */
    public static function getFilesListPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = DUPX_INIT . '/dup-scanned-files__' . self::getPackageHash() . '.txt';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $path
     * @return    string
     */
    public static function getScanJsonPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = DUPX_INIT . '/dup-scan__' . self::getPackageHash() . '.json';
        }
        return $path;
    }

    /**
     *
     * @return int
     */
    public static function getSqlFileSize()
    {
        return (is_readable(self::getSqlFilePath())) ? (int) filesize(self::getSqlFilePath()) : 0;
    }

    /**
     *
     * @param callable $callback
     *
     * @return boolean
     */
    public static function foreachDirCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Not valid callback');
        }

        $dirFiles = DUPX_Package::getDirsListPath();

        if (($handle = fopen($dirFiles, "r")) === false) {
            throw new Exception('Can\'t open dirs file list');
        }

        while (($line = fgets($handle)) !== false) {
            if (($info = json_decode($line)) === null) {
                throw new Exception('Invalid json line in dirs file: ' . $line);
            }

            call_user_func($callback, $info);
        }

        fclose($handle);
        return true;
    }

    /**
     *
     * @param callable $callback
     *
     * @return boolean
     */
    public static function foreachFileCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new Exception('Not valid callback');
        }

        $filesPath = DUPX_Package::getFilesListPath();

        if (($handle = fopen($filesPath, "r")) === false) {
            throw new Exception('Can\'t open files file list');
        }

        while (($line = fgets($handle)) !== false) {
            if (($info = json_decode($line)) === null) {
                throw new Exception('Invalid json line in files file: ' . $line);
            }

            call_user_func($callback, $info);
        }

        fclose($handle);
        return true;
    }
}
