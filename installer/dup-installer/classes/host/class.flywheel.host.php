<?php

/**
 * Flywheel custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\DB
 * @link    http://www.php-fig.org/psr/psr-2/
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

/**
 * class for wordpress.com managed hosting
 *
 * @todo not yet implemneted
 */
class DUPX_Flywheel_Host implements DUPX_Host_interface
{
    /**
     * return the current host itentifier
     *
     * @return string
     */
    public static function getIdentifier()
    {
        return DUPX_Custom_Host_Manager::HOST_FLYWHEEL;
    }

    /**
     * @return bool true if is current host
     */
    public function isHosting()
    {
        // check only mu plugin file exists

        $testFile = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . '/.fw-config.php';
        return file_exists($testFile);
    }

    /**
     * the init function.
     * is called only if isHosting is true
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Flywheel';
    }

    /**
     * this function is called if current hosting is this
     */
    public function setCustomParams()
    {
        $paramsManager = PrmMng::getInstance();

        $paramsManager->setValue(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES, DUP_Extraction::FILTER_SKIP_WP_CORE);
    }
}
