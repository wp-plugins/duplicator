<?php
/**
 * Snap OS utils
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DupLiteSnapLib
 * @copyright (c) 2017, Snapcreek LLC
 * @license	https://opensource.org/licenses/GPL-3.0 GNU Public License
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (!class_exists('DupLiteSnapLibOSU', false)) {

    class DupLiteSnapLibOSU
    {
        const WindowsMaxPathLength = 259;

        public static $isWindows;

        public static function init()
        {

            self::$isWindows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        }
    }
    DupLiteSnapLibOSU::init();
}