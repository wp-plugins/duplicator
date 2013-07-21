<?php

/**
 *  DUPLICATOR_CREATE_DBSCRIPT
 *  Create the SQL DataDump File
 *  @param string $destination		The full path and filname where the sql script will be written
 */
function duplicator_create_dbscript($destination) {
    try {

        global $wpdb;
        $time_start = DuplicatorUtils::GetMicrotime();
        $handle = fopen($destination, 'w+');
        $tables = $wpdb->get_col('SHOW TABLES');
        

        $sql_header = "/* DUPLICATOR MYSQL SCRIPT CREATED ON : " . @date("F j, Y, g:i a") . " */\n\n";
        $sql_header .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        @fwrite($handle, $sql_header);

        //BUILD CREATES:
        //All creates must be created before inserts do to foreign key constraints
        foreach ($tables as $table) {
            //$sql_del = ($GLOBALS['duplicator_opts']['dbadd_drop']) ? "DROP TABLE IF EXISTS {$table};\n\n" : "";
            //@fwrite($handle, $sql_del);
            $create = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);
            @fwrite($handle, "{$create[1]};\n\n");
        }

        //BUILD INSERTS: 
        //Create Insert in 100 row increments to better handle memory
        foreach ($tables as $table) {

            $row_count = $wpdb->get_var("SELECT Count(*) FROM `{$table}`");
            duplicator_log("{$table} ({$row_count})");

            if ($row_count > 100) {
                $row_count = ceil($row_count / 100);
            } else if ($row_count > 0) {
                $row_count = 1;
            }

            if ($row_count >= 1) {
                @fwrite($handle, "\n/* INSERT TABLE DATA: {$table} */\n");
            }

            for ($i = 0; $i < $row_count; $i++) {
                $sql = "";
                $limit = $i * 100;
                $query = "SELECT * FROM `{$table}` LIMIT {$limit}, 100";
                $rows = $wpdb->get_results($query, ARRAY_A);
                if (is_array($rows)) {
                    foreach ($rows as $row) {
                        $sql .= "INSERT INTO `{$table}` VALUES(";
                        $num_values = count($row);
                        $num_counter = 1;
                        foreach ($row as $value) {
							if ( is_null( $value ) || ! isset( $value ) ) {
								($num_values == $num_counter) 	? $sql .= 'NULL' 	: $sql .= 'NULL, ';
							} else {
								($num_values == $num_counter) 
									? $sql .= '"' . @mysql_real_escape_string($value) . '"' 
									: $sql .= '"' . @mysql_real_escape_string($value) . '", ';
							}
                            $num_counter++;
                        }
                        $sql .= ");\n";
                    }
                    @fwrite($handle, $sql);
                    duplicator_fcgi_flush();
                }
            }
        }
		
		unset($sql);
        $sql_footer = "\nSET FOREIGN_KEY_CHECKS = 1;";
        @fwrite($handle, $sql_footer);

        duplicator_log("SQL CREATED: {$destination}");
        fclose($handle);
        $wpdb->flush();
        
        $time_end = DuplicatorUtils::GetMicrotime();
        $time_sum = DuplicatorUtils::ElapsedTime($time_end, $time_start);
		
		$sql_file_size = filesize($destination);
		if ($sql_file_size <= 0) {
			duplicator_error("ERROR: SQL file generated zero bytes.  \nERROR INFO: No data was written to the sql file.  Check permission on file and parent directory at [$destination]");
		}
		duplicator_log("SQL FILE SIZE: " . duplicator_bytesize($sql_file_size));
        duplicator_log("SQL RUNTIME: {$time_sum}");
        
    } catch (Exception $e) {
		duplicator_error("ERROR: Runtime error in duplicator_create_dbscript  \nERROR INFO: '{$e}'");
    }
}

/**
 *  DUPLICATOR_BUILD_INSTALLERFILE
 *  Builds the Installer file from the contents of the files/installer directory
 */
