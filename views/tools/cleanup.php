<?php
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/javascript.php'); 
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php'); 

	$_GET['action'] = isset($_GET['action']) ? $_GET['action'] : 'display';
	switch ($_GET['action']) {
		case 'installer' : 	
			$action_response = __('Installer File Cleanup Ran.', 'wpduplicator');		
			break;		
		case 'legacy': 
			DUP_Settings::LegacyClean();			
			$action_response = __('Legacy data removed.', 'wpduplicator');
			break;
	} 
	
?>

<style type="text/css">
	div.success {color:#4A8254}
	div.failed {color:red}
	table.dup-reset-opts td:first-child {font-weight: bold}
	table.dup-reset-opts td {padding:4px}
	form#dup-settings-form {padding: 0px 10px 0px 10px}
</style>


<form id="dup-settings-form" action="?page=duplicator-tools&tab=cleanup" method="post">
	<?php wp_nonce_field( 'duplicator_cleanup_page' ); ?>
	
	<?php if ($_GET['action'] != 'display')  :	?>
		<div id="message" class="updated below-h2">
			<p><?php echo $action_response; ?></p>
			<?php if ( $_GET['action'] == 'installer') :  ?>
			
			<?php	
				$html = "";
				$installer_file 	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP;
				$installer_sql  	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL;
				$installer_log  	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG;
				$package_name   	= (isset($_GET['package'])) ? DUPLICATOR_WPROOTPATH . esc_html($_GET['package']) : '';
				$html .= (@unlink($installer_file)) ?  "<div class='success'>Successfully removed {$installer_file}</div>"	:  "<div class='failed'>Does not exsist or unable to remove file: {$installer_file}</div>";
				$html .= (@unlink($installer_sql))  ?  "<div class='success'>Successfully removed {$installer_sql}</div>"  	:  "<div class='failed'>Does not exsist or unable to remove file: {$installer_sql}</div>";
				$html .= (@unlink($installer_log))  ?  "<div class='success'>Successfully removed {$installer_log}</div>"	:  "<div class='failed'>Does not exsist or unable to remove file: {$installer_log}</div>";

				$path_parts = pathinfo($package_name);
				$path_parts = (isset($path_parts['extension'])) ? $path_parts['extension'] : '';
				if ($path_parts  == "zip"  && ! is_dir($package_name)) {
					$html .= (@unlink($package_name))   
						?  "<div class='success'>Successfully removed {$package_name}</div>"   
						:  "<div class='failed'>Does not exsist or unable to remove file: {$package_name}</div>";
				} else {
					$html .= "<div class='failed'>Does not exsist or unable to remove file '{$package_name}'.  Validate that an archive file exists.</div>";
				}
				echo $html;
			 ?>
			
			<i> <br/>
			 <?php _e('If the installer files did not successfully get removed, then you WILL need to remove them manually', 'wpduplicator')?>. <br/>
			 <?php _e('Please remove all installer files to avoid leaving open security issues on your server', 'wpduplicator')?>. <br/><br/>
			</i>
			
		<?php endif ?>
		</div>
	<?php endif; ?>	
	

	<h3><?php _e('Data Cleanup', 'wpduplicator')?><hr size="1"/></h3>
	<table class="dup-reset-opts">
		<tr>
			<td><a href="?page=duplicator-tools&tab=cleanup&action=installer"><?php _e("Delete Reserved Files", 'wpduplicator'); ?></a></td>
			<td><?php _e("Removes all installer files from a previous install", 'wpduplicator'); ?></td>
		</tr>
		<tr>
			<td><a href="javascript:void(0)" onclick="Duplicator.Settings.DeleteLegacy()"><?php _e("Delete Legacy Data", 'wpduplicator'); ?></a></td>
			<td><?php _e("Removes all legacy data and settings prior to version", 'wpduplicator'); ?> [<?php echo DUPLICATOR_VERSION ?>].</td>
		</tr>				
	</table>

	
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
		
	   window.location = '?page=duplicator-tools&tab=cleanup&action=legacy';
   }
  
	
});	
</script>

