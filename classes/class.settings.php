<?php
if ( ! class_exists( 'DuplicatorSettings' ) ) {
	class DuplicatorSettings
	{
		public $Data;
		public $Version = DUPLICATOR_VERSION;
		public $OptionName = 'duplicator_settings';

		/**
		*  DUPLICATOR-SETTINGS
		*  Class used to manage all the settings for the plugin
		*/
		function __construct() {
			$this->Data = get_option($this->OptionName);
			
			//when the plugin updated, this will be true
			if (empty($this->Data) || $this->Version > $this->Data['version']){
				$this->SetDefaults();
			}
		}
		
		/**
		*  GET: Find the setting value
		*  @param string $key	The name of the key to find
		*  @return The value stored in the key returns null if key does not exist
		*/
		function Get($key = '') {
			return isset($this->Data[$key]) ? $this->Data[$key] : null;
		}
		
		/**
		*  SET: Set the settings value in memory only
		*  @param string $key		The name of the key to find
		*  @param string $value		The value to set
		*  remarks:	 The Save() method must be called to write the Settings object to the DB
		*/
		function Set($key = '', $value) {
			if (isset($this->Data[$key]) && $value != null) {
				$this->Data[$key] = $value;
			} elseif (!empty($key) && $value != null) {
				$this->Data[$key] = $value;
			}
		}

		/**
		*  SAVE: Save all the setting values to the database
		*  @return True if option value has changed, false if not or if update failed.
		*/
		public function Save() {
			return update_option($this->OptionName, $this->Data);
		}
		
		/**
		*  DELETE: Delete all the setting values to the database
		*  @return True if option value has changed, false if not or if update failed.
		*/
		public function Delete() {
			return delete_option($this->OptionName);
		}
		
		/**
		*  SETDEFAULTS
		*  Sets the defaults if they have not been set
		*  @return True if option value has changed, false if not or if update failed.
		*/
		public function SetDefaults() {
			$default = array();
			$default['version'] = $this->Version;
			
			//Flag used to remove the wp_options value duplicator_settings which are all the settings in this class
			$default['uninstall_settings'] = isset($this->Data['uninstall_settings']) ? $this->Data['uninstall_settings'] : true;
			
			//Flag used to remove entire wp-snapshot directory
			$default['uninstall_files']    = isset($this->Data['uninstall_files'])  ? $this->Data['uninstall_files']  : true;
			
			//Flag used to remove all tables
			$default['uninstall_tables']   = isset($this->Data['uninstall_tables']) ? $this->Data['uninstall_tables'] : true;

			$this->Data = $default;
			return $this->Save();
			
		}
		
	}
}
?>