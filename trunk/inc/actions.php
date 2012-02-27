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
	$packname = isset($_POST['package_name']) ? trim($_POST['package_name']) : null;

	duplicator_log("*********************************************************");
	duplicator_log("START PROCESSING");
	duplicator_log("duplicator:" . DUPLICATOR_VERSION . " | wp:{$wp_version} | php:" .  phpversion());
	duplicator_log("server:{$_SERVER['SERVER_SOFTWARE']}");
	duplicator_log("browser:{$_SERVER['HTTP_USER_AGENT']}");
	duplicator_log("package:{$packname}");
	
	if($packname) {
		
		$max_time   = @ini_set("max_execution_time", $GLOBALS['duplicator_opts']['max_time']); 
		$max_memory = @ini_set('memory_limit', $GLOBALS['duplicator_opts']['max_memory']);
		$max_time   = ($max_time == false)   ? "Unabled to set max_execution_time" : $GLOBALS['duplicator_opts']['max_time'];
		$max_memory = ($max_memory == false) ? "Unabled to set memory_limit"		: $GLOBALS['duplicator_opts']['max_memory'];
		duplicator_log("max_time:{$max_time} | max_memory:{$max_memory}");
			
		global $wpdb;
		global $current_user;

		$secure_token = uniqid();
		$uniquename   = $packname . '_' .  $secure_token;
		$zipfilename  = "{$uniquename}.zip";
		$temp_path 	  = DUPLICATOR_SSDIR_PATH . "/{$uniquename}";
		$zipsize 	  = 0;
		
		duplicator_log("mysql wait_timeout: {$GLOBALS['duplicator_opts']['max_time']}");
		$wpdb->query("SET session wait_timeout = {$GLOBALS['duplicator_opts']['max_time']}");
		
		
		//TEMPORARY FILE BACKUP
		//Very important to remove trailing slash for copy function to work
		duplicator_log("*********************************************************");
		duplicator_log("TEMPORARY FILE BACKUP");
		
		duplicator_create_snapshotpath();
		if(!file_exists($temp_path)) {
			if (!mkdir($temp_path, 0755)) {
				die(duplicator_log("Unable to create temporary snapshot directory '$temp_path' "));
			}
		}
		
		duplicator_log("log:act__create=>temp backup to: " . $temp_path, 1);
		duplicator_full_copy(rtrim(DUPLICATOR_WPROOTPATH, "/\\") ,$temp_path);
		duplicator_log("log:act__create=>temp backup complete.", 1);
		
		//SQL SCRIPT
		duplicator_log("*********************************************************");
		duplicator_log("SQL SCRIPT");
		duplicator_create_dbscript($temp_path);
		duplicator_log("log:act__create=>sql script complete.", 2);		
		
		
		//CREATE ZIP ARCHIVE
		duplicator_log("*********************************************************");
		duplicator_log("ZIP ARCHIVE");
		$zip = new Duplicator_Zip("{$temp_path}.zip", $temp_path, '.svn');
		$zipsize = filesize("{$temp_path}.zip");
		if ($zipsize == false) {
			duplicator_log("log:act__create=>warning: zipsize is unknown.");
		} else {
			duplicator_log("log:act__create=>zip file size is: " . $zipsize);
		}
		duplicator_log("log:act__create=>zip archive complete.", 2);
		
		//Serlized settings
		$settings = array('plugin_version' => DUPLICATOR_VERSION, 
						  'secure_token'   => $secure_token,
						  'type' 		   => 'Manual');
		$serialized_settings = serialize($settings);
		
		//Record archive info to database
		 $results = $wpdb->insert($wpdb->prefix . "duplicator", 
						array('packname' => $packname, 
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
		
		
		//REMOVE TEMPORARY BACKUP
		duplicator_log("*********************************************************");
		duplicator_log("REMOVE TEMPORARY BACKUP");
		duplicator_log("log:act__create=>remove backup temp data from: " . $temp_path, 2);
		duplicator_delete_all($temp_path, true);
		duplicator_log("log:act__create=>backup temp data removed.", 2);
		
	
		//UPDATE INSTALL FILE
		duplicator_log("*********************************************************");
		duplicator_log("UPDATE INSTALLER FILE");
		duplicator_create_installerFile($zipfilename);


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
			duplicator_log("log:act__create=>email finished");
		}

	} 
	
	duplicator_log("*********************************************************");
	duplicator_log("DONE PROCESSING => {$packname}");
	die(duplicator_log("*********************************************************"));
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
		$id = isset($_POST['duplicator_delid']) ? trim($_POST['duplicator_delid']) : null;
		if ($id != null) {
			$arr = explode(",", $id);
			foreach ($arr as $val) {
				$path = DUPLICATOR_SSDIR_PATH . '/' . $val;
				$msg  = duplicator_unlink($val, $path);
			}
		}
		die(duplicator_log($msg));
	}  
	catch(Exception $e) 
	{
		duplicator_log("log:fun__delete=>runtime error: " . $e);
	}
}


