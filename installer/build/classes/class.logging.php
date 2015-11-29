<?php
// Exit if accessed directly
if (! defined('DUPLICATOR_INIT')) {
	$_baseURL =  strlen($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
	$_baseURL =  "http://" . $_baseURL;
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $_baseURL");
	exit; 
}

define('ERR_CONFIG_FOUND',		'A wp-config.php already exists in this location.  This error prevents users from accidentally overwriting the wrong directories contents.  You have two options: <ul><li>Empty this root directory except for the package and installer and try again.</li><li>Delete just the wp-config.php file and try again.  This will over-write all other files in the directory.</li></ul>');
define('ERR_ZIPNOTFOUND',		'The packaged zip file was not found. Be sure the zip package is in the same directory as the installer file and as the correct permissions.  If you are trying to reinstall a package you can copy the package from the "' . DUPLICATOR_SSDIR_NAME . '" directory back up to your root which is the same location as your installer.php file.');
define('ERR_ZIPOPEN',			'Failed to open zip archive file. Please be sure the archive is completely downloaded before running the installer. Try to extract the archive manually to make sure the file is not corrupted.');
define('ERR_ZIPEXTRACTION',		'Errors extracting zip file.  Portions or part of the zip archive did not extract correctly.    Try to extract the archive manually with a client side program like unzip/win-zip/winrar to make sure the file is not corrupted.  If the file extracts correctly then there is an invalid file or directory that PHP is unable to extract.  This can happen if your moving from one operating system to another where certain naming conventions work on one environment and not another. <br/><br/> Workarounds: <br/> 1. Create a new package and be sure to exclude any directories that have invalid names or files in them.   This warning will be displayed on the scan results under "Invalid Names". <br/> 2. Manually extract the zip file with a client side program.  Then under advanced options in step 1 of the installer check the "Manual package extraction" option and perform the install.');
define('ERR_ZIPMANUAL',			'When choosing manual package extraction, the contents of the package must already be extracted and the wp-config.php and database.sql files must be present in the same directory as the installer.php for the process to continue.  Please manually extract the package into the current directory before continuing in manual extraction mode.  Also validate that the wp-config.php and database.sql files are present.');
define('ERR_MAKELOG',			'PHP is having issues writing to the log file <b>' . DupUtil::set_safe_path($GLOBALS['CURRENT_ROOT_PATH']) . '\installer-log.txt .</b> In order for the Duplicator to proceed validate your owner/group and permission settings for PHP on this path. Try temporarily setting you permissions to 777 to see if the issue gets resolved.  If you are on a shared hosting environment please contact your hosting company and tell them you are getting errors writing files to the path above when using PHP.');
define('ERR_ZIPARCHIVE',		'In order to extract the archive.zip file the PHP ZipArchive module must be installed.  Please read the FAQ for more details.  You can still install this package but you will need to check the Manual package extraction checkbox found in the Advanced Options.  Please read the online user guide for details in performing a manual package extraction.');
define('ERR_MYSQLI_SUPPORT',	'In order to complete an install the mysqli extension for PHP is required. If you are on a hosted server please contact your host and request that mysqli be enabled.  For more information visit: http://php.net/manual/en/mysqli.installation.php');
define('ERR_DBCONNECT',			'DATABASE CONNECTION FAILED!<br/>');
define('ERR_DBCONNECT_CREATE',  'DATABASE CREATION FAILURE!<br/> Unable to create database "%s". Check to make sure the user has "Create" privileges.  Some hosts will restrict creation of a database only through the cpanel.  Try creating the database manually to proceed with installation.  If the database already exists then check the radio button labeled "Connect and Remove All Data" which will remove all existing tables.');
define('ERR_DBTRYCLEAN',		'DATABASE CREATION FAILURE!<br/> Unable to remove all tables from database "%s".<br/>  Please remove all tables from this database and try the installation again.');
define('ERR_DBCREATE',			'The database "%s" does not exists.<br/>  Change mode to create in order to create a new database.');
define('ERR_DBEMPTY',			'The database "%s" has "%s" tables.  The Duplicator only works with an EMPTY database.  Enable the action "Connect and Remove All Data" radio button to remove all tables and or create a new database. Some hosting providers do not allow table removal from scripts.  In this case you will need to login to your hosting providers control panel and remove the tables manually.  Please contact your hosting provider for further details.  Always backup all your data before proceeding!');

/** * *****************************************************
 * DUPX_Log  
 * Class used to log information  */

class DUPX_Log {

    /** METHOD: LOG
     *  Used to write debug info to the text log file
     *  @param string $msg		Any text data
     *  @param int $loglevel	Log level
     */
    static public function Info($msg, $logging = 1) {
        if ($logging <= $GLOBALS["LOGGING"]) {
            @fwrite($GLOBALS["LOG_FILE_HANDLE"], "{$msg}\n");
        }
    }
	
	static public function Error($errorMessage) {
		if ($logging <= $GLOBALS["LOGGING"]) {
			$breaks = array("<br />","<br>","<br/>");  
			$msg = str_ireplace($breaks, "\r\n", $errorMessage);  
            @fwrite($GLOBALS["LOG_FILE_HANDLE"], "\nINSTALLER ERROR:\n{$msg}\n");
			@fclose($GLOBALS["LOG_FILE_HANDLE"]);
        }
        die("<div class='dup-ui-error'><b style='color:#B80000;'>INSTALL ERROR!</b><br/>{$errorMessage}</div><br/>");
    }
}
?>
