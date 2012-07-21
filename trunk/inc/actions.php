<?php
/**
 *  DUPLICATOR_CREATE
 *  Creates the zip file, database entry, and installer file for the
 *  new culmination of a 'Package Set'
 *
 *  @return string   A message about the action
 *		- log:act__create=>done
 */
function duplicator_create() {

	global $wp_version;
	global $wpdb;
	global $current_user;
	
	$packname = isset($_POST['package_name']) ? trim($_POST['package_name']) : null;
	
	$secure_token  = uniqid() . mt_rand(1000, 9999);
	$uniquename    = "{$secure_token}_{$packname}";
	foreach(glob(DUPLICATOR_SSDIR_PATH . '/*.log') as $log_file){
		@unlink($log_file);
	}
	
	$logfilename = "{$uniquename}.log";
	$GLOBALS['duplicator_package_log_handle'] = @fopen(DUPLICATOR_SSDIR_PATH . "/{$logfilename}", "c+");
	
	duplicator_log("*********************************************************");
	duplicator_log("PACKAGE-LOG: ");
	duplicator_log("*********************************************************");
	duplicator_log("duplicator: " . DUPLICATOR_VERSION);
	duplicator_log("wordpress: {$wp_version}");
	duplicator_log("php: " .  phpversion());
	duplicator_log("php sapi: " .  php_sapi_name());
	duplicator_log("server: {$_SERVER['SERVER_SOFTWARE']}");
	duplicator_log("browser: {$_SERVER['HTTP_USER_AGENT']}");
	duplicator_log("package name: {$packname}");

	if($packname) {
		
		$max_time   = @ini_set("max_execution_time", $GLOBALS['duplicator_opts']['max_time']); 
		$max_memory = @ini_set('memory_limit', $GLOBALS['duplicator_opts']['max_memory']);
		
		$max_time   = ($max_time === false)   ? "Unabled to set max_execution_time"  : "from={$max_time} to={$GLOBALS['duplicator_opts']['max_time']}";
		$max_memory = ($max_memory === false) ? "Unabled to set memory_limit"		 : "from={$max_memory} to={$GLOBALS['duplicator_opts']['max_memory']}";
		
		@set_time_limit(0);
		duplicator_log("max_time: {$max_time}");
		duplicator_log("max_memory: {$max_memory}");

		$zipfilename  = "{$uniquename}_package.zip";
		$sqlfilename  = "{$uniquename}_database.sql";
		$exefilename  = "{$uniquename}_installer.php";
		
		$zipfilepath  = DUPLICATOR_SSDIR_PATH . "/{$zipfilename}";
		$sqlfilepath  = DUPLICATOR_SSDIR_PATH . "/{$sqlfilename}";
		$exefilepath  = DUPLICATOR_SSDIR_PATH . "/{$exefilename}";
		$zipsize 	  = 0;
		
		duplicator_log("mysql wait_timeout: {$GLOBALS['duplicator_opts']['max_time']}");
		$wpdb->query("SET session wait_timeout = {$GLOBALS['duplicator_opts']['max_time']}");
		

		duplicator_log("*********************************************************");
		duplicator_log("SQL SCRIPT");
		duplicator_log("*********************************************************");
		duplicator_create_dbscript($sqlfilepath);		
		
		
		//CREATE ZIP ARCHIVE
		duplicator_log("*********************************************************");
		duplicator_log("ZIP ARCHIVE");
		duplicator_log("*********************************************************");
		$zip = new Duplicator_Zip($zipfilepath, DUPLICATOR_WPROOTPATH, '.svn', $sqlfilepath);
		$zipsize = filesize($zipfilepath);
			
		($zipsize == false) 
			? duplicator_log("log:act__create=>warning: zipsize is unknown.")
			: duplicator_log("log:act__create=>zip file size is: " . duplicator_bytesize($zipsize));
	
		duplicator_log("log:act__create=>zip archive complete.", 2);
		
		//Serlized settings
		$settings = array('plugin_version' => DUPLICATOR_VERSION,
						  'type' 		   => 'Manual');
		$serialized_settings = serialize($settings);
		
		//Record archive info to database
		 $results = $wpdb->insert($wpdb->prefix . "duplicator", 
						array(
						      'token'    => $secure_token, 
							  'packname' => $packname, 
							  'zipname'  => $zipfilename, 
							  'zipsize'  => $zipsize, 
							  'created'  => current_time('mysql', get_option('gmt_offset')),
							  'owner'    => $current_user->user_login,
							  'settings' => "{$serialized_settings}") 
						);
						
		if ($wpdb->insert_id) {
			duplicator_log("log:act__create=>recorded archieve id: " . $wpdb->insert_id);
		} else {
			duplicator_log("log:act__create=>unable to record to database.");
		}
		$wpdb->flush();
			
	
		//UPDATE INSTALL FILE
		duplicator_log("*********************************************************");
		duplicator_log("UPDATE INSTALLER FILE");
		duplicator_log("*********************************************************");
		duplicator_create_installerFile($uniquename);

		//SEND EMAIL
		//TODO: Send only SQL File via mail.  Zip files can get too large
		if( $GLOBALS['duplicator_opts']['email-me'] == "1" ) {
			duplicator_log("log:act__create=>email started");
			$status      = ($zipsize) ? 'Success' : 'Failure';
			$attachments = ""; //array(DUPLICATOR_SSDIR_PATH . '/' . $packname .'.zip');
			$headers = 'From: Duplicator Plugin <no-reply@lifeinthegrid.com>' ."\r\n";
			$subject = "Package '{$packname}' completed";
			$message = "Run Status: {$status}\n\rSite Name: " . get_bloginfo('name') . "\n\rPackage Name: {$packname} \n\rCompleted at: " .current_time('mysql',get_option('gmt_offset'))  ;
			wp_mail($current_user->user_email, $subject, $message, $headers, $attachments);
			duplicator_log("log:act__create=>sent email to: {$current_user->user_email}");
			$other_emails = @preg_split("/[,;]/",  $GLOBALS['duplicator_opts']['email_others']);
			if (count($other_emails)) {
				wp_mail($other_emails, $subject, $message, $headers, $attachments);
				duplicator_log("log:act__create=>other emails sent: {$GLOBALS['duplicator_opts']['email_others']}");
			}
			duplicator_log("log:act__create=>email finished");
		}

	} 
	
	duplicator_log("*********************************************************");
	duplicator_log("DONE PROCESSING => {$packname}");
	duplicator_log("*********************************************************");
	@fclose($GLOBALS['duplicator_package_log_handle']);
	die();
}


