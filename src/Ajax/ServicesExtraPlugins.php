<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Utils\ExtraPlugins\ExtraPluginsMng;

class ServicesExtraPlugins extends AbstractAjaxService
{
    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall('wp_ajax_duplicator_install_extra_plugin', 'extraPluginInstall');
    }

    /**
     * Install and activate or just activate plugin
     *
     * @return string
     */
    public static function extraPluginInstallCallback()
    {
        $slug    = filter_input(INPUT_POST, 'plugin', FILTER_SANITIZE_STRING);
        $message = '';

        if (!ExtraPluginsMng::getInstance()->install($slug, $message)) {
            throw new \Exception($message);
        }

        return $message;
    }

    /**
     *  Addon plugin install action callback
     *
     * @return void
     */
    public function extraPluginInstall()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'extraPluginInstallCallback'),
            'duplicator_install_extra_plugin',
            $_POST['nonce'],
            'install_plugins'
        );
    }
}
