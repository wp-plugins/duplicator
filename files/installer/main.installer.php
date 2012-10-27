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

//DOWNLOAD ONLY: 
if ( isset($_GET['get']) && file_exists($_GET['file'])) {
	if (strstr($_GET['file'], '_installer.php')	|| strstr($_GET['file'], 'installer.rescue.php')) {
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=installer.php');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . filesize($_GET['file']));
		ob_clean();
		@flush();
		if (@readfile($_GET['file']) == false) {
			$data = file_get_contents($_GET['file']);
			if ($data == false) {
				die("Unable to read installer file.  The server currently has readfile and file_get_contents disabled on this server.  Please contact your server admin to remove this restriction");
			} else {
				print $data;
			}
		}
		exit;
	} else {
		header("HTML/1.1 404 Not Found", true, 404); 
		header("Status: 404 Not Found"); 
	}
} 

//Prevent Access from rovers or direct browsing in snapshop directory
if (file_exists('dtoken.php')) {
	exit;
}
?>

<?php if ( false ) :?>
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

$GLOBALS['FW_TABLEPREFIX'] 	= '%fwrite_wp_tableprefix%';
$GLOBALS['FW_URL_OLD'] 		= '%fwrite_url_old%';
$GLOBALS['FW_URL_NEW'] 		= '%fwrite_url_new%';
$GLOBALS['FW_PACKAGE_NAME'] = '%fwrite_package_name%';
$GLOBALS['FW_SECURE_NAME'] 	= '%fwrite_secure_name%';
$GLOBALS['FW_DBHOST'] 		= '%fwrite_dbhost%';
$GLOBALS['FW_DBNAME'] 		= '%fwrite_dbname%';
$GLOBALS['FW_DBUSER'] 		= '%fwrite_dbuser%';
$GLOBALS['FW_BLOGNAME'] 	= '%fwrite_blogname%';
$GLOBALS['FW_RESCUE_FLAG'] 	= '%fwrite_rescue_flag%';
$GLOBALS['FW_WPROOT'] 		= '%fwrite_wproot%';

//DATABASE SETUP: all time in seconds	
$GLOBALS['DB_MAX_TIME']    = 4000;
$GLOBALS['DB_MAX_PACKETS'] = 268435456;
ini_set('mysql.connect_timeout', '4000');

//PHP SETUP: all time in seconds
ini_set('memory_limit',		  '2048M');
ini_set("max_execution_time", '5000'); 
ini_set("max_input_time",	  '5000');
ini_set('default_socket_timeout', '5000');
set_time_limit(0);

$GLOBALS['DBCHARSET_DEFAULT'] = 'utf8';
$GLOBALS['DBCOLLATE_DEFAULT'] = 'utf8_general_ci';

//UPDATE TABLE SETTINGS
$GLOBALS['TABLES_SKIP_COLS'] = array('');
$GLOBALS['REPLACE_LIST'] =  array();


/* ================================================================================================
END ADVANCED FEATURES: Do not edit below here.
=================================================================================================== */

//CONSTANTS
define("DUPLICATOR_SSDIR_NAME", 	'wp-snapshots');  //This should match DUPLICATOR_SSDIR_NAME in duplicator.php

//GLOBALS
$GLOBALS['DUPLICATOR_INSTALLER_VERSION'] =  '0.4.0';
$GLOBALS["SQL_FILE_NAME"] 	= "installer-data.sql";
$GLOBALS["LOG_FILE_NAME"] 	= "installer-log.txt";
$GLOBALS['SEPERATOR1']		= str_repeat("********", 10);
$GLOBALS['LOGGING']			= isset($_POST['logging']) ? $_POST['logging'] : 1;

