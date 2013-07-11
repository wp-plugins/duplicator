<?php

//====================================================================================================
//PRECHECKS - Validate and make sure to have a clean enviroment
//====================================================================================================
define('MSG_ERR_CONFIG_FOUND', '<div class="error"><b style="color:#C16C1D;">INSTALL ERROR!</b><br/> A wp-config.php already exists in this location.  This error prevents users from accidentally overwriting the wrong directories contents.  You have two options: <ul><li>Empty this root directory except for the package and installer and try again.</li><li>Delete just the wp-config.php file and try again.  This will over-write all other files in the directory.</li></ul></div>');
define('MSG_ERR_ZIPNOTFOUND', '<div class="error"><b style="color:#C16C1D;">INSTALL ERROR!</b><br/> The packaged zip file was not found. Be sure the zip package is in the same directory as the installer file.  If you are trying to reinstall a package you can copy the package from the "' . DUPLICATOR_SSDIR_NAME . '" directory back up to your root which is the same location as your installer.php file. </div>');
define('MSG_ERR_ZIPEXTRACTION', '<div class="error"><b style="color:#C16C1D;">INSTALL ERROR!</b><br/> Failed in extracting zip file. Please be sure the archive is completely downloaded. Try to extract the archive manually to make sure the file is not corrupted.  </div>');
define('MSG_ERR_ZIPMANUAL', '<div class="error"><b style="color:#C16C1D;">INSTALL ERROR!</b><br/> When choosing manual package extraction, the contents of the package must already be extracted and the wp-config.php and database.sql files must be present in the same directory as the installer.php for the process to continue.  Please manually extract the package into the current directory before continuing in manual extraction mode.  Also validate that the wp-config.php and database.sql files are present.  </div>');
define('MSG_ERR_MAKELOG', '<div class="error"><b style="color:#C16C1D;">INSTALL ERROR!</b><br/> PHP is having issues writing to the log file <b>' . DupUtil::set_safe_path($GLOBALS['CURRENT_ROOT_PATH']) . '\installer-log.txt .</b> In order for the Duplicator to proceed validate your owner/group and permission settings for PHP on this path. Try temporarily setting you permissions to 777 to see if the issue gets resolved.  If you are on a shared hosting environment please contact your hosting company and tell them you are getting errors writing files to the path above when using PHP. </div>');
define('MSG_FAIL_ZIPARCHIVE', '<div class="error"><b style="color:#C16C1D;">ZIPARCHIVE NOT INSTALLED!</b><br/> In order to extract the package.zip file the PHP ZipArchive module must be installed.  Please read the FAQ for more details.  You can still install this package but you will need to check the Manual package extraction checkbox found in the Advanced Options.  Please read the online user guide for details in performing a manual package extraction.</div>');
define('MSG_FAIL_MYSQLI_SUPPORT', '<div class="error"><b style="color:#B80000;">PHP MYSQLI NOT ENABLED!</b><br/>In order to complete an install the mysqli extension for PHP is required. If you are on a hosted server please contact your host and request that mysqli be enabled.  For more information visit: http://php.net/manual/en/mysqli.installation.php</div>');
define('MSG_FAIL_DBCONNECT', '<div class="error"><b style="color:#B80000;">DATABASE CONNECTION FAILED!</b><br/></div>');
define('MSG_FAIL_DBCONNECT_CREATE', '<div class="error"><b style="color:#B80000;">DATABASE CREATION FAILURE!</b><br/> Unable to create database "%s".<br/>  Please try creating the database manually to proceed with installation</div>');
define('MSG_FAIL_DBTRYCLEAN', '<div class="error"><b style="color:#C16C1D;">DATABASE CREATION FAILURE!</b><br/> Unable to remove all tables from database "%s".<br/>  Please remove all tables from this database and try the installation again.</div>');
define('MSG_ERR_DBCREATE', '<div class="error"><b style="color:#C16C1D;">INSTALL ERROR!</b><br/> The database "%s" does not exists.<br/>  Enable allow database creation to proceed with the installation.</div>');
define('MSG_ERR_DBCLEANCHECK', '<div class="error"><b style="color:#C16C1D;">INSTALL ERROR!</b><br/> The database "%s" has %s tables.  The Duplicator only works with an EMPTY database.  Enable the "Table Removal" checkbox to delete all tables and proceed with installation.  Please backup all your data before proceeding!<br/><br/>  Some hosting providers do not allow table removal from scripts like the Duplicator.  In this case you will need to login to your hosting providers control panel and remove the tables manually.  Please contact your hosting provider for further details. </div>');
define('MSG_OK_DBPASS', '<div class="error"><b style="color:#006E32;">CONNECTION SUCCESSFUL!</b><br/> With the parameters provided.</div>');


