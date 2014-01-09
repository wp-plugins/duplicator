<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.archive.php');
require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.installer.php');
require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.database.php');
require_once (DUPLICATOR_PLUGIN_PATH . 'classes/utility.php');

final class DUP_PackageStatus {
   private function __construct() {}
   const START		= 10;
   const DBSTART	= 20;
   const DBDONE		= 30;
   const ARCSTART	= 40;
   const ARCDONE	= 50;
   const COMPLETE	= 100;
}

final class DUP_PackageType {
   private function __construct() {}
   const MANUAL		= 0;
   const SCHEDULED	= 1;
}

/**
 * Class used to store and process all Package logic
 * @package Dupicator\classes
 */
class DUP_Package {
	
	const OPT_ACTIVE   = 'duplicator_package_active';
	
	//Properties
	public $ID;
	public $Name;
	public $Hash;
	public $NameHash;
	public $Version;
	public $Type;
	public $Notes;
	public $StorePath;
	public $StoreURL;
	public $Runtime;
	public $ExeSize;
	public $ZipSize;
	//Objects
	public $Archive;
	public $Installer;
	public $Database;

	 /**
     *  Manages the Package Process
     */
    function __construct() {
		
		$name = date('Ymd') . '_' . sanitize_title(get_bloginfo( 'name', 'display' ));
		$name = substr(str_replace('-', '', sanitize_file_name($name)), 0 , 40);

		$this->ID			= null;
		$this->Version		= DUPLICATOR_VERSION;
		$this->Type			= DUP_PackageType::MANUAL;
		$this->Name			= $name;
		$this->Notes		= null;
		$this->StoreURL     = DUP_Util::SSDirURL();
		$this->StorePath    = DUPLICATOR_SSDIR_PATH_TMP;
		$this->Database		= new DUP_Database($this);
		$this->Archive		= new DUP_Archive($this);
		$this->Installer	= new DUP_Installer($this);
	}
	
	
	/**
	 * Starts the package build process
	 * @return DUP_Package
	 */
	public function Build() {
		
		global $wp_version;
		global $wpdb;
		global $current_user;

		$timerStart = DUP_Util::GetMicrotime();

		$this->Hash				  = uniqid() . mt_rand(1000, 9999) . date("ymdHis");
		$this->NameHash			  = "{$this->Name}_{$this->Hash}";
		$this->Archive->File	  = "{$this->NameHash}_archive.zip";
		$this->Installer->File    = "{$this->NameHash}_installer.php";
		$this->Database->File     = "{$this->NameHash}_database.sql";
		
		//START LOGGING
		DUP_Log::Open($this->NameHash);
		$php_max_time	= @ini_get("max_execution_time");
		$php_max_memory = @ini_set('memory_limit', DUPLICATOR_PHP_MAX_MEMORY);
		$php_max_time	= ($php_max_time == 0)        ? "(0) no time limit imposed" : "[{$php_max_time}] not allowed";
		$php_max_memory = ($php_max_memory === false) ? "Unabled to set php memory_limit" :  DUPLICATOR_PHP_MAX_MEMORY . " ({$php_max_memory} default)";
		
		$info  = "********************************************************************************\n";
		$info .= "PACKAGE-LOG: " . @date("Y-m-d H:i:s") . "\n";
		$info .= "NOTICE: Do NOT post to public sites or forums \n";
		$info .= "********************************************************************************\n";
		$info .= "VERSION:\t" . DUPLICATOR_VERSION . "\n";
		$info .= "WORDPRESS:\t{$wp_version}\n";
		$info .= "PHP INFO:\t" . phpversion() . ' | ' . 'SAPI: ' . php_sapi_name() . "\n";
		$info .= "SERVER:\t\t{$_SERVER['SERVER_SOFTWARE']} \n";
		$info .= "PHP TIME LIMIT: {$php_max_time} \n";
		$info .= "PHP MAX MEMORY: {$php_max_memory}";
		DUP_Log::Info($info);
		unset($info);
		
		//CREATE DB RECORD
		$packageObj = serialize($this);
		if (! $packageObj) {
			DUP_Log::Error("Unable to serialize pacakge object while building record.");
		}
		
		$results = $wpdb->insert($wpdb->prefix . "duplicator_packages", array(
			'name'    => $this->Name,
			'hash'	  => $this->Hash,
			'status'  => DUP_PackageStatus::START,
			'created' => current_time('mysql', get_option('gmt_offset', 1)),
			'owner'	  => isset($current_user->user_login) ? $current_user->user_login : 'unknown',
			'package' => $packageObj)
		);

		if ($results == false) {
			$error_result = $wpdb->print_error();
			DUP_Log::Error("Unable to insert record into database table.", "'{$error_result}'");
		}
		$this->ID = $wpdb->insert_id;
		
		//START BUILD
		$this->Database->Build();
		$this->Archive->Build();
		$this->Installer->Build();

		//VALIDATE FILE SIZE
		$dbSizeRead	 = DUP_Util::ByteSize($this->Database->Size);
		$zipSizeRead = DUP_Util::ByteSize($this->Archive->Size);
		$exeSizeRead = DUP_Util::ByteSize($this->Installer->Size);
		if ( !($this->Archive->Size && $this->Database->Size && $this->Installer->Size)) {
			DUP_Log::Error("A required file contains zero bytes.", "Archive Size: {$zipSizeRead} | SQL Size: {$dbSizeRead} | Installer Size: {$exeSizeRead}");
		}

		$this->SetStatus(DUP_PackageStatus::COMPLETE);
		$timerEnd = DUP_Util::GetMicrotime();
		$timerSum = DUP_Util::ElapsedTime($timerEnd, $timerStart);
		
		$this->Runtime  = $timerSum;
		$this->ExeSize  = $exeSizeRead;
		$this->ZipSize  = $zipSizeRead;
		
		$info  = "\n********************************************************************************\n";
		$info .= "RECORD ID:[{$this->ID}]\n";
		$info .= "FILE SIZE: Archive:{$zipSizeRead} | SQL:{$dbSizeRead} | Installer:{$exeSizeRead}\n";
		$info .= "TOTAL PROCESS RUNTIME: {$timerSum}\n";
		$info .= "DONE PROCESSING => {$this->Name} " . @date("Y-m-d H:i:s") . "\n";
		$info .= "********************************************************************************\n";
		DUP_Log::Info($info);
		DUP_Log::Close();
		
		return $this;
	}
	
