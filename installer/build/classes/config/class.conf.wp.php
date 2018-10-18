<?php
defined("DUPXABSPATH") or die("");
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
	 *  Updates the web server config files in Step 3
	 *
	 *  @return null
	 */
	public static function updateStandard()
	{
		if (!file_exists('wp-config.php')) {
			DUPX_Log::info('WARNING: Unable to locate wp-config.php file during standard update process.  Be sure the file is present in your archive.');
			return;
		}

		$root_path	= DUPX_U::setSafePath($GLOBALS['CURRENT_ROOT_PATH']);
		$wpconfig	= @file_get_contents('wp-config.php', true);

		$db_port    = is_int($_POST['dbport'])   ? DUPX_U::sanitize_text_field($_POST['dbport']) : 3306;
		$db_host	= ($db_port == 3306) ? DUPX_U::sanitize_text_field($_POST['dbhost']) : DUPX_U::sanitize_text_field($_POST['dbhost']).':'.DUPX_U::sanitize_text_field($db_port);
		$db_name	= isset($_POST['dbname']) ? DUPX_U::sanitize_text_field($_POST['dbname']) : null;
		$db_user	= isset($_POST['dbuser']) ? DUPX_U::sanitize_text_field($_POST['dbuser']) : null;
       	$db_pass	= isset($_POST['dbpass']) ? DUPX_U::sanitize_text_field($_POST['dbpass']) : null;

		$patterns = array(
			"/'DB_NAME',\s*'.*?'/",
			"/'DB_USER',\s*'.*?'/",
			"/'DB_PASSWORD',\s*'.*?'/",
			"/'DB_HOST',\s*'.*?'/"
		);

		$replace = array(
			"'DB_NAME', "		. "'{$db_name}'",
			"'DB_USER', "		. "'{$db_user}'",
			"'DB_PASSWORD', "	. "'{$db_pass}'",
			"'DB_HOST', "		. "'{$db_host}'"
		);

		//SSL CHECKS
		if ($_POST['ssl_admin']) {
			if (!strstr($wpconfig, 'FORCE_SSL_ADMIN')) {
				$wpconfig = $wpconfig.PHP_EOL."define('FORCE_SSL_ADMIN', true);";
			}
		} else {
			array_push($patterns, "/'FORCE_SSL_ADMIN',\s*true/");
			array_push($replace, "'FORCE_SSL_ADMIN', false");
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

		// array_map replaced because of installer error in PHP 5.2.9 - Cannot call method self::customEscape() or method does not exist in installer.php on line 1480
		// $replace  = array_map('self::customEscape', $replace);
		foreach ($replace as $key=>$val) {
			$replace[$key] = self::customEscape($val);
		}
        
		$wpconfig = preg_replace($patterns, $replace, $wpconfig);

		file_put_contents('wp-config.php', $wpconfig);
		$wpconfig = null;
	}

	/**
	 *  Updates the web server config files in Step 3
	 *
	 *  @return null
	 */
	public static function updateExtended()
	{
		$config_file = '';
		if (!file_exists('wp-config.php')) {
			return $config_file;
		}

		$root_path		= DUPX_U::setSafePath($GLOBALS['CURRENT_ROOT_PATH']);
		$wpconfig_path	= "{$root_path}/wp-config.php";
		$config_file	= @file_get_contents($wpconfig_path, true);

		$patterns	 = array(
			"/('|\")WP_HOME.*?\)\s*;/",
			"/('|\")WP_SITEURL.*?\)\s*;/");
		
		$post_url_new = DUPX_U::sanitize_text_field($_POST['url_new']);
		$replace	 = array(
			"'WP_HOME', '".$post_url_new."');",
			"'WP_SITEURL', '".$post_url_new."');");

		//Not sure how well tokenParser works on all servers so only using for not critical constants at this point.
		//$count checks for dynamic variable types such as:  define('WP_TEMP_DIR',	'D:/' . $var . 'somepath/');
		//which should not be updated.
		$defines = self::tokenParser($wpconfig_path);

		//WP_CONTENT_DIR
		if (isset($defines['WP_CONTENT_DIR'])) {
			$post_path_old = DUPX_U::sanitize_text_field($_POST['path_old']);
			$post_path_new = DUPX_U::sanitize_text_field($_POST['path_new']);
			$val = str_replace($post_path_old, $post_path_new, DUPX_U::setSafePath($defines['WP_CONTENT_DIR']), $count);
			if ($count > 0) {
				array_push($patterns, "/('|\")WP_CONTENT_DIR.*?\)\s*;/");
				array_push($replace, "'WP_CONTENT_DIR', '{$val}');");
			}
		}

		//WP_CONTENT_URL
		if (isset($defines['WP_CONTENT_URL'])) {
			$post_url_old = DUPX_U::sanitize_text_field($_POST['url_old']);
			$post_url_new = DUPX_U::sanitize_text_field($_POST['url_new']);
			$val = str_replace($post_url_old . '/', $post_url_new . '/', $defines['WP_CONTENT_URL'], $count);
			if ($count > 0) {
				array_push($patterns, "/('|\")WP_CONTENT_URL.*?\)\s*;/");
				array_push($replace, "'WP_CONTENT_URL', '{$val}');");
			}
		}

		//WP_TEMP_DIR
		if (isset($defines['WP_TEMP_DIR'])) {
			$post_path_old = DUPX_U::sanitize_text_field($_POST['path_old']);
			$post_path_new = DUPX_U::sanitize_text_field($_POST['path_new']);
			$val = str_replace($post_path_old, $post_path_new, DUPX_U::setSafePath($defines['WP_TEMP_DIR']) , $count);
			if ($count > 0) {
				array_push($patterns, "/('|\")WP_TEMP_DIR.*?\)\s*;/");
				array_push($replace, "'WP_TEMP_DIR', '{$val}');");
			}
		}
		
		//DOMAIN_CURRENT_SITE
		if (isset($defines['DOMAIN_CURRENT_SITE'])) {
			$post_url_new = DUPX_U::sanitize_text_field($_POST['url_new']);
			$mu_newDomainHost = parse_url($post_url_new, PHP_URL_HOST);
			array_push($patterns, "/('|\")DOMAIN_CURRENT_SITE.*?\)\s*;/");
			array_push($replace, "'DOMAIN_CURRENT_SITE', '{$mu_newDomainHost}');");
		}

		//PATH_CURRENT_SITE
		if (isset($defines['PATH_CURRENT_SITE'])) {
			$post_url_new = DUPX_U::sanitize_text_field($_POST['url_new']);
			$mu_newUrlPath = parse_url($post_url_new, PHP_URL_PATH);
			array_push($patterns, "/('|\")PATH_CURRENT_SITE.*?\)\s*;/");
			array_push($replace, "'PATH_CURRENT_SITE', '{$mu_newUrlPath}');");
		}
		
		$config_file = preg_replace($patterns, $replace, $config_file);
		file_put_contents($wpconfig_path, $config_file);
		$config_file = file_get_contents($wpconfig_path, true);

		return $config_file;
	}

	/**
	 *  Used to parse the wp-config.php file
	 *
	 *  @return null
	 */
	public static function tokenParser($wpconfig_path)
	{
		$defines = array();
		$wpconfig_file = @file_get_contents($wpconfig_path);

		if (!function_exists('token_get_all')) {
			DUPX_Log::info("\nNOTICE: PHP function 'token_get_all' does not exist so skipping WP_CONTENT_DIR and WP_CONTENT_URL processing.");
			return $defines;
		}

		if ($wpconfig_file === false) {
			return $defines;
		}

		$defines = array();
		$tokens	 = token_get_all($wpconfig_file);
		$token	 = reset($tokens);
		while ($token) {
			if (is_array($token)) {
				if ($token[0] == T_WHITESPACE || $token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
					// do nothing
				} else if ($token[0] == T_STRING && strtolower($token[1]) == 'define') {
					$state = 1;
				} else if ($state == 2 && self::isConstant($token[0])) {
					$key	 = $token[1];
					$state	 = 3;
				} else if ($state == 4 && self::isConstant($token[0])) {
					$value	 = $token[1];
					$state	 = 5;
				}
			} else {
				$symbol = trim($token);
				if ($symbol == '(' && $state == 1) {
					$state = 2;
				} else if ($symbol == ',' && $state == 3) {
					$state = 4;
				} else if ($symbol == ')' && $state == 5) {
					$defines[self::tokenStrip($key)] = self::tokenStrip($value);
					$state = 0;
				}
			}
			$token = next($tokens);
		}

		return $defines;
	}

	private static function tokenStrip($value)
	{
		return preg_replace('!^([\'"])(.*)\1$!', '$2', $value);
	}

	private static function customEscape($str)
    {
		return str_replace('\\', '\\\\', $str);
	}

	private static function isConstant($token)
	{
		return $token == T_CONSTANT_ENCAPSED_STRING || $token == T_STRING || $token == T_LNUMBER || $token == T_DNUMBER;
	}
}
?>