//POST PARAMS
$_POST['dbmake'] = (isset($_POST['dbmake']) && $_POST['dbmake'] == '1') ? true : false;
$_POST['dbclean'] = (isset($_POST['dbclean']) && $_POST['dbclean'] == '1') ? true : false;
$_POST['dbnbsp'] = (isset($_POST['dbnbsp']) && $_POST['dbnbsp'] == '1') ? true : false;
$_POST['ssl_admin'] = (isset($_POST['ssl_admin'])) ? true : false;
$_POST['ssl_login'] = (isset($_POST['ssl_login'])) ? true : false;
$_POST['cache_wp'] = (isset($_POST['cache_wp'])) ? true : false;
$_POST['cache_path'] = (isset($_POST['cache_path'])) ? true : false;
$_POST['package_name'] = isset($_POST['package_name']) ? $_POST['package_name'] : null;
$_POST['zip_manual'] = (isset($_POST['zip_manual']) && $_POST['zip_manual'] == '1') ? true : false;

//LOGGING
$POST_LOG = $_POST;
unset($POST_LOG['dbpass']);
ksort($POST_LOG);

//PAGE VARS
$back_link = "<div class='tryagain'><a href='javascript:void(0)' onclick='Duplicator.hideErrorResult()' style='color:#444; font-weight:bold'>&laquo; Try Again</a></div>";
$root_path = DupUtil::set_safe_path($GLOBALS['CURRENT_ROOT_PATH']);
$package_path = "{$root_path}/{$_POST['package_name']}";
$package_size = @filesize($package_path);
$ajax1_start = DupUtil::get_microtime();
$JSON = array();
$JSON['pass'] = 0;

/* JSON RESPONSE: Most sites have warnings turned off by default, but if they're turned on the warnings
cause errors in the JSON data Here we hide the status so warning level is reset at it at the end*/
$ajax1_error_level = error_reporting();
error_reporting(E_ERROR);

//===============================
//DATABASE TEST CONNECTION
//===============================
if (isset($_GET['dbtest'])) {

	$db_port = parse_url($_POST['dbhost'], PHP_URL_PORT);

	if (!is_null($_POST['dbname'])) {
		$dbh = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname'], $db_port);
	} else {
		$dbh = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $db_port);
	}

	if (!$dbh) {
		die(MSG_FAIL_DBCONNECT . mysqli_connect_error());
	}

	if (!$_POST['dbmake']) {
		mysqli_select_db($dbh, $_POST['dbname']) or die(sprintf(MSG_ERR_DBCREATE, $_POST['dbname']));
	}

	if (!$_POST['dbclean']) {
		$tblcount = DupUtil::dbtable_count($dbh, $_POST['dbname']);
		if ($tblcount > 0) {
			die(sprintf(MSG_ERR_DBCLEANCHECK, $_POST['dbname'], $tblcount));
		}
	}
	die(MSG_OK_DBPASS);
}

//===============================
//VALIDATION MESSAGES
//===============================
//MSG_ERR_MAKELOG
($GLOBALS['LOG_FILE_HANDLE'] != false) or die(MSG_ERR_MAKELOG . $back_link);


