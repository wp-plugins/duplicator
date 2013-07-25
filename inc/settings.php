<?php
	require_once('javascript.php'); 
	require_once('inc.header.php'); 

	ob_start();
	phpinfo();
	$serverinfo = ob_get_contents();
	ob_end_clean();
	
	$serverinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms',  '$1',  $serverinfo);
	$serverinfo = preg_replace( '%^.*<title>(.*)</title>.*$%ms','$1',  $serverinfo);
	$action_response = __("Settings Saved", 'wpduplicator');
	$dbvar_maxtime  = DuplicatorUtils::MysqlVariableValue('wait_timeout');
	$dbvar_maxpacks = DuplicatorUtils::MysqlVariableValue('max_allowed_packet');
	$dbvar_maxtime  = is_null($dbvar_maxtime)  ? __("unknow", 'wpduplicator') : $dbvar_maxtime;
	$dbvar_maxpacks = is_null($dbvar_maxpacks) ? __("unknow", 'wpduplicator') : $dbvar_maxpacks;	

	global $DuplicatorSettings;
	global $wp_version;
	global $wpdb;
	
	$action_updated = null;
	if (isset($_POST['action']) && $_POST['action'] == 'save') {
		//General Tab
		$DuplicatorSettings->Set('uninstall_settings',	isset($_POST['uninstall_settings']) ? "1" : "0");
		$DuplicatorSettings->Set('uninstall_files',		isset($_POST['uninstall_files'])  ? "1" : "0");
		$DuplicatorSettings->Set('uninstall_tables',	isset($_POST['uninstall_tables']) ? "1" : "0");
		
		$action_updated  = $DuplicatorSettings->Save();
	} 

	$uninstall_settings	= $DuplicatorSettings->Get('uninstall_settings');
	$uninstall_files	= $DuplicatorSettings->Get('uninstall_files');
	$uninstall_tables	= $DuplicatorSettings->Get('uninstall_tables');
	
	$space = @disk_total_space(DUPLICATOR_WPROOTPATH);
	$space_free = @disk_free_space(DUPLICATOR_WPROOTPATH);
	$perc = @round((100/$space)*$space_free,2);

?>

