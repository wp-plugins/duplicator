<?php

/**
 *  DUPLICATOR_CREATE_DBSCRIPT
 *  Create the SQL DataDump File
 *  @param string $destination		The full path and filname where the sql script will be written
 */
function duplicator_create_dbscript($destination) {
	try {

		global $wpdb;
		$dbiconv = ($GLOBALS['duplicator_opts']['dbiconv'] == "0" && function_exists("iconv")) ? false : true;
		$handle  = fopen($destination,'w+');
		$tables  = $wpdb->get_col('SHOW TABLES');
		
		duplicator_log("log:fun__create_dbscript=>started");
		if ($dbiconv) {
			duplicator_log("log:fun__create_dbscript=>dbiconv enabled");
		}
		
		foreach ($tables as $table) {
		
			//Generate Drop Statement
			//$sql_del = ($GLOBALS['duplicator_opts']['dbadd_drop']) ? "DROP TABLE IF EXISTS {$table};\n\n" : "";
			//@fwrite($handle, $sql_del);
			
			//Generate Create Statement
			$row_count  = $wpdb->get_var("SELECT Count(*) FROM `{$table}`");
			duplicator_log("start: {$table} ({$row_count})");	
		
			$create  = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);
			$sql_crt = "{$create[1]};\n\n";
			@fwrite($handle, $sql_crt);
			
			if ($row_count > 100) {
				$row_count = ceil($row_count / 100);
			} else if ($row_count > 0) {
				$row_count = 1;  
			}
			
			//PERFORM ICONV ROUTINE
			//Chunck the query results to avoid memory issues
			if ($dbiconv) {
			
				for ($i = 0; $i < $row_count; $i++) {
					$sql   = "";
					$limit = $i * 100;
					$query = "SELECT * FROM `{$table}` LIMIT {$limit}, 100";
					$rows  = $wpdb->get_results($query, ARRAY_A);
					if (is_array($rows)) {
						foreach ($rows as $row) {
							$sql .= "INSERT INTO `{$table}` VALUES(";
							$num_values  = count($row);
							$num_counter = 1;
							foreach ($row as $value) {
								$value = @iconv(DUPLICATOR_DB_ICONV_IN, DUPLICATOR_DB_ICONV_OUT, $value);
								($num_values == $num_counter) 
									? $sql .= '"' . @mysql_real_escape_string($value) . '"'
									: $sql .= '"' . @mysql_real_escape_string($value) . '", ';
								$num_counter++;
							}
							$sql .= ");\n";
						}
						@fwrite($handle, $sql);
						duplicator_fcgi_flush();
					}
				}
				
			//DO NOT PERFORM ICONV
			} else {

				for ($i = 0; $i < $row_count; $i++) {
					$sql   = "";
					$limit = $i * 100;
					$query = "SELECT * FROM `{$table}` LIMIT {$limit}, 100";
					$rows  = $wpdb->get_results($query, ARRAY_A);
					if (is_array($rows)) {
						foreach ($rows as $row) {
							$sql .= "INSERT INTO `{$table}` VALUES(";
							$num_values  = count($row);
							$num_counter = 1;
							foreach ($row as $value) {
								($num_values == $num_counter) 
									? $sql .= '"' . @mysql_real_escape_string($value) . '"'
									: $sql .= '"' . @mysql_real_escape_string($value) . '", ';
								$num_counter++;
							}
							$sql .= ");\n";
						}
						@fwrite($handle, $sql);
						duplicator_fcgi_flush();
					}
				}
			
			}
			
			@fwrite($handle, "\n\n");
			duplicator_log("done:  {$table}");
		}		
	
		duplicator_log("log:fun__create_dbscript=>sql file written to {$destination}");
		fclose($handle);
		$wpdb->flush();
		duplicator_log("log:fun__create_dbscript=>ended");
	
	} catch(Exception $e) {
		duplicator_log("log:fun__create_dbscript=>runtime error: " . $e);
	}
}

/**
 *  DUPLICATOR_CREATE_INSTALLERFILE
 *  Prep the Installer file for use. use %string% token for replacing 
 *  @param string $uniquename	The unique name this installer file will be associated with
 */