/**
 *  DUPLICATOR_DELETE
 *  Deletes the zip file and database record entries for the
 *  selected ids.  Supports 1 to many deletes
 *
 *  @return string   A message about the action.  
 *		- see: duplicator_unlink
 */
function duplicator_delete() {
	try
	{
		$uniqueid = isset($_POST['duplicator_delid']) ? trim($_POST['duplicator_delid']) : null;
		if ($uniqueid != null) {
			$unique_list = explode(",", $uniqueid);
			foreach ($unique_list as $id) {
				$msg  = duplicator_unlink($id);
			}
		}
		die($msg);
	}  
	catch(Exception $e) 
	{
		die("log:fun__delete=>runtime error: " . $e);
	}
}


/**
 *  DUPLICATOR_SYSTEM_CHECK
 *  Check to see if the package already exsits or required files
 *  are installed.  Also check for package size
 *  
 *  @return string   A message about the action
 *		- log:act__system_check=>create new package
 *		- log:act__system_check=>overwrite
 */
function duplicator_system_check() {
	global $wpdb;
	
	@set_time_limit($GLOBALS['duplicator_opts']['max_time']);
	duplicator_init_snapshotpath();
	
	$json = array();
		
	//SYS-100: FILE PERMS
	$test = is_readable(DUPLICATOR_WPROOTPATH)
			&& is_writeable(DUPLICATOR_SSDIR_PATH)
			&& is_writeable(DUPLICATOR_PLUGIN_PATH . 'files/')
			&& is_writeable(DUPLICATOR_PLUGIN_PATH . 'files/installer.php');
	$json['SYS-100'] = ($test) ? 'Pass' : 'Fail';

	//SYS-101 RESERVED FILE
	$phpFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP) ? DUPLICATOR_INSTALL_PHP : "";
	$sqlFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL) ? DUPLICATOR_INSTALL_SQL : "";
	$logFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG) ? DUPLICATOR_INSTALL_LOG : "";
	$test = ! (strlen($phpFile) || strlen($sqlFile ) || strlen($logFile));
	$json['SYS-101'] = ($test) ? 'Pass' : 'Fail';

	//SYS-102 ZIP-ARCHIVE
	$test = class_exists('ZipArchive');
	$json['SYS-102'] = ($test) ? 'Pass' : 'Fail';
	
	//SYS-103 SAFE MODE
	$test = ini_get('safe_mode');;
	$json['SYS-103'] = ! ($test) ? 'Pass' : 'Fail';
	
	//SYS-104 MYSQLI SUPPORT
	$test = function_exists('mysqli_connect');
	$json['SYS-104'] = ($test) ? 'Pass' : 'Fail';

	$result = in_array('Fail', $json);
	$json['Success'] = ! $result;
	
	die(json_encode($json));
}