//MSG_FAIL_MYSQLI_SUPPORT
function_exists('mysqli_connect') or die(MSG_FAIL_MYSQLI_SUPPORT);

//MSG_FAIL_DBCONNECT
$dbh = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass']);
($dbh) or die(MSG_FAIL_DBCONNECT . mysqli_connect_error() . $back_link);
if (!$_POST['dbmake']) {
	mysqli_select_db($dbh, $_POST['dbname']) or die(sprintf(MSG_ERR_DBCREATE, $_POST['dbname']) . $back_link);
}
//MSG_ERR_DBCLEANCHECK
if (!$_POST['dbclean']) {
	$tblcount = DupUtil::dbtable_count($dbh, $_POST['dbname']);
	if ($tblcount > 0) {
		die(sprintf(MSG_ERR_DBCLEANCHECK, $_POST['dbname'], $tblcount) . $back_link);
	}
}

//MSG_ERR_ZIPMANUAL
if ($_POST['zip_manual']) {
	if (!file_exists("wp-config.php") && !file_exists("database.sql")) {
		die(MSG_ERR_ZIPMANUAL . $back_link);
	}
} else {
	//MSG_ERR_CONFIG_FOUND
	(!file_exists('wp-config.php')) or die(MSG_ERR_CONFIG_FOUND . $back_link);
	//MSG_ERR_ZIPNOTFOUND
	(is_readable("{$package_path}")) or die(MSG_ERR_ZIPNOTFOUND . $back_link);
}

DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log('DUPLICATOR INSTALL-LOG');
DupUtil::log('STEP1 START @ ' . @date('h:i:s'));
DupUtil::log('NOTICE: Do not post to public sites or forums');
DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log("VERSION:\t{$GLOBALS['FW_DUPLICATOR_VERSION']}");
DupUtil::log("PHP:\t\t" . phpversion());
DupUtil::log("PHP SAPI:\t" . php_sapi_name());
DupUtil::log("ZIPARCHIVE:\t" . var_export(class_exists('ZipArchive'), true));
DupUtil::log("SERVER:\t\t{$_SERVER['SERVER_SOFTWARE']}");
DupUtil::log("DOC ROOT:\t{$root_path}");
DupUtil::log("DOC ROOT 755:\t" . var_export($GLOBALS['CHOWN_ROOT_PATH'], true));
DupUtil::log("LOG FILE 644:\t" . var_export($GLOBALS['CHOWN_LOG_PATH'], true));
DupUtil::log("BUILD NAME:\t{$GLOBALS['FW_SECURE_NAME']}");
DupUtil::log("REQUEST URL:\t{$GLOBALS['URL_PATH']}");
DupUtil::log("--------------------------------------");
DupUtil::log("POST DATA");
DupUtil::log("--------------------------------------");
DupUtil::log(print_r($POST_LOG, true));


//====================================================================================================
//UNZIP & FILE SETUP - Extract the zip file and prep files
//====================================================================================================
DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log('UNZIP & FILE SETUP');
DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log("PACKAGE:\t" . $_POST['package_name']);
DupUtil::log("SIZE:\t\t" . DupUtil::readable_bytesize(@filesize($_POST['package_name'])));

$zip_start = DupUtil::get_microtime();

if ($_POST['zip_manual']) {
	DupUtil::log("\n-package extraction is in manual mode-\n");
} else {
	if ($GLOBALS['FW_PACKAGE_NAME'] != $_POST['package_name']) {
		DupUtil::log("WARNING: This Package Set may be incompatible!  \nBelow is a summary of the package this installer was built with and the package used. \nTo guarantee accuracy make sure the installer and package match. For more details see the online FAQs.  \ncreated with:   {$GLOBALS['FW_PACKAGE_NAME']}  \nprocessed with: {$_POST['package_name']}  \n");
	}
	
	if (! class_exists('ZipArchive')) {
		DupUtil::log("ERROR: Stopping install process.  Trying to extract without ZipArchive module installed.  Please use the 'Manual Package extraction' mode to extract zip file.");
		die(MSG_FAIL_ZIPARCHIVE . $back_link);
	}

	$target = $root_path;
	$zip = new ZipArchive();
	if ($zip->open($_POST['package_name']) === TRUE) {
		@$zip->extractTo($target);
		DupUtil::log("INFORMATION:\t" . print_r($zip, true));
		$close_response = $zip->close();
		DupUtil::log("ZIP CLOSE: " . var_export($close_response, true));
	} else {
		die(MSG_ERR_ZIPEXTRACTION . $back_link);
	}
	$zip = null;
}

