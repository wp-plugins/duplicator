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
		*  GET
		*  Find the setting value
		*
		*  @param string $key	The name of the key to find
		*  @return The value stored in the key returns null if key does not exist
		*/
		function Get($key = '') {
			return isset($this->Data[$key]) ? $this->Data[$key] : null;
		}
		
		function Set($key = '', $value) {
			if (isset($this->Data[$key]) && $value != null) {
				$this->Data[$key] = $value;
			}
		}

		/**
		*  SAVE
		*  sAVE the setting value
		*
		*  @return True if option value has changed, false if not or if update failed.
		*/
		public function Save() {
			return update_option($this->OptionName, $this->Data);
		}
		
		/**
		*  SETDEFAULTS
		*  Sets the defaults if they have not been set
		*
		*  @return True if option value has changed, false if not or if update failed.
		*/
		public function SetDefaults() {
			$default['version'] = $this->Version;
			$default['uninstall_files']  = isset($this->Data['uninstall_files'])  ? $this->Data['uninstall_files']  : false;
			$default['uninstall_tables'] = isset($this->Data['uninstall_tables']) ? $this->Data['uninstall_tables'] : true;

			$this->Data = $default;
			return $this->Save();
			
		}
		
	}
}

?>