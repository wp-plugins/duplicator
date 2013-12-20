<?php
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/javascript.php'); 
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php'); 

	global $wp_version;
	global $wpdb;
	
	ob_start();
	phpinfo();
	$serverinfo = ob_get_contents();
	ob_end_clean();
	
	$serverinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms',  '$1',  $serverinfo);
	$serverinfo = preg_replace( '%^.*<title>(.*)</title>.*$%ms','$1',  $serverinfo);
	$action_response = null;
	$dbvar_maxtime  = DUP_Util::MysqlVariableValue('wait_timeout');
	$dbvar_maxpacks = DUP_Util::MysqlVariableValue('max_allowed_packet');
	$dbvar_maxtime  = is_null($dbvar_maxtime)  ? __("unknow", 'wpduplicator') : $dbvar_maxtime;
	$dbvar_maxpacks = is_null($dbvar_maxpacks) ? __("unknow", 'wpduplicator') : $dbvar_maxpacks;	

	$space = @disk_total_space(DUPLICATOR_WPROOTPATH);
	$space_free = @disk_free_space(DUPLICATOR_WPROOTPATH);
	$perc = @round((100/$space)*$space_free,2);
	
	$view_state = DUP_UI::GetViewStateArray();
	$ui_css_srv_panel   = ($view_state['dup-settings-diag-srv-panel'])   ? 'display:block' : 'display:none';
	$ui_css_opts_panel  = ($view_state['dup-settings-diag-opts-panel'])   ? 'display:block' : 'display:none';
	
	//POST BACK
	$action_updated = null;
	if (isset($_POST['action'])) {
		switch ($_POST['action']) {
			case 'clear_view_state' : 
				DUP_Settings::DeleteWPOption('duplicator_ui_view_state');		
				$action_response = __('View State Settings Reset', 'wpduplicator');
				break;
			case 'clear_legacy_data': 
				DUP_Settings::LegacyClean();			
				$action_response = __('Legacy Data Removed', 'wpduplicator');
				break;
		}
	} 
?>

