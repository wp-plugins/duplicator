<?php
/*
  Copyright 2011-12 Cory Lamle  lifeinthegrid.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

  SOURCE CONTRIBUTORS:
  Gaurav Aggarwal
  David Coveney of Interconnect IT Ltd
  https://github.com/interconnectit/Search-Replace-DB/
 */

if (file_exists('dtoken.php')) {
    //This is most likely inside the snapshot folder.
    
    //DOWNLOAD ONLY: (Only enable download from within the snapshot directory)
    if (isset($_GET['get']) && isset($_GET['file'])) {
        //Clean the input, strip out anything not alpha-numeric or "_.", so restricts
        //only downloading files in same folder, and removes risk of allowing directory
        //separators in other charsets (vulnerability in older IIS servers), also
        //strips out anything that might cause it to use an alternate stream since
        //that would require :// near the front.
    	$filename = preg_replace('/[^a-zA-Z0-9_.]*/','',$_GET['file']);
    	if (strlen($filename) && file_exists($filename) && (strstr($filename, '_installer.php') || strstr($filename, 'installer.rescue.php'))) {
            //Attempt to push the file to the browser
    	    header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=installer.php');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
            //FIXME: We should consider removing all error supression like this
            //as it makes troubleshooting a wild goose chase for times that the
            //script failes on such a line.  The same can and should be accomplished
            //at the server level by turning off displaying errors in PHP.
            @ob_clean();
            @flush();
            if (@readfile($filename) == false) {
                $data = file_get_contents($filename);
                if ($data == false) {
                    die("Unable to read installer file.  The server currently has readfile and file_get_contents disabled on this server.  Please contact your server admin to remove this restriction");
                } else {
                    print $data;
                }
            }
        } else {
            header("HTML/1.1 404 Not Found", true, 404);
            header("Status: 404 Not Found");
        }
    }

	//Prevent Access from rovers or direct browsing in snapshop directory, or when
    //requesting to download a file, should not go past this point.
    exit;
}
?>

<?php if (false) : ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>Error: PHP is not running</title>
        </head>
        <body>
            <h2>Error: PHP is not running</h2>
            <p>Duplicator requires that your web server is running PHP. Your server does not have PHP installed, or PHP is turned off.</p>
        </body>
    </html>
<?php endif; ?> 


<?php
/* ==============================================================================================
  ADVANCED FEATURES - Allows admins to perform aditional logic on the import.

  $GLOBALS['TABLES_SKIP_COLS']
  Add Known column names of tables you don't want the search and replace logic to run on.
  $GLOBALS['REPLACE_LIST']
  Add additional search and replace items add list here
  Example: array(array('search'=> '/html/oldpath/images',  'replace'=> '/html/newpath/images'));
  ================================================================================================= */

$GLOBALS['FW_TABLEPREFIX'] = '%fwrite_wp_tableprefix%';
$GLOBALS['FW_URL_OLD'] = '%fwrite_url_old%';
$GLOBALS['FW_URL_NEW'] = '%fwrite_url_new%';
$GLOBALS['FW_PACKAGE_NAME'] = '%fwrite_package_name%';
$GLOBALS['FW_SECURE_NAME'] = '%fwrite_secure_name%';
$GLOBALS['FW_DBHOST'] = '%fwrite_dbhost%';
$GLOBALS['FW_DBNAME'] = '%fwrite_dbname%';
$GLOBALS['FW_DBUSER'] = '%fwrite_dbuser%';
$GLOBALS['FW_DBPASS'] = '%fwrite_dbpass%';
$GLOBALS['FW_SSL_ADMIN'] = '%fwrite_ssl_admin%';
$GLOBALS['FW_SSL_LOGIN'] = '%fwrite_ssl_login%';
$GLOBALS['FW_CACHE_WP'] = '%fwrite_cache_wp%';
$GLOBALS['FW_CACHE_PATH'] = '%fwrite_cache_path%';
$GLOBALS['FW_BLOGNAME'] = '%fwrite_blogname%';
$GLOBALS['FW_RESCUE_FLAG'] = '%fwrite_rescue_flag%';
$GLOBALS['FW_WPROOT'] = '%fwrite_wproot%';
$GLOBALS['FW_DUPLICATOR_VERSION'] = '%fwrite_duplicator_version%';

//DATABASE SETUP: all time in seconds	
$GLOBALS['DB_MAX_TIME'] = 5000;
$GLOBALS['DB_MAX_PACKETS'] = 268435456;
ini_set('mysql.connect_timeout', '5000');

//PHP SETUP: all time in seconds
ini_set('memory_limit', '5000M');
ini_set("max_execution_time", '5000');
ini_set("max_input_time", '5000');
ini_set('default_socket_timeout', '5000');
@set_time_limit(0);

$GLOBALS['DBCHARSET_DEFAULT'] = 'utf8';
$GLOBALS['DBCOLLATE_DEFAULT'] = 'utf8_general_ci';

//UPDATE TABLE SETTINGS
$GLOBALS['TABLES_SKIP_COLS'] = array('');
$GLOBALS['REPLACE_LIST'] = array();


/* ================================================================================================
  END ADVANCED FEATURES: Do not edit below here.
  =================================================================================================== */

//CONSTANTS
define("DUPLICATOR_SSDIR_NAME", 'wp-snapshots');  //This should match DUPLICATOR_SSDIR_NAME in duplicator.php
//GLOBALS
$GLOBALS["SQL_FILE_NAME"] = "installer-data.sql";
$GLOBALS["LOG_FILE_NAME"] = "installer-log.txt";
$GLOBALS['SEPERATOR1'] = str_repeat("********", 10);
$GLOBALS['LOGGING'] = isset($_POST['logging']) ? $_POST['logging'] : 1;

$GLOBALS['CURRENT_ROOT_PATH'] = dirname(__FILE__);
$GLOBALS['CHOWN_ROOT_PATH'] = @chmod("{$GLOBALS['CURRENT_ROOT_PATH']}", 0755);
$GLOBALS['CHOWN_LOG_PATH'] = @chmod("{$GLOBALS['CURRENT_ROOT_PATH']}/{$GLOBALS['LOG_FILE_NAME']}", 0644);
$GLOBALS['URL_SSL'] = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') ? true : false;
$GLOBALS['URL_PATH'] = ($GLOBALS['URL_SSL']) ? "https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}" : "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";

//SHARED POST PARMS
$_POST['action_step'] = isset($_POST['action_step']) ? $_POST['action_step'] : "0";
$_POST['dbhost'] = isset($_POST['dbhost']) ? trim($_POST['dbhost']) : null;
$_POST['dbuser'] = isset($_POST['dbuser']) ? trim($_POST['dbuser']) : null;
$_POST['dbpass'] = isset($_POST['dbpass']) ? trim($_POST['dbpass']) : null;
$_POST['dbname'] = isset($_POST['dbname']) ? trim($_POST['dbname']) : null;
$_POST['dbcharset'] = isset($_POST['dbcharset']) ? trim($_POST['dbcharset']) : $GLOBALS['DBCHARSET_DEFAULT'];
$_POST['dbcollate'] = isset($_POST['dbcollate']) ? trim($_POST['dbcollate']) : $GLOBALS['DBCOLLATE_DEFAULT'];


//Restart log if user starts from step 1
if ($_POST['action_step'] == 1) {
    $GLOBALS['LOG_FILE_HANDLE'] = @fopen($GLOBALS['LOG_FILE_NAME'], "w+");
} else {
    $GLOBALS['LOG_FILE_HANDLE'] = @fopen($GLOBALS['LOG_FILE_NAME'], "a+");
}
?>

<?php

/** * *****************************************************
 *  CLASS::DupUtil
 *  Various Static Utility methods for working with the installer */
class DupUtil {

    /** METHOD: LOG
     *  Used to write debug info to the output page
     *  @param string $msg		A a message that belongs to a unique title block
     *  @param int $loglevel	Log level
     *  @param bool $newline	Insert a newline 
     */
    static public function log($msg, $logging = 1) {
        if ($logging <= $GLOBALS["LOGGING"]) {
            @fwrite($GLOBALS["LOG_FILE_HANDLE"], "{$msg}\n");
        }
    }

    /** METHOD: GET_MICROTIME
     * Get current microtime as a float. Can be used for simple profiling.
     */
    static public function get_microtime() {
        return microtime(true);
    }

    /** METHOD: ELAPSED_TIME
     * Return a string with the elapsed time.
     * Order of $end and $start can be switched. 
     */
    static public function elapsed_time($end, $start) {
        return sprintf("%.4f sec.", abs($end - $start));
    }

    /** METHOD: ESC_HTML_ATTR
     * @param string $string Thing that needs escaping
     * @param bool $echo   Do we echo or return?
     * @return string    Escaped string. 
     */
    static public function esc_html_attr($string = '', $echo = false) {
        $output = htmlentities($string, ENT_QUOTES, 'UTF-8');
        if ($echo)
            echo $output;
        else
            return $output;
    }

    /** METHOD: TABLE_COUNT
     *  Count the tables in a given database
     *  @param string $_POST['dbname']		Database to count tables in 
     */
    static public function dbtable_count($conn, $dbname) {
        $res = mysqli_query($conn, "SELECT COUNT(*) AS count FROM information_schema.tables WHERE table_schema = '{$dbname}' ");
        $row = mysqli_fetch_row($res);
        return is_null($row) ? 0 : $row[0];
    }

    /** METHOD: TABLE_ROW_COUNT
     *  Returns the table count
     *  @param string $conn	        A valid link resource
     *  @param string $table_name	A valid table name
     */
    static public function table_row_count($conn, $table_name) {
        $total = mysqli_query($conn, "SELECT COUNT(*) FROM `$table_name`");
        if ($total) {
            $total = @mysqli_fetch_array($total);
            return $total[0];
        } else {
            return 0;
        }
    }

    /** METHOD: ADD_ENDING_SLASH
     *  Adds a slash to the end of a path
     *  @param string $path		A path
     */
    static public function add_slash($path) {
        $last_char = substr($path, strlen($path) - 1, 1);
        if ($last_char != '/') {
            $path .= '/';
        }
        return $path;
    }

    /** METHOD: SET_SAFE_PATH
     *  Makes path safe for any OS
     *  Paths should ALWAYS READ be "/"
     * 		uni: /home/path/file.xt
     * 		win:  D:/home/path/file.txt 
     *  @param string $path		The path to make safe
     */
    static public function set_safe_path($path) {
        return str_replace("\\", "/", $path);
    }

    static public function unset_safe_path($path) {
        return str_replace("/", "\\", $path);
    }

    /** METHOD: FCGI_FLUSH
     *  PHP_SAPI for fcgi requires a data flush of at least 256
     *  bytes every 40 seconds or else it forces a script hault
     */
    static public function fcgi_flush() {
        echo(str_repeat(' ', 256));
        @flush();
    }

    /** METHOD: COPY_FILE
     *  A safe method used to copy larger files
     *  @param string $source		The path to the file being copied
     *  @param string $destination	The path to the file being made
     */
    static public function copy_file($source, $destination) {
        $sp = fopen($source, 'r');
        $op = fopen($destination, 'w');

        while (!feof($sp)) {
            $buffer = fread($sp, 512);  // use a buffer of 512 bytes
            fwrite($op, $buffer);
        }
        // close handles
        fclose($op);
        fclose($sp);
    }

    /** METHOD: string_has_value
     *  Finds a string in a file and returns true if found
     *  @param array  $list		A list of strings to search for
     *  @param string $haystack	The string to search in
     */
    static public function string_has_value($list, $file) {
        foreach ($list as $var) {
            if (strstr($file, $var) !== false) {
                return true;
            }
        }
        return false;
    }

    /** METHOD: get_active_plugins
     *  Returns the active plugins for a package
     *  @param  conn   $dbh	 A database connection handle
     *  @return array  $list A list of active plugins
     */
    static public function get_active_plugins($dbh) {
        $query = @mysqli_query($dbh, "SELECT option_value FROM `{$GLOBALS['FW_TABLEPREFIX']}options` WHERE option_name = 'active_plugins' ");
        if ($query) {
            $row = @mysqli_fetch_array($query);
            $all_plugins = unserialize($row[0]);
            if (is_array($all_plugins)) {
                return $all_plugins;
            }
        }
        return array();
    }

    /** METHOD: get_database_tables
     *  Returns the tables for a database
     *  @param  conn   $dbh	 A database connection handle	 
     *  @return array  $list A list of all table names
     */
    static public function get_database_tables($dbh) {
        $query = @mysqli_query($dbh, 'SHOW TABLES');
        if ($query) {
            while ($table = @mysqli_fetch_array($query)) {
                $all_tables[] = $table[0];
            }
            if (is_array($all_tables)) {
                return $all_tables;
            }
        }
        return array();
    }

    /**
     * MySQL database version number
     * @param conn $dbh Database connection handle
     * @return false|string false on failure, version number on success
     */
    static public function mysql_version($dbh) {
        if (function_exists('mysqli_get_server_info')) {
            return preg_replace('/[^0-9.].*/', '', mysqli_get_server_info($dbh));
        } else {
            return 0;
        }
    }

    /**
     * MySQL server variable
     * @param conn $dbh Database connection handle
     * @return string the server variable to query for
     */
    static public function mysql_variable_value($dbh, $variable) {
        $result = @mysqli_query($dbh, "SHOW VARIABLES LIKE '{$variable}'");
        $row = @mysqli_fetch_array($result);
        @mysqli_free_result($result);
        return isset($row[1]) ? $row[1] : null;
    }

    /**
     * Determine if a MySQL database supports a particular feature
     * @param conn $dbh Database connection handle	 
     * @param string $feature the feature to check for
     * @return bool
     */
    static public function mysql_has_ability($dbh, $feature) {
        $version = self::mysql_version($dbh);

        switch (strtolower($feature)) {
            case 'collation' :
            case 'group_concat' :
            case 'subqueries' :
                return version_compare($version, '4.1', '>=');
            case 'set_charset' :
                return version_compare($version, '5.0.7', '>=');
        };
        return false;
    }

    /**
     * Sets the MySQL connection's character set.
     * @param resource $dbh     The resource given by mysqli_connect
     * @param string   $charset The character set (optional)
     * @param string   $collate The collation (optional)
     */
    static public function mysql_set_charset($dbh, $charset = null, $collate = null) {

        $charset = (!isset($charset) ) ? $GLOBALS['DBCHARSET_DEFAULT'] : $charset;
        $collate = (!isset($collate) ) ? $GLOBALS['DBCOLLATE_DEFAULT'] : $collate;

        if (self::mysql_has_ability($dbh, 'collation') && !empty($charset)) {
            if (function_exists('mysqli_set_charset') && self::mysql_has_ability($dbh, 'set_charset')) {
                return mysqli_set_charset($dbh, $charset);
            } else {
                $sql = " SET NAMES {$charset}";
                if (!empty($collate))
                    $sql .= " COLLATE {$collate}";
                return mysqli_query($dbh, $sql);
            }
        }
    }

    /**
     *  READABLE_BYTESIZE
     *  Display human readable byte sizes
     *  @param string $size		The size in bytes
     */
    static public function readable_bytesize($size) {
        try {
            $units = array('B', 'KB', 'MB', 'GB', 'TB');
            for ($i = 0; $size >= 1024 && $i < 4; $i++)
                $size /= 1024;
            return round($size, 2) . $units[$i];
        } catch (Exception $e) {
            return "n/a";
        }
    }

}

?>


<?php
if (isset($_POST['action_ajax'])) {
    switch ($_POST['action_ajax']) {
        case "1" :
            ?> <?php

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
//SCRIPTS: wp-config/database.sql
//===============================
//WP-CONFIG

$wpconfig = @file_get_contents('wp-config.php', true);

$patterns = array(
	"/'DB_NAME',\s*'.*?'/",
	"/'DB_USER',\s*'.*?'/",
	"/'DB_PASSWORD',\s*'.*?'/",
	"/'DB_HOST',\s*'.*?'/");

$replace = array(
	"'DB_NAME', " . '\'' . $_POST['dbname'] . '\'',
	"'DB_USER', " . '\'' . $_POST['dbuser'] . '\'',
	"'DB_PASSWORD', " . '\'' . $_POST['dbpass'] . '\'',
	"'DB_HOST', " . '\'' . $_POST['dbhost'] . '\'');

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


//DATABASE SCRIPT
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
?> <?php break;
        case "2" :
            ?> <?php
/* JSON RESPONSE: Most sites have warnings turned off by default, but if they're turned on the warnings
cause errors in the JSON data Here we hide the status so warning level is reset at it at the end*/
$ajax2_error_level = error_reporting();
error_reporting(E_ERROR);

/** * *****************************************************
 * CLASS::DUPDBTEXTSWAP
 * Walks every table in db that then walks every row and column replacing searches with replaces
 * large tables are split into 50k row blocks to save on memory. */
class DupDBTextSwap {

	/**
	 * LOG ERRORS
	 */
	static public function log_errors($report) {
		if (!empty($report['errsql'])) {
			DupUtil::log("====================================");
			DupUtil::log("DATA-REPLACE ERRORS (MySQL)");
			foreach ($report['errsql'] as $error) {
				DupUtil::log($error);
			}
			DupUtil::log("");
		}
		if (!empty($report['errser'])) {
			DupUtil::log("====================================");
			DupUtil::log("DATA-REPLACE ERRORS (Serialization):");
			foreach ($report['errser'] as $error) {
				DupUtil::log($error);
			}
			DupUtil::log("");
		}
		if (!empty($report['errkey'])) {
			DupUtil::log("====================================");
			DupUtil::log("DATA-REPLACE ERRORS (Key):");
			DupUtil::log('Use SQL: SELECT @row := @row + 1 as row, t.* FROM some_table t, (SELECT @row := 0) r');
			foreach ($report['errkey'] as $error) {
				DupUtil::log($error);
			}
			DupUtil::log("");
		}
	}

	/**
	 * LOG STATS
	 */
	static public function log_stats($report) {
		if (!empty($report) && is_array($report)) {
			$stats = sprintf("SEARCH1:\t'%s' \nREPLACE1:\t'%s' \n", $_POST['url_old'], $_POST['url_new']);
			$stats .= sprintf("SEARCH2:\t'%s' \nREPLACE2:\t'%s' \n", $_POST['path_old'], $_POST['path_new']);
			$stats .= sprintf("SCANNED:\tTables:%d | Rows:%d | Cells:%d \n", $report['scan_tables'], $report['scan_rows'], $report['scan_cells']);
			$stats .= sprintf("UPDATED:\tTables:%d | Rows:%d |Cells:%d \n", $report['updt_tables'], $report['updt_rows'], $report['updt_cells']);
			$stats .= sprintf("ERRORS:\t\t%d \nRUNTIME:\t%f sec", $report['err_all'], $report['time']);
			DupUtil::log($stats);
		}
	}

	/**
	 * LOAD
	 * Begins the processing for replace logic
	 * @param mysql  $conn 		 The db connection object
	 * @param array  $list       Key value pair of 'search' and 'replace' arrays
	 * @param array  $tables     The tables we want to look at.
	 * @return array Collection of information gathered during the run.
	 */
	static public function load($conn, $list = array(), $tables = array(), $cols = array()) {
		$exclude_cols = $cols;

		$report = array('scan_tables' => 0, 'scan_rows' => 0, 'scan_cells' => 0,
			'updt_tables' => 0, 'updt_rows' => 0, 'updt_cells' => 0,
			'errsql' => array(), 'errser' => array(), 'errkey' => array(),
			'errsql_sum' => 0, 'errser_sum' => 0, 'errkey_sum' => 0,
			'time' => '', 'err_all' => 0);

		$profile_start = DupUtil::get_microtime();
		if (is_array($tables) && !empty($tables)) {

			foreach ($tables as $table) {
				$report['scan_tables']++;
				$columns = array();

				// Get a list of columns in this table
				$fields = mysqli_query($conn, 'DESCRIBE ' . $table);
				while ($column = mysqli_fetch_array($fields)) {
					$columns[$column['Field']] = $column['Key'] == 'PRI' ? true : false;
				}

				// Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
				$row_count = mysqli_query($conn, 'SELECT COUNT(*) FROM ' . $table);
				$rows_result = mysqli_fetch_array($row_count);
				@mysqli_free_result($row_count);
				$row_count = $rows_result[0];
				if ($row_count == 0)
					continue;

				$page_size = 25000;
				$pages = ceil($row_count / $page_size);

				for ($page = 0; $page < $pages; $page++) {

					$current_row = 0;
					$start = $page * $page_size;
					$end = $start + $page_size;
					// Grab the content of the table
					$data = mysqli_query($conn, sprintf('SELECT * FROM %s LIMIT %d, %d', $table, $start, $end));

					if (!$data)
						$report['errsql'][] = mysqli_error($conn);

					//Loops every row
					while ($row = mysqli_fetch_array($data)) {
						$report['scan_rows']++;
						$current_row++;
						$upd_col = array();
						$upd_sql = array();
						$where_sql = array();
						$upd = false;
						$serial_err = 0;

						//Loops every cell
						foreach ($columns as $column => $primary_key) {
							if (in_array($column, $exclude_cols)) {
								continue;
							}

							$report['scan_cells']++;
							$edited_data = $data_to_fix = $row[$column];
							$base64coverted = false;

							//Only replacing string values
							if (!is_numeric($row[$column])) {

								//Base 64 detection
								if (base64_decode($row[$column], true)) {
									$decoded = base64_decode($row[$column], true);
									if (self::is_serialized($decoded)) {
										$edited_data = $decoded;
										$base64coverted = true;
									}
								}

								//Replace logic - level 1: simple check on basic serilized strings
								foreach ($list as $item) {
									$edited_data = self::recursive_unserialize_replace($item['search'], $item['replace'], $edited_data);
								}

								//Replace logic - level 2: repair larger/complex serilized strings
								$serial_check = self::fix_serial_string($edited_data);
								if ($serial_check['fixed']) {
									$edited_data = $serial_check['data'];
								} elseif ($serial_check['tried'] && !$serial_check['fixed']) {
									$serial_err++;
								}
							}

							//Change was made
							if ($edited_data != $data_to_fix || $serial_err > 0) {
								$report['updt_cells']++;
								//Base 64 encode
								if ($base64coverted) {
									$edited_data = base64_encode($edited_data);
								}
								$upd_col[] = $column;
								$upd_sql[] = $column . ' = "' . mysqli_real_escape_string($conn, $edited_data) . '"';
								$upd = true;
							}

							if ($primary_key) {
								$where_sql[] = $column . ' = "' . mysqli_real_escape_string($conn, $data_to_fix) . '"';
							}
						}

						//PERFORM ROW UPDATE
						if ($upd && !empty($where_sql)) {

							$sql = "UPDATE `{$table}` SET " . implode(', ', $upd_sql) . ' WHERE ' . implode(' AND ', array_filter($where_sql));
							$result = mysqli_query($conn, $sql) or $report['errsql'][] = mysqli_error($conn);
							if ($result) {
								if ($serial_err > 0) {
									$report['errser'][] = "SELECT " . implode(', ', $upd_col) . " FROM `{$table}`  WHERE " . implode(' AND ', array_filter($where_sql)) . ';';
								}
								$report['updt_rows']++;
							}
						} elseif ($upd) {
							$report['errkey'][] = sprintf("Row [%s] on Table [%s] requires a manual update.", $current_row, $table);
						}
						DupUtil::fcgi_flush();
					}
					@mysqli_free_result($data);
				}

				if ($upd) {
					$report['updt_tables']++;
				}
			}
		}
		$profile_end = DupUtil::get_microtime();
		$report['time'] = DupUtil::elapsed_time($profile_end, $profile_start);
		$report['errsql_sum'] = empty($report['errsql']) ? 0 : count($report['errsql']);
		$report['errser_sum'] = empty($report['errser']) ? 0 : count($report['errser']);
		$report['errkey_sum'] = empty($report['errkey']) ? 0 : count($report['errkey']);
		$report['err_all'] = $report['errsql_sum'] + $report['errser_sum'] + $report['errkey_sum'];
		return $report;
	}

	/**
	 * Take a serialised array and unserialise it replacing elements and
	 * unserialising any subordinate arrays and performing the replace.
	 * @param string $from       String we're looking to replace.
	 * @param string $to         What we want it to be replaced with
	 * @param array  $data       Used to pass any subordinate arrays back to in.
	 * @param bool   $serialised Does the array passed via $data need serialising.
	 * @return array	The original array with all elements replaced as needed. 
	 */
	static private function recursive_unserialize_replace($from = '', $to = '', $data = '', $serialised = false) {

		// some unseriliased data cannot be re-serialised eg. SimpleXMLElements
		try {

			if (is_string($data) && ($unserialized = @unserialize($data)) !== false) {
				$data = self::recursive_unserialize_replace($from, $to, $unserialized, true);
			} elseif (is_array($data)) {
				$_tmp = array();
				foreach ($data as $key => $value) {
					$_tmp[$key] = self::recursive_unserialize_replace($from, $to, $value, false);
				}
				$data = $_tmp;
				unset($_tmp);
			} elseif (is_object($data)) {
				$dataClass = get_class($data);
				$_tmp = new $dataClass();
				foreach ($data as $key => $value) {
					$_tmp->$key = self::recursive_unserialize_replace($from, $to, $value, false);
				}
				$data = $_tmp;
				unset($_tmp);
			} else {
				if (is_string($data)) {
					$data = str_replace($from, $to, $data);
				}
			}

			if ($serialised)
				return serialize($data);
		} catch (Exception $error) {
			DupUtil::log("\nRECURSIVE UNSERIALIZE ERROR: With string\n" . $error, 2);
		}
		return $data;
	}

	/**
	 *  IS_SERIALIZED
	 *  Test if a string in properly serialized */
	static public function is_serialized($data) {
		$test = @unserialize(($data));
		return ($test !== false || $test === 'b:0;') ? true : false;
	}

	/**
	 *  FIX_STRING
	 *  Fixes the string length of a string object that has been serialized but the length is broken
	 *  @param string $data	The string ojbect to recalculate the size on.
	 *  @return 
	 */
	static private function fix_serial_string($data) {

		$result = array('data' => $data, 'fixed' => false, 'tried' => false);

		if (preg_match("/s:[0-9]+:/", $data)) {
			if (!self::is_serialized($data)) {
				$regex = '!(?<=^|;)s:(\d+)(?=:"(.*?)";(?:}|a:|s:|b:|d:|i:|o:|N;))!s';
				$serial_string = preg_match('/^s:[0-9]+:"(.*$)/s', trim($data), $matches);
				//Nested serial string
				if ($serial_string) {
					$inner = preg_replace_callback($regex, 'DupDBTextSwap::fix_string_callback', rtrim($matches[1], '";'));
					$serialized_fixed = 's:' . strlen($inner) . ':"' . $inner . '";';
				} else {
					$serialized_fixed = preg_replace_callback($regex, 'DupDBTextSwap::fix_string_callback', $data);
				}

				if (self::is_serialized($serialized_fixed)) {
					$result['data'] = $serialized_fixed;
					$result['fixed'] = true;
				}
				$result['tried'] = true;
			}
		}
		return $result;
	}

	static private function fix_string_callback($matches) {
		return 's:' . strlen(($matches[2]));
	}

}

//====================================================================================================
//DATABASE UPDATES
//====================================================================================================

$ajax2_start = DupUtil::get_microtime();

//MYSQL CONNECTION
$db_port = parse_url($_POST['dbhost'], PHP_URL_PORT);
$dbh = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname'], $db_port);
$charset_server = @mysqli_character_set_name($dbh);
@mysqli_query($dbh, "SET wait_timeout = {$GLOBALS['DB_MAX_TIME']}");
DupUtil::mysql_set_charset($dbh, $_POST['dbcharset'], $_POST['dbcollate']);

//POST PARAMS
$_POST['blogname'] = mysqli_real_escape_string($dbh, $_POST['blogname']);
$_POST['postguid'] = isset($_POST['postguid']) && $_POST['postguid'] == 1 ? 1 : 0;
$_POST['path_old'] = isset($_POST['path_old']) ? trim($_POST['path_old']) : null;
$_POST['path_new'] = isset($_POST['path_new']) ? trim($_POST['path_new']) : null;
$_POST['siteurl'] = isset($_POST['siteurl']) ? rtrim(trim($_POST['siteurl']), '/') : null;
$_POST['tables'] = isset($_POST['tables']) && is_array($_POST['tables']) ? array_map('stripcslashes', $_POST['tables']) : array();
$_POST['url_old'] = isset($_POST['url_old']) ? trim($_POST['url_old']) : null;
$_POST['url_new'] = isset($_POST['url_new']) ? rtrim(trim($_POST['url_new']), '/') : null;

//LOGGING
$POST_LOG = $_POST;
unset($POST_LOG['tables']);
unset($POST_LOG['plugins']);
unset($POST_LOG['dbpass']);
ksort($POST_LOG);

//GLOBAL DB-REPLACE
DupUtil::log("\n\n\n{$GLOBALS['SEPERATOR1']}");
DupUtil::log('DUPLICATOR INSTALL-LOG');
DupUtil::log('STEP2 START @ ' . @date('h:i:s'));
DupUtil::log('NOTICE: NOTICE: Do not post to public sites or forums');
DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log("CHARSET SERVER:\t{$charset_server}");
DupUtil::log("CHARSET CLIENT:\t" . @mysqli_character_set_name($dbh));
DupUtil::log("--------------------------------------");
DupUtil::log("POST DATA");
DupUtil::log("--------------------------------------");
DupUtil::log(print_r($POST_LOG, true));

DupUtil::log("--------------------------------------");
DupUtil::log("SCANNED TABLES");
DupUtil::log("--------------------------------------");
$msg = (isset($_POST['tables']) && count($_POST['tables'] > 0)) ? print_r($_POST['tables'], true) : 'No tables selected to update';
DupUtil::log($msg);

DupUtil::log("--------------------------------------");
DupUtil::log("KEEP PLUGINS ACTIVE");
DupUtil::log("--------------------------------------");
$msg = (isset($_POST['plugins']) && count($_POST['plugins'] > 0)) ? print_r($_POST['plugins'], true) : 'No plugins selected for activation';
DupUtil::log($msg);

//UPDATE SETTINGS
$serial_plugin_list = (isset($_POST['plugins']) && count($_POST['plugins'] > 0)) ? @serialize($_POST['plugins']) : '';
mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}options` SET option_value = '{$_POST['blogname']}' WHERE option_name = 'blogname' ");
mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}options` SET option_value = '{$serial_plugin_list}'  WHERE option_name = 'active_plugins' ");

DupUtil::log("--------------------------------------");
DupUtil::log("GLOBAL DB-REPLACE");
DupUtil::log("--------------------------------------");

array_push($GLOBALS['REPLACE_LIST'], 
		array('search' => $_POST['url_old'], 'replace' => $_POST['url_new']), 
		array('search' => $_POST['path_old'], 'replace' => $_POST['path_new']), 
		array('search' => rtrim(DupUtil::unset_safe_path($_POST['path_old']), '\\'), 'replace' => rtrim($_POST['path_new'], '/'))
);

@mysqli_autocommit($dbh, false);
$report = DupDBTextSwap::load($dbh, $GLOBALS['REPLACE_LIST'], $_POST['tables'], $GLOBALS['TABLES_SKIP_COLS']);
@mysqli_commit($dbh);
@mysqli_autocommit($dbh, true);


//BUILD JSON RESPONSE
$JSON = array();
$JSON['step1'] = json_decode(urldecode($_POST['json']));
$JSON['step2'] = $report;
$JSON['step2']['warn_all'] = 0;
$JSON['step2']['warnlist'] = array();

DupDBTextSwap::log_stats($report);
DupDBTextSwap::log_errors($report);

//Reset the postguid data
if ($_POST['postguid']) {
	mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}posts` SET guid = REPLACE(guid, '{$_POST['url_new']}', '{$_POST['url_old']}')");
	$update_guid = @mysqli_affected_rows($dbh) or 0;
	DupUtil::log("Reverted '{$update_guid}' post guid columns back to '{$_POST['url_old']}'");
}

/* FINAL UPDATES: Must happen after the global replace to prevent double pathing
  http://xyz.com/abc01 will become http://xyz.com/abc0101  with trailing data */
mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}options` SET option_value = '{$_POST['url_new']}'  WHERE option_name = 'home' ");
mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}options` SET option_value = '{$_POST['siteurl']}'  WHERE option_name = 'siteurl' ");


//====================================================================================================
//FINAL CLEANUP
//====================================================================================================
DupUtil::log("\n{$GLOBALS['SEPERATOR1']}");
DupUtil::log('START FINAL CLEANUP: ' . @date('h:i:s'));
DupUtil::log("{$GLOBALS['SEPERATOR1']}");

/*CREATE NEW USER LOGIC */
if (strlen($_POST['wp_username']) >= 4 && strlen($_POST['wp_password']) >= 6) {
	
	$newuser_check = mysqli_query($dbh, "SELECT COUNT(*) AS count FROM `{$GLOBALS['FW_TABLEPREFIX']}users` WHERE user_login = '{$_POST['wp_username']}' ");
	$newuser_row   = mysqli_fetch_row($newuser_check);
    $newuser_count = is_null($newuser_row) ? 0 : $newuser_row[0];
	
	if ($newuser_count == 0) {
	
		$newuser_datetime =	@date("Y-m-d H:i:s");
		$newuser_security = mysqli_real_escape_string($dbh, 'a:1:{s:13:"administrator";s:1:"1";}');

		$newuser_test1 = @mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}users` 
			(`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) 
			VALUES ('{$_POST['wp_username']}', MD5('{$_POST['wp_password']}'), '{$_POST['wp_username']}', '', '{$newuser_datetime}', '', '0', '{$_POST['wp_username']}')");

		$newuser_insert_id = mysqli_insert_id($dbh);

		$newuser_test2 = @mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` 
				(`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', '{$GLOBALS['FW_TABLEPREFIX']}capabilities', '{$newuser_security}')");

		$newuser_test3 = @mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` 
				(`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', '{$GLOBALS['FW_TABLEPREFIX']}user_level', '10')");
				
		//Misc Meta-Data Settings:
		@mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', 'rich_editing', 'true')");
		@mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', 'admin_color',  'fresh')");
		@mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', 'nickname', '{$_POST['wp_username']}')");

		if ($newuser_test1 && $newuser_test2 && $newuser_test3) {
			DupUtil::log("NEW WP-ADMIN USER: New username '{$_POST['wp_username']}' was created successfully \n ");
		} else {
			$newuser_warnmsg = "NEW WP-ADMIN USER: Failed to create the user '{$_POST['wp_username']}' \n ";
			$JSON['step2']['warnlist'][] = $newuser_warnmsg;
			DupUtil::log($newuser_warnmsg);
		}			
	} 
	else {
		$newuser_warnmsg = "NEW WP-ADMIN USER: Username '{$_POST['wp_username']}' already exists in the database.  Unable to create new account \n";
		$JSON['step2']['warnlist'][] = $newuser_warnmsg;
		DupUtil::log($newuser_warnmsg);
	}
}

/*UPDATE WP-CONFIG FILE */
$patterns = array("/'WP_HOME',\s*'.*?'/", "/'WP_SITEURL',\s*'.*?'/");

$replace = array("'WP_HOME', " . '\'' . $_POST['url_new'] . '\'',
	"'WP_SITEURL', " . '\'' . $_POST['url_new'] . '\'');

$config_file = @file_get_contents('wp-config.php', true);
$config_file = preg_replace($patterns, $replace, $config_file);
file_put_contents('wp-config.php', $config_file);


//Create Snapshots directory
if (!file_exists(DUPLICATOR_SSDIR_NAME)) {
	mkdir(DUPLICATOR_SSDIR_NAME, 0755);
}
$fp = fopen(DUPLICATOR_SSDIR_NAME . '/index.php', 'w');
fclose($fp);


//WEB CONFIG FILE(S)
$currdata = parse_url($_POST['url_old']);
$newdata = parse_url($_POST['url_new']);
$currpath = DupUtil::add_slash(isset($currdata['path']) ? $currdata['path'] : "");
$newpath = DupUtil::add_slash(isset($newdata['path']) ? $newdata['path'] : "");

if ($currpath != $newpath) {
	DupUtil::log("HTACCESS CHANGES:");
	@copy('.htaccess', '.htaccess.orig');
	@copy('web.config', 'web.config.orig');
	@unlink('.htaccess');
	@unlink('web.config');
	DupUtil::log("created backup of original .htaccess to htaccess.orig and web.config to web.config.orig");

	$tmp_htaccess = <<<HTACCESS
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . {$newpath}index.php [L]
</IfModule>
# END WordPress
HTACCESS;

	file_put_contents('.htaccess', $tmp_htaccess);
	DupUtil::log("created basic .htaccess file.  If using IIS web.config this process will need to be done manually.");
	DupUtil::log("updated .htaccess file.");
} else {
	DupUtil::log("web configuration file was not renamed because the paths did not change.");
}


//===============================
//WARNING TESTS
//===============================
DupUtil::log("\n--------------------------------------");
DupUtil::log("WARNINGS");
DupUtil::log("--------------------------------------");
$config_vars = array('WP_CONTENT_DIR', 'WP_CONTENT_URL', 'WPCACHEHOME', 'COOKIE_DOMAIN', 'WP_SITEURL', 'WP_HOME', 'WP_TEMP_DIR');
$config_found = DupUtil::string_has_value($config_vars, $config_file);

//Files
if ($config_found) {
	$msg = 'WP-CONFIG WARNING: The wp-config.php has one or more of these values "' . implode(", ", $config_vars) . '" which may cause issues please validate these values by opening the file.';
	$JSON['step2']['warnlist'][] = $msg;
	DupUtil::log($msg);
}

//Database
$result = @mysqli_query($dbh, "SELECT option_value FROM `{$GLOBALS['FW_TABLEPREFIX']}options` WHERE option_name IN ('upload_url_path','upload_path')");
if ($result) {
	while ($row = mysqli_fetch_row($result)) {
		if (strlen($row[0])) {
			$msg = "MEDIA SETTINGS WARNING: The table '{$GLOBALS['FW_TABLEPREFIX']}options' has at least one the following values ['upload_url_path','upload_path'] set please validate settings. These settings can be changed in the wp-admin by going to Settings->Media area see 'Uploading Files'";
			$JSON['step2']['warnlist'][] = $msg;
			DupUtil::log($msg);
			break;
		}
	}
}

if (empty($JSON['step2']['warnlist'])) {
	DupUtil::log("No Warnings Found\n");
}

$JSON['step2']['warn_all'] = empty($JSON['step2']['warnlist']) ? 0 : count($JSON['step2']['warnlist']);

mysqli_close($dbh);
@unlink('database.sql');

$ajax2_end = DupUtil::get_microtime();
$ajax2_sum = DupUtil::elapsed_time($ajax2_end, $ajax2_start);
DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log('STEP 2 COMPLETE @ ' . @date('h:i:s') . " - TOTAL RUNTIME: {$ajax2_sum}");
DupUtil::log("{$GLOBALS['SEPERATOR1']}");

$JSON['step2']['pass'] = 1;
error_reporting($ajax2_error_level);
die(json_encode($JSON));
?> <?php
            break;
    }
    @fclose($GLOBALS["LOG_FILE_HANDLE"]);
    die("");
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="robots" content="noindex,nofollow">
        <title>Wordpress Duplicator</title>
        <style type="text/css">

body {font-family:Verdana, Geneva, sans-serif;}
body,td,th {font-size:13px;color:#000;}
fieldset {border:1px solid silver; border-radius:5px; padding:10px}
h3 {margin:1px; padding:1px; font-size:14px;}
a {color:#222}
a:hover{color:gray}
input[type=text] { width:500px; border-radius:3px; height:17px; font-size:12px; border:1px solid silver;}

div#content {border:1px solid #CDCDCD;  width:750px; min-height:550px; margin:auto; margin-top:18px; border-radius:5px; box-shadow: 3px 3px 5px 6px #ccc;}
div#content-inner {padding:10px 30px; min-height:550px}
form.content-form {min-height:550px; position:relative; line-height:17px}
input.readonly {background-color:#efefef;}
i.small {font-size:11px}
span.warning {color:#C04B40}
span.pass {color:#52A34E}


/* ============================
MAIN TEMPLATE */
select#dup-hlp-lnk {border-radius:3px; font-size:11px; margin:3px 5px 0px 0px; background-color:#efefef; border:1px solid silver}
div.dup-logfile-link {float:right; font-weight:normal; font-size:12px}
table.header-wizard {border-top-left-radius:5px; border-top-right-radius:5px; width:100%; box-shadow:0 6px 4px -4px #777;	background-color:#E4E4E4}
.wizard-steps {margin:10px 10px 0px 20px; padding:0px; position:relative; clear:both; font-weight:bold;}
.wizard-steps div {position:relative;}
/* wiz-steps numbers */
.wizard-steps span {display:block;float:left; font-size:11px; text-align:center; width:15px; margin:2px 5px 0px 0px; line-height:15px; color:#ccc; background:#FFF; border:2px solid #999; -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; }
/* wiz-steps default*/
.wizard-steps a { position:relative; display:block; width:auto; height:24px; margin-right:18px; padding:0px 10px 0px 3px; float:left; font-size:11px; line-height:24px; color:#666; background:#F0EEE3; text-decoration:none; text-shadow:1px 1px 1px rgba(255,255,255, 0.8); }
.wizard-steps a:before { width:0px; height:0px; border-top:12px solid #F0EEE3; border-bottom:12px solid #F0EEE3; border-left:12px solid transparent; position:absolute; content:""; top:0px; left:-12px; }
.wizard-steps a:after { width:0; height:0; border-top:12px solid transparent; border-bottom:12px solid transparent; border-left:12px solid #F0EEE3; position:absolute; content:""; top:0px; right:-12px; }
/* wiz-steps completed*/
.wizard-steps .completed-step a {color:#999; background:#000;}
.wizard-steps .completed-step a:before {border-top:12px solid #000; border-bottom:12px solid #000;}
.wizard-steps .completed-step a:after {border-left:12px solid #000;}
.wizard-steps .completed-step span {border:2px solid #777; color:#999;}
/* wiz-steps active*/
.wizard-steps .active-step a {color:#000; background:#999;}
.wizard-steps .active-step a:before {border-top:12px solid #999; border-bottom:12px solid #999;}
.wizard-steps .active-step a:after {border-left:12px solid #999;}
.wizard-steps .active-step span {color:#000; border:2px solid #555;}
div.dup-help-page {padding:5px 0px 0px 5px}
div.dup-help-page fieldset {margin-bottom:25px}
div#dup-main-help h3 {background-color:#dfdfdf; border:1px solid silver; border-radius:5px; padding:3px; margin-bottom:8px}


/* ============================
COMMON VIEW ELEMENTS*/
div#progress-area {padding:5px; margin:150px 0px 0px 0px; text-align:center;}
div#ajaxerr-data {padding:5px; height:440px; width:99%; border:1px solid silver; border-radius:5px; background-color:#efefef; font-size:11px; overflow-y:scroll}
div.title-header {padding:2px; border-bottom:1px solid silver; font-weight:bold; margin-bottom:5px;}


/* ============================
STEP 1 VIEW */
i#dup-step1-sys-req-msg {font-weight:normal; display:block; padding:0px 0px 0px 20px;}
div.circle-pass, div.circle-fail {display:block;width:13px;height:13px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-shadow:0 1px 0 #666;text-align:center;text-decoration:none;box-shadow:1px 1px 2px #000;background:#207D1D;opacity:0.95; display:inline-block;}
div.circle-fail {background:#9A0D1D !important;}
div.warning-info {padding:5px;font-size:11px; color:gray; line-height:12px;font-style:italic; overflow-y:scroll; height:75px; border:1px solid #dfdfdf; background-color:#fff; border-radius:3px}
select#logging {font-size:11px}
table.table-inputs{width: 100%; border: 0px;}
table.table-inputs td{white-space:nowrap; padding:2px;}
table.dbtable-opts td{font-size:12px;}
div#dup-step1-cpanel {}
input#dup-step1-dbconn-btn {font-size:11px; height:20px; border:1px solid gray; border-radius:3px; cursor:pointer}
div.dup-step1-warning-area {padding:5px; font-size:12px; font-weight:normal; font-style:italic;}
div.tryagain{padding-top:50px; text-align:center; width:100%; font-size:14px}

/*Dialog*/
div#dup-step1-dialog-data {height:90%; font-size:11px; padding:5px; line-height:20px; }
td.dup-step1-dialog-data-details {padding:0px 0px 0px 30px; border-radius:4px; line-height:14px; font-size:11px; display:none}
td.dup-step1-dialog-data-details b {width:50px;display:inline-block}
.dup-pass {display:inline-block; color:green;}
.dup-ok {display:inline-block; color:#5860C7;}
.dup-fail {display:inline-block; color:#AF0000;}
hr.dup-dots { border:none; border-top:1px dotted silver; height:1px; width:100%;}
div.error {padding-top:2px;}
div.help {color:#555; font-style:italic; font-size:11px}

/* ============================
STEP 2 VIEW */
div#dup-step2-adv-opts {margin-top:5px; }
div.dup-step2-allnonelinks {font-size:11px; float:right;}

/* ============================
STEP 3 VIEW */
div.dup-step3-final-msg {height:110px; border:1px solid #CDCDCD; padding:8px;font-size:12px; border-radius:5px;box-shadow:0 4px 2px -2px #777;}
div.dup-step3-final-title {color:#BE2323;}
div.dup-step3-connect {font-size:12px; text-align:center; font-style:italic; position:absolute; bottom:10px; padding:10px; width:100%; margin-top:20px}
div#dup-step3-dialog{font-size:12px}
table.dup-step3-report-results,
table.dup-step3-report-errs {border-collapse:collapse; border:1px solid #dfdfdf; }
table.dup-step3-report-errs  td {text-align:center; width:33%}
table.dup-step3-report-results th, table.dup-step3-report-errs th {background-color:#efefef; padding:0px; font-size:12px; padding:0px}
table.dup-step3-report-results td, table.dup-step3-report-errs td {padding:0px; white-space:nowrap; border:1px solid #dfdfdf; text-align:center; font-size:11px}
table.dup-step3-report-results td:first-child {text-align:left; font-weight:bold; padding-left:3px}

div.dup-step3-err-title a {}
div.dup-step3-err-msg {padding:8px;  display:none; border:1px dashed #999; margin:10px 0px 20px 0px; border-radius:5px;}
div.dup-step3-err-msg div.content{padding:5px; font-size:11px; line-height:17px; max-height:125px; overflow-y:scroll; border:1px solid silver; margin:3px;  }
div.dup-step3-err-msg div.info{padding:2px; background-color:#FCFEC5; border:1px solid silver; border-radius:5px; font-size:11px; line-height:16px }

/* ============================
BUTTONS */	
div.dup-footer-buttons {position:absolute; bottom:10px; padding:10px; width:100%; text-align:right;}
div.dup-footer-buttons  input, button, div#dup-step1-sys-req-btn {
	background:#dfdfdf;
	background:-moz-linear-gradient(top,#dfdfdf 0%,#efefef 100%);
	background:-webkit-gradient(linear,left top,left bottom,color-stop(0%,#dfdfdf),color-stop(100%,#efefef));
	background:-webkit-linear-gradient(top,#dfdfdf 0%,#efefef 100%);
	background:-o-linear-gradient(top,#dfdfdf 0%,#efefef 100%);
	background:-ms-linear-gradient(top,#dfdfdf 0%,#efefef 100%);
	background:linear-gradient(top,#dfdfdf 0%,#efefef 100%);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#dfdfdf',endColorstr='#efefef',GradientType=0);
	padding:4px 8px;
	color:#000;
	font-size:12px;
	border-radius:5px;
	-moz-border-radius:5px;
	border:1px solid #999
}
div.dup-footer-buttons input[disabled=disabled]{background-color:#F4F4F4; color:silver; border:1px solid silver;}
div#dup-step1-sys-req-btn{padding:2px 2px 4px 2px; box-shadow: 0 6px 4px -4px #444; width:250px; margin:auto;  font-weight:bold;}
div.dup-footer-buttons  input, button, div#dup-step1-sys-req-btn:hover {cursor:pointer; border:1px solid #000; }
	
	

/*!
 * jQuery UI CSS Framework 1.8.23
 * jquery.org/license
 */

/* Layout helpers
----------------------------------*/
.ui-helper-hidden { display: none; }
.ui-helper-hidden-accessible { position: absolute !important; clip: rect(1px 1px 1px 1px); clip: rect(1px,1px,1px,1px); }
.ui-helper-reset { margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none; }
.ui-helper-clearfix:before, .ui-helper-clearfix:after { content: ""; display: table; }
.ui-helper-clearfix:after { clear: both; }
.ui-helper-clearfix { zoom: 1; }
.ui-helper-zfix { width: 100%; height: 100%; top: 0; left: 0; position: absolute; opacity: 0; filter:Alpha(Opacity=0); }
.ui-state-disabled { cursor: default !important; }

/* Icons
----------------------------------*/
/* states and images */
.ui-icon { display: block;  overflow: hidden; background-repeat: no-repeat; }
/* Overlays */
.ui-widget-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

/* Component containers
----------------------------------*/
.ui-widget { font-family: Verdana,Arial,sans-serif; font-size: 1.1em; }
.ui-widget .ui-widget { font-size: 1em; }
.ui-widget input, .ui-widget select, .ui-widget textarea, .ui-widget button { font-family: Verdana,Arial,sans-serif; font-size: 1em; }
.ui-widget-content { border: 1px solid #aaaaaa; background: #ffffff; color: #222222; }
.ui-widget-content a { color: #222222; }
.ui-widget-header { border: 1px solid #aaaaaa; background: #cccccc; color: #222222; font-weight: bold; }
.ui-widget-header a { color: #222222; }

/* Interaction states/cues
----------------------------------*/
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { border: 1px solid #d3d3d3; background: #e6e6e6; font-weight: normal; color: #555555; }
.ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited { color: #555555; text-decoration: none; }
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { border: 1px solid #999999; background: #dadada; font-weight: normal; color: #212121; }
.ui-state-hover a, .ui-state-hover a:hover { color: #212121; text-decoration: none; }
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active { border: 1px solid #aaaaaa; background: #ffffff; font-weight: normal; color: #212121; }
.ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited { color: #212121; text-decoration: none; }
.ui-widget :active { outline: none; }

.ui-state-highlight, .ui-widget-content .ui-state-highlight, .ui-widget-header .ui-state-highlight  {border: 1px solid #fcefa1; background: #fbf9ee; color: #363636; }
.ui-state-highlight a, .ui-widget-content .ui-state-highlight a,.ui-widget-header .ui-state-highlight a { color: #363636; }
.ui-state-error, .ui-widget-content .ui-state-error, .ui-widget-header .ui-state-error {border: 1px solid #cd0a0a; background: #fef1ec; color: #cd0a0a; }
.ui-state-error a, .ui-widget-content .ui-state-error a, .ui-widget-header .ui-state-error a { color: #cd0a0a; }
.ui-state-error-text, .ui-widget-content .ui-state-error-text, .ui-widget-header .ui-state-error-text { color: #cd0a0a; }
.ui-priority-primary, .ui-widget-content .ui-priority-primary, .ui-widget-header .ui-priority-primary { font-weight: bold; }
.ui-priority-secondary, .ui-widget-content .ui-priority-secondary,  .ui-widget-header .ui-priority-secondary { opacity: .7; filter:Alpha(Opacity=70); font-weight: normal; }
.ui-state-disabled, .ui-widget-content .ui-state-disabled, .ui-widget-header .ui-state-disabled { opacity: .35; filter:Alpha(Opacity=35); background-image: none; }

/* Icons
----------------------------------*/

/* states and images */
.ui-icon { width: 16px; height: 16px; }

/* Misc visuals
----------------------------------*/

/* Corner radius */
.ui-corner-all, .ui-corner-top, .ui-corner-left, .ui-corner-tl { -moz-border-radius-topleft: 4px; -webkit-border-top-left-radius: 4px; -khtml-border-top-left-radius: 4px; border-top-left-radius: 4px; }
.ui-corner-all, .ui-corner-top, .ui-corner-right, .ui-corner-tr { -moz-border-radius-topright: 4px; -webkit-border-top-right-radius: 4px; -khtml-border-top-right-radius: 4px; border-top-right-radius: 4px; }
.ui-corner-all, .ui-corner-bottom, .ui-corner-left, .ui-corner-bl { -moz-border-radius-bottomleft: 4px; -webkit-border-bottom-left-radius: 4px; -khtml-border-bottom-left-radius: 4px; border-bottom-left-radius: 4px; }
.ui-corner-all, .ui-corner-bottom, .ui-corner-right, .ui-corner-br { -moz-border-radius-bottomright: 4px; -webkit-border-bottom-right-radius: 4px; -khtml-border-bottom-right-radius: 4px; border-bottom-right-radius: 4px; }

/* Overlays */
.ui-widget-overlay { background: #aaaaaa; opacity: .30;filter:Alpha(Opacity=30); }
.ui-widget-shadow { margin: -8px 0 0 -8px; padding: 8px; background: #aaaaaa; opacity: .30;filter:Alpha(Opacity=30); -moz-border-radius: 8px; -khtml-border-radius: 8px; -webkit-border-radius: 8px; border-radius: 8px; }

/*!
 * jQuery UI Resizable 1.8.23
 */
.ui-resizable { position: relative;}
.ui-resizable-handle { position: absolute;font-size: 0.1px; display: block; }
.ui-resizable-disabled .ui-resizable-handle, .ui-resizable-autohide .ui-resizable-handle { display: none; }
.ui-resizable-n { cursor: n-resize; height: 7px; width: 100%; top: -5px; left: 0; }
.ui-resizable-s { cursor: s-resize; height: 7px; width: 100%; bottom: -5px; left: 0; }
.ui-resizable-e { cursor: e-resize; width: 7px; right: -5px; top: 0; height: 100%; }
.ui-resizable-w { cursor: w-resize; width: 7px; left: -5px; top: 0; height: 100%; }
.ui-resizable-se { cursor: se-resize; width: 12px; height: 12px; right: 1px; bottom: 1px; }
.ui-resizable-sw { cursor: sw-resize; width: 9px; height: 9px; left: -5px; bottom: -5px; }
.ui-resizable-nw { cursor: nw-resize; width: 9px; height: 9px; left: -5px; top: -5px; }
.ui-resizable-ne { cursor: ne-resize; width: 9px; height: 9px; right: -5px; top: -5px;}
/*!
 * jQuery UI Selectable 1.8.23
 */
.ui-selectable-helper { position: absolute; z-index: 100; border:1px dotted black; }

/*!
 * jQuery UI Dialog 1.8.23
 */
.ui-dialog { position: absolute; padding: .2em; width: 300px; overflow: hidden; }
.ui-dialog .ui-dialog-titlebar { padding: .4em 1em; position: relative;  }
.ui-dialog .ui-dialog-title { float: left; margin: .1em 16px .1em 0; } 
.ui-dialog .ui-dialog-titlebar-close { position: absolute; right: .3em; top: 50%; width: 19px; margin: -10px 0 0 0; padding: 1px; height: 18px; }
.ui-dialog .ui-dialog-titlebar-close span { display: block; margin: 1px; }
.ui-dialog .ui-dialog-titlebar-close:hover, .ui-dialog .ui-dialog-titlebar-close:focus { padding: 0; }
.ui-dialog .ui-dialog-content { position: relative; border: 0; padding: .5em 1em; background: none; overflow: auto; zoom: 1; }
.ui-dialog .ui-dialog-buttonpane { text-align: left; border-width: 1px 0 0 0; background-image: none; margin: .5em 0 0 0; padding: .3em 1em .5em .4em; }
.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset { float: right; }
.ui-dialog .ui-dialog-buttonpane button { margin: .5em .4em .5em 0; cursor: pointer; }
.ui-dialog .ui-resizable-se { width: 14px; height: 14px; right: 3px; bottom: 3px; }
.ui-draggable .ui-dialog-titlebar { cursor: move; }

/*!
 * jQuery UI Progressbar 1.8.23
 */
.ui-progressbar { height:2em; text-align: left; overflow: hidden; }
.ui-progressbar .ui-progressbar-value {margin: -1px; height:100%; }

/*!
 * password indicator
 */
.top_testresult{font-weight: bold;	font-size:11px; color:#222;	padding:1px 1px 1px 4px; margin:4px 0px 0px 0px; width:495px; dislay:inline-block}
.top_testresult span{margin:0;}
.top_shortPass{background:#edabab; border:1px solid #bc0000;display:block;}
.top_badPass{background:#edabab;border:1px solid #bc0000;display:block;}
.top_goodPass{background:#ffffe0; border:1px solid #e6db55;	display:block;}
.top_strongPass{background:#d3edab;	border:1px solid #73bc00; display:block;}



/* ============================
CUSTOME OVERIDE */
.ui-textclose {display:inline-block; padding:0px 0px 0px 3px; line-height:15px;}
a.ui-dialog-titlebar-close {text-decoration:none}

</style>
        <script type="text/javascript">
/*! jQuery v1.8.2 jquery.com | jquery.org/license */
(function(a,b){function G(a){var b=F[a]={};return p.each(a.split(s),function(a,c){b[c]=!0}),b}function J(a,c,d){if(d===b&&a.nodeType===1){var e="data-"+c.replace(I,"-$1").toLowerCase();d=a.getAttribute(e);if(typeof d=="string"){try{d=d==="true"?!0:d==="false"?!1:d==="null"?null:+d+""===d?+d:H.test(d)?p.parseJSON(d):d}catch(f){}p.data(a,c,d)}else d=b}return d}function K(a){var b;for(b in a){if(b==="data"&&p.isEmptyObject(a[b]))continue;if(b!=="toJSON")return!1}return!0}function ba(){return!1}function bb(){return!0}function bh(a){return!a||!a.parentNode||a.parentNode.nodeType===11}function bi(a,b){do a=a[b];while(a&&a.nodeType!==1);return a}function bj(a,b,c){b=b||0;if(p.isFunction(b))return p.grep(a,function(a,d){var e=!!b.call(a,d,a);return e===c});if(b.nodeType)return p.grep(a,function(a,d){return a===b===c});if(typeof b=="string"){var d=p.grep(a,function(a){return a.nodeType===1});if(be.test(b))return p.filter(b,d,!c);b=p.filter(b,d)}return p.grep(a,function(a,d){return p.inArray(a,b)>=0===c})}function bk(a){var b=bl.split("|"),c=a.createDocumentFragment();if(c.createElement)while(b.length)c.createElement(b.pop());return c}function bC(a,b){return a.getElementsByTagName(b)[0]||a.appendChild(a.ownerDocument.createElement(b))}function bD(a,b){if(b.nodeType!==1||!p.hasData(a))return;var c,d,e,f=p._data(a),g=p._data(b,f),h=f.events;if(h){delete g.handle,g.events={};for(c in h)for(d=0,e=h[c].length;d<e;d++)p.event.add(b,c,h[c][d])}g.data&&(g.data=p.extend({},g.data))}function bE(a,b){var c;if(b.nodeType!==1)return;b.clearAttributes&&b.clearAttributes(),b.mergeAttributes&&b.mergeAttributes(a),c=b.nodeName.toLowerCase(),c==="object"?(b.parentNode&&(b.outerHTML=a.outerHTML),p.support.html5Clone&&a.innerHTML&&!p.trim(b.innerHTML)&&(b.innerHTML=a.innerHTML)):c==="input"&&bv.test(a.type)?(b.defaultChecked=b.checked=a.checked,b.value!==a.value&&(b.value=a.value)):c==="option"?b.selected=a.defaultSelected:c==="input"||c==="textarea"?b.defaultValue=a.defaultValue:c==="script"&&b.text!==a.text&&(b.text=a.text),b.removeAttribute(p.expando)}function bF(a){return typeof a.getElementsByTagName!="undefined"?a.getElementsByTagName("*"):typeof a.querySelectorAll!="undefined"?a.querySelectorAll("*"):[]}function bG(a){bv.test(a.type)&&(a.defaultChecked=a.checked)}function bY(a,b){if(b in a)return b;var c=b.charAt(0).toUpperCase()+b.slice(1),d=b,e=bW.length;while(e--){b=bW[e]+c;if(b in a)return b}return d}function bZ(a,b){return a=b||a,p.css(a,"display")==="none"||!p.contains(a.ownerDocument,a)}function b$(a,b){var c,d,e=[],f=0,g=a.length;for(;f<g;f++){c=a[f];if(!c.style)continue;e[f]=p._data(c,"olddisplay"),b?(!e[f]&&c.style.display==="none"&&(c.style.display=""),c.style.display===""&&bZ(c)&&(e[f]=p._data(c,"olddisplay",cc(c.nodeName)))):(d=bH(c,"display"),!e[f]&&d!=="none"&&p._data(c,"olddisplay",d))}for(f=0;f<g;f++){c=a[f];if(!c.style)continue;if(!b||c.style.display==="none"||c.style.display==="")c.style.display=b?e[f]||"":"none"}return a}function b_(a,b,c){var d=bP.exec(b);return d?Math.max(0,d[1]-(c||0))+(d[2]||"px"):b}function ca(a,b,c,d){var e=c===(d?"border":"content")?4:b==="width"?1:0,f=0;for(;e<4;e+=2)c==="margin"&&(f+=p.css(a,c+bV[e],!0)),d?(c==="content"&&(f-=parseFloat(bH(a,"padding"+bV[e]))||0),c!=="margin"&&(f-=parseFloat(bH(a,"border"+bV[e]+"Width"))||0)):(f+=parseFloat(bH(a,"padding"+bV[e]))||0,c!=="padding"&&(f+=parseFloat(bH(a,"border"+bV[e]+"Width"))||0));return f}function cb(a,b,c){var d=b==="width"?a.offsetWidth:a.offsetHeight,e=!0,f=p.support.boxSizing&&p.css(a,"boxSizing")==="border-box";if(d<=0||d==null){d=bH(a,b);if(d<0||d==null)d=a.style[b];if(bQ.test(d))return d;e=f&&(p.support.boxSizingReliable||d===a.style[b]),d=parseFloat(d)||0}return d+ca(a,b,c||(f?"border":"content"),e)+"px"}function cc(a){if(bS[a])return bS[a];var b=p("<"+a+">").appendTo(e.body),c=b.css("display");b.remove();if(c==="none"||c===""){bI=e.body.appendChild(bI||p.extend(e.createElement("iframe"),{frameBorder:0,width:0,height:0}));if(!bJ||!bI.createElement)bJ=(bI.contentWindow||bI.contentDocument).document,bJ.write("<!doctype html><html><body>"),bJ.close();b=bJ.body.appendChild(bJ.createElement(a)),c=bH(b,"display"),e.body.removeChild(bI)}return bS[a]=c,c}function ci(a,b,c,d){var e;if(p.isArray(b))p.each(b,function(b,e){c||ce.test(a)?d(a,e):ci(a+"["+(typeof e=="object"?b:"")+"]",e,c,d)});else if(!c&&p.type(b)==="object")for(e in b)ci(a+"["+e+"]",b[e],c,d);else d(a,b)}function cz(a){return function(b,c){typeof b!="string"&&(c=b,b="*");var d,e,f,g=b.toLowerCase().split(s),h=0,i=g.length;if(p.isFunction(c))for(;h<i;h++)d=g[h],f=/^\+/.test(d),f&&(d=d.substr(1)||"*"),e=a[d]=a[d]||[],e[f?"unshift":"push"](c)}}function cA(a,c,d,e,f,g){f=f||c.dataTypes[0],g=g||{},g[f]=!0;var h,i=a[f],j=0,k=i?i.length:0,l=a===cv;for(;j<k&&(l||!h);j++)h=i[j](c,d,e),typeof h=="string"&&(!l||g[h]?h=b:(c.dataTypes.unshift(h),h=cA(a,c,d,e,h,g)));return(l||!h)&&!g["*"]&&(h=cA(a,c,d,e,"*",g)),h}function cB(a,c){var d,e,f=p.ajaxSettings.flatOptions||{};for(d in c)c[d]!==b&&((f[d]?a:e||(e={}))[d]=c[d]);e&&p.extend(!0,a,e)}function cC(a,c,d){var e,f,g,h,i=a.contents,j=a.dataTypes,k=a.responseFields;for(f in k)f in d&&(c[k[f]]=d[f]);while(j[0]==="*")j.shift(),e===b&&(e=a.mimeType||c.getResponseHeader("content-type"));if(e)for(f in i)if(i[f]&&i[f].test(e)){j.unshift(f);break}if(j[0]in d)g=j[0];else{for(f in d){if(!j[0]||a.converters[f+" "+j[0]]){g=f;break}h||(h=f)}g=g||h}if(g)return g!==j[0]&&j.unshift(g),d[g]}function cD(a,b){var c,d,e,f,g=a.dataTypes.slice(),h=g[0],i={},j=0;a.dataFilter&&(b=a.dataFilter(b,a.dataType));if(g[1])for(c in a.converters)i[c.toLowerCase()]=a.converters[c];for(;e=g[++j];)if(e!=="*"){if(h!=="*"&&h!==e){c=i[h+" "+e]||i["* "+e];if(!c)for(d in i){f=d.split(" ");if(f[1]===e){c=i[h+" "+f[0]]||i["* "+f[0]];if(c){c===!0?c=i[d]:i[d]!==!0&&(e=f[0],g.splice(j--,0,e));break}}}if(c!==!0)if(c&&a["throws"])b=c(b);else try{b=c(b)}catch(k){return{state:"parsererror",error:c?k:"No conversion from "+h+" to "+e}}}h=e}return{state:"success",data:b}}function cL(){try{return new a.XMLHttpRequest}catch(b){}}function cM(){try{return new a.ActiveXObject("Microsoft.XMLHTTP")}catch(b){}}function cU(){return setTimeout(function(){cN=b},0),cN=p.now()}function cV(a,b){p.each(b,function(b,c){var d=(cT[b]||[]).concat(cT["*"]),e=0,f=d.length;for(;e<f;e++)if(d[e].call(a,b,c))return})}function cW(a,b,c){var d,e=0,f=0,g=cS.length,h=p.Deferred().always(function(){delete i.elem}),i=function(){var b=cN||cU(),c=Math.max(0,j.startTime+j.duration-b),d=1-(c/j.duration||0),e=0,f=j.tweens.length;for(;e<f;e++)j.tweens[e].run(d);return h.notifyWith(a,[j,d,c]),d<1&&f?c:(h.resolveWith(a,[j]),!1)},j=h.promise({elem:a,props:p.extend({},b),opts:p.extend(!0,{specialEasing:{}},c),originalProperties:b,originalOptions:c,startTime:cN||cU(),duration:c.duration,tweens:[],createTween:function(b,c,d){var e=p.Tween(a,j.opts,b,c,j.opts.specialEasing[b]||j.opts.easing);return j.tweens.push(e),e},stop:function(b){var c=0,d=b?j.tweens.length:0;for(;c<d;c++)j.tweens[c].run(1);return b?h.resolveWith(a,[j,b]):h.rejectWith(a,[j,b]),this}}),k=j.props;cX(k,j.opts.specialEasing);for(;e<g;e++){d=cS[e].call(j,a,k,j.opts);if(d)return d}return cV(j,k),p.isFunction(j.opts.start)&&j.opts.start.call(a,j),p.fx.timer(p.extend(i,{anim:j,queue:j.opts.queue,elem:a})),j.progress(j.opts.progress).done(j.opts.done,j.opts.complete).fail(j.opts.fail).always(j.opts.always)}function cX(a,b){var c,d,e,f,g;for(c in a){d=p.camelCase(c),e=b[d],f=a[c],p.isArray(f)&&(e=f[1],f=a[c]=f[0]),c!==d&&(a[d]=f,delete a[c]),g=p.cssHooks[d];if(g&&"expand"in g){f=g.expand(f),delete a[d];for(c in f)c in a||(a[c]=f[c],b[c]=e)}else b[d]=e}}function cY(a,b,c){var d,e,f,g,h,i,j,k,l=this,m=a.style,n={},o=[],q=a.nodeType&&bZ(a);c.queue||(j=p._queueHooks(a,"fx"),j.unqueued==null&&(j.unqueued=0,k=j.empty.fire,j.empty.fire=function(){j.unqueued||k()}),j.unqueued++,l.always(function(){l.always(function(){j.unqueued--,p.queue(a,"fx").length||j.empty.fire()})})),a.nodeType===1&&("height"in b||"width"in b)&&(c.overflow=[m.overflow,m.overflowX,m.overflowY],p.css(a,"display")==="inline"&&p.css(a,"float")==="none"&&(!p.support.inlineBlockNeedsLayout||cc(a.nodeName)==="inline"?m.display="inline-block":m.zoom=1)),c.overflow&&(m.overflow="hidden",p.support.shrinkWrapBlocks||l.done(function(){m.overflow=c.overflow[0],m.overflowX=c.overflow[1],m.overflowY=c.overflow[2]}));for(d in b){f=b[d];if(cP.exec(f)){delete b[d];if(f===(q?"hide":"show"))continue;o.push(d)}}g=o.length;if(g){h=p._data(a,"fxshow")||p._data(a,"fxshow",{}),q?p(a).show():l.done(function(){p(a).hide()}),l.done(function(){var b;p.removeData(a,"fxshow",!0);for(b in n)p.style(a,b,n[b])});for(d=0;d<g;d++)e=o[d],i=l.createTween(e,q?h[e]:0),n[e]=h[e]||p.style(a,e),e in h||(h[e]=i.start,q&&(i.end=i.start,i.start=e==="width"||e==="height"?1:0))}}function cZ(a,b,c,d,e){return new cZ.prototype.init(a,b,c,d,e)}function c$(a,b){var c,d={height:a},e=0;b=b?1:0;for(;e<4;e+=2-b)c=bV[e],d["margin"+c]=d["padding"+c]=a;return b&&(d.opacity=d.width=a),d}function da(a){return p.isWindow(a)?a:a.nodeType===9?a.defaultView||a.parentWindow:!1}var c,d,e=a.document,f=a.location,g=a.navigator,h=a.jQuery,i=a.$,j=Array.prototype.push,k=Array.prototype.slice,l=Array.prototype.indexOf,m=Object.prototype.toString,n=Object.prototype.hasOwnProperty,o=String.prototype.trim,p=function(a,b){return new p.fn.init(a,b,c)},q=/[\-+]?(?:\d*\.|)\d+(?:[eE][\-+]?\d+|)/.source,r=/\S/,s=/\s+/,t=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,u=/^(?:[^#<]*(<[\w\W]+>)[^>]*$|#([\w\-]*)$)/,v=/^<(\w+)\s*\/?>(?:<\/\1>|)$/,w=/^[\],:{}\s]*$/,x=/(?:^|:|,)(?:\s*\[)+/g,y=/\\(?:["\\\/bfnrt]|u[\da-fA-F]{4})/g,z=/"[^"\\\r\n]*"|true|false|null|-?(?:\d\d*\.|)\d+(?:[eE][\-+]?\d+|)/g,A=/^-ms-/,B=/-([\da-z])/gi,C=function(a,b){return(b+"").toUpperCase()},D=function(){e.addEventListener?(e.removeEventListener("DOMContentLoaded",D,!1),p.ready()):e.readyState==="complete"&&(e.detachEvent("onreadystatechange",D),p.ready())},E={};p.fn=p.prototype={constructor:p,init:function(a,c,d){var f,g,h,i;if(!a)return this;if(a.nodeType)return this.context=this[0]=a,this.length=1,this;if(typeof a=="string"){a.charAt(0)==="<"&&a.charAt(a.length-1)===">"&&a.length>=3?f=[null,a,null]:f=u.exec(a);if(f&&(f[1]||!c)){if(f[1])return c=c instanceof p?c[0]:c,i=c&&c.nodeType?c.ownerDocument||c:e,a=p.parseHTML(f[1],i,!0),v.test(f[1])&&p.isPlainObject(c)&&this.attr.call(a,c,!0),p.merge(this,a);g=e.getElementById(f[2]);if(g&&g.parentNode){if(g.id!==f[2])return d.find(a);this.length=1,this[0]=g}return this.context=e,this.selector=a,this}return!c||c.jquery?(c||d).find(a):this.constructor(c).find(a)}return p.isFunction(a)?d.ready(a):(a.selector!==b&&(this.selector=a.selector,this.context=a.context),p.makeArray(a,this))},selector:"",jquery:"1.8.2",length:0,size:function(){return this.length},toArray:function(){return k.call(this)},get:function(a){return a==null?this.toArray():a<0?this[this.length+a]:this[a]},pushStack:function(a,b,c){var d=p.merge(this.constructor(),a);return d.prevObject=this,d.context=this.context,b==="find"?d.selector=this.selector+(this.selector?" ":"")+c:b&&(d.selector=this.selector+"."+b+"("+c+")"),d},each:function(a,b){return p.each(this,a,b)},ready:function(a){return p.ready.promise().done(a),this},eq:function(a){return a=+a,a===-1?this.slice(a):this.slice(a,a+1)},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},slice:function(){return this.pushStack(k.apply(this,arguments),"slice",k.call(arguments).join(","))},map:function(a){return this.pushStack(p.map(this,function(b,c){return a.call(b,c,b)}))},end:function(){return this.prevObject||this.constructor(null)},push:j,sort:[].sort,splice:[].splice},p.fn.init.prototype=p.fn,p.extend=p.fn.extend=function(){var a,c,d,e,f,g,h=arguments[0]||{},i=1,j=arguments.length,k=!1;typeof h=="boolean"&&(k=h,h=arguments[1]||{},i=2),typeof h!="object"&&!p.isFunction(h)&&(h={}),j===i&&(h=this,--i);for(;i<j;i++)if((a=arguments[i])!=null)for(c in a){d=h[c],e=a[c];if(h===e)continue;k&&e&&(p.isPlainObject(e)||(f=p.isArray(e)))?(f?(f=!1,g=d&&p.isArray(d)?d:[]):g=d&&p.isPlainObject(d)?d:{},h[c]=p.extend(k,g,e)):e!==b&&(h[c]=e)}return h},p.extend({noConflict:function(b){return a.$===p&&(a.$=i),b&&a.jQuery===p&&(a.jQuery=h),p},isReady:!1,readyWait:1,holdReady:function(a){a?p.readyWait++:p.ready(!0)},ready:function(a){if(a===!0?--p.readyWait:p.isReady)return;if(!e.body)return setTimeout(p.ready,1);p.isReady=!0;if(a!==!0&&--p.readyWait>0)return;d.resolveWith(e,[p]),p.fn.trigger&&p(e).trigger("ready").off("ready")},isFunction:function(a){return p.type(a)==="function"},isArray:Array.isArray||function(a){return p.type(a)==="array"},isWindow:function(a){return a!=null&&a==a.window},isNumeric:function(a){return!isNaN(parseFloat(a))&&isFinite(a)},type:function(a){return a==null?String(a):E[m.call(a)]||"object"},isPlainObject:function(a){if(!a||p.type(a)!=="object"||a.nodeType||p.isWindow(a))return!1;try{if(a.constructor&&!n.call(a,"constructor")&&!n.call(a.constructor.prototype,"isPrototypeOf"))return!1}catch(c){return!1}var d;for(d in a);return d===b||n.call(a,d)},isEmptyObject:function(a){var b;for(b in a)return!1;return!0},error:function(a){throw new Error(a)},parseHTML:function(a,b,c){var d;return!a||typeof a!="string"?null:(typeof b=="boolean"&&(c=b,b=0),b=b||e,(d=v.exec(a))?[b.createElement(d[1])]:(d=p.buildFragment([a],b,c?null:[]),p.merge([],(d.cacheable?p.clone(d.fragment):d.fragment).childNodes)))},parseJSON:function(b){if(!b||typeof b!="string")return null;b=p.trim(b);if(a.JSON&&a.JSON.parse)return a.JSON.parse(b);if(w.test(b.replace(y,"@").replace(z,"]").replace(x,"")))return(new Function("return "+b))();p.error("Invalid JSON: "+b)},parseXML:function(c){var d,e;if(!c||typeof c!="string")return null;try{a.DOMParser?(e=new DOMParser,d=e.parseFromString(c,"text/xml")):(d=new ActiveXObject("Microsoft.XMLDOM"),d.async="false",d.loadXML(c))}catch(f){d=b}return(!d||!d.documentElement||d.getElementsByTagName("parsererror").length)&&p.error("Invalid XML: "+c),d},noop:function(){},globalEval:function(b){b&&r.test(b)&&(a.execScript||function(b){a.eval.call(a,b)})(b)},camelCase:function(a){return a.replace(A,"ms-").replace(B,C)},nodeName:function(a,b){return a.nodeName&&a.nodeName.toLowerCase()===b.toLowerCase()},each:function(a,c,d){var e,f=0,g=a.length,h=g===b||p.isFunction(a);if(d){if(h){for(e in a)if(c.apply(a[e],d)===!1)break}else for(;f<g;)if(c.apply(a[f++],d)===!1)break}else if(h){for(e in a)if(c.call(a[e],e,a[e])===!1)break}else for(;f<g;)if(c.call(a[f],f,a[f++])===!1)break;return a},trim:o&&!o.call("? ")?function(a){return a==null?"":o.call(a)}:function(a){return a==null?"":(a+"").replace(t,"")},makeArray:function(a,b){var c,d=b||[];return a!=null&&(c=p.type(a),a.length==null||c==="string"||c==="function"||c==="regexp"||p.isWindow(a)?j.call(d,a):p.merge(d,a)),d},inArray:function(a,b,c){var d;if(b){if(l)return l.call(b,a,c);d=b.length,c=c?c<0?Math.max(0,d+c):c:0;for(;c<d;c++)if(c in b&&b[c]===a)return c}return-1},merge:function(a,c){var d=c.length,e=a.length,f=0;if(typeof d=="number")for(;f<d;f++)a[e++]=c[f];else while(c[f]!==b)a[e++]=c[f++];return a.length=e,a},grep:function(a,b,c){var d,e=[],f=0,g=a.length;c=!!c;for(;f<g;f++)d=!!b(a[f],f),c!==d&&e.push(a[f]);return e},map:function(a,c,d){var e,f,g=[],h=0,i=a.length,j=a instanceof p||i!==b&&typeof i=="number"&&(i>0&&a[0]&&a[i-1]||i===0||p.isArray(a));if(j)for(;h<i;h++)e=c(a[h],h,d),e!=null&&(g[g.length]=e);else for(f in a)e=c(a[f],f,d),e!=null&&(g[g.length]=e);return g.concat.apply([],g)},guid:1,proxy:function(a,c){var d,e,f;return typeof c=="string"&&(d=a[c],c=a,a=d),p.isFunction(a)?(e=k.call(arguments,2),f=function(){return a.apply(c,e.concat(k.call(arguments)))},f.guid=a.guid=a.guid||p.guid++,f):b},access:function(a,c,d,e,f,g,h){var i,j=d==null,k=0,l=a.length;if(d&&typeof d=="object"){for(k in d)p.access(a,c,k,d[k],1,g,e);f=1}else if(e!==b){i=h===b&&p.isFunction(e),j&&(i?(i=c,c=function(a,b,c){return i.call(p(a),c)}):(c.call(a,e),c=null));if(c)for(;k<l;k++)c(a[k],d,i?e.call(a[k],k,c(a[k],d)):e,h);f=1}return f?a:j?c.call(a):l?c(a[0],d):g},now:function(){return(new Date).getTime()}}),p.ready.promise=function(b){if(!d){d=p.Deferred();if(e.readyState==="complete")setTimeout(p.ready,1);else if(e.addEventListener)e.addEventListener("DOMContentLoaded",D,!1),a.addEventListener("load",p.ready,!1);else{e.attachEvent("onreadystatechange",D),a.attachEvent("onload",p.ready);var c=!1;try{c=a.frameElement==null&&e.documentElement}catch(f){}c&&c.doScroll&&function g(){if(!p.isReady){try{c.doScroll("left")}catch(a){return setTimeout(g,50)}p.ready()}}()}}return d.promise(b)},p.each("Boolean Number String Function Array Date RegExp Object".split(" "),function(a,b){E["[object "+b+"]"]=b.toLowerCase()}),c=p(e);var F={};p.Callbacks=function(a){a=typeof a=="string"?F[a]||G(a):p.extend({},a);var c,d,e,f,g,h,i=[],j=!a.once&&[],k=function(b){c=a.memory&&b,d=!0,h=f||0,f=0,g=i.length,e=!0;for(;i&&h<g;h++)if(i[h].apply(b[0],b[1])===!1&&a.stopOnFalse){c=!1;break}e=!1,i&&(j?j.length&&k(j.shift()):c?i=[]:l.disable())},l={add:function(){if(i){var b=i.length;(function d(b){p.each(b,function(b,c){var e=p.type(c);e==="function"&&(!a.unique||!l.has(c))?i.push(c):c&&c.length&&e!=="string"&&d(c)})})(arguments),e?g=i.length:c&&(f=b,k(c))}return this},remove:function(){return i&&p.each(arguments,function(a,b){var c;while((c=p.inArray(b,i,c))>-1)i.splice(c,1),e&&(c<=g&&g--,c<=h&&h--)}),this},has:function(a){return p.inArray(a,i)>-1},empty:function(){return i=[],this},disable:function(){return i=j=c=b,this},disabled:function(){return!i},lock:function(){return j=b,c||l.disable(),this},locked:function(){return!j},fireWith:function(a,b){return b=b||[],b=[a,b.slice?b.slice():b],i&&(!d||j)&&(e?j.push(b):k(b)),this},fire:function(){return l.fireWith(this,arguments),this},fired:function(){return!!d}};return l},p.extend({Deferred:function(a){var b=[["resolve","done",p.Callbacks("once memory"),"resolved"],["reject","fail",p.Callbacks("once memory"),"rejected"],["notify","progress",p.Callbacks("memory")]],c="pending",d={state:function(){return c},always:function(){return e.done(arguments).fail(arguments),this},then:function(){var a=arguments;return p.Deferred(function(c){p.each(b,function(b,d){var f=d[0],g=a[b];e[d[1]](p.isFunction(g)?function(){var a=g.apply(this,arguments);a&&p.isFunction(a.promise)?a.promise().done(c.resolve).fail(c.reject).progress(c.notify):c[f+"With"](this===e?c:this,[a])}:c[f])}),a=null}).promise()},promise:function(a){return a!=null?p.extend(a,d):d}},e={};return d.pipe=d.then,p.each(b,function(a,f){var g=f[2],h=f[3];d[f[1]]=g.add,h&&g.add(function(){c=h},b[a^1][2].disable,b[2][2].lock),e[f[0]]=g.fire,e[f[0]+"With"]=g.fireWith}),d.promise(e),a&&a.call(e,e),e},when:function(a){var b=0,c=k.call(arguments),d=c.length,e=d!==1||a&&p.isFunction(a.promise)?d:0,f=e===1?a:p.Deferred(),g=function(a,b,c){return function(d){b[a]=this,c[a]=arguments.length>1?k.call(arguments):d,c===h?f.notifyWith(b,c):--e||f.resolveWith(b,c)}},h,i,j;if(d>1){h=new Array(d),i=new Array(d),j=new Array(d);for(;b<d;b++)c[b]&&p.isFunction(c[b].promise)?c[b].promise().done(g(b,j,c)).fail(f.reject).progress(g(b,i,h)):--e}return e||f.resolveWith(j,c),f.promise()}}),p.support=function(){var b,c,d,f,g,h,i,j,k,l,m,n=e.createElement("div");n.setAttribute("className","t"),n.innerHTML="  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>",c=n.getElementsByTagName("*"),d=n.getElementsByTagName("a")[0],d.style.cssText="top:1px;float:left;opacity:.5";if(!c||!c.length)return{};f=e.createElement("select"),g=f.appendChild(e.createElement("option")),h=n.getElementsByTagName("input")[0],b={leadingWhitespace:n.firstChild.nodeType===3,tbody:!n.getElementsByTagName("tbody").length,htmlSerialize:!!n.getElementsByTagName("link").length,style:/top/.test(d.getAttribute("style")),hrefNormalized:d.getAttribute("href")==="/a",opacity:/^0.5/.test(d.style.opacity),cssFloat:!!d.style.cssFloat,checkOn:h.value==="on",optSelected:g.selected,getSetAttribute:n.className!=="t",enctype:!!e.createElement("form").enctype,html5Clone:e.createElement("nav").cloneNode(!0).outerHTML!=="<:nav></:nav>",boxModel:e.compatMode==="CSS1Compat",submitBubbles:!0,changeBubbles:!0,focusinBubbles:!1,deleteExpando:!0,noCloneEvent:!0,inlineBlockNeedsLayout:!1,shrinkWrapBlocks:!1,reliableMarginRight:!0,boxSizingReliable:!0,pixelPosition:!1},h.checked=!0,b.noCloneChecked=h.cloneNode(!0).checked,f.disabled=!0,b.optDisabled=!g.disabled;try{delete n.test}catch(o){b.deleteExpando=!1}!n.addEventListener&&n.attachEvent&&n.fireEvent&&(n.attachEvent("onclick",m=function(){b.noCloneEvent=!1}),n.cloneNode(!0).fireEvent("onclick"),n.detachEvent("onclick",m)),h=e.createElement("input"),h.value="t",h.setAttribute("type","radio"),b.radioValue=h.value==="t",h.setAttribute("checked","checked"),h.setAttribute("name","t"),n.appendChild(h),i=e.createDocumentFragment(),i.appendChild(n.lastChild),b.checkClone=i.cloneNode(!0).cloneNode(!0).lastChild.checked,b.appendChecked=h.checked,i.removeChild(h),i.appendChild(n);if(n.attachEvent)for(k in{submit:!0,change:!0,focusin:!0})j="on"+k,l=j in n,l||(n.setAttribute(j,"return;"),l=typeof n[j]=="function"),b[k+"Bubbles"]=l;return p(function(){var c,d,f,g,h="padding:0;margin:0;border:0;display:block;overflow:hidden;",i=e.getElementsByTagName("body")[0];if(!i)return;c=e.createElement("div"),c.style.cssText="visibility:hidden;border:0;width:0;height:0;position:static;top:0;margin-top:1px",i.insertBefore(c,i.firstChild),d=e.createElement("div"),c.appendChild(d),d.innerHTML="<table><tr><td></td><td>t</td></tr></table>",f=d.getElementsByTagName("td"),f[0].style.cssText="padding:0;margin:0;border:0;display:none",l=f[0].offsetHeight===0,f[0].style.display="",f[1].style.display="none",b.reliableHiddenOffsets=l&&f[0].offsetHeight===0,d.innerHTML="",d.style.cssText="box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%;",b.boxSizing=d.offsetWidth===4,b.doesNotIncludeMarginInBodyOffset=i.offsetTop!==1,a.getComputedStyle&&(b.pixelPosition=(a.getComputedStyle(d,null)||{}).top!=="1%",b.boxSizingReliable=(a.getComputedStyle(d,null)||{width:"4px"}).width==="4px",g=e.createElement("div"),g.style.cssText=d.style.cssText=h,g.style.marginRight=g.style.width="0",d.style.width="1px",d.appendChild(g),b.reliableMarginRight=!parseFloat((a.getComputedStyle(g,null)||{}).marginRight)),typeof d.style.zoom!="undefined"&&(d.innerHTML="",d.style.cssText=h+"width:1px;padding:1px;display:inline;zoom:1",b.inlineBlockNeedsLayout=d.offsetWidth===3,d.style.display="block",d.style.overflow="visible",d.innerHTML="<div></div>",d.firstChild.style.width="5px",b.shrinkWrapBlocks=d.offsetWidth!==3,c.style.zoom=1),i.removeChild(c),c=d=f=g=null}),i.removeChild(n),c=d=f=g=h=i=n=null,b}();var H=/(?:\{[\s\S]*\}|\[[\s\S]*\])$/,I=/([A-Z])/g;p.extend({cache:{},deletedIds:[],uuid:0,expando:"jQuery"+(p.fn.jquery+Math.random()).replace(/\D/g,""),noData:{embed:!0,object:"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",applet:!0},hasData:function(a){return a=a.nodeType?p.cache[a[p.expando]]:a[p.expando],!!a&&!K(a)},data:function(a,c,d,e){if(!p.acceptData(a))return;var f,g,h=p.expando,i=typeof c=="string",j=a.nodeType,k=j?p.cache:a,l=j?a[h]:a[h]&&h;if((!l||!k[l]||!e&&!k[l].data)&&i&&d===b)return;l||(j?a[h]=l=p.deletedIds.pop()||p.guid++:l=h),k[l]||(k[l]={},j||(k[l].toJSON=p.noop));if(typeof c=="object"||typeof c=="function")e?k[l]=p.extend(k[l],c):k[l].data=p.extend(k[l].data,c);return f=k[l],e||(f.data||(f.data={}),f=f.data),d!==b&&(f[p.camelCase(c)]=d),i?(g=f[c],g==null&&(g=f[p.camelCase(c)])):g=f,g},removeData:function(a,b,c){if(!p.acceptData(a))return;var d,e,f,g=a.nodeType,h=g?p.cache:a,i=g?a[p.expando]:p.expando;if(!h[i])return;if(b){d=c?h[i]:h[i].data;if(d){p.isArray(b)||(b in d?b=[b]:(b=p.camelCase(b),b in d?b=[b]:b=b.split(" ")));for(e=0,f=b.length;e<f;e++)delete d[b[e]];if(!(c?K:p.isEmptyObject)(d))return}}if(!c){delete h[i].data;if(!K(h[i]))return}g?p.cleanData([a],!0):p.support.deleteExpando||h!=h.window?delete h[i]:h[i]=null},_data:function(a,b,c){return p.data(a,b,c,!0)},acceptData:function(a){var b=a.nodeName&&p.noData[a.nodeName.toLowerCase()];return!b||b!==!0&&a.getAttribute("classid")===b}}),p.fn.extend({data:function(a,c){var d,e,f,g,h,i=this[0],j=0,k=null;if(a===b){if(this.length){k=p.data(i);if(i.nodeType===1&&!p._data(i,"parsedAttrs")){f=i.attributes;for(h=f.length;j<h;j++)g=f[j].name,g.indexOf("data-")||(g=p.camelCase(g.substring(5)),J(i,g,k[g]));p._data(i,"parsedAttrs",!0)}}return k}return typeof a=="object"?this.each(function(){p.data(this,a)}):(d=a.split(".",2),d[1]=d[1]?"."+d[1]:"",e=d[1]+"!",p.access(this,function(c){if(c===b)return k=this.triggerHandler("getData"+e,[d[0]]),k===b&&i&&(k=p.data(i,a),k=J(i,a,k)),k===b&&d[1]?this.data(d[0]):k;d[1]=c,this.each(function(){var b=p(this);b.triggerHandler("setData"+e,d),p.data(this,a,c),b.triggerHandler("changeData"+e,d)})},null,c,arguments.length>1,null,!1))},removeData:function(a){return this.each(function(){p.removeData(this,a)})}}),p.extend({queue:function(a,b,c){var d;if(a)return b=(b||"fx")+"queue",d=p._data(a,b),c&&(!d||p.isArray(c)?d=p._data(a,b,p.makeArray(c)):d.push(c)),d||[]},dequeue:function(a,b){b=b||"fx";var c=p.queue(a,b),d=c.length,e=c.shift(),f=p._queueHooks(a,b),g=function(){p.dequeue(a,b)};e==="inprogress"&&(e=c.shift(),d--),e&&(b==="fx"&&c.unshift("inprogress"),delete f.stop,e.call(a,g,f)),!d&&f&&f.empty.fire()},_queueHooks:function(a,b){var c=b+"queueHooks";return p._data(a,c)||p._data(a,c,{empty:p.Callbacks("once memory").add(function(){p.removeData(a,b+"queue",!0),p.removeData(a,c,!0)})})}}),p.fn.extend({queue:function(a,c){var d=2;return typeof a!="string"&&(c=a,a="fx",d--),arguments.length<d?p.queue(this[0],a):c===b?this:this.each(function(){var b=p.queue(this,a,c);p._queueHooks(this,a),a==="fx"&&b[0]!=="inprogress"&&p.dequeue(this,a)})},dequeue:function(a){return this.each(function(){p.dequeue(this,a)})},delay:function(a,b){return a=p.fx?p.fx.speeds[a]||a:a,b=b||"fx",this.queue(b,function(b,c){var d=setTimeout(b,a);c.stop=function(){clearTimeout(d)}})},clearQueue:function(a){return this.queue(a||"fx",[])},promise:function(a,c){var d,e=1,f=p.Deferred(),g=this,h=this.length,i=function(){--e||f.resolveWith(g,[g])};typeof a!="string"&&(c=a,a=b),a=a||"fx";while(h--)d=p._data(g[h],a+"queueHooks"),d&&d.empty&&(e++,d.empty.add(i));return i(),f.promise(c)}});var L,M,N,O=/[\t\r\n]/g,P=/\r/g,Q=/^(?:button|input)$/i,R=/^(?:button|input|object|select|textarea)$/i,S=/^a(?:rea|)$/i,T=/^(?:autofocus|autoplay|async|checked|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped|selected)$/i,U=p.support.getSetAttribute;p.fn.extend({attr:function(a,b){return p.access(this,p.attr,a,b,arguments.length>1)},removeAttr:function(a){return this.each(function(){p.removeAttr(this,a)})},prop:function(a,b){return p.access(this,p.prop,a,b,arguments.length>1)},removeProp:function(a){return a=p.propFix[a]||a,this.each(function(){try{this[a]=b,delete this[a]}catch(c){}})},addClass:function(a){var b,c,d,e,f,g,h;if(p.isFunction(a))return this.each(function(b){p(this).addClass(a.call(this,b,this.className))});if(a&&typeof a=="string"){b=a.split(s);for(c=0,d=this.length;c<d;c++){e=this[c];if(e.nodeType===1)if(!e.className&&b.length===1)e.className=a;else{f=" "+e.className+" ";for(g=0,h=b.length;g<h;g++)f.indexOf(" "+b[g]+" ")<0&&(f+=b[g]+" ");e.className=p.trim(f)}}}return this},removeClass:function(a){var c,d,e,f,g,h,i;if(p.isFunction(a))return this.each(function(b){p(this).removeClass(a.call(this,b,this.className))});if(a&&typeof a=="string"||a===b){c=(a||"").split(s);for(h=0,i=this.length;h<i;h++){e=this[h];if(e.nodeType===1&&e.className){d=(" "+e.className+" ").replace(O," ");for(f=0,g=c.length;f<g;f++)while(d.indexOf(" "+c[f]+" ")>=0)d=d.replace(" "+c[f]+" "," ");e.className=a?p.trim(d):""}}}return this},toggleClass:function(a,b){var c=typeof a,d=typeof b=="boolean";return p.isFunction(a)?this.each(function(c){p(this).toggleClass(a.call(this,c,this.className,b),b)}):this.each(function(){if(c==="string"){var e,f=0,g=p(this),h=b,i=a.split(s);while(e=i[f++])h=d?h:!g.hasClass(e),g[h?"addClass":"removeClass"](e)}else if(c==="undefined"||c==="boolean")this.className&&p._data(this,"__className__",this.className),this.className=this.className||a===!1?"":p._data(this,"__className__")||""})},hasClass:function(a){var b=" "+a+" ",c=0,d=this.length;for(;c<d;c++)if(this[c].nodeType===1&&(" "+this[c].className+" ").replace(O," ").indexOf(b)>=0)return!0;return!1},val:function(a){var c,d,e,f=this[0];if(!arguments.length){if(f)return c=p.valHooks[f.type]||p.valHooks[f.nodeName.toLowerCase()],c&&"get"in c&&(d=c.get(f,"value"))!==b?d:(d=f.value,typeof d=="string"?d.replace(P,""):d==null?"":d);return}return e=p.isFunction(a),this.each(function(d){var f,g=p(this);if(this.nodeType!==1)return;e?f=a.call(this,d,g.val()):f=a,f==null?f="":typeof f=="number"?f+="":p.isArray(f)&&(f=p.map(f,function(a){return a==null?"":a+""})),c=p.valHooks[this.type]||p.valHooks[this.nodeName.toLowerCase()];if(!c||!("set"in c)||c.set(this,f,"value")===b)this.value=f})}}),p.extend({valHooks:{option:{get:function(a){var b=a.attributes.value;return!b||b.specified?a.value:a.text}},select:{get:function(a){var b,c,d,e,f=a.selectedIndex,g=[],h=a.options,i=a.type==="select-one";if(f<0)return null;c=i?f:0,d=i?f+1:h.length;for(;c<d;c++){e=h[c];if(e.selected&&(p.support.optDisabled?!e.disabled:e.getAttribute("disabled")===null)&&(!e.parentNode.disabled||!p.nodeName(e.parentNode,"optgroup"))){b=p(e).val();if(i)return b;g.push(b)}}return i&&!g.length&&h.length?p(h[f]).val():g},set:function(a,b){var c=p.makeArray(b);return p(a).find("option").each(function(){this.selected=p.inArray(p(this).val(),c)>=0}),c.length||(a.selectedIndex=-1),c}}},attrFn:{},attr:function(a,c,d,e){var f,g,h,i=a.nodeType;if(!a||i===3||i===8||i===2)return;if(e&&p.isFunction(p.fn[c]))return p(a)[c](d);if(typeof a.getAttribute=="undefined")return p.prop(a,c,d);h=i!==1||!p.isXMLDoc(a),h&&(c=c.toLowerCase(),g=p.attrHooks[c]||(T.test(c)?M:L));if(d!==b){if(d===null){p.removeAttr(a,c);return}return g&&"set"in g&&h&&(f=g.set(a,d,c))!==b?f:(a.setAttribute(c,d+""),d)}return g&&"get"in g&&h&&(f=g.get(a,c))!==null?f:(f=a.getAttribute(c),f===null?b:f)},removeAttr:function(a,b){var c,d,e,f,g=0;if(b&&a.nodeType===1){d=b.split(s);for(;g<d.length;g++)e=d[g],e&&(c=p.propFix[e]||e,f=T.test(e),f||p.attr(a,e,""),a.removeAttribute(U?e:c),f&&c in a&&(a[c]=!1))}},attrHooks:{type:{set:function(a,b){if(Q.test(a.nodeName)&&a.parentNode)p.error("type property can't be changed");else if(!p.support.radioValue&&b==="radio"&&p.nodeName(a,"input")){var c=a.value;return a.setAttribute("type",b),c&&(a.value=c),b}}},value:{get:function(a,b){return L&&p.nodeName(a,"button")?L.get(a,b):b in a?a.value:null},set:function(a,b,c){if(L&&p.nodeName(a,"button"))return L.set(a,b,c);a.value=b}}},propFix:{tabindex:"tabIndex",readonly:"readOnly","for":"htmlFor","class":"className",maxlength:"maxLength",cellspacing:"cellSpacing",cellpadding:"cellPadding",rowspan:"rowSpan",colspan:"colSpan",usemap:"useMap",frameborder:"frameBorder",contenteditable:"contentEditable"},prop:function(a,c,d){var e,f,g,h=a.nodeType;if(!a||h===3||h===8||h===2)return;return g=h!==1||!p.isXMLDoc(a),g&&(c=p.propFix[c]||c,f=p.propHooks[c]),d!==b?f&&"set"in f&&(e=f.set(a,d,c))!==b?e:a[c]=d:f&&"get"in f&&(e=f.get(a,c))!==null?e:a[c]},propHooks:{tabIndex:{get:function(a){var c=a.getAttributeNode("tabindex");return c&&c.specified?parseInt(c.value,10):R.test(a.nodeName)||S.test(a.nodeName)&&a.href?0:b}}}}),M={get:function(a,c){var d,e=p.prop(a,c);return e===!0||typeof e!="boolean"&&(d=a.getAttributeNode(c))&&d.nodeValue!==!1?c.toLowerCase():b},set:function(a,b,c){var d;return b===!1?p.removeAttr(a,c):(d=p.propFix[c]||c,d in a&&(a[d]=!0),a.setAttribute(c,c.toLowerCase())),c}},U||(N={name:!0,id:!0,coords:!0},L=p.valHooks.button={get:function(a,c){var d;return d=a.getAttributeNode(c),d&&(N[c]?d.value!=="":d.specified)?d.value:b},set:function(a,b,c){var d=a.getAttributeNode(c);return d||(d=e.createAttribute(c),a.setAttributeNode(d)),d.value=b+""}},p.each(["width","height"],function(a,b){p.attrHooks[b]=p.extend(p.attrHooks[b],{set:function(a,c){if(c==="")return a.setAttribute(b,"auto"),c}})}),p.attrHooks.contenteditable={get:L.get,set:function(a,b,c){b===""&&(b="false"),L.set(a,b,c)}}),p.support.hrefNormalized||p.each(["href","src","width","height"],function(a,c){p.attrHooks[c]=p.extend(p.attrHooks[c],{get:function(a){var d=a.getAttribute(c,2);return d===null?b:d}})}),p.support.style||(p.attrHooks.style={get:function(a){return a.style.cssText.toLowerCase()||b},set:function(a,b){return a.style.cssText=b+""}}),p.support.optSelected||(p.propHooks.selected=p.extend(p.propHooks.selected,{get:function(a){var b=a.parentNode;return b&&(b.selectedIndex,b.parentNode&&b.parentNode.selectedIndex),null}})),p.support.enctype||(p.propFix.enctype="encoding"),p.support.checkOn||p.each(["radio","checkbox"],function(){p.valHooks[this]={get:function(a){return a.getAttribute("value")===null?"on":a.value}}}),p.each(["radio","checkbox"],function(){p.valHooks[this]=p.extend(p.valHooks[this],{set:function(a,b){if(p.isArray(b))return a.checked=p.inArray(p(a).val(),b)>=0}})});var V=/^(?:textarea|input|select)$/i,W=/^([^\.]*|)(?:\.(.+)|)$/,X=/(?:^|\s)hover(\.\S+|)\b/,Y=/^key/,Z=/^(?:mouse|contextmenu)|click/,$=/^(?:focusinfocus|focusoutblur)$/,_=function(a){return p.event.special.hover?a:a.replace(X,"mouseenter$1 mouseleave$1")};p.event={add:function(a,c,d,e,f){var g,h,i,j,k,l,m,n,o,q,r;if(a.nodeType===3||a.nodeType===8||!c||!d||!(g=p._data(a)))return;d.handler&&(o=d,d=o.handler,f=o.selector),d.guid||(d.guid=p.guid++),i=g.events,i||(g.events=i={}),h=g.handle,h||(g.handle=h=function(a){return typeof p!="undefined"&&(!a||p.event.triggered!==a.type)?p.event.dispatch.apply(h.elem,arguments):b},h.elem=a),c=p.trim(_(c)).split(" ");for(j=0;j<c.length;j++){k=W.exec(c[j])||[],l=k[1],m=(k[2]||"").split(".").sort(),r=p.event.special[l]||{},l=(f?r.delegateType:r.bindType)||l,r=p.event.special[l]||{},n=p.extend({type:l,origType:k[1],data:e,handler:d,guid:d.guid,selector:f,needsContext:f&&p.expr.match.needsContext.test(f),namespace:m.join(".")},o),q=i[l];if(!q){q=i[l]=[],q.delegateCount=0;if(!r.setup||r.setup.call(a,e,m,h)===!1)a.addEventListener?a.addEventListener(l,h,!1):a.attachEvent&&a.attachEvent("on"+l,h)}r.add&&(r.add.call(a,n),n.handler.guid||(n.handler.guid=d.guid)),f?q.splice(q.delegateCount++,0,n):q.push(n),p.event.global[l]=!0}a=null},global:{},remove:function(a,b,c,d,e){var f,g,h,i,j,k,l,m,n,o,q,r=p.hasData(a)&&p._data(a);if(!r||!(m=r.events))return;b=p.trim(_(b||"")).split(" ");for(f=0;f<b.length;f++){g=W.exec(b[f])||[],h=i=g[1],j=g[2];if(!h){for(h in m)p.event.remove(a,h+b[f],c,d,!0);continue}n=p.event.special[h]||{},h=(d?n.delegateType:n.bindType)||h,o=m[h]||[],k=o.length,j=j?new RegExp("(^|\\.)"+j.split(".").sort().join("\\.(?:.*\\.|)")+"(\\.|$)"):null;for(l=0;l<o.length;l++)q=o[l],(e||i===q.origType)&&(!c||c.guid===q.guid)&&(!j||j.test(q.namespace))&&(!d||d===q.selector||d==="**"&&q.selector)&&(o.splice(l--,1),q.selector&&o.delegateCount--,n.remove&&n.remove.call(a,q));o.length===0&&k!==o.length&&((!n.teardown||n.teardown.call(a,j,r.handle)===!1)&&p.removeEvent(a,h,r.handle),delete m[h])}p.isEmptyObject(m)&&(delete r.handle,p.removeData(a,"events",!0))},customEvent:{getData:!0,setData:!0,changeData:!0},trigger:function(c,d,f,g){if(!f||f.nodeType!==3&&f.nodeType!==8){var h,i,j,k,l,m,n,o,q,r,s=c.type||c,t=[];if($.test(s+p.event.triggered))return;s.indexOf("!")>=0&&(s=s.slice(0,-1),i=!0),s.indexOf(".")>=0&&(t=s.split("."),s=t.shift(),t.sort());if((!f||p.event.customEvent[s])&&!p.event.global[s])return;c=typeof c=="object"?c[p.expando]?c:new p.Event(s,c):new p.Event(s),c.type=s,c.isTrigger=!0,c.exclusive=i,c.namespace=t.join("."),c.namespace_re=c.namespace?new RegExp("(^|\\.)"+t.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,m=s.indexOf(":")<0?"on"+s:"";if(!f){h=p.cache;for(j in h)h[j].events&&h[j].events[s]&&p.event.trigger(c,d,h[j].handle.elem,!0);return}c.result=b,c.target||(c.target=f),d=d!=null?p.makeArray(d):[],d.unshift(c),n=p.event.special[s]||{};if(n.trigger&&n.trigger.apply(f,d)===!1)return;q=[[f,n.bindType||s]];if(!g&&!n.noBubble&&!p.isWindow(f)){r=n.delegateType||s,k=$.test(r+s)?f:f.parentNode;for(l=f;k;k=k.parentNode)q.push([k,r]),l=k;l===(f.ownerDocument||e)&&q.push([l.defaultView||l.parentWindow||a,r])}for(j=0;j<q.length&&!c.isPropagationStopped();j++)k=q[j][0],c.type=q[j][1],o=(p._data(k,"events")||{})[c.type]&&p._data(k,"handle"),o&&o.apply(k,d),o=m&&k[m],o&&p.acceptData(k)&&o.apply&&o.apply(k,d)===!1&&c.preventDefault();return c.type=s,!g&&!c.isDefaultPrevented()&&(!n._default||n._default.apply(f.ownerDocument,d)===!1)&&(s!=="click"||!p.nodeName(f,"a"))&&p.acceptData(f)&&m&&f[s]&&(s!=="focus"&&s!=="blur"||c.target.offsetWidth!==0)&&!p.isWindow(f)&&(l=f[m],l&&(f[m]=null),p.event.triggered=s,f[s](),p.event.triggered=b,l&&(f[m]=l)),c.result}return},dispatch:function(c){c=p.event.fix(c||a.event);var d,e,f,g,h,i,j,l,m,n,o=(p._data(this,"events")||{})[c.type]||[],q=o.delegateCount,r=k.call(arguments),s=!c.exclusive&&!c.namespace,t=p.event.special[c.type]||{},u=[];r[0]=c,c.delegateTarget=this;if(t.preDispatch&&t.preDispatch.call(this,c)===!1)return;if(q&&(!c.button||c.type!=="click"))for(f=c.target;f!=this;f=f.parentNode||this)if(f.disabled!==!0||c.type!=="click"){h={},j=[];for(d=0;d<q;d++)l=o[d],m=l.selector,h[m]===b&&(h[m]=l.needsContext?p(m,this).index(f)>=0:p.find(m,this,null,[f]).length),h[m]&&j.push(l);j.length&&u.push({elem:f,matches:j})}o.length>q&&u.push({elem:this,matches:o.slice(q)});for(d=0;d<u.length&&!c.isPropagationStopped();d++){i=u[d],c.currentTarget=i.elem;for(e=0;e<i.matches.length&&!c.isImmediatePropagationStopped();e++){l=i.matches[e];if(s||!c.namespace&&!l.namespace||c.namespace_re&&c.namespace_re.test(l.namespace))c.data=l.data,c.handleObj=l,g=((p.event.special[l.origType]||{}).handle||l.handler).apply(i.elem,r),g!==b&&(c.result=g,g===!1&&(c.preventDefault(),c.stopPropagation()))}}return t.postDispatch&&t.postDispatch.call(this,c),c.result},props:"attrChange attrName relatedNode srcElement altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),fixHooks:{},keyHooks:{props:"char charCode key keyCode".split(" "),filter:function(a,b){return a.which==null&&(a.which=b.charCode!=null?b.charCode:b.keyCode),a}},mouseHooks:{props:"button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),filter:function(a,c){var d,f,g,h=c.button,i=c.fromElement;return a.pageX==null&&c.clientX!=null&&(d=a.target.ownerDocument||e,f=d.documentElement,g=d.body,a.pageX=c.clientX+(f&&f.scrollLeft||g&&g.scrollLeft||0)-(f&&f.clientLeft||g&&g.clientLeft||0),a.pageY=c.clientY+(f&&f.scrollTop||g&&g.scrollTop||0)-(f&&f.clientTop||g&&g.clientTop||0)),!a.relatedTarget&&i&&(a.relatedTarget=i===a.target?c.toElement:i),!a.which&&h!==b&&(a.which=h&1?1:h&2?3:h&4?2:0),a}},fix:function(a){if(a[p.expando])return a;var b,c,d=a,f=p.event.fixHooks[a.type]||{},g=f.props?this.props.concat(f.props):this.props;a=p.Event(d);for(b=g.length;b;)c=g[--b],a[c]=d[c];return a.target||(a.target=d.srcElement||e),a.target.nodeType===3&&(a.target=a.target.parentNode),a.metaKey=!!a.metaKey,f.filter?f.filter(a,d):a},special:{load:{noBubble:!0},focus:{delegateType:"focusin"},blur:{delegateType:"focusout"},beforeunload:{setup:function(a,b,c){p.isWindow(this)&&(this.onbeforeunload=c)},teardown:function(a,b){this.onbeforeunload===b&&(this.onbeforeunload=null)}}},simulate:function(a,b,c,d){var e=p.extend(new p.Event,c,{type:a,isSimulated:!0,originalEvent:{}});d?p.event.trigger(e,null,b):p.event.dispatch.call(b,e),e.isDefaultPrevented()&&c.preventDefault()}},p.event.handle=p.event.dispatch,p.removeEvent=e.removeEventListener?function(a,b,c){a.removeEventListener&&a.removeEventListener(b,c,!1)}:function(a,b,c){var d="on"+b;a.detachEvent&&(typeof a[d]=="undefined"&&(a[d]=null),a.detachEvent(d,c))},p.Event=function(a,b){if(this instanceof p.Event)a&&a.type?(this.originalEvent=a,this.type=a.type,this.isDefaultPrevented=a.defaultPrevented||a.returnValue===!1||a.getPreventDefault&&a.getPreventDefault()?bb:ba):this.type=a,b&&p.extend(this,b),this.timeStamp=a&&a.timeStamp||p.now(),this[p.expando]=!0;else return new p.Event(a,b)},p.Event.prototype={preventDefault:function(){this.isDefaultPrevented=bb;var a=this.originalEvent;if(!a)return;a.preventDefault?a.preventDefault():a.returnValue=!1},stopPropagation:function(){this.isPropagationStopped=bb;var a=this.originalEvent;if(!a)return;a.stopPropagation&&a.stopPropagation(),a.cancelBubble=!0},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=bb,this.stopPropagation()},isDefaultPrevented:ba,isPropagationStopped:ba,isImmediatePropagationStopped:ba},p.each({mouseenter:"mouseover",mouseleave:"mouseout"},function(a,b){p.event.special[a]={delegateType:b,bindType:b,handle:function(a){var c,d=this,e=a.relatedTarget,f=a.handleObj,g=f.selector;if(!e||e!==d&&!p.contains(d,e))a.type=f.origType,c=f.handler.apply(this,arguments),a.type=b;return c}}}),p.support.submitBubbles||(p.event.special.submit={setup:function(){if(p.nodeName(this,"form"))return!1;p.event.add(this,"click._submit keypress._submit",function(a){var c=a.target,d=p.nodeName(c,"input")||p.nodeName(c,"button")?c.form:b;d&&!p._data(d,"_submit_attached")&&(p.event.add(d,"submit._submit",function(a){a._submit_bubble=!0}),p._data(d,"_submit_attached",!0))})},postDispatch:function(a){a._submit_bubble&&(delete a._submit_bubble,this.parentNode&&!a.isTrigger&&p.event.simulate("submit",this.parentNode,a,!0))},teardown:function(){if(p.nodeName(this,"form"))return!1;p.event.remove(this,"._submit")}}),p.support.changeBubbles||(p.event.special.change={setup:function(){if(V.test(this.nodeName)){if(this.type==="checkbox"||this.type==="radio")p.event.add(this,"propertychange._change",function(a){a.originalEvent.propertyName==="checked"&&(this._just_changed=!0)}),p.event.add(this,"click._change",function(a){this._just_changed&&!a.isTrigger&&(this._just_changed=!1),p.event.simulate("change",this,a,!0)});return!1}p.event.add(this,"beforeactivate._change",function(a){var b=a.target;V.test(b.nodeName)&&!p._data(b,"_change_attached")&&(p.event.add(b,"change._change",function(a){this.parentNode&&!a.isSimulated&&!a.isTrigger&&p.event.simulate("change",this.parentNode,a,!0)}),p._data(b,"_change_attached",!0))})},handle:function(a){var b=a.target;if(this!==b||a.isSimulated||a.isTrigger||b.type!=="radio"&&b.type!=="checkbox")return a.handleObj.handler.apply(this,arguments)},teardown:function(){return p.event.remove(this,"._change"),!V.test(this.nodeName)}}),p.support.focusinBubbles||p.each({focus:"focusin",blur:"focusout"},function(a,b){var c=0,d=function(a){p.event.simulate(b,a.target,p.event.fix(a),!0)};p.event.special[b]={setup:function(){c++===0&&e.addEventListener(a,d,!0)},teardown:function(){--c===0&&e.removeEventListener(a,d,!0)}}}),p.fn.extend({on:function(a,c,d,e,f){var g,h;if(typeof a=="object"){typeof c!="string"&&(d=d||c,c=b);for(h in a)this.on(h,c,d,a[h],f);return this}d==null&&e==null?(e=c,d=c=b):e==null&&(typeof c=="string"?(e=d,d=b):(e=d,d=c,c=b));if(e===!1)e=ba;else if(!e)return this;return f===1&&(g=e,e=function(a){return p().off(a),g.apply(this,arguments)},e.guid=g.guid||(g.guid=p.guid++)),this.each(function(){p.event.add(this,a,e,d,c)})},one:function(a,b,c,d){return this.on(a,b,c,d,1)},off:function(a,c,d){var e,f;if(a&&a.preventDefault&&a.handleObj)return e=a.handleObj,p(a.delegateTarget).off(e.namespace?e.origType+"."+e.namespace:e.origType,e.selector,e.handler),this;if(typeof a=="object"){for(f in a)this.off(f,c,a[f]);return this}if(c===!1||typeof c=="function")d=c,c=b;return d===!1&&(d=ba),this.each(function(){p.event.remove(this,a,d,c)})},bind:function(a,b,c){return this.on(a,null,b,c)},unbind:function(a,b){return this.off(a,null,b)},live:function(a,b,c){return p(this.context).on(a,this.selector,b,c),this},die:function(a,b){return p(this.context).off(a,this.selector||"**",b),this},delegate:function(a,b,c,d){return this.on(b,a,c,d)},undelegate:function(a,b,c){return arguments.length===1?this.off(a,"**"):this.off(b,a||"**",c)},trigger:function(a,b){return this.each(function(){p.event.trigger(a,b,this)})},triggerHandler:function(a,b){if(this[0])return p.event.trigger(a,b,this[0],!0)},toggle:function(a){var b=arguments,c=a.guid||p.guid++,d=0,e=function(c){var e=(p._data(this,"lastToggle"+a.guid)||0)%d;return p._data(this,"lastToggle"+a.guid,e+1),c.preventDefault(),b[e].apply(this,arguments)||!1};e.guid=c;while(d<b.length)b[d++].guid=c;return this.click(e)},hover:function(a,b){return this.mouseenter(a).mouseleave(b||a)}}),p.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "),function(a,b){p.fn[b]=function(a,c){return c==null&&(c=a,a=null),arguments.length>0?this.on(b,null,a,c):this.trigger(b)},Y.test(b)&&(p.event.fixHooks[b]=p.event.keyHooks),Z.test(b)&&(p.event.fixHooks[b]=p.event.mouseHooks)}),function(a,b){function bc(a,b,c,d){c=c||[],b=b||r;var e,f,i,j,k=b.nodeType;if(!a||typeof a!="string")return c;if(k!==1&&k!==9)return[];i=g(b);if(!i&&!d)if(e=P.exec(a))if(j=e[1]){if(k===9){f=b.getElementById(j);if(!f||!f.parentNode)return c;if(f.id===j)return c.push(f),c}else if(b.ownerDocument&&(f=b.ownerDocument.getElementById(j))&&h(b,f)&&f.id===j)return c.push(f),c}else{if(e[2])return w.apply(c,x.call(b.getElementsByTagName(a),0)),c;if((j=e[3])&&_&&b.getElementsByClassName)return w.apply(c,x.call(b.getElementsByClassName(j),0)),c}return bp(a.replace(L,"$1"),b,c,d,i)}function bd(a){return function(b){var c=b.nodeName.toLowerCase();return c==="input"&&b.type===a}}function be(a){return function(b){var c=b.nodeName.toLowerCase();return(c==="input"||c==="button")&&b.type===a}}function bf(a){return z(function(b){return b=+b,z(function(c,d){var e,f=a([],c.length,b),g=f.length;while(g--)c[e=f[g]]&&(c[e]=!(d[e]=c[e]))})})}function bg(a,b,c){if(a===b)return c;var d=a.nextSibling;while(d){if(d===b)return-1;d=d.nextSibling}return 1}function bh(a,b){var c,d,f,g,h,i,j,k=C[o][a];if(k)return b?0:k.slice(0);h=a,i=[],j=e.preFilter;while(h){if(!c||(d=M.exec(h)))d&&(h=h.slice(d[0].length)),i.push(f=[]);c=!1;if(d=N.exec(h))f.push(c=new q(d.shift())),h=h.slice(c.length),c.type=d[0].replace(L," ");for(g in e.filter)(d=W[g].exec(h))&&(!j[g]||(d=j[g](d,r,!0)))&&(f.push(c=new q(d.shift())),h=h.slice(c.length),c.type=g,c.matches=d);if(!c)break}return b?h.length:h?bc.error(a):C(a,i).slice(0)}function bi(a,b,d){var e=b.dir,f=d&&b.dir==="parentNode",g=u++;return b.first?function(b,c,d){while(b=b[e])if(f||b.nodeType===1)return a(b,c,d)}:function(b,d,h){if(!h){var i,j=t+" "+g+" ",k=j+c;while(b=b[e])if(f||b.nodeType===1){if((i=b[o])===k)return b.sizset;if(typeof i=="string"&&i.indexOf(j)===0){if(b.sizset)return b}else{b[o]=k;if(a(b,d,h))return b.sizset=!0,b;b.sizset=!1}}}else while(b=b[e])if(f||b.nodeType===1)if(a(b,d,h))return b}}function bj(a){return a.length>1?function(b,c,d){var e=a.length;while(e--)if(!a[e](b,c,d))return!1;return!0}:a[0]}function bk(a,b,c,d,e){var f,g=[],h=0,i=a.length,j=b!=null;for(;h<i;h++)if(f=a[h])if(!c||c(f,d,e))g.push(f),j&&b.push(h);return g}function bl(a,b,c,d,e,f){return d&&!d[o]&&(d=bl(d)),e&&!e[o]&&(e=bl(e,f)),z(function(f,g,h,i){if(f&&e)return;var j,k,l,m=[],n=[],o=g.length,p=f||bo(b||"*",h.nodeType?[h]:h,[],f),q=a&&(f||!b)?bk(p,m,a,h,i):p,r=c?e||(f?a:o||d)?[]:g:q;c&&c(q,r,h,i);if(d){l=bk(r,n),d(l,[],h,i),j=l.length;while(j--)if(k=l[j])r[n[j]]=!(q[n[j]]=k)}if(f){j=a&&r.length;while(j--)if(k=r[j])f[m[j]]=!(g[m[j]]=k)}else r=bk(r===g?r.splice(o,r.length):r),e?e(null,g,r,i):w.apply(g,r)})}function bm(a){var b,c,d,f=a.length,g=e.relative[a[0].type],h=g||e.relative[" "],i=g?1:0,j=bi(function(a){return a===b},h,!0),k=bi(function(a){return y.call(b,a)>-1},h,!0),m=[function(a,c,d){return!g&&(d||c!==l)||((b=c).nodeType?j(a,c,d):k(a,c,d))}];for(;i<f;i++)if(c=e.relative[a[i].type])m=[bi(bj(m),c)];else{c=e.filter[a[i].type].apply(null,a[i].matches);if(c[o]){d=++i;for(;d<f;d++)if(e.relative[a[d].type])break;return bl(i>1&&bj(m),i>1&&a.slice(0,i-1).join("").replace(L,"$1"),c,i<d&&bm(a.slice(i,d)),d<f&&bm(a=a.slice(d)),d<f&&a.join(""))}m.push(c)}return bj(m)}function bn(a,b){var d=b.length>0,f=a.length>0,g=function(h,i,j,k,m){var n,o,p,q=[],s=0,u="0",x=h&&[],y=m!=null,z=l,A=h||f&&e.find.TAG("*",m&&i.parentNode||i),B=t+=z==null?1:Math.E;y&&(l=i!==r&&i,c=g.el);for(;(n=A[u])!=null;u++){if(f&&n){for(o=0;p=a[o];o++)if(p(n,i,j)){k.push(n);break}y&&(t=B,c=++g.el)}d&&((n=!p&&n)&&s--,h&&x.push(n))}s+=u;if(d&&u!==s){for(o=0;p=b[o];o++)p(x,q,i,j);if(h){if(s>0)while(u--)!x[u]&&!q[u]&&(q[u]=v.call(k));q=bk(q)}w.apply(k,q),y&&!h&&q.length>0&&s+b.length>1&&bc.uniqueSort(k)}return y&&(t=B,l=z),x};return g.el=0,d?z(g):g}function bo(a,b,c,d){var e=0,f=b.length;for(;e<f;e++)bc(a,b[e],c,d);return c}function bp(a,b,c,d,f){var g,h,j,k,l,m=bh(a),n=m.length;if(!d&&m.length===1){h=m[0]=m[0].slice(0);if(h.length>2&&(j=h[0]).type==="ID"&&b.nodeType===9&&!f&&e.relative[h[1].type]){b=e.find.ID(j.matches[0].replace(V,""),b,f)[0];if(!b)return c;a=a.slice(h.shift().length)}for(g=W.POS.test(a)?-1:h.length-1;g>=0;g--){j=h[g];if(e.relative[k=j.type])break;if(l=e.find[k])if(d=l(j.matches[0].replace(V,""),R.test(h[0].type)&&b.parentNode||b,f)){h.splice(g,1),a=d.length&&h.join("");if(!a)return w.apply(c,x.call(d,0)),c;break}}}return i(a,m)(d,b,f,c,R.test(a)),c}function bq(){}var c,d,e,f,g,h,i,j,k,l,m=!0,n="undefined",o=("sizcache"+Math.random()).replace(".",""),q=String,r=a.document,s=r.documentElement,t=0,u=0,v=[].pop,w=[].push,x=[].slice,y=[].indexOf||function(a){var b=0,c=this.length;for(;b<c;b++)if(this[b]===a)return b;return-1},z=function(a,b){return a[o]=b==null||b,a},A=function(){var a={},b=[];return z(function(c,d){return b.push(c)>e.cacheLength&&delete a[b.shift()],a[c]=d},a)},B=A(),C=A(),D=A(),E="[\\x20\\t\\r\\n\\f]",F="(?:\\\\.|[-\\w]|[^\\x00-\\xa0])+",G=F.replace("w","w#"),H="([*^$|!~]?=)",I="\\["+E+"*("+F+")"+E+"*(?:"+H+E+"*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|("+G+")|)|)"+E+"*\\]",J=":("+F+")(?:\\((?:(['\"])((?:\\\\.|[^\\\\])*?)\\2|([^()[\\]]*|(?:(?:"+I+")|[^:]|\\\\.)*|.*))\\)|)",K=":(even|odd|eq|gt|lt|nth|first|last)(?:\\("+E+"*((?:-\\d)?\\d*)"+E+"*\\)|)(?=[^-]|$)",L=new RegExp("^"+E+"+|((?:^|[^\\\\])(?:\\\\.)*)"+E+"+$","g"),M=new RegExp("^"+E+"*,"+E+"*"),N=new RegExp("^"+E+"*([\\x20\\t\\r\\n\\f>+~])"+E+"*"),O=new RegExp(J),P=/^(?:#([\w\-]+)|(\w+)|\.([\w\-]+))$/,Q=/^:not/,R=/[\x20\t\r\n\f]*[+~]/,S=/:not\($/,T=/h\d/i,U=/input|select|textarea|button/i,V=/\\(?!\\)/g,W={ID:new RegExp("^#("+F+")"),CLASS:new RegExp("^\\.("+F+")"),NAME:new RegExp("^\\[name=['\"]?("+F+")['\"]?\\]"),TAG:new RegExp("^("+F.replace("w","w*")+")"),ATTR:new RegExp("^"+I),PSEUDO:new RegExp("^"+J),POS:new RegExp(K,"i"),CHILD:new RegExp("^:(only|nth|first|last)-child(?:\\("+E+"*(even|odd|(([+-]|)(\\d*)n|)"+E+"*(?:([+-]|)"+E+"*(\\d+)|))"+E+"*\\)|)","i"),needsContext:new RegExp("^"+E+"*[>+~]|"+K,"i")},X=function(a){var b=r.createElement("div");try{return a(b)}catch(c){return!1}finally{b=null}},Y=X(function(a){return a.appendChild(r.createComment("")),!a.getElementsByTagName("*").length}),Z=X(function(a){return a.innerHTML="<a href='#'></a>",a.firstChild&&typeof a.firstChild.getAttribute!==n&&a.firstChild.getAttribute("href")==="#"}),$=X(function(a){a.innerHTML="<select></select>";var b=typeof a.lastChild.getAttribute("multiple");return b!=="boolean"&&b!=="string"}),_=X(function(a){return a.innerHTML="<div class='hidden e'></div><div class='hidden'></div>",!a.getElementsByClassName||!a.getElementsByClassName("e").length?!1:(a.lastChild.className="e",a.getElementsByClassName("e").length===2)}),ba=X(function(a){a.id=o+0,a.innerHTML="<a name='"+o+"'></a><div name='"+o+"'></div>",s.insertBefore(a,s.firstChild);var b=r.getElementsByName&&r.getElementsByName(o).length===2+r.getElementsByName(o+0).length;return d=!r.getElementById(o),s.removeChild(a),b});try{x.call(s.childNodes,0)[0].nodeType}catch(bb){x=function(a){var b,c=[];for(;b=this[a];a++)c.push(b);return c}}bc.matches=function(a,b){return bc(a,null,null,b)},bc.matchesSelector=function(a,b){return bc(b,null,null,[a]).length>0},f=bc.getText=function(a){var b,c="",d=0,e=a.nodeType;if(e){if(e===1||e===9||e===11){if(typeof a.textContent=="string")return a.textContent;for(a=a.firstChild;a;a=a.nextSibling)c+=f(a)}else if(e===3||e===4)return a.nodeValue}else for(;b=a[d];d++)c+=f(b);return c},g=bc.isXML=function(a){var b=a&&(a.ownerDocument||a).documentElement;return b?b.nodeName!=="HTML":!1},h=bc.contains=s.contains?function(a,b){var c=a.nodeType===9?a.documentElement:a,d=b&&b.parentNode;return a===d||!!(d&&d.nodeType===1&&c.contains&&c.contains(d))}:s.compareDocumentPosition?function(a,b){return b&&!!(a.compareDocumentPosition(b)&16)}:function(a,b){while(b=b.parentNode)if(b===a)return!0;return!1},bc.attr=function(a,b){var c,d=g(a);return d||(b=b.toLowerCase()),(c=e.attrHandle[b])?c(a):d||$?a.getAttribute(b):(c=a.getAttributeNode(b),c?typeof a[b]=="boolean"?a[b]?b:null:c.specified?c.value:null:null)},e=bc.selectors={cacheLength:50,createPseudo:z,match:W,attrHandle:Z?{}:{href:function(a){return a.getAttribute("href",2)},type:function(a){return a.getAttribute("type")}},find:{ID:d?function(a,b,c){if(typeof b.getElementById!==n&&!c){var d=b.getElementById(a);return d&&d.parentNode?[d]:[]}}:function(a,c,d){if(typeof c.getElementById!==n&&!d){var e=c.getElementById(a);return e?e.id===a||typeof e.getAttributeNode!==n&&e.getAttributeNode("id").value===a?[e]:b:[]}},TAG:Y?function(a,b){if(typeof b.getElementsByTagName!==n)return b.getElementsByTagName(a)}:function(a,b){var c=b.getElementsByTagName(a);if(a==="*"){var d,e=[],f=0;for(;d=c[f];f++)d.nodeType===1&&e.push(d);return e}return c},NAME:ba&&function(a,b){if(typeof b.getElementsByName!==n)return b.getElementsByName(name)},CLASS:_&&function(a,b,c){if(typeof b.getElementsByClassName!==n&&!c)return b.getElementsByClassName(a)}},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(a){return a[1]=a[1].replace(V,""),a[3]=(a[4]||a[5]||"").replace(V,""),a[2]==="~="&&(a[3]=" "+a[3]+" "),a.slice(0,4)},CHILD:function(a){return a[1]=a[1].toLowerCase(),a[1]==="nth"?(a[2]||bc.error(a[0]),a[3]=+(a[3]?a[4]+(a[5]||1):2*(a[2]==="even"||a[2]==="odd")),a[4]=+(a[6]+a[7]||a[2]==="odd")):a[2]&&bc.error(a[0]),a},PSEUDO:function(a){var b,c;if(W.CHILD.test(a[0]))return null;if(a[3])a[2]=a[3];else if(b=a[4])O.test(b)&&(c=bh(b,!0))&&(c=b.indexOf(")",b.length-c)-b.length)&&(b=b.slice(0,c),a[0]=a[0].slice(0,c)),a[2]=b;return a.slice(0,3)}},filter:{ID:d?function(a){return a=a.replace(V,""),function(b){return b.getAttribute("id")===a}}:function(a){return a=a.replace(V,""),function(b){var c=typeof b.getAttributeNode!==n&&b.getAttributeNode("id");return c&&c.value===a}},TAG:function(a){return a==="*"?function(){return!0}:(a=a.replace(V,"").toLowerCase(),function(b){return b.nodeName&&b.nodeName.toLowerCase()===a})},CLASS:function(a){var b=B[o][a];return b||(b=B(a,new RegExp("(^|"+E+")"+a+"("+E+"|$)"))),function(a){return b.test(a.className||typeof a.getAttribute!==n&&a.getAttribute("class")||"")}},ATTR:function(a,b,c){return function(d,e){var f=bc.attr(d,a);return f==null?b==="!=":b?(f+="",b==="="?f===c:b==="!="?f!==c:b==="^="?c&&f.indexOf(c)===0:b==="*="?c&&f.indexOf(c)>-1:b==="$="?c&&f.substr(f.length-c.length)===c:b==="~="?(" "+f+" ").indexOf(c)>-1:b==="|="?f===c||f.substr(0,c.length+1)===c+"-":!1):!0}},CHILD:function(a,b,c,d){return a==="nth"?function(a){var b,e,f=a.parentNode;if(c===1&&d===0)return!0;if(f){e=0;for(b=f.firstChild;b;b=b.nextSibling)if(b.nodeType===1){e++;if(a===b)break}}return e-=d,e===c||e%c===0&&e/c>=0}:function(b){var c=b;switch(a){case"only":case"first":while(c=c.previousSibling)if(c.nodeType===1)return!1;if(a==="first")return!0;c=b;case"last":while(c=c.nextSibling)if(c.nodeType===1)return!1;return!0}}},PSEUDO:function(a,b){var c,d=e.pseudos[a]||e.setFilters[a.toLowerCase()]||bc.error("unsupported pseudo: "+a);return d[o]?d(b):d.length>1?(c=[a,a,"",b],e.setFilters.hasOwnProperty(a.toLowerCase())?z(function(a,c){var e,f=d(a,b),g=f.length;while(g--)e=y.call(a,f[g]),a[e]=!(c[e]=f[g])}):function(a){return d(a,0,c)}):d}},pseudos:{not:z(function(a){var b=[],c=[],d=i(a.replace(L,"$1"));return d[o]?z(function(a,b,c,e){var f,g=d(a,null,e,[]),h=a.length;while(h--)if(f=g[h])a[h]=!(b[h]=f)}):function(a,e,f){return b[0]=a,d(b,null,f,c),!c.pop()}}),has:z(function(a){return function(b){return bc(a,b).length>0}}),contains:z(function(a){return function(b){return(b.textContent||b.innerText||f(b)).indexOf(a)>-1}}),enabled:function(a){return a.disabled===!1},disabled:function(a){return a.disabled===!0},checked:function(a){var b=a.nodeName.toLowerCase();return b==="input"&&!!a.checked||b==="option"&&!!a.selected},selected:function(a){return a.parentNode&&a.parentNode.selectedIndex,a.selected===!0},parent:function(a){return!e.pseudos.empty(a)},empty:function(a){var b;a=a.firstChild;while(a){if(a.nodeName>"@"||(b=a.nodeType)===3||b===4)return!1;a=a.nextSibling}return!0},header:function(a){return T.test(a.nodeName)},text:function(a){var b,c;return a.nodeName.toLowerCase()==="input"&&(b=a.type)==="text"&&((c=a.getAttribute("type"))==null||c.toLowerCase()===b)},radio:bd("radio"),checkbox:bd("checkbox"),file:bd("file"),password:bd("password"),image:bd("image"),submit:be("submit"),reset:be("reset"),button:function(a){var b=a.nodeName.toLowerCase();return b==="input"&&a.type==="button"||b==="button"},input:function(a){return U.test(a.nodeName)},focus:function(a){var b=a.ownerDocument;return a===b.activeElement&&(!b.hasFocus||b.hasFocus())&&(!!a.type||!!a.href)},active:function(a){return a===a.ownerDocument.activeElement},first:bf(function(a,b,c){return[0]}),last:bf(function(a,b,c){return[b-1]}),eq:bf(function(a,b,c){return[c<0?c+b:c]}),even:bf(function(a,b,c){for(var d=0;d<b;d+=2)a.push(d);return a}),odd:bf(function(a,b,c){for(var d=1;d<b;d+=2)a.push(d);return a}),lt:bf(function(a,b,c){for(var d=c<0?c+b:c;--d>=0;)a.push(d);return a}),gt:bf(function(a,b,c){for(var d=c<0?c+b:c;++d<b;)a.push(d);return a})}},j=s.compareDocumentPosition?function(a,b){return a===b?(k=!0,0):(!a.compareDocumentPosition||!b.compareDocumentPosition?a.compareDocumentPosition:a.compareDocumentPosition(b)&4)?-1:1}:function(a,b){if(a===b)return k=!0,0;if(a.sourceIndex&&b.sourceIndex)return a.sourceIndex-b.sourceIndex;var c,d,e=[],f=[],g=a.parentNode,h=b.parentNode,i=g;if(g===h)return bg(a,b);if(!g)return-1;if(!h)return 1;while(i)e.unshift(i),i=i.parentNode;i=h;while(i)f.unshift(i),i=i.parentNode;c=e.length,d=f.length;for(var j=0;j<c&&j<d;j++)if(e[j]!==f[j])return bg(e[j],f[j]);return j===c?bg(a,f[j],-1):bg(e[j],b,1)},[0,0].sort(j),m=!k,bc.uniqueSort=function(a){var b,c=1;k=m,a.sort(j);if(k)for(;b=a[c];c++)b===a[c-1]&&a.splice(c--,1);return a},bc.error=function(a){throw new Error("Syntax error, unrecognized expression: "+a)},i=bc.compile=function(a,b){var c,d=[],e=[],f=D[o][a];if(!f){b||(b=bh(a)),c=b.length;while(c--)f=bm(b[c]),f[o]?d.push(f):e.push(f);f=D(a,bn(e,d))}return f},r.querySelectorAll&&function(){var a,b=bp,c=/'|\\/g,d=/\=[\x20\t\r\n\f]*([^'"\]]*)[\x20\t\r\n\f]*\]/g,e=[":focus"],f=[":active",":focus"],h=s.matchesSelector||s.mozMatchesSelector||s.webkitMatchesSelector||s.oMatchesSelector||s.msMatchesSelector;X(function(a){a.innerHTML="<select><option selected=''></option></select>",a.querySelectorAll("[selected]").length||e.push("\\["+E+"*(?:checked|disabled|ismap|multiple|readonly|selected|value)"),a.querySelectorAll(":checked").length||e.push(":checked")}),X(function(a){a.innerHTML="<p test=''></p>",a.querySelectorAll("[test^='']").length&&e.push("[*^$]="+E+"*(?:\"\"|'')"),a.innerHTML="<input type='hidden'/>",a.querySelectorAll(":enabled").length||e.push(":enabled",":disabled")}),e=new RegExp(e.join("|")),bp=function(a,d,f,g,h){if(!g&&!h&&(!e||!e.test(a))){var i,j,k=!0,l=o,m=d,n=d.nodeType===9&&a;if(d.nodeType===1&&d.nodeName.toLowerCase()!=="object"){i=bh(a),(k=d.getAttribute("id"))?l=k.replace(c,"\\$&"):d.setAttribute("id",l),l="[id='"+l+"'] ",j=i.length;while(j--)i[j]=l+i[j].join("");m=R.test(a)&&d.parentNode||d,n=i.join(",")}if(n)try{return w.apply(f,x.call(m.querySelectorAll(n),0)),f}catch(p){}finally{k||d.removeAttribute("id")}}return b(a,d,f,g,h)},h&&(X(function(b){a=h.call(b,"div");try{h.call(b,"[test!='']:sizzle"),f.push("!=",J)}catch(c){}}),f=new RegExp(f.join("|")),bc.matchesSelector=function(b,c){c=c.replace(d,"='$1']");if(!g(b)&&!f.test(c)&&(!e||!e.test(c)))try{var i=h.call(b,c);if(i||a||b.document&&b.document.nodeType!==11)return i}catch(j){}return bc(c,null,null,[b]).length>0})}(),e.pseudos.nth=e.pseudos.eq,e.filters=bq.prototype=e.pseudos,e.setFilters=new bq,bc.attr=p.attr,p.find=bc,p.expr=bc.selectors,p.expr[":"]=p.expr.pseudos,p.unique=bc.uniqueSort,p.text=bc.getText,p.isXMLDoc=bc.isXML,p.contains=bc.contains}(a);var bc=/Until$/,bd=/^(?:parents|prev(?:Until|All))/,be=/^.[^:#\[\.,]*$/,bf=p.expr.match.needsContext,bg={children:!0,contents:!0,next:!0,prev:!0};p.fn.extend({find:function(a){var b,c,d,e,f,g,h=this;if(typeof a!="string")return p(a).filter(function(){for(b=0,c=h.length;b<c;b++)if(p.contains(h[b],this))return!0});g=this.pushStack("","find",a);for(b=0,c=this.length;b<c;b++){d=g.length,p.find(a,this[b],g);if(b>0)for(e=d;e<g.length;e++)for(f=0;f<d;f++)if(g[f]===g[e]){g.splice(e--,1);break}}return g},has:function(a){var b,c=p(a,this),d=c.length;return this.filter(function(){for(b=0;b<d;b++)if(p.contains(this,c[b]))return!0})},not:function(a){return this.pushStack(bj(this,a,!1),"not",a)},filter:function(a){return this.pushStack(bj(this,a,!0),"filter",a)},is:function(a){return!!a&&(typeof a=="string"?bf.test(a)?p(a,this.context).index(this[0])>=0:p.filter(a,this).length>0:this.filter(a).length>0)},closest:function(a,b){var c,d=0,e=this.length,f=[],g=bf.test(a)||typeof a!="string"?p(a,b||this.context):0;for(;d<e;d++){c=this[d];while(c&&c.ownerDocument&&c!==b&&c.nodeType!==11){if(g?g.index(c)>-1:p.find.matchesSelector(c,a)){f.push(c);break}c=c.parentNode}}return f=f.length>1?p.unique(f):f,this.pushStack(f,"closest",a)},index:function(a){return a?typeof a=="string"?p.inArray(this[0],p(a)):p.inArray(a.jquery?a[0]:a,this):this[0]&&this[0].parentNode?this.prevAll().length:-1},add:function(a,b){var c=typeof a=="string"?p(a,b):p.makeArray(a&&a.nodeType?[a]:a),d=p.merge(this.get(),c);return this.pushStack(bh(c[0])||bh(d[0])?d:p.unique(d))},addBack:function(a){return this.add(a==null?this.prevObject:this.prevObject.filter(a))}}),p.fn.andSelf=p.fn.addBack,p.each({parent:function(a){var b=a.parentNode;return b&&b.nodeType!==11?b:null},parents:function(a){return p.dir(a,"parentNode")},parentsUntil:function(a,b,c){return p.dir(a,"parentNode",c)},next:function(a){return bi(a,"nextSibling")},prev:function(a){return bi(a,"previousSibling")},nextAll:function(a){return p.dir(a,"nextSibling")},prevAll:function(a){return p.dir(a,"previousSibling")},nextUntil:function(a,b,c){return p.dir(a,"nextSibling",c)},prevUntil:function(a,b,c){return p.dir(a,"previousSibling",c)},siblings:function(a){return p.sibling((a.parentNode||{}).firstChild,a)},children:function(a){return p.sibling(a.firstChild)},contents:function(a){return p.nodeName(a,"iframe")?a.contentDocument||a.contentWindow.document:p.merge([],a.childNodes)}},function(a,b){p.fn[a]=function(c,d){var e=p.map(this,b,c);return bc.test(a)||(d=c),d&&typeof d=="string"&&(e=p.filter(d,e)),e=this.length>1&&!bg[a]?p.unique(e):e,this.length>1&&bd.test(a)&&(e=e.reverse()),this.pushStack(e,a,k.call(arguments).join(","))}}),p.extend({filter:function(a,b,c){return c&&(a=":not("+a+")"),b.length===1?p.find.matchesSelector(b[0],a)?[b[0]]:[]:p.find.matches(a,b)},dir:function(a,c,d){var e=[],f=a[c];while(f&&f.nodeType!==9&&(d===b||f.nodeType!==1||!p(f).is(d)))f.nodeType===1&&e.push(f),f=f[c];return e},sibling:function(a,b){var c=[];for(;a;a=a.nextSibling)a.nodeType===1&&a!==b&&c.push(a);return c}});var bl="abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",bm=/ jQuery\d+="(?:null|\d+)"/g,bn=/^\s+/,bo=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,bp=/<([\w:]+)/,bq=/<tbody/i,br=/<|&#?\w+;/,bs=/<(?:script|style|link)/i,bt=/<(?:script|object|embed|option|style)/i,bu=new RegExp("<(?:"+bl+")[\\s/>]","i"),bv=/^(?:checkbox|radio)$/,bw=/checked\s*(?:[^=]|=\s*.checked.)/i,bx=/\/(java|ecma)script/i,by=/^\s*<!(?:\[CDATA\[|\-\-)|[\]\-]{2}>\s*$/g,bz={option:[1,"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],area:[1,"<map>","</map>"],_default:[0,"",""]},bA=bk(e),bB=bA.appendChild(e.createElement("div"));bz.optgroup=bz.option,bz.tbody=bz.tfoot=bz.colgroup=bz.caption=bz.thead,bz.th=bz.td,p.support.htmlSerialize||(bz._default=[1,"X<div>","</div>"]),p.fn.extend({text:function(a){return p.access(this,function(a){return a===b?p.text(this):this.empty().append((this[0]&&this[0].ownerDocument||e).createTextNode(a))},null,a,arguments.length)},wrapAll:function(a){if(p.isFunction(a))return this.each(function(b){p(this).wrapAll(a.call(this,b))});if(this[0]){var b=p(a,this[0].ownerDocument).eq(0).clone(!0);this[0].parentNode&&b.insertBefore(this[0]),b.map(function(){var a=this;while(a.firstChild&&a.firstChild.nodeType===1)a=a.firstChild;return a}).append(this)}return this},wrapInner:function(a){return p.isFunction(a)?this.each(function(b){p(this).wrapInner(a.call(this,b))}):this.each(function(){var b=p(this),c=b.contents();c.length?c.wrapAll(a):b.append(a)})},wrap:function(a){var b=p.isFunction(a);return this.each(function(c){p(this).wrapAll(b?a.call(this,c):a)})},unwrap:function(){return this.parent().each(function(){p.nodeName(this,"body")||p(this).replaceWith(this.childNodes)}).end()},append:function(){return this.domManip(arguments,!0,function(a){(this.nodeType===1||this.nodeType===11)&&this.appendChild(a)})},prepend:function(){return this.domManip(arguments,!0,function(a){(this.nodeType===1||this.nodeType===11)&&this.insertBefore(a,this.firstChild)})},before:function(){if(!bh(this[0]))return this.domManip(arguments,!1,function(a){this.parentNode.insertBefore(a,this)});if(arguments.length){var a=p.clean(arguments);return this.pushStack(p.merge(a,this),"before",this.selector)}},after:function(){if(!bh(this[0]))return this.domManip(arguments,!1,function(a){this.parentNode.insertBefore(a,this.nextSibling)});if(arguments.length){var a=p.clean(arguments);return this.pushStack(p.merge(this,a),"after",this.selector)}},remove:function(a,b){var c,d=0;for(;(c=this[d])!=null;d++)if(!a||p.filter(a,[c]).length)!b&&c.nodeType===1&&(p.cleanData(c.getElementsByTagName("*")),p.cleanData([c])),c.parentNode&&c.parentNode.removeChild(c);return this},empty:function(){var a,b=0;for(;(a=this[b])!=null;b++){a.nodeType===1&&p.cleanData(a.getElementsByTagName("*"));while(a.firstChild)a.removeChild(a.firstChild)}return this},clone:function(a,b){return a=a==null?!1:a,b=b==null?a:b,this.map(function(){return p.clone(this,a,b)})},html:function(a){return p.access(this,function(a){var c=this[0]||{},d=0,e=this.length;if(a===b)return c.nodeType===1?c.innerHTML.replace(bm,""):b;if(typeof a=="string"&&!bs.test(a)&&(p.support.htmlSerialize||!bu.test(a))&&(p.support.leadingWhitespace||!bn.test(a))&&!bz[(bp.exec(a)||["",""])[1].toLowerCase()]){a=a.replace(bo,"<$1></$2>");try{for(;d<e;d++)c=this[d]||{},c.nodeType===1&&(p.cleanData(c.getElementsByTagName("*")),c.innerHTML=a);c=0}catch(f){}}c&&this.empty().append(a)},null,a,arguments.length)},replaceWith:function(a){return bh(this[0])?this.length?this.pushStack(p(p.isFunction(a)?a():a),"replaceWith",a):this:p.isFunction(a)?this.each(function(b){var c=p(this),d=c.html();c.replaceWith(a.call(this,b,d))}):(typeof a!="string"&&(a=p(a).detach()),this.each(function(){var b=this.nextSibling,c=this.parentNode;p(this).remove(),b?p(b).before(a):p(c).append(a)}))},detach:function(a){return this.remove(a,!0)},domManip:function(a,c,d){a=[].concat.apply([],a);var e,f,g,h,i=0,j=a[0],k=[],l=this.length;if(!p.support.checkClone&&l>1&&typeof j=="string"&&bw.test(j))return this.each(function(){p(this).domManip(a,c,d)});if(p.isFunction(j))return this.each(function(e){var f=p(this);a[0]=j.call(this,e,c?f.html():b),f.domManip(a,c,d)});if(this[0]){e=p.buildFragment(a,this,k),g=e.fragment,f=g.firstChild,g.childNodes.length===1&&(g=f);if(f){c=c&&p.nodeName(f,"tr");for(h=e.cacheable||l-1;i<l;i++)d.call(c&&p.nodeName(this[i],"table")?bC(this[i],"tbody"):this[i],i===h?g:p.clone(g,!0,!0))}g=f=null,k.length&&p.each(k,function(a,b){b.src?p.ajax?p.ajax({url:b.src,type:"GET",dataType:"script",async:!1,global:!1,"throws":!0}):p.error("no ajax"):p.globalEval((b.text||b.textContent||b.innerHTML||"").replace(by,"")),b.parentNode&&b.parentNode.removeChild(b)})}return this}}),p.buildFragment=function(a,c,d){var f,g,h,i=a[0];return c=c||e,c=!c.nodeType&&c[0]||c,c=c.ownerDocument||c,a.length===1&&typeof i=="string"&&i.length<512&&c===e&&i.charAt(0)==="<"&&!bt.test(i)&&(p.support.checkClone||!bw.test(i))&&(p.support.html5Clone||!bu.test(i))&&(g=!0,f=p.fragments[i],h=f!==b),f||(f=c.createDocumentFragment(),p.clean(a,c,f,d),g&&(p.fragments[i]=h&&f)),{fragment:f,cacheable:g}},p.fragments={},p.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(a,b){p.fn[a]=function(c){var d,e=0,f=[],g=p(c),h=g.length,i=this.length===1&&this[0].parentNode;if((i==null||i&&i.nodeType===11&&i.childNodes.length===1)&&h===1)return g[b](this[0]),this;for(;e<h;e++)d=(e>0?this.clone(!0):this).get(),p(g[e])[b](d),f=f.concat(d);return this.pushStack(f,a,g.selector)}}),p.extend({clone:function(a,b,c){var d,e,f,g;p.support.html5Clone||p.isXMLDoc(a)||!bu.test("<"+a.nodeName+">")?g=a.cloneNode(!0):(bB.innerHTML=a.outerHTML,bB.removeChild(g=bB.firstChild));if((!p.support.noCloneEvent||!p.support.noCloneChecked)&&(a.nodeType===1||a.nodeType===11)&&!p.isXMLDoc(a)){bE(a,g),d=bF(a),e=bF(g);for(f=0;d[f];++f)e[f]&&bE(d[f],e[f])}if(b){bD(a,g);if(c){d=bF(a),e=bF(g);for(f=0;d[f];++f)bD(d[f],e[f])}}return d=e=null,g},clean:function(a,b,c,d){var f,g,h,i,j,k,l,m,n,o,q,r,s=b===e&&bA,t=[];if(!b||typeof b.createDocumentFragment=="undefined")b=e;for(f=0;(h=a[f])!=null;f++){typeof h=="number"&&(h+="");if(!h)continue;if(typeof h=="string")if(!br.test(h))h=b.createTextNode(h);else{s=s||bk(b),l=b.createElement("div"),s.appendChild(l),h=h.replace(bo,"<$1></$2>"),i=(bp.exec(h)||["",""])[1].toLowerCase(),j=bz[i]||bz._default,k=j[0],l.innerHTML=j[1]+h+j[2];while(k--)l=l.lastChild;if(!p.support.tbody){m=bq.test(h),n=i==="table"&&!m?l.firstChild&&l.firstChild.childNodes:j[1]==="<table>"&&!m?l.childNodes:[];for(g=n.length-1;g>=0;--g)p.nodeName(n[g],"tbody")&&!n[g].childNodes.length&&n[g].parentNode.removeChild(n[g])}!p.support.leadingWhitespace&&bn.test(h)&&l.insertBefore(b.createTextNode(bn.exec(h)[0]),l.firstChild),h=l.childNodes,l.parentNode.removeChild(l)}h.nodeType?t.push(h):p.merge(t,h)}l&&(h=l=s=null);if(!p.support.appendChecked)for(f=0;(h=t[f])!=null;f++)p.nodeName(h,"input")?bG(h):typeof h.getElementsByTagName!="undefined"&&p.grep(h.getElementsByTagName("input"),bG);if(c){q=function(a){if(!a.type||bx.test(a.type))return d?d.push(a.parentNode?a.parentNode.removeChild(a):a):c.appendChild(a)};for(f=0;(h=t[f])!=null;f++)if(!p.nodeName(h,"script")||!q(h))c.appendChild(h),typeof h.getElementsByTagName!="undefined"&&(r=p.grep(p.merge([],h.getElementsByTagName("script")),q),t.splice.apply(t,[f+1,0].concat(r)),f+=r.length)}return t},cleanData:function(a,b){var c,d,e,f,g=0,h=p.expando,i=p.cache,j=p.support.deleteExpando,k=p.event.special;for(;(e=a[g])!=null;g++)if(b||p.acceptData(e)){d=e[h],c=d&&i[d];if(c){if(c.events)for(f in c.events)k[f]?p.event.remove(e,f):p.removeEvent(e,f,c.handle);i[d]&&(delete i[d],j?delete e[h]:e.removeAttribute?e.removeAttribute(h):e[h]=null,p.deletedIds.push(d))}}}}),function(){var a,b;p.uaMatch=function(a){a=a.toLowerCase();var b=/(chrome)[ \/]([\w.]+)/.exec(a)||/(webkit)[ \/]([\w.]+)/.exec(a)||/(opera)(?:.*version|)[ \/]([\w.]+)/.exec(a)||/(msie) ([\w.]+)/.exec(a)||a.indexOf("compatible")<0&&/(mozilla)(?:.*? rv:([\w.]+)|)/.exec(a)||[];return{browser:b[1]||"",version:b[2]||"0"}},a=p.uaMatch(g.userAgent),b={},a.browser&&(b[a.browser]=!0,b.version=a.version),b.chrome?b.webkit=!0:b.webkit&&(b.safari=!0),p.browser=b,p.sub=function(){function a(b,c){return new a.fn.init(b,c)}p.extend(!0,a,this),a.superclass=this,a.fn=a.prototype=this(),a.fn.constructor=a,a.sub=this.sub,a.fn.init=function c(c,d){return d&&d instanceof p&&!(d instanceof a)&&(d=a(d)),p.fn.init.call(this,c,d,b)},a.fn.init.prototype=a.fn;var b=a(e);return a}}();var bH,bI,bJ,bK=/alpha\([^)]*\)/i,bL=/opacity=([^)]*)/,bM=/^(top|right|bottom|left)$/,bN=/^(none|table(?!-c[ea]).+)/,bO=/^margin/,bP=new RegExp("^("+q+")(.*)$","i"),bQ=new RegExp("^("+q+")(?!px)[a-z%]+$","i"),bR=new RegExp("^([-+])=("+q+")","i"),bS={},bT={position:"absolute",visibility:"hidden",display:"block"},bU={letterSpacing:0,fontWeight:400},bV=["Top","Right","Bottom","Left"],bW=["Webkit","O","Moz","ms"],bX=p.fn.toggle;p.fn.extend({css:function(a,c){return p.access(this,function(a,c,d){return d!==b?p.style(a,c,d):p.css(a,c)},a,c,arguments.length>1)},show:function(){return b$(this,!0)},hide:function(){return b$(this)},toggle:function(a,b){var c=typeof a=="boolean";return p.isFunction(a)&&p.isFunction(b)?bX.apply(this,arguments):this.each(function(){(c?a:bZ(this))?p(this).show():p(this).hide()})}}),p.extend({cssHooks:{opacity:{get:function(a,b){if(b){var c=bH(a,"opacity");return c===""?"1":c}}}},cssNumber:{fillOpacity:!0,fontWeight:!0,lineHeight:!0,opacity:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{"float":p.support.cssFloat?"cssFloat":"styleFloat"},style:function(a,c,d,e){if(!a||a.nodeType===3||a.nodeType===8||!a.style)return;var f,g,h,i=p.camelCase(c),j=a.style;c=p.cssProps[i]||(p.cssProps[i]=bY(j,i)),h=p.cssHooks[c]||p.cssHooks[i];if(d===b)return h&&"get"in h&&(f=h.get(a,!1,e))!==b?f:j[c];g=typeof d,g==="string"&&(f=bR.exec(d))&&(d=(f[1]+1)*f[2]+parseFloat(p.css(a,c)),g="number");if(d==null||g==="number"&&isNaN(d))return;g==="number"&&!p.cssNumber[i]&&(d+="px");if(!h||!("set"in h)||(d=h.set(a,d,e))!==b)try{j[c]=d}catch(k){}},css:function(a,c,d,e){var f,g,h,i=p.camelCase(c);return c=p.cssProps[i]||(p.cssProps[i]=bY(a.style,i)),h=p.cssHooks[c]||p.cssHooks[i],h&&"get"in h&&(f=h.get(a,!0,e)),f===b&&(f=bH(a,c)),f==="normal"&&c in bU&&(f=bU[c]),d||e!==b?(g=parseFloat(f),d||p.isNumeric(g)?g||0:f):f},swap:function(a,b,c){var d,e,f={};for(e in b)f[e]=a.style[e],a.style[e]=b[e];d=c.call(a);for(e in b)a.style[e]=f[e];return d}}),a.getComputedStyle?bH=function(b,c){var d,e,f,g,h=a.getComputedStyle(b,null),i=b.style;return h&&(d=h[c],d===""&&!p.contains(b.ownerDocument,b)&&(d=p.style(b,c)),bQ.test(d)&&bO.test(c)&&(e=i.width,f=i.minWidth,g=i.maxWidth,i.minWidth=i.maxWidth=i.width=d,d=h.width,i.width=e,i.minWidth=f,i.maxWidth=g)),d}:e.documentElement.currentStyle&&(bH=function(a,b){var c,d,e=a.currentStyle&&a.currentStyle[b],f=a.style;return e==null&&f&&f[b]&&(e=f[b]),bQ.test(e)&&!bM.test(b)&&(c=f.left,d=a.runtimeStyle&&a.runtimeStyle.left,d&&(a.runtimeStyle.left=a.currentStyle.left),f.left=b==="fontSize"?"1em":e,e=f.pixelLeft+"px",f.left=c,d&&(a.runtimeStyle.left=d)),e===""?"auto":e}),p.each(["height","width"],function(a,b){p.cssHooks[b]={get:function(a,c,d){if(c)return a.offsetWidth===0&&bN.test(bH(a,"display"))?p.swap(a,bT,function(){return cb(a,b,d)}):cb(a,b,d)},set:function(a,c,d){return b_(a,c,d?ca(a,b,d,p.support.boxSizing&&p.css(a,"boxSizing")==="border-box"):0)}}}),p.support.opacity||(p.cssHooks.opacity={get:function(a,b){return bL.test((b&&a.currentStyle?a.currentStyle.filter:a.style.filter)||"")?.01*parseFloat(RegExp.$1)+"":b?"1":""},set:function(a,b){var c=a.style,d=a.currentStyle,e=p.isNumeric(b)?"alpha(opacity="+b*100+")":"",f=d&&d.filter||c.filter||"";c.zoom=1;if(b>=1&&p.trim(f.replace(bK,""))===""&&c.removeAttribute){c.removeAttribute("filter");if(d&&!d.filter)return}c.filter=bK.test(f)?f.replace(bK,e):f+" "+e}}),p(function(){p.support.reliableMarginRight||(p.cssHooks.marginRight={get:function(a,b){return p.swap(a,{display:"inline-block"},function(){if(b)return bH(a,"marginRight")})}}),!p.support.pixelPosition&&p.fn.position&&p.each(["top","left"],function(a,b){p.cssHooks[b]={get:function(a,c){if(c){var d=bH(a,b);return bQ.test(d)?p(a).position()[b]+"px":d}}}})}),p.expr&&p.expr.filters&&(p.expr.filters.hidden=function(a){return a.offsetWidth===0&&a.offsetHeight===0||!p.support.reliableHiddenOffsets&&(a.style&&a.style.display||bH(a,"display"))==="none"},p.expr.filters.visible=function(a){return!p.expr.filters.hidden(a)}),p.each({margin:"",padding:"",border:"Width"},function(a,b){p.cssHooks[a+b]={expand:function(c){var d,e=typeof c=="string"?c.split(" "):[c],f={};for(d=0;d<4;d++)f[a+bV[d]+b]=e[d]||e[d-2]||e[0];return f}},bO.test(a)||(p.cssHooks[a+b].set=b_)});var cd=/%20/g,ce=/\[\]$/,cf=/\r?\n/g,cg=/^(?:color|date|datetime|datetime-local|email|hidden|month|number|password|range|search|tel|text|time|url|week)$/i,ch=/^(?:select|textarea)/i;p.fn.extend({serialize:function(){return p.param(this.serializeArray())},serializeArray:function(){return this.map(function(){return this.elements?p.makeArray(this.elements):this}).filter(function(){return this.name&&!this.disabled&&(this.checked||ch.test(this.nodeName)||cg.test(this.type))}).map(function(a,b){var c=p(this).val();return c==null?null:p.isArray(c)?p.map(c,function(a,c){return{name:b.name,value:a.replace(cf,"\r\n")}}):{name:b.name,value:c.replace(cf,"\r\n")}}).get()}}),p.param=function(a,c){var d,e=[],f=function(a,b){b=p.isFunction(b)?b():b==null?"":b,e[e.length]=encodeURIComponent(a)+"="+encodeURIComponent(b)};c===b&&(c=p.ajaxSettings&&p.ajaxSettings.traditional);if(p.isArray(a)||a.jquery&&!p.isPlainObject(a))p.each(a,function(){f(this.name,this.value)});else for(d in a)ci(d,a[d],c,f);return e.join("&").replace(cd,"+")};var cj,ck,cl=/#.*$/,cm=/^(.*?):[ \t]*([^\r\n]*)\r?$/mg,cn=/^(?:about|app|app\-storage|.+\-extension|file|res|widget):$/,co=/^(?:GET|HEAD)$/,cp=/^\/\//,cq=/\?/,cr=/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,cs=/([?&])_=[^&]*/,ct=/^([\w\+\.\-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,cu=p.fn.load,cv={},cw={},cx=["*/"]+["*"];try{ck=f.href}catch(cy){ck=e.createElement("a"),ck.href="",ck=ck.href}cj=ct.exec(ck.toLowerCase())||[],p.fn.load=function(a,c,d){if(typeof a!="string"&&cu)return cu.apply(this,arguments);if(!this.length)return this;var e,f,g,h=this,i=a.indexOf(" ");return i>=0&&(e=a.slice(i,a.length),a=a.slice(0,i)),p.isFunction(c)?(d=c,c=b):c&&typeof c=="object"&&(f="POST"),p.ajax({url:a,type:f,dataType:"html",data:c,complete:function(a,b){d&&h.each(d,g||[a.responseText,b,a])}}).done(function(a){g=arguments,h.html(e?p("<div>").append(a.replace(cr,"")).find(e):a)}),this},p.each("ajaxStart ajaxStop ajaxComplete ajaxError ajaxSuccess ajaxSend".split(" "),function(a,b){p.fn[b]=function(a){return this.on(b,a)}}),p.each(["get","post"],function(a,c){p[c]=function(a,d,e,f){return p.isFunction(d)&&(f=f||e,e=d,d=b),p.ajax({type:c,url:a,data:d,success:e,dataType:f})}}),p.extend({getScript:function(a,c){return p.get(a,b,c,"script")},getJSON:function(a,b,c){return p.get(a,b,c,"json")},ajaxSetup:function(a,b){return b?cB(a,p.ajaxSettings):(b=a,a=p.ajaxSettings),cB(a,b),a},ajaxSettings:{url:ck,isLocal:cn.test(cj[1]),global:!0,type:"GET",contentType:"application/x-www-form-urlencoded; charset=UTF-8",processData:!0,async:!0,accepts:{xml:"application/xml, text/xml",html:"text/html",text:"text/plain",json:"application/json, text/javascript","*":cx},contents:{xml:/xml/,html:/html/,json:/json/},responseFields:{xml:"responseXML",text:"responseText"},converters:{"* text":a.String,"text html":!0,"text json":p.parseJSON,"text xml":p.parseXML},flatOptions:{context:!0,url:!0}},ajaxPrefilter:cz(cv),ajaxTransport:cz(cw),ajax:function(a,c){function y(a,c,f,i){var k,s,t,u,w,y=c;if(v===2)return;v=2,h&&clearTimeout(h),g=b,e=i||"",x.readyState=a>0?4:0,f&&(u=cC(l,x,f));if(a>=200&&a<300||a===304)l.ifModified&&(w=x.getResponseHeader("Last-Modified"),w&&(p.lastModified[d]=w),w=x.getResponseHeader("Etag"),w&&(p.etag[d]=w)),a===304?(y="notmodified",k=!0):(k=cD(l,u),y=k.state,s=k.data,t=k.error,k=!t);else{t=y;if(!y||a)y="error",a<0&&(a=0)}x.status=a,x.statusText=(c||y)+"",k?o.resolveWith(m,[s,y,x]):o.rejectWith(m,[x,y,t]),x.statusCode(r),r=b,j&&n.trigger("ajax"+(k?"Success":"Error"),[x,l,k?s:t]),q.fireWith(m,[x,y]),j&&(n.trigger("ajaxComplete",[x,l]),--p.active||p.event.trigger("ajaxStop"))}typeof a=="object"&&(c=a,a=b),c=c||{};var d,e,f,g,h,i,j,k,l=p.ajaxSetup({},c),m=l.context||l,n=m!==l&&(m.nodeType||m instanceof p)?p(m):p.event,o=p.Deferred(),q=p.Callbacks("once memory"),r=l.statusCode||{},t={},u={},v=0,w="canceled",x={readyState:0,setRequestHeader:function(a,b){if(!v){var c=a.toLowerCase();a=u[c]=u[c]||a,t[a]=b}return this},getAllResponseHeaders:function(){return v===2?e:null},getResponseHeader:function(a){var c;if(v===2){if(!f){f={};while(c=cm.exec(e))f[c[1].toLowerCase()]=c[2]}c=f[a.toLowerCase()]}return c===b?null:c},overrideMimeType:function(a){return v||(l.mimeType=a),this},abort:function(a){return a=a||w,g&&g.abort(a),y(0,a),this}};o.promise(x),x.success=x.done,x.error=x.fail,x.complete=q.add,x.statusCode=function(a){if(a){var b;if(v<2)for(b in a)r[b]=[r[b],a[b]];else b=a[x.status],x.always(b)}return this},l.url=((a||l.url)+"").replace(cl,"").replace(cp,cj[1]+"//"),l.dataTypes=p.trim(l.dataType||"*").toLowerCase().split(s),l.crossDomain==null&&(i=ct.exec(l.url.toLowerCase())||!1,l.crossDomain=i&&i.join(":")+(i[3]?"":i[1]==="http:"?80:443)!==cj.join(":")+(cj[3]?"":cj[1]==="http:"?80:443)),l.data&&l.processData&&typeof l.data!="string"&&(l.data=p.param(l.data,l.traditional)),cA(cv,l,c,x);if(v===2)return x;j=l.global,l.type=l.type.toUpperCase(),l.hasContent=!co.test(l.type),j&&p.active++===0&&p.event.trigger("ajaxStart");if(!l.hasContent){l.data&&(l.url+=(cq.test(l.url)?"&":"?")+l.data,delete l.data),d=l.url;if(l.cache===!1){var z=p.now(),A=l.url.replace(cs,"$1_="+z);l.url=A+(A===l.url?(cq.test(l.url)?"&":"?")+"_="+z:"")}}(l.data&&l.hasContent&&l.contentType!==!1||c.contentType)&&x.setRequestHeader("Content-Type",l.contentType),l.ifModified&&(d=d||l.url,p.lastModified[d]&&x.setRequestHeader("If-Modified-Since",p.lastModified[d]),p.etag[d]&&x.setRequestHeader("If-None-Match",p.etag[d])),x.setRequestHeader("Accept",l.dataTypes[0]&&l.accepts[l.dataTypes[0]]?l.accepts[l.dataTypes[0]]+(l.dataTypes[0]!=="*"?", "+cx+"; q=0.01":""):l.accepts["*"]);for(k in l.headers)x.setRequestHeader(k,l.headers[k]);if(!l.beforeSend||l.beforeSend.call(m,x,l)!==!1&&v!==2){w="abort";for(k in{success:1,error:1,complete:1})x[k](l[k]);g=cA(cw,l,c,x);if(!g)y(-1,"No Transport");else{x.readyState=1,j&&n.trigger("ajaxSend",[x,l]),l.async&&l.timeout>0&&(h=setTimeout(function(){x.abort("timeout")},l.timeout));try{v=1,g.send(t,y)}catch(B){if(v<2)y(-1,B);else throw B}}return x}return x.abort()},active:0,lastModified:{},etag:{}});var cE=[],cF=/\?/,cG=/(=)\?(?=&|$)|\?\?/,cH=p.now();p.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var a=cE.pop()||p.expando+"_"+cH++;return this[a]=!0,a}}),p.ajaxPrefilter("json jsonp",function(c,d,e){var f,g,h,i=c.data,j=c.url,k=c.jsonp!==!1,l=k&&cG.test(j),m=k&&!l&&typeof i=="string"&&!(c.contentType||"").indexOf("application/x-www-form-urlencoded")&&cG.test(i);if(c.dataTypes[0]==="jsonp"||l||m)return f=c.jsonpCallback=p.isFunction(c.jsonpCallback)?c.jsonpCallback():c.jsonpCallback,g=a[f],l?c.url=j.replace(cG,"$1"+f):m?c.data=i.replace(cG,"$1"+f):k&&(c.url+=(cF.test(j)?"&":"?")+c.jsonp+"="+f),c.converters["script json"]=function(){return h||p.error(f+" was not called"),h[0]},c.dataTypes[0]="json",a[f]=function(){h=arguments},e.always(function(){a[f]=g,c[f]&&(c.jsonpCallback=d.jsonpCallback,cE.push(f)),h&&p.isFunction(g)&&g(h[0]),h=g=b}),"script"}),p.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/javascript|ecmascript/},converters:{"text script":function(a){return p.globalEval(a),a}}}),p.ajaxPrefilter("script",function(a){a.cache===b&&(a.cache=!1),a.crossDomain&&(a.type="GET",a.global=!1)}),p.ajaxTransport("script",function(a){if(a.crossDomain){var c,d=e.head||e.getElementsByTagName("head")[0]||e.documentElement;return{send:function(f,g){c=e.createElement("script"),c.async="async",a.scriptCharset&&(c.charset=a.scriptCharset),c.src=a.url,c.onload=c.onreadystatechange=function(a,e){if(e||!c.readyState||/loaded|complete/.test(c.readyState))c.onload=c.onreadystatechange=null,d&&c.parentNode&&d.removeChild(c),c=b,e||g(200,"success")},d.insertBefore(c,d.firstChild)},abort:function(){c&&c.onload(0,1)}}}});var cI,cJ=a.ActiveXObject?function(){for(var a in cI)cI[a](0,1)}:!1,cK=0;p.ajaxSettings.xhr=a.ActiveXObject?function(){return!this.isLocal&&cL()||cM()}:cL,function(a){p.extend(p.support,{ajax:!!a,cors:!!a&&"withCredentials"in a})}(p.ajaxSettings.xhr()),p.support.ajax&&p.ajaxTransport(function(c){if(!c.crossDomain||p.support.cors){var d;return{send:function(e,f){var g,h,i=c.xhr();c.username?i.open(c.type,c.url,c.async,c.username,c.password):i.open(c.type,c.url,c.async);if(c.xhrFields)for(h in c.xhrFields)i[h]=c.xhrFields[h];c.mimeType&&i.overrideMimeType&&i.overrideMimeType(c.mimeType),!c.crossDomain&&!e["X-Requested-With"]&&(e["X-Requested-With"]="XMLHttpRequest");try{for(h in e)i.setRequestHeader(h,e[h])}catch(j){}i.send(c.hasContent&&c.data||null),d=function(a,e){var h,j,k,l,m;try{if(d&&(e||i.readyState===4)){d=b,g&&(i.onreadystatechange=p.noop,cJ&&delete cI[g]);if(e)i.readyState!==4&&i.abort();else{h=i.status,k=i.getAllResponseHeaders(),l={},m=i.responseXML,m&&m.documentElement&&(l.xml=m);try{l.text=i.responseText}catch(a){}try{j=i.statusText}catch(n){j=""}!h&&c.isLocal&&!c.crossDomain?h=l.text?200:404:h===1223&&(h=204)}}}catch(o){e||f(-1,o)}l&&f(h,j,l,k)},c.async?i.readyState===4?setTimeout(d,0):(g=++cK,cJ&&(cI||(cI={},p(a).unload(cJ)),cI[g]=d),i.onreadystatechange=d):d()},abort:function(){d&&d(0,1)}}}});var cN,cO,cP=/^(?:toggle|show|hide)$/,cQ=new RegExp("^(?:([-+])=|)("+q+")([a-z%]*)$","i"),cR=/queueHooks$/,cS=[cY],cT={"*":[function(a,b){var c,d,e=this.createTween(a,b),f=cQ.exec(b),g=e.cur(),h=+g||0,i=1,j=20;if(f){c=+f[2],d=f[3]||(p.cssNumber[a]?"":"px");if(d!=="px"&&h){h=p.css(e.elem,a,!0)||c||1;do i=i||".5",h=h/i,p.style(e.elem,a,h+d);while(i!==(i=e.cur()/g)&&i!==1&&--j)}e.unit=d,e.start=h,e.end=f[1]?h+(f[1]+1)*c:c}return e}]};p.Animation=p.extend(cW,{tweener:function(a,b){p.isFunction(a)?(b=a,a=["*"]):a=a.split(" ");var c,d=0,e=a.length;for(;d<e;d++)c=a[d],cT[c]=cT[c]||[],cT[c].unshift(b)},prefilter:function(a,b){b?cS.unshift(a):cS.push(a)}}),p.Tween=cZ,cZ.prototype={constructor:cZ,init:function(a,b,c,d,e,f){this.elem=a,this.prop=c,this.easing=e||"swing",this.options=b,this.start=this.now=this.cur(),this.end=d,this.unit=f||(p.cssNumber[c]?"":"px")},cur:function(){var a=cZ.propHooks[this.prop];return a&&a.get?a.get(this):cZ.propHooks._default.get(this)},run:function(a){var b,c=cZ.propHooks[this.prop];return this.options.duration?this.pos=b=p.easing[this.easing](a,this.options.duration*a,0,1,this.options.duration):this.pos=b=a,this.now=(this.end-this.start)*b+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),c&&c.set?c.set(this):cZ.propHooks._default.set(this),this}},cZ.prototype.init.prototype=cZ.prototype,cZ.propHooks={_default:{get:function(a){var b;return a.elem[a.prop]==null||!!a.elem.style&&a.elem.style[a.prop]!=null?(b=p.css(a.elem,a.prop,!1,""),!b||b==="auto"?0:b):a.elem[a.prop]},set:function(a){p.fx.step[a.prop]?p.fx.step[a.prop](a):a.elem.style&&(a.elem.style[p.cssProps[a.prop]]!=null||p.cssHooks[a.prop])?p.style(a.elem,a.prop,a.now+a.unit):a.elem[a.prop]=a.now}}},cZ.propHooks.scrollTop=cZ.propHooks.scrollLeft={set:function(a){a.elem.nodeType&&a.elem.parentNode&&(a.elem[a.prop]=a.now)}},p.each(["toggle","show","hide"],function(a,b){var c=p.fn[b];p.fn[b]=function(d,e,f){return d==null||typeof d=="boolean"||!a&&p.isFunction(d)&&p.isFunction(e)?c.apply(this,arguments):this.animate(c$(b,!0),d,e,f)}}),p.fn.extend({fadeTo:function(a,b,c,d){return this.filter(bZ).css("opacity",0).show().end().animate({opacity:b},a,c,d)},animate:function(a,b,c,d){var e=p.isEmptyObject(a),f=p.speed(b,c,d),g=function(){var b=cW(this,p.extend({},a),f);e&&b.stop(!0)};return e||f.queue===!1?this.each(g):this.queue(f.queue,g)},stop:function(a,c,d){var e=function(a){var b=a.stop;delete a.stop,b(d)};return typeof a!="string"&&(d=c,c=a,a=b),c&&a!==!1&&this.queue(a||"fx",[]),this.each(function(){var b=!0,c=a!=null&&a+"queueHooks",f=p.timers,g=p._data(this);if(c)g[c]&&g[c].stop&&e(g[c]);else for(c in g)g[c]&&g[c].stop&&cR.test(c)&&e(g[c]);for(c=f.length;c--;)f[c].elem===this&&(a==null||f[c].queue===a)&&(f[c].anim.stop(d),b=!1,f.splice(c,1));(b||!d)&&p.dequeue(this,a)})}}),p.each({slideDown:c$("show"),slideUp:c$("hide"),slideToggle:c$("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(a,b){p.fn[a]=function(a,c,d){return this.animate(b,a,c,d)}}),p.speed=function(a,b,c){var d=a&&typeof a=="object"?p.extend({},a):{complete:c||!c&&b||p.isFunction(a)&&a,duration:a,easing:c&&b||b&&!p.isFunction(b)&&b};d.duration=p.fx.off?0:typeof d.duration=="number"?d.duration:d.duration in p.fx.speeds?p.fx.speeds[d.duration]:p.fx.speeds._default;if(d.queue==null||d.queue===!0)d.queue="fx";return d.old=d.complete,d.complete=function(){p.isFunction(d.old)&&d.old.call(this),d.queue&&p.dequeue(this,d.queue)},d},p.easing={linear:function(a){return a},swing:function(a){return.5-Math.cos(a*Math.PI)/2}},p.timers=[],p.fx=cZ.prototype.init,p.fx.tick=function(){var a,b=p.timers,c=0;for(;c<b.length;c++)a=b[c],!a()&&b[c]===a&&b.splice(c--,1);b.length||p.fx.stop()},p.fx.timer=function(a){a()&&p.timers.push(a)&&!cO&&(cO=setInterval(p.fx.tick,p.fx.interval))},p.fx.interval=13,p.fx.stop=function(){clearInterval(cO),cO=null},p.fx.speeds={slow:600,fast:200,_default:400},p.fx.step={},p.expr&&p.expr.filters&&(p.expr.filters.animated=function(a){return p.grep(p.timers,function(b){return a===b.elem}).length});var c_=/^(?:body|html)$/i;p.fn.offset=function(a){if(arguments.length)return a===b?this:this.each(function(b){p.offset.setOffset(this,a,b)});var c,d,e,f,g,h,i,j={top:0,left:0},k=this[0],l=k&&k.ownerDocument;if(!l)return;return(d=l.body)===k?p.offset.bodyOffset(k):(c=l.documentElement,p.contains(c,k)?(typeof k.getBoundingClientRect!="undefined"&&(j=k.getBoundingClientRect()),e=da(l),f=c.clientTop||d.clientTop||0,g=c.clientLeft||d.clientLeft||0,h=e.pageYOffset||c.scrollTop,i=e.pageXOffset||c.scrollLeft,{top:j.top+h-f,left:j.left+i-g}):j)},p.offset={bodyOffset:function(a){var b=a.offsetTop,c=a.offsetLeft;return p.support.doesNotIncludeMarginInBodyOffset&&(b+=parseFloat(p.css(a,"marginTop"))||0,c+=parseFloat(p.css(a,"marginLeft"))||0),{top:b,left:c}},setOffset:function(a,b,c){var d=p.css(a,"position");d==="static"&&(a.style.position="relative");var e=p(a),f=e.offset(),g=p.css(a,"top"),h=p.css(a,"left"),i=(d==="absolute"||d==="fixed")&&p.inArray("auto",[g,h])>-1,j={},k={},l,m;i?(k=e.position(),l=k.top,m=k.left):(l=parseFloat(g)||0,m=parseFloat(h)||0),p.isFunction(b)&&(b=b.call(a,c,f)),b.top!=null&&(j.top=b.top-f.top+l),b.left!=null&&(j.left=b.left-f.left+m),"using"in b?b.using.call(a,j):e.css(j)}},p.fn.extend({position:function(){if(!this[0])return;var a=this[0],b=this.offsetParent(),c=this.offset(),d=c_.test(b[0].nodeName)?{top:0,left:0}:b.offset();return c.top-=parseFloat(p.css(a,"marginTop"))||0,c.left-=parseFloat(p.css(a,"marginLeft"))||0,d.top+=parseFloat(p.css(b[0],"borderTopWidth"))||0,d.left+=parseFloat(p.css(b[0],"borderLeftWidth"))||0,{top:c.top-d.top,left:c.left-d.left}},offsetParent:function(){return this.map(function(){var a=this.offsetParent||e.body;while(a&&!c_.test(a.nodeName)&&p.css(a,"position")==="static")a=a.offsetParent;return a||e.body})}}),p.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(a,c){var d=/Y/.test(c);p.fn[a]=function(e){return p.access(this,function(a,e,f){var g=da(a);if(f===b)return g?c in g?g[c]:g.document.documentElement[e]:a[e];g?g.scrollTo(d?p(g).scrollLeft():f,d?f:p(g).scrollTop()):a[e]=f},a,e,arguments.length,null)}}),p.each({Height:"height",Width:"width"},function(a,c){p.each({padding:"inner"+a,content:c,"":"outer"+a},function(d,e){p.fn[e]=function(e,f){var g=arguments.length&&(d||typeof e!="boolean"),h=d||(e===!0||f===!0?"margin":"border");return p.access(this,function(c,d,e){var f;return p.isWindow(c)?c.document.documentElement["client"+a]:c.nodeType===9?(f=c.documentElement,Math.max(c.body["scroll"+a],f["scroll"+a],c.body["offset"+a],f["offset"+a],f["client"+a])):e===b?p.css(c,d,e,h):p.style(c,d,e,h)},c,g?e:b,g,null)}})}),a.jQuery=a.$=p,typeof define=="function"&&define.amd&&define.amd.jQuery&&define("jquery",[],function(){return p})})(window);
</script>	


<script type="text/javascript">
/*! jQuery UI - v1.8.23 - 2012-08-15
* Includes: jquery.ui.core.js
* Copyright (c) 2012 AUTHORS.txt; Licensed MIT, GPL */
(function(a,b){function c(b,c){var e=b.nodeName.toLowerCase();if("area"===e){var f=b.parentNode,g=f.name,h;return!b.href||!g||f.nodeName.toLowerCase()!=="map"?!1:(h=a("img[usemap=#"+g+"]")[0],!!h&&d(h))}return(/input|select|textarea|button|object/.test(e)?!b.disabled:"a"==e?b.href||c:c)&&d(b)}function d(b){return!a(b).parents().andSelf().filter(function(){return a.curCSS(this,"visibility")==="hidden"||a.expr.filters.hidden(this)}).length}a.ui=a.ui||{};if(a.ui.version)return;a.extend(a.ui,{version:"1.8.23",keyCode:{ALT:18,BACKSPACE:8,CAPS_LOCK:20,COMMA:188,COMMAND:91,COMMAND_LEFT:91,COMMAND_RIGHT:93,CONTROL:17,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,INSERT:45,LEFT:37,MENU:93,NUMPAD_ADD:107,NUMPAD_DECIMAL:110,NUMPAD_DIVIDE:111,NUMPAD_ENTER:108,NUMPAD_MULTIPLY:106,NUMPAD_SUBTRACT:109,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SHIFT:16,SPACE:32,TAB:9,UP:38,WINDOWS:91}}),a.fn.extend({propAttr:a.fn.prop||a.fn.attr,_focus:a.fn.focus,focus:function(b,c){return typeof b=="number"?this.each(function(){var d=this;setTimeout(function(){a(d).focus(),c&&c.call(d)},b)}):this._focus.apply(this,arguments)},scrollParent:function(){var b;return a.browser.msie&&/(static|relative)/.test(this.css("position"))||/absolute/.test(this.css("position"))?b=this.parents().filter(function(){return/(relative|absolute|fixed)/.test(a.curCSS(this,"position",1))&&/(auto|scroll)/.test(a.curCSS(this,"overflow",1)+a.curCSS(this,"overflow-y",1)+a.curCSS(this,"overflow-x",1))}).eq(0):b=this.parents().filter(function(){return/(auto|scroll)/.test(a.curCSS(this,"overflow",1)+a.curCSS(this,"overflow-y",1)+a.curCSS(this,"overflow-x",1))}).eq(0),/fixed/.test(this.css("position"))||!b.length?a(document):b},zIndex:function(c){if(c!==b)return this.css("zIndex",c);if(this.length){var d=a(this[0]),e,f;while(d.length&&d[0]!==document){e=d.css("position");if(e==="absolute"||e==="relative"||e==="fixed"){f=parseInt(d.css("zIndex"),10);if(!isNaN(f)&&f!==0)return f}d=d.parent()}}return 0},disableSelection:function(){return this.bind((a.support.selectstart?"selectstart":"mousedown")+".ui-disableSelection",function(a){a.preventDefault()})},enableSelection:function(){return this.unbind(".ui-disableSelection")}}),a("<a>").outerWidth(1).jquery||a.each(["Width","Height"],function(c,d){function h(b,c,d,f){return a.each(e,function(){c-=parseFloat(a.curCSS(b,"padding"+this,!0))||0,d&&(c-=parseFloat(a.curCSS(b,"border"+this+"Width",!0))||0),f&&(c-=parseFloat(a.curCSS(b,"margin"+this,!0))||0)}),c}var e=d==="Width"?["Left","Right"]:["Top","Bottom"],f=d.toLowerCase(),g={innerWidth:a.fn.innerWidth,innerHeight:a.fn.innerHeight,outerWidth:a.fn.outerWidth,outerHeight:a.fn.outerHeight};a.fn["inner"+d]=function(c){return c===b?g["inner"+d].call(this):this.each(function(){a(this).css(f,h(this,c)+"px")})},a.fn["outer"+d]=function(b,c){return typeof b!="number"?g["outer"+d].call(this,b):this.each(function(){a(this).css(f,h(this,b,!0,c)+"px")})}}),a.extend(a.expr[":"],{data:a.expr.createPseudo?a.expr.createPseudo(function(b){return function(c){return!!a.data(c,b)}}):function(b,c,d){return!!a.data(b,d[3])},focusable:function(b){return c(b,!isNaN(a.attr(b,"tabindex")))},tabbable:function(b){var d=a.attr(b,"tabindex"),e=isNaN(d);return(e||d>=0)&&c(b,!e)}}),a(function(){var b=document.body,c=b.appendChild(c=document.createElement("div"));c.offsetHeight,a.extend(c.style,{minHeight:"100px",height:"auto",padding:0,borderWidth:0}),a.support.minHeight=c.offsetHeight===100,a.support.selectstart="onselectstart"in c,b.removeChild(c).style.display="none"}),a.curCSS||(a.curCSS=a.css),a.extend(a.ui,{plugin:{add:function(b,c,d){var e=a.ui[b].prototype;for(var f in d)e.plugins[f]=e.plugins[f]||[],e.plugins[f].push([c,d[f]])},call:function(a,b,c){var d=a.plugins[b];if(!d||!a.element[0].parentNode)return;for(var e=0;e<d.length;e++)a.options[d[e][0]]&&d[e][1].apply(a.element,c)}},contains:function(a,b){return document.compareDocumentPosition?a.compareDocumentPosition(b)&16:a!==b&&a.contains(b)},hasScroll:function(b,c){if(a(b).css("overflow")==="hidden")return!1;var d=c&&c==="left"?"scrollLeft":"scrollTop",e=!1;return b[d]>0?!0:(b[d]=1,e=b[d]>0,b[d]=0,e)},isOverAxis:function(a,b,c){return a>b&&a<b+c},isOver:function(b,c,d,e,f,g){return a.ui.isOverAxis(b,d,f)&&a.ui.isOverAxis(c,e,g)}})})(jQuery);;
/* Includes: jquery.ui.widget.js */
(function(a,b){if(a.cleanData){var c=a.cleanData;a.cleanData=function(b){for(var d=0,e;(e=b[d])!=null;d++)try{a(e).triggerHandler("remove")}catch(f){}c(b)}}else{var d=a.fn.remove;a.fn.remove=function(b,c){return this.each(function(){return c||(!b||a.filter(b,[this]).length)&&a("*",this).add([this]).each(function(){try{a(this).triggerHandler("remove")}catch(b){}}),d.call(a(this),b,c)})}}a.widget=function(b,c,d){var e=b.split(".")[0],f;b=b.split(".")[1],f=e+"-"+b,d||(d=c,c=a.Widget),a.expr[":"][f]=function(c){return!!a.data(c,b)},a[e]=a[e]||{},a[e][b]=function(a,b){arguments.length&&this._createWidget(a,b)};var g=new c;g.options=a.extend(!0,{},g.options),a[e][b].prototype=a.extend(!0,g,{namespace:e,widgetName:b,widgetEventPrefix:a[e][b].prototype.widgetEventPrefix||b,widgetBaseClass:f},d),a.widget.bridge(b,a[e][b])},a.widget.bridge=function(c,d){a.fn[c]=function(e){var f=typeof e=="string",g=Array.prototype.slice.call(arguments,1),h=this;return e=!f&&g.length?a.extend.apply(null,[!0,e].concat(g)):e,f&&e.charAt(0)==="_"?h:(f?this.each(function(){var d=a.data(this,c),f=d&&a.isFunction(d[e])?d[e].apply(d,g):d;if(f!==d&&f!==b)return h=f,!1}):this.each(function(){var b=a.data(this,c);b?b.option(e||{})._init():a.data(this,c,new d(e,this))}),h)}},a.Widget=function(a,b){arguments.length&&this._createWidget(a,b)},a.Widget.prototype={widgetName:"widget",widgetEventPrefix:"",options:{disabled:!1},_createWidget:function(b,c){a.data(c,this.widgetName,this),this.element=a(c),this.options=a.extend(!0,{},this.options,this._getCreateOptions(),b);var d=this;this.element.bind("remove."+this.widgetName,function(){d.destroy()}),this._create(),this._trigger("create"),this._init()},_getCreateOptions:function(){return a.metadata&&a.metadata.get(this.element[0])[this.widgetName]},_create:function(){},_init:function(){},destroy:function(){this.element.unbind("."+this.widgetName).removeData(this.widgetName),this.widget().unbind("."+this.widgetName).removeAttr("aria-disabled").removeClass(this.widgetBaseClass+"-disabled "+"ui-state-disabled")},widget:function(){return this.element},option:function(c,d){var e=c;if(arguments.length===0)return a.extend({},this.options);if(typeof c=="string"){if(d===b)return this.options[c];e={},e[c]=d}return this._setOptions(e),this},_setOptions:function(b){var c=this;return a.each(b,function(a,b){c._setOption(a,b)}),this},_setOption:function(a,b){return this.options[a]=b,a==="disabled"&&this.widget()[b?"addClass":"removeClass"](this.widgetBaseClass+"-disabled"+" "+"ui-state-disabled").attr("aria-disabled",b),this},enable:function(){return this._setOption("disabled",!1)},disable:function(){return this._setOption("disabled",!0)},_trigger:function(b,c,d){var e,f,g=this.options[b];d=d||{},c=a.Event(c),c.type=(b===this.widgetEventPrefix?b:this.widgetEventPrefix+b).toLowerCase(),c.target=this.element[0],f=c.originalEvent;if(f)for(e in f)e in c||(c[e]=f[e]);return this.element.trigger(c,d),!(a.isFunction(g)&&g.call(this.element[0],c,d)===!1||c.isDefaultPrevented())}}})(jQuery);;
/*! Includes: jquery.ui.mouse.js */
(function(a,b){var c=!1;a(document).mouseup(function(a){c=!1}),a.widget("ui.mouse",{options:{cancel:":input,option",distance:1,delay:0},_mouseInit:function(){var b=this;this.element.bind("mousedown."+this.widgetName,function(a){return b._mouseDown(a)}).bind("click."+this.widgetName,function(c){if(!0===a.data(c.target,b.widgetName+".preventClickEvent"))return a.removeData(c.target,b.widgetName+".preventClickEvent"),c.stopImmediatePropagation(),!1}),this.started=!1},_mouseDestroy:function(){this.element.unbind("."+this.widgetName),this._mouseMoveDelegate&&a(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate)},_mouseDown:function(b){if(c)return;this._mouseStarted&&this._mouseUp(b),this._mouseDownEvent=b;var d=this,e=b.which==1,f=typeof this.options.cancel=="string"&&b.target.nodeName?a(b.target).closest(this.options.cancel).length:!1;if(!e||f||!this._mouseCapture(b))return!0;this.mouseDelayMet=!this.options.delay,this.mouseDelayMet||(this._mouseDelayTimer=setTimeout(function(){d.mouseDelayMet=!0},this.options.delay));if(this._mouseDistanceMet(b)&&this._mouseDelayMet(b)){this._mouseStarted=this._mouseStart(b)!==!1;if(!this._mouseStarted)return b.preventDefault(),!0}return!0===a.data(b.target,this.widgetName+".preventClickEvent")&&a.removeData(b.target,this.widgetName+".preventClickEvent"),this._mouseMoveDelegate=function(a){return d._mouseMove(a)},this._mouseUpDelegate=function(a){return d._mouseUp(a)},a(document).bind("mousemove."+this.widgetName,this._mouseMoveDelegate).bind("mouseup."+this.widgetName,this._mouseUpDelegate),b.preventDefault(),c=!0,!0},_mouseMove:function(b){return!a.browser.msie||document.documentMode>=9||!!b.button?this._mouseStarted?(this._mouseDrag(b),b.preventDefault()):(this._mouseDistanceMet(b)&&this._mouseDelayMet(b)&&(this._mouseStarted=this._mouseStart(this._mouseDownEvent,b)!==!1,this._mouseStarted?this._mouseDrag(b):this._mouseUp(b)),!this._mouseStarted):this._mouseUp(b)},_mouseUp:function(b){return a(document).unbind("mousemove."+this.widgetName,this._mouseMoveDelegate).unbind("mouseup."+this.widgetName,this._mouseUpDelegate),this._mouseStarted&&(this._mouseStarted=!1,b.target==this._mouseDownEvent.target&&a.data(b.target,this.widgetName+".preventClickEvent",!0),this._mouseStop(b)),!1},_mouseDistanceMet:function(a){return Math.max(Math.abs(this._mouseDownEvent.pageX-a.pageX),Math.abs(this._mouseDownEvent.pageY-a.pageY))>=this.options.distance},_mouseDelayMet:function(a){return this.mouseDelayMet},_mouseStart:function(a){},_mouseDrag:function(a){},_mouseStop:function(a){},_mouseCapture:function(a){return!0}})})(jQuery);;
/*! Includes: jquery.ui.position.js */
(function(a,b){a.ui=a.ui||{};var c=/left|center|right/,d=/top|center|bottom/,e="center",f={},g=a.fn.position,h=a.fn.offset;a.fn.position=function(b){if(!b||!b.of)return g.apply(this,arguments);b=a.extend({},b);var h=a(b.of),i=h[0],j=(b.collision||"flip").split(" "),k=b.offset?b.offset.split(" "):[0,0],l,m,n;return i.nodeType===9?(l=h.width(),m=h.height(),n={top:0,left:0}):i.setTimeout?(l=h.width(),m=h.height(),n={top:h.scrollTop(),left:h.scrollLeft()}):i.preventDefault?(b.at="left top",l=m=0,n={top:b.of.pageY,left:b.of.pageX}):(l=h.outerWidth(),m=h.outerHeight(),n=h.offset()),a.each(["my","at"],function(){var a=(b[this]||"").split(" ");a.length===1&&(a=c.test(a[0])?a.concat([e]):d.test(a[0])?[e].concat(a):[e,e]),a[0]=c.test(a[0])?a[0]:e,a[1]=d.test(a[1])?a[1]:e,b[this]=a}),j.length===1&&(j[1]=j[0]),k[0]=parseInt(k[0],10)||0,k.length===1&&(k[1]=k[0]),k[1]=parseInt(k[1],10)||0,b.at[0]==="right"?n.left+=l:b.at[0]===e&&(n.left+=l/2),b.at[1]==="bottom"?n.top+=m:b.at[1]===e&&(n.top+=m/2),n.left+=k[0],n.top+=k[1],this.each(function(){var c=a(this),d=c.outerWidth(),g=c.outerHeight(),h=parseInt(a.curCSS(this,"marginLeft",!0))||0,i=parseInt(a.curCSS(this,"marginTop",!0))||0,o=d+h+(parseInt(a.curCSS(this,"marginRight",!0))||0),p=g+i+(parseInt(a.curCSS(this,"marginBottom",!0))||0),q=a.extend({},n),r;b.my[0]==="right"?q.left-=d:b.my[0]===e&&(q.left-=d/2),b.my[1]==="bottom"?q.top-=g:b.my[1]===e&&(q.top-=g/2),f.fractions||(q.left=Math.round(q.left),q.top=Math.round(q.top)),r={left:q.left-h,top:q.top-i},a.each(["left","top"],function(c,e){a.ui.position[j[c]]&&a.ui.position[j[c]][e](q,{targetWidth:l,targetHeight:m,elemWidth:d,elemHeight:g,collisionPosition:r,collisionWidth:o,collisionHeight:p,offset:k,my:b.my,at:b.at})}),a.fn.bgiframe&&c.bgiframe(),c.offset(a.extend(q,{using:b.using}))})},a.ui.position={fit:{left:function(b,c){var d=a(window),e=c.collisionPosition.left+c.collisionWidth-d.width()-d.scrollLeft();b.left=e>0?b.left-e:Math.max(b.left-c.collisionPosition.left,b.left)},top:function(b,c){var d=a(window),e=c.collisionPosition.top+c.collisionHeight-d.height()-d.scrollTop();b.top=e>0?b.top-e:Math.max(b.top-c.collisionPosition.top,b.top)}},flip:{left:function(b,c){if(c.at[0]===e)return;var d=a(window),f=c.collisionPosition.left+c.collisionWidth-d.width()-d.scrollLeft(),g=c.my[0]==="left"?-c.elemWidth:c.my[0]==="right"?c.elemWidth:0,h=c.at[0]==="left"?c.targetWidth:-c.targetWidth,i=-2*c.offset[0];b.left+=c.collisionPosition.left<0?g+h+i:f>0?g+h+i:0},top:function(b,c){if(c.at[1]===e)return;var d=a(window),f=c.collisionPosition.top+c.collisionHeight-d.height()-d.scrollTop(),g=c.my[1]==="top"?-c.elemHeight:c.my[1]==="bottom"?c.elemHeight:0,h=c.at[1]==="top"?c.targetHeight:-c.targetHeight,i=-2*c.offset[1];b.top+=c.collisionPosition.top<0?g+h+i:f>0?g+h+i:0}}},a.offset.setOffset||(a.offset.setOffset=function(b,c){/static/.test(a.curCSS(b,"position"))&&(b.style.position="relative");var d=a(b),e=d.offset(),f=parseInt(a.curCSS(b,"top",!0),10)||0,g=parseInt(a.curCSS(b,"left",!0),10)||0,h={top:c.top-e.top+f,left:c.left-e.left+g};"using"in c?c.using.call(b,h):d.css(h)},a.fn.offset=function(b){var c=this[0];return!c||!c.ownerDocument?null:b?a.isFunction(b)?this.each(function(c){a(this).offset(b.call(this,c,a(this).offset()))}):this.each(function(){a.offset.setOffset(this,b)}):h.call(this)}),a.curCSS||(a.curCSS=a.css),function(){var b=document.getElementsByTagName("body")[0],c=document.createElement("div"),d,e,g,h,i;d=document.createElement(b?"div":"body"),g={visibility:"hidden",width:0,height:0,border:0,margin:0,background:"none"},b&&a.extend(g,{position:"absolute",left:"-1000px",top:"-1000px"});for(var j in g)d.style[j]=g[j];d.appendChild(c),e=b||document.documentElement,e.insertBefore(d,e.firstChild),c.style.cssText="position: absolute; left: 10.7432222px; top: 10.432325px; height: 30px; width: 201px;",h=a(c).offset(function(a,b){return b}).offset(),d.innerHTML="",e.removeChild(d),i=h.top+h.left+(b?2e3:0),f.fractions=i>21&&i<22}()})(jQuery);;
/*! Includes: jquery.ui.draggable.js */
(function(a,b){a.widget("ui.draggable",a.ui.mouse,{widgetEventPrefix:"drag",options:{addClasses:!0,appendTo:"parent",axis:!1,connectToSortable:!1,containment:!1,cursor:"auto",cursorAt:!1,grid:!1,handle:!1,helper:"original",iframeFix:!1,opacity:!1,refreshPositions:!1,revert:!1,revertDuration:500,scope:"default",scroll:!0,scrollSensitivity:20,scrollSpeed:20,snap:!1,snapMode:"both",snapTolerance:20,stack:!1,zIndex:!1},_create:function(){this.options.helper=="original"&&!/^(?:r|a|f)/.test(this.element.css("position"))&&(this.element[0].style.position="relative"),this.options.addClasses&&this.element.addClass("ui-draggable"),this.options.disabled&&this.element.addClass("ui-draggable-disabled"),this._mouseInit()},destroy:function(){if(!this.element.data("draggable"))return;return this.element.removeData("draggable").unbind(".draggable").removeClass("ui-draggable ui-draggable-dragging ui-draggable-disabled"),this._mouseDestroy(),this},_mouseCapture:function(b){var c=this.options;return this.helper||c.disabled||a(b.target).is(".ui-resizable-handle")?!1:(this.handle=this._getHandle(b),this.handle?(c.iframeFix&&a(c.iframeFix===!0?"iframe":c.iframeFix).each(function(){a('<div class="ui-draggable-iframeFix" style="background: #fff;"></div>').css({width:this.offsetWidth+"px",height:this.offsetHeight+"px",position:"absolute",opacity:"0.001",zIndex:1e3}).css(a(this).offset()).appendTo("body")}),!0):!1)},_mouseStart:function(b){var c=this.options;return this.helper=this._createHelper(b),this.helper.addClass("ui-draggable-dragging"),this._cacheHelperProportions(),a.ui.ddmanager&&(a.ui.ddmanager.current=this),this._cacheMargins(),this.cssPosition=this.helper.css("position"),this.scrollParent=this.helper.scrollParent(),this.offset=this.positionAbs=this.element.offset(),this.offset={top:this.offset.top-this.margins.top,left:this.offset.left-this.margins.left},a.extend(this.offset,{click:{left:b.pageX-this.offset.left,top:b.pageY-this.offset.top},parent:this._getParentOffset(),relative:this._getRelativeOffset()}),this.originalPosition=this.position=this._generatePosition(b),this.originalPageX=b.pageX,this.originalPageY=b.pageY,c.cursorAt&&this._adjustOffsetFromHelper(c.cursorAt),c.containment&&this._setContainment(),this._trigger("start",b)===!1?(this._clear(),!1):(this._cacheHelperProportions(),a.ui.ddmanager&&!c.dropBehaviour&&a.ui.ddmanager.prepareOffsets(this,b),this._mouseDrag(b,!0),a.ui.ddmanager&&a.ui.ddmanager.dragStart(this,b),!0)},_mouseDrag:function(b,c){this.position=this._generatePosition(b),this.positionAbs=this._convertPositionTo("absolute");if(!c){var d=this._uiHash();if(this._trigger("drag",b,d)===!1)return this._mouseUp({}),!1;this.position=d.position}if(!this.options.axis||this.options.axis!="y")this.helper[0].style.left=this.position.left+"px";if(!this.options.axis||this.options.axis!="x")this.helper[0].style.top=this.position.top+"px";return a.ui.ddmanager&&a.ui.ddmanager.drag(this,b),!1},_mouseStop:function(b){var c=!1;a.ui.ddmanager&&!this.options.dropBehaviour&&(c=a.ui.ddmanager.drop(this,b)),this.dropped&&(c=this.dropped,this.dropped=!1);var d=this.element[0],e=!1;while(d&&(d=d.parentNode))d==document&&(e=!0);if(!e&&this.options.helper==="original")return!1;if(this.options.revert=="invalid"&&!c||this.options.revert=="valid"&&c||this.options.revert===!0||a.isFunction(this.options.revert)&&this.options.revert.call(this.element,c)){var f=this;a(this.helper).animate(this.originalPosition,parseInt(this.options.revertDuration,10),function(){f._trigger("stop",b)!==!1&&f._clear()})}else this._trigger("stop",b)!==!1&&this._clear();return!1},_mouseUp:function(b){return this.options.iframeFix===!0&&a("div.ui-draggable-iframeFix").each(function(){this.parentNode.removeChild(this)}),a.ui.ddmanager&&a.ui.ddmanager.dragStop(this,b),a.ui.mouse.prototype._mouseUp.call(this,b)},cancel:function(){return this.helper.is(".ui-draggable-dragging")?this._mouseUp({}):this._clear(),this},_getHandle:function(b){var c=!this.options.handle||!a(this.options.handle,this.element).length?!0:!1;return a(this.options.handle,this.element).find("*").andSelf().each(function(){this==b.target&&(c=!0)}),c},_createHelper:function(b){var c=this.options,d=a.isFunction(c.helper)?a(c.helper.apply(this.element[0],[b])):c.helper=="clone"?this.element.clone().removeAttr("id"):this.element;return d.parents("body").length||d.appendTo(c.appendTo=="parent"?this.element[0].parentNode:c.appendTo),d[0]!=this.element[0]&&!/(fixed|absolute)/.test(d.css("position"))&&d.css("position","absolute"),d},_adjustOffsetFromHelper:function(b){typeof b=="string"&&(b=b.split(" ")),a.isArray(b)&&(b={left:+b[0],top:+b[1]||0}),"left"in b&&(this.offset.click.left=b.left+this.margins.left),"right"in b&&(this.offset.click.left=this.helperProportions.width-b.right+this.margins.left),"top"in b&&(this.offset.click.top=b.top+this.margins.top),"bottom"in b&&(this.offset.click.top=this.helperProportions.height-b.bottom+this.margins.top)},_getParentOffset:function(){this.offsetParent=this.helper.offsetParent();var b=this.offsetParent.offset();this.cssPosition=="absolute"&&this.scrollParent[0]!=document&&a.ui.contains(this.scrollParent[0],this.offsetParent[0])&&(b.left+=this.scrollParent.scrollLeft(),b.top+=this.scrollParent.scrollTop());if(this.offsetParent[0]==document.body||this.offsetParent[0].tagName&&this.offsetParent[0].tagName.toLowerCase()=="html"&&a.browser.msie)b={top:0,left:0};return{top:b.top+(parseInt(this.offsetParent.css("borderTopWidth"),10)||0),left:b.left+(parseInt(this.offsetParent.css("borderLeftWidth"),10)||0)}},_getRelativeOffset:function(){if(this.cssPosition=="relative"){var a=this.element.position();return{top:a.top-(parseInt(this.helper.css("top"),10)||0)+this.scrollParent.scrollTop(),left:a.left-(parseInt(this.helper.css("left"),10)||0)+this.scrollParent.scrollLeft()}}return{top:0,left:0}},_cacheMargins:function(){this.margins={left:parseInt(this.element.css("marginLeft"),10)||0,top:parseInt(this.element.css("marginTop"),10)||0,right:parseInt(this.element.css("marginRight"),10)||0,bottom:parseInt(this.element.css("marginBottom"),10)||0}},_cacheHelperProportions:function(){this.helperProportions={width:this.helper.outerWidth(),height:this.helper.outerHeight()}},_setContainment:function(){var b=this.options;b.containment=="parent"&&(b.containment=this.helper[0].parentNode);if(b.containment=="document"||b.containment=="window")this.containment=[b.containment=="document"?0:a(window).scrollLeft()-this.offset.relative.left-this.offset.parent.left,b.containment=="document"?0:a(window).scrollTop()-this.offset.relative.top-this.offset.parent.top,(b.containment=="document"?0:a(window).scrollLeft())+a(b.containment=="document"?document:window).width()-this.helperProportions.width-this.margins.left,(b.containment=="document"?0:a(window).scrollTop())+(a(b.containment=="document"?document:window).height()||document.body.parentNode.scrollHeight)-this.helperProportions.height-this.margins.top];if(!/^(document|window|parent)$/.test(b.containment)&&b.containment.constructor!=Array){var c=a(b.containment),d=c[0];if(!d)return;var e=c.offset(),f=a(d).css("overflow")!="hidden";this.containment=[(parseInt(a(d).css("borderLeftWidth"),10)||0)+(parseInt(a(d).css("paddingLeft"),10)||0),(parseInt(a(d).css("borderTopWidth"),10)||0)+(parseInt(a(d).css("paddingTop"),10)||0),(f?Math.max(d.scrollWidth,d.offsetWidth):d.offsetWidth)-(parseInt(a(d).css("borderLeftWidth"),10)||0)-(parseInt(a(d).css("paddingRight"),10)||0)-this.helperProportions.width-this.margins.left-this.margins.right,(f?Math.max(d.scrollHeight,d.offsetHeight):d.offsetHeight)-(parseInt(a(d).css("borderTopWidth"),10)||0)-(parseInt(a(d).css("paddingBottom"),10)||0)-this.helperProportions.height-this.margins.top-this.margins.bottom],this.relative_container=c}else b.containment.constructor==Array&&(this.containment=b.containment)},_convertPositionTo:function(b,c){c||(c=this.position);var d=b=="absolute"?1:-1,e=this.options,f=this.cssPosition=="absolute"&&(this.scrollParent[0]==document||!a.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,g=/(html|body)/i.test(f[0].tagName);return{top:c.top+this.offset.relative.top*d+this.offset.parent.top*d-(a.browser.safari&&a.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollTop():g?0:f.scrollTop())*d),left:c.left+this.offset.relative.left*d+this.offset.parent.left*d-(a.browser.safari&&a.browser.version<526&&this.cssPosition=="fixed"?0:(this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():g?0:f.scrollLeft())*d)}},_generatePosition:function(b){var c=this.options,d=this.cssPosition=="absolute"&&(this.scrollParent[0]==document||!a.ui.contains(this.scrollParent[0],this.offsetParent[0]))?this.offsetParent:this.scrollParent,e=/(html|body)/i.test(d[0].tagName),f=b.pageX,g=b.pageY;if(this.originalPosition){var h;if(this.containment){if(this.relative_container){var i=this.relative_container.offset();h=[this.containment[0]+i.left,this.containment[1]+i.top,this.containment[2]+i.left,this.containment[3]+i.top]}else h=this.containment;b.pageX-this.offset.click.left<h[0]&&(f=h[0]+this.offset.click.left),b.pageY-this.offset.click.top<h[1]&&(g=h[1]+this.offset.click.top),b.pageX-this.offset.click.left>h[2]&&(f=h[2]+this.offset.click.left),b.pageY-this.offset.click.top>h[3]&&(g=h[3]+this.offset.click.top)}if(c.grid){var j=c.grid[1]?this.originalPageY+Math.round((g-this.originalPageY)/c.grid[1])*c.grid[1]:this.originalPageY;g=h?j-this.offset.click.top<h[1]||j-this.offset.click.top>h[3]?j-this.offset.click.top<h[1]?j+c.grid[1]:j-c.grid[1]:j:j;var k=c.grid[0]?this.originalPageX+Math.round((f-this.originalPageX)/c.grid[0])*c.grid[0]:this.originalPageX;f=h?k-this.offset.click.left<h[0]||k-this.offset.click.left>h[2]?k-this.offset.click.left<h[0]?k+c.grid[0]:k-c.grid[0]:k:k}}return{top:g-this.offset.click.top-this.offset.relative.top-this.offset.parent.top+(a.browser.safari&&a.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollTop():e?0:d.scrollTop()),left:f-this.offset.click.left-this.offset.relative.left-this.offset.parent.left+(a.browser.safari&&a.browser.version<526&&this.cssPosition=="fixed"?0:this.cssPosition=="fixed"?-this.scrollParent.scrollLeft():e?0:d.scrollLeft())}},_clear:function(){this.helper.removeClass("ui-draggable-dragging"),this.helper[0]!=this.element[0]&&!this.cancelHelperRemoval&&this.helper.remove(),this.helper=null,this.cancelHelperRemoval=!1},_trigger:function(b,c,d){return d=d||this._uiHash(),a.ui.plugin.call(this,b,[c,d]),b=="drag"&&(this.positionAbs=this._convertPositionTo("absolute")),a.Widget.prototype._trigger.call(this,b,c,d)},plugins:{},_uiHash:function(a){return{helper:this.helper,position:this.position,originalPosition:this.originalPosition,offset:this.positionAbs}}}),a.extend(a.ui.draggable,{version:"1.8.23"}),a.ui.plugin.add("draggable","connectToSortable",{start:function(b,c){var d=a(this).data("draggable"),e=d.options,f=a.extend({},c,{item:d.element});d.sortables=[],a(e.connectToSortable).each(function(){var c=a.data(this,"sortable");c&&!c.options.disabled&&(d.sortables.push({instance:c,shouldRevert:c.options.revert}),c.refreshPositions(),c._trigger("activate",b,f))})},stop:function(b,c){var d=a(this).data("draggable"),e=a.extend({},c,{item:d.element});a.each(d.sortables,function(){this.instance.isOver?(this.instance.isOver=0,d.cancelHelperRemoval=!0,this.instance.cancelHelperRemoval=!1,this.shouldRevert&&(this.instance.options.revert=!0),this.instance._mouseStop(b),this.instance.options.helper=this.instance.options._helper,d.options.helper=="original"&&this.instance.currentItem.css({top:"auto",left:"auto"})):(this.instance.cancelHelperRemoval=!1,this.instance._trigger("deactivate",b,e))})},drag:function(b,c){var d=a(this).data("draggable"),e=this,f=function(b){var c=this.offset.click.top,d=this.offset.click.left,e=this.positionAbs.top,f=this.positionAbs.left,g=b.height,h=b.width,i=b.top,j=b.left;return a.ui.isOver(e+c,f+d,i,j,g,h)};a.each(d.sortables,function(f){this.instance.positionAbs=d.positionAbs,this.instance.helperProportions=d.helperProportions,this.instance.offset.click=d.offset.click,this.instance._intersectsWith(this.instance.containerCache)?(this.instance.isOver||(this.instance.isOver=1,this.instance.currentItem=a(e).clone().removeAttr("id").appendTo(this.instance.element).data("sortable-item",!0),this.instance.options._helper=this.instance.options.helper,this.instance.options.helper=function(){return c.helper[0]},b.target=this.instance.currentItem[0],this.instance._mouseCapture(b,!0),this.instance._mouseStart(b,!0,!0),this.instance.offset.click.top=d.offset.click.top,this.instance.offset.click.left=d.offset.click.left,this.instance.offset.parent.left-=d.offset.parent.left-this.instance.offset.parent.left,this.instance.offset.parent.top-=d.offset.parent.top-this.instance.offset.parent.top,d._trigger("toSortable",b),d.dropped=this.instance.element,d.currentItem=d.element,this.instance.fromOutside=d),this.instance.currentItem&&this.instance._mouseDrag(b)):this.instance.isOver&&(this.instance.isOver=0,this.instance.cancelHelperRemoval=!0,this.instance.options.revert=!1,this.instance._trigger("out",b,this.instance._uiHash(this.instance)),this.instance._mouseStop(b,!0),this.instance.options.helper=this.instance.options._helper,this.instance.currentItem.remove(),this.instance.placeholder&&this.instance.placeholder.remove(),d._trigger("fromSortable",b),d.dropped=!1)})}}),a.ui.plugin.add("draggable","cursor",{start:function(b,c){var d=a("body"),e=a(this).data("draggable").options;d.css("cursor")&&(e._cursor=d.css("cursor")),d.css("cursor",e.cursor)},stop:function(b,c){var d=a(this).data("draggable").options;d._cursor&&a("body").css("cursor",d._cursor)}}),a.ui.plugin.add("draggable","opacity",{start:function(b,c){var d=a(c.helper),e=a(this).data("draggable").options;d.css("opacity")&&(e._opacity=d.css("opacity")),d.css("opacity",e.opacity)},stop:function(b,c){var d=a(this).data("draggable").options;d._opacity&&a(c.helper).css("opacity",d._opacity)}}),a.ui.plugin.add("draggable","scroll",{start:function(b,c){var d=a(this).data("draggable");d.scrollParent[0]!=document&&d.scrollParent[0].tagName!="HTML"&&(d.overflowOffset=d.scrollParent.offset())},drag:function(b,c){var d=a(this).data("draggable"),e=d.options,f=!1;if(d.scrollParent[0]!=document&&d.scrollParent[0].tagName!="HTML"){if(!e.axis||e.axis!="x")d.overflowOffset.top+d.scrollParent[0].offsetHeight-b.pageY<e.scrollSensitivity?d.scrollParent[0].scrollTop=f=d.scrollParent[0].scrollTop+e.scrollSpeed:b.pageY-d.overflowOffset.top<e.scrollSensitivity&&(d.scrollParent[0].scrollTop=f=d.scrollParent[0].scrollTop-e.scrollSpeed);if(!e.axis||e.axis!="y")d.overflowOffset.left+d.scrollParent[0].offsetWidth-b.pageX<e.scrollSensitivity?d.scrollParent[0].scrollLeft=f=d.scrollParent[0].scrollLeft+e.scrollSpeed:b.pageX-d.overflowOffset.left<e.scrollSensitivity&&(d.scrollParent[0].scrollLeft=f=d.scrollParent[0].scrollLeft-e.scrollSpeed)}else{if(!e.axis||e.axis!="x")b.pageY-a(document).scrollTop()<e.scrollSensitivity?f=a(document).scrollTop(a(document).scrollTop()-e.scrollSpeed):a(window).height()-(b.pageY-a(document).scrollTop())<e.scrollSensitivity&&(f=a(document).scrollTop(a(document).scrollTop()+e.scrollSpeed));if(!e.axis||e.axis!="y")b.pageX-a(document).scrollLeft()<e.scrollSensitivity?f=a(document).scrollLeft(a(document).scrollLeft()-e.scrollSpeed):a(window).width()-(b.pageX-a(document).scrollLeft())<e.scrollSensitivity&&(f=a(document).scrollLeft(a(document).scrollLeft()+e.scrollSpeed))}f!==!1&&a.ui.ddmanager&&!e.dropBehaviour&&a.ui.ddmanager.prepareOffsets(d,b)}}),a.ui.plugin.add("draggable","snap",{start:function(b,c){var d=a(this).data("draggable"),e=d.options;d.snapElements=[],a(e.snap.constructor!=String?e.snap.items||":data(draggable)":e.snap).each(function(){var b=a(this),c=b.offset();this!=d.element[0]&&d.snapElements.push({item:this,width:b.outerWidth(),height:b.outerHeight(),top:c.top,left:c.left})})},drag:function(b,c){var d=a(this).data("draggable"),e=d.options,f=e.snapTolerance,g=c.offset.left,h=g+d.helperProportions.width,i=c.offset.top,j=i+d.helperProportions.height;for(var k=d.snapElements.length-1;k>=0;k--){var l=d.snapElements[k].left,m=l+d.snapElements[k].width,n=d.snapElements[k].top,o=n+d.snapElements[k].height;if(!(l-f<g&&g<m+f&&n-f<i&&i<o+f||l-f<g&&g<m+f&&n-f<j&&j<o+f||l-f<h&&h<m+f&&n-f<i&&i<o+f||l-f<h&&h<m+f&&n-f<j&&j<o+f)){d.snapElements[k].snapping&&d.options.snap.release&&d.options.snap.release.call(d.element,b,a.extend(d._uiHash(),{snapItem:d.snapElements[k].item})),d.snapElements[k].snapping=!1;continue}if(e.snapMode!="inner"){var p=Math.abs(n-j)<=f,q=Math.abs(o-i)<=f,r=Math.abs(l-h)<=f,s=Math.abs(m-g)<=f;p&&(c.position.top=d._convertPositionTo("relative",{top:n-d.helperProportions.height,left:0}).top-d.margins.top),q&&(c.position.top=d._convertPositionTo("relative",{top:o,left:0}).top-d.margins.top),r&&(c.position.left=d._convertPositionTo("relative",{top:0,left:l-d.helperProportions.width}).left-d.margins.left),s&&(c.position.left=d._convertPositionTo("relative",{top:0,left:m}).left-d.margins.left)}var t=p||q||r||s;if(e.snapMode!="outer"){var p=Math.abs(n-i)<=f,q=Math.abs(o-j)<=f,r=Math.abs(l-g)<=f,s=Math.abs(m-h)<=f;p&&(c.position.top=d._convertPositionTo("relative",{top:n,left:0}).top-d.margins.top),q&&(c.position.top=d._convertPositionTo("relative",{top:o-d.helperProportions.height,left:0}).top-d.margins.top),r&&(c.position.left=d._convertPositionTo("relative",{top:0,left:l}).left-d.margins.left),s&&(c.position.left=d._convertPositionTo("relative",{top:0,left:m-d.helperProportions.width}).left-d.margins.left)}!d.snapElements[k].snapping&&(p||q||r||s||t)&&d.options.snap.snap&&d.options.snap.snap.call(d.element,b,a.extend(d._uiHash(),{snapItem:d.snapElements[k].item})),d.snapElements[k].snapping=p||q||r||s||t}}}),a.ui.plugin.add("draggable","stack",{start:function(b,c){var d=a(this).data("draggable").options,e=a.makeArray(a(d.stack)).sort(function(b,c){return(parseInt(a(b).css("zIndex"),10)||0)-(parseInt(a(c).css("zIndex"),10)||0)});if(!e.length)return;var f=parseInt(e[0].style.zIndex)||0;a(e).each(function(a){this.style.zIndex=f+a}),this[0].style.zIndex=f+e.length}}),a.ui.plugin.add("draggable","zIndex",{start:function(b,c){var d=a(c.helper),e=a(this).data("draggable").options;d.css("zIndex")&&(e._zIndex=d.css("zIndex")),d.css("zIndex",e.zIndex)},stop:function(b,c){var d=a(this).data("draggable").options;d._zIndex&&a(c.helper).css("zIndex",d._zIndex)}})})(jQuery);;
/*! Includes: jquery.ui.resizable.js */
(function(a,b){a.widget("ui.resizable",a.ui.mouse,{widgetEventPrefix:"resize",options:{alsoResize:!1,animate:!1,animateDuration:"slow",animateEasing:"swing",aspectRatio:!1,autoHide:!1,containment:!1,ghost:!1,grid:!1,handles:"e,s,se",helper:!1,maxHeight:null,maxWidth:null,minHeight:10,minWidth:10,zIndex:1e3},_create:function(){var b=this,c=this.options;this.element.addClass("ui-resizable"),a.extend(this,{_aspectRatio:!!c.aspectRatio,aspectRatio:c.aspectRatio,originalElement:this.element,_proportionallyResizeElements:[],_helper:c.helper||c.ghost||c.animate?c.helper||"ui-resizable-helper":null}),this.element[0].nodeName.match(/canvas|textarea|input|select|button|img/i)&&(this.element.wrap(a('<div class="ui-wrapper" style="overflow: hidden;"></div>').css({position:this.element.css("position"),width:this.element.outerWidth(),height:this.element.outerHeight(),top:this.element.css("top"),left:this.element.css("left")})),this.element=this.element.parent().data("resizable",this.element.data("resizable")),this.elementIsWrapper=!0,this.element.css({marginLeft:this.originalElement.css("marginLeft"),marginTop:this.originalElement.css("marginTop"),marginRight:this.originalElement.css("marginRight"),marginBottom:this.originalElement.css("marginBottom")}),this.originalElement.css({marginLeft:0,marginTop:0,marginRight:0,marginBottom:0}),this.originalResizeStyle=this.originalElement.css("resize"),this.originalElement.css("resize","none"),this._proportionallyResizeElements.push(this.originalElement.css({position:"static",zoom:1,display:"block"})),this.originalElement.css({margin:this.originalElement.css("margin")}),this._proportionallyResize()),this.handles=c.handles||(a(".ui-resizable-handle",this.element).length?{n:".ui-resizable-n",e:".ui-resizable-e",s:".ui-resizable-s",w:".ui-resizable-w",se:".ui-resizable-se",sw:".ui-resizable-sw",ne:".ui-resizable-ne",nw:".ui-resizable-nw"}:"e,s,se");if(this.handles.constructor==String){this.handles=="all"&&(this.handles="n,e,s,w,se,sw,ne,nw");var d=this.handles.split(",");this.handles={};for(var e=0;e<d.length;e++){var f=a.trim(d[e]),g="ui-resizable-"+f,h=a('<div class="ui-resizable-handle '+g+'"></div>');h.css({zIndex:c.zIndex}),"se"==f&&h.addClass("ui-icon ui-icon-gripsmall-diagonal-se"),this.handles[f]=".ui-resizable-"+f,this.element.append(h)}}this._renderAxis=function(b){b=b||this.element;for(var c in this.handles){this.handles[c].constructor==String&&(this.handles[c]=a(this.handles[c],this.element).show());if(this.elementIsWrapper&&this.originalElement[0].nodeName.match(/textarea|input|select|button/i)){var d=a(this.handles[c],this.element),e=0;e=/sw|ne|nw|se|n|s/.test(c)?d.outerHeight():d.outerWidth();var f=["padding",/ne|nw|n/.test(c)?"Top":/se|sw|s/.test(c)?"Bottom":/^e$/.test(c)?"Right":"Left"].join("");b.css(f,e),this._proportionallyResize()}if(!a(this.handles[c]).length)continue}},this._renderAxis(this.element),this._handles=a(".ui-resizable-handle",this.element).disableSelection(),this._handles.mouseover(function(){if(!b.resizing){if(this.className)var a=this.className.match(/ui-resizable-(se|sw|ne|nw|n|e|s|w)/i);b.axis=a&&a[1]?a[1]:"se"}}),c.autoHide&&(this._handles.hide(),a(this.element).addClass("ui-resizable-autohide").hover(function(){if(c.disabled)return;a(this).removeClass("ui-resizable-autohide"),b._handles.show()},function(){if(c.disabled)return;b.resizing||(a(this).addClass("ui-resizable-autohide"),b._handles.hide())})),this._mouseInit()},destroy:function(){this._mouseDestroy();var b=function(b){a(b).removeClass("ui-resizable ui-resizable-disabled ui-resizable-resizing").removeData("resizable").unbind(".resizable").find(".ui-resizable-handle").remove()};if(this.elementIsWrapper){b(this.element);var c=this.element;c.after(this.originalElement.css({position:c.css("position"),width:c.outerWidth(),height:c.outerHeight(),top:c.css("top"),left:c.css("left")})).remove()}return this.originalElement.css("resize",this.originalResizeStyle),b(this.originalElement),this},_mouseCapture:function(b){var c=!1;for(var d in this.handles)a(this.handles[d])[0]==b.target&&(c=!0);return!this.options.disabled&&c},_mouseStart:function(b){var d=this.options,e=this.element.position(),f=this.element;this.resizing=!0,this.documentScroll={top:a(document).scrollTop(),left:a(document).scrollLeft()},(f.is(".ui-draggable")||/absolute/.test(f.css("position")))&&f.css({position:"absolute",top:e.top,left:e.left}),this._renderProxy();var g=c(this.helper.css("left")),h=c(this.helper.css("top"));d.containment&&(g+=a(d.containment).scrollLeft()||0,h+=a(d.containment).scrollTop()||0),this.offset=this.helper.offset(),this.position={left:g,top:h},this.size=this._helper?{width:f.outerWidth(),height:f.outerHeight()}:{width:f.width(),height:f.height()},this.originalSize=this._helper?{width:f.outerWidth(),height:f.outerHeight()}:{width:f.width(),height:f.height()},this.originalPosition={left:g,top:h},this.sizeDiff={width:f.outerWidth()-f.width(),height:f.outerHeight()-f.height()},this.originalMousePosition={left:b.pageX,top:b.pageY},this.aspectRatio=typeof d.aspectRatio=="number"?d.aspectRatio:this.originalSize.width/this.originalSize.height||1;var i=a(".ui-resizable-"+this.axis).css("cursor");return a("body").css("cursor",i=="auto"?this.axis+"-resize":i),f.addClass("ui-resizable-resizing"),this._propagate("start",b),!0},_mouseDrag:function(b){var c=this.helper,d=this.options,e={},f=this,g=this.originalMousePosition,h=this.axis,i=b.pageX-g.left||0,j=b.pageY-g.top||0,k=this._change[h];if(!k)return!1;var l=k.apply(this,[b,i,j]),m=a.browser.msie&&a.browser.version<7,n=this.sizeDiff;this._updateVirtualBoundaries(b.shiftKey);if(this._aspectRatio||b.shiftKey)l=this._updateRatio(l,b);return l=this._respectSize(l,b),this._propagate("resize",b),c.css({top:this.position.top+"px",left:this.position.left+"px",width:this.size.width+"px",height:this.size.height+"px"}),!this._helper&&this._proportionallyResizeElements.length&&this._proportionallyResize(),this._updateCache(l),this._trigger("resize",b,this.ui()),!1},_mouseStop:function(b){this.resizing=!1;var c=this.options,d=this;if(this._helper){var e=this._proportionallyResizeElements,f=e.length&&/textarea/i.test(e[0].nodeName),g=f&&a.ui.hasScroll(e[0],"left")?0:d.sizeDiff.height,h=f?0:d.sizeDiff.width,i={width:d.helper.width()-h,height:d.helper.height()-g},j=parseInt(d.element.css("left"),10)+(d.position.left-d.originalPosition.left)||null,k=parseInt(d.element.css("top"),10)+(d.position.top-d.originalPosition.top)||null;c.animate||this.element.css(a.extend(i,{top:k,left:j})),d.helper.height(d.size.height),d.helper.width(d.size.width),this._helper&&!c.animate&&this._proportionallyResize()}return a("body").css("cursor","auto"),this.element.removeClass("ui-resizable-resizing"),this._propagate("stop",b),this._helper&&this.helper.remove(),!1},_updateVirtualBoundaries:function(a){var b=this.options,c,e,f,g,h;h={minWidth:d(b.minWidth)?b.minWidth:0,maxWidth:d(b.maxWidth)?b.maxWidth:Infinity,minHeight:d(b.minHeight)?b.minHeight:0,maxHeight:d(b.maxHeight)?b.maxHeight:Infinity};if(this._aspectRatio||a)c=h.minHeight*this.aspectRatio,f=h.minWidth/this.aspectRatio,e=h.maxHeight*this.aspectRatio,g=h.maxWidth/this.aspectRatio,c>h.minWidth&&(h.minWidth=c),f>h.minHeight&&(h.minHeight=f),e<h.maxWidth&&(h.maxWidth=e),g<h.maxHeight&&(h.maxHeight=g);this._vBoundaries=h},_updateCache:function(a){var b=this.options;this.offset=this.helper.offset(),d(a.left)&&(this.position.left=a.left),d(a.top)&&(this.position.top=a.top),d(a.height)&&(this.size.height=a.height),d(a.width)&&(this.size.width=a.width)},_updateRatio:function(a,b){var c=this.options,e=this.position,f=this.size,g=this.axis;return d(a.height)?a.width=a.height*this.aspectRatio:d(a.width)&&(a.height=a.width/this.aspectRatio),g=="sw"&&(a.left=e.left+(f.width-a.width),a.top=null),g=="nw"&&(a.top=e.top+(f.height-a.height),a.left=e.left+(f.width-a.width)),a},_respectSize:function(a,b){var c=this.helper,e=this._vBoundaries,f=this._aspectRatio||b.shiftKey,g=this.axis,h=d(a.width)&&e.maxWidth&&e.maxWidth<a.width,i=d(a.height)&&e.maxHeight&&e.maxHeight<a.height,j=d(a.width)&&e.minWidth&&e.minWidth>a.width,k=d(a.height)&&e.minHeight&&e.minHeight>a.height;j&&(a.width=e.minWidth),k&&(a.height=e.minHeight),h&&(a.width=e.maxWidth),i&&(a.height=e.maxHeight);var l=this.originalPosition.left+this.originalSize.width,m=this.position.top+this.size.height,n=/sw|nw|w/.test(g),o=/nw|ne|n/.test(g);j&&n&&(a.left=l-e.minWidth),h&&n&&(a.left=l-e.maxWidth),k&&o&&(a.top=m-e.minHeight),i&&o&&(a.top=m-e.maxHeight);var p=!a.width&&!a.height;return p&&!a.left&&a.top?a.top=null:p&&!a.top&&a.left&&(a.left=null),a},_proportionallyResize:function(){var b=this.options;if(!this._proportionallyResizeElements.length)return;var c=this.helper||this.element;for(var d=0;d<this._proportionallyResizeElements.length;d++){var e=this._proportionallyResizeElements[d];if(!this.borderDif){var f=[e.css("borderTopWidth"),e.css("borderRightWidth"),e.css("borderBottomWidth"),e.css("borderLeftWidth")],g=[e.css("paddingTop"),e.css("paddingRight"),e.css("paddingBottom"),e.css("paddingLeft")];this.borderDif=a.map(f,function(a,b){var c=parseInt(a,10)||0,d=parseInt(g[b],10)||0;return c+d})}if(!a.browser.msie||!a(c).is(":hidden")&&!a(c).parents(":hidden").length)e.css({height:c.height()-this.borderDif[0]-this.borderDif[2]||0,width:c.width()-this.borderDif[1]-this.borderDif[3]||0});else continue}},_renderProxy:function(){var b=this.element,c=this.options;this.elementOffset=b.offset();if(this._helper){this.helper=this.helper||a('<div style="overflow:hidden;"></div>');var d=a.browser.msie&&a.browser.version<7,e=d?1:0,f=d?2:-1;this.helper.addClass(this._helper).css({width:this.element.outerWidth()+f,height:this.element.outerHeight()+f,position:"absolute",left:this.elementOffset.left-e+"px",top:this.elementOffset.top-e+"px",zIndex:++c.zIndex}),this.helper.appendTo("body").disableSelection()}else this.helper=this.element},_change:{e:function(a,b,c){return{width:this.originalSize.width+b}},w:function(a,b,c){var d=this.options,e=this.originalSize,f=this.originalPosition;return{left:f.left+b,width:e.width-b}},n:function(a,b,c){var d=this.options,e=this.originalSize,f=this.originalPosition;return{top:f.top+c,height:e.height-c}},s:function(a,b,c){return{height:this.originalSize.height+c}},se:function(b,c,d){return a.extend(this._change.s.apply(this,arguments),this._change.e.apply(this,[b,c,d]))},sw:function(b,c,d){return a.extend(this._change.s.apply(this,arguments),this._change.w.apply(this,[b,c,d]))},ne:function(b,c,d){return a.extend(this._change.n.apply(this,arguments),this._change.e.apply(this,[b,c,d]))},nw:function(b,c,d){return a.extend(this._change.n.apply(this,arguments),this._change.w.apply(this,[b,c,d]))}},_propagate:function(b,c){a.ui.plugin.call(this,b,[c,this.ui()]),b!="resize"&&this._trigger(b,c,this.ui())},plugins:{},ui:function(){return{originalElement:this.originalElement,element:this.element,helper:this.helper,position:this.position,size:this.size,originalSize:this.originalSize,originalPosition:this.originalPosition}}}),a.extend(a.ui.resizable,{version:"1.8.23"}),a.ui.plugin.add("resizable","alsoResize",{start:function(b,c){var d=a(this).data("resizable"),e=d.options,f=function(b){a(b).each(function(){var b=a(this);b.data("resizable-alsoresize",{width:parseInt(b.width(),10),height:parseInt(b.height(),10),left:parseInt(b.css("left"),10),top:parseInt(b.css("top"),10)})})};typeof e.alsoResize=="object"&&!e.alsoResize.parentNode?e.alsoResize.length?(e.alsoResize=e.alsoResize[0],f(e.alsoResize)):a.each(e.alsoResize,function(a){f(a)}):f(e.alsoResize)},resize:function(b,c){var d=a(this).data("resizable"),e=d.options,f=d.originalSize,g=d.originalPosition,h={height:d.size.height-f.height||0,width:d.size.width-f.width||0,top:d.position.top-g.top||0,left:d.position.left-g.left||0},i=function(b,d){a(b).each(function(){var b=a(this),e=a(this).data("resizable-alsoresize"),f={},g=d&&d.length?d:b.parents(c.originalElement[0]).length?["width","height"]:["width","height","top","left"];a.each(g,function(a,b){var c=(e[b]||0)+(h[b]||0);c&&c>=0&&(f[b]=c||null)}),b.css(f)})};typeof e.alsoResize=="object"&&!e.alsoResize.nodeType?a.each(e.alsoResize,function(a,b){i(a,b)}):i(e.alsoResize)},stop:function(b,c){a(this).removeData("resizable-alsoresize")}}),a.ui.plugin.add("resizable","animate",{stop:function(b,c){var d=a(this).data("resizable"),e=d.options,f=d._proportionallyResizeElements,g=f.length&&/textarea/i.test(f[0].nodeName),h=g&&a.ui.hasScroll(f[0],"left")?0:d.sizeDiff.height,i=g?0:d.sizeDiff.width,j={width:d.size.width-i,height:d.size.height-h},k=parseInt(d.element.css("left"),10)+(d.position.left-d.originalPosition.left)||null,l=parseInt(d.element.css("top"),10)+(d.position.top-d.originalPosition.top)||null;d.element.animate(a.extend(j,l&&k?{top:l,left:k}:{}),{duration:e.animateDuration,easing:e.animateEasing,step:function(){var c={width:parseInt(d.element.css("width"),10),height:parseInt(d.element.css("height"),10),top:parseInt(d.element.css("top"),10),left:parseInt(d.element.css("left"),10)};f&&f.length&&a(f[0]).css({width:c.width,height:c.height}),d._updateCache(c),d._propagate("resize",b)}})}}),a.ui.plugin.add("resizable","containment",{start:function(b,d){var e=a(this).data("resizable"),f=e.options,g=e.element,h=f.containment,i=h instanceof a?h.get(0):/parent/.test(h)?g.parent().get(0):h;if(!i)return;e.containerElement=a(i);if(/document/.test(h)||h==document)e.containerOffset={left:0,top:0},e.containerPosition={left:0,top:0},e.parentData={element:a(document),left:0,top:0,width:a(document).width(),height:a(document).height()||document.body.parentNode.scrollHeight};else{var j=a(i),k=[];a(["Top","Right","Left","Bottom"]).each(function(a,b){k[a]=c(j.css("padding"+b))}),e.containerOffset=j.offset(),e.containerPosition=j.position(),e.containerSize={height:j.innerHeight()-k[3],width:j.innerWidth()-k[1]};var l=e.containerOffset,m=e.containerSize.height,n=e.containerSize.width,o=a.ui.hasScroll(i,"left")?i.scrollWidth:n,p=a.ui.hasScroll(i)?i.scrollHeight:m;e.parentData={element:i,left:l.left,top:l.top,width:o,height:p}}},resize:function(b,c){var d=a(this).data("resizable"),e=d.options,f=d.containerSize,g=d.containerOffset,h=d.size,i=d.position,j=d._aspectRatio||b.shiftKey,k={top:0,left:0},l=d.containerElement;l[0]!=document&&/static/.test(l.css("position"))&&(k=g),i.left<(d._helper?g.left:0)&&(d.size.width=d.size.width+(d._helper?d.position.left-g.left:d.position.left-k.left),j&&(d.size.height=d.size.width/d.aspectRatio),d.position.left=e.helper?g.left:0),i.top<(d._helper?g.top:0)&&(d.size.height=d.size.height+(d._helper?d.position.top-g.top:d.position.top),j&&(d.size.width=d.size.height*d.aspectRatio),d.position.top=d._helper?g.top:0),d.offset.left=d.parentData.left+d.position.left,d.offset.top=d.parentData.top+d.position.top;var m=Math.abs((d._helper?d.offset.left-k.left:d.offset.left-k.left)+d.sizeDiff.width),n=Math.abs((d._helper?d.offset.top-k.top:d.offset.top-g.top)+d.sizeDiff.height),o=d.containerElement.get(0)==d.element.parent().get(0),p=/relative|absolute/.test(d.containerElement.css("position"));o&&p&&(m-=d.parentData.left),m+d.size.width>=d.parentData.width&&(d.size.width=d.parentData.width-m,j&&(d.size.height=d.size.width/d.aspectRatio)),n+d.size.height>=d.parentData.height&&(d.size.height=d.parentData.height-n,j&&(d.size.width=d.size.height*d.aspectRatio))},stop:function(b,c){var d=a(this).data("resizable"),e=d.options,f=d.position,g=d.containerOffset,h=d.containerPosition,i=d.containerElement,j=a(d.helper),k=j.offset(),l=j.outerWidth()-d.sizeDiff.width,m=j.outerHeight()-d.sizeDiff.height;d._helper&&!e.animate&&/relative/.test(i.css("position"))&&a(this).css({left:k.left-h.left-g.left,width:l,height:m}),d._helper&&!e.animate&&/static/.test(i.css("position"))&&a(this).css({left:k.left-h.left-g.left,width:l,height:m})}}),a.ui.plugin.add("resizable","ghost",{start:function(b,c){var d=a(this).data("resizable"),e=d.options,f=d.size;d.ghost=d.originalElement.clone(),d.ghost.css({opacity:.25,display:"block",position:"relative",height:f.height,width:f.width,margin:0,left:0,top:0}).addClass("ui-resizable-ghost").addClass(typeof e.ghost=="string"?e.ghost:""),d.ghost.appendTo(d.helper)},resize:function(b,c){var d=a(this).data("resizable"),e=d.options;d.ghost&&d.ghost.css({position:"relative",height:d.size.height,width:d.size.width})},stop:function(b,c){var d=a(this).data("resizable"),e=d.options;d.ghost&&d.helper&&d.helper.get(0).removeChild(d.ghost.get(0))}}),a.ui.plugin.add("resizable","grid",{resize:function(b,c){var d=a(this).data("resizable"),e=d.options,f=d.size,g=d.originalSize,h=d.originalPosition,i=d.axis,j=e._aspectRatio||b.shiftKey;e.grid=typeof e.grid=="number"?[e.grid,e.grid]:e.grid;var k=Math.round((f.width-g.width)/(e.grid[0]||1))*(e.grid[0]||1),l=Math.round((f.height-g.height)/(e.grid[1]||1))*(e.grid[1]||1);/^(se|s|e)$/.test(i)?(d.size.width=g.width+k,d.size.height=g.height+l):/^(ne)$/.test(i)?(d.size.width=g.width+k,d.size.height=g.height+l,d.position.top=h.top-l):/^(sw)$/.test(i)?(d.size.width=g.width+k,d.size.height=g.height+l,d.position.left=h.left-k):(d.size.width=g.width+k,d.size.height=g.height+l,d.position.top=h.top-l,d.position.left=h.left-k)}});var c=function(a){return parseInt(a,10)||0},d=function(a){return!isNaN(parseInt(a,10))}})(jQuery);;
/*! Includes: jquery.ui.dialog.js*/
(function(a,b){var c="ui-dialog ui-widget ui-widget-content ui-corner-all ",d={buttons:!0,height:!0,maxHeight:!0,maxWidth:!0,minHeight:!0,minWidth:!0,width:!0},e={maxHeight:!0,maxWidth:!0,minHeight:!0,minWidth:!0};a.widget("ui.dialog",{options:{autoOpen:!0,buttons:{},closeOnEscape:!0,closeText:"close",dialogClass:"",draggable:!0,hide:null,height:"auto",maxHeight:!1,maxWidth:!1,minHeight:150,minWidth:150,modal:!1,position:{my:"center",at:"center",collision:"fit",using:function(b){var c=a(this).css(b).offset().top;c<0&&a(this).css("top",b.top-c)}},resizable:!0,show:null,stack:!0,title:"",width:300,zIndex:1e3},_create:function(){this.originalTitle=this.element.attr("title"),typeof this.originalTitle!="string"&&(this.originalTitle=""),this.options.title=this.options.title||this.originalTitle;var b=this,d=b.options,e=d.title||"&#160;",f=a.ui.dialog.getTitleId(b.element),g=(b.uiDialog=a("<div></div>")).appendTo(document.body).hide().addClass(c+d.dialogClass).css({zIndex:d.zIndex}).attr("tabIndex",-1).css("outline",0).keydown(function(c){d.closeOnEscape&&!c.isDefaultPrevented()&&c.keyCode&&c.keyCode===a.ui.keyCode.ESCAPE&&(b.close(c),c.preventDefault())}).attr({role:"dialog","aria-labelledby":f}).mousedown(function(a){b.moveToTop(!1,a)}),h=b.element.show().removeAttr("title").addClass("ui-dialog-content ui-widget-content").appendTo(g),i=(b.uiDialogTitlebar=a("<div></div>")).addClass("ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix").prependTo(g),j=a('<a href="#"></a>').addClass("ui-dialog-titlebar-close ui-corner-all").attr("role","button").hover(function(){j.addClass("ui-state-hover")},function(){j.removeClass("ui-state-hover")}).focus(function(){j.addClass("ui-state-focus")}).blur(function(){j.removeClass("ui-state-focus")}).click(function(a){return b.close(a),!1}).appendTo(i),k=(b.uiDialogTitlebarCloseText=a("<span></span>")).addClass("ui-icon ui-textclose").html("X").appendTo(j),l=a("<span></span>").addClass("ui-dialog-title").attr("id",f).html(e).prependTo(i);a.isFunction(d.beforeclose)&&!a.isFunction(d.beforeClose)&&(d.beforeClose=d.beforeclose),i.find("*").add(i).disableSelection(),d.draggable&&a.fn.draggable&&b._makeDraggable(),d.resizable&&a.fn.resizable&&b._makeResizable(),b._createButtons(d.buttons),b._isOpen=!1,a.fn.bgiframe&&g.bgiframe()},_init:function(){this.options.autoOpen&&this.open()},destroy:function(){var a=this;return a.overlay&&a.overlay.destroy(),a.uiDialog.hide(),a.element.unbind(".dialog").removeData("dialog").removeClass("ui-dialog-content ui-widget-content").hide().appendTo("body"),a.uiDialog.remove(),a.originalTitle&&a.element.attr("title",a.originalTitle),a},widget:function(){return this.uiDialog},close:function(b){var c=this,d,e;if(!1===c._trigger("beforeClose",b))return;return c.overlay&&c.overlay.destroy(),c.uiDialog.unbind("keypress.ui-dialog"),c._isOpen=!1,c.options.hide?c.uiDialog.hide(c.options.hide,function(){c._trigger("close",b)}):(c.uiDialog.hide(),c._trigger("close",b)),a.ui.dialog.overlay.resize(),c.options.modal&&(d=0,a(".ui-dialog").each(function(){this!==c.uiDialog[0]&&(e=a(this).css("z-index"),isNaN(e)||(d=Math.max(d,e)))}),a.ui.dialog.maxZ=d),c},isOpen:function(){return this._isOpen},moveToTop:function(b,c){var d=this,e=d.options,f;return e.modal&&!b||!e.stack&&!e.modal?d._trigger("focus",c):(e.zIndex>a.ui.dialog.maxZ&&(a.ui.dialog.maxZ=e.zIndex),d.overlay&&(a.ui.dialog.maxZ+=1,d.overlay.$el.css("z-index",a.ui.dialog.overlay.maxZ=a.ui.dialog.maxZ)),f={scrollTop:d.element.scrollTop(),scrollLeft:d.element.scrollLeft()},a.ui.dialog.maxZ+=1,d.uiDialog.css("z-index",a.ui.dialog.maxZ),d.element.attr(f),d._trigger("focus",c),d)},open:function(){if(this._isOpen)return;var b=this,c=b.options,d=b.uiDialog;return b.overlay=c.modal?new a.ui.dialog.overlay(b):null,b._size(),b._position(c.position),d.show(c.show),b.moveToTop(!0),c.modal&&d.bind("keydown.ui-dialog",function(b){if(b.keyCode!==a.ui.keyCode.TAB)return;var c=a(":tabbable",this),d=c.filter(":first"),e=c.filter(":last");if(b.target===e[0]&&!b.shiftKey)return d.focus(1),!1;if(b.target===d[0]&&b.shiftKey)return e.focus(1),!1}),a(b.element.find(":tabbable").get().concat(d.find(".ui-dialog-buttonpane :tabbable").get().concat(d.get()))).eq(0).focus(),b._isOpen=!0,b._trigger("open"),b},_createButtons:function(b){var c=this,d=!1,e=a("<div></div>").addClass("ui-dialog-buttonpane ui-widget-content ui-helper-clearfix"),f=a("<div></div>").addClass("ui-dialog-buttonset").appendTo(e);c.uiDialog.find(".ui-dialog-buttonpane").remove(),typeof b=="object"&&b!==null&&a.each(b,function(){return!(d=!0)}),d&&(a.each(b,function(b,d){d=a.isFunction(d)?{click:d,text:b}:d;var e=a('<button type="button"></button>').click(function(){d.click.apply(c.element[0],arguments)}).appendTo(f);a.each(d,function(a,b){if(a==="click")return;a in e?e[a](b):e.attr(a,b)}),a.fn.button&&e.button()}),e.appendTo(c.uiDialog))},_makeDraggable:function(){function f(a){return{position:a.position,offset:a.offset}}var b=this,c=b.options,d=a(document),e;b.uiDialog.draggable({cancel:".ui-dialog-content, .ui-dialog-titlebar-close",handle:".ui-dialog-titlebar",containment:"document",start:function(d,g){e=c.height==="auto"?"auto":a(this).height(),a(this).height(a(this).height()).addClass("ui-dialog-dragging"),b._trigger("dragStart",d,f(g))},drag:function(a,c){b._trigger("drag",a,f(c))},stop:function(g,h){c.position=[h.position.left-d.scrollLeft(),h.position.top-d.scrollTop()],a(this).removeClass("ui-dialog-dragging").height(e),b._trigger("dragStop",g,f(h)),a.ui.dialog.overlay.resize()}})},_makeResizable:function(c){function h(a){return{originalPosition:a.originalPosition,originalSize:a.originalSize,position:a.position,size:a.size}}c=c===b?this.options.resizable:c;var d=this,e=d.options,f=d.uiDialog.css("position"),g=typeof c=="string"?c:"n,e,s,w,se,sw,ne,nw";d.uiDialog.resizable({cancel:".ui-dialog-content",containment:"document",alsoResize:d.element,maxWidth:e.maxWidth,maxHeight:e.maxHeight,minWidth:e.minWidth,minHeight:d._minHeight(),handles:g,start:function(b,c){a(this).addClass("ui-dialog-resizing"),d._trigger("resizeStart",b,h(c))},resize:function(a,b){d._trigger("resize",a,h(b))},stop:function(b,c){a(this).removeClass("ui-dialog-resizing"),e.height=a(this).height(),e.width=a(this).width(),d._trigger("resizeStop",b,h(c)),a.ui.dialog.overlay.resize()}}).css("position",f).find(".ui-resizable-se").addClass("ui-icon ui-icon-grip-diagonal-se")},_minHeight:function(){var a=this.options;return a.height==="auto"?a.minHeight:Math.min(a.minHeight,a.height)},_position:function(b){var c=[],d=[0,0],e;if(b){if(typeof b=="string"||typeof b=="object"&&"0"in b)c=b.split?b.split(" "):[b[0],b[1]],c.length===1&&(c[1]=c[0]),a.each(["left","top"],function(a,b){+c[a]===c[a]&&(d[a]=c[a],c[a]=b)}),b={my:c.join(" "),at:c.join(" "),offset:d.join(" ")};b=a.extend({},a.ui.dialog.prototype.options.position,b)}else b=a.ui.dialog.prototype.options.position;e=this.uiDialog.is(":visible"),e||this.uiDialog.show(),this.uiDialog.css({top:0,left:0}).position(a.extend({of:window},b)),e||this.uiDialog.hide()},_setOptions:function(b){var c=this,f={},g=!1;a.each(b,function(a,b){c._setOption(a,b),a in d&&(g=!0),a in e&&(f[a]=b)}),g&&this._size(),this.uiDialog.is(":data(resizable)")&&this.uiDialog.resizable("option",f)},_setOption:function(b,d){var e=this,f=e.uiDialog;switch(b){case"beforeclose":b="beforeClose";break;case"buttons":e._createButtons(d);break;case"closeText":e.uiDialogTitlebarCloseText.text(""+d);break;case"dialogClass":f.removeClass(e.options.dialogClass).addClass(c+d);break;case"disabled":d?f.addClass("ui-dialog-disabled"):f.removeClass("ui-dialog-disabled");break;case"draggable":var g=f.is(":data(draggable)");g&&!d&&f.draggable("destroy"),!g&&d&&e._makeDraggable();break;case"position":e._position(d);break;case"resizable":var h=f.is(":data(resizable)");h&&!d&&f.resizable("destroy"),h&&typeof d=="string"&&f.resizable("option","handles",d),!h&&d!==!1&&e._makeResizable(d);break;case"title":a(".ui-dialog-title",e.uiDialogTitlebar).html(""+(d||"&#160;"))}a.Widget.prototype._setOption.apply(e,arguments)},_size:function(){var b=this.options,c,d,e=this.uiDialog.is(":visible");this.element.show().css({width:"auto",minHeight:0,height:0}),b.minWidth>b.width&&(b.width=b.minWidth),c=this.uiDialog.css({height:"auto",width:b.width}).height(),d=Math.max(0,b.minHeight-c);if(b.height==="auto")if(a.support.minHeight)this.element.css({minHeight:d,height:"auto"});else{this.uiDialog.show();var f=this.element.css("height","auto").height();e||this.uiDialog.hide(),this.element.height(Math.max(f,d))}else this.element.height(Math.max(b.height-c,0));this.uiDialog.is(":data(resizable)")&&this.uiDialog.resizable("option","minHeight",this._minHeight())}}),a.extend(a.ui.dialog,{version:"1.8.23",uuid:0,maxZ:0,getTitleId:function(a){var b=a.attr("id");return b||(this.uuid+=1,b=this.uuid),"ui-dialog-title-"+b},overlay:function(b){this.$el=a.ui.dialog.overlay.create(b)}}),a.extend(a.ui.dialog.overlay,{instances:[],oldInstances:[],maxZ:0,events:a.map("focus,mousedown,mouseup,keydown,keypress,click".split(","),function(a){return a+".dialog-overlay"}).join(" "),create:function(b){this.instances.length===0&&(setTimeout(function(){a.ui.dialog.overlay.instances.length&&a(document).bind(a.ui.dialog.overlay.events,function(b){if(a(b.target).zIndex()<a.ui.dialog.overlay.maxZ)return!1})},1),a(document).bind("keydown.dialog-overlay",function(c){b.options.closeOnEscape&&!c.isDefaultPrevented()&&c.keyCode&&c.keyCode===a.ui.keyCode.ESCAPE&&(b.close(c),c.preventDefault())}),a(window).bind("resize.dialog-overlay",a.ui.dialog.overlay.resize));var c=(this.oldInstances.pop()||a("<div></div>").addClass("ui-widget-overlay")).appendTo(document.body).css({width:this.width(),height:this.height()});return a.fn.bgiframe&&c.bgiframe(),this.instances.push(c),c},destroy:function(b){var c=a.inArray(b,this.instances);c!=-1&&this.oldInstances.push(this.instances.splice(c,1)[0]),this.instances.length===0&&a([document,window]).unbind(".dialog-overlay"),b.remove();var d=0;a.each(this.instances,function(){d=Math.max(d,this.css("z-index"))}),this.maxZ=d},height:function(){var b,c;return a.browser.msie&&a.browser.version<7?(b=Math.max(document.documentElement.scrollHeight,document.body.scrollHeight),c=Math.max(document.documentElement.offsetHeight,document.body.offsetHeight),b<c?a(window).height()+"px":b+"px"):a(document).height()+"px"},width:function(){var b,c;return a.browser.msie?(b=Math.max(document.documentElement.scrollWidth,document.body.scrollWidth),c=Math.max(document.documentElement.offsetWidth,document.body.offsetWidth),b<c?a(window).width()+"px":b+"px"):a(document).width()+"px"},resize:function(){var b=a([]);a.each(a.ui.dialog.overlay.instances,function(){b=b.add(this)}),b.css({width:0,height:0}).css({width:a.ui.dialog.overlay.width(),height:a.ui.dialog.overlay.height()})}}),a.extend(a.ui.dialog.overlay.prototype,{destroy:function(){a.ui.dialog.overlay.destroy(this.$el)}})})(jQuery);;
/*! Includes: jquery.ui.progressbar.js */
(function(a,b){a.widget("ui.progressbar",{options:{value:0,max:100},min:0,_create:function(){this.element.addClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").attr({role:"progressbar","aria-valuemin":this.min,"aria-valuemax":this.options.max,"aria-valuenow":this._value()}),this.valueDiv=a("<div class='ui-progressbar-value ui-widget-header ui-corner-left'></div>").appendTo(this.element),this.oldValue=this._value(),this._refreshValue()},destroy:function(){this.element.removeClass("ui-progressbar ui-widget ui-widget-content ui-corner-all").removeAttr("role").removeAttr("aria-valuemin").removeAttr("aria-valuemax").removeAttr("aria-valuenow"),this.valueDiv.remove(),a.Widget.prototype.destroy.apply(this,arguments)},value:function(a){return a===b?this._value():(this._setOption("value",a),this)},_setOption:function(b,c){b==="value"&&(this.options.value=c,this._refreshValue(),this._value()===this.options.max&&this._trigger("complete")),a.Widget.prototype._setOption.apply(this,arguments)},_value:function(){var a=this.options.value;return typeof a!="number"&&(a=0),Math.min(this.options.max,Math.max(this.min,a))},_percentage:function(){return 100*this._value()/this.options.max},_refreshValue:function(){var a=this.value(),b=this._percentage();this.oldValue!==a&&(this.oldValue=a,this._trigger("change")),this.valueDiv.toggle(a>this.min).toggleClass("ui-corner-right",a===this.options.max).width(b.toFixed(0)+"%"),this.element.attr("aria-valuenow",a)}}),a.extend(a.ui.progressbar,{version:"1.8.23"})})(jQuery);;
</script>


<script type="text/javascript">
/* json2.js 
 * See www.JSON.org/js.html*/
if(!this.JSON){JSON=function(){function f(n){return n<10?'0'+n:n;}
Date.prototype.toJSON=function(){return this.getUTCFullYear()+'-'+
f(this.getUTCMonth()+1)+'-'+
f(this.getUTCDate())+'T'+
f(this.getUTCHours())+':'+
f(this.getUTCMinutes())+':'+
f(this.getUTCSeconds())+'Z';};var m={'\b':'\\b','\t':'\\t','\n':'\\n','\f':'\\f','\r':'\\r','"':'\\"','\\':'\\\\'};function stringify(value,whitelist){var a,i,k,l,r=/["\\\x00-\x1f\x7f-\x9f]/g,v;switch(typeof value){case'string':return r.test(value)?'"'+value.replace(r,function(a){var c=m[a];if(c){return c;}
c=a.charCodeAt();return'\\u00'+Math.floor(c/16).toString(16)+
(c%16).toString(16);})+'"':'"'+value+'"';case'number':return isFinite(value)?String(value):'null';case'boolean':case'null':return String(value);case'object':if(!value){return'null';}
if(typeof value.toJSON==='function'){return stringify(value.toJSON());}
a=[];if(typeof value.length==='number'&&!(value.propertyIsEnumerable('length'))){l=value.length;for(i=0;i<l;i+=1){a.push(stringify(value[i],whitelist)||'null');}
return'['+a.join(',')+']';}
if(whitelist){l=whitelist.length;for(i=0;i<l;i+=1){k=whitelist[i];if(typeof k==='string'){v=stringify(value[k],whitelist);if(v){a.push(stringify(k)+':'+v);}}}}else{for(k in value){if(typeof k==='string'){v=stringify(value[k],whitelist);if(v){a.push(stringify(k)+':'+v);}}}}
return'{'+a.join(',')+'}';}}
return{stringify:stringify,parse:function(text,filter){var j;function walk(k,v){var i,n;if(v&&typeof v==='object'){for(i in v){if(Object.prototype.hasOwnProperty.apply(v,[i])){n=walk(i,v[i]);if(n!==undefined){v[i]=n;}}}}
return filter(k,v);}
if(/^[\],:{}\s]*$/.test(text.replace(/\\./g,'@').replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,']').replace(/(?:^|:|,)(?:\s*\[)+/g,''))){j=eval('('+text+')');return typeof filter==='function'?walk('',j):j;}
throw new SyntaxError('parseJSON');}};}();}
</script>


<script type="text/javascript">
// Knockout JavaScript library v2.1.0
// (c) Steven Sanderson - knockoutjs.com/
// License: MIT (www.opensource.org/licenses/mit-license.php)
(function(window,document,navigator,undefined){
function m(w){throw w;}var n=void 0,p=!0,s=null,t=!1;function A(w){return function(){return w}};function E(w){function B(b,c,d){d&&c!==a.k.r(b)&&a.k.S(b,c);c!==a.k.r(b)&&a.a.va(b,"change")}var a="undefined"!==typeof w?w:{};a.b=function(b,c){for(var d=b.split("."),f=a,g=0;g<d.length-1;g++)f=f[d[g]];f[d[d.length-1]]=c};a.B=function(a,c,d){a[c]=d};a.version="2.1.0";a.b("version",a.version);a.a=new function(){function b(b,c){if("input"!==a.a.o(b)||!b.type||"click"!=c.toLowerCase())return t;var e=b.type;return"checkbox"==e||"radio"==e}var c=/^(\s|\u00A0)+|(\s|\u00A0)+$/g,d={},f={};d[/Firefox\/2/i.test(navigator.userAgent)?
"KeyboardEvent":"UIEvents"]=["keyup","keydown","keypress"];d.MouseEvents="click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave".split(" ");for(var g in d){var e=d[g];if(e.length)for(var h=0,j=e.length;h<j;h++)f[e[h]]=g}var k={propertychange:p},i=function(){for(var a=3,b=document.createElement("div"),c=b.getElementsByTagName("i");b.innerHTML="<\!--[if gt IE "+ ++a+"]><i></i><![endif]--\>",c[0];);return 4<a?a:n}();return{Ca:["authenticity_token",/^__RequestVerificationToken(_.*)?$/],
v:function(a,b){for(var c=0,e=a.length;c<e;c++)b(a[c])},j:function(a,b){if("function"==typeof Array.prototype.indexOf)return Array.prototype.indexOf.call(a,b);for(var c=0,e=a.length;c<e;c++)if(a[c]===b)return c;return-1},ab:function(a,b,c){for(var e=0,f=a.length;e<f;e++)if(b.call(c,a[e]))return a[e];return s},ba:function(b,c){var e=a.a.j(b,c);0<=e&&b.splice(e,1)},za:function(b){for(var b=b||[],c=[],e=0,f=b.length;e<f;e++)0>a.a.j(c,b[e])&&c.push(b[e]);return c},T:function(a,b){for(var a=a||[],c=[],
e=0,f=a.length;e<f;e++)c.push(b(a[e]));return c},aa:function(a,b){for(var a=a||[],c=[],e=0,f=a.length;e<f;e++)b(a[e])&&c.push(a[e]);return c},N:function(a,b){if(b instanceof Array)a.push.apply(a,b);else for(var c=0,e=b.length;c<e;c++)a.push(b[c]);return a},extend:function(a,b){if(b)for(var c in b)b.hasOwnProperty(c)&&(a[c]=b[c]);return a},ga:function(b){for(;b.firstChild;)a.removeNode(b.firstChild)},Ab:function(b){for(var b=a.a.L(b),c=document.createElement("div"),e=0,f=b.length;e<f;e++)a.F(b[e]),
c.appendChild(b[e]);return c},X:function(b,c){a.a.ga(b);if(c)for(var e=0,f=c.length;e<f;e++)b.appendChild(c[e])},Na:function(b,c){var e=b.nodeType?[b]:b;if(0<e.length){for(var f=e[0],d=f.parentNode,g=0,h=c.length;g<h;g++)d.insertBefore(c[g],f);g=0;for(h=e.length;g<h;g++)a.removeNode(e[g])}},Pa:function(a,b){0<=navigator.userAgent.indexOf("MSIE 6")?a.setAttribute("selected",b):a.selected=b},w:function(a){return(a||"").replace(c,"")},Ib:function(b,c){for(var e=[],f=(b||"").split(c),g=0,d=f.length;g<
d;g++){var h=a.a.w(f[g]);""!==h&&e.push(h)}return e},Hb:function(a,b){a=a||"";return b.length>a.length?t:a.substring(0,b.length)===b},eb:function(a,b){for(var c="return ("+a+")",e=0;e<b;e++)c="with(sc["+e+"]) { "+c+" } ";return new Function("sc",c)},kb:function(a,b){if(b.compareDocumentPosition)return 16==(b.compareDocumentPosition(a)&16);for(;a!=s;){if(a==b)return p;a=a.parentNode}return t},fa:function(b){return a.a.kb(b,b.ownerDocument)},o:function(a){return a&&a.tagName&&a.tagName.toLowerCase()},
n:function(a,c,e){var f=i&&k[c];if(!f&&"undefined"!=typeof jQuery){if(b(a,c))var g=e,e=function(a,b){var c=this.checked;b&&(this.checked=b.fb!==p);g.call(this,a);this.checked=c};jQuery(a).bind(c,e)}else!f&&"function"==typeof a.addEventListener?a.addEventListener(c,e,t):"undefined"!=typeof a.attachEvent?a.attachEvent("on"+c,function(b){e.call(a,b)}):m(Error("Browser doesn't support addEventListener or attachEvent"))},va:function(a,c){(!a||!a.nodeType)&&m(Error("element must be a DOM node when calling triggerEvent"));
if("undefined"!=typeof jQuery){var e=[];b(a,c)&&e.push({fb:a.checked});jQuery(a).trigger(c,e)}else"function"==typeof document.createEvent?"function"==typeof a.dispatchEvent?(e=document.createEvent(f[c]||"HTMLEvents"),e.initEvent(c,p,p,window,0,0,0,0,0,t,t,t,t,0,a),a.dispatchEvent(e)):m(Error("The supplied element doesn't support dispatchEvent")):"undefined"!=typeof a.fireEvent?(b(a,c)&&(a.checked=a.checked!==p),a.fireEvent("on"+c)):m(Error("Browser doesn't support triggering events"))},d:function(b){return a.la(b)?
b():b},Ua:function(b,c,e){var f=(b.className||"").split(/\s+/),g=0<=a.a.j(f,c);if(e&&!g)b.className+=(f[0]?" ":"")+c;else if(g&&!e){e="";for(g=0;g<f.length;g++)f[g]!=c&&(e+=f[g]+" ");b.className=a.a.w(e)}},Qa:function(b,c){var e=a.a.d(c);if(e===s||e===n)e="";"innerText"in b?b.innerText=e:b.textContent=e;9<=i&&(b.style.display=b.style.display)},lb:function(a){if(9<=i){var b=a.style.width;a.style.width=0;a.style.width=b}},Eb:function(b,e){for(var b=a.a.d(b),e=a.a.d(e),c=[],f=b;f<=e;f++)c.push(f);return c},
L:function(a){for(var b=[],e=0,c=a.length;e<c;e++)b.push(a[e]);return b},tb:6===i,ub:7===i,ja:i,Da:function(b,e){for(var c=a.a.L(b.getElementsByTagName("input")).concat(a.a.L(b.getElementsByTagName("textarea"))),f="string"==typeof e?function(a){return a.name===e}:function(a){return e.test(a.name)},g=[],d=c.length-1;0<=d;d--)f(c[d])&&g.push(c[d]);return g},Bb:function(b){return"string"==typeof b&&(b=a.a.w(b))?window.JSON&&window.JSON.parse?window.JSON.parse(b):(new Function("return "+b))():s},sa:function(b,
e,c){("undefined"==typeof JSON||"undefined"==typeof JSON.stringify)&&m(Error("Cannot find JSON.stringify(). Some browsers (e.g., IE < 8) don't support it natively, but you can overcome this by adding a script reference to json2.js, downloadable from www.json.org/json2.js"));return JSON.stringify(a.a.d(b),e,c)},Cb:function(b,e,c){var c=c||{},f=c.params||{},g=c.includeFields||this.Ca,d=b;if("object"==typeof b&&"form"===a.a.o(b))for(var d=b.action,h=g.length-1;0<=h;h--)for(var k=a.a.Da(b,g[h]),
j=k.length-1;0<=j;j--)f[k[j].name]=k[j].value;var e=a.a.d(e),i=document.createElement("form");i.style.display="none";i.action=d;i.method="post";for(var z in e)b=document.createElement("input"),b.name=z,b.value=a.a.sa(a.a.d(e[z])),i.appendChild(b);for(z in f)b=document.createElement("input"),b.name=z,b.value=f[z],i.appendChild(b);document.body.appendChild(i);c.submitter?c.submitter(i):i.submit();setTimeout(function(){i.parentNode.removeChild(i)},0)}}};a.b("utils",a.a);a.b("utils.arrayForEach",a.a.v);
a.b("utils.arrayFirst",a.a.ab);a.b("utils.arrayFilter",a.a.aa);a.b("utils.arrayGetDistinctValues",a.a.za);a.b("utils.arrayIndexOf",a.a.j);a.b("utils.arrayMap",a.a.T);a.b("utils.arrayPushAll",a.a.N);a.b("utils.arrayRemoveItem",a.a.ba);a.b("utils.extend",a.a.extend);a.b("utils.fieldsIncludedWithJsonPost",a.a.Ca);a.b("utils.getFormFields",a.a.Da);a.b("utils.postJson",a.a.Cb);a.b("utils.parseJson",a.a.Bb);a.b("utils.registerEventHandler",a.a.n);a.b("utils.stringifyJson",a.a.sa);a.b("utils.range",a.a.Eb);
a.b("utils.toggleDomNodeCssClass",a.a.Ua);a.b("utils.triggerEvent",a.a.va);a.b("utils.unwrapObservable",a.a.d);Function.prototype.bind||(Function.prototype.bind=function(a){var c=this,d=Array.prototype.slice.call(arguments),a=d.shift();return function(){return c.apply(a,d.concat(Array.prototype.slice.call(arguments)))}});a.a.f=new function(){var b=0,c="__ko__"+(new Date).getTime(),d={};return{get:function(b,c){var e=a.a.f.getAll(b,t);return e===n?n:e[c]},set:function(b,c,e){e===n&&a.a.f.getAll(b,
t)===n||(a.a.f.getAll(b,p)[c]=e)},getAll:function(a,g){var e=a[c];if(!(e&&"null"!==e)){if(!g)return;e=a[c]="ko"+b++;d[e]={}}return d[e]},clear:function(a){var b=a[c];b&&(delete d[b],a[c]=s)}}};a.b("utils.domData",a.a.f);a.b("utils.domData.clear",a.a.f.clear);a.a.G=new function(){function b(b,c){var f=a.a.f.get(b,d);f===n&&c&&(f=[],a.a.f.set(b,d,f));return f}function c(e){var f=b(e,t);if(f)for(var f=f.slice(0),d=0;d<f.length;d++)f[d](e);a.a.f.clear(e);"function"==typeof jQuery&&"function"==typeof jQuery.cleanData&&
jQuery.cleanData([e]);if(g[e.nodeType])for(f=e.firstChild;e=f;)f=e.nextSibling,8===e.nodeType&&c(e)}var d="__ko_domNodeDisposal__"+(new Date).getTime(),f={1:p,8:p,9:p},g={1:p,9:p};return{wa:function(a,c){"function"!=typeof c&&m(Error("Callback must be a function"));b(a,p).push(c)},Ma:function(c,f){var g=b(c,t);g&&(a.a.ba(g,f),0==g.length&&a.a.f.set(c,d,n))},F:function(b){if(f[b.nodeType]&&(c(b),g[b.nodeType])){var d=[];a.a.N(d,b.getElementsByTagName("*"));for(var b=0,j=d.length;b<j;b++)c(d[b])}},
removeNode:function(b){a.F(b);b.parentNode&&b.parentNode.removeChild(b)}}};a.F=a.a.G.F;a.removeNode=a.a.G.removeNode;a.b("cleanNode",a.F);a.b("removeNode",a.removeNode);a.b("utils.domNodeDisposal",a.a.G);a.b("utils.domNodeDisposal.addDisposeCallback",a.a.G.wa);a.b("utils.domNodeDisposal.removeDisposeCallback",a.a.G.Ma);(function(){a.a.pa=function(b){var c;if("undefined"!=typeof jQuery){if((c=jQuery.clean([b]))&&c[0]){for(b=c[0];b.parentNode&&11!==b.parentNode.nodeType;)b=b.parentNode;b.parentNode&&
b.parentNode.removeChild(b)}}else{var d=a.a.w(b).toLowerCase();c=document.createElement("div");d=d.match(/^<(thead|tbody|tfoot)/)&&[1,"<table>","</table>"]||!d.indexOf("<tr")&&[2,"<table><tbody>","</tbody></table>"]||(!d.indexOf("<td")||!d.indexOf("<th"))&&[3,"<table><tbody><tr>","</tr></tbody></table>"]||[0,"",""];b="ignored<div>"+d[1]+b+d[2]+"</div>";for("function"==typeof window.innerShiv?c.appendChild(window.innerShiv(b)):c.innerHTML=b;d[0]--;)c=c.lastChild;c=a.a.L(c.lastChild.childNodes)}return c};
a.a.Y=function(b,c){a.a.ga(b);if(c!==s&&c!==n)if("string"!=typeof c&&(c=c.toString()),"undefined"!=typeof jQuery)jQuery(b).html(c);else for(var d=a.a.pa(c),f=0;f<d.length;f++)b.appendChild(d[f])}})();a.b("utils.parseHtmlFragment",a.a.pa);a.b("utils.setHtml",a.a.Y);a.s=function(){function b(){return(4294967296*(1+Math.random())|0).toString(16).substring(1)}function c(b,g){if(b)if(8==b.nodeType){var e=a.s.Ja(b.nodeValue);e!=s&&g.push({jb:b,yb:e})}else if(1==b.nodeType)for(var e=0,d=b.childNodes,j=d.length;e<
j;e++)c(d[e],g)}var d={};return{na:function(a){"function"!=typeof a&&m(Error("You can only pass a function to ko.memoization.memoize()"));var c=b()+b();d[c]=a;return"<\!--[ko_memo:"+c+"]--\>"},Va:function(a,b){var c=d[a];c===n&&m(Error("Couldn't find any memo with ID "+a+". Perhaps it's already been unmemoized."));try{return c.apply(s,b||[]),p}finally{delete d[a]}},Wa:function(b,d){var e=[];c(b,e);for(var h=0,j=e.length;h<j;h++){var k=e[h].jb,i=[k];d&&a.a.N(i,d);a.s.Va(e[h].yb,i);k.nodeValue="";k.parentNode&&
k.parentNode.removeChild(k)}},Ja:function(a){return(a=a.match(/^\[ko_memo\:(.*?)\]$/))?a[1]:s}}}();a.b("memoization",a.s);a.b("memoization.memoize",a.s.na);a.b("memoization.unmemoize",a.s.Va);a.b("memoization.parseMemoText",a.s.Ja);a.b("memoization.unmemoizeDomNodeAndDescendants",a.s.Wa);a.Ba={throttle:function(b,c){b.throttleEvaluation=c;var d=s;return a.h({read:b,write:function(a){clearTimeout(d);d=setTimeout(function(){b(a)},c)}})},notify:function(b,c){b.equalityComparer="always"==c?A(t):a.m.fn.equalityComparer;
return b}};a.b("extenders",a.Ba);a.Sa=function(b,c,d){this.target=b;this.ca=c;this.ib=d;a.B(this,"dispose",this.A)};a.Sa.prototype.A=function(){this.sb=p;this.ib()};a.R=function(){this.u={};a.a.extend(this,a.R.fn);a.B(this,"subscribe",this.ta);a.B(this,"extend",this.extend);a.B(this,"getSubscriptionsCount",this.ob)};a.R.fn={ta:function(b,c,d){var d=d||"change",b=c?b.bind(c):b,f=new a.Sa(this,b,function(){a.a.ba(this.u[d],f)}.bind(this));this.u[d]||(this.u[d]=[]);this.u[d].push(f);return f},notifySubscribers:function(b,
c){c=c||"change";this.u[c]&&a.a.v(this.u[c].slice(0),function(a){a&&a.sb!==p&&a.ca(b)})},ob:function(){var a=0,c;for(c in this.u)this.u.hasOwnProperty(c)&&(a+=this.u[c].length);return a},extend:function(b){var c=this;if(b)for(var d in b){var f=a.Ba[d];"function"==typeof f&&(c=f(c,b[d]))}return c}};a.Ga=function(a){return"function"==typeof a.ta&&"function"==typeof a.notifySubscribers};a.b("subscribable",a.R);a.b("isSubscribable",a.Ga);a.U=function(){var b=[];return{bb:function(a){b.push({ca:a,Aa:[]})},
end:function(){b.pop()},La:function(c){a.Ga(c)||m(Error("Only subscribable things can act as dependencies"));if(0<b.length){var d=b[b.length-1];0<=a.a.j(d.Aa,c)||(d.Aa.push(c),d.ca(c))}}}}();var G={undefined:p,"boolean":p,number:p,string:p};a.m=function(b){function c(){if(0<arguments.length){if(!c.equalityComparer||!c.equalityComparer(d,arguments[0]))c.I(),d=arguments[0],c.H();return this}a.U.La(c);return d}var d=b;a.R.call(c);c.H=function(){c.notifySubscribers(d)};c.I=function(){c.notifySubscribers(d,
"beforeChange")};a.a.extend(c,a.m.fn);a.B(c,"valueHasMutated",c.H);a.B(c,"valueWillMutate",c.I);return c};a.m.fn={equalityComparer:function(a,c){return a===s||typeof a in G?a===c:t}};var x=a.m.Db="__ko_proto__";a.m.fn[x]=a.m;a.ia=function(b,c){return b===s||b===n||b[x]===n?t:b[x]===c?p:a.ia(b[x],c)};a.la=function(b){return a.ia(b,a.m)};a.Ha=function(b){return"function"==typeof b&&b[x]===a.m||"function"==typeof b&&b[x]===a.h&&b.pb?p:t};a.b("observable",a.m);a.b("isObservable",a.la);a.b("isWriteableObservable",
a.Ha);a.Q=function(b){0==arguments.length&&(b=[]);b!==s&&(b!==n&&!("length"in b))&&m(Error("The argument passed when initializing an observable array must be an array, or null, or undefined."));var c=a.m(b);a.a.extend(c,a.Q.fn);return c};a.Q.fn={remove:function(a){for(var c=this(),d=[],f="function"==typeof a?a:function(c){return c===a},g=0;g<c.length;g++){var e=c[g];f(e)&&(0===d.length&&this.I(),d.push(e),c.splice(g,1),g--)}d.length&&this.H();return d},removeAll:function(b){if(b===n){var c=this(),
d=c.slice(0);this.I();c.splice(0,c.length);this.H();return d}return!b?[]:this.remove(function(c){return 0<=a.a.j(b,c)})},destroy:function(a){var c=this(),d="function"==typeof a?a:function(c){return c===a};this.I();for(var f=c.length-1;0<=f;f--)d(c[f])&&(c[f]._destroy=p);this.H()},destroyAll:function(b){return b===n?this.destroy(A(p)):!b?[]:this.destroy(function(c){return 0<=a.a.j(b,c)})},indexOf:function(b){var c=this();return a.a.j(c,b)},replace:function(a,c){var d=this.indexOf(a);0<=d&&(this.I(),
this()[d]=c,this.H())}};a.a.v("pop push reverse shift sort splice unshift".split(" "),function(b){a.Q.fn[b]=function(){var a=this();this.I();a=a[b].apply(a,arguments);this.H();return a}});a.a.v(["slice"],function(b){a.Q.fn[b]=function(){var a=this();return a[b].apply(a,arguments)}});a.b("observableArray",a.Q);a.h=function(b,c,d){function f(){a.a.v(v,function(a){a.A()});v=[]}function g(){var a=h.throttleEvaluation;a&&0<=a?(clearTimeout(x),x=setTimeout(e,a)):e()}function e(){if(!l)if(i&&w())u();else{l=
p;try{var b=a.a.T(v,function(a){return a.target});a.U.bb(function(c){var e;0<=(e=a.a.j(b,c))?b[e]=n:v.push(c.ta(g))});for(var e=q.call(c),f=b.length-1;0<=f;f--)b[f]&&v.splice(f,1)[0].A();i=p;h.notifySubscribers(k,"beforeChange");k=e}finally{a.U.end()}h.notifySubscribers(k);l=t}}function h(){if(0<arguments.length)j.apply(h,arguments);else return i||e(),a.U.La(h),k}function j(){"function"===typeof o?o.apply(c,arguments):m(Error("Cannot write a value to a ko.computed unless you specify a 'write' option. If you wish to read the current value, don't pass any parameters."))}
var k,i=t,l=t,q=b;q&&"object"==typeof q?(d=q,q=d.read):(d=d||{},q||(q=d.read));"function"!=typeof q&&m(Error("Pass a function that returns the value of the ko.computed"));var o=d.write;c||(c=d.owner);var v=[],u=f,r="object"==typeof d.disposeWhenNodeIsRemoved?d.disposeWhenNodeIsRemoved:s,w=d.disposeWhen||A(t);if(r){u=function(){a.a.G.Ma(r,arguments.callee);f()};a.a.G.wa(r,u);var y=w,w=function(){return!a.a.fa(r)||y()}}var x=s;h.nb=function(){return v.length};h.pb="function"===typeof d.write;h.A=function(){u()};
a.R.call(h);a.a.extend(h,a.h.fn);d.deferEvaluation!==p&&e();a.B(h,"dispose",h.A);a.B(h,"getDependenciesCount",h.nb);return h};a.rb=function(b){return a.ia(b,a.h)};w=a.m.Db;a.h[w]=a.m;a.h.fn={};a.h.fn[w]=a.h;a.b("dependentObservable",a.h);a.b("computed",a.h);a.b("isComputed",a.rb);(function(){function b(a,g,e){e=e||new d;a=g(a);if(!("object"==typeof a&&a!==s&&a!==n&&!(a instanceof Date)))return a;var h=a instanceof Array?[]:{};e.save(a,h);c(a,function(c){var d=g(a[c]);switch(typeof d){case "boolean":case "number":case "string":case "function":h[c]=
d;break;case "object":case "undefined":var i=e.get(d);h[c]=i!==n?i:b(d,g,e)}});return h}function c(a,b){if(a instanceof Array){for(var c=0;c<a.length;c++)b(c);"function"==typeof a.toJSON&&b("toJSON")}else for(c in a)b(c)}function d(){var b=[],c=[];this.save=function(e,d){var j=a.a.j(b,e);0<=j?c[j]=d:(b.push(e),c.push(d))};this.get=function(e){e=a.a.j(b,e);return 0<=e?c[e]:n}}a.Ta=function(c){0==arguments.length&&m(Error("When calling ko.toJS, pass the object you want to convert."));return b(c,function(b){for(var c=
0;a.la(b)&&10>c;c++)b=b();return b})};a.toJSON=function(b,c,e){b=a.Ta(b);return a.a.sa(b,c,e)}})();a.b("toJS",a.Ta);a.b("toJSON",a.toJSON);(function(){a.k={r:function(b){switch(a.a.o(b)){case "option":return b.__ko__hasDomDataOptionValue__===p?a.a.f.get(b,a.c.options.oa):b.getAttribute("value");case "select":return 0<=b.selectedIndex?a.k.r(b.options[b.selectedIndex]):n;default:return b.value}},S:function(b,c){switch(a.a.o(b)){case "option":switch(typeof c){case "string":a.a.f.set(b,a.c.options.oa,
n);"__ko__hasDomDataOptionValue__"in b&&delete b.__ko__hasDomDataOptionValue__;b.value=c;break;default:a.a.f.set(b,a.c.options.oa,c),b.__ko__hasDomDataOptionValue__=p,b.value="number"===typeof c?c:""}break;case "select":for(var d=b.options.length-1;0<=d;d--)if(a.k.r(b.options[d])==c){b.selectedIndex=d;break}break;default:if(c===s||c===n)c="";b.value=c}}}})();a.b("selectExtensions",a.k);a.b("selectExtensions.readValue",a.k.r);a.b("selectExtensions.writeValue",a.k.S);a.g=function(){function b(a,b){for(var d=
s;a!=d;)d=a,a=a.replace(c,function(a,c){return b[c]});return a}var c=/\@ko_token_(\d+)\@/g,d=/^[\_$a-z][\_$a-z0-9]*(\[.*?\])*(\.[\_$a-z][\_$a-z0-9]*(\[.*?\])*)*$/i,f=["true","false"];return{D:[],W:function(c){var e=a.a.w(c);if(3>e.length)return[];"{"===e.charAt(0)&&(e=e.substring(1,e.length-1));for(var c=[],d=s,f,k=0;k<e.length;k++){var i=e.charAt(k);if(d===s)switch(i){case '"':case "'":case "/":d=k,f=i}else if(i==f&&"\\"!==e.charAt(k-1)){i=e.substring(d,k+1);c.push(i);var l="@ko_token_"+(c.length-
1)+"@",e=e.substring(0,d)+l+e.substring(k+1),k=k-(i.length-l.length),d=s}}f=d=s;for(var q=0,o=s,k=0;k<e.length;k++){i=e.charAt(k);if(d===s)switch(i){case "{":d=k;o=i;f="}";break;case "(":d=k;o=i;f=")";break;case "[":d=k,o=i,f="]"}i===o?q++:i===f&&(q--,0===q&&(i=e.substring(d,k+1),c.push(i),l="@ko_token_"+(c.length-1)+"@",e=e.substring(0,d)+l+e.substring(k+1),k-=i.length-l.length,d=s))}f=[];e=e.split(",");d=0;for(k=e.length;d<k;d++)q=e[d],o=q.indexOf(":"),0<o&&o<q.length-1?(i=q.substring(o+1),f.push({key:b(q.substring(0,
o),c),value:b(i,c)})):f.push({unknown:b(q,c)});return f},ka:function(b){for(var c="string"===typeof b?a.g.W(b):b,h=[],b=[],j,k=0;j=c[k];k++)if(0<h.length&&h.push(","),j.key){var i;a:{i=j.key;var l=a.a.w(i);switch(l.length&&l.charAt(0)){case "'":case '"':break a;default:i="'"+l+"'"}}j=j.value;h.push(i);h.push(":");h.push(j);l=a.a.w(j);if(0<=a.a.j(f,a.a.w(l).toLowerCase())?0:l.match(d)!==s)0<b.length&&b.push(", "),b.push(i+" : function(__ko_value) { "+j+" = __ko_value; }")}else j.unknown&&h.push(j.unknown);
c=h.join("");0<b.length&&(c=c+", '_ko_property_writers' : { "+b.join("")+" } ");return c},wb:function(b,c){for(var d=0;d<b.length;d++)if(a.a.w(b[d].key)==c)return p;return t},$:function(b,c,d,f,k){if(!b||!a.Ha(b)){if((b=c()._ko_property_writers)&&b[d])b[d](f)}else(!k||b()!==f)&&b(f)}}}();a.b("jsonExpressionRewriting",a.g);a.b("jsonExpressionRewriting.bindingRewriteValidators",a.g.D);a.b("jsonExpressionRewriting.parseObjectLiteral",a.g.W);a.b("jsonExpressionRewriting.insertPropertyAccessorsIntoJson",
a.g.ka);(function(){function b(a){return 8==a.nodeType&&(g?a.text:a.nodeValue).match(e)}function c(a){return 8==a.nodeType&&(g?a.text:a.nodeValue).match(h)}function d(a,e){for(var d=a,f=1,g=[];d=d.nextSibling;){if(c(d)&&(f--,0===f))return g;g.push(d);b(d)&&f++}e||m(Error("Cannot find closing comment tag to match: "+a.nodeValue));return s}function f(a,b){var c=d(a,b);return c?0<c.length?c[c.length-1].nextSibling:a.nextSibling:s}var g="<\!--test--\>"===document.createComment("test").text,e=g?/^<\!--\s*ko\s+(.*\:.*)\s*--\>$/:
/^\s*ko\s+(.*\:.*)\s*$/,h=g?/^<\!--\s*\/ko\s*--\>$/:/^\s*\/ko\s*$/,j={ul:p,ol:p};a.e={C:{},childNodes:function(a){return b(a)?d(a):a.childNodes},ha:function(c){if(b(c))for(var c=a.e.childNodes(c),e=0,d=c.length;e<d;e++)a.removeNode(c[e]);else a.a.ga(c)},X:function(c,e){if(b(c)){a.e.ha(c);for(var d=c.nextSibling,f=0,g=e.length;f<g;f++)d.parentNode.insertBefore(e[f],d)}else a.a.X(c,e)},Ka:function(a,c){b(a)?a.parentNode.insertBefore(c,a.nextSibling):a.firstChild?a.insertBefore(c,a.firstChild):a.appendChild(c)},
Fa:function(a,c,e){b(a)?a.parentNode.insertBefore(c,e.nextSibling):e.nextSibling?a.insertBefore(c,e.nextSibling):a.appendChild(c)},firstChild:function(a){return!b(a)?a.firstChild:!a.nextSibling||c(a.nextSibling)?s:a.nextSibling},nextSibling:function(a){b(a)&&(a=f(a));return a.nextSibling&&c(a.nextSibling)?s:a.nextSibling},Xa:function(a){return(a=b(a))?a[1]:s},Ia:function(e){if(j[a.a.o(e)]){var d=e.firstChild;if(d){do if(1===d.nodeType){var g;g=d.firstChild;var h=s;if(g){do if(h)h.push(g);else if(b(g)){var o=
f(g,p);o?g=o:h=[g]}else c(g)&&(h=[g]);while(g=g.nextSibling)}if(g=h){h=d.nextSibling;for(o=0;o<g.length;o++)h?e.insertBefore(g[o],h):e.appendChild(g[o])}}while(d=d.nextSibling)}}}}})();a.b("virtualElements",a.e);a.b("virtualElements.allowedBindings",a.e.C);a.b("virtualElements.emptyNode",a.e.ha);a.b("virtualElements.insertAfter",a.e.Fa);a.b("virtualElements.prepend",a.e.Ka);a.b("virtualElements.setDomNodeChildren",a.e.X);(function(){a.J=function(){this.cb={}};a.a.extend(a.J.prototype,{nodeHasBindings:function(b){switch(b.nodeType){case 1:return b.getAttribute("data-bind")!=
s;case 8:return a.e.Xa(b)!=s;default:return t}},getBindings:function(a,c){var d=this.getBindingsString(a,c);return d?this.parseBindingsString(d,c):s},getBindingsString:function(b){switch(b.nodeType){case 1:return b.getAttribute("data-bind");case 8:return a.e.Xa(b);default:return s}},parseBindingsString:function(b,c){try{var d=c.$data,d="object"==typeof d&&d!=s?[d,c]:[c],f=d.length,g=this.cb,e=f+"_"+b,h;if(!(h=g[e])){var j=" { "+a.g.ka(b)+" } ";h=g[e]=a.a.eb(j,f)}return h(d)}catch(k){m(Error("Unable to parse bindings.\nMessage: "+
k+";\nBindings value: "+b))}}});a.J.instance=new a.J})();a.b("bindingProvider",a.J);(function(){function b(b,d,e){for(var h=a.e.firstChild(d);d=h;)h=a.e.nextSibling(d),c(b,d,e)}function c(c,g,e){var h=p,j=1===g.nodeType;j&&a.e.Ia(g);if(j&&e||a.J.instance.nodeHasBindings(g))h=d(g,s,c,e).Gb;h&&b(c,g,!j)}function d(b,c,e,d){function j(a){return function(){return l[a]}}function k(){return l}var i=0,l,q;a.h(function(){var o=e&&e instanceof a.z?e:new a.z(a.a.d(e)),v=o.$data;d&&a.Ra(b,o);if(l=("function"==
typeof c?c():c)||a.J.instance.getBindings(b,o)){if(0===i){i=1;for(var u in l){var r=a.c[u];r&&8===b.nodeType&&!a.e.C[u]&&m(Error("The binding '"+u+"' cannot be used with virtual elements"));if(r&&"function"==typeof r.init&&(r=(0,r.init)(b,j(u),k,v,o))&&r.controlsDescendantBindings)q!==n&&m(Error("Multiple bindings ("+q+" and "+u+") are trying to control descendant bindings of the same element. You cannot use these bindings together on the same element.")),q=u}i=2}if(2===i)for(u in l)(r=a.c[u])&&"function"==
typeof r.update&&(0,r.update)(b,j(u),k,v,o)}},s,{disposeWhenNodeIsRemoved:b});return{Gb:q===n}}a.c={};a.z=function(b,c){c?(a.a.extend(this,c),this.$parentContext=c,this.$parent=c.$data,this.$parents=(c.$parents||[]).slice(0),this.$parents.unshift(this.$parent)):(this.$parents=[],this.$root=b);this.$data=b};a.z.prototype.createChildContext=function(b){return new a.z(b,this)};a.z.prototype.extend=function(b){var c=a.a.extend(new a.z,this);return a.a.extend(c,b)};a.Ra=function(b,c){if(2==arguments.length)a.a.f.set(b,
"__ko_bindingContext__",c);else return a.a.f.get(b,"__ko_bindingContext__")};a.ya=function(b,c,e){1===b.nodeType&&a.e.Ia(b);return d(b,c,e,p)};a.Ya=function(a,c){(1===c.nodeType||8===c.nodeType)&&b(a,c,p)};a.xa=function(a,b){b&&(1!==b.nodeType&&8!==b.nodeType)&&m(Error("ko.applyBindings: first parameter should be your view model; second parameter should be a DOM node"));b=b||window.document.body;c(a,b,p)};a.ea=function(b){switch(b.nodeType){case 1:case 8:var c=a.Ra(b);if(c)return c;if(b.parentNode)return a.ea(b.parentNode)}};
a.hb=function(b){return(b=a.ea(b))?b.$data:n};a.b("bindingHandlers",a.c);a.b("applyBindings",a.xa);a.b("applyBindingsToDescendants",a.Ya);a.b("applyBindingsToNode",a.ya);a.b("contextFor",a.ea);a.b("dataFor",a.hb)})();a.a.v(["click"],function(b){a.c[b]={init:function(c,d,f,g){return a.c.event.init.call(this,c,function(){var a={};a[b]=d();return a},f,g)}}});a.c.event={init:function(b,c,d,f){var g=c()||{},e;for(e in g)(function(){var g=e;"string"==typeof g&&a.a.n(b,g,function(b){var e,i=c()[g];if(i){var l=
d();try{var q=a.a.L(arguments);q.unshift(f);e=i.apply(f,q)}finally{e!==p&&(b.preventDefault?b.preventDefault():b.returnValue=t)}l[g+"Bubble"]===t&&(b.cancelBubble=p,b.stopPropagation&&b.stopPropagation())}})})()}};a.c.submit={init:function(b,c,d,f){"function"!=typeof c()&&m(Error("The value for a submit binding must be a function"));a.a.n(b,"submit",function(a){var e,d=c();try{e=d.call(f,b)}finally{e!==p&&(a.preventDefault?a.preventDefault():a.returnValue=t)}})}};a.c.visible={update:function(b,c){var d=
a.a.d(c()),f="none"!=b.style.display;d&&!f?b.style.display="":!d&&f&&(b.style.display="none")}};a.c.enable={update:function(b,c){var d=a.a.d(c());d&&b.disabled?b.removeAttribute("disabled"):!d&&!b.disabled&&(b.disabled=p)}};a.c.disable={update:function(b,c){a.c.enable.update(b,function(){return!a.a.d(c())})}};a.c.value={init:function(b,c,d){function f(){var e=c(),f=a.k.r(b);a.g.$(e,d,"value",f,p)}var g=["change"],e=d().valueUpdate;e&&("string"==typeof e&&(e=[e]),a.a.N(g,e),g=a.a.za(g));if(a.a.ja&&
("input"==b.tagName.toLowerCase()&&"text"==b.type&&"off"!=b.autocomplete&&(!b.form||"off"!=b.form.autocomplete))&&-1==a.a.j(g,"propertychange")){var h=t;a.a.n(b,"propertychange",function(){h=p});a.a.n(b,"blur",function(){if(h){h=t;f()}})}a.a.v(g,function(c){var e=f;if(a.a.Hb(c,"after")){e=function(){setTimeout(f,0)};c=c.substring(5)}a.a.n(b,c,e)})},update:function(b,c){var d="select"===a.a.o(b),f=a.a.d(c()),g=a.k.r(b),e=f!=g;0===f&&(0!==g&&"0"!==g)&&(e=p);e&&(g=function(){a.k.S(b,f)},g(),d&&setTimeout(g,
0));d&&0<b.length&&B(b,f,t)}};a.c.options={update:function(b,c,d){"select"!==a.a.o(b)&&m(Error("options binding applies only to SELECT elements"));for(var f=0==b.length,g=a.a.T(a.a.aa(b.childNodes,function(b){return b.tagName&&"option"===a.a.o(b)&&b.selected}),function(b){return a.k.r(b)||b.innerText||b.textContent}),e=b.scrollTop,h=a.a.d(c());0<b.length;)a.F(b.options[0]),b.remove(0);if(h){d=d();"number"!=typeof h.length&&(h=[h]);if(d.optionsCaption){var j=document.createElement("option");a.a.Y(j,
d.optionsCaption);a.k.S(j,n);b.appendChild(j)}for(var c=0,k=h.length;c<k;c++){var j=document.createElement("option"),i="string"==typeof d.optionsValue?h[c][d.optionsValue]:h[c],i=a.a.d(i);a.k.S(j,i);var l=d.optionsText,i="function"==typeof l?l(h[c]):"string"==typeof l?h[c][l]:i;if(i===s||i===n)i="";a.a.Qa(j,i);b.appendChild(j)}h=b.getElementsByTagName("option");c=j=0;for(k=h.length;c<k;c++)0<=a.a.j(g,a.k.r(h[c]))&&(a.a.Pa(h[c],p),j++);b.scrollTop=e;f&&"value"in d&&B(b,a.a.d(d.value),p);a.a.lb(b)}}};
a.c.options.oa="__ko.optionValueDomData__";a.c.selectedOptions={Ea:function(b){for(var c=[],b=b.childNodes,d=0,f=b.length;d<f;d++){var g=b[d],e=a.a.o(g);"option"==e&&g.selected?c.push(a.k.r(g)):"optgroup"==e&&(g=a.c.selectedOptions.Ea(g),Array.prototype.splice.apply(c,[c.length,0].concat(g)))}return c},init:function(b,c,d){a.a.n(b,"change",function(){var b=c(),g=a.c.selectedOptions.Ea(this);a.g.$(b,d,"value",g)})},update:function(b,c){"select"!=a.a.o(b)&&m(Error("values binding applies only to SELECT elements"));
var d=a.a.d(c());if(d&&"number"==typeof d.length)for(var f=b.childNodes,g=0,e=f.length;g<e;g++){var h=f[g];"option"===a.a.o(h)&&a.a.Pa(h,0<=a.a.j(d,a.k.r(h)))}}};a.c.text={update:function(b,c){a.a.Qa(b,c())}};a.c.html={init:function(){return{controlsDescendantBindings:p}},update:function(b,c){var d=a.a.d(c());a.a.Y(b,d)}};a.c.css={update:function(b,c){var d=a.a.d(c()||{}),f;for(f in d)if("string"==typeof f){var g=a.a.d(d[f]);a.a.Ua(b,f,g)}}};a.c.style={update:function(b,c){var d=a.a.d(c()||{}),f;
for(f in d)if("string"==typeof f){var g=a.a.d(d[f]);b.style[f]=g||""}}};a.c.uniqueName={init:function(b,c){c()&&(b.name="ko_unique_"+ ++a.c.uniqueName.gb,(a.a.tb||a.a.ub)&&b.mergeAttributes(document.createElement("<input name='"+b.name+"'/>"),t))}};a.c.uniqueName.gb=0;a.c.checked={init:function(b,c,d){a.a.n(b,"click",function(){var f;if("checkbox"==b.type)f=b.checked;else if("radio"==b.type&&b.checked)f=b.value;else return;var g=c();"checkbox"==b.type&&a.a.d(g)instanceof Array?(f=a.a.j(a.a.d(g),b.value),
b.checked&&0>f?g.push(b.value):!b.checked&&0<=f&&g.splice(f,1)):a.g.$(g,d,"checked",f,p)});"radio"==b.type&&!b.name&&a.c.uniqueName.init(b,A(p))},update:function(b,c){var d=a.a.d(c());"checkbox"==b.type?b.checked=d instanceof Array?0<=a.a.j(d,b.value):d:"radio"==b.type&&(b.checked=b.value==d)}};var F={"class":"className","for":"htmlFor"};a.c.attr={update:function(b,c){var d=a.a.d(c())||{},f;for(f in d)if("string"==typeof f){var g=a.a.d(d[f]),e=g===t||g===s||g===n;e&&b.removeAttribute(f);8>=a.a.ja&&
f in F?(f=F[f],e?b.removeAttribute(f):b[f]=g):e||b.setAttribute(f,g.toString())}}};a.c.hasfocus={init:function(b,c,d){function f(b){var e=c();a.g.$(e,d,"hasfocus",b,p)}a.a.n(b,"focus",function(){f(p)});a.a.n(b,"focusin",function(){f(p)});a.a.n(b,"blur",function(){f(t)});a.a.n(b,"focusout",function(){f(t)})},update:function(b,c){var d=a.a.d(c());d?b.focus():b.blur();a.a.va(b,d?"focusin":"focusout")}};a.c["with"]={p:function(b){return function(){var c=b();return{"if":c,data:c,templateEngine:a.q.K}}},
init:function(b,c){return a.c.template.init(b,a.c["with"].p(c))},update:function(b,c,d,f,g){return a.c.template.update(b,a.c["with"].p(c),d,f,g)}};a.g.D["with"]=t;a.e.C["with"]=p;a.c["if"]={p:function(b){return function(){return{"if":b(),templateEngine:a.q.K}}},init:function(b,c){return a.c.template.init(b,a.c["if"].p(c))},update:function(b,c,d,f,g){return a.c.template.update(b,a.c["if"].p(c),d,f,g)}};a.g.D["if"]=t;a.e.C["if"]=p;a.c.ifnot={p:function(b){return function(){return{ifnot:b(),templateEngine:a.q.K}}},
init:function(b,c){return a.c.template.init(b,a.c.ifnot.p(c))},update:function(b,c,d,f,g){return a.c.template.update(b,a.c.ifnot.p(c),d,f,g)}};a.g.D.ifnot=t;a.e.C.ifnot=p;a.c.foreach={p:function(b){return function(){var c=a.a.d(b());return!c||"number"==typeof c.length?{foreach:c,templateEngine:a.q.K}:{foreach:c.data,includeDestroyed:c.includeDestroyed,afterAdd:c.afterAdd,beforeRemove:c.beforeRemove,afterRender:c.afterRender,templateEngine:a.q.K}}},init:function(b,c){return a.c.template.init(b,a.c.foreach.p(c))},
update:function(b,c,d,f,g){return a.c.template.update(b,a.c.foreach.p(c),d,f,g)}};a.g.D.foreach=t;a.e.C.foreach=p;a.t=function(){};a.t.prototype.renderTemplateSource=function(){m(Error("Override renderTemplateSource"))};a.t.prototype.createJavaScriptEvaluatorBlock=function(){m(Error("Override createJavaScriptEvaluatorBlock"))};a.t.prototype.makeTemplateSource=function(b,c){if("string"==typeof b){var c=c||document,d=c.getElementById(b);d||m(Error("Cannot find template with ID "+b));return new a.l.i(d)}if(1==
b.nodeType||8==b.nodeType)return new a.l.M(b);m(Error("Unknown template type: "+b))};a.t.prototype.renderTemplate=function(a,c,d,f){return this.renderTemplateSource(this.makeTemplateSource(a,f),c,d)};a.t.prototype.isTemplateRewritten=function(a,c){return this.allowTemplateRewriting===t||!(c&&c!=document)&&this.V&&this.V[a]?p:this.makeTemplateSource(a,c).data("isRewritten")};a.t.prototype.rewriteTemplate=function(a,c,d){var f=this.makeTemplateSource(a,d),c=c(f.text());f.text(c);f.data("isRewritten",
p);!(d&&d!=document)&&"string"==typeof a&&(this.V=this.V||{},this.V[a]=p)};a.b("templateEngine",a.t);a.Z=function(){function b(b,c,e){for(var b=a.g.W(b),d=a.g.D,j=0;j<b.length;j++){var k=b[j].key;if(d.hasOwnProperty(k)){var i=d[k];"function"===typeof i?(k=i(b[j].value))&&m(Error(k)):i||m(Error("This template engine does not support the '"+k+"' binding within its templates"))}}b="ko.templateRewriting.applyMemoizedBindingsToNextSibling(function() {             return (function() { return { "+a.g.ka(b)+
" } })()         })";return e.createJavaScriptEvaluatorBlock(b)+c}var c=/(<[a-z]+\d*(\s+(?!data-bind=)[a-z0-9\-]+(=(\"[^\"]*\"|\'[^\']*\'))?)*\s+)data-bind=(["'])([\s\S]*?)\5/gi,d=/<\!--\s*ko\b\s*([\s\S]*?)\s*--\>/g;return{mb:function(b,c,e){c.isTemplateRewritten(b,e)||c.rewriteTemplate(b,function(b){return a.Z.zb(b,c)},e)},zb:function(a,g){return a.replace(c,function(a,c,d,f,i,l,q){return b(q,c,g)}).replace(d,function(a,c){return b(c,"<\!-- ko --\>",g)})},Za:function(b){return a.s.na(function(c,
e){c.nextSibling&&a.ya(c.nextSibling,b,e)})}}}();a.b("templateRewriting",a.Z);a.b("templateRewriting.applyMemoizedBindingsToNextSibling",a.Z.Za);(function(){a.l={};a.l.i=function(a){this.i=a};a.l.i.prototype.text=function(){var b=a.a.o(this.i),b="script"===b?"text":"textarea"===b?"value":"innerHTML";if(0==arguments.length)return this.i[b];var c=arguments[0];"innerHTML"===b?a.a.Y(this.i,c):this.i[b]=c};a.l.i.prototype.data=function(b){if(1===arguments.length)return a.a.f.get(this.i,"templateSourceData_"+
b);a.a.f.set(this.i,"templateSourceData_"+b,arguments[1])};a.l.M=function(a){this.i=a};a.l.M.prototype=new a.l.i;a.l.M.prototype.text=function(){if(0==arguments.length){var b=a.a.f.get(this.i,"__ko_anon_template__")||{};b.ua===n&&b.da&&(b.ua=b.da.innerHTML);return b.ua}a.a.f.set(this.i,"__ko_anon_template__",{ua:arguments[0]})};a.l.i.prototype.nodes=function(){if(0==arguments.length)return(a.a.f.get(this.i,"__ko_anon_template__")||{}).da;a.a.f.set(this.i,"__ko_anon_template__",{da:arguments[0]})};
a.b("templateSources",a.l);a.b("templateSources.domElement",a.l.i);a.b("templateSources.anonymousTemplate",a.l.M)})();(function(){function b(b,c,d){for(var f,c=a.e.nextSibling(c);b&&(f=b)!==c;)b=a.e.nextSibling(f),(1===f.nodeType||8===f.nodeType)&&d(f)}function c(c,d){if(c.length){var f=c[0],g=c[c.length-1];b(f,g,function(b){a.xa(d,b)});b(f,g,function(b){a.s.Wa(b,[d])})}}function d(a){return a.nodeType?a:0<a.length?a[0]:s}function f(b,f,j,k,i){var i=i||{},l=b&&d(b),l=l&&l.ownerDocument,q=i.templateEngine||
g;a.Z.mb(j,q,l);j=q.renderTemplate(j,k,i,l);("number"!=typeof j.length||0<j.length&&"number"!=typeof j[0].nodeType)&&m(Error("Template engine must return an array of DOM nodes"));l=t;switch(f){case "replaceChildren":a.e.X(b,j);l=p;break;case "replaceNode":a.a.Na(b,j);l=p;break;case "ignoreTargetNode":break;default:m(Error("Unknown renderMode: "+f))}l&&(c(j,k),i.afterRender&&i.afterRender(j,k.$data));return j}var g;a.ra=function(b){b!=n&&!(b instanceof a.t)&&m(Error("templateEngine must inherit from ko.templateEngine"));
g=b};a.qa=function(b,c,j,k,i){j=j||{};(j.templateEngine||g)==n&&m(Error("Set a template engine before calling renderTemplate"));i=i||"replaceChildren";if(k){var l=d(k);return a.h(function(){var g=c&&c instanceof a.z?c:new a.z(a.a.d(c)),o="function"==typeof b?b(g.$data):b,g=f(k,i,o,g,j);"replaceNode"==i&&(k=g,l=d(k))},s,{disposeWhen:function(){return!l||!a.a.fa(l)},disposeWhenNodeIsRemoved:l&&"replaceNode"==i?l.parentNode:l})}return a.s.na(function(d){a.qa(b,c,j,d,"replaceNode")})};a.Fb=function(b,
d,g,k,i){function l(a,b){c(b,o);g.afterRender&&g.afterRender(b,a)}function q(c,d){var h="function"==typeof b?b(c):b;o=i.createChildContext(a.a.d(c));o.$index=d;return f(s,"ignoreTargetNode",h,o,g)}var o;return a.h(function(){var b=a.a.d(d)||[];"undefined"==typeof b.length&&(b=[b]);b=a.a.aa(b,function(b){return g.includeDestroyed||b===n||b===s||!a.a.d(b._destroy)});a.a.Oa(k,b,q,g,l)},s,{disposeWhenNodeIsRemoved:k})};a.c.template={init:function(b,c){var d=a.a.d(c());if("string"!=typeof d&&!d.name&&
(1==b.nodeType||8==b.nodeType))d=1==b.nodeType?b.childNodes:a.e.childNodes(b),d=a.a.Ab(d),(new a.l.M(b)).nodes(d);return{controlsDescendantBindings:p}},update:function(b,c,d,f,g){c=a.a.d(c());f=p;"string"==typeof c?d=c:(d=c.name,"if"in c&&(f=f&&a.a.d(c["if"])),"ifnot"in c&&(f=f&&!a.a.d(c.ifnot)));var l=s;"object"===typeof c&&"foreach"in c?l=a.Fb(d||b,f&&c.foreach||[],c,b,g):f?(g="object"==typeof c&&"data"in c?g.createChildContext(a.a.d(c.data)):g,l=a.qa(d||b,g,c,b)):a.e.ha(b);g=l;(c=a.a.f.get(b,"__ko__templateSubscriptionDomDataKey__"))&&
"function"==typeof c.A&&c.A();a.a.f.set(b,"__ko__templateSubscriptionDomDataKey__",g)}};a.g.D.template=function(b){b=a.g.W(b);return 1==b.length&&b[0].unknown||a.g.wb(b,"name")?s:"This template engine does not support anonymous templates nested within its templates"};a.e.C.template=p})();a.b("setTemplateEngine",a.ra);a.b("renderTemplate",a.qa);(function(){a.a.O=function(b,c,d){if(d===n)return a.a.O(b,c,1)||a.a.O(b,c,10)||a.a.O(b,c,Number.MAX_VALUE);for(var b=b||[],c=c||[],f=b,g=c,e=[],h=0;h<=g.length;h++)e[h]=
[];for(var h=0,j=Math.min(f.length,d);h<=j;h++)e[0][h]=h;h=1;for(j=Math.min(g.length,d);h<=j;h++)e[h][0]=h;for(var j=f.length,k,i=g.length,h=1;h<=j;h++){k=Math.max(1,h-d);for(var l=Math.min(i,h+d);k<=l;k++)e[k][h]=f[h-1]===g[k-1]?e[k-1][h-1]:Math.min(e[k-1][h]===n?Number.MAX_VALUE:e[k-1][h]+1,e[k][h-1]===n?Number.MAX_VALUE:e[k][h-1]+1)}d=b.length;f=c.length;g=[];h=e[f][d];if(h===n)e=s;else{for(;0<d||0<f;){j=e[f][d];i=0<f?e[f-1][d]:h+1;l=0<d?e[f][d-1]:h+1;k=0<f&&0<d?e[f-1][d-1]:h+1;if(i===n||i<j-1)i=
h+1;if(l===n||l<j-1)l=h+1;k<j-1&&(k=h+1);i<=l&&i<k?(g.push({status:"added",value:c[f-1]}),f--):(l<i&&l<k?g.push({status:"deleted",value:b[d-1]}):(g.push({status:"retained",value:b[d-1]}),f--),d--)}e=g.reverse()}return e}})();a.b("utils.compareArrays",a.a.O);(function(){function b(a){if(2<a.length){for(var b=a[0],c=a[a.length-1],e=[b];b!==c;){b=b.nextSibling;if(!b)return;e.push(b)}Array.prototype.splice.apply(a,[0,a.length].concat(e))}}function c(c,f,g,e,h){var j=[],c=a.h(function(){var c=f(g,h)||
[];0<j.length&&(b(j),a.a.Na(j,c),e&&e(g,c));j.splice(0,j.length);a.a.N(j,c)},s,{disposeWhenNodeIsRemoved:c,disposeWhen:function(){return 0==j.length||!a.a.fa(j[0])}});return{xb:j,h:c}}a.a.Oa=function(d,f,g,e,h){for(var f=f||[],e=e||{},j=a.a.f.get(d,"setDomNodeChildrenFromArrayMapping_lastMappingResult")===n,k=a.a.f.get(d,"setDomNodeChildrenFromArrayMapping_lastMappingResult")||[],i=a.a.T(k,function(a){return a.$a}),l=a.a.O(i,f),f=[],q=0,o=[],v=0,i=[],u=s,r=0,w=l.length;r<w;r++)switch(l[r].status){case "retained":var y=
k[q];y.qb(v);v=f.push(y);0<y.P.length&&(u=y.P[y.P.length-1]);q++;break;case "deleted":k[q].h.A();b(k[q].P);a.a.v(k[q].P,function(a){o.push({element:a,index:r,value:l[r].value});u=a});q++;break;case "added":for(var y=l[r].value,x=a.m(v),v=c(d,g,y,h,x),C=v.xb,v=f.push({$a:l[r].value,P:C,h:v.h,qb:x}),z=0,B=C.length;z<B;z++){var D=C[z];i.push({element:D,index:r,value:l[r].value});u==s?a.e.Ka(d,D):a.e.Fa(d,D,u);u=D}h&&h(y,C,x)}a.a.v(o,function(b){a.F(b.element)});g=t;if(!j){if(e.afterAdd)for(r=0;r<i.length;r++)e.afterAdd(i[r].element,
i[r].index,i[r].value);if(e.beforeRemove){for(r=0;r<o.length;r++)e.beforeRemove(o[r].element,o[r].index,o[r].value);g=p}}if(!g&&o.length)for(r=0;r<o.length;r++)e=o[r].element,e.parentNode&&e.parentNode.removeChild(e);a.a.f.set(d,"setDomNodeChildrenFromArrayMapping_lastMappingResult",f)}})();a.b("utils.setDomNodeChildrenFromArrayMapping",a.a.Oa);a.q=function(){this.allowTemplateRewriting=t};a.q.prototype=new a.t;a.q.prototype.renderTemplateSource=function(b){var c=!(9>a.a.ja)&&b.nodes?b.nodes():s;
if(c)return a.a.L(c.cloneNode(p).childNodes);b=b.text();return a.a.pa(b)};a.q.K=new a.q;a.ra(a.q.K);a.b("nativeTemplateEngine",a.q);(function(){a.ma=function(){var a=this.vb=function(){if("undefined"==typeof jQuery||!jQuery.tmpl)return 0;try{if(0<=jQuery.tmpl.tag.tmpl.open.toString().indexOf("__"))return 2}catch(a){}return 1}();this.renderTemplateSource=function(b,f,g){g=g||{};2>a&&m(Error("Your version of jQuery.tmpl is too old. Please upgrade to jQuery.tmpl 1.0.0pre or later."));var e=b.data("precompiled");
e||(e=b.text()||"",e=jQuery.template(s,"{{ko_with $item.koBindingContext}}"+e+"{{/ko_with}}"),b.data("precompiled",e));b=[f.$data];f=jQuery.extend({koBindingContext:f},g.templateOptions);f=jQuery.tmpl(e,b,f);f.appendTo(document.createElement("div"));jQuery.fragments={};return f};this.createJavaScriptEvaluatorBlock=function(a){return"{{ko_code ((function() { return "+a+" })()) }}"};this.addTemplate=function(a,b){document.write("<script type='text/html' id='"+a+"'>"+b+"<\/script>")};0<a&&(jQuery.tmpl.tag.ko_code=
{open:"__.push($1 || '');"},jQuery.tmpl.tag.ko_with={open:"with($1) {",close:"} "})};a.ma.prototype=new a.t;var b=new a.ma;0<b.vb&&a.ra(b);a.b("jqueryTmplTemplateEngine",a.ma)})()}"function"===typeof require&&"object"===typeof exports&&"object"===typeof module?E(module.exports||exports):"function"===typeof define&&define.amd?define(["exports"],E):E(window.ko={});p;
})(window,document,navigator);
</script>


<script type="text/javascript">
/**
 * password_strength_plugin.js
 * Copyright (c) 20010 myPocket technologies (www.mypocket-technologies.com)

 * @author Darren Mason (djmason9@gmail.com)
 * @date 3/13/2009
 * @projectDescription Password Strength Meter is a jQuery plug-in provide you smart algorithm to detect a password strength. 
 * Based on Firas Kassem orginal plugin - phiras.wordpress.com/2007/04/08/password-strength-meter-a-jquery-plugin/
 * @version 1.0.1
*/
(function(a){a.fn.shortPass="Too short";a.fn.badPass="Weak";a.fn.goodPass="Good";a.fn.strongPass="Strong";a.fn.samePassword="Username and Password identical.";a.fn.resultStyle="";a.fn.passStrength=function(b){var d={shortPass:"shortPass",badPass:"badPass",goodPass:"goodPass",strongPass:"strongPass",baseStyle:"testresult",userid:"",messageloc:1};
var c=a.extend(d,b);return this.each(function(){var e=a(this);a(e).unbind().keyup(function(){var f=a.fn.teststrength(a(this).val(),a(c.userid).val(),c);if(c.messageloc===1){a(this).next("."+c.baseStyle).remove();a(this).after('<span class="'+c.baseStyle+'"><span></span></span>');a(this).next("."+c.baseStyle).addClass(a(this).resultStyle).find("span").text(f)
}else{a(this).prev("."+c.baseStyle).remove();a(this).before('<span class="'+c.baseStyle+'"><span></span></span>');a(this).prev("."+c.baseStyle).addClass(a(this).resultStyle).find("span").text(f)}});a.fn.teststrength=function(f,i,g){var h=0;if(f.length<4){this.resultStyle=g.shortPass;return a(this).shortPass
}if(f.toLowerCase()==i.toLowerCase()){this.resultStyle=g.badPass;return a(this).samePassword}h+=f.length*4;h+=(a.fn.checkRepetition(1,f).length-f.length)*1;h+=(a.fn.checkRepetition(2,f).length-f.length)*1;h+=(a.fn.checkRepetition(3,f).length-f.length)*1;h+=(a.fn.checkRepetition(4,f).length-f.length)*1;
if(f.match(/(.*[0-9].*[0-9].*[0-9])/)){h+=5}if(f.match(/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/)){h+=5}if(f.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)){h+=10}if(f.match(/([a-zA-Z])/)&&f.match(/([0-9])/)){h+=15}if(f.match(/([!,@,#,$,%,^,&,*,?,_,~])/)&&f.match(/([0-9])/)){h+=15}if(f.match(/([!,@,#,$,%,^,&,*,?,_,~])/)&&f.match(/([a-zA-Z])/)){h+=15
}if(f.match(/^\w+$/)||f.match(/^\d+$/)){h-=10}if(h<0){h=0}if(h>100){h=100}if(h<34){this.resultStyle=g.badPass;return a(this).badPass}if(h<68){this.resultStyle=g.goodPass;return a(this).goodPass}this.resultStyle=g.strongPass;return a(this).strongPass}})}})(jQuery);$.fn.checkRepetition=function(a,f){var d="";
for(var c=0;c<f.length;c++){var e=true;for(var b=0;b<a&&(b+c+a)<f.length;b++){e=e&&(f.charAt(b+c)==f.charAt(b+c+a))}if(b<a){e=false}if(e){c+=a-1;e=false}else{d+=f.charAt(c)}}return d};
</script>


<script type="text/javascript">
	//Unique namespace
	Duplicator = new Object();
	
	Duplicator.showProgressBar = function () {
		Duplicator.animateProgressBar('progress-bar');
		$('#ajaxerr-area').hide();
		$('#progress-area').show();
	}

	Duplicator.hideProgressBar = function () {
		$('#progress-area').hide(100);
		$('#ajaxerr-area').fadeIn(400);
	}

	Duplicator.animateProgressBar = function(id) {
		//Create Progress Bar
		var $mainbar   = $("#" + id);
		$mainbar.progressbar({ value: 100 });
		$mainbar.height(25);
		runAnimation($mainbar);
		
		function runAnimation($pb) {
			$pb.css({ "padding-left": "0%", "padding-right": "90%" });
			$pb.progressbar("option", "value", 100);
			$pb.animate({ paddingLeft: "90%", paddingRight: "0%" }, 3500, "linear", function () { runAnimation($pb); });
		}
	}
	
	Duplicator.dlgHelp = function() {
		$("#dup-main-help").dialog({
			height:650, width:750, modal: true,
			position:['center', 150],
			buttons: {Close: function() {$(this).dialog( "close" );}}
		});
	}
	
	$(document).ready(function() {
		//ATTACHED EVENTS
		$('#dup-hlp-lnk').change(function() {
			if ($(this).val() != "null") 
				window.open($(this).val())
		});
	});
</script>
    </head>
    <body>

        <div id="content">
            <!-- =========================================
            HEADER TEMPLATE: Common header on all steps -->
            <table cellspacing="0" class="header-wizard">
                <tr>
                    <td style="width:100%;">
                        <div style="font-size:19px; text-shadow:1px 1px 1px #777;">
                            <!-- !!DO NOT CHANGE/EDIT OR REMOVE PRODUCT NAME!!
                            If your interested in Private Label Rights please contact us at the URL below to discuss
                            customizations to product labeling: http://lifeinthegrid.com/services/	-->
                            &nbsp; Duplicator - Installer
                        </div>
                    </td>
                    <td style="white-space:nowrap;padding:4px">
                        <select id="dup-hlp-lnk">
                            <option value="null"> - Online Resources -</option>
                            <option value="http://lifeinthegrid.com/duplicator-docs">&raquo; Knowledge Base</option>
                            <option value="http://lifeinthegrid.com/duplicator-guide">&raquo; User Guide</option>
                            <option value="http://lifeinthegrid.com/duplicator-faq">&raquo; Common FAQs</option>
                            <option value="http://lifeinthegrid.com/duplicator-hosts">&raquo; Approved Hosts</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <?php
                        $step1CSS = ($_POST['action_step'] <= 1) ? "active-step" : "complete-step";
                        $step2CSS = ($_POST['action_step'] == 2) ? "active-step" : "";

                        $step3CSS = "";
                        if ($_POST['action_step'] == 3) {
                            $step2CSS = "complete-step";
                            $step3CSS = "active-step";
                        }
                        ?>
                        <div class="wizard-steps">
                            <div class="<?php echo $step1CSS; ?>"><a><span>1</span> Deploy</a></div>
                            <div class="<?php echo $step2CSS; ?>"><a><span>2</span> Update </a></div>
                            <div class="<?php echo $step3CSS; ?>"><a><span>3</span> Test </a></div>
                        </div>
                        <div style="float:right; padding-right:8px">
                            <i style='font-size:11px; color:#999'>installer version: <?php echo $GLOBALS['FW_DUPLICATOR_VERSION'] . $GLOBALS['FW_RESCUE_FLAG'] ?></i> &nbsp;
                            <a href="javascript:void(0)" onclick="Duplicator.dlgHelp()">[Help]</a>
                        </div>
                    </td>
                </tr>
            </table>	

            <!-- =========================================
            FORM DATA: Data Steps -->
            <div id="content-inner">
		<?php
		switch ($_POST['action_step']) {
		    case "0" :
			?> <?php

	//DETECT ARCHIVE FILES
	$zip_file_name  = "No package file found";
	$zip_file_count = 0;
	foreach (glob("*.zip") as $filename) {
		$zip_file_name = $filename;
		$zip_file_count++;
	}
	if ($zip_file_count > 1) {
		$zip_file_name = "Too many zip files in directory";
	}
	
	$req01a = @is_writeable($GLOBALS["CURRENT_ROOT_PATH"]) 	? 'Pass' : 'Fail';
	$req01b = ($zip_file_count == 1) ? 'Pass' : 'Fail';
	$req01  = ($req01a == 'Pass' && $req01b == 'Pass') ? 'Pass' : 'Fail';
	$req02 = (((strtolower(@ini_get('safe_mode'))   == 'on')   
				||  (strtolower(@ini_get('safe_mode')) == 'yes') 
				||  (strtolower(@ini_get('safe_mode')) == 'true') 
				||  (ini_get("safe_mode") == 1 ))) ? 'Fail' : 'Pass';
	$req03  = function_exists('mysqli_connect') ? 'Pass' : 'Fail';
	$php_compare  = version_compare(phpversion(), '5.2.17');
	$req04 = $php_compare >= 0 ? 'Pass' : 'Fail';
	$total_req = ($req01 == 'Pass' && $req02 == 'Pass' && $req03 == 'Pass' && $req04 == 'Pass') ? 'Pass' : 'Fail';
?>

<script type="text/javascript">
	/** **********************************************
	* METHOD:  Performs Ajax post to extract files and create db
	* Timeout (10000000 = 166 minutes) */
	Duplicator.runDeployment = function() {
		
		if ( $.trim($("#dbhost").val()) == "" )  {alert("The database server 'Host' field is required!"); return false;}
		if ( $.trim($("#dbuser").val()) == "" )  {alert("The database server 'User' field is required!"); return false;}
		if ( $.trim($("#dbname").val()) == "" )  {alert("The 'Database Name' field is required!"); return false;}
		
		var msg =  "Continue installation with the following settings?\n\n";
			msg += "Server: " + $("#dbhost").val() + "\nDatabase: " + $("#dbname").val() + "\n\n";
			msg += "WARNING: Be sure these database parameters are correct!\n";
			msg += "Entering the wrong information WILL overwrite an existing database.\n";
			msg += "Make sure to have backups of all your data before proceeding.\n\n";
			
		var answer = confirm(msg);
		if (answer) {
			$.ajax({
				type: "POST",
				timeout: 10000000,
				dataType: "json",
				url: window.location.href,
				data: $('#dup-step1-input-form').serialize(),
				beforeSend: function() {
					Duplicator.showProgressBar();
					$('#dup-step1-input-form').hide();
					$('#dup-step1-result-form').show();
				},			
				success: function(data, textStatus, xhr){ 
					if (typeof(data) != 'undefined' && data.pass == 1) {
						$("#ajax-dbhost").val($("#dbhost").val());
						$("#ajax-dbuser").val($("#dbuser").val());
						$("#ajax-dbpass").val($("#dbpass").val());
						$("#ajax-dbname").val($("#dbname").val());
						$("#ajax-dbcharset").val($("#dbcharset").val());
						$("#ajax-dbcollate").val($("#dbcollate").val());
						$("#ajax-logging").val($("#logging").val());
						$("#ajax-json").val(escape(JSON.stringify(data)));
						setTimeout(function() {$('#dup-step1-result-form').submit();}, 200);
						$('#progress-area').fadeOut(700);
					} else {
						Duplicator.hideProgressBar();
					}
				},
				error: function(xhr) { 
					var status = "<b>server code:</b> " + xhr.status + "<br/><b>status:</b> " + xhr.statusText + "<br/><b>response:</b> " +  xhr.responseText;
					$('#ajaxerr-data').html(status);
					Duplicator.hideProgressBar();
				}
			});	
		} 
	};

	/** **********************************************
	* METHOD: Accetps Useage Warning */
	Duplicator.acceptWarning = function() {
		if ($("#accept-warnings").is(':checked')) {
			$("#dup-step1-deploy-btn").removeAttr("disabled");
		} else {
			$("#dup-step1-deploy-btn").attr("disabled", "true");
		}
	};

	/** **********************************************
	* METHOD: Go back on AJAX result view */
	Duplicator.hideErrorResult = function() {
		$('#dup-step1-result-form').hide();			
		$('#dup-step1-input-form').show(200);
	};
	
	/** **********************************************
	* METHOD: Go back on AJAX result view */
	Duplicator.dlgSysChecks = function() {
		$( "#dup-step1-dialog" ).dialog({
			height:500, width:600, modal: true,
			position:['center', 150],
			buttons: {Close: function() {$(this).dialog( "close" );}}
		});
	};
	
	/** **********************************************
	* METHOD: Shows results of database connection 
	* Timeout (45000 = 45 secs) */
	Duplicator.dlgTestDB = function () {
		$.ajax({
			type: "POST",
			timeout: 45000,
			url: window.location.href + '?' + 'dbtest=1',
			data: $('#dup-step1-input-form').serialize(),
			success: function(data){ $('#dbconn-test-msg').html(data); },
			error:   function(data){ alert('An error occurred while testing the database connection!  Be sure the install file and package are both in the same directory.'); }
		});
		
		$("#dup-step1-dialog-db").dialog({
			height:400, width:550, modal: true,
			position:['center', 150],
			buttons: {Close: function() {$(this).dialog( "close" );}}
		});
	
		$('#dbconn-test-msg').html("Attempting Connection.  Please wait...");
	};
	
	//DOCUMENT LOAD
	$(document).ready(function() {
		Duplicator.acceptWarning();
	});
</script>


<!-- =========================================
VIEW: STEP 1- INPUT -->
<form id='dup-step1-input-form' method="post" class="content-form">
	<input type="hidden" name="action_ajax" value="1" />
	<input type="hidden" name="action_step" value="1" />
	<input type="hidden" name="package_name"  value="<?php echo $zip_file_name ?>" />
	
	<div class="dup-logfile-link">
		<select name="logging" id="logging">
		    <option value="1" selected="selected">Light Logging</option>
		    <option value="2">Detailed Logging</option>
		</select>
	</div>
	<h3 style="margin-bottom:5px">
	    Step 1: Files &amp; Database
	</h3>
	<hr size="1" />
	
	<!-- CHECKS: FAIL -->
	<?php if ( $total_req == 'Fail')  :	?>
	    
    	<div id="dup-step1-sys-req-btn" onclick="Duplicator.dlgSysChecks()">
    	    <div id="system-circle" class="circle-fail"></div> &nbsp; System Requirements: Fail...
    	</div><br/>
    		    
    	<i id="dup-step1-sys-req-msg">This installation will not be able to proceed until the system requirements pass. Please validate your system requirements by clicking on the button above. 
    	In order to get these values to pass please contact your server administrator, hosting provider or visit the online FAQ.</i><br/>
    		    
    	<div style="line-height:28px; font-size:14px; padding:0px 0px 0px 30px; font-weight:normal">
    	    <b>Helpful Resources:</b><br/>
    	    &raquo; <a href="http://lifeinthegrid.com/duplicator-faq" target="_blank">Common FAQs</a> <br/>
    	    &raquo; <a href="http://lifeinthegrid.com/duplicator-guide" target="_blank">User Guide</a> <br/>
    	    &raquo; <a href="http://lifeinthegrid.com/duplicator-hosts" target="_blank">Approved Hosts</a> <br/>
    	</div><br/>
	
	<!-- CHECKS: PASS -->
	<?php else : ?>	

	<div id="dup-step1-sys-req-btn" onclick="Duplicator.dlgSysChecks()">
    	    <div id="system-circle" class="circle-pass"></div>  &nbsp; System Requirements: Pass...<br/>
    	</div>
    	<div style='color:#999; font-size:11px; text-align:center; margin:3px 0px 0px 0px'><i>Package Name:<?php echo $zip_file_name; ?> </i></div><br/>
    		    
    	<div class="title-header">
    	    MySQL Server
    	</div>
    	<table class="table-inputs">
    	    <tr><td style="width:130px">Host</td><td><input type="text" name="dbhost" id="dbhost" value="<?php echo htmlspecialchars($GLOBALS['FW_DBHOST']); ?>" /></td></tr>
    	    <tr><td>User</td><td><input type="text" name="dbuser" id="dbuser" value="<?php echo htmlspecialchars($GLOBALS['FW_DBUSER']); ?>" /></td></tr>
    	    <tr><td>Password</td><td><input type="text" name="dbpass" id="dbpass" value="<?php echo htmlspecialchars($GLOBALS['FW_DBPASS']); ?>" /></td></tr>
    	</table>
    		    
    	<table class="table-inputs">
    	    <tr><td style="width:130px">Database Name</td><td><input type="text" name="dbname" id="dbname" value="<?php echo htmlspecialchars($GLOBALS['FW_DBNAME']); ?>" /></td></tr>
    	    <tr>
    		<td>Allow Options</td>
    		<td>
    		    <table class="dbtable-opts">
	    			<tr>
	    			    <td><input type="checkbox" name="dbmake" id="dbmake" checked="checked" value="1" /> <label for="dbmake">Database Creation</label></td>
	    			    <td><input type="checkbox" name="dbclean" id="dbclean" value="1" /> <label for="dbclean">Table Removal</label> </td>
	    			</tr>						
    		    </table>	
    		</td>
    	    </tr>
    	</table>
	<div style="margin:auto; text-align:center"><input id="dup-step1-dbconn-btn" type="button" onclick="Duplicator.dlgTestDB()" style="" value="Test Connection..." /></div>
    	<br/>
	
    		    
    	<!-- !!DO NOT CHANGE/EDIT OR REMOVE THIS SECTION!!
    	If your interested in Private Label Rights please contact us at the URL below to discuss
    	customizations to product labeling: http://lifeinthegrid.com/services/	-->
    	<a href="javascript:void(0)" onclick="$('#dup-step1-cpanel').toggle(250)"><b>Database Setup Help...</b></a>
    	<div id='dup-step1-cpanel' style="display:none">
    	    <div style="padding:10px 0px 0px 10px;line-height:22px">
    		<b>Need cPanel Database Help?</b> <br/>
    		&raquo; See the online <a href="http://lifeinthegrid.com/duplicator-tutorials" target="_blank">video tutorials &amp; guides</a> <br/>
    		&raquo; Need a host that supports cPanel?  See the Duplicator <a href="http://lifeinthegrid.com/duplicator-hosts" target="_blank">approved hosting</a> page.
    	    </div>
    	</div><br/><br/>
    		    
    	<a href="javascript:void(0)" onclick="$('#dup-step1-adv-opts').toggle(250)"><b>Advanced Options...</b></a>
    	<div id='dup-step1-adv-opts' style="display:none">
    	    <table class="table-inputs">
    		<tr><td colspan="2"><input type="checkbox" name="zip_manual"  id="zip_manual"   value="1" /> <label for="zip_manual">Manual package extraction</label></td></tr>
    		<tr><td colspan="2"><input type="checkbox" name="ssl_admin" id="ssl_admin" <?php echo ($GLOBALS['FW_SSL_ADMIN']) ? "checked='checked'" : ""; ?> /> <label for="ssl_admin">Enforce SSL on Admin</label></td></tr>
			<tr><td colspan="2"><input type="checkbox" name="ssl_login" id="ssl_login" <?php echo ($GLOBALS['FW_SSL_LOGIN']) ? "checked='checked'" : ""; ?> /> <label for="ssl_login">Enforce SSL on Login</label></td></tr>
			<tr><td colspan="2"><input type="checkbox" name="cache_wp" id="cache_wp" <?php echo ($GLOBALS['FW_CACHE_WP']) ? "checked='checked'" : ""; ?> /> <label for="cache_wp">Keep Cache Enabled</label></td></tr>
			<tr><td colspan="2"><input type="checkbox" name="cache_path" id="cache_path" <?php echo ($GLOBALS['FW_CACHE_PATH']) ? "checked='checked'" : ""; ?> /> <label for="cache_path">Keep Cache Home Path</label></td></tr>
    		<tr><td colspan="2"><input type="checkbox" name="dbnbsp" id="dbnbsp" value="1" /> <label for="dbnbsp">Fix non-breaking space characters</label></td></tr>
    		<tr><td style="width:130px">MySQL Charset</td><td><input type="text" name="dbcharset" id="dbcharset" value="<?php echo $_POST['dbcharset'] ?>" /> </td></tr>
    		<tr><td>MySQL Collation </td><td><input type="text" name="dbcollate" id="dbcollate" value="<?php echo $_POST['dbcollate'] ?>" /> </tr>
    	    </table>
    	</div>

	<!-- NOTICES  -->
    	<div class="warning-info" style="margin-top:50px">
    	    <b>WARNINGS &amp; NOTICES</b> 
    	    <p><b>Disclaimer:</b> This plugin has been heavily tested, however it does require above average technical knowledge. Please use it at your own risk and do not forget to back up your database and files beforehand. If you're not sure about how to use this tool then please enlist the guidance of a technical professional.</p>
    			    
    	    <p><b>Database:</b>  Do not attempt to connect to an existing database unless you are 100% sure you want to remove all of it's data. Connecting to a database that already exists will permanently DELETE all data in that database. This tool is designed to populate and fill a database with NEW data from a duplicated database using the SQL script in the package name above.</p>
    			    
    	    <p><b>Setup:</b> Only the package (zip file) and installer.php file should be in the install directory, unless you have manually extracted the package and checked the 'Manual Package Extraction' checkbox. All other files will be OVERWRITTEN during install.  Make sure you have full backups of all your databases and files before continuing with an installation.</p>
    			    
    	    <p><b>Manual Extraction:</b> Manual extraction requires that all contents in the package are extracted to the same directory as the installer.php file.  Manual extraction is only needed when your server does not support the ZipArchive extension.  Please see the online help for more details.</p>
    			    
    	    <p><b>After Install:</b>When you are done with the installation remove the installer.php, installer-data.sql and the installer-log.txt files from your directory. 
    		These files contain sensitive information and should not remain on a production system.</p><br/>
    	</div>
    		    
    	<div class="dup-step1-warning-area">
    	    <input id="accept-warnings"   type="checkbox" onclick="Duplicator.acceptWarning()" /> <label for="accept-warnings">I have read all warnings &amp; notices</label><br/>
    	</div><br/><br/><br/>
    		    
    	<div class="dup-footer-buttons">
    	    <input id="dup-step1-deploy-btn" type="button" value=" Run Deployment " onclick="Duplicator.runDeployment()" />
    	</div>				
	<?php endif; ?>	
</form>


<!-- =========================================
VIEW: STEP 1 - AJAX RESULT
Auto Posts to view.step2.php  -->
<form id='dup-step1-result-form' method="post" class="content-form" style="display:none">
	<input type="hidden" name="action_step" value="2" />
	<input type="hidden" name="package_name" value="<?php echo $zip_file_name ?>" />
	<!-- Set via jQuery -->
	<input type="hidden" name="logging" id="ajax-logging"  />	
	<input type="hidden" name="dbhost" id="ajax-dbhost" />
	<input type="hidden" name="dbuser" id="ajax-dbuser" />
	<input type="hidden" name="dbpass" id="ajax-dbpass" />
	<input type="hidden" name="dbname" id="ajax-dbname" />
	<input type="hidden" name="json"   id="ajax-json" />
	<input type="hidden" name="dbcharset" id="ajax-dbcharset" />
	<input type="hidden" name="dbcollate" id="ajax-dbcollate" />
	
    <div class="dup-logfile-link"><a href="installer-log.txt" target="_blank">installer-log.txt</a></div>
	<h3>Step 1: Files &amp; Database</h3>
	<hr size="1" />
	    
	<!--  PROGRESS BAR -->
	<div id="progress-area">
	    <div style="width:500px; margin:auto">
		<h3>Processing Files &amp; Database Please Wait...</h3>
		<div id="progress-bar"></div>
		<i>This may take several minutes</i>
	    </div>
	</div>
	    
	<!--  AJAX SYSTEM ERROR -->
	<div id="ajaxerr-area" style="display:none">
	    <p>Please try again an issue has occurred.</p>
	    <div style="padding: 0px 10px 10px 10px;">
		<div id="ajaxerr-data">An unknown issue has occurred with the file and database setup process.  Please see the installer-log.txt file for more details.</div>
		<i style='font-size:11px'>See online help for more details at <a href='http://lifeinthegrid.com/support' target='_blank'>support.lifeinthegrid.com</a></i>
	    </div>
	</div>
</form>


<!-- =========================================
DIALOG: SERVER CHECKS  -->
<div id="dup-step1-dialog" title="System Status" style="display:none">
	<div id="dup-step1-dialog-data" style="padding: 0px 10px 10px 10px;">
					
	    <!-- SYSTEM REQUIREMENTS -->
	    <b>REQUIREMENTS</b> &nbsp; <i style='font-size:11px'>click links for details</i>
	    <hr size="1"/>
	    <table style="width:100%">
		<tr>
		    <td style="width:300px"><a href="javascript:void(0)" onclick="$('#dup-SRV01').toggle(400)">Root Directory</a></td>
		    <td class="<?php echo ($req01 == 'Pass') ? 'dup-pass' : 'dup-fail' ?>"><?php echo $req01; ?></td>
		</tr>
		<tr>
		    <td colspan="2" id="dup-SRV01" class='dup-step1-dialog-data-details'>
			<?php
			echo "<i>Path: {$GLOBALS['CURRENT_ROOT_PATH']} </i><br/>";
			printf("<b>[%s]</b> %s <br/>", $req01a, "Is Writable");
			printf("<b>[%s]</b> %s <br/>", $req01b, "Contains only one zip file.<div style='padding-left:55px'>Result = {$zip_file_name} <br/> <i>Manual extraction still requires zip file</i> </div> ");
			?>
		    </td>
		</tr>
		<tr>
		    <td>Safe Mode Off</td>
		    <td class="<?php echo ($req02 == 'Pass') ? 'dup-pass' : 'dup-fail' ?>"><?php echo $req02; ?></td>
		</tr>
		<tr>
		    <td>MySQL Support</td>
		    <td class="<?php echo ($req03 == 'Pass') ? 'dup-pass' : 'dup-fail' ?>"><?php echo $req03; ?></td>
		</tr>
		<tr>
		    <td valign="top">
			PHP Version: <?php echo phpversion(); ?><br/>
			<i style="font-size:10px">(PHP 5.2.17+ is required)</i>
		    </td>
		    <td class="<?php echo ($req04 == 'Pass') ? 'dup-pass' : 'dup-fail' ?>"><?php echo $req04; ?> </td>
		</tr>
	    </table><br/>
		

	    <!-- SYSTEM CHECKS -->
	    <b>CHECKS</b><hr style='margin-top:-2px' size="1"/>
	    <table style="width:100%">
		<tr>
		    <td style="width:300px"></td>
		    <td></td>
		</tr>
		<tr>
		    <?php if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') !== false): ?>
    		    <td><b>Web Server:</b> Apache</td>
    		    <td><div class='dup-pass'>Good</div></td>
		    <?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false): ?> 
    		    <td><b>Web Server:</b> LiteSpeed</td>
    		    <td><div class='dup-ok'>OK</div></td>
		    <?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false): ?> 
    		    <td><b>Web Server:</b> Nginx</td>
    		    <td><div class='dup-ok'>OK</div></td>
		    <?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false): ?> 
    		    <td><b>Web Server:</b> Lighthttpd</td>
    		    <td><div class='dup-ok'>OK</div></td>
		    <?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') !== false): ?> 
    		    <td><b>Web Server:</b> Microsoft IIS</td>
    		    <td><div class='dup-ok'>OK</div></td>
		    <?php else: ?>
    		    <td><b>Web Server:</b> Not detected</td>
    		    <td><div class='dup-fail'>Caution</div></td>
		    <?php endif; ?>				
		</tr>
		<tr>
		    <?php
		    $open_basedir_set = ini_get("open_basedir");
		    if (empty($open_basedir_set)):
			?>
    		    <td><b>Open Base Dir:</b> Off
    		    <td><div class='dup-pass'>Good</div>
		    <?php else: ?>
    		    <td><b>Open Base Dir:</b> On</td>
    		    <td><div class='dup-fail'>Caution</div></td>
		    <?php endif; ?>
		</tr>
	    </table>
		    
	    <hr class='dup-dots' />
	    <!-- SAPI -->
	    <b>PHP SAPI:</b>  <?php echo php_sapi_name(); ?><br/>
	    <b>PHP ZIP Archive:</b> <?php echo class_exists('ZipArchive') ? 'Is Installed' : 'Not Installed'; ?> 
	</div>
</div>


<!-- =========================================
DIALOG: DB CONNECTION CHECK  -->
<div id="dup-step1-dialog-db" title="Test Database Connection" style="display:none">
    <div id="dup-step1-dialog-db-data" style="padding: 0px 10px 10px 10px;">		
	<div id="dbconn-test-msg" style="min-height:50px"></div>
	<br/><hr size="1" />
	<div class="help">
	    <b>Common Connection Issues:</b><br/>
	    - Double check case sensitive values 'User', 'Password' &amp; the 'Database Name' <br/>
	    - Validate the database and database user exist on this server <br/>
	    - Check if the database user has the correct permission levels to this database <br/>
	    - The host 'localhost' may not work on all hosting providers <br/>
	    - Contact your hosting provider for the exact required parameters <br/>
	    - See the 'Database Setup Help' section on step 1 for more details<br/>
	    - Visit the online resources 'Common FAQ page' <br/>
	</div>
    </div>
</div> <?php
		    break;
		    case "1" :
			?> <?php

	//DETECT ARCHIVE FILES
	$zip_file_name  = "No package file found";
	$zip_file_count = 0;
	foreach (glob("*.zip") as $filename) {
		$zip_file_name = $filename;
		$zip_file_count++;
	}
	if ($zip_file_count > 1) {
		$zip_file_name = "Too many zip files in directory";
	}
	
	$req01a = @is_writeable($GLOBALS["CURRENT_ROOT_PATH"]) 	? 'Pass' : 'Fail';
	$req01b = ($zip_file_count == 1) ? 'Pass' : 'Fail';
	$req01  = ($req01a == 'Pass' && $req01b == 'Pass') ? 'Pass' : 'Fail';
	$req02 = (((strtolower(@ini_get('safe_mode'))   == 'on')   
				||  (strtolower(@ini_get('safe_mode')) == 'yes') 
				||  (strtolower(@ini_get('safe_mode')) == 'true') 
				||  (ini_get("safe_mode") == 1 ))) ? 'Fail' : 'Pass';
	$req03  = function_exists('mysqli_connect') ? 'Pass' : 'Fail';
	$php_compare  = version_compare(phpversion(), '5.2.17');
	$req04 = $php_compare >= 0 ? 'Pass' : 'Fail';
	$total_req = ($req01 == 'Pass' && $req02 == 'Pass' && $req03 == 'Pass' && $req04 == 'Pass') ? 'Pass' : 'Fail';
?>

<script type="text/javascript">
	/** **********************************************
	* METHOD:  Performs Ajax post to extract files and create db
	* Timeout (10000000 = 166 minutes) */
	Duplicator.runDeployment = function() {
		
		if ( $.trim($("#dbhost").val()) == "" )  {alert("The database server 'Host' field is required!"); return false;}
		if ( $.trim($("#dbuser").val()) == "" )  {alert("The database server 'User' field is required!"); return false;}
		if ( $.trim($("#dbname").val()) == "" )  {alert("The 'Database Name' field is required!"); return false;}
		
		var msg =  "Continue installation with the following settings?\n\n";
			msg += "Server: " + $("#dbhost").val() + "\nDatabase: " + $("#dbname").val() + "\n\n";
			msg += "WARNING: Be sure these database parameters are correct!\n";
			msg += "Entering the wrong information WILL overwrite an existing database.\n";
			msg += "Make sure to have backups of all your data before proceeding.\n\n";
			
		var answer = confirm(msg);
		if (answer) {
			$.ajax({
				type: "POST",
				timeout: 10000000,
				dataType: "json",
				url: window.location.href,
				data: $('#dup-step1-input-form').serialize(),
				beforeSend: function() {
					Duplicator.showProgressBar();
					$('#dup-step1-input-form').hide();
					$('#dup-step1-result-form').show();
				},			
				success: function(data, textStatus, xhr){ 
					if (typeof(data) != 'undefined' && data.pass == 1) {
						$("#ajax-dbhost").val($("#dbhost").val());
						$("#ajax-dbuser").val($("#dbuser").val());
						$("#ajax-dbpass").val($("#dbpass").val());
						$("#ajax-dbname").val($("#dbname").val());
						$("#ajax-dbcharset").val($("#dbcharset").val());
						$("#ajax-dbcollate").val($("#dbcollate").val());
						$("#ajax-logging").val($("#logging").val());
						$("#ajax-json").val(escape(JSON.stringify(data)));
						setTimeout(function() {$('#dup-step1-result-form').submit();}, 200);
						$('#progress-area').fadeOut(700);
					} else {
						Duplicator.hideProgressBar();
					}
				},
				error: function(xhr) { 
					var status = "<b>server code:</b> " + xhr.status + "<br/><b>status:</b> " + xhr.statusText + "<br/><b>response:</b> " +  xhr.responseText;
					$('#ajaxerr-data').html(status);
					Duplicator.hideProgressBar();
				}
			});	
		} 
	};

	/** **********************************************
	* METHOD: Accetps Useage Warning */
	Duplicator.acceptWarning = function() {
		if ($("#accept-warnings").is(':checked')) {
			$("#dup-step1-deploy-btn").removeAttr("disabled");
		} else {
			$("#dup-step1-deploy-btn").attr("disabled", "true");
		}
	};

	/** **********************************************
	* METHOD: Go back on AJAX result view */
	Duplicator.hideErrorResult = function() {
		$('#dup-step1-result-form').hide();			
		$('#dup-step1-input-form').show(200);
	};
	
	/** **********************************************
	* METHOD: Go back on AJAX result view */
	Duplicator.dlgSysChecks = function() {
		$( "#dup-step1-dialog" ).dialog({
			height:500, width:600, modal: true,
			position:['center', 150],
			buttons: {Close: function() {$(this).dialog( "close" );}}
		});
	};
	
	/** **********************************************
	* METHOD: Shows results of database connection 
	* Timeout (45000 = 45 secs) */
	Duplicator.dlgTestDB = function () {
		$.ajax({
			type: "POST",
			timeout: 45000,
			url: window.location.href + '?' + 'dbtest=1',
			data: $('#dup-step1-input-form').serialize(),
			success: function(data){ $('#dbconn-test-msg').html(data); },
			error:   function(data){ alert('An error occurred while testing the database connection!  Be sure the install file and package are both in the same directory.'); }
		});
		
		$("#dup-step1-dialog-db").dialog({
			height:400, width:550, modal: true,
			position:['center', 150],
			buttons: {Close: function() {$(this).dialog( "close" );}}
		});
	
		$('#dbconn-test-msg').html("Attempting Connection.  Please wait...");
	};
	
	//DOCUMENT LOAD
	$(document).ready(function() {
		Duplicator.acceptWarning();
	});
</script>


<!-- =========================================
VIEW: STEP 1- INPUT -->
<form id='dup-step1-input-form' method="post" class="content-form">
	<input type="hidden" name="action_ajax" value="1" />
	<input type="hidden" name="action_step" value="1" />
	<input type="hidden" name="package_name"  value="<?php echo $zip_file_name ?>" />
	
	<div class="dup-logfile-link">
		<select name="logging" id="logging">
		    <option value="1" selected="selected">Light Logging</option>
		    <option value="2">Detailed Logging</option>
		</select>
	</div>
	<h3 style="margin-bottom:5px">
	    Step 1: Files &amp; Database
	</h3>
	<hr size="1" />
	
	<!-- CHECKS: FAIL -->
	<?php if ( $total_req == 'Fail')  :	?>
	    
    	<div id="dup-step1-sys-req-btn" onclick="Duplicator.dlgSysChecks()">
    	    <div id="system-circle" class="circle-fail"></div> &nbsp; System Requirements: Fail...
    	</div><br/>
    		    
    	<i id="dup-step1-sys-req-msg">This installation will not be able to proceed until the system requirements pass. Please validate your system requirements by clicking on the button above. 
    	In order to get these values to pass please contact your server administrator, hosting provider or visit the online FAQ.</i><br/>
    		    
    	<div style="line-height:28px; font-size:14px; padding:0px 0px 0px 30px; font-weight:normal">
    	    <b>Helpful Resources:</b><br/>
    	    &raquo; <a href="http://lifeinthegrid.com/duplicator-faq" target="_blank">Common FAQs</a> <br/>
    	    &raquo; <a href="http://lifeinthegrid.com/duplicator-guide" target="_blank">User Guide</a> <br/>
    	    &raquo; <a href="http://lifeinthegrid.com/duplicator-hosts" target="_blank">Approved Hosts</a> <br/>
    	</div><br/>
	
	<!-- CHECKS: PASS -->
	<?php else : ?>	

	<div id="dup-step1-sys-req-btn" onclick="Duplicator.dlgSysChecks()">
    	    <div id="system-circle" class="circle-pass"></div>  &nbsp; System Requirements: Pass...<br/>
    	</div>
    	<div style='color:#999; font-size:11px; text-align:center; margin:3px 0px 0px 0px'><i>Package Name:<?php echo $zip_file_name; ?> </i></div><br/>
    		    
    	<div class="title-header">
    	    MySQL Server
    	</div>
    	<table class="table-inputs">
    	    <tr><td style="width:130px">Host</td><td><input type="text" name="dbhost" id="dbhost" value="<?php echo htmlspecialchars($GLOBALS['FW_DBHOST']); ?>" /></td></tr>
    	    <tr><td>User</td><td><input type="text" name="dbuser" id="dbuser" value="<?php echo htmlspecialchars($GLOBALS['FW_DBUSER']); ?>" /></td></tr>
    	    <tr><td>Password</td><td><input type="text" name="dbpass" id="dbpass" value="<?php echo htmlspecialchars($GLOBALS['FW_DBPASS']); ?>" /></td></tr>
    	</table>
    		    
    	<table class="table-inputs">
    	    <tr><td style="width:130px">Database Name</td><td><input type="text" name="dbname" id="dbname" value="<?php echo htmlspecialchars($GLOBALS['FW_DBNAME']); ?>" /></td></tr>
    	    <tr>
    		<td>Allow Options</td>
    		<td>
    		    <table class="dbtable-opts">
	    			<tr>
	    			    <td><input type="checkbox" name="dbmake" id="dbmake" checked="checked" value="1" /> <label for="dbmake">Database Creation</label></td>
	    			    <td><input type="checkbox" name="dbclean" id="dbclean" value="1" /> <label for="dbclean">Table Removal</label> </td>
	    			</tr>						
    		    </table>	
    		</td>
    	    </tr>
    	</table>
	<div style="margin:auto; text-align:center"><input id="dup-step1-dbconn-btn" type="button" onclick="Duplicator.dlgTestDB()" style="" value="Test Connection..." /></div>
    	<br/>
	
    		    
    	<!-- !!DO NOT CHANGE/EDIT OR REMOVE THIS SECTION!!
    	If your interested in Private Label Rights please contact us at the URL below to discuss
    	customizations to product labeling: http://lifeinthegrid.com/services/	-->
    	<a href="javascript:void(0)" onclick="$('#dup-step1-cpanel').toggle(250)"><b>Database Setup Help...</b></a>
    	<div id='dup-step1-cpanel' style="display:none">
    	    <div style="padding:10px 0px 0px 10px;line-height:22px">
    		<b>Need cPanel Database Help?</b> <br/>
    		&raquo; See the online <a href="http://lifeinthegrid.com/duplicator-tutorials" target="_blank">video tutorials &amp; guides</a> <br/>
    		&raquo; Need a host that supports cPanel?  See the Duplicator <a href="http://lifeinthegrid.com/duplicator-hosts" target="_blank">approved hosting</a> page.
    	    </div>
    	</div><br/><br/>
    		    
    	<a href="javascript:void(0)" onclick="$('#dup-step1-adv-opts').toggle(250)"><b>Advanced Options...</b></a>
    	<div id='dup-step1-adv-opts' style="display:none">
    	    <table class="table-inputs">
    		<tr><td colspan="2"><input type="checkbox" name="zip_manual"  id="zip_manual"   value="1" /> <label for="zip_manual">Manual package extraction</label></td></tr>
    		<tr><td colspan="2"><input type="checkbox" name="ssl_admin" id="ssl_admin" <?php echo ($GLOBALS['FW_SSL_ADMIN']) ? "checked='checked'" : ""; ?> /> <label for="ssl_admin">Enforce SSL on Admin</label></td></tr>
			<tr><td colspan="2"><input type="checkbox" name="ssl_login" id="ssl_login" <?php echo ($GLOBALS['FW_SSL_LOGIN']) ? "checked='checked'" : ""; ?> /> <label for="ssl_login">Enforce SSL on Login</label></td></tr>
			<tr><td colspan="2"><input type="checkbox" name="cache_wp" id="cache_wp" <?php echo ($GLOBALS['FW_CACHE_WP']) ? "checked='checked'" : ""; ?> /> <label for="cache_wp">Keep Cache Enabled</label></td></tr>
			<tr><td colspan="2"><input type="checkbox" name="cache_path" id="cache_path" <?php echo ($GLOBALS['FW_CACHE_PATH']) ? "checked='checked'" : ""; ?> /> <label for="cache_path">Keep Cache Home Path</label></td></tr>
    		<tr><td colspan="2"><input type="checkbox" name="dbnbsp" id="dbnbsp" value="1" /> <label for="dbnbsp">Fix non-breaking space characters</label></td></tr>
    		<tr><td style="width:130px">MySQL Charset</td><td><input type="text" name="dbcharset" id="dbcharset" value="<?php echo $_POST['dbcharset'] ?>" /> </td></tr>
    		<tr><td>MySQL Collation </td><td><input type="text" name="dbcollate" id="dbcollate" value="<?php echo $_POST['dbcollate'] ?>" /> </tr>
    	    </table>
    	</div>

	<!-- NOTICES  -->
    	<div class="warning-info" style="margin-top:50px">
    	    <b>WARNINGS &amp; NOTICES</b> 
    	    <p><b>Disclaimer:</b> This plugin has been heavily tested, however it does require above average technical knowledge. Please use it at your own risk and do not forget to back up your database and files beforehand. If you're not sure about how to use this tool then please enlist the guidance of a technical professional.</p>
    			    
    	    <p><b>Database:</b>  Do not attempt to connect to an existing database unless you are 100% sure you want to remove all of it's data. Connecting to a database that already exists will permanently DELETE all data in that database. This tool is designed to populate and fill a database with NEW data from a duplicated database using the SQL script in the package name above.</p>
    			    
    	    <p><b>Setup:</b> Only the package (zip file) and installer.php file should be in the install directory, unless you have manually extracted the package and checked the 'Manual Package Extraction' checkbox. All other files will be OVERWRITTEN during install.  Make sure you have full backups of all your databases and files before continuing with an installation.</p>
    			    
    	    <p><b>Manual Extraction:</b> Manual extraction requires that all contents in the package are extracted to the same directory as the installer.php file.  Manual extraction is only needed when your server does not support the ZipArchive extension.  Please see the online help for more details.</p>
    			    
    	    <p><b>After Install:</b>When you are done with the installation remove the installer.php, installer-data.sql and the installer-log.txt files from your directory. 
    		These files contain sensitive information and should not remain on a production system.</p><br/>
    	</div>
    		    
    	<div class="dup-step1-warning-area">
    	    <input id="accept-warnings"   type="checkbox" onclick="Duplicator.acceptWarning()" /> <label for="accept-warnings">I have read all warnings &amp; notices</label><br/>
    	</div><br/><br/><br/>
    		    
    	<div class="dup-footer-buttons">
    	    <input id="dup-step1-deploy-btn" type="button" value=" Run Deployment " onclick="Duplicator.runDeployment()" />
    	</div>				
	<?php endif; ?>	
</form>


<!-- =========================================
VIEW: STEP 1 - AJAX RESULT
Auto Posts to view.step2.php  -->
<form id='dup-step1-result-form' method="post" class="content-form" style="display:none">
	<input type="hidden" name="action_step" value="2" />
	<input type="hidden" name="package_name" value="<?php echo $zip_file_name ?>" />
	<!-- Set via jQuery -->
	<input type="hidden" name="logging" id="ajax-logging"  />	
	<input type="hidden" name="dbhost" id="ajax-dbhost" />
	<input type="hidden" name="dbuser" id="ajax-dbuser" />
	<input type="hidden" name="dbpass" id="ajax-dbpass" />
	<input type="hidden" name="dbname" id="ajax-dbname" />
	<input type="hidden" name="json"   id="ajax-json" />
	<input type="hidden" name="dbcharset" id="ajax-dbcharset" />
	<input type="hidden" name="dbcollate" id="ajax-dbcollate" />
	
    <div class="dup-logfile-link"><a href="installer-log.txt" target="_blank">installer-log.txt</a></div>
	<h3>Step 1: Files &amp; Database</h3>
	<hr size="1" />
	    
	<!--  PROGRESS BAR -->
	<div id="progress-area">
	    <div style="width:500px; margin:auto">
		<h3>Processing Files &amp; Database Please Wait...</h3>
		<div id="progress-bar"></div>
		<i>This may take several minutes</i>
	    </div>
	</div>
	    
	<!--  AJAX SYSTEM ERROR -->
	<div id="ajaxerr-area" style="display:none">
	    <p>Please try again an issue has occurred.</p>
	    <div style="padding: 0px 10px 10px 10px;">
		<div id="ajaxerr-data">An unknown issue has occurred with the file and database setup process.  Please see the installer-log.txt file for more details.</div>
		<i style='font-size:11px'>See online help for more details at <a href='http://lifeinthegrid.com/support' target='_blank'>support.lifeinthegrid.com</a></i>
	    </div>
	</div>
</form>


<!-- =========================================
DIALOG: SERVER CHECKS  -->
<div id="dup-step1-dialog" title="System Status" style="display:none">
	<div id="dup-step1-dialog-data" style="padding: 0px 10px 10px 10px;">
					
	    <!-- SYSTEM REQUIREMENTS -->
	    <b>REQUIREMENTS</b> &nbsp; <i style='font-size:11px'>click links for details</i>
	    <hr size="1"/>
	    <table style="width:100%">
		<tr>
		    <td style="width:300px"><a href="javascript:void(0)" onclick="$('#dup-SRV01').toggle(400)">Root Directory</a></td>
		    <td class="<?php echo ($req01 == 'Pass') ? 'dup-pass' : 'dup-fail' ?>"><?php echo $req01; ?></td>
		</tr>
		<tr>
		    <td colspan="2" id="dup-SRV01" class='dup-step1-dialog-data-details'>
			<?php
			echo "<i>Path: {$GLOBALS['CURRENT_ROOT_PATH']} </i><br/>";
			printf("<b>[%s]</b> %s <br/>", $req01a, "Is Writable");
			printf("<b>[%s]</b> %s <br/>", $req01b, "Contains only one zip file.<div style='padding-left:55px'>Result = {$zip_file_name} <br/> <i>Manual extraction still requires zip file</i> </div> ");
			?>
		    </td>
		</tr>
		<tr>
		    <td>Safe Mode Off</td>
		    <td class="<?php echo ($req02 == 'Pass') ? 'dup-pass' : 'dup-fail' ?>"><?php echo $req02; ?></td>
		</tr>
		<tr>
		    <td>MySQL Support</td>
		    <td class="<?php echo ($req03 == 'Pass') ? 'dup-pass' : 'dup-fail' ?>"><?php echo $req03; ?></td>
		</tr>
		<tr>
		    <td valign="top">
			PHP Version: <?php echo phpversion(); ?><br/>
			<i style="font-size:10px">(PHP 5.2.17+ is required)</i>
		    </td>
		    <td class="<?php echo ($req04 == 'Pass') ? 'dup-pass' : 'dup-fail' ?>"><?php echo $req04; ?> </td>
		</tr>
	    </table><br/>
		

	    <!-- SYSTEM CHECKS -->
	    <b>CHECKS</b><hr style='margin-top:-2px' size="1"/>
	    <table style="width:100%">
		<tr>
		    <td style="width:300px"></td>
		    <td></td>
		</tr>
		<tr>
		    <?php if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') !== false): ?>
    		    <td><b>Web Server:</b> Apache</td>
    		    <td><div class='dup-pass'>Good</div></td>
		    <?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false): ?> 
    		    <td><b>Web Server:</b> LiteSpeed</td>
    		    <td><div class='dup-ok'>OK</div></td>
		    <?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false): ?> 
    		    <td><b>Web Server:</b> Nginx</td>
    		    <td><div class='dup-ok'>OK</div></td>
		    <?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false): ?> 
    		    <td><b>Web Server:</b> Lighthttpd</td>
    		    <td><div class='dup-ok'>OK</div></td>
		    <?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') !== false): ?> 
    		    <td><b>Web Server:</b> Microsoft IIS</td>
    		    <td><div class='dup-ok'>OK</div></td>
		    <?php else: ?>
    		    <td><b>Web Server:</b> Not detected</td>
    		    <td><div class='dup-fail'>Caution</div></td>
		    <?php endif; ?>				
		</tr>
		<tr>
		    <?php
		    $open_basedir_set = ini_get("open_basedir");
		    if (empty($open_basedir_set)):
			?>
    		    <td><b>Open Base Dir:</b> Off
    		    <td><div class='dup-pass'>Good</div>
		    <?php else: ?>
    		    <td><b>Open Base Dir:</b> On</td>
    		    <td><div class='dup-fail'>Caution</div></td>
		    <?php endif; ?>
		</tr>
	    </table>
		    
	    <hr class='dup-dots' />
	    <!-- SAPI -->
	    <b>PHP SAPI:</b>  <?php echo php_sapi_name(); ?><br/>
	    <b>PHP ZIP Archive:</b> <?php echo class_exists('ZipArchive') ? 'Is Installed' : 'Not Installed'; ?> 
	</div>
</div>


<!-- =========================================
DIALOG: DB CONNECTION CHECK  -->
<div id="dup-step1-dialog-db" title="Test Database Connection" style="display:none">
    <div id="dup-step1-dialog-db-data" style="padding: 0px 10px 10px 10px;">		
	<div id="dbconn-test-msg" style="min-height:50px"></div>
	<br/><hr size="1" />
	<div class="help">
	    <b>Common Connection Issues:</b><br/>
	    - Double check case sensitive values 'User', 'Password' &amp; the 'Database Name' <br/>
	    - Validate the database and database user exist on this server <br/>
	    - Check if the database user has the correct permission levels to this database <br/>
	    - The host 'localhost' may not work on all hosting providers <br/>
	    - Contact your hosting provider for the exact required parameters <br/>
	    - See the 'Database Setup Help' section on step 1 for more details<br/>
	    - Visit the online resources 'Common FAQ page' <br/>
	</div>
    </div>
</div> <?php
		    break;
		    case "2" :
			?> <?php
	$dbh = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname']);

	$all_tables     = DupUtil::get_database_tables($dbh);
	$active_plugins = DupUtil::get_active_plugins($dbh);
	

	$old_path = $GLOBALS['FW_WPROOT'];
	$new_path = DupUtil::set_safe_path($GLOBALS['CURRENT_ROOT_PATH']);
	$new_path = ((strrpos($old_path, '/') + 1) == strlen($old_path)) ? DupUtil::add_slash($new_path) : $new_path; 
?>

<script type="text/javascript">
	/** **********************************************
	* METHOD:  
	* Timeout (10000000 = 166 minutes) */
	Duplicator.runUpdate = function() {
		
		//Validation
		var wp_username = $.trim($("#wp_username").val()).length || 0;
		var wp_password = $.trim($("#wp_password").val()).length || 0;
		
		if ( $.trim($("#url_new").val()) == "" )  {alert("The 'New URL' field is required!"); return false;}
		if ( $.trim($("#siteurl").val()) == "" )  {alert("The 'Site URL' field is required!"); return false;}
		if (wp_username >= 1 && wp_username < 4) {alert("The New Admin Account 'Username' must be four or more characters"); return false;}
		if (wp_username >= 4 && wp_password < 6) {alert("The New Admin Account 'Password' must be six or more characters"); return false;}

		$.ajax({
			type: "POST",
			timeout: 10000000,
			dataType: "json",			
			url: window.location.href,
			data: $('#dup-step2-input-form').serialize(),
			beforeSend: function() {
				Duplicator.showProgressBar();
				$('#dup-step2-input-form').hide();
				$('#dup-step2-result-form').show();
			},			
			success: function(data){ 
				if (typeof(data) != 'undefined' && data.step2.pass == 1) {
					$("#ajax-url_new").val($("#url_new").val());
					$("#ajax-json").val(escape(JSON.stringify(data)));
					setTimeout(function(){$('#dup-step2-result-form').submit();}, 100);
					$('#progress-area').fadeOut(1800);
				} else {
					Duplicator.hideProgressBar();
				}
			},
			error: function(xhr) { 
				var status = "<b>server code:</b> " + xhr.status + "<br/><b>status:</b> " + xhr.statusText + "<br/><b>response:</b> " +  xhr.responseText;
				$('#ajaxerr-data').html(status);
				Duplicator.hideProgressBar();
			}
		});
	};

	/** **********************************************
	* METHOD: Returns the windows active url */
	Duplicator.getNewURL = function(id) {
		var filename= window.location.pathname.split('/').pop() || 'installer.php' ;
		$("#" + id).val(window.location.href.replace(filename, ''));
	};
	
	/** **********************************************
	* METHOD: Allows user to edit the package url  */
	Duplicator.editOldURL = function() {
		var msg = 'This is the URL that was generated when the package was created.\n';
		msg += 'Changing this value may cause issues with the install process.\n\n';
		msg += 'Only modify  this value if you know exactly what the value should be.\n';
		msg += 'See "General Settings" in the WordPress Administrator for more details.\n\n';
		msg += 'Are you sure you want to continue?';
	
		if (confirm(msg)) {
			$("#url_old").removeAttr('readonly');
			$("#url_old").removeClass('readonly');
			$('#edit_url_old').hide('slow');
		}
	};
	
	/** **********************************************
	* METHOD: Allows user to edit the package path  */
	Duplicator.editOldPath = function() {
		var msg = 'This is the SERVER URL that was generated when the package was created.\n';
		msg += 'Changing this value may cause issues with the install process.\n\n';
		msg += 'Only modify  this value if you know exactly what the value should be.\n';
		msg += 'Are you sure you want to continue?';
	
		if (confirm(msg)) {
			$("#path_old").removeAttr('readonly');
			$("#path_old").removeClass('readonly');
			$('#edit_path_old').hide('slow');
		}
	};
	
	//DOCUMENT LOAD
	$(document).ready(function() {
		Duplicator.getNewURL('url_new');
		Duplicator.getNewURL('siteurl');
		
		$("#wp_password").passStrength({
				shortPass: 		"top_shortPass",
				badPass:		"top_badPass",
				goodPass:		"top_goodPass",
				strongPass:		"top_strongPass",
				baseStyle:		"top_testresult",
				userid:			"#wp_username",
				messageloc:		1	});
	});
</script>


<!-- =========================================
VIEW: STEP 2- INPUT -->
<form id='dup-step2-input-form' method="post" class="content-form">
	<input type="hidden" name="action_ajax"	 value="2" />
	<input type="hidden" name="action_step"	 value="2" />
	<input type="hidden" name="logging"		 value="<?php echo $_POST['logging'] ?>" />
	<input type="hidden" name="package_name" value="<?php echo $_POST['package_name'] ?>" />
	<input type="hidden" name="json"		 value="<?php echo $_POST['json']; ?>" />
	<input type="hidden" name="dbhost"		 value="<?php echo $_POST['dbhost'] ?>" />
	<input type="hidden" name="dbuser" 		 value="<?php echo $_POST['dbuser'] ?>" />
	<input type="hidden" name="dbpass" 		 value="<?php echo $_POST['dbpass'] ?>" />
	<input type="hidden" name="dbname" 		 value="<?php echo $_POST['dbname'] ?>" />
	<input type="hidden" name="dbcharset" 	 value="<?php echo $_POST['dbcharset'] ?>" />
	<input type="hidden" name="dbcollate" 	 value="<?php echo $_POST['dbcollate'] ?>" />
	
	
	<div class="dup-logfile-link"><a href="installer-log.txt" target="_blank">installer-log.txt</a></div>
	<h3>Step 2: Files &amp; Database</h3>
	<hr size="1" /><br />

	<div class="title-header">Old Settings</div>
	<table class="table-inputs">
		<tr valign="top">
			<td style="width:80px">URL</td>
			<td>
				<input type="text" name="url_old" id="url_old" value="<?php echo $GLOBALS['FW_URL_OLD'] ?>" readonly="readonly"  class="readonly" />
				<a href="javascript:Duplicator.editOldURL()" id="edit_url_old" style="font-size:12px">edit</a>		
			</td>
		</tr>
		<tr valign="top">
			<td>Path</td>
			<td>
				<input type="text" name="path_old" id="path_old" value="<?php echo $old_path ?>" readonly="readonly"  class="readonly" />
				<a href="javascript:Duplicator.editOldPath()" id="edit_path_old" style="font-size:12px">edit</a>		
			</td>
		</tr>
	</table>

	<div class="title-header" style="margin-top:8px">New Settings</div>
	<table class="table-inputs">		
		<tr>
			<td style="width:80px">URL</td>
			<td>
				<input type="text" name="url_new" id="url_new" value="<?php echo $GLOBALS['FW_URL_NEW'] ?>" />
				<a href="javascript:Duplicator.getNewURL('url_new')" style="font-size:12px">get</a>
			</td>
		</tr>
		<tr>
			<td>Path</td>
			<td><input type="text" name="path_new" id="path_new" value="<?php echo $new_path ?>" /></td>
		</tr>	
		<tr>
			<td>Title</td>
			<td><input type="text" name="blogname" id="blogname" value="<?php echo $GLOBALS['FW_BLOGNAME'] ?>" /></td>
		</tr>
	</table><br/>
	
	<!-- ==========================
    CREATE NEW USER -->
	<a href="javascript:void(0)" onclick="$('#dup-step2-user-opts').toggle(0)"><b>New Admin Account...</b></a>
	<div id='dup-step2-user-opts' style="display:none;">
	<table class="table-inputs" style="margin-top:7px">
		<tr><td colspan="2"><i style="color:gray;font-size: 11px">This feature is optional.  If the username already exists the account will NOT be created or updated.</i></td></tr>
		<tr>
			<td>Username </td>
			<td><input type="text" name="wp_username" id="wp_username" value="" title="4 characters minimum" placeholder="(4 or more characters)" /></td>
		</tr>	
		<tr>
			<td valign="top">Password</td>
			<td><input type="text" name="wp_password" id="wp_password" value="" title="6 characters minimum"  placeholder="(6 or more characters)" /></td>
		</tr>
	</table>
	</div><br/><br/>
	
	
	<!-- ==========================
    ADVANCED OPTIONS -->
	<a href="javascript:void(0)" onclick="$('#dup-step2-adv-opts').toggle(0)"><b>Advanced Options...</b></a>
	<div id='dup-step2-adv-opts' style="display:none;">
		<table style="width: 100%;">
			<tr>
				<td valign="top" style="width:80px">Site URL</td>
				<td>
					<input type="text" name="siteurl" id="siteurl" value="" />
					<a href="javascript:Duplicator.getNewURL('siteurl')" style="font-size:12px">get</a><br/>
				</td>
			</tr>
		</table><br/>
		

		<table>
			<tr>
				<td style="padding-right:10px">
					Scan Tables
					<div class="dup-step2-allnonelinks">
						<a href="javascript:void(0)" onclick="$('#tables option').prop('selected',true);">[All]</a> 
						<a href="javascript:void(0)" onclick="$('#tables option').prop('selected',false);">[None]</a>
					</div><br style="clear:both" />
					<select id="tables" name="tables[]" multiple="multiple" style="width:315px; height:100px">
						<?php
						foreach( $all_tables as $table ) {
							echo '<option selected="selected" value="' . DupUtil::esc_html_attr( $table ) . '">' . $table . '</option>';
						} 
						?>
					</select>

				</td>
				<td valign="top">
					Activate Plugins
					<div class="dup-step2-allnonelinks">
						<a href="javascript:void(0)" onclick="$('#plugins option').prop('selected',true);">[All]</a> 
						<a href="javascript:void(0)" onclick="$('#plugins option').prop('selected',false);">[None]</a>
					</div><br style="clear:both" />
					<select id="plugins" name="plugins[]" multiple="multiple" style="width:315px; height:100px">
						<?php
						foreach ($active_plugins as $plugin) {
							echo '<option selected="selected" value="' . DupUtil::esc_html_attr( $plugin ) . '">' . dirname($plugin) . '</option>';
						} 
						?>
					</select>
				</td>
			</tr>							
		</table><br/>
		
		<input type="checkbox" name="postguid" id="postguid" value="1" /> <label for="postguid">Keep Post GUID unchanged?</label><br/>
		<br/><br/><br/><br/>	
		
	</div>

	<div class="dup-footer-buttons">
		<input id="dup-step2-next" type="button" value=" Run Update " onclick="Duplicator.runUpdate()"  />
	</div>	
</form>


<!-- =========================================
VIEW: STEP 2 - AJAX RESULT  -->
<form id='dup-step2-result-form' method="post" class="content-form" style="display:none">
	<input type="hidden" name="action_step"  value="3" />
	<input type="hidden" name="package_name" value="<?php echo $_POST['package_name'] ?>" />
	<!-- Set via jQuery -->
	<input type="hidden" name="url_new" id="ajax-url_new"  />
	<input type="hidden" name="json"    id="ajax-json" />	
	
	<div class="dup-logfile-link"><a href="installer-log.txt" target="_blank">installer-log.txt</a></div>
	<h3>Step 2: Update Table Data</h3>
	<hr size="1" />
	
	<!--  PROGRESS BAR -->
	<div id="progress-area">
		<div style="width:500px; margin:auto">
			<h3>Processing Data Replacement Please Wait...</h3>
			<div id="progress-bar"></div>
			<i>This may take several minutes</i>
		</div>
	</div>
	
	<!--  AJAX SYSTEM ERROR -->
	<div id="ajaxerr-area" style="display:none">
		<p>Please try again an issue has occurred.</p>
		<div style="padding: 0px 10px 10px 10px;">
			<div id="ajaxerr-data">An unknown issue has occurred with the data replacement setup process.  Please see the installer-log.txt file for more details.</div>
			<i style='font-size:11px'>See online help for more details at <a href='http://lifeinthegrid.com/support' target='_blank'>support.lifeinthegrid.com</a></i>
		</div>
	</div>
</form>

 <?php
		    break;
		    case "3" :
			?> 
<script type="text/javascript">
	/** **********************************************
	* METHOD: Auto posts to admin page on success */	
	Duplicator.prepAdminPage = function() {
		var nurl = $('#url_new').val() + '/wp-admin/';
		$.ajax({type: "POST", url: nurl, success: function(data) {}	});
	};
	
	/** **********************************************
	* METHOD: Opens the tips dialog */	
	Duplicator.dlgTips = function() {
		$("#dup-step3-dialog").dialog({
			height:600, width:700, modal: true,
			position:['center', 150],
			buttons: {Close: function() {$(this).dialog( "close" );}}
		});	
	};
	
	/** **********************************************
	* METHOD: Posts to page to remove install files */	
	Duplicator.removeInstallerFiles = function(package) {
		var msg = "Delete all installer files now? \n\nThis will remove the page you are now viewing.\nThe page will stay active until you navigate away.";
		if (confirm(msg)) {
			var nurl = $('#url_new').val() + '/wp-content/plugins/duplicator/files/installer.cleanup.php?remove=1&package=' + package;
			window.open(nurl, "_blank");
		}
	};
	
	//DOCUMENT LOAD
	$(document).ready(function() {
		//Duplicator.prepAdminPage();	
	});
</script>


<!-- =========================================
VIEW: STEP 3- INPUT -->
<form id='dup-step3-input-form' method="post" class="content-form" style="line-height:20px">
	<input type="hidden" name="url_new" id="url_new" value="<?php echo rtrim($_POST['url_new'], "/"); ?>" />	
	<div class="dup-logfile-link"><a href="installer-log.txt" target="_blank">installer-log.txt</a></div>
	<h3>Step 3: Test Site</h3>
	<hr size="1" /><br />
	

	<div class="title-header">
		<div class="dup-step3-final-title">IMPORTANT FINAL STEPS!</div>
	</div>
		
	<table>
		<tr>
			<td style="width:170px">&raquo; <a href='<?php echo rtrim($_POST['url_new'], "/"); ?>/wp-admin/options-permalink.php' target='_blank'><b>Resave Permalinks</b></a> </td>
			<td><i style='font-size:11px'>This will update url rewrite items like the .htaccess file (requires login)</i></td>
		</tr>
		<tr>
			<td>&raquo; <a href="javascript:void(0)" onclick="Duplicator.removeInstallerFiles('<?php echo $_POST['package_name'] ?>')"><b>Delete Installer Files</b></a></td>
			<td><i style='font-size:11px'>Removes installer.php, installer-data.sql, installer-log.txt &amp; package (requires login)</i></td>
		</tr>
		<tr>
			<td>&raquo; <a href='<?php echo $_POST['url_new']; ?>' target='_blank'><b>Test Entire Site</b></a> </td>
			<td><i style='font-size:11px'>Validate all pages, links images and plugins</i></td>
		</tr>
		<tr>
			<td>&raquo; <a href="javascript:void(0)" onclick="$('#dup-step3-install-report').toggle(400)"><b>View Install Report</b></a></td>
			<td>
				<i style='font-size:11px; color:#BE2323'>
					<span data-bind="with: status.step1">Deploy Errors: <span data-bind="text: query_errs"></span></span> &nbsp; &nbsp;
					<span data-bind="with: status.step2">Update Errors: <span data-bind="text: err_all"></span></span> &nbsp; &nbsp;
					<span data-bind="with: status.step2">Warnings: <span data-bind="text: warn_all"></span></span>
				</i>
			</td>
		</tr>	
		<tr>
			<td colspan="2" style="text-align:center">
				<div style="border-bottom: 1px dotted #dfdfdf; border-top: 1px dotted #dfdfdf;">
					<i style='font-size:11px'>To re-install <a href="javascript:history.go(-2)">start over at step 1</a>.</i>
				</div>
			
			</td>
		</tr>
	</table><br/>


	<!-- ========================
	INSTALL REPORT -->
	<div id="dup-step3-install-report" style='display:none'>
		<table class='dup-step3-report-results' style="width:100%">
			<tr><th colspan="4">Database Results</th></tr>
			<tr style="font-weight:bold">
				<td style="width:150px"></td>
				<td>Tables</td>
				<td>Rows</td>
				<td>Cells</td>
			</tr>
			<tr data-bind="with: status.step1">
				<td>Created</td>
				<td><span data-bind="text: table_count"></span></td>
				<td><span data-bind="text: table_rows"></span></td>
				<td>n/a</td>
			</tr>	
			<tr data-bind="with: status.step2">
				<td>Scanned</td>
				<td><span data-bind="text: scan_tables"></span></td>        
				<td><span data-bind="text: scan_rows"></span></td>
				<td><span data-bind="text: scan_cells"></span></td>
			</tr>
			<tr data-bind="with: status.step2">
				<td>Updated</td>
				<td><span data-bind="text: updt_tables"></span></td>        
				<td><span data-bind="text: updt_rows"></span></td>
				<td><span data-bind="text: updt_cells"></span></td>
			</tr>
		</table>
		
		<table class='dup-step3-report-errs' style="width:100%; border-top:none">
			<tr><th colspan="4">Errors &amp; Warnings <br/> <i style="font-size:10px; font-weight:normal">(click links below to view details)</i></th></tr>
			<tr>
				<td data-bind="with: status.step1">
					<a href="javascript:void(0);" onclick="$('#dup-step3-errs-create').toggle(400)">Deploy Errors (<span data-bind="text: query_errs"></span>)</a><br/>
				</td>
				<td data-bind="with: status.step2">
					<a href="javascript:void(0);" onclick="$('#dup-step3-errs-upd').toggle(400)">Update Errors (<span data-bind="text: err_all"></span>)</a>
				</td>
				<td data-bind="with: status.step2">
					<a href="#dup-step2-errs-warn-anchor" onclick="$('#dup-step3-warnlist').toggle(400)">General Warnings (<span data-bind="text: warn_all"></span>)</a>
				</td>
			</tr>
			<tr><td colspan="4"><i style="font-size:10px; font-weight:normal">Notice: Your root .htaccess file was reset.  Resave all plugins that write to this file.</i></td></tr>
		</table>
		
		
		<div id="dup-step3-errs-create" class="dup-step3-err-msg">
		
			<b data-bind="with: status.step1">STEP 1: DEPLOY ERRORS (<span data-bind="text: query_errs"></span>)</b><br/>
			<div class="info">Queries that error during the deploy process are logged to the <a href="installer-log.txt" target="_blank">install-log.txt</a> file.  
			To view the error result look under the section titled 'DATABASE RESULTS'.  If errors are present they will be marked with '**ERROR**'. <br/><br/>  For errors titled
			'Query size limit' you will need to manually post the values or update your mysql server with the max_allowed_packet setting to handle larger payloads.
			If your on a hosted server you will need to contact the server admin, for more details see: https://dev.mysql.com/doc/refman/5.5/en/packet-too-large.html. <br/><br/>
			</div>
			
		</div>
		

		<div id="dup-step3-errs-upd" class="dup-step3-err-msg">
		
			<!-- MYSQL QUERY ERRORS -->
			<b data-bind="with: status.step2">STEP2: UPDATE ERRORS (<span data-bind="text: errsql_sum"></span>) </b><br/>
			<div class="info">Errors that show here are the result of queries that could not be performed.</div>
			<div class="content">
				<div data-bind="foreach: status.step2.errsql"><div data-bind="text: $data"></div></div>
				<div data-bind="visible: status.step2.errsql.length == 0">No MySQL query errors found</div>
			</div>
			
			<!-- TABLE KEY ERRORS -->
			<b data-bind="with: status.step2">TABLE KEY ERRORS (<span data-bind="text: errkey_sum"></span>)</b><br/>
			<div class="info">
				A primary key is required on a table to efficiently run the update engine. Below is a list of tables and the rows that will need to 
				be manually updated.  Use the query below to find the data.<br/>
				<i>SELECT @row := @row + 1 as row, t.* FROM some_table t, (SELECT @row := 0) r</i>
			</div>
			<div class="content">
				<div data-bind="foreach: status.step2.errkey"><div data-bind="text: $data"></div></div>
				<div data-bind="visible: status.step2.errkey.length == 0">No missing primary key errors</div>
			</div>
			
			<!-- SERIALIZE ERRORS -->
			<b data-bind="with: status.step2">SERIALIZATION ERRORS  (<span data-bind="text: errser_sum"></span>)</b><br/>
			<div class="info">
				Use the SQL below to display data that may have not been updated correctly during the serialization process.
			</div>
			<div class="content">
				<div data-bind="foreach: status.step2.errser"><div data-bind="text: $data"></div></div>
				<div data-bind="visible: status.step2.errser.length == 0">No serialization errors found</div>
			</div>			
			
		</div>
		
		
		<!-- WARNINGS-->
		<div id="dup-step3-warnlist" class="dup-step3-err-msg">
			<a href="#" id="dup-step2-errs-warn-anchor"></a>
			<b>GENERAL WARNINGS</b><br/>
			<div class="info">
				The following is a list of warnings that may need to be fixed in order to finalize your setup.
			</div>
			<div class="content">
				<div data-bind="foreach: status.step2.warnlist">
					 <div data-bind="text: $data"></div>
				</div>
				<div data-bind="visible: status.step2.warnlist.length == 0">
					No warnings found
				</div>
			</div>
		</div><br/>
		
		
	</div><br/><br/>



		
	<div class='dup-step3-connect'>
		Please consider <a href='http://lifeinthegrid.com/partner/' target='_blank'>Partnering or a Donation</a>! <br/>
		<a href="javascript:void(0)" onclick="Duplicator.dlgTips()">Troubleshoot</a> | 
		<a href='http://support.lifeinthegrid.com/knowledgebase.php' target='_blank'>FAQs</a> | 
		<a href='http://support.lifeinthegrid.com' target='_blank'>Support</a>
	</div><br/>
</form>


 <!-- =========================================
DIALOG: TROUBLSHOOTING DIALOG -->
<div id="dup-step3-dialog" title="Troubleshooting Tips" style="display:none">
	<div style="padding: 0px 10px 10px 10px;">		
		<b>Common Quick Fix Issues:</b>
		<ul>
			<li>Use an <a href='http://lifeinthegrid.com/duplicator-certified' target='_blank'>approved hosting provider</a></li>
			<li>Validate directory and file permissions (see below)</li>
			<li>Validate web server configuration file (see below)</li>
			<li>Clear your browsers cache</li>
			<li>Deactivate and reactivate all plugins</li>
			<li>Resave a plugins settings if it reports errors</li>
			<li>Make sure your root directory is empty</li>
		</ul>

		<b>Permissions:</b><br/> 
		Not all operating systems are alike.  Therefore, when you move a package (zip file) from one location to another the file and directory permissions may not always stick.  If this is the case then check your WordPress directories and make sure it's permissions are set to 755. For files make sure the permissions are set to 644 (this does not apply to windows servers).   Also pay attention to the owner/group attributes.  For a full overview of the correct file changes see the <a href='http://codex.wordpress.org/Hardening_WordPress#File_permissions' target='_blank'>WordPress permissions codex</a>
		<br/><br/>

		<b>Web server configuration files:</b><br/>
		For Apache web server the root .htaccess file was copied to .htaccess.orig. A new stripped down .htaccess file was created to help simplify access issues.  For IIS web server the web.config file was copied to web.config.orig, however no new web.config file was created.  If you have not altered this file manually then resaving your permalinks and resaving your plugins should resolve most all changes that were made to the root web configuration file.   If your still experiencing issues then open the .orig file and do a compare to see what changes need to be made. <br/><br/><b>Plugin Notes:</b><br/> It's impossible to know how all 3rd party plugins function.  The Duplicator attempts to fix the new install URL for settings stored in the WordPress options table.   Please validate that all plugins retained there settings after installing.   If you experience issues try to bulk deactivate all plugins then bulk reactivate them on your new duplicated site. If you run into issues were a plugin does not retain its data then try to resave the plugins settings.
		<br/><br/>
		 
		 <b>Cache Systems:</b><br/>
		 Any type of cache system such as Super Cache, W3 Cache, etc. should be emptied before you create a package.  Another alternative is to include the cache directory in the directory exclusion path list found in the options dialog. Including a directory such as \pathtowordpress\wp-content\w3tc\ (the w3 Total Cache directory) will exclude this directory from being packaged. In is highly recommended to always perform a cache empty when you first fire up your new site even if you excluded your cache directory.
		 <br/><br/>
		 
		 <b>Trying Again:</b><br/>
		 If you need to retry and reinstall this package you can easily run the process again by deleting all files except the installer.php and package file and then browse to the installer.php again.
		 <br/><br/>
		 
		 <b>Additional Notes:</b><br/>
		 If you have made changes to your PHP files directly this might have an impact on your duplicated site.  Be sure all changes made will correspond to the sites new location. 
		 Only the package (zip file) and the installer.php file should be in the directory where you are installing the site.  Please read through our knowledge base before submitting any issues. 
		 If you have a large log file that needs evaluated please email the file, or attach it to a help ticket.
		 <br/><br/>
		 
		 <b>Approved Hosts:</b><br/>
		 Please check out our <a href='http://lifeinthegrid.com/duplicator-certified' target='_blank'>approved hosts page</a> as it has a list of hosting providers and themes that have been tested
		 successfully with the Duplicator plugin.<br/><br/>
	</div>
</div>

<script type="text/javascript">
	MyViewModel = function() { this.status = <?php echo urldecode($_POST['json']); ?>;};
	ko.applyBindings(new MyViewModel());
</script>
 
  <?php
		    break;
		}
		?>
            </div>
        </div><br/>


        <!-- =========================================
        HELP FORM -->
        <div id="dup-main-help" title="Quick Help" style="display:none; font-size:12px">

            <div style="text-align:center">For in-depth help please see the <a href="http://lifeinthegrid.com/duplicator-docs" target="_blank">online resources</a></div>

            <h3>Step 1 - Deploy</h3>
            <div id="dup-help-step1" class="dup-help-page">
                <!-- MYSQL SERVER -->
                <fieldset>
                    <legend><b>MySQL Server</b></legend>

                    <b>Host:</b><br/>
                    The name of the host server that the database resides on.  Many times this will be localhost, however each hosting provider will have it's own naming convention please check with your server administrator.
                    <br/><br/>

                    <b>User:</b><br/>
                    The name of a MySQL database server user. This is special account that has privileges to access a database and can read from or write to that database.  <i style='font-size:11px'>This is <b>not</b> the same thing as your WordPress administrator account</i> 
                    <br/><br/>

                    <b>Password:</b><br/>
                    The password of the MySQL database server user.
                    <br/><br/>

                    <b>Database Name:</b><br/>
                    The name of the database to which this installation will connect and install the new tables onto.
                    <br/><br/>

                    <b>Database Creation:</b><br/>
                    If checked this option will try to create the database if it does not exist.  This option will not work on many hosting providers as they usually lock down the ability to create a database.  If the database does not exist then you will need to login to your control panel and create your database.  Please contact your server administrator for more details.
                    <br/><br/>

                    <b>Table Removal:</b><br/>
                    If checked this will automatically remove all tables in the database you are connecting to.  The Duplicator requires a blank database in order for an install to take place.  Please make sure you have backups of all your data before using an portion of the installer, as this option WILL remove data.
                    <br/>
                </fieldset>				

                <!-- ADVANCED OPTS -->
                <fieldset>
                    <legend><b>Advanced Options</b></legend>
                    <b>Manual Package Extraction:</b><br/>
                    This allows you to manually extract the zip archive on your own. This can be useful if your system does not have the ZipArchive support enabled.
                    <br/><br/>		

                    <b>Enforce SSL on Admin:</b><br/>
                    Turn off SSL support for WordPress. This sets FORCE_SSL_ADMIN in your wp-config file to false if true, otherwise it will create the setting if not set.
                    <br/><br/>	
					
                    <b>Enforce SSL on Login:</b><br/>
                    Turn off SSL support for WordPress Logins. This sets FORCE_SSL_LOGIN in your wp-config file to false if true, otherwise it will create the setting if not set.
                    <br/><br/>			
					
					<b>Keep Cache Enabled:</b><br/>
                    Turn off Cache support for WordPress. This sets WP_CACHE in your wp-config file to false if true, otherwise it will create the setting if not set.
                    <br/><br/>	
					
					<b>Keep Cache Home Path:</b><br/>
                    This sets WPCACHEHOME in your wp-config file to nothing if true, otherwise nothing is changed.
                    <br/><br/>	
					
                    <b>Fix non-breaking space characters:</b><br/>
                    The process will remove utf8 characters represented as 'xC2' 'xA0' and replace with a uniform space.  Use this option if you find strange question marks in you posts
                    <br/><br/>	

                    <b>MySQL Charset &amp; MySQL Collation:</b><br/>
                    When the database is populated from the SQL script it will use this value as part of its connection.  Only change this value if you know what your databases character set should be.
                    <br/>				
                </fieldset>			
            </div>

            <h3>Step 2 - Update</h3>
            <div id="dup-help-step1" class="dup-help-page">

                <!-- SETTINGS-->
                <fieldset>
                    <legend><b>Settings</b></legend>
                    <b>Old Settings:</b><br/>
                    The URL and Path settings are the original values that the package was created with.  These values should not be changed.
                    <br/><br/>

                    <b>New Settings:</b><br/>
                    These are the new values (URL, Path and Title) you can update for the new location at which your site will be installed at.
                    <br/>		
                </fieldset>
				
				<!-- NEW ADMIN ACCOUNT-->
                <fieldset>
                    <legend><b>New Admin Account</b></legend>
                    <b>Username:</b><br/>
                    The new username to create.  This will create a new WordPress administrator account.  Please note that usernames are not changeable from the within the UI.
                    <br/><br/>

                    <b>Password:</b><br/>
                    The new password for the user. 
                    <br/>		
                </fieldset>

                <!-- ADVANCED OPTS -->
                <fieldset>
                    <legend><b>Advanced Options</b></legend>
                    <b>Site URL:</b><br/>
                    For details see WordPress <a href="http://codex.wordpress.org/Changing_The_Site_URL" target="_blank">Site URL</a> &amp; <a href="http://codex.wordpress.org/Giving_WordPress_Its_Own_Directory" target="_blank">Alternate Directory</a>.  If you're not sure about this value then leave it the same as the new settings URL.
                    <br/><br/>

                    <b>Scan Tables:</b><br/>
                    Select the tables to be updated. This process will update all of the 'Old Settings' with the 'New Settings'. Hold down the 'ctrl key' to select/deselect multiple.
                    <br/><br/>

                    <b>Activate Plugins:</b><br/>
                    These plug-ins are the plug-ins that were activated when the package was created and represent the plug-ins that will be activated after the install.
                    <br/><br/>

                    <b>Post GUID:</b><br/>
                    If your moving a site keep this value checked. For more details see the <a href="http://codex.wordpress.org/Changing_The_Site_URL#Important_GUID_Note" target="_blank">notes on GUIDS</a>.	Changing values in the posts table GUID column can change RSS readers to evaluate that the posts are new and may show them in feeds again.		
                    <br/>		
                </fieldset>

            </div>

            <h3>Step 3 - Test</h3>
            <fieldset>
                <legend><b>Final Steps</b></legend>

                <b>Resave Permalinks</b><br/>
                Re-saving your perma-links will reconfigure your .htaccess file to match the correct path on your server.  This step requires logging back into the WordPress administrator.
                <br/><br/>

                <b>Delete Installer Files</b><br/>
                When you're completed with the installation please delete all installer files.  Leaving these files on your server can impose a security risk!
                <br/><br/>

                <b>Test Entire Site</b><br/>
                After the install is complete run through your entire site and test all pages and posts.
                <br/><br/>

                <b>View Install Report</b><br/>
                The install report is designed to give you a synopsis of the possible errors and warnings that may exist after the installation is completed.
                <br/>
            </fieldset>

        </div>


    </body>
</html>