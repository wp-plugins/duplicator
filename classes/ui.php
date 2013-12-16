<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

class DUP_UI {
	
	//Private
	private static $OptionsTableKey = 'duplicator_ui_view_state';
	
	
    /** METHOD: SaveViewState
     * Saves the state of a UI element
	 * @return json result string
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
    static public function SaveViewState() {

		$post = stripslashes_deep($_POST);
		$json = array();
	   
		$view_state = array();
		$view_state = get_option(self::$OptionsTableKey);
		$view_state[$post['key']] =  $post['value'];
		$success = update_option(self::$OptionsTableKey, $view_state);
		
		//Show Results as JSON
		$json['key']    = $post['key'];
		$json['value']  = $post['value'];
		$json['update-success'] = $success;
		die(json_encode($json));
    }
	
	
	/** METHOD: GetViewStateArray
     *  
     */
    static public function GetViewStateArray() {
		return get_option(self::$OptionsTableKey);
	}
	
	 /** METHOD: GetViewStateValue
     *  
     */
    static public function GetViewStateValue($searchKey) {
		$view_state = get_option(self::$OptionsTableKey);
		if (is_array($view_state)) {
			foreach ($view_state as $key => $value) {
				if ($key == $searchKey) {
					return $value;	
				}
			}
		} else {
			return null;
		}
	}
	
	

}
?>