<style>
	div#message {margin:0px 0px 10px 0px}
	div#dup-server-info-area { padding:10px 5px;  }
	div#dup-server-info-area table { padding:1px; background:#dfdfdf;  -webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px; width:100% !important; box-shadow:0 8px 6px -6px #777; }
	div#dup-server-info-area td, th {padding:3px; background:#fff; -webkit-border-radius:2px;-moz-border-radius:2px;border-radius:2px;}
	div#dup-server-info-area tr.h img { display:none; }
	div#dup-server-info-area tr.h td{ background:none; }
	div#dup-server-info-area tr.h th{ text-align:center; background-color:#efefef;  }
	div#dup-server-info-area td.e{ font-weight:bold }
	td.dup-settings-diag-header {background-color:#D8D8D8; font-weight: bold; border-style: none; color:black}
	.widefat th {font-weight:bold; }
	.widefat td {padding:2px 2px 2px 8px}
	.widefat td:nth-child(1) {width:10px;}
	.widefat td:nth-child(2) {padding-left: 20px; width:100% !important}
	textarea.dup-opts-read {width:100%; height:65px; font-size:12px}
</style>

<form id="dup-settings-form" action="<?php echo admin_url( 'admin.php?page=duplicator-settings&tab=diagnostics' ); ?>" method="post">
	<?php wp_nonce_field( 'duplicator_settings_page' ); ?>
	<input type="hidden" id="dup-settings-form-action" name="action" value="">
	<br/>

	<?php if (! empty($action_response))  :	?>
		<div id="message" class="updated below-h2"><p><?php echo $action_response; ?></p></div>
	<?php endif; ?>	
		
	<!-- ==============================
	SERVER SETTINGS -->	
	<div class="dup-box">
	<div class="dup-box-title">
		<i class="fa fa-tachometer"></i>
		<?php _e("Server Settings", 'wpduplicator') ?>
		<div class="dup-box-arrow"></div>
	</div>
	<div class="dup-box-panel" id="dup-settings-diag-srv-panel" style="<?php echo $ui_css_srv_panel?>">
		<table class="widefat" cellspacing="0">		   
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
				<td><?php echo DUP_Util::RunAPC() ? 'Yes' : 'No'  ?></td>
			</tr>					   
			<tr>
				<td><?php _e("Root Path", 'wpduplicator'); ?></td>
				<td><?php echo DUPLICATOR_WPROOTPATH ?></td>
			</tr>	
			<tr>
				<td><?php _e("Plugins Path", 'wpduplicator'); ?></td>
				<td><?php echo DUP_Util::SafePath(WP_PLUGIN_DIR) ?></td>
			</tr>
			<tr>
				<td><?php _e("Loaded PHP INI", 'wpduplicator'); ?></td>
				<td><?php echo php_ini_loaded_file () ;?></td>
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
				<td><?php echo WP_MEMORY_LIMIT ?> (<?php _e("Max", 'wpduplicator'); echo '&nbsp;' . WP_MAX_MEMORY_LIMIT; ?>)</td>
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
				 <td><?php echo $perc;?>% -- <?php echo DUP_Util::ByteSize($space_free);?> from <?php echo DUP_Util::ByteSize($space);?><br/>
					  <small>
						  <?php _e("Note: This value is the physical servers hard-drive allocation.", 'wpduplicator'); ?> <br/>
						  <?php _e("On shared hosts check your control panel for the 'TRUE' disk space quota value.", 'wpduplicator'); ?>
					  </small>
				 </td>
			 </tr>	

		</table><br/>

	</div> <!-- end .dup-box-panel -->	
	</div> <!-- end .dup-box -->	
	<br/>

	<!-- ==============================
	OPTIONS DATA -->
	<div class="dup-box">
		<div class="dup-box-title">
			<i class="fa fa-th-list"></i>
			<?php _e("Options Data", 'wpduplicator'); ?>
			<div class="dup-box-arrow"></div>
		</div>
		<div class="dup-box-panel" id="dup-settings-diag-opts-panel" style="<?php echo $ui_css_opts_panel?>">
			<div style="padding:0px 20px 0px 25px">

			<h3 class="title" style="margin-left:-15px"><?php _e("Reset/Remove", 'wpduplicator') ?> </h3>	

			<b><a href="javascript:void(0)" onclick="Duplicator.Settings.DeleteViewState()"><?php _e("Clear View State", 'wpduplicator'); ?></a></b> &nbsp; 	
			<small><?php _e("This will enable all notice messages again and reset any view state.", 'wpduplicator'); ?></small> <br/>

			<b><a href="javascript:void(0)" onclick="Duplicator.Settings.DeleteLegacy()"><?php _e("Clear Legacy Data", 'wpduplicator'); ?></a></b> &nbsp; 	
			<small><?php _e("This will remove all legacy plugin settings prior to version", 'wpduplicator'); ?> [<?php echo DUPLICATOR_VERSION ?>].</small> <br/><br/>

			
			<h3 class="title" style="margin-left:-15px"><?php _e("Details", 'wpduplicator') ?> </h3>	
			<table class="widefat" cellspacing="0">		
				<tr>
					<th>Name</th>
					<th>Value</th>
				</tr>		
				<?php 
					$sql = "SELECT * FROM `{$wpdb->prefix}options` WHERE  `option_name` LIKE  '%duplicator_%' ORDER BY option_name";
					foreach( $wpdb->get_results("{$sql}") as $key => $row) { ?>	
					<tr>
						<td><?php echo $row->option_name?></td>
						<td><textarea class="dup-opts-read" readonly="readonly"><?php echo $row->option_value?></textarea></td>
					</tr>
				<?php } ?>	
			</table>
			</div>

		</div> <!-- end .dup-box-panel -->	
	</div> <!-- end .dup-box -->	
	<br/>
	
	<!-- ==============================
	PHP INFORMATION -->
	<div class="dup-box">
		<div class="dup-box-title">
			<i class="fa fa-info-circle"></i>
			<?php _e("PHP Information", 'wpduplicator'); ?>
			<div class="dup-box-arrow"></div>
		</div>
		<div class="dup-box-panel" style="display:none">	

		<div id="dup-phpinfo" style="width:95%">
			<?php 	echo "<div id='dup-server-info-area'>{$serverinfo}</div>"; ?>
		</div><br/>	

		</div> <!-- end .dup-box-panel -->	
	</div> <!-- end .dup-box -->	

</form>

<script>	
jQuery(document).ready(function($) {
	
	/*  ----------------------------------------
	*  METHOD:   */
   Duplicator.Settings.DeleteLegacy = function () {

	   <?php
		   $msg  = __('This action will remove all legacy settings prior to version %1$s.  ', 'wpduplicator');
		   $msg .= __('Legacy settings are only needed if you plan to migrate back to an older version of this plugin.', 'wpduplicator'); 
	   ?>
	   var result = true;
	   var result = confirm('<?php printf(__($msg, 'wpduplicator'), DUPLICATOR_VERSION) ?>');
	   if (! result) 
		   return;
		
	   jQuery('#dup-settings-form-action').val('clear_legacy_data');
	   jQuery('#dup-settings-form').submit();
   }
   
   
   Duplicator.Settings.DeleteViewState = function () {

		var result = confirm('<?php _e("Delete the view state settings?", "wpduplicator"); ?>');
		if (! result) 
			return;
		
	   jQuery('#dup-settings-form-action').val('clear_view_state');
	   jQuery('#dup-settings-form').submit();
			

		
	}
   
   
   
	
});	
</script>

