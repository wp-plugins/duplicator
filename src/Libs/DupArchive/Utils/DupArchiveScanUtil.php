<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\Utils;

use Duplicator\Libs\Snap\SnapJson;
use Exception;
use stdClass;

/**
 * Description of class
 *
 * @author Robert
 */
class DupArchiveScanUtil
{
    /**
     * Get scan
     *
     * @param string $scanFilepath scan file path
     *
     * @return void
     */
    public static function getScan($scanFilepath)
    {
        DupArchiveUtil::tlog("Getting scen");
        $scan_handle = fopen($scanFilepath, 'r');

        if ($scan_handle === false) {
            throw new Exception("Can't open {$scanFilepath}");
        }

        $scan_file = fread($scan_handle, filesize($scanFilepath));

        if ($scan_file === false) {
            throw new Exception("Can't read from {$scanFilepath}");
        }

        $scan = json_decode($scan_file);
        if (!$scan) {
            throw new Exception("Error decoding scan file");
        }

        fclose($scan_handle);

        return $scan;
    }

    /**
     * Get scan object
     *
     * @param string $sourceDirectory folder to scan
     *
     * @return stdClass
     */
    public static function createScanObject($sourceDirectory)
    {
        $scan = new stdClass();

        $scan->Dirs  = DupArchiveUtil::expandDirectories($sourceDirectory, true);
        $scan->Files = DupArchiveUtil::expandFiles($sourceDirectory, true);

        return $scan;
    }

    /**
     * Scan folder and add result to scan file
     *
     * @param string $scanFilepath    scan file
     * @param string $sourceDirectory folder to scan
     *
     * @return void
     */
    public static function createScan($scanFilepath, $sourceDirectory)
    {
        DupArchiveUtil::tlog("Creating scan");

        $scan        = self::createScanObject($sourceDirectory);
        $scan_handle = fopen($scanFilepath, 'w');

        if ($scan_handle === false) {
            echo "Couldn't create scan file";
            die();
        }

        $jsn = SnapJson::jsonEncode($scan);

        fwrite($scan_handle, $jsn);
        return $scan;
    }
}