//===============================
//WP-CONFIG: wp-config
//===============================
$wpconfig = @file_get_contents('wp-config.php', true);

$patterns = array(
	"/'DB_NAME',\s*'.*?'/",
	"/'DB_USER',\s*'.*?'/",
	"/'DB_PASSWORD',\s*'.*?'/",
	"/'DB_HOST',\s*'.*?'/");

$replace = array(
	"'DB_NAME', "	  . '\'' . $_POST['dbname']				. '\'',
	"'DB_USER', "	  . '\'' . $_POST['dbuser']				. '\'',
	"'DB_PASSWORD', " . '\'' . DupUtil::preg_replacement_quote($_POST['dbpass']) . '\'',
	"'DB_HOST', "	  . '\'' . $_POST['dbhost']				. '\'');

//SSL CHECKS
if ($_POST['ssl_admin']) {
	if (! strstr($wpconfig, 'FORCE_SSL_ADMIN')) {
		$wpconfig = $wpconfig . PHP_EOL . "define('FORCE_SSL_ADMIN', true);";
	}
} else {
	array_push($patterns, "/'FORCE_SSL_ADMIN',\s*true/");
	array_push($replace,  "'FORCE_SSL_ADMIN', false");
}

if ($_POST['ssl_login']) {
	if (! strstr($wpconfig, 'FORCE_SSL_LOGIN')) {
		$wpconfig = $wpconfig . PHP_EOL . "define('FORCE_SSL_LOGIN', true);";
	}
} else {
	array_push($patterns, "/'FORCE_SSL_LOGIN',\s*true/");
	array_push($replace, "'FORCE_SSL_LOGIN', false");
}

//CACHE CHECKS
if ($_POST['cache_wp']) {
	if (! strstr($wpconfig, 'WP_CACHE')) {
		$wpconfig = $wpconfig . PHP_EOL . "define('WP_CACHE', true);";
	}
} else {
	array_push($patterns, "/'WP_CACHE',\s*true/");
	array_push($replace,  "'WP_CACHE', false");
}
if (! $_POST['cache_path']) {
	array_push($patterns, "/'WPCACHEHOME',\s*'.*?'/");
	array_push($replace,  "'WPCACHEHOME', ''");
}

$wpconfig = preg_replace($patterns, $replace, $wpconfig);
file_put_contents('wp-config.php', $wpconfig);
$wpconfig = null;



//===============================
//DATABASE SCRIPT
//===============================
@chmod("{$root_path}/database.sql", 0777);
$sql_file = @file_get_contents('database.sql', true);
if ($sql_file == false || strlen($sql_file) < 10) {
	$sql_file = file_get_contents('installer-data.sql', true);
	if ($sql_file == false || strlen($sql_file) < 10) {
		DupUtil::log("ERROR: Unable to read from the extracted database.sql file .\nValidate the permissions and/or group-owner rights on directory '{$root_path}'\n");
	}
}

//Complex Subject See: http://webcollab.sourceforge.net/unicode.html
//Removes invalid space characters
if ($_POST['dbnbsp']) {
	DupUtil::log("ran fix non-breaking space characters\n");
	$sql_file = preg_replace('/\xC2\xA0/', ' ', $sql_file);
}

//Write new contents to install-data.sql
@chmod($sql_result_file_path, 0777);
file_put_contents($GLOBALS['SQL_FILE_NAME'], $sql_file);

