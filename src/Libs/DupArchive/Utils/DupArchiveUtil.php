<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive\Utils;

use Duplicator\Libs\Snap\SnapUtil;

class DupArchiveUtil
{
    public static $TRACE_ON = false;    //rodo rework this
    public static $logger   = null;

    /**
     * get file list
     *
     * @param string $base_dir folder to check
     * @param bool   $recurse  true for recursive scan
     *
     * @return string[]
     */
    public static function expandFiles($base_dir, $recurse)
    {
        $files = array();

        foreach (scandir($base_dir) as $file) {
            if (($file == '.') || ($file == '..')) {
                continue;
            }

            $file = "{$base_dir}/{$file}";

            if (is_file($file)) {
                $files [] = $file;
            } elseif (is_dir($file) && $recurse) {
                $files = array_merge($files, self::expandFiles($file, $recurse));
            }
        }

        return $files;
    }

    /**
     * get folder list
     *
     * @param string $base_dir folder to check
     * @param bool   $recurse  true for recursive scan
     *
     * @return string[]
     */
    public static function expandDirectories($base_dir, $recurse)
    {
        $directories = array();

        foreach (scandir($base_dir) as $candidate) {
            if (($candidate == '.') || ($candidate == '..')) {
                continue;
            }
            $candidate = "{$base_dir}/{$candidate}";

            if (is_dir($candidate)) {
                $directories[] = $candidate;
                if ($recurse) {
                    $directories = array_merge($directories, self::expandDirectories($candidate, $recurse));
                }
            }
        }

        return $directories;
    }

    /**
     * Write $s in log
     *
     * @param string  $s                   log string
     * @param boolean $flush               if true flosh name
     * @param string  $callingFunctionName function has called log
     *
     * @return void
     */
    public static function log($s, $flush = false, $callingFunctionName = null)
    {
        if (self::$logger != null) {
            if ($callingFunctionName === null) {
                $callingFunctionName = SnapUtil::getCallingFunctionName();
            }

            self::$logger->log($s, $flush, $callingFunctionName);
        } else {
         //   throw new Exception('Logging object not initialized');
        }
    }

    /**
     * Write trace log
     *
     * @param string  $s                   log string
     * @param boolean $flush               if true flosh name
     * @param string  $callingFunctionName function has called log
     *
     * @return void
     */
    public static function tlog($s, $flush = false, $callingFunctionName = null)
    {
        if (self::$TRACE_ON) {
            if ($callingFunctionName === null) {
                $callingFunctionName = SnapUtil::getCallingFunctionName();
            }

            self::log("####{$s}", $flush, $callingFunctionName);
        }
    }

    /**
     * Write object in trace log
     *
     * @param string  $s                   log string
     * @param mixed   $o                   value to write in log
     * @param boolean $flush               if true flosh name
     * @param string  $callingFunctionName function has called log
     *
     * @return void
     */
    public static function tlogObject($s, $o, $flush = false, $callingFunctionName = null)
    {
        if (is_object($o)) {
            $o = get_object_vars($o);
        }

        $ostring = print_r($o, true);

        if ($callingFunctionName === null) {
            $callingFunctionName = SnapUtil::getCallingFunctionName();
        }

        self::tlog($s, $flush, $callingFunctionName);
        self::tlog($ostring, $flush, $callingFunctionName);
    }

    /**
     * Write object in log
     *
     * @param string  $s                   log string
     * @param mixed   $o                   value to write in log
     * @param boolean $flush               if true flosh name
     * @param string  $callingFunctionName function has called log
     *
     * @return void
     */
    public static function logObject($s, $o, $flush = false, $callingFunctionName = null)
    {
        $ostring = print_r($o, true);

        if ($callingFunctionName === null) {
            $callingFunctionName = SnapUtil::getCallingFunctionName();
        }

        self::log($s, $flush, $callingFunctionName);
        self::log($ostring, $flush, $callingFunctionName);
    }
}
