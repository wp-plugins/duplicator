<?php
/*
  Copyright 2011-17  snapcreek.com

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 3, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  GPL v3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

if ( !defined('DUPXABSPATH') )
	define('DUPXABSPATH', dirname(__FILE__) . '/');

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
    	if (strlen($filename) && file_exists($filename) && (strstr($filename, '_installer.php'))) {
            //Attempt to push the file to the browser
    	    header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=installer.php');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filename));
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
            header("HTTP/1.1 404 Not Found", true, 404);
            header("Status: 404 Not Found");
        }
    }
	//Prevent Access from rovers or direct browsing in snapshop directory, or when
    //requesting to download a file, should not go past this point.
    exit;
}

/* ==============================================================================================
ADVANCED FEATURES - Allows admins to perform aditional logic on the import.

$GLOBALS['REPLACE_LIST']
	Add additional search and replace items to step 2 for the serialize engine.  
	Place directly below $GLOBALS['REPLACE_LIST'] variable below your items
	EXAMPLE:
		array_push($GLOBALS['REPLACE_LIST'], array('search' => 'https://oldurl/',  'replace' => 'https://newurl/'));
		array_push($GLOBALS['REPLACE_LIST'], array('search' => 'ftps://oldurl/',   'replace' => 'ftps://newurl/'));
  ================================================================================================= */

// Some machines don’t have this set so just do it here.
date_default_timezone_set('UTC');

