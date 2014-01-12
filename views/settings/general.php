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
		DUP_Settings::Set('package_debug',			isset($_POST['package_debug']) ? "1" : "0");
		DUP_Settings::Set('package_zip_flush',		isset($_POST['package_zip_flush']) ? "1" : "0");
		DUP_Settings::Set('package_mysqldump',		isset($_POST['package_mysqldump']) ? "1" : "0");
		DUP_Settings::Set('package_mysqldump_path',	trim($_POST['package_mysqldump_path']));
		
		$action_updated  = DUP_Settings::Save();
	} 

	$uninstall_settings		= DUP_Settings::Get('uninstall_settings');
	$uninstall_files		= DUP_Settings::Get('uninstall_files');
	$uninstall_tables		= DUP_Settings::Get('uninstall_tables');
	$package_skip_scanner	= DUP_Settings::Get('package_skip_scanner');
	$package_debug			= DUP_Settings::Get('package_debug');
	$package_zip_flush		= DUP_Settings::Get('package_zip_flush');
	$package_mysqldump		= DUP_Settings::Get('package_mysqldump');
	$package_mysqldump_path	= trim(DUP_Settings::Get('package_mysqldump_path'));
	
	
	$mysqlDumpPath = DUP_Database::GetMySqlDumpPath();
	$mysqlDumpFound = ($mysqlDumpPath) ? true : false;

?>

<style>
	form#dup-settings-form input[type=text] {width: 400px; }
	input#package_mysqldump_path_found {margin-top:5px}
	div.dup-mysql-dump-found {padding:3px; border:1px solid silver; background: #f7fcfe; border-radius: 3px; width:400px; font-size: 12px}
	div.dup-mysql-dump-notfound {padding:3px; border:1px solid silver; background: #fcf3ef; border-radius: 3px; width:400px; font-size: 12px}
</style>

<form id="dup-settings-form" action="<?php echo admin_url( 'admin.php?page=duplicator-settings&tab=general' ); ?>" method="post">
	
	<?php wp_nonce_field( 'duplicator_settings_page' ); ?>
	<input type="hidden" name="action" value="save">
	<input type="hidden" name="page"   value="duplicator-settings">

	<?php if($action_updated)  :	?>
		<div id="message" class="updated below-h2"><p><?php echo $action_response; ?></p></div>
	<?php endif; ?>	
	
	
	<!-- ===============================
	PLUG-IN SETTINGS -->
	<h3 class="title"><?php _e("Plugin", 'wpduplicator') ?> </h3>
	<hr size="1" />
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
	
	
	<!-- ===============================
	PACKAGE SETTINGS -->
	<h3 class="title"><?php _e("Package", 'wpduplicator') ?> </h3>
	<hr size="1" />
	<table class="form-table">
		<tr>
			<th scope="row"><label><?php _e("Auto Skip Scanner", 'wpduplicator'); ?></label></th>
			<td>
				<input type="checkbox" name="package_skip_scanner" id="package_skip_scanner" <?php echo ($package_skip_scanner) ? 'checked="checked"' : ''; ?> />
				<label for="package_skip_scanner"><?php _e("Skip Scanner Step", 'wpduplicator'); ?></label>
				<p class="description">
					<?php _e("Keeps the 'Skip Scan (step 2)' option checked.", 'wpduplicator'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label><?php _e("Archive Flush", 'wpduplicator'); ?></label></th>
			<td>
				<input type="checkbox" name="package_zip_flush" id="package_zip_flush" <?php echo ($package_zip_flush) ? 'checked="checked"' : ''; ?> />
				<label for="package_zip_flush"><?php _e("Attempt Network Keep Alive", 'wpduplicator'); ?></label>
				<i style="font-size:12px">(<?php _e("recommended only for large archives", 'wpduplicator'); ?>)</i> 
				<p class="description">
					<?php _e("This will attempt to keep a network connection established for large archives.", 'wpduplicator'); ?>
				</p>
			</td>
		</tr>		
		<tr>
			<th scope="row"><label><?php _e("Database Build", 'wpduplicator'); ?></label></th>
			<td>
				
				<?php if (! DUP_Util::IsShellExecAvailable()) :?>
					<p class="description">
						<?php 
							_e("This server does not have shell_exec configured to run.", 'wpduplicator'); echo '<br/>';
							_e("Please contact the server administrator to enable this feature.", 'wpduplicator'); 
						?>
					</p>
				<?php else : ?>
					<input type="checkbox" name="package_mysqldump" id="package_mysqldump" <?php echo ($package_mysqldump) ? 'checked="checked"' : ''; ?> />
					<label for="package_mysqldump"><?php _e("Use mysqldump", 'wpduplicator'); ?></label> &nbsp;
					<i style="font-size:12px">(<?php _e("recommended for large databases", 'wpduplicator'); ?>)</i> <br/><br/>
					
					<div style="margin:5px 0px 0px 25px">
						<?php if ($mysqlDumpFound) :?>
							<div class="dup-mysql-dump-found">
								<?php _e("Working Path:", 'wpduplicator'); ?> &nbsp;
								<i><?php echo $mysqlDumpPath ?></i>
							</div><br/>
						<?php else : ?>
							<div class="dup-mysql-dump-notfound">
								<?php 
									_e('Mysqldump was not found at its default location or the location provided.  Please enter a path to a valid location where mysqldump can run.  If the problem persist contact your server administrator.', 'wpduplicator'); 
								?>
							</div><br/>
						<?php endif ?>

						<label><?php _e("Add Custom Path:", 'wpduplicator'); ?></label><br/>
						<input type="text" name="package_mysqldump_path" id="package_mysqldump_path" value="<?php echo $package_mysqldump_path; ?> " />
						<p class="description">
							<?php 
								_e("This is the path to your mysqldump program.", 'wpduplicator'); 
							?>
						</p>
					</div>
			
				<?php endif ?>
			</td>
		</tr>	
		<tr>
			<th scope="row"><label><?php _e("Package Debug", 'wpduplicator'); ?></label></th>
			<td>
				<input type="checkbox" name="package_debug" id="package_debug" <?php echo ($package_debug) ? 'checked="checked"' : ''; ?> />
				<label for="package_debug"><?php _e("Show Package Debug Status in Packages Screen", 'wpduplicator'); ?></label>
			</td>
		</tr>	
		
	</table>

	<p class="submit" style="margin: 20px 0px 0xp 5px;">
		<div style="border-top: 1px solid #efefef"></div><br/>
		<input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e("Save Settings", 'wpduplicator') ?>" style="display: inline-block;"/>
	</p>
</form>