$sql_result_file_data = explode(";\n", $sql_file);
$sql_result_file_length = count($sql_result_file_data);
$sql_result_file_path = "{$root_path}/{$GLOBALS['SQL_FILE_NAME']}";
$sql_file = null;

if (!is_readable($sql_result_file_path) || filesize($sql_result_file_path) == 0) {
	DupUtil::log("ERROR: Unable to create new sql file {$GLOBALS['SQL_FILE_NAME']}.\nValidate the permissions and/or group-owner rights on directory '{$root_path}' and file '{$GLOBALS['SQL_FILE_NAME']}'\n");
}

DupUtil::log("UPDATED SCRIPTS:");
DupUtil::log("\tsql file:  '{$sql_result_file_path}'");
DupUtil::log("\twp-config: '{$root_path}/wp-config.php'");
$zip_end = DupUtil::get_microtime();
DupUtil::log("\nSECTION RUNTIME: " . DupUtil::elapsed_time($zip_end, $zip_start));
DupUtil::log("\n");
DupUtil::fcgi_flush();


//====================================================================================================
//DATABASE ROUTINES
//====================================================================================================

@mysqli_query($dbh, "SET wait_timeout = {$GLOBALS['DB_MAX_TIME']}");
@mysqli_query($dbh, "SET max_allowed_packet = {$GLOBALS['DB_MAX_PACKETS']}");
DupUtil::mysql_set_charset($dbh, $_POST['dbcharset'], $_POST['dbcollate']);

//Set defaults incase the variable could not be read
$dbvar_maxtime = DupUtil::mysql_variable_value($dbh, 'wait_timeout');
$dbvar_maxpacks = DupUtil::mysql_variable_value($dbh, 'max_allowed_packet');
$dbvar_maxtime = is_null($dbvar_maxtime) ? 300 : $dbvar_maxtime;
$dbvar_maxpacks = is_null($dbvar_maxpacks) ? 1048576 : $dbvar_maxpacks;


DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log('DATABASE-ROUTINES');
DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log("--------------------------------------");
DupUtil::log("SERVER ENVIROMENT");
DupUtil::log("--------------------------------------");
DupUtil::log("MYSQL VERSION:\t" . mysqli_get_server_info($dbh));
DupUtil::log("TIMEOUT:\t{$dbvar_maxtime}");
DupUtil::log("MAXPACK:\t{$dbvar_maxpacks}");

//CREATE DB
if ($_POST['dbmake']) {
	mysqli_query($dbh, "CREATE DATABASE IF NOT EXISTS `{$_POST['dbname']}`");
	mysqli_select_db($dbh, $_POST['dbname'])
			or die(sprintf(MSG_FAIL_DBCONNECT_CREATE, $_POST['dbname']) . $back_link);
}

//DROP DB TABLES
$drop_log = "Database already empty. Ready for install.";
if ($_POST['dbclean']) {
	$sql = "SHOW TABLES FROM `{$_POST['dbname']}`";
	$found_tables = null;
	if ($result = mysqli_query($dbh, $sql)) {
		while ($row = mysqli_fetch_row($result)) {
			$found_tables[] = $row[0];
		}
		if (count($found_tables) > 0) {
			foreach ($found_tables as $table_name) {
				$sql = "DROP TABLE `{$_POST['dbname']}`.`{$table_name}`";
				if (!$result = mysqli_query($dbh, $sql)) {
					die(sprintf(MSG_FAIL_DBTRYCLEAN, $_POST['dbname']) . $back_link);
				}
			}
		}
		$drop_log = 'removed (' . count($found_tables) . ') tables';
	}
}