	/**
	 * Gets the active package.  The active package is defined as the package that was lasted saved.
	 * @see DUP_Package::SaveActive
	 * @return DUP_Package
	 */
	public function GetActive() {
		
		$tmpOpts = get_option(self::OPT_ACTIVE, false);	
		if ($tmpOpts != false) {
			return  @unserialize($tmpOpts);
		} else {
			return $this;
		}
	}
	
	/**
	 *  Saves the active options associted with the active(latest) package.
	 *  @param $_POST $post The Post server object
	 *  @see DUP_Package::GetActive
	 *  @return void */
	public function SaveActive($post = null) {

		if (isset($post)) {
			$post = stripslashes_deep($post);
			$name = ( isset($post['package-name']) && ! empty($post['package-name']))
				? $post['package-name'] 
				: date('Ymd') . '_' . sanitize_title(get_bloginfo( 'name', 'display' ));
			
			$name          = substr(str_replace('-', '', sanitize_file_name($name)), 0 , 40);
			$filter_dirs   = isset($post['filter-dirs']) ? $this->parseDirectoryFilter($post['filter-dirs']) : '';
			$filter_exts   = isset($post['filter-exts']) ? $this->parseExtensionFilter($post['filter-exts']) : '';
			$tablelist     = isset($post['dbtables'])    ? implode(',', $post['dbtables']) : '';

			//PACKAGE
			$this->Version		= DUPLICATOR_VERSION;
			$this->Name			= $name;
			$this->Notes		= esc_html($post['package-notes']);
			//ARCHIVE
			$this->Archive->PackDir			= rtrim(DUPLICATOR_WPROOTPATH, '/');
			$this->Archive->Format			= 'ZIP';
			$this->Archive->FilterOn		= isset($post['filter-on'])   ? 1 : 0;
			$this->Archive->FilterDirs		= esc_html($filter_dirs);
			$this->Archive->FilterExts		= esc_html($filter_exts);
			
			//INSTALLER
			$this->Installer->OptsDBHost		= esc_html($post['dbhost']);
			$this->Installer->OptsDBName		= esc_html($post['dbname']);
			$this->Installer->OptsDBUser		= esc_html($post['dbuser']);
			$this->Installer->OptsSSLAdmin		= isset($post['ssl-admin'])		? 1 : 0;
			$this->Installer->OptsSSLLogin		= isset($post['ssl-login'])		? 1 : 0;
			$this->Installer->OptsCacheWP		= isset($post['cache-wp'])		? 1 : 0;
			$this->Installer->OptsCachePath		= isset($post['cache-path'])	? 1 : 0;
			$this->Installer->OptsURLNew		= esc_html($post['url-new']);
			//DATABASE
			$this->Database->FilterOn		= isset($post['dbfilter-on'])   ? 1 : 0;
			$this->Database->FilterTables	= esc_html($tablelist);

			update_option(self::OPT_ACTIVE, serialize($this));
		}
	}
	

	
	public function SetStatus($status) {
		global $wpdb;
		
		if ( ! isset($status)) {
			return false;
		}

		$packageObj = serialize($this);
		if (! $packageObj) {
			DUP_Log::Error("Unable to serialize pacakge object while updating record.");
		}
		
		$result = $wpdb->update( 
			$wpdb->prefix . "duplicator_packages", 
			array( 
				'status' => $status,
				'package' => $packageObj
			), 
			array( 'ID' => $this->ID )
		);
		
		
		return $result;
	}	
	
	
	/** 
	* Gets the required system checks
	* @return array   An array of system checks
	*/
	public static function GetSystemRequirments() {

		global $wpdb;

		$dup_tests = array();
		//SYS-100: FILE PERMS
		$test = is_writeable(DUPLICATOR_WPROOTPATH)
				&& is_writeable(DUPLICATOR_SSDIR_PATH)
				&& is_writeable(DUPLICATOR_SSDIR_PATH_TMP);
		$dup_tests['SYS-100'] = ($test) ? 'Pass' : 'Fail';

		//SYS-101 RESERVED FILE
		$dup_tests['SYS-101'] = (self::RequiredFilesFound()) ? 'Fail' : 'Pass';

		//SYS-102: ZIP-ARCHIVE
		$test = class_exists('ZipArchive');
		$dup_tests['SYS-102'] = ($test) ? 'Pass' : 'Fail';

		//SYS-103: SAFE MODE
		$test = (((strtolower(@ini_get('safe_mode'))   == 'on')   
				||  (strtolower(@ini_get('safe_mode')) == 'yes') 
				||  (strtolower(@ini_get('safe_mode')) == 'true') 
				||  (ini_get("safe_mode") == 1 )));
		$dup_tests['SYS-103'] = !($test) ? 'Pass' : 'Fail';

		//SYS-104: MYSQL SUPPORT
		$mysql_test1 = function_exists('mysqli_connect');
		$mysql_test2 = version_compare($wpdb->db_version(), '5.0', '>=');
		$dup_tests['SYS-104'] = ($mysql_test1 && $mysql_test2) ? 'Pass' : 'Fail';

		//SYS-105: PHP TESTS
		$php_test1 = version_compare(phpversion(), '5.2.17');
		$php_test2 =  function_exists("file_get_contents");
		$php_test3 =  function_exists("file_put_contents");
		$dup_tests['SYS-105'] = ($php_test1 >= 0 && $php_test2 && $php_test3) ? 'Pass' : 'Fail';

		//SYS-106: WEB SERVER 
		$servers = $GLOBALS['DUPLICATOR_SERVER_LIST'];
		$test = false;
		foreach ($servers as $value) {
			if (stristr($_SERVER['SERVER_SOFTWARE'], $value)) {
				$test = true;
				break;
			}
		}
		$dup_tests['SYS-106'] = ($test) ? 'Pass' : 'Fail';

		//RESULTS
		$result = in_array('Fail', $dup_tests);
		$dup_tests['Success'] = !$result;

		return $dup_tests;
	}		
	
