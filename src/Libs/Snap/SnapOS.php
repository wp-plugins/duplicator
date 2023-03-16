<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Snap;

class SnapOS
{
    const DEFAULT_WINDOWS_MAXPATH = 260;
    const DEFAULT_LINUX_MAXPATH   = 4096;

    /**
     * Return true if current SO is windows
     *
     * @return boolean
     */
    public static function isWindows()
    {
        static $isWindows = null;
        if (is_null($isWindows)) {
            $isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        }
        return $isWindows;
    }

    /**
     * Return true if current SO is OSX
     *
     * @return boolean
     */
    public static function isOSX()
    {
        static $isOSX = null;
        if (is_null($isOSX)) {
            $isOSX = (strtoupper(substr(PHP_OS, 0, 6)) === 'DARWIN');
        }
        return $isOSX;
    }
}