//WRITE DATA
DupUtil::log("--------------------------------------");
DupUtil::log("DATABASE RESULTS");
DupUtil::log("--------------------------------------");
$profile_start = DupUtil::get_microtime();
$fcgi_buffer_pool = 5000;
$fcgi_buffer_count = 0;
$dbquery_rows = 0;
$dbquery_errs = 0;
$counter = 0;
@mysqli_autocommit($dbh, false);
while ($counter < $sql_result_file_length) {

	$query_strlen = strlen(trim($sql_result_file_data[$counter]));
	if ($dbvar_maxpacks < $query_strlen) {
		DupUtil::log("**ERROR** Query size limit [length={$query_strlen}] [sql=" . substr($sql_result_file_data[$counter], 75) . "...]");
		$dbquery_errs++;
	} elseif ($query_strlen > 0) {
		@mysqli_free_result(@mysqli_query($dbh, ($sql_result_file_data[$counter])));
		$err = mysqli_error($dbh);
		//Check to make sure the connection is alive
		if (!empty($err)) {

			if (!mysqli_ping($dbh)) {
				mysqli_close($dbh);
				$dbh = mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname']);
			}
			DupUtil::log("**ERROR** database error write '{$err}' - [sql=" . substr($sql_result_file_data[$counter], 0, 75) . "...]");
			$dbquery_errs++;

		//Buffer data to browser to keep connection open				
		} else {
			if ($fcgi_buffer_count++ > $fcgi_buffer_pool) {
				$fcgi_buffer_count = 0;
				DupUtil::fcgi_flush();
			}
			$dbquery_rows++;
		}
	}
	$counter++;
}
@mysqli_commit($dbh);
@mysqli_autocommit($dbh, true);

DupUtil::log("ERRORS FOUND:\t{$dbquery_errs}");
DupUtil::log("DROP TABLE:\t{$drop_log}");
DupUtil::log("QUERIES RAN:\t{$dbquery_rows}\n");

$dbtable_count = 0;
if ($result = mysqli_query($dbh, "SHOW TABLES")) {
	while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
		$table_rows = DupUtil::table_row_count($dbh, $row[0]);
		DupUtil::log("{$row[0]}: ({$table_rows})");
		$dbtable_count++;
	}
	@mysqli_free_result($result);
}

if ($dbtable_count == 0) {
	DupUtil::log("NOTICE: You may have to manually run the installer-data.sql to validate data input. Also check to make sure your installer file is correct and the
		table prefix '{$GLOBALS['FW_TABLEPREFIX']}' is correct for this particular version of WordPress. \n");
}

//DATA CLEANUP: Perform Transient Cache Cleanup
//Remove all duplicator entries and record this one since this is a new install.
$dbdelete_count = 0;
@mysqli_query($dbh, "DELETE FROM `{$GLOBALS['FW_TABLEPREFIX']}duplicator`");
$dbdelete_count1 = @mysqli_affected_rows($dbh) or 0;
@mysqli_query($dbh, "DELETE FROM `{$GLOBALS['FW_TABLEPREFIX']}options` WHERE `option_name` LIKE ('_transient%') OR `option_name` LIKE ('_site_transient%')");
$dbdelete_count2 = @mysqli_affected_rows($dbh) or 0;
$dbdelete_count = (abs($dbdelete_count1) + abs($dbdelete_count2));
DupUtil::log("Removed '{$dbdelete_count}' cache/transient rows");
@mysqli_close($dbh);

$profile_end = DupUtil::get_microtime();
DupUtil::log("\nSECTION RUNTIME: " . DupUtil::elapsed_time($profile_end, $profile_start));

$ajax1_end = DupUtil::get_microtime();
$ajax1_sum = DupUtil::elapsed_time($ajax1_end, $ajax1_start);
DupUtil::log("\n{$GLOBALS['SEPERATOR1']}");
DupUtil::log('STEP1 COMPLETE @ ' . @date('h:i:s') . " - TOTAL RUNTIME: {$ajax1_sum}");
DupUtil::log("{$GLOBALS['SEPERATOR1']}");

$JSON['pass'] = 1;
$JSON['table_count'] = $dbtable_count;
$JSON['table_rows'] = ($dbquery_rows - ($dbtable_count + $dbdelete_count + $dbquery_errs));
$JSON['query_errs'] = $dbquery_errs;
echo json_encode($JSON);
error_reporting($ajax1_error_level);
die('');
?>