	/** 
	* Gets the required system checks
	*  @return array   An array of system checks
	*/
	public static function RequiredFilesFound() {
		
		$phpFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP);
		$sqlFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL);
		$logFile = file_exists(DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG);
		return  ($phpFile || $sqlFile || $logFile);
		
	}
	

	public function GetServerChecks() {
		$dup_checks = array();

		//CHK-SRV-100
		$test = ini_get("open_basedir");
		$dup_checks['CHK-SRV-100'] = empty($test) ? 'Good' : 'Warn';

		//CHK-SRV-101
		$cache_path = DUP_Util::SafePath(WP_CONTENT_DIR) .  '/cache';

		$dirEmpty = DUP_Util::IsDirectoryEmpty($cache_path);
		$dirSize  = DUP_Util::GetDirectorySize($cache_path); //50K
		$dup_checks['CHK-SRV-101'] = ($dirEmpty  || $dirSize < 50000 )	? 'Good' : 'Warn';

		//CHK-SRV-102
		$test = ini_get("max_execution_time");
		
		$dup_checks['CHK-SRV-102'] = ($test > DUPLICATOR_SCAN_TIMEOUT || strcmp($test, 'Off') == 0 || $test == 0) ? 'Good' : 'Warn';
		
		//RESULTS
		$result = in_array('Warn', $dup_checks);
		$dup_checks['Success'] = !$result;

		return $dup_checks;
	}
	
	
	private function parseDirectoryFilter($dirs = "") {
		$filter_dirs = "";
		foreach (explode(";", $dirs) as $val) {
			if (strlen($val) >= 2) {
				$filter_dirs .= DUP_Util::SafePath(trim(rtrim($val, "/\\"))) . ";";
			}
		}
		return $filter_dirs;
	}
	
	private function parseExtensionFilter($extensions = "") {
		$filter_exts = "";
		if (strlen($extensions) >= 1 && $extensions != ";") {
			$filter_exts   = str_replace(array(' ', '.'), '', $extensions);
			$filter_exts   = str_replace(",", ";", $filter_exts);
			$filter_exts   = DUP_Util::StringAppend($extensions, ";");
		}
		return $filter_exts;
	}
	
}
?>