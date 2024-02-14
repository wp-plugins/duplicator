<?php

/**
 * Original installer files manager
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Bootstrap;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapOrigFileManager;

/**
 * Original installer files manager
 *
 * This class saves a file or folder in the original files folder and saves the original location persistant.
 * By entry we mean a file or a folder but not the files contained within it.
 * In this way it is possible, for example, to move an entire plugin to restore it later.
 *
 * singleton class
 */
final class DUPX_Orig_File_Manager extends SnapOrigFileManager
{
    /**
     *
     * @var DUPX_Orig_File_Manager
     */
    private static $instance = null;

    /**
     *
     * @return DUPX_Orig_File_Manager
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * This class should be singleton, but unfortunately it is not possible to change the constructor in private with versions prior to PHP 7.2.
     */
    public function __construct()
    {
        //Init Original File Manager
        $packageHash = Bootstrap::getPackageHash();
        $root        = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW);
        parent::__construct($root, DUPX_INIT, $packageHash);
    }

    private function __clone()
    {
    }
}
