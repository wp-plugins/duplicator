<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;

class DUPX_RemoveRedundantData
{
    public static function loadWP()
    {
        static $loaded = null;
        if (is_null($loaded)) {
            $wp_root_dir = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
            require_once($wp_root_dir . '/wp-load.php');
            if (!class_exists('WP_Privacy_Policy_Content')) {
                require_once($wp_root_dir . '/wp-admin/includes/misc.php');
            }
            if (!function_exists('request_filesystem_credentials')) {
                require_once($wp_root_dir . '/wp-admin/includes/file.php');
            }
            if (!function_exists('get_plugins')) {
                require_once $wp_root_dir . '/wp-admin/includes/plugin.php';
            }
            if (!function_exists('delete_theme')) {
                require_once $wp_root_dir . '/wp-admin/includes/theme.php';
            }
            $GLOBALS['wpdb']->show_errors(false);
            $loaded = true;
        }
        return $loaded;
    }
}
