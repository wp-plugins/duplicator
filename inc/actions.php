<?php

/**
 *  DUPLICATOR_CREATE
 *  Creates the zip file, database entry, and installer file for the
 *  new culmination of a 'Package Set'  
 *
 *  @return string   A message about the action
 * 		- log:act__create=>done
 */
function duplicator_create() {

    global $wp_version;
    global $wpdb;
    global $current_user;
    //post data un-stripped, as WP magic quotes _POST for some reason...
    $post = stripslashes_deep($_POST);
	
	$error_level = error_reporting();
	error_reporting(E_ERROR);

    $fulltime_start = DuplicatorUtils::GetMicrotime();
    $packname = isset($post['package_name']) ? trim($post['package_name']) : null;

    $secure_token = uniqid() . mt_rand(1000, 9999);
    $uniquename = "{$secure_token}_{$packname}";
    foreach (glob(DUPLICATOR_SSDIR_PATH . '/*.log') as $log_file) {
        @unlink($log_file);
    }

    $logfilename = "{$uniquename}.log";
    $GLOBALS['duplicator_package_log_handle'] = @fopen(DUPLICATOR_SSDIR_PATH . "/{$logfilename}", "c+");

    duplicator_log("********************************************************************************");
    duplicator_log("PACKAGE-LOG: " . @date('h:i:s'));
    duplicator_log("NOTICE: Do not post to public sites or forums");
    duplicator_log("********************************************************************************");
    duplicator_log("duplicator: " . DUPLICATOR_VERSION);
    duplicator_log("wordpress: {$wp_version}");
    duplicator_log("php: " . phpversion());
    duplicator_log("php sapi: " . php_sapi_name());
    duplicator_log("server: {$_SERVER['SERVER_SOFTWARE']}");
    duplicator_log("browser: {$_SERVER['HTTP_USER_AGENT']}");
    duplicator_log("package name: {$packname}");

    if ($packname) {

        $php_max_time = @ini_set("max_execution_time", DUPLICATOR_PHP_MAX_TIME);
        $php_max_memory = @ini_set('memory_limit', DUPLICATOR_PHP_MAX_MEMORY);
        $php_max_time = ($php_max_time === false) ? "Unabled to set php max_execution_time" : "set from={$php_max_time} to=" . DUPLICATOR_PHP_MAX_TIME;
        $php_max_memory = ($php_max_memory === false) ? "Unabled to set php memory_limit"   : "set from={$php_max_memory} to=" . DUPLICATOR_PHP_MAX_MEMORY;

        @set_time_limit(0);
        duplicator_log("php_max_time: {$php_max_time}");
        duplicator_log("php_max_memory: {$php_max_memory}");
        duplicator_log("mysql wait_timeout:" . DUPLICATOR_PHP_MAX_TIME);

        $zipfilename = "{$uniquename}_package.zip";
        $sqlfilename = "{$uniquename}_database.sql";
        $exefilename = "{$uniquename}_installer.php";

        $zipfilepath = DUPLICATOR_SSDIR_PATH . "/{$zipfilename}";
        $sqlfilepath = DUPLICATOR_SSDIR_PATH . "/{$sqlfilename}";
        $exefilepath = DUPLICATOR_SSDIR_PATH . "/{$exefilename}";
        $zipsize = 0;

        $wpdb->query("SET session wait_timeout = " . DUPLICATOR_DB_MAX_TIME);

        duplicator_log("********************************************************************************");
        duplicator_log("SQL SCRIPT");
        duplicator_log("********************************************************************************");
        duplicator_create_dbscript($sqlfilepath);


        //CREATE ZIP ARCHIVE
        duplicator_log("********************************************************************************");
        duplicator_log("ZIP ARCHIVE:");
        duplicator_log("********************************************************************************");

        $zip = new Duplicator_Zip($zipfilepath, rtrim(DUPLICATOR_WPROOTPATH, '/'), $sqlfilepath);
        $zipsize = filesize($zipfilepath);

        ($zipsize == false) ? duplicator_log("WARNING: ZIPSIZE IS UNKNOWN.") : duplicator_log("ZIP FILE SIZE: " . duplicator_bytesize($zipsize));

        //Serlized settings
        $settings = array('plugin_version' => DUPLICATOR_VERSION, 'type' => 'Manual');
        $serialized_settings = serialize($settings);
		$wpdb->flush();

        //Record archive info to database
        $results = $wpdb->insert($wpdb->prefix . "duplicator", array(
            'token' => $secure_token,
            'packname' => $packname,
            'zipname' => $zipfilename,
            'zipsize' => $zipsize,
            'created' => current_time('mysql', get_option('gmt_offset')),
            'owner' => $current_user->user_login,
            'settings' => "{$serialized_settings}")
        );
        if ($wpdb->insert_id) {
            duplicator_log("RECORDED ARCHIVE ID: " . $wpdb->insert_id);
        } else {
			$error_result = $wpdb->print_error();
            duplicator_log("WARNING: UNABLE TO RECORD TO DATABASE.  PLEASE TRY AGAIN.\n{$error_result}");
        }
       
        //UPDATE INSTALL FILE
        duplicator_log("********************************************************************************");
        duplicator_log("FINALIZATION ROUTINES:");
        duplicator_log("********************************************************************************");
        duplicator_build_installerFile();
        duplicator_create_installerFile($uniquename);

        //SEND EMAIL
        //TODO: Send only SQL File via mail.  Zip files can get too large
        if ($GLOBALS['duplicator_opts']['email-me'] == "1") {
            duplicator_log("log:act__create=>email started");
            $status = ($zipsize) ? 'Success' : 'Failure';
            $attachments = ""; //array(DUPLICATOR_SSDIR_PATH . '/' . $packname .'.zip');
            $headers = 'From: Duplicator Plugin <no-reply@lifeinthegrid.com>' . "\r\n";
            $subject = "Package '{$packname}' completed";
            $message = "Run Status: {$status}\n\rSite Name: " . get_bloginfo('name') . "\n\rPackage Name: {$packname} \n\rCompleted at: " . current_time('mysql', get_option('gmt_offset'));
            wp_mail($current_user->user_email, $subject, $message, $headers, $attachments);
            duplicator_log("log:act__create=>sent email to: {$current_user->user_email}");
            $other_emails = @preg_split("/[,;]/", $GLOBALS['duplicator_opts']['email_others']);
            if (count($other_emails)) {
                wp_mail($other_emails, $subject, $message, $headers, $attachments);
                duplicator_log("log:act__create=>other emails sent: {$GLOBALS['duplicator_opts']['email_others']}");
            }
            duplicator_log("log:act__create=>email finished");
        }
    }
    
    $fulltime_end = DuplicatorUtils::GetMicrotime();
    $fulltime_sum = DuplicatorUtils::ElapsedTime($fulltime_end, $fulltime_start);
    duplicator_log("COMPLETE PACKAGE RUNTIME: {$fulltime_sum}");

    duplicator_log("********************************************************************************");
    duplicator_log("DONE PROCESSING => {$packname} " . @date('h:i:s'));
    duplicator_log("********************************************************************************");
    @fclose($GLOBALS['duplicator_package_log_handle']);
	error_reporting($error_level);
    die();
}