/**
 *  DUPLICATOR_SYSTEM_DIRECTORY
 *  Returns the directory size and file count for the root directory minus
 *  any of the filters
 *  
 *  @return json   size and file count of directory
 */
function duplicator_system_directory() {

	$json = array();
	$dirInfo = duplicator_dirInfo(rtrim(duplicator_safe_path(DUPLICATOR_WPROOTPATH), '/'));
	$dirSizeFormat   = duplicator_bytesize($dirInfo['size']) or "0";
	$dirCountFormat  = number_format($dirInfo['count']) or "unknown";
	$dirFolderFormat = number_format($dirInfo['folders']) or "unknown";

	$json['size']    = $dirSizeFormat;
	$json['count']   = $dirCountFormat;
	$json['folders'] = $dirFolderFormat;
	$json['flag'] = $dirInfo['flag'];

	die(json_encode($json));
}

/**
 *  DUPLICATOR_UNLINK
 *  Removes the package
 *  
 *  @param string $file	 	The file name of the file to delete.
 *  @param string $path		The full path and file name of the file to delete
 *
 *  @return string   A message about the action
 *		- log:act__unlink=>removed
 *		- log:act__unlink=>file not found
 */
function duplicator_unlink($uniqueid) {
	try
	{
		$msg = "log:act__unlink=>file not found";
		if($uniqueid) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'duplicator';
			if($wpdb->query("DELETE FROM {$table_name} WHERE zipname= '{$uniqueid}_package.zip'" ) != 0) {
				$msg = "log:act__unlink=>removed";
				try {
					@chmod(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_package.zip"), 0644);
				    @chmod(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_database.sql"), 0644);
					@unlink(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_package.zip"));
					@unlink(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_database.sql"));
				}
				catch(Exception $e) {
					error_log(var_dump($e->getMessage()));
				}
			}
		}
		return $msg;
	}  
	catch(Exception $e) 
	{
		die("log:fun__unlink=>runtime error: " . $e);
	}	
}


/**
 *  DUPLICATOR_SETTINGS
 *  Saves plugin settings
 *  
 *  @return string   A message about the action
 *		- log:act__settings=>saved
 */
function duplicator_settings(){

	//defaults
	add_option('duplicator_options', '');
	
	$by_pass_array = explode(";", $_POST['dir_bypass']);
	$by_pass_clean = "";
	
	foreach ($by_pass_array as $val) {
		if (strlen($val) >= 2) {
			$by_pass_clean .= duplicator_safe_path(trim(rtrim($val, "/\\"))) . ";";
		}
	}

	if (is_numeric($_POST['max_memory'])) {
		$maxmem = $_POST['max_memory'] < 256 ? 256 : $_POST['max_memory'];
	} else {
		$maxmem = 256;
	}
	

	$duplicator_opts = array(
		'dbhost'		=>$_POST['dbhost'],
		'dbname'		=>$_POST['dbname'],
		'dbuser'		=>$_POST['dbuser'],
		'dbiconv'		=>$_POST['dbiconv'],
		'nurl'			=>rtrim($_POST['nurl'], '/'),
		'email-me'		=>$_POST['email-me'],
		'email_others'	=>$_POST['email_others'],
		'max_time'		=>$_POST['max_time'],
		'max_memory'	=>preg_replace('/\D/', '', $maxmem) . 'M',
		'dir_bypass'	=>$by_pass_clean,
		'log_level'		=>$_POST['log_level']);
		

	update_option('duplicator_options', serialize($duplicator_opts));
	
	die("log:act__settings=>saved");
}
//DO NOT ADD A CARRIAGE RETURN BEYOND THIS POINT (headers issue)!!
?>