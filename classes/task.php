<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

class DUP_Task {
		

	public $Package;
	public $MsgRunning;

	function __construct() {

	}
	
	public function Create() {
		
		$Package = new DUP_Package();
		$Package = $Package->GetActive();
		$this->Package = $Package->Build();
		$this->moveFromTmp();

	}
	

	private function moveFromTmp() {
		
		$files   = glob(DUPLICATOR_SSDIR_PATH_TMP . "/{*.zip,*.sql,*.php}", GLOB_BRACE);
		$newPath = DUPLICATOR_SSDIR_PATH;
		
		if (function_exists('rename')) {
			foreach($files as $file){
				$name = basename($file);
				rename($file,"{$newPath}/{$name}");
			}
		} else {
			foreach($files as $file){
				$name = basename($file);
				copy($file,"{$newPath}/{$name}");
				unlink($file);
			}
		}
	}

}
?>