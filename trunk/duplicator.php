<?php
/*
Plugin Name: Duplicator
Plugin URI: http://www.lifeinthegrid.com/duplicator/
Description: Create a full WordPress backup of your files and database with one click. Duplicate and move an entire site from one location to another in 3 easy steps. Create full snapshot of your site at any point in time.
Version: 0.2.3
Author: LifeInTheGrid
Author URI: http://www.lifeinthegrid.com
License: GPLv2 or later
*/

/* ================================================================================ 
Copyright 2011  Cory Lamle 
Copyright 2011  Gaurav Aggarwal  
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
Contributors:
 Many thanks go out to Gaurav Aggarwal for starting the Backup and Move Plugin.
 This project is a fork of that project
 See http://www.logiclord.com/backup-and-move/ for more details.		

================================================================================ */

//==============================================================================
//Update per relase
define('DUPLICATOR_VERSION',   		'0.2.3');
define('DUPLICATOR_DBVERSION', 		'0.2.3');
define("DUPLICATOR_HELPLINK",  		"http://lifeinthegrid.com/support/knowledgebase.php?category=4");
define("DUPLICATOR_GIVELINK",		"http://lifeinthegrid.com/partner/");
define("DUPLICATOR_DB_ICONV_IN",	"UTF-8"); 
define("DUPLICATOR_DB_ICONV_OUT",	"ISO-8859-1//TRANSLIT"); 
define('DUPLICATOR_PLUGIN_URL', plugin_dir_url( __FILE__ ));
define('DUPLICATOR_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
//==============================================================================

if (is_admin() == true) {
	
	$GLOBALS['duplicator_opts'] 		      = unserialize(get_option('duplicator_options'));	
	$GLOBALS['duplicator_bypass-array']		  = explode(";", $GLOBALS['duplicator_opts']['dir_bypass'], -1);
	$GLOBALS['duplicator_bypass-array'] 	  = count($GLOBALS['duplicator_bypass-array']) ? $GLOBALS['duplicator_bypass-array'] : null;
	$GLOBALS['duplicator_opts']['max_time']   = is_numeric($GLOBALS['duplicator_opts']['max_time']) ? $GLOBALS['duplicator_opts']['max_time']   : 300;
	$GLOBALS['duplicator_opts']['max_memory'] = isset($GLOBALS['duplicator_opts']['max_memory'])    ? $GLOBALS['duplicator_opts']['max_memory'] : "128M";

	/* Paths should ALWAYS read "/"
		uni: /home/path/file.txt
		win:  D:/home/path/file.txt 
		SSDIR = SnapShot Directory */
	if ( !defined('ABSPATH') ) {
		define('ABSPATH', dirname('__FILE__'));
	}
	define('DUPLICATOR_WPROOTPATH',  str_replace("\\", "/", ABSPATH));
	define("DUPLICATOR_SSDIR_NAME",  'wp-snapshots'); 
	define("DUPLICATOR_SSDIR_PATH",  DUPLICATOR_WPROOTPATH . DUPLICATOR_SSDIR_NAME);
	define("DUPLICATOR_LOGLEVEL",    $GLOBALS['duplicator_opts']['log_level']);
	
	
	switch (DUPLICATOR_LOGLEVEL) {
		case 3: error_reporting(E_ALL ^ E_NOTICE); ini_set('display_errors', 1);break;
		//LEVEL 9: Developer useage
		case 9: error_reporting(E_ALL); ini_set('display_errors', 1); break;
	}
	
	require_once 'inc/functions.php';
	require_once 'inc/class.zip.php';
	require_once 'inc/actions.php';

	/* ACTIVATION 
	Only called when plugin is activated */
	register_activation_hook(__FILE__ , 'duplicator_activate');
	function duplicator_activate() {
		global $wpdb;

		$table_name = $wpdb->prefix . "duplicator";
		
		if ( $wpdb->get_var('SHOW TABLES LIKE ' . $table_name) != $table_name ) {
		
			$sql = "CREATE TABLE {$table_name} (
			 bid BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			 zipname 	VARCHAR(250) NOT NULL, 
			 zipsize 	INT (11),
			 created 	DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			 owner 		VARCHAR(60) NOT NULL,
			 type 		VARCHAR(30) NOT NULL,
			 status		VARCHAR(30) NOT NULL,
			 ver_plug 	VARCHAR(10) NOT NULL,
			 ver_db 	VARCHAR(10) NOT NULL)" ;

			require_once(DUPLICATOR_WPROOTPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			duplicator_create_snapshotpath();
			
			//defaults, duplicator_version_plugin reffers to this plugin
			add_option('duplicator_version_plugin', 	DUPLICATOR_VERSION);
			add_option('duplicator_version_database', 	DUPLICATOR_DBVERSION);

			$duplicator_opts = array(
				'dbhost'		=>'localhost',
				'dbname'		=>'',
				'dbuser'		=>'',
				'nurl'			=>'',
				'email-me'		=>'0',
				'max_time'		=>$GLOBALS['duplicator_opts']['max_time'],
				'max_memory'	=>$GLOBALS['duplicator_opts']['max_memory'],
				'dir_bypass'	=>'',
				'log_level'		=>'0',
				'log_paneheight'=>'200',
				'dbiconv'		=>'1');
				
			update_option('duplicator_options', serialize($duplicator_opts));
		}
	}
	
	/* DEACTIVATION 
	Only called when plugin is deactivated */
	register_deactivation_hook(__FILE__ , 'duplicator_deactivate');
	function duplicator_deactivate() {
		//Possible Cleanup
	}
	
	//ACTIONS
	add_action('admin_init', 						'duplicator_init' );
	add_action('admin_menu', 						'duplicator_menu');
	add_action('wp_ajax_duplicator_overwrite',		'duplicator_overwrite');
	add_action('wp_ajax_duplicator_system_check',	'duplicator_system_check');
	add_action('wp_ajax_duplicator_delete',			'duplicator_delete');
	add_action('wp_ajax_duplicator_create',			'duplicator_create');
	add_action('wp_ajax_duplicator_settings',		'duplicator_settings');
	add_filter('plugin_action_links', 				'duplicator_manage_link', 10, 2 );


	/**
	 *  DUPLICATOR_INIT
	 *  Init routines  */
	function duplicator_init() {
	   /* Register our stylesheet. */
	   wp_register_style('jquery-ui', 		  DUPLICATOR_PLUGIN_URL . 'css/jquery-ui.css' );
	   wp_register_style('duplicator_style',  DUPLICATOR_PLUGIN_URL . 'css/style.css' );
	}

	/**
	 *  DUPLICATOR_VIEWS
	 *  Inlcude all visual elements  */
	function duplicator_views() {
		include 'inc/view.main.php';
	}

	/**
	 *  DUPLICATOR_MENU
	 *  Loads the menu item into the WP tools section and queues the actions for only this plugin */
	function duplicator_menu() {
		$view = add_submenu_page('tools.php','Duplicator', 'Duplicator', 'import', 'duplicator_list', 'duplicator_views');
		add_action('admin_print_scripts-' . $view, 'duplicator_scripts');
		add_action('admin_print_styles-'  . $view, 'duplicator_styles' );
	}

	/**
	 *  DUPLICATOR_SCRIPTS
	 *  Loads the required javascript libs only for this plugin  */
	function duplicator_scripts() {
		$script_path = path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ ) ).'/js');
		wp_deregister_script('jquery');
		wp_enqueue_script("jquery",    "${script_path}/jquery.min.js",    '',  	 "1.5.1");
		wp_enqueue_script("jquery-ui", "${script_path}/jquery-ui.min.js", array( 'jquery' ), "1.8.10");
		wp_enqueue_script("duplicator_handler_script", "${script_path}/ajax.js", array( 'jquery' )  );
	}

	/**
	 *  DUPLICATOR_STYLES
	 *  Loads the required css links only for this plugin  */
	function duplicator_styles() {
	   wp_enqueue_style('jquery-ui');
	   wp_enqueue_style('duplicator_style');
	}
	
	/**
	 *  DUPLICATOR_MANAGE_LINK
	 *  Adds the manage link in the plugins list */
	function duplicator_manage_link($links, $file) {
		static $this_plugin;
		if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
		 
		if ($file == $this_plugin){
		$settings_link = '<a href="tools.php?page=duplicator_list">'.__("Manage", "manage-duplicator").'</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
	}

}
?>