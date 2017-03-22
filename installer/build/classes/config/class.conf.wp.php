<?php

/**
 * Class used to update and edit and update the wp-config.php
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\WPConfig
 *
 */
class DUPX_WPConfig
{

	/**
	 *  Updates the web server config files in Step 1
	 *
	 *  @return null
	 */
	public static function updateStandard()
	{
		if (!file_exists('wp-config.php')) return;

		$root_path	 = DUPX_U::setSafePath($GLOBALS['CURRENT_ROOT_PATH']);
		$wpconfig	 = @file_get_contents('wp-config.php', true);

		$patterns = array(
			"/'DB_NAME',\s*'.*?'/",
			"/'DB_USER',\s*'.*?'/",
			"/'DB_PASSWORD',\s*'.*?'/",
			"/'DB_HOST',\s*'.*?'/");

		$db_host = ($_POST['dbport'] == 3306) ? $_POST['dbhost'] : "{$_POST['dbhost']}:{$_POST['dbport']}";

		$replace = array(
			"'DB_NAME', ".'\''.$_POST['dbname'].'\'',
			"'DB_USER', ".'\''.$_POST['dbuser'].'\'',
			"'DB_PASSWORD', ".'\''.DUPX_U::pregReplacementQuote($_POST['dbpass']).'\'',
			"'DB_HOST', ".'\''.$db_host.'\'');

		//SSL CHECKS
		if ($_POST['ssl_admin']) {
			if (!strstr($wpconfig, 'FORCE_SSL_ADMIN')) {
				$wpconfig = $wpconfig.PHP_EOL."define('FORCE_SSL_ADMIN', true);";
			}
		} else {
			array_push($patterns, "/'FORCE_SSL_ADMIN',\s*true/");
			array_push($replace, "'FORCE_SSL_ADMIN', false");
		}

		if ($_POST['ssl_login']) {
			if (!strstr($wpconfig, 'FORCE_SSL_LOGIN')) {
				$wpconfig = $wpconfig.PHP_EOL."define('FORCE_SSL_LOGIN', true);";
			}
		} else {
			array_push($patterns, "/'FORCE_SSL_LOGIN',\s*true/");
			array_push($replace, "'FORCE_SSL_LOGIN', false");
		}

		//CACHE CHECKS
		if ($_POST['cache_wp']) {
			if (!strstr($wpconfig, 'WP_CACHE')) {
				$wpconfig = $wpconfig.PHP_EOL."define('WP_CACHE', true);";
			}
		} else {
			array_push($patterns, "/'WP_CACHE',\s*true/");
			array_push($replace, "'WP_CACHE', false");
		}
		if (!$_POST['cache_path']) {
			array_push($patterns, "/'WPCACHEHOME',\s*'.*?'/");
			array_push($replace, "'WPCACHEHOME', ''");
		}

		if (!is_writable("{$root_path}/wp-config.php")) {
			if (file_exists("{$root_path}/wp-config.php")) {
				chmod("{$root_path}/wp-config.php", 0644) ? DUPX_Log::info('File Permission Update: wp-config.php set to 0644') : DUPX_Log::info('WARNING: Unable to update file permissions and write to wp-config.php.  Please visit the online FAQ for setting file permissions and work with your hosting provider or server administrator to enable this installer.php script to write to the wp-config.php file.');
			} else {
				DUPX_Log::info('WARNING: Unable to locate wp-config.php file.  Be sure the file is present in your archive.');
			}
		}

		$wpconfig	 = preg_replace($patterns, $replace, $wpconfig);
		file_put_contents('wp-config.php', $wpconfig);
		$wpconfig	 = null;
	}

	/**
	 *  Updates the web server config files in Step 1
	 *
	 *  @return null
	 */
	public static function updateExtended()
	{
		$config_file = '';
		if (!file_exists('wp-config.php')) {
			return $config_file;
		}

		$patterns	 = array("/('|\")WP_HOME.*?\)\s*;/",
			"/('|\")WP_SITEURL.*?\)\s*;/",
			"/('|\")DOMAIN_CURRENT_SITE.*?\)\s*;/",
			"/('|\")PATH_CURRENT_SITE.*?\)\s*;/");
		$replace	 = array("'WP_HOME', '{$_POST['url_new']}');",
			"'WP_SITEURL', '{$_POST['url_new']}');",
			"'DOMAIN_CURRENT_SITE', '{$mu_newDomainHost}');",
			"'PATH_CURRENT_SITE', '{$mu_newUrlPath}');");

		$config_file = file_get_contents('wp-config.php', true);
		$config_file = preg_replace($patterns, $replace, $config_file);
		file_put_contents('wp-config.php', $config_file);

		return $config_file;
	}
}
?>