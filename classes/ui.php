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
	public static function ShowRandomAffilateLink($format = 'one') 
	{
		/*-- AFFILIATES --*/
		$aff_urls = array();
		$aff_urls[0] = 'https://snapcreek.com/visit/bluehost';
		$aff_urls[1] = 'https://snapcreek.com/visit/inmotion';
		$aff_urls[2] = 'https://snapcreek.com/visit/elegantthemes';
		$aff_urls[3] = 'https://snapcreek.com/visit/managewp';
		$aff_urls[4] = 'https://snapcreek.com/visit/maxcdn';
		//$aff_urls[5] = 'https://snapcreek.com/visit/ninjaforms';
		//$aff_urls[6] = 'https://snapcreek.com/visit/optinmonster';

		$aff_text = array();
		// Bluehost
		$aff_text[0] = sprintf("<b>%s</b> <i>%s</i>", 
							__('Need a new host?', 'duplicator'), 
							__('Get Bluehost Hosting for 50% off today!', 'duplicator'));
		// InMotion
		$aff_text[1] = sprintf("<b>%s</b> <i>%s</i>", 
							__('On a bad host?', 'duplicator'), 
							__('Trade up to InMotion - with FREE SSDs and up to 25% off!', 'duplicator'));
		// Elegant Themes
		$aff_text[2] = sprintf("<b>%s</b> <i>%s</i>", 
							__('Have a cheesy theme?', 'duplicator'), 
							__('Change to an Elegant Theme and get 10% off today!', 'duplicator'));
		// ManageWP
		$aff_text[3] = sprintf("<b>%s</b> <i>%s</i>", 
							__('Juggling multiple sites?', 'duplicator'), 
							__('Control them all from ONE dashboard - 10% off ManageWP today!', 'duplicator'));
		// MaxCDN
		$aff_text[4] = sprintf("<b>%s</b> <i>%s</i>", 
							__('Slow site?', 'duplicator'), 
							__('Supercharge it with MaxCDN and get 25% off today!', 'duplicator'));		
		
		// Ninja Forms
		$aff_text[5] = sprintf("<b>%s</b> <i>%s</i>", 
							__('Need a great formbuilder?', 'duplicator'), 
							__('Get Ninja Forms!', 'duplicator'));
		// Optinmonster	
		$aff_text[6] = sprintf("<b>%s</b> <i>%s</i>", 
							__('Visitors leaving too quickly?', 'duplicator'), 
							__('Snag \'em with Optinmonster!', 'duplicator'));
	
		$aff_icon = array();
		$aff_fa[0] = "fa-th";
		$aff_fa[1] = "fa-cube";
		$aff_fa[2] = "fa-asterisk";
		$aff_fa[3] = "fa-sitemap";
		$aff_fa[4] = "fa-maxcdn";
		$aff_fa[5] = "fa-check-square-o";
		$aff_fa[6] = "fa-envelope";

		if ($format == 'list')
		{
			//Generate a list
			$html = '<div id="dup-add-slider"><ul>';
			for ($i = 0; $i < count($aff_urls); $i++)
			{
				$html .= "<li><i class='fa {$aff_fa[$i]}'></i>&nbsp; <a target='_blank' href='{$aff_urls[$i]}'>$aff_text[$i]</a></li>";
			}
			$html .= '</ul></div>';
		} 
		else 
		{
			//Return single item
			$aff_index = rand(0, count($aff_urls) - 1);
			$html  = "<span id='dup-add-link'><i class='fa {$aff_fa[$aff_index]}'></i>&nbsp; <a href='admin.php?page=duplicator-perks&amp;a={$aff_index}'>$aff_text[$aff_index]</a> &nbsp; ";
		}
		return $html;
	}
}
?>
