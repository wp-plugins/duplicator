<?php
if ( ! defined('DUPLICATOR_VERSION') ) exit; // Exit if accessed directly

require_once(DUPLICATOR_PLUGIN_PATH . '/ctrls/ctrl.base.php'); 
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/ui.php'); 

/**
 * Controller for Tools 
 * @package Dupicator\ctrls
 */
class DUP_CTRL_UI extends DUP_CTRL_Base
{	 
	
	/** 
     * Calls the SaveViewState and returns a JSON result
	 * 
	 * @param string $_POST['key']		A unique key that idetifies the state of the UI element
	 * @param bool   $_POST['value']	The value to store for the state of the UI element
	 * 
	 * @notes: Testing = /wp-admin/admin-ajax.php?action=DUP_CTRL_UI_SaveViewState
	 * 
	 * <code>
	 * //JavaScript Ajax Request
	 * Duplicator.UI.SaveViewState('dup-pack-archive-panel', 1);
	 * 
	 * //Call PHP Code
	 * $view_state       = DUP_UI::GetViewStateValue('dup-pack-archive-panel');
	 * $ui_css_archive   = ($view_state == 1)   ? 'display:block' : 'display:none';
	 * </code>
     */
	public static function SaveViewState($post) 
	{
		$post   = is_array($post) ? $post : array();
		$post   = array_merge($_POST, $post);
		$result = new DUP_CTRL_Result();
	
		try 
		{
			//SETUP		
			DUP_Util::CheckPermissions('read');
			//check_ajax_referer('DUP_CTRL_UI_SaveViewState', 'nonce');
			header('Content-Type: application/json');
			
			//====================
			//CONTROLLER LOGIC
			$post  = stripslashes_deep($_POST);
			$key   = esc_html($post['key']);
			$value = esc_html($post['value']);
			$success = DUP_UI::SaveViewState($key, $value);

			$payload = array();
			$payload['key']    = $key;
			$payload['value']  = $value;
			$payload['update-success'] = $success;
			
			//====================
			//RETURN RESULT
			$test = ($success) 
					? DUP_CTRL_ResultStatus::SUCCESS
					: DUP_CTRL_ResultStatus::FAILED;
			$result->Process($payload, $test);			
		} 
		catch (Exception $exc) 
		{
			$result->ProcessError($exc);
		}
    }
	
}
?>