$GLOBALS['CURRENT_ROOT_PATH']	= dirname(__FILE__);
$GLOBALS['CHOWN_ROOT_PATH']	 	= @chmod("{$GLOBALS['CURRENT_ROOT_PATH']}", 0755);
$GLOBALS['CHOWN_LOG_PATH']  	= @chmod("{$GLOBALS['CURRENT_ROOT_PATH']}/{$GLOBALS['LOG_FILE_NAME']}", 0644);
$GLOBALS['URL_SSL']				= (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') ? true : false;
$GLOBALS['URL_PATH']			= ($GLOBALS['URL_SSL']) 
									? "https://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}" 
									: "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";	

//SHARED POST PARMS
$_POST['action_step']	= isset($_POST['action_step']) 	? $_POST['action_step'] 		: "0";
$_POST['dbhost']		= isset($_POST['dbhost'])	 	? trim($_POST['dbhost'])	 	: null;
$_POST['dbuser']		= isset($_POST['dbuser'])	 	? trim($_POST['dbuser'])	 	: null;
$_POST['dbpass']  		= isset($_POST['dbpass'])		? trim($_POST['dbpass'])		: null;
$_POST['dbname']		= isset($_POST['dbname'])	 	? trim($_POST['dbname'])	 	: null;
$_POST['dbcharset']		= isset($_POST['dbcharset'])	? trim($_POST['dbcharset'])	: $GLOBALS['DBCHARSET_DEFAULT'];
$_POST['dbcollate']		= isset($_POST['dbcollate'])	? trim($_POST['dbcollate'])	: $GLOBALS['DBCOLLATE_DEFAULT'];


//Restart log if user starts from step 1
if ($_POST['action_step'] == 1) {
	$GLOBALS['LOG_FILE_HANDLE']		= @fopen($GLOBALS['LOG_FILE_NAME'], "w+");
} else {
	$GLOBALS['LOG_FILE_HANDLE']		= @fopen($GLOBALS['LOG_FILE_NAME'], "a+");
}

?>

@@INC.UTILS.PHP@@

<?php
	if (isset($_POST['action_ajax'])) {
		switch ( $_POST['action_ajax'] ) {
			case "1" :?> @@AJAX.STEP1.PHP@@ <?php break; 
			case "2" :?> @@AJAX.STEP2.PHP@@ <?php break; 
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
	@@INC.STYLE.CSS@@
	@@INC.SCRIPTS.JS@@
</head>
<body>

	<div id="content">
		<!-- =========================================
		HEADER TEMPLATE: Common header on all steps -->
		<table cellspacing="0" class="header-wizard">
			<tr>
				<td style="width:100%;"><div style="font-size:19px; text-shadow:1px 1px 1px #777;"> &nbsp; Duplicator - Installer</div></td>
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
						<i style='font-size:11px; color:#999'>installer version: <?php echo $GLOBALS['DUPLICATOR_INSTALLER_VERSION'] . $GLOBALS['FW_RESCUE_FLAG'] ?></i>
					</div>
				</td>
			</tr>
		</table>	
	
		<!-- =========================================
		FORM DATA: Data Steps -->
		<div id="content-inner">
			<?php
				switch ( $_POST['action_step'] ) {
					case "0" :  ?> @@VIEW.STEP1.PHP@@ <?php break; 
					case "1" :  ?> @@VIEW.STEP1.PHP@@ <?php break; 
					case "2" :	?> @@VIEW.STEP2.PHP@@ <?php break; 
					case "3" :	?> @@VIEW.STEP3.PHP@@ <?php break; 
				}
			?>
		</div>
	</div><br/>
	
	
	<!-- =========================================
	HELP FORM -->
	<div style="display:none">
		<h2>Step 1 - Deploy</h2>
		
		<h2>Step 2 - Deploy</h2>
		<table>
			<tr>
				<td>Scan Tables</td>
				<td>Select the tables to be updated. This process will update all of the 'Old Settings' with the 'New Settings'. Hold down the 'ctrl key' to select/deselect multiple. </td>
			</tr>
			<tr>
				<td>Site URL</td>
				<td>For details see WordPress <a href="http://codex.wordpress.org/Changing_The_Site_URL" target="_blank">Site URL</a> &amp; <a href="http://codex.wordpress.org/Giving_WordPress_Its_Own_Directory" target="_blank">Alternate Directory</a>.</td>
			</tr>
			<tr>
				<td>Post GUID</td>
				<td>If your moving a site keep this value checked. For more details see the <a href="http://codex.wordpress.org/Changing_The_Site_URL#Important_GUID_Note" target="_blank">notes on GUIDS</a>.
			 Changing values in the posts table GUID column can change RSS readers to evaluate that the posts are new and may show them in feeds again</td>
			</tr>
		</table>
				
		<h2>Step 3 - Deploy</h2>
		
		
	
	</div>
	
	
</body>
</html>