<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

class DUP_Settings
{
	public static $Data;
	public static $Version = DUPLICATOR_VERSION;
	public static $OptionName = 'duplicator_settings';

	/**
	*  DUPLICATOR-SETTINGS
	*  Class used to manage all the settings for the plugin
	*/
	static function init() {
		self::$Data = get_option(self::$OptionName);

		//when the plugin updated, this will be true
		if (empty(self::$Data) || self::$Version > self::$Data['version']){
			self::SetDefaults();
		}
	}

	/**
	*  GET: Find the setting value
	*  @param string $key	The name of the key to find
	*  @return The value stored in the key returns null if key does not exist
	*/
	public static function Get($key = '') {
		return isset(self::$Data[$key]) ? self::$Data[$key] : null;
	}

	/**
	*  SET: Set the settings value in memory only
	*  @param string $key		The name of the key to find
	*  @param string $value		The value to set
	*  remarks:	 The Save() method must be called to write the Settings object to the DB
	*/
	public static function Set($key = '', $value) {
		if (isset(self::$Data[$key]) && $value != null) {
			self::$Data[$key] = $value;
		} elseif (!empty($key) && $value != null) {
			self::$Data[$key] = $value;
		}
	}

	/**
	*  SAVE: Save all the setting values to the database
	*  @return True if option value has changed, false if not or if update failed.
	*/
	public static function Save() {
		return update_option(self::$OptionName, self::$Data);
	}

	/**
	*  DELETE: Delete all the setting values to the database
	*  @return True if option value has changed, false if not or if update failed.
	*/
	public static function Delete() {
		return delete_option(self::$OptionName);
	}

	/**
	*  SETDEFAULTS
	*  Sets the defaults if they have not been set
	*  @return True if option value has changed, false if not or if update failed.
	*/
	public static function SetDefaults() {
		$default = array();
		$default['version'] = self::$Version;

		//Flag used to remove the wp_options value duplicator_settings which are all the settings in this class
		$default['uninstall_settings'] = isset(self::$Data['uninstall_settings']) ? self::$Data['uninstall_settings'] : true;

		//Flag used to remove entire wp-snapshot directory
		$default['uninstall_files']    = isset(self::$Data['uninstall_files'])  ? self::$Data['uninstall_files']  : true;

		//Flag used to remove all tables
		$default['uninstall_tables']   = isset(self::$Data['uninstall_tables']) ? self::$Data['uninstall_tables'] : true;

		//Flag used to auto skip scanner
		$default['package_skip_scanner']   = isset(self::$Data['package_skip_scanner']) ? self::$Data['package_skip_scanner'] : false;

		self::$Data = $default;
		return self::Save();

	}
	
	
	/**
	*  LegacyClean: Cleans up legacy data
	*/
	public static function LegacyClean() {
		global $wpdb;

		//PRE 5.0
		$table = $wpdb->prefix."duplicator";
		$wpdb->query("DROP TABLE IF EXISTS $table");
		delete_option('duplicator_pack_passcount'); 
		delete_option('duplicator_add1_passcount'); 
		delete_option('duplicator_add1_clicked'); 
		delete_option('duplicator_options'); 
		
		//PRE 5.1
		//Next version here if needed
	}
	
	/**
	*  DeleteWPOption: Cleans up legacy data
	*/
	public static function DeleteWPOption($optionName) {
		
		$safeOpts = array('duplicator_ui_view_state', 'duplicator_package_active');

		if ( in_array($optionName, $safeOpts) ) {
			return delete_option('duplicator_ui_view_state'); 
		}
		return false;
	}

}

//Init Class
DUP_Settings::init();

?>