function duplicator_create_installerFile($uniquename) {

	duplicator_log("log:fun__create_installerFile=>started");
	
	global $wpdb;
	$template		 = duplicator_safe_path(DUPLICATOR_PLUGIN_PATH  . 'files/installer.template.php');
	$installerRescue = duplicator_safe_path(DUPLICATOR_PLUGIN_PATH  . 'files/installer.rescue.php');
	$installerCore	 = duplicator_safe_path(DUPLICATOR_SSDIR_PATH)  . "/{$uniquename}_installer.php";

	$err_msg    = "\n!!!WARNING!!! unable to read/write installer\nSee file:{$installerCore} \nPlease check permission and owner on file and parent folder."; 
	
	get_option('duplicator_options') == ""  ? "" : $duplicator_opts = unserialize(get_option('duplicator_options'));
	$replace_items = Array(
		"fwrite_current_url" 		=> get_option('siteurl'),
		"fwrite_package_name"  	 	=> "{$uniquename}_package.zip",
		"fwrite_secure_name"	 	=> "{$uniquename}",
		"fwrite_nurl" 				=> $duplicator_opts['nurl'],
		"fwrite_dbhost" 			=> $duplicator_opts['dbhost'],
		"fwrite_dbname" 			=> $duplicator_opts['dbname'],
		"fwrite_dbuser" 			=> $duplicator_opts['dbuser'],
		"fwrite_wp_tableprefix" 	=> $wpdb->prefix,
		"fwrite_site_title"			=> get_option('blogname'),
		"fwrite_rescue_flag"		=> "");
		
	if( file_exists($template) && is_readable($template)) {
	
		$install_str = duplicator_parse_template($template, $replace_items);
		if (empty($install_str)) {
			die(duplicator_log("log:fun__create_installerFile=>file-empty-read" . $err_msg));
		}
		
		//RESCUE FILE
		$replace_items["fwrite_rescue_flag"] = '(rescue file)';
		$rescue_str = duplicator_parse_template($template, $replace_items);
		$fp  = fopen($installerRescue, (!file_exists($installerRescue)) ? 'x+' : 'w');	
		@fwrite($fp, $rescue_str, strlen($rescue_str));
		@fclose($fp);
		$rescue_str = null;
		
		//INSTALLER FILE
		if (!file_exists($installerCore)) {
			$fp2 = fopen($installerCore, 'x+') or die(duplicator_log("log:fun__create_installerFile=>file-open-error-x" . $err_msg));
		} else {
			$fp2 = fopen($installerCore, 'w')  or die(duplicator_log("log:fun__create_installerFile=>file-open-error-w" . $err_msg));
		}
		
		if (fwrite($fp2, $install_str, strlen($install_str))) {
			duplicator_log("log:fun__create_installerFile=>installer.php updated at: {$installerCore}");
		} else {
			duplicator_log("log:fun__create_installerFile=>file-create-error" . $err_msg);
		}
				
		@fclose($fp2);
	} 
	else
	{
		die(duplicator_log("log:fun__create_installerFile=>Template missing or unreadable: '$template'"));
	}
	
	duplicator_log("log:fun__create_installerFile=>ended");
}

/**
 *  DUPLICATOR_PARSE_TEMPLATE
 *  Tokenize a file based on an array key 
 *
 *  @param string $filename		The filename to tokenize
 *  @param array  $data			The array of key value items to tokenize
 */
function duplicator_parse_template($filename, $data) {
    $q = file_get_contents($filename);
    foreach ($data as $key => $value) {
        $q = str_replace('%'.$key.'%', $value, $q);
    }
    return $q;
}


/**
 *  DUPLICATOR_BYTESIZE
 *  Display human readable byte sizes
 *  @param string $size		The size in bytes
 */
function duplicator_bytesize($size) {
    try {
		$units = array('B', 'KB', 'MB', 'GB', 'TB');
		for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
		return round($size, 2).$units[$i];
	} catch (Exception $e) {
		return "n/a";
	}
}

/**
 *  DUPLICATOR_DIRSIZE
 *  Get the directory size recursively, but don't calc the snapshot directory, exclusion diretories
 *  @param string $directory		The directory to calculate
 */
function duplicator_dirInfo($directory) { 
	try {
		
		$size = 0; 
		$count = 0;
		$folders = 0;
		$flag = false;

		//EXCLUDE: Snapshot directory
		$directory = duplicator_safe_path($directory);
		if( strstr($directory, DUPLICATOR_SSDIR_PATH)) {
			return;
		}
		
		//EXCLUDE: Directory Exclusions List
		if ($GLOBALS['duplicator_bypass-array'] != null) {
			foreach ($GLOBALS['duplicator_bypass-array'] as $val) {
				if (duplicator_safe_path($val) == $directory) {
					return;
				}
			}
		}

		if ($handle = @opendir($directory)) { 
			while (false !== ($file = @readdir($handle))) { 
				if ($file != '.' && $file != '..') { 
					$nextpath = $directory . '/' . $file; 
					if (is_dir($nextpath)) { 
						$folders++;
						$result = duplicator_dirInfo($nextpath); 
						$size  += $result['size']; 
						$count += $result['count']; 
						$folders += $result['folders']; 
					} 
					else if (is_file($nextpath) && is_readable($nextpath)) { 
						if(!in_array(@pathinfo($nextpath, PATHINFO_EXTENSION), $GLOBALS['duplicator_skip_ext-array'])) {
							$fmod  = @filesize($nextpath);
							if ($fmod === false) {
								$flag = true;
							} else {
								$size +=  @filesize($nextpath);
							}
							$count++;
						}
					} 
				} 
			} 
		} 
		closedir($handle); 
		$total['size']    = $size; 
		$total['count']   = $count; 
		$total['folders'] = $folders; 
		$total['flag']    = $flag; 
		return $total; 
		
	}  catch(Exception $e) {
		duplicator_log("log:fun__dirInfo=>runtime error: " . $e . "\nNOTE: Try excluding the stat failed to the Duplicators directory exclusion list or change the permissions.");
	}
} 

