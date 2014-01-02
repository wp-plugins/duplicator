<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

class DUP_Task {
	
	const OPT_RUNNING = 'duplicator_task_running';
	
	public $IsRunning;
	public $Package;
	public $MsgRunning;

	function __construct() {
		$this->IsRunning = get_option(self::OPT_RUNNING, 0);
		
		$msg  = 'Another package is currently building.  Please wait for the task to complete.  ';
		$msg .= 'If the task does not finish see the Settings Diagnostics section and delete the [%1$s] flag.';
		$this->MsgRunning = sprintf(__($msg, 'wpduplicator'), self::OPT_RUNNING);
	}
	
	public function Create() {
		$this->IsRunning = update_option(self::OPT_RUNNING, 1);
		
		$Package = new DUP_Package();
		$Package = $Package->GetActive();
		$this->Package = $Package->Build();
		
		$this->IsRunning = update_option(self::OPT_RUNNING, 0);
		
	}

}
?>