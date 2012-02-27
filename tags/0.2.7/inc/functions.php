<?php

/**
 *  duplicator_create_dbscript
 *  Create the SQL DataDump File
 *
 *  @param string $destination		Where will the file be written to
 */
function duplicator_create_dbscript($destination) {
	try {
		duplicator_log("log:fun__create_dbscript=>started");
		
		global $wpdb;
		$tables =  $wpdb->get_col('SHOW TABLES');
		$dbiconv = $GLOBALS['duplicator_opts']['dbiconv'] == "0" ? false : true;
		$return = "";
		
		//CREATE TABLES
		//PERFORM ICONV
		if ($dbiconv && function_exists("iconv")) {
			duplicator_log("log:fun__create_dbscript=>dbiconv enabled");
			foreach($tables as $table) {
				duplicator_log("log:fun__create_dbscript=>creating table: $table", 2);
				$result 	 = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_N);
				$num_fields  = count(@$result[0]);   
				$items		 = count($result);
				$return		.= '';
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
			}
		//DO NOT PERFORM ICONV
		} else {
			foreach($tables as $table) {
				duplicator_log("log:fun__create_dbscript=>creating table: $table", 2);
				$result 	 = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_N);
				$num_fields  = count($result[0]);   
				$items		 = count($result);
				$return		.= '';
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
			}
		}

		$handle = fopen($destination.'/database.sql','w+');
		fwrite($handle,$return);
		duplicator_log("log:fun__create_dbscript=>database.sql created", 2);
		fclose($handle);
		$wpdb->flush();
		$return = '';
		
		duplicator_log("log:fun__create_dbscript=>ended");
	
	} catch(Exception $e) {
		duplicator_log("log:fun__create_dbscript=>runtime error: " . $e);
	}
}


/**
 *  DUPLICATOR_FULL_COPY
 *  Recursively copy all files except for the wp-snapshot file
 *
 *  @param string $source		The path to backup
 *  @param string $target		Where the source will be written to
 */
function duplicator_full_copy( $source, $target ) {
  if (is_readable($source)) {
	if ( is_dir( $source ) ) {
		
		//Directory Exclusions
		if ($GLOBALS['duplicator_bypass-array'] != null) {
			foreach ($GLOBALS['duplicator_bypass-array'] as $val) {
				if (duplicator_safe_path($source) == $val) {
					duplicator_log("directory exclusion found: {$val}", 2);
					return;
				}
			}
		}

		if( ! strstr(duplicator_safe_path($source), DUPLICATOR_SSDIR_PATH) ) {
			@mkdir( $target );
			$d = dir( $source );
			while ( FALSE !== ( $entry = $d->read() ) ) {
				if ( $entry == '.' || $entry == '..' ) {
					continue;
				}
				$Entry = "{$source}/{$entry}";
				if ( is_dir( $Entry ) ) {
					duplicator_full_copy($Entry,  $target . '/' . $entry );
					continue;
				}
				@copy( $Entry, $target . '/' . $entry );
			}
			$d->close();
		}

	} else {
		copy( $source, $target );
	}
  }
}

/**
 *  DUPLICATOR_DELETE_ALL
 *  Recursively delete all files/directories
 *
 *  @param string $directory	The path to delete
 *  @param string $empty		Delete the topmost directory
 */
function duplicator_delete_all($directory, $empty = false) {
	try {
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), RecursiveIteratorIterator::CHILD_FIRST);
		//duplicator_log("log:fun__delete_all=>exclusion list: " . implode(";", $GLOBALS['duplicator_bypass-array']), 2);
		foreach ($iterator as $path) {
			if ($path->isDir()) {
				@rmdir($path->__toString());
			} else {
				@unlink($path->__toString());
			}
		}
		
		if($empty == true) {
			if(! @rmdir($directory)) {
				return false;
			}
		}	
		return true;
	
	} catch(Exception $e) {
		duplicator_log("log:fun__delete_all=>runtime error: " . $e);
	}
}

/**
 *  DUPLICATOR_CREATE_INSTALLERFILE
 *  Prep the Installer file for use. use %string% token for replacing 
 *
 *  @param string $packagename	The package name this installer file will be associated with
 */
