<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

require_once (DUPLICATOR_PLUGIN_PATH . 'classes/utility.php');

/**
 * Class used to get server statis
 * @package Dupicator\classes
 */
class DUP_Server {
	
	/** 
	* Gets the system requirments which must pass to buld a package
	* @return array   An array of requirements
	*/
	public static function GetRequirments() {

		global $wpdb;

		$req = array();
		//SYS-100: FILE PERMS
		$test = is_writeable(DUPLICATOR_WPROOTPATH)
				&& is_writeable(DUPLICATOR_SSDIR_PATH)
				&& is_writeable(DUPLICATOR_SSDIR_PATH_TMP);
		$req['SYS-100'] = ($test) ? 'Pass' : 'Fail';

		//SYS-101 RESERVED FILE
		$req['SYS-101'] = (self::InstallerFilesFound()) ? 'Fail' : 'Pass';

		//SYS-102: ZIP-ARCHIVE
		$test = class_exists('ZipArchive');
		$req['SYS-102'] = ($test) ? 'Pass' : 'Fail';

		//SYS-103: SAFE MODE
		$test = (((strtolower(@ini_get('safe_mode'))   == 'on')   
				||  (strtolower(@ini_get('safe_mode')) == 'yes') 
				||  (strtolower(@ini_get('safe_mode')) == 'true') 
				||  (ini_get("safe_mode") == 1 )));
		$req['SYS-103'] = !($test) ? 'Pass' : 'Fail';

		//SYS-104: MYSQL SUPPORT
		$mysql_test1 = function_exists('mysqli_connect');
		$mysql_test2 = version_compare($wpdb->db_version(), '5.0', '>=');
		$req['SYS-104'] = ($mysql_test1 && $mysql_test2) ? 'Pass' : 'Fail';

		//SYS-105: PHP TESTS
		$php_test1 = version_compare(phpversion(), '5.2.17');
		$php_test2 =  function_exists("file_get_contents");
		$php_test3 =  function_exists("file_put_contents");
		$req['SYS-105'] = ($php_test1 >= 0 && $php_test2 && $php_test3) ? 'Pass' : 'Fail';



		//RESULTS
		$result = in_array('Fail', $req);
		$req['Success'] = !$result;

		return $req;
	}		
	
	/** 
	* Gets the system checks which are not required
	* @return array   An array of system checks
	*/
	public static function GetChecks() {
		$checks = array();

		//CHK-SRV-100: PHP SETTINGS
		$php_test1 = ini_get("open_basedir");
		$php_test1 = empty($php_test1) ? true : false;
		$php_test2 = ini_get("max_execution_time");
		$php_test2  = ($php_test2  > DUPLICATOR_SCAN_TIMEOUT || strcmp($php_test2 , 'Off') == 0 || $php_test2  == 0) ? 'Good' : 'Warn';
		$checks['CHK-SRV-100'] = ($php_test1 && $php_test2) ? 'Good' : 'Warn';

		//CHK-SRV-101: CACHE DIRECTORY
		$cache_path = DUP_Util::SafePath(WP_CONTENT_DIR) .  '/cache';
		$dirEmpty = DUP_Util::IsDirectoryEmpty($cache_path);
		$dirSize  = DUP_Util::GetDirectorySize($cache_path); //50K
		$checks['CHK-SRV-101'] = ($dirEmpty  || $dirSize < 50000 )	? 'Good' : 'Warn';

		//CHK-SRV-102: WEB SERVER 
		$servers = $GLOBALS['DUPLICATOR_SERVER_LIST'];
		$test = false;
		foreach ($servers as $value) {
			if (stristr($_SERVER['SERVER_SOFTWARE'], $value)) {
				$test = true;
				break;
			}
		}
		$checks['CHK-SRV-102'] = ($test) ? 'Good' : 'Warn';
		
		//RESULTS
		$result = in_array('Warn', $checks);
		$checks['Success'] = !$result;

		return $checks;
	}
	
	/** 
	* Check to see if duplicator installer files are present
	* @return bool   True if any reserved files are found
	*/
	public static function InstallerFilesFound() {
		
		$phpFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP);
		$sqlFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL);
		$logFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG);
		return  ($phpFile || $sqlFile || $logFile);
	}
	
}
?>