/**
 *  DUPLICATOR_DELETE
 *  Deletes the zip file and database record entries for the
 *  selected ids.  Supports 1 to many deletes
 *
 *  @return string   A message about the action.  
 * 		- see: duplicator_unlink
 */
function duplicator_delete() {
	//post data un-stripped, as WP magic quotes _POST for some reason...
	$post = stripslashes_deep($_POST);
    try {
		$uniqueid = isset($post['duplicator_delid']) ? trim($post['duplicator_delid']) : null;
        if ($uniqueid != null) {
            $unique_list = explode(",", $uniqueid);
            foreach ($unique_list as $id) {
                $msg = duplicator_unlink($id);
            }
        }
        die($msg);
    } catch (Exception $e) {
        die("log:fun__delete=>runtime error: " . $e);
    }
}

/**
 *  DUPLICATOR_SYSTEM_CHECK
 *  Check to see if the package already exsits or required files
 *  are installed.  Also check for package size
 *  
 *  @return string   A message about the action
 * 		- log:act__system_check=>create new package
 * 		- log:act__system_check=>overwrite
 */
function duplicator_system_check() {
    global $wpdb;

    @set_time_limit(0);
    duplicator_init_snapshotpath();

    $json = array();

    //SYS-100: FILE PERMS
    $test = is_writeable(DUPLICATOR_WPROOTPATH)
            && is_writeable(DUPLICATOR_SSDIR_PATH)
            && is_writeable(DUPLICATOR_PLUGIN_PATH . 'files/')
            && is_writeable(DUPLICATOR_PLUGIN_PATH . 'files/installer.rescue.php');
    $json['SYS-100'] = ($test) ? 'Pass' : 'Fail';

    //SYS-101 RESERVED FILE
    $phpFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP) ? DUPLICATOR_INSTALL_PHP : "";
    $sqlFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL) ? DUPLICATOR_INSTALL_SQL : "";
    $logFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG) ? DUPLICATOR_INSTALL_LOG : "";
    $test = !(strlen($phpFile) || strlen($sqlFile) || strlen($logFile));
    $json['SYS-101'] = ($test) ? 'Pass' : 'Fail';

    //SYS-102 ZIP-ARCHIVE
    $test = class_exists('ZipArchive');
    $json['SYS-102'] = ($test) ? 'Pass' : 'Fail';

    //SYS-103 SAFE MODE
    $test = (((strtolower(@ini_get('safe_mode'))   == 'on')   
			||  (strtolower(@ini_get('safe_mode')) == 'yes') 
			||  (strtolower(@ini_get('safe_mode')) == 'true') 
			||  (ini_get("safe_mode") == 1 )));
    $json['SYS-103'] = !($test) ? 'Pass' : 'Fail';

    //SYS-104 MYSQL SUPPORT
    $mysql_test1 = function_exists('mysqli_connect');
    $mysql_test2 = version_compare($wpdb->db_version(), '4.1', '>=');
    $json['SYS-104'] = ($mysql_test1 && $mysql_test2) ? 'Pass' : 'Fail';

    //SYS-105 PHP TESTS
    $php_test1 = version_compare(phpversion(), '5.2.17');
	$php_test2 =  function_exists("file_get_contents");
	$php_test3 =  function_exists("file_put_contents");
    $json['SYS-105'] = ($php_test1 >= 0 && $php_test2 && $php_test3) ? 'Pass' : 'Fail';

    $result = in_array('Fail', $json);
    $json['Success'] = !$result;

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
    $dirSizeFormat = duplicator_bytesize($dirInfo['size']) or "0";
    $dirCountFormat = number_format($dirInfo['count']) or "unknown";
    $dirFolderFormat = number_format($dirInfo['folders']) or "unknown";

    $json['size'] = $dirSizeFormat;
    $json['count'] = $dirCountFormat;
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
 * 		- log:act__unlink=>removed
 * 		- log:act__unlink=>file not found
 */