/**
 *  DUPLICATOR_SYSTEM_CHECK
 *  Check to see if the package already exsits
 *  
 *  @return string   A message about the action
 *		- log:act__system_check=>create new package
 *		- log:act__system_check=>overwrite
 */
function duplicator_system_check() {
	global $wpdb;
	
	$dirSize = duplicator_dirSize(DUPLICATOR_WPROOTPATH);
	$dirSizeFormat = duplicator_bytesize($dirSize);
	duplicator_log("log:act__system_check=>content_size: <input type='hidden' class='dir-size' value='{$dirSizeFormat}' />{$dirSizeFormat}");
	
	//TODO: Find a way around the 2GB limit
	if ($dirSize >  2100000000) {
		die(duplicator_log("log:act__system_check=>size_limit"));
	}
	
	$new_name = isset($_POST['duplicator_new']) ? trim($_POST['duplicator_new']).'.zip' : null;
	$ajax_msg = "log:act__system_check=>create new package";
	$table = $wpdb->prefix . 'duplicator';
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$table}` WHERE zipname= '".$new_name."'"));
	if($count > 0) {
		$ajax_msg="log:act__system_check=>overwrite";
	}
	
	//Check for reserved files
	$phpFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP) ? DUPLICATOR_INSTALL_PHP : "";
	$sqlFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL) ? DUPLICATOR_INSTALL_SQL : "";
	$logFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG) ? DUPLICATOR_INSTALL_LOG : "";
	
	if(strlen($phpFile) || strlen($sqlFile ) || strlen($logFile) ) {
		$ajax_msg = "log:act__system_check=>reserved-file " . $phpFile  ." ". $sqlFile ." ". $logFile . " found in root directory";
	}

	die(duplicator_log($ajax_msg));
}


/**
 *  DUPLICATOR_OVERWRITE
 *  Performs an overwrite of the package
 *  
 *  @return string   A message about the action
 *		- see: duplicator_unlink
 */
function duplicator_overwrite(){
	try 
	{
		$file_name = isset($_POST['duplicator_new']) ? trim($_POST['duplicator_new']) . '.zip' : null;
		$file_path = DUPLICATOR_SSDIR_PATH . '/' . $file_name;
		$msg  = duplicator_unlink($file_name, $file_path);
		die(duplicator_log($msg));
	}  
	catch(Exception $e) 
	{
		duplicator_log("log:fun__overwrite=>runtime error: " . $e);
	}
}


/**
 *  DUPLICATOR_UNLINK
 *  Performs an overwrite of the package
 *  
 *  @param string $file	 	The file name of the file to delete.
 *  @param string $path		The full path and file name of the file to delete
 *
 *  @return string   A message about the action
 *		- log:act__unlink=>removed
 *		- log:act__unlink=>error
 */
function duplicator_unlink($file, $path) {
	try
	{
		$msg = "log:act__unlink=>error";
		if($file && $path) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'duplicator';
			if($wpdb->query("DELETE FROM $table_name WHERE zipname= '".$file."'" ) != 0) {
				$msg = "log:act__unlink=>removed";
				try {
					@unlink($path);
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
		duplicator_log("log:fun__unlink=>runtime error: " . $e);
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
		'max_time'		=>$_POST['max_time'],
		'max_memory'	=>preg_replace('/\D/', '', $maxmem) . 'M',
		'dir_bypass'	=>$by_pass_clean,
		'log_level'			=>$_POST['log_level'],
		'log_paneheight'	=>$_POST['log_paneheight']);
		

	update_option('duplicator_options', serialize($duplicator_opts));
	
	die(duplicator_log("log:act__settings=>saved"));
}
//DO NOT ADD A CARRIAGE RETURN BEYOND THIS POINT (headers issue)!!
?>