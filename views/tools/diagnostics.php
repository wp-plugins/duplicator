<?php
	require_once(DUPLICATOR_PLUGIN_PATH . '/assets/js/javascript.php'); 
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
	$dbvar_maxtime  = is_null($dbvar_maxtime)  ? __("unknow", 'duplicator') : $dbvar_maxtime;
	$dbvar_maxpacks = is_null($dbvar_maxpacks) ? __("unknow", 'duplicator') : $dbvar_maxpacks;	

	$space = @disk_total_space(DUPLICATOR_WPROOTPATH);
	$space_free = @disk_free_space(DUPLICATOR_WPROOTPATH);
	$perc = @round((100/$space)*$space_free,2);
	$mysqldumpPath = DUP_Database::GetMySqlDumpPath();
	$mysqlDumpSupport = ($mysqldumpPath) ? $mysqldumpPath : 'Path Not Found';
	
	$view_state = DUP_UI::GetViewStateArray();
	$ui_css_srv_panel   = (isset($view_state['dup-settings-diag-srv-panel'])  && $view_state['dup-settings-diag-srv-panel'])   ? 'display:block' : 'display:none';
	$ui_css_opts_panel  = (isset($view_state['dup-settings-diag-opts-panel']) && $view_state['dup-settings-diag-opts-panel'])  ? 'display:block' : 'display:none';
	

	$client_ip_address = DUP_Server::GetClientIP();
	
	//POST BACK
	$action_updated = null;
	if (isset($_POST['action'])) {
		$action_result = DUP_Settings::DeleteWPOption($_POST['action']);
		switch ($_POST['action']) {
			case 'duplicator_settings'		 : 	$action_response = __('Plugin settings reset.', 'duplicator');		break;
			case 'duplicator_ui_view_state'  : 	$action_response = __('View state settings reset.', 'duplicator');	 break;
			case 'duplicator_package_active' : 	$action_response = __('Active package settings reset.', 'duplicator'); break;		
			case 'clear_legacy_data': 
				DUP_Settings::LegacyClean();			
				$action_response = __('Legacy data removed.', 'duplicator');
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
	textarea.dup-opts-read {width:100%; height:40px; font-size:12px}
</style>

<form id="dup-settings-form" action="<?php echo admin_url( 'admin.php?page=duplicator-tools&tab=diagnostics' ); ?>" method="post">
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
		<?php _e("Server Settings", 'duplicator') ?>
		<div class="dup-box-arrow"></div>
	</div>
	<div class="dup-box-panel" id="dup-settings-diag-srv-panel" style="<?php echo $ui_css_srv_panel?>">
		<table class="widefat" cellspacing="0">		   
			<tr>
				<td class='dup-settings-diag-header' colspan="2"><?php _e("General", 'duplicator'); ?></td>
			</tr>
			<tr>
				<td><?php _e("Duplicator Version", 'duplicator'); ?></td>
				<td><?php echo DUPLICATOR_VERSION ?></td>
			</tr>
			<tr>
				<td><?php _e("Operating System", 'duplicator'); ?></td>
				<td><?php echo PHP_OS ?></td>
			</tr>
			<tr>
				<td><?php _e("Timezone", 'duplicator'); ?></td>
				<td><?php echo  date_default_timezone_get() ; ?> &nbsp; <small><i>This is a <a href='options-general.php'>WordPress setting</a></i></small></td>
			</tr>	
			<tr>
				<td><?php _e("Server Time", 'duplicator'); ?></td>
				<td><?php echo date("Y-m-d H:i:s"); ?></td>
			</tr>				   
			<tr>
				<td><?php _e("Web Server", 'duplicator'); ?></td>
				<td><?php echo $_SERVER['SERVER_SOFTWARE'] ?></td>
			</tr>
			<tr>
				<td><?php _e("APC Enabled", 'duplicator'); ?></td>
				<td><?php echo DUP_Util::RunAPC() ? 'Yes' : 'No'  ?></td>
			</tr>					   
			<tr>
				<td><?php _e("Root Path", 'duplicator'); ?></td>
				<td><?php echo DUPLICATOR_WPROOTPATH ?></td>
			</tr>	
			<tr>
				<td><?php _e("ABSPATH", 'duplicator'); ?></td>
				<td><?php echo ABSPATH ?></td>
			</tr>			
			<tr>
				<td><?php _e("Plugins Path", 'duplicator'); ?></td>
				<td><?php echo DUP_Util::SafePath(WP_PLUGIN_DIR) ?></td>
			</tr>
			<tr>
				<td><?php _e("Loaded PHP INI", 'duplicator'); ?></td>
				<td><?php echo php_ini_loaded_file() ;?></td>
			</tr>	
			<tr>
				<td><?php _e("Server IP", 'duplicator'); ?></td>
				<td><?php echo $_SERVER['SERVER_ADDR'];?></td>
			</tr>	
			<tr>
				<td><?php _e("Client IP", 'duplicator'); ?></td>
				<td><?php echo $client_ip_address;?></td>
			</tr>
			<tr>
				<td class='dup-settings-diag-header' colspan="2">WordPress</td>
			</tr>
			<tr>
				<td><?php _e("Version", 'duplicator'); ?></td>
				<td><?php echo $wp_version ?></td>
			</tr>
			<tr>
				<td><?php _e("Language", 'duplicator'); ?></td>
				<td><?php echo get_bloginfo('language') ?></td>
			</tr>	
			<tr>
				<td><?php _e("Charset", 'duplicator'); ?></td>
				<td><?php echo get_bloginfo('charset') ?></td>
			</tr>
			<tr>
				<td><?php _e("Memory Limit ", 'duplicator'); ?></td>
				<td><?php echo WP_MEMORY_LIMIT ?> (<?php _e("Max", 'duplicator'); echo '&nbsp;' . WP_MAX_MEMORY_LIMIT; ?>)</td>
			</tr>
			<tr>
				<td class='dup-settings-diag-header' colspan="2">PHP</td>
			</tr>
			<tr>
				<td><?php _e("Version", 'duplicator'); ?></td>
				<td><?php echo phpversion() ?></td>
			</tr>	
			<tr>
				<td>SAPI</td>
				<td><?php echo PHP_SAPI ?></td>
			</tr>
			<tr>
				<td><?php _e("User", 'duplicator'); ?></td>
				<td><?php echo DUP_Util::GetCurrentUser(); ?></td>
			</tr>
			<tr>
				<td><?php _e("Process", 'duplicator'); ?></td>
				<td><?php echo DUP_Util::GetProcessOwner(); ?></td>
			</tr>
			<tr>
				<td><a href="http://php.net/manual/en/features.safe-mode.php" target="_blank"><?php _e("Safe Mode", 'duplicator'); ?></a></td>
				<td>
				<?php echo (((strtolower(@ini_get('safe_mode')) == 'on')	  ||  (strtolower(@ini_get('safe_mode')) == 'yes') || 
							 (strtolower(@ini_get('safe_mode')) == 'true') ||  (ini_get("safe_mode") == 1 )))  
							 ? __('On', 'duplicator') : __('Off', 'duplicator'); 
				?>
				</td>
			</tr>
			<tr>
				<td><a href="http://www.php.net/manual/en/ini.core.php#ini.memory-limit" target="_blank"><?php _e("Memory Limit", 'duplicator'); ?></a></td>
				<td><?php echo @ini_get('memory_limit') ?></td>
			</tr>
			<tr>
				<td><?php _e("Memory In Use", 'duplicator'); ?></td>
				<td><?php echo size_format(@memory_get_usage(TRUE), 2) ?></td>
			</tr>
			<tr>
				<td><a href="http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank"><?php _e("Max Execution Time", 'duplicator'); ?></a></td>
				<td><?php echo @ini_get( 'max_execution_time' ); ?></td>
			</tr>
			<tr>
				<td><a href="http://us3.php.net/shell_exec" target="_blank"><?php _e("Shell Exec", 'duplicator'); ?></a></td>
				<td><?php echo (DUP_Util::IsShellExecAvailable()) ? _e("Is Supported", 'duplicator') : _e("Not Supported", 'duplicator'); ?></td>
			</tr>            
            <tr>
				<td><?php _e("Shell Exec Zip", 'duplicator'); ?></td>
				<td><?php echo (DUP_Util::GetZipPath() != null) ? _e("Is Supported", 'duplicator') : _e("Not Supported", 'duplicator'); ?></td>
			</tr>
			<tr>
				<td class='dup-settings-diag-header' colspan="2">MySQL</td>
			</tr>					   
			<tr>
				<td><?php _e("Version", 'duplicator'); ?></td>
				<td><?php echo $wpdb->db_version() ?></td>
			</tr>
			<tr>
				<td><?php _e("Charset", 'duplicator'); ?></td>
				<td><?php echo DB_CHARSET ?></td>
			</tr>
			<tr>
				<td><a href="http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_wait_timeout" target="_blank"><?php _e("Wait Timeout", 'duplicator'); ?></a></td>
				<td><?php echo $dbvar_maxtime ?></td>
			</tr>
			<tr>
				<td style="white-space:nowrap"><a href="http://dev.mysql.com/doc/refman/5.0/en/server-system-variables.html#sysvar_max_allowed_packet" target="_blank"><?php _e("Max Allowed Packets", 'duplicator'); ?></a></td>
				<td><?php echo $dbvar_maxpacks ?></td>
			</tr>
			<tr>
				<td><a href="http://dev.mysql.com/doc/refman/5.0/en/mysqldump.html" target="_blank"><?php _e("msyqldump Path", 'duplicator'); ?></a></td>
				<td><?php echo $mysqlDumpSupport ?></td>
			</tr>
			 <tr>
				 <td class='dup-settings-diag-header' colspan="2"><?php _e("Server Disk", 'duplicator'); ?></td>
			 </tr>
			 <tr valign="top">
				 <td><?php _e('Free space', 'hyper-cache'); ?></td>
				 <td><?php echo $perc;?>% -- <?php echo DUP_Util::ByteSize($space_free);?> from <?php echo DUP_Util::ByteSize($space);?><br/>
					  <small>
						  <?php _e("Note: This value is the physical servers hard-drive allocation.", 'duplicator'); ?> <br/>
						  <?php _e("On shared hosts check your control panel for the 'TRUE' disk space quota value.", 'duplicator'); ?>
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
			<?php _e("Stored Data", 'duplicator'); ?>
			<div class="dup-box-arrow"></div>
		</div>
		<div class="dup-box-panel" id="dup-settings-diag-opts-panel" style="<?php echo $ui_css_opts_panel?>">
			<div style="padding:0px 20px 0px 25px">
				<h3 class="title" style="margin-left:-15px"><?php _e("Options Values", 'duplicator') ?> </h3>	

				<table class="widefat" cellspacing="0">		
					<tr>
						<th>Key</th>
						<th>Value</th>
					</tr>		
					<?php 
						$sql = "SELECT * FROM `{$wpdb->prefix}options` WHERE  `option_name` LIKE  '%duplicator_%' ORDER BY option_name";
						foreach( $wpdb->get_results("{$sql}") as $key => $row) { ?>	
						<tr>
							<td>
								<?php 
									 echo (in_array($row->option_name, $GLOBALS['DUPLICATOR_OPTS_DELETE']))
										? "<a href='javascript:void(0)' onclick='Duplicator.Settings.DeleteOption(this)'>{$row->option_name}</a>"
										: $row->option_name;
								?>
							</td>
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
			<?php _e("PHP Information", 'duplicator'); ?>
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
	
	Duplicator.Settings.DeleteOption = function (anchor) {
		var key = $(anchor).text();
		var result = confirm('<?php _e("Delete this option value", "duplicator"); ?> [' + key + '] ?');
		if (! result) 	return;
		
		jQuery('#dup-settings-form-action').val(key);
		jQuery('#dup-settings-form').submit();
	}
	
});	
</script>