//PATCH FOR IIS:  Does not support REQUEST_URI
if (!isset($_SERVER['REQUEST_URI']))  {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],0);
	if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != "") {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

//COMPARE VALUES
$GLOBALS['DUPX_DEBUG']			= false;
$GLOBALS['DUPX_DBPASS_CHECK']	= true;
$GLOBALS['FW_CREATED']			= '%fwrite_created%';
$GLOBALS['FW_VERSION_DUP']		= '%fwrite_version_dup%';
$GLOBALS['FW_VERSION_WP']		= '%fwrite_version_wp%';
$GLOBALS['FW_VERSION_DB']		= '%fwrite_version_db%';
$GLOBALS['FW_VERSION_PHP']		= '%fwrite_version_php%';
$GLOBALS['FW_VERSION_OS']		= '%fwrite_version_os%';
//GENERAL
$GLOBALS['FW_TABLEPREFIX']		= '%fwrite_wp_tableprefix%';
$GLOBALS['FW_URL_OLD']			= '%fwrite_url_old%';
$GLOBALS['FW_PACKAGE_NAME']		= '%fwrite_archive_name%';
$GLOBALS['FW_PACKAGE_NOTES']	= '%fwrite_package_notes%';
$GLOBALS['FW_PACKAGE_EST_SIZE']	= '%fwrite_package_size%';
$GLOBALS['FW_SECURE_NAME']		= '%fwrite_secure_name%';
$GLOBALS['FW_DBHOST']			= '%fwrite_dbhost%';
$GLOBALS['FW_DBHOST']			= empty($GLOBALS['FW_DBHOST']) ? 'localhost' : $GLOBALS['FW_DBHOST'];
$GLOBALS['FW_DBPORT']			= '%fwrite_dbport%';
$GLOBALS['FW_DBPORT']			= empty($GLOBALS['FW_DBPORT']) ? 3306 : $GLOBALS['FW_DBPORT'];
$GLOBALS['FW_DBNAME']			= '%fwrite_dbname%';
$GLOBALS['FW_DBUSER']			= '%fwrite_dbuser%';
$GLOBALS['FW_DBPASS']			= '%fwrite_dbpass%';
$GLOBALS['FW_SECUREON']			= '%fwrite_secureon%';
$GLOBALS['FW_SECUREPASS']		= '%fwrite_securepass%';
$GLOBALS['FW_BLOGNAME']			= '%fwrite_blogname%';
$GLOBALS['FW_WPROOT']			= '%fwrite_wproot%';
$GLOBALS['FW_WPLOGIN_URL']		= '%fwrite_wplogin_url%';
$GLOBALS['FW_OPTS_DELETE']		= json_decode("%fwrite_opts_delete%", true);
$GLOBALS['FW_DUPLICATOR_VERSION'] = '%fwrite_duplicator_version%';
$GLOBALS['FW_ARCHIVE_ONLYDB']	= '%fwrite_archive_onlydb%';
$GLOBALS['PACKAGE_HASH']		= '%package_hash%';

//DATABASE SETUP: all time in seconds	
$GLOBALS['DB_MAX_TIME']		= 5000;
$GLOBALS['DB_MAX_PACKETS']	= 268435456;
$GLOBALS['DB_FCGI_FLUSH']	= false;
ini_set('mysql.connect_timeout', '5000');

//PHP SETUP: all time in seconds
ini_set('memory_limit', '2048M');
ini_set("max_execution_time", '5000');
ini_set("max_input_time", '5000');
ini_set('default_socket_timeout', '5000');
@set_time_limit(0);

$GLOBALS['DBCHARSET_DEFAULT'] = 'utf8';
$GLOBALS['DBCOLLATE_DEFAULT'] = 'utf8_general_ci';
$GLOBALS['FAQ_URL'] = 'https://snapcreek.com/duplicator/docs/faqs-tech';
$GLOBALS['NOW_DATE'] = @date("Y-m-d-H:i:s");
$GLOBALS['DB_RENAME_PREFIX'] = 'x-bak__';

//UPDATE TABLE SETTINGS
$GLOBALS['REPLACE_LIST'] = array();


/** ================================================================================================
  END ADVANCED FEATURES: Do not edit below here.
  =================================================================================================== */

//CONSTANTS
define("DUPLICATOR_INIT", 1); 
define("DUPLICATOR_SSDIR_NAME", 'wp-snapshots');  //This should match DUPLICATOR_SSDIR_NAME in duplicator.php

//SHARED POST PARMS
$_POST['action_step'] = isset($_POST['action_step']) ? DUPX_U::sanitize_text_field($_POST['action_step']) : "0";
$_POST['secure-pass'] = isset($_POST['secure-pass']) ? DUPX_U::sanitize_text_field($_POST['secure-pass']) : '';

if ($GLOBALS['FW_SECUREON']) {
	$pass_hasher = new DUPX_PasswordHash(8, FALSE);
	$post_secure_pass = DUPX_U::sanitize_text_field($_POST['secure-pass']);
	$pass_check  = $pass_hasher->CheckPassword(base64_encode($post_secure_pass), $GLOBALS['FW_SECUREPASS']);
	if (! $pass_check) {
		$_POST['action_step'] = 0;
	}
}

/** Host has several combinations :
localhost | localhost:55 | localhost: | http://localhost | http://localhost:55 */
if (isset($_POST['dbhost'])) {
	$post_db_host = DUPX_U::sanitize_text_field($_POST['dbhost']);
	$_POST['dbhost'] = DUPX_U::sanitize_text_field($post_db_host);
} else {
	$_POST['dbhost'] = null;
}

if (isset($_POST['dbport'])) {
	$post_db_port = DUPX_U::sanitize_text_field($_POST['dbport']);
	$_POST['dbport'] = trim($post_db_port);
} else {
	$_POST['dbport'] = 3306;
}

$_POST['dbuser'] = isset($_POST['dbuser']) ? DUPX_U::sanitize_text_field($_POST['dbuser']) : null;

if (isset($_POST['dbpass'])) {
	$post_db_pass = DUPX_U::sanitize_text_field($_POST['dbpass']);
	$_POST['dbpass'] = trim($post_db_pass);
} else {
	$_POST['dbpass'] = null;
}


if (isset($_POST['dbname'])) {
	$post_db_name = DUPX_U::sanitize_text_field($_POST['dbname']);
	$_POST['dbname'] = trim($post_db_name);
} else {
	$_POST['dbname'] = null;
}

if (isset($_POST['dbcharset'])) {
	$post_db_charset = DUPX_U::sanitize_text_field($_POST['dbcharset']);
	$_POST['dbcharset'] = trim($post_db_charset);
} else {
	$_POST['dbcharset'] = $GLOBALS['DBCHARSET_DEFAULT'];
}

if (isset($_POST['dbcollate'])) {
	$post_db_collate = DUPX_U::sanitize_text_field($_POST['dbcollate']);
	$_POST['dbcollate'] = trim($post_db_collate);
} else {
	$_POST['dbcollate'] = $GLOBALS['DBCOLLATE_DEFAULT'];
}

//GLOBALS
// Constants which are dependent on the $GLOBALS['DUPX_AC']
$GLOBALS['SQL_FILE_NAME'] = "dup-installer-data__{$GLOBALS['PACKAGE_HASH']}.sql";
$GLOBALS['LOG_FILE_NAME']       = "dup-installer-log__{$GLOBALS['PACKAGE_HASH']}.txt";
$GLOBALS['LOGGING']             = isset($_POST['logging']) ? DUPX_U::sanitize_text_field($_POST['logging']) : 1;
$GLOBALS['CURRENT_ROOT_PATH']   = dirname(__FILE__);
$GLOBALS['CHOWN_ROOT_PATH']     = @chmod("{$GLOBALS['CURRENT_ROOT_PATH']}", 0755);
$GLOBALS['CHOWN_LOG_PATH']      = @chmod("{$GLOBALS['CURRENT_ROOT_PATH']}/{$GLOBALS['LOG_FILE_NAME']}", 0644);
$GLOBALS['URL_SSL']             = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') ? true : false;
$GLOBALS['URL_PATH']            = ($GLOBALS['URL_SSL']) ? "https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}" : "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
$GLOBALS['PHP_MEMORY_LIMIT']    = ini_get('memory_limit') === false ? 'n/a' : ini_get('memory_limit');
$GLOBALS['PHP_SUHOSIN_ON']      = extension_loaded('suhosin') ? 'enabled' : 'disabled';
$GLOBALS['ARCHIVE_PATH']        = $GLOBALS['CURRENT_ROOT_PATH'] . '/' . $GLOBALS['FW_PACKAGE_NAME'];
$GLOBALS['ARCHIVE_PATH']        = str_replace("\\", "/", $GLOBALS['ARCHIVE_PATH']);
if (isset($_GET["view"])) {
	$GLOBALS["VIEW"] = $_GET["view"];
} elseif (!empty($_POST["view"])) {
	$GLOBALS["VIEW"] = $_POST["view"];
} else {
	$GLOBALS["VIEW"] = 'step1';
}

//Restart log if user starts from step 1
if ($_POST['action_step'] == 1 && ! isset($_GET['help'])) {
    $GLOBALS['LOG_FILE_HANDLE'] = @fopen($GLOBALS['LOG_FILE_NAME'], "w+");
} else {
    $GLOBALS['LOG_FILE_HANDLE'] = @fopen($GLOBALS['LOG_FILE_NAME'], "a+");
}
?>
@@CLASS.U.PHP@@
@@CLASS.CSRF.PHP@@
@@CLASS.SERVER.PHP@@
@@CLASS.DB.PHP@@
@@CLASS.LOGGING.PHP@@
@@CLASS.ENGINE.PHP@@
@@CLASS.CONF.WP.PHP@@
@@CLASS.CONF.SRV.PHP@@
@@CLASS.HTTP.PHP@@
@@CLASS.PASSWORD.PHP@@
<?php
// CSRF checking
if (!empty($GLOBAL['view'])) {
	$csrf_views = array(
		'secure',
		'step1',
		'step2',
		'step3',
		'step4',
	);
	if (in_array($GLOBAL['view'], $csrf_views)) {
        if (!DUPX_CSRF::check($_POST['csrf_token'], $GLOBAL['view'])) {
			die('CSRF security issue for the view: '.$GLOBAL['view']);
        }
	}
}

if (isset($_POST['action_ajax'])) :

	if ($GLOBALS['FW_SECUREON']) {
		$pass_hasher = new DUPX_PasswordHash(8, FALSE);
		$pass_check  = $pass_hasher->CheckPassword(base64_encode($_POST['secure-pass']), $GLOBALS['FW_SECUREPASS']);
		if (! $pass_check) {
			die("Unauthorized Access:  Please provide a password!");
		}
	}

	//Alternative control switch structer will not work in this case
	//see: http://php.net/manual/en/control-structures.alternative-syntax.php
	//Some clients will create double spaces such as the FTP client which
	//will break example found online
	switch ($_POST['action_ajax']) :

		case "1": ?>@@CTRL.STEP1.PHP@@<?php break;

		case "2": ?>@@CTRL.STEP2.PHP@@<?php break;

		case "3": ?>@@CTRL.STEP3.PHP@@<?php break;

	endswitch;

    @fclose($GLOBALS["LOG_FILE_HANDLE"]);
    die("");

endif;
?>
	
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex,nofollow">
	<title>Duplicator</title>
	@@INC.LIBS.CSS.PHP@@	
	@@INC.CSS.PHP@@	
	@@INC.LIBS.JS.PHP@@
	@@INC.JS.PHP@@
</head>
<body>

<div id="content">
<!-- =========================================
HEADER TEMPLATE: Common header on all steps -->
<table cellspacing="0" class="dupx-header">
    <tr>
        <td style="width:100%;">
            <div style="font-size:26px; padding:7px 0 7px 0">
                <!-- !!DO NOT CHANGE/EDIT OR REMOVE PRODUCT NAME!!
                If your interested in Private Label Rights please contact us at the URL below to discuss
                customizations to product labeling: http://snapcreek.com	-->
                &nbsp; Duplicator
            </div>
        </td>
        <td class="dupx-header-version">
            <a href="javascript:void(0)" onclick="DUPX.showServerInfo()">version: <?php echo DUPX_U::esc_html($GLOBALS['FW_DUPLICATOR_VERSION']); ?></a><br/>
			<a href="?help=1" target="_blank">help</a>
			<?php
				echo ' &raquo; <a href="?help=1#secure" target="_blank">';
				echo ($GLOBALS['FW_SECUREON']) ? 'locked</a>' : '<i class="secure-unlocked">unlocked</i></a>';
			?>
        </td>
    </tr>
</table>

<div style="position: relative">
	<div class="installer-mode">
		<?php
			echo 'Mode: ';
			echo ($GLOBALS['FW_ARCHIVE_ONLYDB']) ? 'Database Only' : 'Standard';
		?>
	</div>
</div>

<!-- =========================================
FORM DATA: Data Steps -->
<div id="content-inner">
<?php

if (! isset($_GET['help'])) {
switch ($_POST['action_step']) {
	case "0" :
	?> @@VIEW.INIT1.PHP@@ <?php
	break;
	case "1" :
	?> @@VIEW.STEP1.PHP@@ <?php
	break;
	case "2" :
	?> @@VIEW.STEP2.PHP@@ <?php
	break;
	case "3" :
	?> @@VIEW.STEP3.PHP@@ <?php
	break;
	case "4" :
	?> @@VIEW.STEP4.PHP@@ <?php
	break;
}
} else {
	?> @@VIEW.HELP.PHP@@ <?php
}
	
?>
</div>
</div><br/>


<!-- CONFIRM DIALOG -->
<div id="dialog-server-info" style="display:none">
	<!-- DETAILS -->
	<div class="dlg-serv-info">
		<?php
			$ini_path 		= php_ini_loaded_file();
			$ini_max_time 	= ini_get('max_execution_time');
			$ini_memory 	= ini_get('memory_limit');
		?>
         <div class="hdr">Current Server</div>
		<label>Web Server:</label>  			<?php echo DUPX_U::esc_html($_SERVER['SERVER_SOFTWARE']); ?><br/>
		<label>Operating System:</label>        <?php echo DUPX_U::esc_html(PHP_OS); ?><br/>
        <label>PHP Version:</label>  			<?php echo DUPX_U::esc_html(DUPX_Server::$php_version); ?><br/>
		<label>PHP INI Path:</label> 			<?php echo empty($ini_path ) ? 'Unable to detect loaded php.ini file' : $ini_path; ?>	<br/>
		<label>PHP SAPI:</label>  				<?php echo DUPX_U::esc_html(php_sapi_name()); ?><br/>
		<label>PHP ZIP Archive:</label> 		<?php echo class_exists('ZipArchive') ? 'Is Installed' : 'Not Installed'; ?> <br/>
		<label>PHP max_execution_time:</label>  <?php echo $ini_max_time === false ? 'unable to find' : DUPX_U::esc_html($ini_max_time); ?><br/>
		<label>PHP memory_limit:</label>  		<?php echo empty($ini_memory)      ? 'unable to find' : DUPX_U::esc_html($ini_memory); ?><br/>

        <br/>
        <div class="hdr">Package Server</div>
		<div class="info-txt">The server where the package was created</div>
        <label>Plugin Version:</label>  		<?php echo DUPX_U::esc_html($GLOBALS['FW_VERSION_DUP']); ?><br/>
        <label>WordPress Version:</label>  		<?php echo DUPX_U::esc_html($GLOBALS['FW_VERSION_WP']); ?><br/>
        <label>PHP Version:</label>             <?php echo DUPX_U::esc_html($GLOBALS['FW_VERSION_PHP']); ?><br/>
        <label>Database Version:</label>        <?php echo DUPX_U::esc_html($GLOBALS['FW_VERSION_DB']); ?><br/>
        <label>Operating System:</label>        <?php echo DUPX_U::esc_html($GLOBALS['FW_VERSION_OS']); ?><br/>
		<br/><br/>
	</div>
</div>

<script>
/* Server Info Dialog*/
DUPX.showServerInfo = function()
{
	modal({
		type: 'alert',
		title: 'Server Information',
		text: $('#dialog-server-info').html()
	});
}
</script>

</body>
</html>