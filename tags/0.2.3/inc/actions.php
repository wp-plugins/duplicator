<?php
/**
 *  DUPLICATOR_CREATE
 *  Creates the zip file, database entry, and installer file for the
 *  new culmination of a 'Package Set'
 *
 *  @return string   A message about the action
 *		- log:act.duplicator_create=>done
 */
function duplicator_create() {

	global $wp_version;

	duplicator_log("log:act.duplicator_create=>start =====> version:" . DUPLICATOR_VERSION . "|wpversion:" . $wp_version );
	$packname = isset($_POST['package_name']) ? trim($_POST['package_name']) : null;
	
	if($packname) {
		
		duplicator_create_snapshotpath();
		ini_set("max_execution_time", $GLOBALS['duplicator_opts']['max_time']); 
		ini_set('memory_limit', $GLOBALS['duplicator_opts']['max_memory']);
			
		global $wpdb;
		global $current_user;
		get_currentuserinfo();
		$zipfilename = $packname.'.zip';
		$temp_path 	 = DUPLICATOR_SSDIR_PATH . "/{$packname}";
		

		if(!file_exists($temp_path)) {
			if (!mkdir($temp_path, 0755)) {
				die(duplicator_log("Unable to create temporary snapshot directory '$temp_path' "));
			}
		}
		
		duplicator_log("log:act.duplicator_create=>using temp directory:" . $temp_path, 2);
		
		//CREATE DBSCRIPT AND ZIP FILE
		//Very important to remove trailing slash for copy function to work
		duplicator_full_copy(rtrim(DUPLICATOR_WPROOTPATH, "/\\") ,$temp_path);
		duplicator_create_dbscript($temp_path);
		$zip = new Duplicator_Zip("{$temp_path}.zip", $temp_path, '.svn');
		duplicator_delete_all($temp_path, true);
	
		$zipsize = filesize("{$temp_path}.zip");
		if ($zipsize) {
			$wpdb->insert($wpdb->prefix . "duplicator", 
				array(	'zipname' => $zipfilename, 
						'created' => current_time('mysql', get_option('gmt_offset')),
						'owner'=>$current_user->user_login,
						'zipsize'=>$zipsize, 
						'type'=>'Manual',
						'ver_plug'=>DUPLICATOR_VERSION, 
						'ver_db'=>DUPLICATOR_DBVERSION ) );
			$wpdb->flush;
			duplicator_create_installerFile($zipfilename);
		} else {
			duplicator_log("log:act.duplicator_create=>error: zipsize is zero. unable to write to database.");
		}

		//SEND EMAIL
		//TODO: Send only SQL File via mail.  Zip files can get too large
		if( $GLOBALS['duplicator_opts']['email-me'] == "1" ) {
			duplicator_log("log:act.duplicator_create=>email started");
			$status      = ($zipsize) ? 'Success' : 'Failure';
			$attachments = ""; //array(DUPLICATOR_SSDIR_PATH . '/' . $packname .'.zip');
			$headers = 'From: Duplicator Plugin <no-reply@lifeinthegrid.com>' ."\r\n";
			$subject = "Package '{$packname}' completed";
			$message = "Run Status: {$status}\n\rSite Name: " . get_bloginfo('name') . "\n\rPackage Name: {$packname} \n\rCompleted at: " .current_time('mysql',get_option('gmt_offset'))  ;
			wp_mail($current_user->user_email, $subject, $message, $headers, $attachments);
			duplicator_log("log:act.duplicator_create=>email finished");
		}
		
	} 
	
	die(duplicator_log("log:act.duplicator_create======>end"));
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
		duplicator_log("log:fun.duplicator_delete=>runtime error: " . $e);
	}
}


/**
 *  DUPLICATOR_SYSTEM_CHECK
 *  Check to see if the package already exsits
 *  
 *  @return string   A message about the action
 *		- log:act.duplicator_system_check=>create new package
 *		- log:act.duplicator_system_check=>overwrite
 */
function duplicator_system_check() {
	global $wpdb;
	
	$dirSize = duplicator_dirSize(DUPLICATOR_WPROOTPATH);
	$dirSizeFormat = duplicator_bytesize($dirSize);
	duplicator_log("log:act.duplicator_system_check=>content_size: <input type='hidden' class='dir-size' value='{$dirSizeFormat}' />{$dirSizeFormat}");
	//TODO: Find a way around the 2GB limit
	if ($dirSize >  2100000000) {
		die(duplicator_log("log:act.duplicator_system_check=>size_limit"));
	}
	
	$new_name = isset($_POST['duplicator_new']) ? trim($_POST['duplicator_new']).'.zip' : null;
	$msg = "log:act.duplicator_system_check=>create new package";
	$table = $wpdb->prefix . 'duplicator';
	$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$table}` WHERE zipname= '".$new_name."'"));
	if($count > 0) {
		$msg="log:act.duplicator_system_check=>overwrite";
	}
	die(duplicator_log($msg));
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
		duplicator_log("log:fun.duplicator_overwrite=>runtime error: " . $e);
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
 *		- log:act.duplicator_unlink=>removed
 *		- log:act.duplicator_unlink=>error
 */
function duplicator_unlink($file, $path) {
	try
	{
		$msg = "log:act.duplicator_unlink=>error";
		if($file && $path) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'duplicator';
			if($wpdb->query("DELETE FROM $table_name WHERE zipname= '".$file."'" ) != 0) {
				$msg = "log:act.duplicator_unlink=>removed";
				try {
					unlink($path);
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
		duplicator_log("log:fun.duplicator_unlink=>runtime error: " . $e);
	}	
}


/**
 *  DUPLICATOR_SETTINGS
 *  Saves plugin settings
 *  
 *  @return string   A message about the action
 *		- log:act.duplicator_settings=>saved
 */
function duplicator_settings(){

	//defaults
	add_option('duplicator_options', '');
	
	$by_pass_array = explode(";", $_POST['dir_bypass']);
	$by_pass_clean = "";
	
	foreach ($by_pass_array as $val) {
		if (strlen($val)) {
			$by_pass_clean .= duplicator_safe_path(trim(rtrim($val, "/\\"))) . ";";
		}
	}

	if (is_numeric($_POST['max_memory'])) {
		$maxmem = $_POST['max_memory'] < 128 ? 128 : $_POST['max_memory'];
	} else {
		$maxmem = 128;
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
	
	die(duplicator_log("log:act.duplicator_settings=>saved"));
}
//DO NOT ADD A CARRIAGE RETURN BEYOND THIS POINT (headers issue)!!
?>