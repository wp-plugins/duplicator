<?php
if ( ! defined('DUPLICATOR_VERSION') ) exit; // Exit if accessed directly

/**
 * Helper Class for UI internactions
 * @package Dupicator\classes
 */
class DUP_UI {
	
	/**
	 * The key used in the wp_options table
	 * @var string 
	 */
	private static $OptionsTableKey = 'duplicator_ui_view_state';
	
	/** 
     * Save the view state of UI elements
	 * @param string $key A unique key to define the ui element
	 * @param string $value A generic value to use for the view state
     */
	public static function SaveViewState($key, $value) {
	   
		$view_state = array();
		$view_state = get_option(self::$OptionsTableKey);
		$view_state[$key] =  $value;
		$success = update_option(self::$OptionsTableKey, $view_state);
		
		return $success;
    }
	
	
    /** 
     * Saves the state of a UI element via post params
	 * @return json result string
	 * <code>
	 * //JavaScript Ajax Request
	 * Duplicator.UI.SaveViewStateByPost('dup-pack-archive-panel', 1);
	 * 
	 * //Call PHP Code
	 * $view_state       = DUP_UI::GetViewStateValue('dup-pack-archive-panel');
	 * $ui_css_archive   = ($view_state == 1)   ? 'display:block' : 'display:none';
	 * </code>
     */
    public static function SaveViewStateByPost() {
		
		DUP_Util::CheckPermissions('read');
		
		$post  = stripslashes_deep($_POST);
		$key   = esc_html($post['key']);
		$value = esc_html($post['value']);
		$success = self::SaveViewState($key, $value);
		
		//Show Results as JSON
		$json = array();
		$json['key']    = $key;
		$json['value']  = $value;
		$json['update-success'] = $success;
		die(json_encode($json));
    }
	
	
	/** 
     *	Gets all the values from the settings array
	 *  @return array Returns and array of all the values stored in the settings array
     */
    public static function GetViewStateArray() {
		return get_option(self::$OptionsTableKey);
	}
	
	 /** 
	  * Return the value of the of view state item
	  * @param type $searchKey The key to search on
	  * @return string Returns the value of the key searched or null if key is not found
	  */
    public static function GetViewStateValue($searchKey) {
		$view_state = get_option(self::$OptionsTableKey);
		if (is_array($view_state)) {
			foreach ($view_state as $key => $value) {
				if ($key == $searchKey) {
					return $value;	
				}
			}
		} 
		return null;
	}
	
	/**
	 * Shows a display message in the wp-admin if any researved files are found
	 * @return type void
	 */
	public static function ShowReservedFilesNotice() 
	{
		//Show only on Duplicator pages and Dashboard when plugin is active
		$dup_active = is_plugin_active('duplicator/duplicator.php');
		$dup_perm   = current_user_can( 'manage_options' );
		if (! $dup_active || ! $dup_perm) 
			return;

		if (DUP_Server::InstallerFilesFound()) 
		{
			$screen = get_current_screen();
			$on_active_tab =  isset($_GET['tab']) && $_GET['tab'] == 'cleanup' ? true : false;
			
			echo '<div class="error" id="dup-global-error-reserved-files"><p>';
			if ($screen->id == 'duplicator_page_duplicator-tools' && $on_active_tab) 
			{
				_e('Reserved Duplicator install files have been detected in the root directory.  Please delete these reserved files to avoid security issues.', 'duplicator');
			}
			else 
			{
				$duplicator_nonce = wp_create_nonce('duplicator_cleanup_page');
				_e('Reserved Duplicator install files have been detected in the root directory.  Please delete these reserved files to avoid security issues.', 'duplicator');
				@printf("<br/><a href='admin.php?page=duplicator-tools&tab=cleanup&_wpnonce=%s'>%s</a>", $duplicator_nonce, __('Take me to the cleanup page!', 'duplicator'));
			}			
			echo "</p></div>";
		} 
	}
	
	/**
	 * Shows a random affilate link
	 * @return type string
	 */
	public static function ShowRandomAffilateLink() 
	{
		/*-- AFFILIATES --*/
		$aff_urls = array();
		$aff_urls[0] = 'https://snapcreek.com/visit/bluehost';
		$aff_urls[1] = 'https://snapcreek.com/visit/inmotion';
		$aff_urls[2] = 'https://snapcreek.com/visit/elegantthemes';
		$aff_urls[3] = 'https://snapcreek.com/visit/ninjaforms';
		$aff_urls[4] = 'https://snapcreek.com/visit/optinmonster';
		$aff_urls[5] = 'https://snapcreek.com/visit/managewp';
		$aff_urls[6] = 'https://snapcreek.com/visit/maxcdn';	

		$aff_text = array();
		$aff_text[0] = __('Need a new host? Try Duplicator-approved Bluehost today!', 'duplicator');	// Bluehost
		$aff_text[1] = __('On a bad host? Trade up to InMotion - with FREE SSDs!', 'duplicator');		// InMotion
		$aff_text[2] = __('Have a cheesy theme? Change to an Elegant Theme today!', 'duplicator');		// Elegant Themes
		$aff_text[3] = __('Need a great formbuilder? Get Ninja Forms!', 'duplicator');					// Ninja Forms
		$aff_text[4] = __('Visitors leaving too quickly? Snag \'em with Optinmonster!', 'duplicator');	// Optinmonster	
		$aff_text[5] = __('Juggling multiple sites? Control them all from ONE dashboard!', 'duplicator');	// ManageWP
		$aff_text[6] = __('Slow site? Supercharge it with MaxCDN today!', 'duplicator');				// MaxCDN
		
		$aff_icon = array();
		$aff_fa[0] = "fa-thumbs-up";
		$aff_fa[1] = "fa-cube";
		$aff_fa[2] = "fa-plug";
		$aff_fa[3] = "fa-check-square-o";
		$aff_fa[4] = "fa-envelope";
		$aff_fa[5] = "fa-sitemap";
		$aff_fa[6] = "fa-maxcdn";

		$aff_index = rand(0, count($aff_urls) - 1);
		
		return 	"<i class='fa {$aff_fa[$aff_index]}'></i>&nbsp; <a style='font-style:italic;font-size:15px;font-weight:normal' target='_blank' href='{$aff_urls[$aff_index]}'>$aff_text[$aff_index]</a>";
	}
	
}
?>
