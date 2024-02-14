<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

defined('ABSPATH') || exit;

use Duplicator\Core\Bootstrap;
use Duplicator\Lite as Duplicator;

/** @var string $currentPluginBootFile */

// CHECK IF PLUGIN CAN BE EXECTUED
require_once __DIR__ . '/src/Lite/Requirements.php';
if (Duplicator\Requirements::canRun($currentPluginBootFile) === false) {
    return;
} else {
    // NOTE: Plugin code must be inside a conditional block to prevent functions definition, simple return is not enough
    define('DUPLICATOR_LITE_PATH', dirname($currentPluginBootFile));
    define('DUPLICATOR_LITE_FILE', $currentPluginBootFile);
    define('DUPLICATOR_LITE_PLUGIN_URL', plugins_url('', $currentPluginBootFile));

    if (!defined('DUPXABSPATH')) {
        define('DUPXABSPATH', dirname(DUPLICATOR_LITE_FILE));
    }

    require_once(DUPLICATOR_LITE_PATH . '/src/Utils/Autoloader.php');
    \Duplicator\Utils\Autoloader::register();

    require_once("helper.php");
    require_once("define.php");

    if (defined('DUPLICATOR_DEACTIVATION_FEEDBACK') && DUPLICATOR_DEACTIVATION_FEEDBACK) {
        require_once 'deactivation.php';
    }
    require_once 'classes/class.constants.php';
    require_once 'classes/host/class.custom.host.manager.php';
    require_once 'classes/class.settings.php';
    require_once 'classes/class.logging.php';
    require_once 'classes/class.plugin.upgrade.php';
    require_once 'classes/utilities/class.u.php';
    require_once 'classes/utilities/class.u.string.php';
    require_once 'classes/utilities/class.u.validator.php';
    require_once 'classes/class.db.php';
    require_once 'classes/class.server.php';
    require_once 'classes/ui/class.ui.viewstate.php';
    require_once 'classes/package/class.pack.php';
    require_once 'views/packages/screen.php';
    require_once 'ctrls/ctrl.package.php';
    require_once 'ctrls/ctrl.tools.php';
    require_once 'ctrls/ctrl.ui.php';
    require_once 'ctrls/class.web.services.php';

    Bootstrap::init();
}
