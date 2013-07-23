<?php
//Prevent directly browsing to the file
if (function_exists('plugin_dir_url')) {
    define('DUPLICATOR_VERSION',        '0.4.6');
    define("DUPLICATOR_HOMEPAGE",       "http://lifeinthegrid.com/labs/duplicator");
    define("DUPLICATOR_GIVELINK",       "http://lifeinthegrid.com/partner");
    define("DUPLICATOR_HELPLINK",       "http://lifeinthegrid.com/duplicator-docs");
    define("DUPLICATOR_CERTIFIED",      "http://lifeinthegrid.com/duplicator-hosts");
    define('DUPLICATOR_PLUGIN_URL',     plugin_dir_url(__FILE__));
    define('DUPLICATOR_PLUGIN_PATH',    str_replace("\\", "/", plugin_dir_path(__FILE__)));

    /* Paths should ALWAYS read "/"
      uni: /home/path/file.txt
      win:  D:/home/path/file.txt
      SSDIR = SnapShot Directory */
    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname('__FILE__'));
    }
    define("DUPLICATOR_SSDIR_NAME",     'wp-snapshots');
    define('DUPLICATOR_WPROOTPATH',     str_replace("\\", "/", ABSPATH));
    define("DUPLICATOR_SSDIR_PATH",     str_replace("\\", "/", DUPLICATOR_WPROOTPATH . DUPLICATOR_SSDIR_NAME));
    define("DUPLICATOR_INSTALL_PHP",    'installer.php');
    define("DUPLICATOR_INSTALL_SQL",    'installer-data.sql');
    define("DUPLICATOR_INSTALL_LOG",    'installer-log.txt');
    define("DUPLICATOR_ZIP_FILE_POOL",  10000);
    define("DUPLICATOR_PHP_MAX_TIME",   5000);
    define("DUPLICATOR_PHP_MAX_MEMORY", '5000M');
    define("DUPLICATOR_DB_MAX_TIME",    5000);
	define("DUPLICATOR_ADD1_TRIPCOUNT", 5);
    $GLOBALS['DUPLICATOR_SEPERATOR1'] = str_repeat("********", 5);
    $GLOBALS['DUPLICATOR_SEPERATOR2'] = str_repeat("--------", 5);
} else {
    error_reporting(0);
    $port = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") ? "https://" : "http://";
    $url = $port . $_SERVER["HTTP_HOST"];
    header("HTTP/1.1 404 Not Found", true, 404);
    header("Status: 404 Not Found");
    exit();
}
?>