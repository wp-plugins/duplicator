<?php

//Prevent directly browsing to the file
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

define('DUPLICATOR_BLOG_URL', 'https://duplicator.com/');
define('DUPLICATOR_DOCS_URL', DUPLICATOR_BLOG_URL . 'knowledge-base/');
define(
    'DUPLICATOR_TECH_FAQ_URL',
    DUPLICATOR_BLOG_URL . 'knowledge-base-article-categories/troubleshooting/'
);

if (function_exists('plugin_dir_url')) {
    define('DUPLICATOR_VERSION', '1.5.7');
    define('DUPLICATOR_PLUGIN_URL', plugin_dir_url(__FILE__));
    define('DUPLICATOR_SITE_URL', get_site_url());

    /* Paths should ALWAYS read "/"
      uni: /home/path/file.txt
      win:  D:/home/path/file.txt
      SSDIR = SnapShot Directory */
    if (!defined('ABSPATH')) {
        define('ABSPATH', dirname(__FILE__));
    }

    //PATH CONSTANTS
    if (! defined('DUPLICATOR_WPROOTPATH')) {
        define('DUPLICATOR_WPROOTPATH', str_replace('\\', '/', ABSPATH));
    }

    define('DUPLICATOR_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path(__FILE__)));
    define('DUPLICATOR_ZIPPED_LOG_FILENAME', 'duplicator_lite_log.zip');
    define('DUPLICATOR_INSTALL_PHP', 'installer.php');
    define('DUPLICATOR_INSTALL_BAK', 'installer-backup.php');
    define('DUPLICATOR_INSTALLER_HASH_PATTERN', '[a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9][a-z0-9]-[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]');
    define('DUPLICATOR_INSTALL_SITE_OVERWRITE_ON', true);

    //GENERAL CONSTRAINTS
    define('DUPLICATOR_PHP_MAX_MEMORY', 4294967296); // 4096MB
    define('DUPLICATOR_DB_MAX_TIME', 5000);
    define('DUPLICATOR_DB_EOF_MARKER', 'DUPLICATOR_MYSQLDUMP_EOF');
    define("DUPLICATOR_DB_MYSQLDUMP_ERROR_CONTAINING_LINE_COUNT", 10);
    define("DUPLICATOR_DB_MYSQLDUMP_ERROR_CHARS_IN_LINE_COUNT", 1000);
    //SCANNER CONSTRAINTS
    define('DUPLICATOR_SCAN_SIZE_DEFAULT', 157286400); //150MB
    define('DUPLICATOR_SCAN_WARNFILESIZE', 3145728);   //3MB
    define('DUPLICATOR_SCAN_WARN_DIR_SIZE', 104857600);//100MB

    define('DUPLICATOR_SCAN_CACHESIZE', 1048576);   //1MB
    define('DUPLICATOR_SCAN_DB_ALL_ROWS', 500000);    //500k per DB
    define('DUPLICATOR_SCAN_DB_ALL_SIZE', 52428800);  //50MB DB
    define('DUPLICATOR_SCAN_DB_TBL_ROWS', 100000);    //100K rows per table
    define('DUPLICATOR_SCAN_DB_TBL_SIZE', 10485760);  //10MB Table
    define('DUPLICATOR_SCAN_TIMEOUT', 150);       //Seconds
    define('DUPLICATOR_SCAN_MIN_WP', '4.7.0');
    define('DUPLICATOR_MAX_DUPARCHIVE_SIZE', 524288000); // 500 GB

    define('DUPLICATOR_TEMP_CLEANUP_SECONDS', 900);     // 15 min = How many seconds to keep temp files around when delete is requested
    define('DUPLICATOR_MAX_BUILD_RETRIES', 10);         // Max times to try a part of progressive build work
    define('DUPLICATOR_WEBCONFIG_ORIG_FILENAME', 'web.config.orig');
    define("DUPLICATOR_INSTALLER_DIRECTORY", duplicator_get_abs_path() . '/dup-installer');
    define('DUPLICATOR_MAX_LOG_SIZE', 400000);    // The higher this is the more overhead
    define('DUPLICATOR_ZIP_ARCHIVE_ADD_FROM_STR', false);
    define('DUPLICATOR_DEACTIVATION_FEEDBACK', false);
    define("DUPLICATOR_BUFFER_READ_WRITE_SIZE", 4377);
    define("DUPLICATOR_ADMIN_NOTICES_USER_META_KEY", 'duplicator_admin_notices');
    define("DUPLICATOR_FEEDBACK_NOTICE_SHOW_AFTER_NO_PACKAGE", 5);

    define('DUPLICATOR_LOCAL_OVERWRITE_PARAMS', 'duplicator_pro_params_overwrite');

    // MATCH archive pattern, matches[1] is archive name and hash
    define('DUPLICATOR_ARCHIVE_REGEX_PATTERN', '/^(.+_[a-z0-9]{7,}_[0-9]{14})_archive\.(?:zip|daf)$/');
    // MATCH installer.php installer-backup.php and full installer with hash
    define('DUPLICATOR_INSTALLER_REGEX_PATTERN', '/^(?:.+_[a-z0-9]{7,}_[0-9]{14}_)?installer(?:-backup)?\.php$/');
    // MATCH dup-installer and dup-installer-[HASH]
    define('DUPLICATOR_DUP_INSTALLER_FOLDER_REGEX_PATTERN', '/^dup-installer(?:-[a-z0-9]{7,}-[0-9]{8})?$/');
    define('DUPLICATOR_DUP_INSTALLER_BOOTLOG_REGEX_PATTERN', '/^dup-installer-bootlog__[a-z0-9]{7,}-[0-9]{8}.txt$/');
    define('DUPLICATOR_DUP_INSTALLER_OWRPARAM_REGEX_PATTERN', '/^' . DUPLICATOR_LOCAL_OVERWRITE_PARAMS . '_[a-z0-9]{7,}-[0-9]{8}.json$/');
    define("DUPLICATOR_ORIG_FOLDER_PREFIX", 'original_files_');
    define('DUPLICATOR_CERT_PATH', apply_filters('duplicator_pro_certificate_path', DUPLICATOR_LITE_PATH . '/src/Libs/Certificates/cacert.pem'));


    $GLOBALS['DUPLICATOR_SERVER_LIST']            = array('Apache','LiteSpeed', 'Nginx', 'Lighttpd', 'IIS', 'WebServerX', 'uWSGI');
    $GLOBALS['DUPLICATOR_OPTS_DELETE']            = array(
        'duplicator_ui_view_state',
        'duplicator_package_active',
        'duplicator_settings',
        'duplicator_is_pro_enable_notice_dismissed'
    );
    $GLOBALS['DUPLICATOR_GLOBAL_FILE_FILTERS_ON'] = true;
    $GLOBALS['DUPLICATOR_GLOBAL_FILE_FILTERS']    = array(
        'error_log',
        'error.log',
        'debug_log',
        'ws_ftp.log',
        'dbcache',
        'pgcache',
        'objectcache',
        '.DS_Store'
    );


    /* Used to flush a response every N items.
     * Note: This value will cause the Zip file to double in size durning the creation process only*/
    define("DUPLICATOR_ZIP_FLUSH_TRIGGER", 1000);

    /* Let's setup few things to cover all PHP versions */
    if (!defined('PHP_VERSION')) {
        define('PHP_VERSION', phpversion());
    }
    if (!defined('PHP_VERSION_ID')) {
        $version = explode('.', PHP_VERSION);
        define('PHP_VERSION_ID', (($version[0] * 10000) + ($version[1] * 100) + $version[2]));
    }
    if (PHP_VERSION_ID < 50207) {
        if (!(isset($version))) {
            $version = explode('.', PHP_VERSION);
        }
        if (!defined('PHP_MAJOR_VERSION')) {
            define('PHP_MAJOR_VERSION', $version[0]);
        }
        if (!defined('PHP_MINOR_VERSION')) {
            define('PHP_MINOR_VERSION', $version[1]);
        }
        if (!defined('PHP_RELEASE_VERSION')) {
            define('PHP_RELEASE_VERSION', $version[2]);
        }
    }

    if (!defined('DUPLICATOR_CUSTOM_STATS_REMOTE_HOST')) {
        define('DUPLICATOR_CUSTOM_STATS_REMOTE_HOST', '');
    }

    if (!defined('DUPLICATOR_USTATS_DISALLOW')) {
        define('DUPLICATOR_USTATS_DISALLOW', false);
    }
} else {
    error_reporting(0);
    $port = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] != "off") ? "https://" : "http://";
    $url  = $port . $_SERVER["HTTP_HOST"];
    header("HTTP/1.1 404 Not Found", true, 404);
    header("Status: 404 Not Found");
    exit();
}
