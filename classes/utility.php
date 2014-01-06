<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

class DUP_Util {
	
	/**
	*  PHP_SAPI for fcgi requires a data flush of at least 256
	*  bytes every 40 seconds or else it forces a script hault
	*/
	static public function FcgiFlush() {
		echo(str_repeat(' ', 300));
		@flush();
	}

	/**
	*  returns the snapshot url
	*/
	static public  function SSDirURL() {
		 return get_site_url(null, '', is_ssl() ? 'https' : 'http') . '/' . DUPLICATOR_SSDIR_NAME . '/';
	}

	/**
	*  Runs the APC cache to pre-cache the php files
	*  returns true if all files where cached
	*/
	static public function RunAPC() {
	   if(function_exists('apc_compile_file')){
		   $file01 = @apc_compile_file(DUPLICATOR_PLUGIN_PATH . "duplicator.php");
		   return ($file01);
	   } else {
		   return false;
	   }
	}

	/**
	*  Display human readable byte sizes
	*  @param string $size		The size in bytes
	*/
	static public function ByteSize($size) {
		try {
			$units = array('B', 'KB', 'MB', 'GB', 'TB');
			for ($i = 0; $size >= 1024 && $i < 4; $i++)
				$size /= 1024;
			return round($size, 2) . $units[$i];
		} catch (Exception $e) {
			return "n/a";
		}
	}

	/**
	*  Makes path safe for any OS
	*  Paths should ALWAYS READ be "/"
	* 		uni: /home/path/file.xt
	* 		win:  D:/home/path/file.txt 
	*  @param string $path		The path to make safe
	*/
	static public function SafePath($path) {
		return str_replace("\\", "/", $path);
	}

	/** 
	 * Get current microtime as a float. Can be used for simple profiling.
	 */
	static public function GetMicrotime() {
		return microtime(true);
	}

	/** 
	 * Append the value to the string if it doesn't already exist
	 */
	static public function StringAppend($string, $value ) {
	   return $string . (substr($string, -1) == $value ? '' : $value);
	}

	/** 
	 * Return a string with the elapsed time.
	 * Order of $end and $start can be switched. 
	 */
	static public function ElapsedTime($end, $start) {
		return sprintf("%.2f sec.", abs($end - $start));
	}

	/**
	 * Get the MySQL system variables
	 * @param conn $dbh Database connection handle
	 * @return string the server variable to query for
	 */
	static public function MysqlVariableValue($variable) {
		global $wpdb;
		$row = $wpdb->get_row("SHOW VARIABLES LIKE '{$variable}'", ARRAY_N);
		return isset($row[1]) ? $row[1] : null;
	}

	/**
	 * List all of the directories of a path
	 * @path path to a system directory
	 * @return array of all directories in that path
	 */
	static public function ListDirs($path = '.') {
		$dirs = array();

		foreach (new DirectoryIterator($path) as $file) {
			if ($file->isDir() && !$file->isDot()) {
				$dirs[] = DUP_Util::SafePath($file->getPathname());
			}
		}
		return $dirs;
	}

	/** 
	 * Does the directory have content
	 */
	static public function IsDirectoryEmpty($dir) {
		if (!is_readable($dir)) return NULL; 
		return (count(scandir($dir)) == 2);
	}
	
	/** 
	 * Size of the directory recuresivly
	 */
	static public function GetDirectorySize($dir) {
		if(!file_exists($dir)) return 0;
		if(is_file($dir)) return filesize($dir);
		$size = 0;
		foreach(glob($dir."/*") as $fn)
		  $size += self::GetDirectorySize($fn);
		return $size;
	}
	
	
	public static function IsShellExecAvailable() {

		if (array_intersect(array('shell_exec', 'escapeshellarg', 'escapeshellcmd'), array_map('trim', explode(',', @ini_get('disable_functions')))))
			return false;

		// Can we issue a simple echo command?
		if (!@shell_exec('echo duplicator'))
			return false;

		return true;
	}
	
	public static function IsOSWindows() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return true;
		}
		
		return false;
	}

	/**
	*  Creates the snapshot directory if it doesn't already exisit
	*/
	static public function InitSnapshotDirectory() {
		$path_wproot	= DUP_Util::SafePath(DUPLICATOR_WPROOTPATH);
		$path_ssdir		= DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH);
		$path_plugin	= DUP_Util::SafePath(DUPLICATOR_PLUGIN_PATH);

		//--------------------------------
		//CHMOD DIRECTORY ACCESS
		//wordpress root directory
		@chmod($path_wproot, 0755);

		//snapshot directory
		@mkdir($path_ssdir, 0755);
		@chmod($path_ssdir, 0755);
		
		//snapshot tmp directory
		$path_ssdir_tmp = $path_ssdir . '/tmp';
		@mkdir($path_ssdir_tmp, 0755);
		@chmod($path_ssdir_tmp, 0755);

		//plugins dir/files
		@chmod($path_plugin . 'files', 0755);

		//--------------------------------
		//FILE CREATION	
		//SSDIR: Create Index File
		$ssfile = @fopen($path_ssdir . '/index.php', 'w');
		@fwrite($ssfile, '<?php error_reporting(0);  if (stristr(php_sapi_name(), "fcgi")) { $url  =  "http://" . $_SERVER["HTTP_HOST"]; header("Location: {$url}/404.html");} else { header("HTML/1.1 404 Not Found", true, 404);} exit(); ?>');
		@fclose($ssfile);

		//SSDIR: Create token file in snapshot
		$tokenfile = @fopen($path_ssdir . '/dtoken.php', 'w');
		@fwrite($tokenfile, '<?php error_reporting(0);  if (stristr(php_sapi_name(), "fcgi")) { $url  =  "http://" . $_SERVER["HTTP_HOST"]; header("Location: {$url}/404.html");} else { header("HTML/1.1 404 Not Found", true, 404);} exit(); ?>');
		@fclose($tokenfile);

		//SSDIR: Create .htaccess
		$htfile = @fopen($path_ssdir . '/.htaccess', 'w');
		@fwrite($htfile, "Options -Indexes");
		@fclose($htfile);

		//SSDIR: Robots.txt file
		$robotfile = @fopen($path_ssdir . '/robots.txt', 'w');
		@fwrite($robotfile, "User-agent: * \nDisallow: /" . DUPLICATOR_SSDIR_NAME . '/');
		@fclose($robotfile);

		//PLUG DIR: Create token file in plugin
		$tokenfile2 = @fopen($path_plugin . 'installer/dtoken.php', 'w');
		@fwrite($tokenfile2, '<?php @error_reporting(0); @require_once("../../../../wp-admin/admin.php"); global $wp_query; $wp_query->set_404(); header("HTML/1.1 404 Not Found", true, 404); header("Status: 404 Not Found"); @include(get_template_directory () . "/404.php"); ?>');
		@fclose($tokenfile2);
	}

}
?>