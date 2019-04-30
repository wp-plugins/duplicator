<?php
/**
 * Snap OS utils
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package SnapLib
 * @copyright (c) 2017, Snapcreek LLC
 * @license	https://opensource.org/licenses/GPL-3.0 GNU Public License
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class SnapLibOSU
{
    const WindowsMaxPathLength = 259;

    public static $isWindows;

    public static function init()
    {

        self::$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }
}
SnapLibOSU::init();


