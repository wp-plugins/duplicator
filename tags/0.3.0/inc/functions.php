<?php

/**
 *  DUPLICATOR_CREATE_DBSCRIPT
 *  Create the SQL DataDump File
 *  @param string $destination		The full path and filname where the sql script will be written
 */
function duplicator_create_dbscript($destination) {
	try {
		duplicator_log("log:fun__create_dbscript=>started");
		
		global $wpdb;
		$tables =  $wpdb->get_col('SHOW TABLES');
		$dbiconv = $GLOBALS['duplicator_opts']['dbiconv'] == "0" ? false : true;
		$return = "";
		$handle = fopen($destination,'w+');
		
		//CREATE TABLES
		//PERFORM ICONV
		if ($dbiconv && function_exists("iconv")) {
			duplicator_log("log:fun__create_dbscript=>dbiconv enabled");
			foreach($tables as $table) {
				duplicator_log("log:fun__create_dbscript=>creating table: $table", 2);
				$result 	 = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_N);
				$num_fields  = count(@$result[0]);   
				$items		 = count($result);
				$return		 = '';
				$row2 		 = $wpdb->get_row('SHOW CREATE TABLE '.$table, ARRAY_N); 
				$return		.= "\n\n" . $row2[1] . ";\n\n";
				{
					$ct=0;
					while($ct < $items) {
						$row	 = $result[$ct];
						$return .= 'INSERT INTO '.$table.' VALUES(';
						for($j=0; $j<$num_fields; $j++) 	{
							$row[$j] = iconv(DUPLICATOR_DB_ICONV_IN, DUPLICATOR_DB_ICONV_OUT, $row[$j]);
							$row[$j] = mysql_real_escape_string($row[$j]);
							$return .= (isset($row[$j])) ? '"'.$row[$j].'"'	: '""'; 
							if ($j < ($num_fields-1)) { $return .= ','; }
						}
						$return.= ");\n";
						$ct++;
					}
				}
				duplicator_log("log:fun__create_dbscript=>table processed: $table", 2);
				$return .= "\n";
				@fwrite($handle, $return);
			}
		//DO NOT PERFORM ICONV
		} else {
			foreach($tables as $table) {
				duplicator_log("log:fun__create_dbscript=>creating table: $table", 2);
				$result 	 = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_N);
				$num_fields  = count($result[0]);   
				$items		 = count($result);
				$return		 = '';
				$row2 		 = $wpdb->get_row('SHOW CREATE TABLE '.$table, ARRAY_N); 
				$return		.= "\n\n" . $row2[1] . ";\n\n";
				{
					$ct=0;
					while($ct < $items) {
						$row	 = $result[$ct];
						$return .= 'INSERT INTO '.$table.' VALUES(';
						for($j=0; $j<$num_fields; $j++) 	{
							$row[$j] = mysql_real_escape_string($row[$j]);
							$return .= (isset($row[$j])) ? '"'.$row[$j].'"'	: '""'; 
							if ($j < ($num_fields-1)) { $return .= ','; }
						}
						$return.= ");\n";
						$ct++;
					}
				}
				duplicator_log("log:fun__create_dbscript=>table processed: $table", 2);
				$return .= "\n";
				@fwrite($handle, $return);
			}
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
	$template 	= duplicator_safe_path(DUPLICATOR_PLUGIN_PATH . 'files/installer.template.php');
	$installer	= duplicator_safe_path(DUPLICATOR_PLUGIN_PATH . 'files/installer.php');
	$installDir = duplicator_safe_path(DUPLICATOR_PLUGIN_PATH) . "files/";
	$err_msg    = "\n!!!WARNING!!! unable to read/write installer\nSee file:{$installer} \nPlease check permission and owner on path:{$installDir}"; 
	
	get_option('duplicator_options') == ""  ? "" : $duplicator_opts = unserialize(get_option('duplicator_options'));
	$replace_items = Array(
		"current_url" 		=> get_option('siteurl'),
		"package_name"  	=> "{$uniquename}_package.zip",
		"nurl" 				=> $duplicator_opts['nurl'],
		"dbhost" 			=> $duplicator_opts['dbhost'],
		"dbname" 			=> $duplicator_opts['dbname'],
		"dbuser" 			=> $duplicator_opts['dbuser'],
		"wp_tableprefix" 	=> $wpdb->prefix,
		"site_title"		=> get_option('blogname'));
		
	if( file_exists($template)) {
		$str = duplicator_parse_template($template, $replace_items);
		
		if (empty($str)) {
			die(duplicator_log("log:fun__create_installerFile=>file-empty-read" . $err_msg));
		}
		
		if (!file_exists($installer)) {
			$fp = fopen($installer, 'x+') 
				or die(duplicator_log("log:fun__create_installerFile=>file-open-error-x" . $err_msg));
		} else {
			$fp = fopen($installer, 'w') 
				or die(duplicator_log("log:fun__create_installerFile=>file-open-error-w" . $err_msg));
		}
		
		if (fwrite($fp, $str, strlen($str))) {
			duplicator_log("log:fun__create_installerFile=>install.php updated at: {$installer}");
		} else {
			duplicator_log("log:fun__create_installerFile=>file-create-error" . $err_msg);
		}
		fclose($fp);
		
	} 
	else
	{
		die(duplicator_log("log:fun__create_installerFile=>Template missing: '$template'"));
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
				if (strstr($directory, duplicator_safe_path($val))) {
					return;
				}
			}
		}

		if ($handle = @opendir($directory)) { 
			while (false !== ($file = @readdir($handle))) { 
				$nextpath = $directory . '/' . $file; 
				if ($file != '.' && $file != '..') { 
					if (is_dir($nextpath)) { 
					  $folders++;
					  $result = duplicator_dirInfo($nextpath); 
					  $size  += $result['size']; 
					  $count += $result['count']; 
					  $folders += $result['folders']; 
					} 
					else if (is_file($nextpath)) { 
					  $fmod  = @filesize($nextpath);
					  if ($fod === false) {
						$flag = true;
					  } else {
						$size +=  @filesize($nextpath);
					  }
					  $count++; 
					} 
				} 
			} 
		} 
		closedir ($handle); 
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

	//WORDPRESS ROOT DIRECTORY
	@chmod(DUPLICATOR_WPROOTPATH, 0755);

	//SNAPSHOT DIRECTORY
	@mkdir(DUPLICATOR_SSDIR_PATH, 0755);
	@chmod(DUPLICATOR_SSDIR_PATH, 0755);
	
	//Create Index File
	$ssfile = @fopen(DUPLICATOR_SSDIR_PATH.'/index.php', 'w');
	@fwrite($ssfile, "<?php header('HTTP/1.0 404 Not Found'); ?>");
	@fclose($ssfile);
	
	//Create .htaccess
	$htfile = @fopen(DUPLICATOR_SSDIR_PATH.'/.htaccess', 'w');
	@fwrite($htfile, "Options -Indexes");
	@fclose($htfile);
	
	//PLUGINS DIR/FILES
	@chmod(DUPLICATOR_PLUGIN_PATH . 'files', 0755);
	@chmod(duplicator_safe_path(DUPLICATOR_PLUGIN_PATH . 'files/installer.php'), 0644);
	
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