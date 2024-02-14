<?php

/**
 * Interface that collects the functions of initial duplicator Bootstrap
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core;

/**
 * Uninstall class
 */
class Unistall
{
    /**
     * Registrer unistall hoosk
     *
     * @return void
     */
    public static function registerHooks()
    {
        if (is_admin()) {
            register_deactivation_hook(DUPLICATOR_LITE_FILE, array(__CLASS__, 'deactivate'));
        }
    }

    /**
     * Deactivation Hook:
     * Hooked into `register_deactivation_hook`.  Routines used to deactivate the plugin
     * For uninstall see uninstall.php  WordPress by default will call the uninstall.php file
     *
     * @return void
     */
    public static function deactivate()
    {
        MigrationMng::renameInstallersPhpFiles();

        do_action('duplicator_after_deactivation');
    }
}
