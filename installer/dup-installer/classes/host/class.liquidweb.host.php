<?php

/**
 * liquidweb custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\DB
 * @link    http://www.php-fig.org/psr/psr-2/
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

class DUPX_Liquidweb_Host implements DUPX_Host_interface
{
    /**
     * return the current host itentifier
     *
     * @return string
     */
    public static function getIdentifier()
    {
        return DUPX_Custom_Host_Manager::HOST_LIQUIDWEB;
    }

    /**
     * @return bool true if is current host
     */
    public function isHosting()
    {
        // check only mu plugin file exists

        $testFile = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW) . '/liquid-web.php';
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
     * return the label of current hosting
     *
     * @return string
     */
    public function getLabel()
    {
        return 'Liquid Web';
    }

    /**
     * this function is called if current hosting is this
     */
    public function setCustomParams()
    {
        PrmMng::getInstance()->setValue(PrmMng::PARAM_IGNORE_PLUGINS, array(
            'liquidweb_mwp.php',
            '000-liquidweb-config.php',
            'liquid-web.php',
            'lw_disable_nags.php'
        ));
    }
}
