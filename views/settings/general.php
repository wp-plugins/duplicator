<?php
	global $wp_version;
	global $wpdb;
	
	$action_updated = null;
	$action_response = __("Settings Saved", 'wpduplicator');
	if (isset($_POST['action']) && $_POST['action'] == 'save') {
		//General Tab
		DUP_Settings::Set('uninstall_settings',		isset($_POST['uninstall_settings']) ? "1" : "0");
		DUP_Settings::Set('uninstall_files',		isset($_POST['uninstall_files'])  ? "1" : "0");
		DUP_Settings::Set('uninstall_tables',		isset($_POST['uninstall_tables']) ? "1" : "0");
		DUP_Settings::Set('package_skip_scanner',	isset($_POST['package_skip_scanner']) ? "1" : "0");
		
		$action_updated  = DUP_Settings::Save();
	} 

	$uninstall_settings		= DUP_Settings::Get('uninstall_settings');
	$uninstall_files		= DUP_Settings::Get('uninstall_files');
	$uninstall_tables		= DUP_Settings::Get('uninstall_tables');
	$package_skip_scanner	= DUP_Settings::Get('package_skip_scanner');

?>

<form id="dup-settings-form" action="<?php echo admin_url( 'admin.php?page=duplicator-settings&tab=general' ); ?>" method="post">
	
	<?php wp_nonce_field( 'duplicator_settings_page' ); ?>
	<input type="hidden" name="action" value="save">
	<input type="hidden" name="page"   value="duplicator-settings">

	<?php if($action_updated)  :	?>
		<div id="message" class="updated below-h2"><p><?php echo $action_response; ?></p></div>
	<?php endif; ?>	
	
	
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
				<p class="description"><?php _e("Snapshot Directory", 'wpduplicator'); ?>: <?php echo DUP_Util::SafePath(DUPLICATOR_SSDIR_PATH); ?></p>
			</td>
		</tr>
	</table>
	
	
	<h3 class="title"><?php _e("Package Settings", 'wpduplicator') ?> </h3>
	
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><label><?php _e("Auto Skip Scanner", 'wpduplicator'); ?></label></th>
			<td>
				<input type="checkbox" name="package_skip_scanner" id="package_skip_scanner" <?php echo ($package_skip_scanner) ? 'checked="checked"' : ''; ?> />
				<label for="package_skip_scanner"><?php _e("Skip Scanner Step", 'wpduplicator'); ?></label>
			</td>
		</tr>
	</table>

	<p class="submit" style="margin: 20px 0px 0xp 5px;">
		<div style="border-top: 1px solid #efefef"></div><br/>
		<input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e("Save Settings", 'wpduplicator') ?>" style="display: inline-block;"/>
	</p>
</form>