function duplicator_build_installerFile() {

    duplicator_log("INSTALLER FILE: Build Start");

    $template_path = duplicator_safe_path(DUPLICATOR_PLUGIN_PATH . 'files/installer.template.php');
    $main_path     = duplicator_safe_path(DUPLICATOR_PLUGIN_PATH . 'files/installer/main.installer.php');
    @chmod($template_path, 0777);
    @chmod($main_path, 0777);

    $main_data = file_get_contents("{$main_path}");
    $template_result = file_put_contents($template_path, $main_data);
    if ($main_data === false || $template_result == false) {
		$err_info ="These files may have permission issues. Please validate that PHP has read/write access.\nMain Installer=<{$main_path}> \nTemplate Installer=<$template_path>";
		duplicator_error("ERROR: Install builder failed to generate files.  \nERROR INFO: {$err_info}");
    }

    $embeded_files = array(
        "inc.utils.php" => "@@INC.UTILS.PHP@@",
        "ajax.step1.php" => "@@AJAX.STEP1.PHP@@",
        "ajax.step2.php" => "@@AJAX.STEP2.PHP@@",
        "inc.style.css" => "@@INC.STYLE.CSS@@",
        "inc.scripts.js" => "@@INC.SCRIPTS.JS@@",
        "view.step1.php" => "@@VIEW.STEP1.PHP@@",
        "view.step2.php" => "@@VIEW.STEP2.PHP@@",
        "view.step3.php" => "@@VIEW.STEP3.PHP@@");

    foreach ($embeded_files as $name => $token) {
        $file_path = DUPLICATOR_PLUGIN_PATH . "files/installer/${name}";
        @chmod($file_path, 0777);

        $search_data = @file_get_contents($template_path);
        $insert_data = @file_get_contents($file_path);
        file_put_contents($template_path, str_replace("${token}", "{$insert_data}", $search_data));
        if ($search_data === false || $insert_data == false) {
			duplicator_error("ERROR: Installer generation failed at {$token}.");
        }
        @chmod($file_path, 0644);
    }

    @chmod($template_path, 0644);
    @chmod($main_path, 0644);

    duplicator_log("INSTALLER FILE: Build Finished");
}

/**
 *  DUPLICATOR_CREATE_INSTALLERFILE
 *  Prep the Installer file for use. use %string% token for replacing 
 *  @param string $uniquename	The unique name this installer file will be associated with
 */