function duplicator_create_installerFile($packagename) {

	duplicator_log("log:fun__create_installerFile=>started");
	
	global $wpdb;
	$template 	= duplicator_safe_path(DUPLICATOR_PLUGIN_PATH . 'files/installer.template.php');
	$installer	= duplicator_safe_path(DUPLICATOR_PLUGIN_PATH . 'files/install.php');
	
	get_option('duplicator_options') == ""  ? "" : $duplicator_opts = unserialize(get_option('duplicator_options'));
	$replace_items = Array(
		"current_url" 		=> get_option('siteurl'),
		"package_name"  	=> $packagename,
		"nurl" 				=> $duplicator_opts['nurl'],
		"dbhost" 			=> $duplicator_opts['dbhost'],
		"dbname" 			=> $duplicator_opts['dbname'],
		"dbuser" 			=> $duplicator_opts['dbuser'],
		"wp_tableprefix" 	=> $wpdb->prefix,
		"site_title"		=> get_option('blogname'));
		
	if( file_exists($template)) {
		$str = duplicator_parse_template($template, $replace_items);
		$fp = fopen($installer, 'w') 
			or die(duplicator_log("log:fun__create_installerFile=>file-create-error"));
		fwrite($fp, $str, strlen($str));
		fclose($fp);
		duplicator_log("log:fun__create_installerFile=>install.php updated at: {$installer}");
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
 *
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
 *
 *  @param string $directory		The directory to calculate
 */
function duplicator_dirSize($directory) { 
	try {
	
		$size     = 0; 
		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory), 
								RecursiveIteratorIterator::LEAVES_ONLY,
								RecursiveIteratorIterator::CATCH_GET_CHILD);
			
		//App Directory Filter & Backup Directory Filter
		if ($GLOBALS['duplicator_bypass-array'] != null) {
			duplicator_log("log:fun__dirSize=>exclusion list: " . implode(";", $GLOBALS['duplicator_bypass-array']), 2);
			foreach($iterator as $file){ 
				$path = duplicator_safe_path($file->getPath());
				foreach ($GLOBALS['duplicator_bypass-array'] as $val) {
					if (@strstr($path, $val) ) {
						$exclusion_found = true;
						break;
					} else {
						$exclusion_found = false;
					}
				}
				if (! $exclusion_found && ! strstr($path, DUPLICATOR_SSDIR_PATH)) {
					$size += $file->getSize();
				}
			}
		//Filter Backup Directory
		} else {
			
			foreach($iterator as $file){ 
				$path = duplicator_safe_path($file->getPath());
				if (!strstr($path , DUPLICATOR_SSDIR_PATH)) {
					$size += $file->getSize();
				} 
			} 
		}
		return $size; 
	
	}  catch(Exception $e) {
		duplicator_log("log:fun__dirSize=>runtime error: " . $e);
	}
} 

/**
 *  DUPLICATOR_LOG
 *  Centralized logging method
 *
 *  @param string $msg		The message to log
 * 
 *  LEVEL 0: pane disabled: required responses
 *	LEVEL 1: pane enabled: light general info
 *	LEVEL 2: pane enabled: detailed heavy logging
 *	LEVEL 3: pane enabled: debug all levels plus php errors
 */
function duplicator_log($msg, $level = 0) {
	if (DUPLICATOR_LOGLEVEL >= $level) {
		$stamp = date('h:i:s');
		echo "{$stamp} {$msg} <br/>\n";
	} 
}

/**
 *  DUPLICATOR_CREATE_SNAPSHOTPATH
 *  Creates the snapshot directory if it doesn't already exisit
 *
 */
function duplicator_create_snapshotpath() {
	if(!file_exists(DUPLICATOR_SSDIR_PATH.'/index.php')) {
		if(!file_exists(DUPLICATOR_SSDIR_PATH)) {
			if (! mkdir(DUPLICATOR_SSDIR_PATH , 0755)) {
				die(duplicator_log("Unable to create directory '" . DUPLICATOR_SSDIR_PATH . "'. Directory is required for snapshot generation."));
			}
			duplicator_log("log:fun__create_snapshotpath=>path created" . DUPLICATOR_SSDIR_PATH, 2);
		}
		$fh = fopen(DUPLICATOR_SSDIR_PATH.'/index.php', 'w');
		fclose($fh);
	} else {
		$perms = chmod(DUPLICATOR_SSDIR_PATH, 0755);
		$msg   = ($perms) 
			? "log:fun__create_snapshotpath=>success setting 755 for: " . DUPLICATOR_SSDIR_PATH 
			: "log:fun__create_snapshotpath=>error setting 755 for directory: " . DUPLICATOR_SSDIR_PATH;
		duplicator_log($msg, 2);
	}
}

/**
 *  DUPLICATOR_SAFE_PATH
 *  Makes path safe for any OS
 *  Paths should ALWAYS READ be "/"
 *		uni: /home/path/file.xt
 *		win:  D:/home/path/file.txt 
 *
 *  @param string $path		The path to make safe
 *
 */
function duplicator_safe_path($path) {
	return str_replace("\\", "/", $path);
}
?>