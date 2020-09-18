<?php
// This file all content is copied from the Duplicator Pro.
/**
 * redirect to installer.php if exists
 */

// for ngrok url and Local by Flywheel Live URL
if (isset($_SERVER['HTTP_X_ORIGINAL_HOST'])) {
    $host = $_SERVER['HTTP_X_ORIGINAL_HOST'];
} else {
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];//WAS SERVER_NAME and caused problems on some boxes
}
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER ['HTTPS'] = 'on';
}
$serverDomain  = 'http'.((isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') ? 's' : '').'://'.$host;
$serverUrlSelf = preg_match('/^[\\\\\/]?$/', dirname($_SERVER['SCRIPT_NAME'])) ? '' : dirname($_SERVER['SCRIPT_NAME']);

define('DUPX_INIT', str_replace('\\', '/', dirname(__FILE__)));
define('DUPX_INIT_URL', $serverDomain.$serverUrlSelf);
define('DUPX_ROOT', preg_match('/^[\\\\\/]?$/', dirname(DUPX_INIT)) ? '/' : dirname(DUPX_INIT));
define('DUPX_ROOT_URL', $serverDomain.(preg_match('/^[\\\\\/]?$/', dirname($serverUrlSelf)) ? '' : dirname($serverUrlSelf)));

if (file_exists(DUPX_ROOT.'/installer.php')) {
    header('Location: '.DUPX_ROOT_URL.'/installer.php');
    die;
}

echo "Please browse to the 'installer.php' or [hash]_installer.php from your web browser to proceed with the install process!";
die;