function duplicator_create_installerFile($uniquename, $table_id) {

    duplicator_log("INSTALLER FILE: Preping for use");

    global $wpdb;
	
	$result		  = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}duplicator` WHERE ID = {$table_id}", ARRAY_A);
	$result_row	  = $result[0];
	$settings	  = unserialize($result_row['settings']);
	$notes		  = empty($settings['notes']) ? 'No notes found for package.' : $settings['notes'] ;
	
    $template = duplicator_safe_path(DUPLICATOR_PLUGIN_PATH . 'files/installer.template.php');
    $installerCore = duplicator_safe_path(DUPLICATOR_SSDIR_PATH) . "/{$uniquename}_installer.php";

    $err_msg = "ERROR: Unable to read/write installer.  \nERROR INFO: Please check permission and owner on file and parent folder. \nInstaller File=<{$installerCore}>";

    get_option('duplicator_options') == "" ? "" : $duplicator_opts = unserialize(get_option('duplicator_options'));
	$replace_items = Array(
        "fwrite_url_old" => get_option('siteurl'),
        "fwrite_package_name" => "{$uniquename}_package.zip",
		"fwrite_package_notes" => $notes,
        "fwrite_secure_name" => "{$uniquename}",
        "fwrite_url_new" => $duplicator_opts['url_new'],
        "fwrite_dbhost" => $duplicator_opts['dbhost'],
        "fwrite_dbname" => $duplicator_opts['dbname'],
        "fwrite_dbuser" => $duplicator_opts['dbuser'],
        "fwrite_dbpass" => '',
		"fwrite_ssl_admin" => $duplicator_opts['ssl_admin'],
		"fwrite_ssl_login" => $duplicator_opts['ssl_login'],
		"fwrite_cache_wp" => $duplicator_opts['cache_wp'],
		"fwrite_cache_path" => $duplicator_opts['cache_path'],
        "fwrite_wp_tableprefix" => $wpdb->prefix,
        "fwrite_blogname" => @addslashes(get_option('blogname')),
        "fwrite_wproot" => DUPLICATOR_WPROOTPATH,
		"fwrite_duplicator_version" => DUPLICATOR_VERSION);
	
    if (file_exists($template) && is_readable($template)) {

        $install_str = duplicator_parse_template($template, $replace_items);
        if (empty($install_str)) {
            duplicator_error("{$err_msg} \n duplicator_create_installerFile=>file-empty-read");
        }

        //INSTALLER FILE
        if (!file_exists($installerCore)) {
            $fp2 = fopen($installerCore, 'x+') or duplicator_error("{$err_msg} \n duplicator_create_installerFile=>file-open-error-x");;
        } else {
            $fp2 = fopen($installerCore, 'w')  or duplicator_error("{$err_msg} \n duplicator_create_installerFile=>file-open-error-w");;
        }

        if (! fwrite($fp2, $install_str, strlen($install_str))) {
            duplicator_error("{$err_msg} \n duplicator_create_installerFile=>file-write-error");
        }

        @fclose($fp2);
    } else {
        duplicator_error("ERROR: Installer Template missing or unreadable. \nERROR INFO: Template [{$template}]");
    }

    duplicator_log("INSTALLER FILE: Complete [{$installerCore}]");
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
        //NOTE: Use var_export as it's probably best and most "thorough" way to
        //make sure the values are set correctly in the template.  But in the template,
        //need to make things properly formatted so that when real syntax errors
        //exist they are easy to spot.  So the values will be surrounded by quotes
        
    	$find = array ("'%{$key}%'", "\"%{$key}%\"");
    	$q = str_replace($find, var_export($value, true), $q);
    	//now, account for places that do not surround with quotes...  these
    	//places do NOT need to use var_export as they are not inside strings
    	$q = str_replace('%' . $key . '%', $value, $q);
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
        for ($i = 0; $size >= 1024 && $i < 4; $i++)
            $size /= 1024;
        return round($size, 2) . $units[$i];
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
        if (strstr($directory, DUPLICATOR_SSDIR_PATH)) {
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
                        $size += $result['size'];
                        $count += $result['count'];
                        $folders += $result['folders'];
                    } else if (is_file($nextpath) && is_readable($nextpath)) {
                        if (!in_array(@pathinfo($nextpath, PATHINFO_EXTENSION), $GLOBALS['duplicator_skip_ext-array'])) {
                            $fmod = @filesize($nextpath);
                            if ($fmod === false) {
                                $flag = true;
                            } else {
                                $size += @filesize($nextpath);
                            }
                            $count++;
                        }
                    }
                }
            }
        }
        @closedir($handle);
        $total['size'] = $size;
        $total['count'] = $count;
        $total['folders'] = $folders;
        $total['flag'] = $flag;
        return $total;
    } catch (Exception $e) {
        duplicator_log("log:fun__dirInfo=>runtime error: " . $e . "\nNOTE: Try excluding the stat failed to the Duplicators directory exclusion list or change the permissions.");
    }
}

/**
 *  DUPLICATOR_CREATE_SNAPSHOTPATH
 *  Creates the snapshot directory if it doesn't already exisit
 */
function duplicator_init_snapshotpath() {

    $path_wproot = duplicator_safe_path(DUPLICATOR_WPROOTPATH);
    $path_ssdir = duplicator_safe_path(DUPLICATOR_SSDIR_PATH);
    $path_plugin = duplicator_safe_path(DUPLICATOR_PLUGIN_PATH);

    //--------------------------------
    //CHMOD DIRECTORY ACCESS
    //wordpress root directory
    @chmod($path_wproot, 0755);

    //snapshot directory
    @mkdir($path_ssdir, 0755);
    @chmod($path_ssdir, 0755);

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
    $tokenfile2 = @fopen($path_plugin . 'files/dtoken.php', 'w');
    @fwrite($tokenfile2, '<?php @error_reporting(0); @require_once("../../../../wp-admin/admin.php"); global $wp_query; $wp_query->set_404(); header("HTML/1.1 404 Not Found", true, 404); header("Status: 404 Not Found"); @include(get_template_directory () . "/404.php"); ?>');
    @fclose($tokenfile2);
}

/**
 *  DUPLICATOR_SAFE_PATH
 *  Makes path safe for any OS
 *  Paths should ALWAYS READ be "/"
 * 		uni: /home/path/file.xt
 * 		win:  D:/home/path/file.txt 
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
    echo(str_repeat(' ', 265));
    @flush();
}

/**
 *  DUPLICATOR_SNAPSHOT_URLPATH
 * 	returns the snapshot url
 */
function duplicator_snapshot_urlpath() {
    return get_site_url(null, '', is_ssl() ? 'https' : 'http') . '/' . DUPLICATOR_SSDIR_NAME . '/';
}

/**
 *  DUPLICATOR_RUN_APC
 *  Runs the APC cache to pre-cache the php files
 *  returns true if all files where cached
 */
function duplicator_run_apc() {
	if(function_exists('apc_compile_file')){
		$file01 = @apc_compile_file(DUPLICATOR_PLUGIN_PATH . "duplicator.php");
		return ($file01);
	} else {
		return false;
	}
}

/**
 *  DUPLICATOR_LOG
 *  Centralized logging method
 *  @param string $msg		The message to log
 */
function duplicator_log($msg) {
	@fwrite($GLOBALS['duplicator_package_log_handle'], "{$msg} \n"); 
}

/**
 *  DUPLICATOR_ERROR
 *  Centralized logging for errors
 *  @param string $msg		The message to log
 */
function duplicator_error($msg) {
	$err_msg  = "--------------------------------------------------------------------------------\n";
	$err_msg .= "{$msg}\n";
	$err_msg .= "Please try the process again. If the error persists please visit the Online FAQs\n";
	$err_msg .= "--------------------------------------------------------------------------------";
	@fwrite($GLOBALS['duplicator_package_log_handle'], "\n{$err_msg}"); 
	die();
}
?>