/**
 *  DUPLICATOR_CREATE_SNAPSHOTPATH
 *  Creates the snapshot directory if it doesn't already exisit
 */
function duplicator_init_snapshotpath() {

	$path_wproot = duplicator_safe_path(DUPLICATOR_WPROOTPATH);
	$path_ssdir  = duplicator_safe_path(DUPLICATOR_SSDIR_PATH);
	$path_plugin = duplicator_safe_path(DUPLICATOR_PLUGIN_PATH);
	
	//--------------------------------
	//CHMOD DIRECTORY ACCESS
	//wordpress root directory
	@chmod($path_wproot , 0755);

	//snapshot directory
	@mkdir($path_ssdir, 0755);
	@chmod($path_ssdir, 0755);
	
	//plugins dir/files
	@chmod($path_plugin . 'files', 0755);
	@chmod(duplicator_safe_path($path_plugin . 'files/installer.rescue.php'), 0644);
	
	//--------------------------------
	//FILE CREATION	
	//SSDIR: Create Index File
	$ssfile = @fopen($path_ssdir .'/index.php', 'w');
	@fwrite($ssfile, '<?php error_reporting(0);  if (stristr(php_sapi_name(), "fcgi")) { $url  =  "http://" . $_SERVER["HTTP_HOST"]; header("Location: {$url}/404.html");} else { header("HTML/1.1 404 Not Found", true, 404);} exit(); ?>');
	@fclose($ssfile);
	
	//SSDIR: Create token file in snapshot
	$tokenfile = @fopen($path_ssdir .'/dtoken.php', 'w');
	@fwrite($tokenfile, '<?php error_reporting(0);  if (stristr(php_sapi_name(), "fcgi")) { $url  =  "http://" . $_SERVER["HTTP_HOST"]; header("Location: {$url}/404.html");} else { header("HTML/1.1 404 Not Found", true, 404);} exit(); ?>');
	@fclose($tokenfile);
	
	//SSDIR: Create .htaccess
	$htfile = @fopen($path_ssdir .'/.htaccess', 'w');
	@fwrite($htfile, "Options -Indexes");
	@fclose($htfile);
	
	//SSDIR: Robots.txt file
	$robotfile = @fopen($path_ssdir .'/robots.txt', 'w');
	@fwrite($robotfile, "User-agent: * \nDisallow: /" . DUPLICATOR_SSDIR_NAME . '/');
	@fclose($robotfile);
	
	//PLUG DIR: Create token file in plugin
	$tokenfile2 = @fopen($path_plugin .'files/dtoken.php', 'w');
	@fwrite($tokenfile2, '<?php @error_reporting(0); @require_once("../../../../wp-admin/admin.php"); global $wp_query; $wp_query->set_404(); header("HTML/1.1 404 Not Found", true, 404); header("Status: 404 Not Found"); @include(get_template_directory () . "/404.php"); ?>');
	@fclose($tokenfile2);
}


/**
 *  DUPLICATOR_SAFE_PATH
 *  Makes path safe for any OS
 *  Paths should ALWAYS READ be "/"
 *		uni: /home/path/file.xt
 *		win:  D:/home/path/file.txt 
 *  @param string $path		The path to make safe
 */
function duplicator_safe_path($path) {
	return str_replace("\\", "/", $path);
}


/**
 *  DUPLICATOR_FCGI_FLUSH
 *  PHP_SAPI for fcgi requires a data flush of at least 256
 *  bytes every 40 seconds or else it forces a script hault
 */
function duplicator_fcgi_flush() {
	echo(str_repeat(' ',256));
	@flush();
}

/**
 *  DUPLICATOR_SNAPSHOT_URLPATH
 *	returns the snapshot url
 */
function duplicator_snapshot_urlpath() {
	return get_site_url(null, '', is_ssl() ? 'https' : 'http') . '/' . DUPLICATOR_SSDIR_NAME . '/' ;
}

/**
 *  DUPLICATOR_LOG
 *  Centralized logging method
 *  @param string $msg		The message to log
 */
function duplicator_log($msg, $level = 0) {
	$stamp = date('h:i:s');
	@fwrite($GLOBALS['duplicator_package_log_handle'], "{$stamp} {$msg} \n");
}

?>