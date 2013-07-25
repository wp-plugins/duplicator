<?php
/*
  Plugin Name: Duplicator
  Plugin URI: http://www.lifeinthegrid.com/duplicator/
  Description: Create a backup of your WordPress files and database. Duplicate and move an entire site from one location to another in a few steps. Create a full snapshot of your site at any point in time.
  Version: 0.4.6
  Author: LifeInTheGrid
  Author URI: http://www.lifeinthegrid.com
  License: GPLv2 or later
 */

/* ================================================================================ 
  Copyright 2011-2013  Cory Lamle

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

  SOURCE CONTRIBUTORS:
  Gaurav Aggarwal
  Jonathan Foote

  ================================================================================ */

require_once("define.php");

if (is_admin() == true) {

    $_tmpDuplicatorOptions = get_option('duplicator_options', false);
    $GLOBALS['duplicator_opts'] = ($_tmpDuplicatorOptions == false) ? array() : @unserialize($_tmpDuplicatorOptions);

    //OPTIONS - PACKAGE TAB
	$GLOBALS['duplicator_opts']['email-me'] = isset($GLOBALS['duplicator_opts']['email-me']) ? $GLOBALS['duplicator_opts']['email-me'] : '0';
    $GLOBALS['duplicator_opts']['email_others'] = isset($GLOBALS['duplicator_opts']['email_others']) ? $GLOBALS['duplicator_opts']['email_others'] : '';
    $GLOBALS['duplicator_opts']['skip_ext'] = isset($GLOBALS['duplicator_opts']['skip_ext']) ? $GLOBALS['duplicator_opts']['skip_ext'] : '';
    $GLOBALS['duplicator_opts']['dir_bypass'] = isset($GLOBALS['duplicator_opts']['dir_bypass']) ? $GLOBALS['duplicator_opts']['dir_bypass'] : '';
	
	//OPTIONS - INSTALLER TAB
    $GLOBALS['duplicator_opts']['dbhost'] = isset($GLOBALS['duplicator_opts']['dbhost']) ? $GLOBALS['duplicator_opts']['dbhost'] : 'localhost';
    $GLOBALS['duplicator_opts']['dbname'] = isset($GLOBALS['duplicator_opts']['dbname']) ? $GLOBALS['duplicator_opts']['dbname'] : '';
    $GLOBALS['duplicator_opts']['dbuser'] = isset($GLOBALS['duplicator_opts']['dbuser']) ? $GLOBALS['duplicator_opts']['dbuser'] : '';
    $GLOBALS['duplicator_opts']['dbadd_drop'] = isset($GLOBALS['duplicator_opts']['dbadd_drop']) ? $GLOBALS['duplicator_opts']['dbadd_drop'] : '0';
	//Advanced Opts
	$GLOBALS['duplicator_opts']['ssl_admin'] = isset($GLOBALS['duplicator_opts']['ssl_admin']) ? $GLOBALS['duplicator_opts']['ssl_admin'] : '0';
	$GLOBALS['duplicator_opts']['ssl_login'] = isset($GLOBALS['duplicator_opts']['ssl_login']) ? $GLOBALS['duplicator_opts']['ssl_login'] : '0';
	$GLOBALS['duplicator_opts']['cache_wp'] = isset($GLOBALS['duplicator_opts']['cache_wp']) ? $GLOBALS['duplicator_opts']['cache_wp'] : '0';
	$GLOBALS['duplicator_opts']['cache_path'] = isset($GLOBALS['duplicator_opts']['cache_path']) ? $GLOBALS['duplicator_opts']['cache_path'] : '0';
	$GLOBALS['duplicator_opts']['url_new'] = isset($GLOBALS['duplicator_opts']['url_new']) ? $GLOBALS['duplicator_opts']['url_new'] : '';

    //Default Arrays
    $GLOBALS['duplicator_bypass-array'] = explode(";", $GLOBALS['duplicator_opts']['dir_bypass'], -1);
    $GLOBALS['duplicator_bypass-array'] = count($GLOBALS['duplicator_bypass-array']) ? $GLOBALS['duplicator_bypass-array'] : array();
    $GLOBALS['duplicator_skip_ext-array'] = explode(";", $GLOBALS['duplicator_opts']['skip_ext']) ? explode(";", $GLOBALS['duplicator_opts']['skip_ext']) : array();

	require_once 'classes/class.util.php';
	require_once 'classes/class.settings.php';
    require_once 'classes/class.zip.php';
	require_once 'inc/functions.php';
    require_once 'inc/actions.php';
    
	//SETTINGS
	global $DuplicatorSettings;
	$DuplicatorSettings = new DuplicatorSettings();
	

    /* ACTIVATION 
      Only called when plugin is activated */
    function duplicator_activate() {

        global $wpdb;
        $table_name = $wpdb->prefix . "duplicator";

        //PRIMARY KEY must have 2 spaces before for dbDelta to work
        $sql = "CREATE TABLE `{$table_name}` (
		 id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT  PRIMARY KEY,
		 token 	 	 VARCHAR(25) NOT NULL, 
		 packname 	 VARCHAR(250) NOT NULL, 
		 zipname 	 VARCHAR(250) NOT NULL, 
		 zipsize 	 INT (11),
		 created 	 DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		 owner 		 VARCHAR(60) NOT NULL,
		 settings 	 LONGTEXT NOT NULL)";

        require_once(DUPLICATOR_WPROOTPATH . 'wp-admin/includes/upgrade.php');
        @dbDelta($sql);

        $duplicator_opts = array(
            'dbhost' => "{$GLOBALS['duplicator_opts']['dbhost']}",
            'dbname' => '',
            'dbuser' => '',
            'url_new' => '',
            'email-me' => "{$GLOBALS['duplicator_opts']['email-me']}",
            'email_others' => "{$GLOBALS['duplicator_opts']['email_others']}",
            'dir_bypass' => "{$GLOBALS['duplicator_opts']['dir_bypass']}",
            'log_level' => '0',
            'skip_ext' => "{$GLOBALS['duplicator_opts']['skip_ext']}");

		//WordPress Options Hooks
        update_option('duplicator_version_plugin', DUPLICATOR_VERSION);
        update_option('duplicator_options', serialize($duplicator_opts));
		
		add_option('duplicator_pack_passcount', 0);
		add_option('duplicator_add1_passcount', 0);
		if (get_option('duplicator_add1_clicked') === false) {
			add_option('duplicator_add1_clicked', 0);
		}

        //Setup All Directories
        duplicator_init_snapshotpath();
    }

    /* UPDATE 
      register_activation_hook is not called when a plugin is updated
      so we need to use the following function */
    function duplicator_update() {
        if (DUPLICATOR_VERSION != get_option("duplicator_version_plugin")) {
            duplicator_activate();
        }
    }

    /* DEACTIVATION 
      Only called when plugin is deactivated */
    function duplicator_deactivate() {
        //No actions needed yet
    }

    /* UNINSTALL 
      Uninstall all duplicator logic */
    function duplicator_uninstall() {
        global $wpdb;
		global $DuplicatorSettings;
		
        $table_name = $wpdb->prefix . "duplicator";
        $wpdb->query("DROP TABLE `{$table_name}`");

        delete_option('duplicator_version_plugin');
        delete_option('duplicator_options');
		
		//Remvoe entire wp-snapshots directory
        if ($DuplicatorSettings->Get('uninstall_files')) {

            $ssdir = duplicator_safe_path(DUPLICATOR_SSDIR_PATH);

            //Sanity check for strange setup
            $check = glob("{$ssdir}/wp-config.php");
            if (count($check) == 0) {

                //PHP is sometimes flaky so lets do a sanity check
                foreach (glob("{$ssdir}/*_database.sql") as $file) {
                    if (strstr($file, '_database.sql')) {
                        @unlink("{$file}");
                    }
                }
                foreach (glob("{$ssdir}/*_installer.php") as $file) {
                    if (strstr($file, '_installer.php')) {
                        @unlink("{$file}");
                    }
                }
                foreach (glob("{$ssdir}/*_package.zip") as $file) {
                    if (strstr($file, '_package.zip')) {
                        @unlink("{$file}");
                    }
                }
                foreach (glob("{$ssdir}/*.log") as $file) {
                    if (strstr($file, '.log')) {
                        @unlink("{$file}");
                    }
                }

                //Check for core files and only continue removing data if the snapshots directory
                //has not been edited by 3rd party sources, this helps to keep the system stable
                $files = glob("{$ssdir}/*");
                if (is_array($files) && count($files) == 3) {
                    $defaults = array("{$ssdir}/index.php", "{$ssdir}/robots.txt", "{$ssdir}/dtoken.php");
                    $compare = array_diff($defaults, $files);

                    if (count($compare) == 0) {
                        foreach ($defaults as $file) {
                            @unlink("{$file}");
                        }
                        @unlink("{$ssdir}/.htaccess");
                        @rmdir($ssdir);
                    }
                    //No packages have ever been created
                } else if (is_array($files) && count($files) == 1) {
                    $defaults = array("{$ssdir}/index.php");
                    $compare = array_diff($defaults, $files);
                    if (count($compare) == 0) {
                        @unlink("{$ssdir}/index.php");
                        @rmdir($ssdir);
                    }
                }
            }
        }
		
		//Remove all Settings
		if ($DuplicatorSettings->Get('uninstall_settings')) {
			$DuplicatorSettings->Delete();
		}
		
    }

    /* META LINK ADDONS
      Adds links to the plugins manager page */
    function duplicator_meta_links($links, $file) {
        $plugin = plugin_basename(__FILE__);
        // create link
        if ($file == $plugin) {
            $links[] = '<a href="' . DUPLICATOR_HELPLINK . '" title="' . __('FAQ', 'wpduplicator') . '" target="_blank">' . __('FAQ', 'wpduplicator') . '</a>';
            $links[] = '<a href="' . DUPLICATOR_GIVELINK . '" title="' . __('Partner', 'wpduplicator') . '" target="_blank">' . __('Partner', 'wpduplicator') . '</a>';
            $links[] = '<a href="' . DUPLICATOR_CERTIFIED . '" title="' . __('Approved Hosts', 'wpduplicator') . '"  target="_blank">' . __('Approved Hosts', 'wpduplicator') . '</a>';
            return $links;
        }
        return $links;
    }

    //HOOKS 
    load_plugin_textdomain('wpduplicator', FALSE, dirname(plugin_basename(__FILE__)) . '/lang/');
    register_activation_hook(__FILE__, 'duplicator_activate');
    register_deactivation_hook(__FILE__, 'duplicator_deactivate');
    register_uninstall_hook(__FILE__, 'duplicator_uninstall');

	//ACTIONS
    add_action('plugins_loaded',						'duplicator_update');
    add_action('admin_init',							'duplicator_init');
    add_action('admin_menu',							'duplicator_menu');
    add_action('wp_ajax_duplicator_system_check',		'duplicator_system_check');
    add_action('wp_ajax_duplicator_system_directory',	'duplicator_system_directory');
    add_action('wp_ajax_duplicator_delete',				'duplicator_delete');
    add_action('wp_ajax_duplicator_create',				'duplicator_create');
    add_action('wp_ajax_duplicator_task_save',			'duplicator_task_save');
	add_action('wp_ajax_duplicator_add1_click',			'duplicator_add1_click');
	
	//FILTERS
    add_filter('plugin_action_links',					'duplicator_manage_link', 10, 2);
    add_filter('plugin_row_meta',						'duplicator_meta_links', 10, 2);

    /**
     *  DUPLICATOR_INIT
     *  Init routines  */
    function duplicator_init() {
        /* Register our stylesheet. */
        wp_register_style('jquery-ui', DUPLICATOR_PLUGIN_URL . 'assets/css/jquery-ui.css', null, "1.9.2");
        wp_register_style('duplicator_style', DUPLICATOR_PLUGIN_URL . 'assets/css/style.css');
    }

    /**
     *  DUPLICATOR_VIEWS
     *  Inlcude all visual elements  */
    function duplicator_package_main() {
        include 'inc/pack.main.php';
    }

    //Settings Page
    function duplicator_settings_page() {
        include 'inc/settings.php';
    }

    //Support Page
    function duplicator_support_page() {
        include 'inc/support.php';
    }
	
	//Cleanup Page
    function duplicator_cleanup_page() {
        include 'inc/cleanup.php';
    }

    /**
     *  DUPLICATOR_MENU
     *  Loads the menu item into the WP tools section and queues the actions for only this plugin */
    function duplicator_menu() {
        //Main Menu
        $page_main = add_menu_page('Duplicator', 'Duplicator', "import", basename(__FILE__), 'duplicator_package_main', plugins_url('duplicator/assets/img/create.png'));
        add_submenu_page(basename(__FILE__), __('Packages', 'wpduplicator'), __('Packages', 'wpduplicator'), "import", basename(__FILE__), 'duplicator_package_main');
        //Sub Menus
        $page_settings = add_submenu_page(basename(__FILE__), __('Settings', 'wpduplicator'), __('Settings', 'wpduplicator'), 'import', 'duplicator_settings_page', 'duplicator_settings_page');
        $page_support  = add_submenu_page(basename(__FILE__), __('Support', 'wpduplicator'), __('Support', 'wpduplicator'), 'import', 'duplicator_support_page', 'duplicator_support_page');
		$page_cleanup  = add_submenu_page(basename(__FILE__), __('Cleanup', 'wpduplicator'), '' , 'import', 'duplicator_cleanup_page', 'duplicator_cleanup_page');

        //Apply Scripts
        add_action('admin_print_scripts-' . $page_main, 'duplicator_scripts');
		add_action('admin_print_scripts-' . $page_settings, 'duplicator_scripts');
		add_action('admin_print_scripts-' . $page_support, 'duplicator_scripts');
		add_action('admin_print_scripts-' . $page_cleanup, 'duplicator_scripts');
		
		//Apply Styles
        add_action('admin_print_styles-'  . $page_main, 'duplicator_styles');
        add_action('admin_print_styles-'  . $page_settings, 'duplicator_styles');
		add_action('admin_print_styles-'  . $page_support, 'duplicator_styles');
        add_action('admin_print_styles-'  . $page_cleanup, 'duplicator_styles');
    }

    /**
     *  DUPLICATOR_SCRIPTS
     *  Loads the required javascript libs only for this plugin  */
    function duplicator_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script('jquery-ui-button');
        wp_enqueue_script('jquery-ui-tabs');
		wp_enqueue_script('jquery-ui-accordion');
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
        if (!$this_plugin)
            $this_plugin = plugin_basename(__FILE__);

        if ($file == $this_plugin) {
            $settings_link = '<a href="admin.php?page=duplicator.php">' . __("Manage", 'wpduplicator') . '</a>';
            array_unshift($links, $settings_link);
        }
        return $links;
    }

    //Use WordPress Debugging log file. file is written to wp-content/debug.log
    //trace with tail command to see real-time issues.
    if (!function_exists('duplicator_debug')) {

        function duplicator_debug($message) {
            if (WP_DEBUG === true) {
                if (is_array($message) || is_object($message)) {
                    error_log(print_r($message, true));
                } else {
                    error_log($message);
                }
            }
        }

    }
}
?>