function duplicator_unlink($uniqueid) {
    try {
        $msg = "log:act__unlink=>file not found";
        if ($uniqueid) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'duplicator';
            if ($wpdb->query("DELETE FROM {$table_name} WHERE zipname= '{$uniqueid}_package.zip'") != 0) {
                $msg = "log:act__unlink=>removed";
                try {
                    @chmod(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_package.zip"), 0644);
                    @chmod(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_database.sql"), 0644);
                    @unlink(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_package.zip"));
                    @unlink(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_database.sql"));
                    @unlink(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}_installer.php"));
                } catch (Exception $e) {
                    error_log(var_dump($e->getMessage()));
                }
                //Check for Legacy pre 0.3.1
            } else if ($wpdb->query("DELETE FROM {$table_name} WHERE zipname= '{$uniqueid}'") != 0) {
                try {
                    @chmod(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}"), 0644);
                    @unlink(duplicator_safe_path(DUPLICATOR_SSDIR_PATH . "/{$uniqueid}"));
                } catch (Exception $e) {
                    error_log(var_dump($e->getMessage()));
                }
            }
        }
        return $msg;
    } catch (Exception $e) {
        die("log:fun__unlink=>runtime error: " . $e);
    }
}

/**
 *  DUPLICATOR_TASK_SAVE
 *  Saves options associted with a packages tasks
 *  
 *  @return string   A message about the action
 * 		- log:act__settings=>saved
 */
function duplicator_task_save() {

    //defaults
    add_option('duplicator_options', '');
	//post data un-stripped, as WP magic quotes _POST for some reason...
	$post = stripslashes_deep($_POST);
    $skip_ext = str_replace(array(' ', '.'), "", $post['skip_ext']);
    $by_pass_array = explode(";", $post['dir_bypass']);
    $by_pass_clean = "";

    foreach ($by_pass_array as $val) {
        if (strlen($val) >= 2) {
            $by_pass_clean .= duplicator_safe_path(trim(rtrim($val, "/\\"))) . ";";
        }
    }

    $duplicator_opts = array(
        'dbhost' => $post['dbhost'],
        'dbname' => $post['dbname'],
        'dbuser' => $post['dbuser'],
		'ssl_admin' => $post['ssl_admin'],
		'ssl_login' => $post['ssl_login'],
		'cache_wp' => $post['cache_wp'],
		'cache_path' => $post['cache_path'],
        'url_new' => rtrim($post['url_new'], '/'),
        'email-me' => $post['email-me'],
        'email_others' => $post['email_others'],
        'skip_ext' => str_replace(",", ";", $skip_ext),
        'dir_bypass' => $by_pass_clean,
        'log_level' => $post['log_level'],
    );


    update_option('duplicator_options', serialize($duplicator_opts));

    die("log:act__task_save=>saved");
}


//DO NOT ADD A CARRIAGE RETURN BEYOND THIS POINT (headers issue)!!
?>