<div class="wrap">
	<!-- h2 required here for general system messages -->
	<h2 style='display:none'></h2>
	<?php duplicator_header(__("Settings", 'wpduplicator') ) ?>
		
	<form id="dup-settings-form" action="<?php echo admin_url( 'admin.php?page=duplicator_settings_page' ); ?>" method="post">
		<?php wp_nonce_field( 'duplicator_settings_page' ); ?>
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="page"   value="duplicator_settings_page">
		<input type="hidden" name="anchor" value="#dup-tab-general">
		
		<h2 class="nav-tab-wrapper">
			<a href="#dup-tab-general" class="nav-tab"><?php _e("General", 'wpduplicator'); ?></a>
			<a href="#dup-tab-diagnostics" class="nav-tab"><?php _e("Diagnostics", 'wpduplicator'); ?></a>
		</h2>
		
		<?php if($action_updated)  :	?>
			<div id="message" class="updated below-h2"><p><?php echo $action_response; ?></p></div>
		<?php endif; ?>	
		
		
		<div class="dup-nav-tab-contents">
			
			<!-- =============================================================================
			TAB GENERAL-->
		   <div class="ui-tabs ui-tabs-hide" id="dup-tab-general">
				<h3 class="title"><?php _e("Plugin Settings", 'wpduplicator') ?> </h3>

				<table class="form-table">
					<tr valign="top">
						<th scope="row"><label><?php _e("Duplicator Version", 'wpduplicator'); ?></label></th>
						<td><?php echo DUPLICATOR_VERSION ?></td>
					</tr>
					<tr valign="top">
						<th scope="row"><label><?php _e("Uninstall Options", 'wpduplicator'); ?></label></th>
						<td>
							<input type="checkbox" name="uninstall_settings" id="uninstall_settings" <?php echo ($uninstall_settings) ? 'checked="checked"' : ''; ?> /> 
							<label for="uninstall_settings"><?php _e("Delete Plugin Settings", 'wpduplicator') ?></label><br/>
							
							<input type="checkbox" name="uninstall_files" id="uninstall_files" <?php echo ($uninstall_files) ? 'checked="checked"' : ''; ?> /> 
							<label for="uninstall_files"><?php _e("Delete Entire Snapshot Directory", 'wpduplicator') ?></label><br/>
							<p class="description"><?php _e("Snapshot Directory", 'wpduplicator'); ?>: <?php echo duplicator_safe_path(DUPLICATOR_SSDIR_PATH); ?></p>
							
						</td>
					</tr>
				</table>
				
			
			<p class="submit" style="margin: 20px 0px 0xp 5px;">
				<div style="border-top: 1px solid #efefef"></div><br/>
				<input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e("Save Settings", 'wpduplicator') ?>" style="display: inline-block;"/>
			</p>
				
				
		   </div>

			<!-- =============================================================================
			TAB DIAGNOSTICS -->
		   <div class="ui-tabs ui-tabs-hide" id="dup-tab-diagnostics">

			   <h3 class="title"><?php _e("Server Settings", 'wpduplicator') ?> </h3>				
			   
			   <table class="wp-list-table widefat fixed" cellspacing="0" style="width: 95%; margin-left: 10px">
				   <thead>
					   <tr>
						   <th width="15%"><?php _e("Setting", 'wpduplicator'); ?></th>
						   <th><?php _e("Value", 'wpduplicator'); ?></th>
					   </tr>
				   </thead>
				   <tbody>				   
					   <tr>
						   <td class='dup-settings-diag-header' colspan="2"><?php _e("General", 'wpduplicator'); ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("Duplicator Version", 'wpduplicator'); ?></td>
						   <td><?php echo DUPLICATOR_VERSION ?></td>
					   </tr>	
					   <tr>
						   <td><?php _e("Operating System", 'wpduplicator'); ?></td>
						   <td><?php echo PHP_OS ?></td>
					   </tr>					   
					   <tr>
						   <td><?php _e("Web Server", 'wpduplicator'); ?></td>
						   <td><?php echo $_SERVER['SERVER_SOFTWARE'] ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("APC Enabled", 'wpduplicator'); ?></td>
						   <td><?php echo duplicator_run_apc() ? 'Yes' : 'No'  ?></td>
					   </tr>					   
					   <tr>
						   <td><?php _e("Root Path", 'wpduplicator'); ?></td>
						   <td><?php echo DUPLICATOR_WPROOTPATH ?></td>
					   </tr>	
					   <tr>
						   <td><?php _e("Plugins Path", 'wpduplicator'); ?></td>
						   <td><?php echo duplicator_safe_path(WP_PLUGIN_DIR) ?></td>
					   </tr>	
					   <tr>
						   <td><?php _e("Packages Built", 'wpduplicator'); ?></td>
						   <td>
							   <?php echo get_option('duplicator_pack_passcount', 0) ?> &nbsp;
							    <i style="font-size:11px"><?php _e("The number of successful packages created.", 'wpduplicator'); ?></i>
						   </td>
					   </tr>	
					   <tr>
						   <td class='dup-settings-diag-header' colspan="2">WordPress</td>
					   </tr>
					   <tr>
						   <td><?php _e("Version", 'wpduplicator'); ?></td>
						   <td><?php echo $wp_version ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("Langugage", 'wpduplicator'); ?></td>
						   <td><?php echo get_bloginfo('language') ?></td>
					   </tr>	
					   <tr>
						   <td><?php _e("Charset", 'wpduplicator'); ?></td>
						   <td><?php echo get_bloginfo('charset') ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("Memory Limit ", 'wpduplicator'); ?></td>
						   <td><?php echo WP_MEMORY_LIMIT ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("Memory Limit Max", 'wpduplicator'); ?></td>
						   <td><?php echo WP_MAX_MEMORY_LIMIT ?></td>
					   </tr>							   
					   <tr>
						   <td class='dup-settings-diag-header' colspan="2">PHP</td>
					   </tr>
					   <tr>
						   <td><?php _e("Version", 'wpduplicator'); ?></td>
						   <td><?php echo phpversion() ?></td>
					   </tr>	
					   <tr>
						   <td>SAPI</td>
						   <td><?php echo PHP_SAPI ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("User", 'wpduplicator'); ?></td>
						   <td><?php echo get_current_user(); ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("Safe Mode", 'wpduplicator'); ?></td>
						   <td>
						   <?php echo (((strtolower(@ini_get('safe_mode')) == 'on')	  ||  (strtolower(@ini_get('safe_mode')) == 'yes') || 
									    (strtolower(@ini_get('safe_mode')) == 'true') ||  (ini_get("safe_mode") == 1 )))  
										? __('On', 'wpduplicator') : __('Off', 'wpduplicator'); 
						   ?>
						   </td>
					   </tr>
					   <tr>
						   <td><?php _e("Memory Limit", 'wpduplicator'); ?></td>
						   <td><?php echo @ini_get('memory_limit') ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("Memory In Use", 'wpduplicator'); ?></td>
						   <td><?php echo size_format(@memory_get_usage(TRUE), 2) ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("Max Execution Time", 'wpduplicator'); ?></td>
						   <td><?php echo @ini_get( 'max_execution_time' ); ?></td>
					   </tr>
					   <tr>
						   <td class='dup-settings-diag-header' colspan="2">MySQL</td>
					   </tr>					   
					   <tr>
						   <td><?php _e("Version", 'wpduplicator'); ?></td>
						   <td><?php echo $wpdb->db_version() ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("Charset", 'wpduplicator'); ?></td>
						   <td><?php echo DB_CHARSET ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("wait_timeout", 'wpduplicator'); ?></td>
						   <td><?php echo $dbvar_maxtime ?></td>
					   </tr>
					   <tr>
						   <td><?php _e("max_allowed_packet", 'wpduplicator'); ?></td>
						   <td><?php echo $dbvar_maxpacks ?></td>
					   </tr>
						<tr>
							<td class='dup-settings-diag-header' colspan="2"><?php _e("Server Disk", 'wpduplicator'); ?></td>
						</tr>
						<tr valign="top">
							<td><?php _e('Free space', 'hyper-cache'); ?></td>
							<td><?php echo $perc;?>% -- <?php echo duplicator_bytesize($space_free);?> from <?php echo duplicator_bytesize($space);?><br/>
								 <small>
									 <?php _e("Note: This value is the physical servers hard-drive allocation.", 'wpduplicator'); ?> <br/>
									 <?php _e("On shared hosts check your control panel for the 'TRUE' disk space quota value.", 'wpduplicator'); ?>
									  
								 </small>
							</td>
						</tr>						   
				   </tbody>
			   </table><br/>

			   <a href="javascript:void(0)" onclick="jQuery('#dup-phpinfo').toggle()" style="font-size:14px; font-weight: bold"><i><?php _e("Show/Hide All PHP Information", 'wpduplicator') ?></i></a>
			   <div id="dup-phpinfo" style="display:none; width:95%">
				   <?php 	echo "<div id='dup-server-info-area'>{$serverinfo}</div>"; ?>
			   </div><br/>	
		   </div>

		</div>
				
	</form>
</div>