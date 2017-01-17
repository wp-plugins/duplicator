<?php
if ( ! defined('DUPLICATOR_VERSION') ) exit; // Exit if accessed directly

require_once(DUPLICATOR_PLUGIN_PATH . '/ctrls/ctrl.base.php'); 
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/scan.validator.php'); 

/**
 * Controller for Tools 
 * @package Dupicator\ctrls
 */
class DUP_CTRL_Tools extends DUP_CTRL_Base
{	 
		
	function __construct() 
	{
		add_action('wp_ajax_DUP_CTRL_Tools_RunScanValidator', array($this, 'RunScanValidator'));
	}
	
	/** 
     * Calls the ScanValidator and returns a JSON result
	 * 
	 * @param string $_POST['scan-path']		The path to start scanning from, defaults to DUPLICATOR_WPROOTPATH
	 * @param bool   $_POST['scan-recursive']	Recursivly search the path
	 * 
	 * @notes: Testing = /wp-admin/admin-ajax.php?action=DUP_CTRL_Tools_RunScanValidator
     */
	public function RunScanValidator($post) 
	{
		$post = $this->PostParamMerge($post);
		check_ajax_referer($post['action'], 'nonce');
		
		$result = new DUP_CTRL_Result($this);
		 
		try 
		{
			//CONTROLLER LOGIC
			$path = isset($post['scan-path']) ? $post['scan-path'] : DUPLICATOR_WPROOTPATH;
			if (!is_dir($path)) {
				throw new Exception("Invalid directory provided '{$path}'!");
			}
			$scanner = new DUP_ScanValidator();
			$scanner->Recursion = (isset($post['scan-recursive']) && $post['scan-recursive'] != 'false') ? true : false;
			$payload = $scanner->Run($path);

			//RETURN RESULT
			$test = ($payload->FileCount > 0) 
					? DUP_CTRL_Status::SUCCESS
					: DUP_CTRL_Status::FAILED;
			$result->Process($payload, $test);			
		} 
		catch (Exception $exc) 
		{
			$result->ProcessError($exc);
		}
    }
	
}
?>
