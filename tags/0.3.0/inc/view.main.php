<?php
	$package_name = date('Ymd') . '_' . sanitize_title(get_bloginfo( 'name', 'display' ));
	$package_name = substr(str_replace('-', '', $package_name), 0 , 40);
	$package_name = sanitize_file_name($package_name);
	
	global $wpdb;
	$result = $wpdb->get_results('SELECT * FROM '. $wpdb->prefix . "duplicator ORDER BY id DESC", ARRAY_A);
	$total_elements = count($result);
	
	//Settings
	$email_me_enabled    = $GLOBALS['duplicator_opts']['email-me'] == "0" 	 	 ? false : true;
	$duplicator_dbiconv	 = $GLOBALS['duplicator_opts']['dbiconv'] == "0" 		 ? false : true;
	
	//INLINE DIALOG WINDOWS
	require_once('javascript.php'); 
	require_once('view.options.php');
	require_once('view.system.php');

?>


<!-- ==========================================
MAIN FORM: Lists all the backups 			-->
<div class="wrap">
	<form id="form-duplicator" method="post">

		<h2 style='margin-top:-3px'>
			<div style='float:left;height:45px'><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/logo.jpg" style='text-align:top'  /></div> 
			<div style='float:left;height:35px; padding-top:7px'>
				Duplicator <i style='font-size:11px'><?php _e("By", 'WPDuplicator') ?> <a href='http://lifeinthegrid.com/duplicator' target='_blank'>lifeinthegrid.com</a></i>
			</div> <br style='clear:both' />
		</h2>
		
		<!-- TOOLBAR -->
		<table border="0" id="toolbar-table" cellspacing="0">
			<tr valign="top">
				<td class="toolbar-label"><label><?php _e("Package Name", 'WPDuplicator') ?>:</label>&nbsp;</td>
				<td class="toolbar-textbox"><input name="package_name" type="text" style="width:250px" value="<?php echo $package_name ?>" maxlength="40" /></td>
				<!-- Create/Delete -->
				<td><input type="submit" id="btn-create-pack" class="btn-create-pack" value="" name="submit" title="<?php _e("Create Package", 'WPDuplicator') ?>" ondblclick="javascript:return void(0);" /></td>
				<td><input type="button" id="btn-delete-pack" title="<?php _e("Delete selected package(s)", 'WPDuplicator') ?>" onclick="Duplicator.deletePackage()"  ondblclick="javascript:return void(0);"/></td>	
				<!-- Options/Logs -->
				<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/hdivider.png" class="toolbar-divider" /></td>
				<td align="center"><input type="button" id="btn-opts-dialog" class="btn-opts-dialog" title="<?php _e("Options", 'WPDuplicator') ?>..." onclick="Duplicator.optionsOpen()" /></td>	
				<td align="center"><input type="button" id="btn-sys-dialog"  class="btn-sys-dialog" onclick="Duplicator.getSystemCheck()" title="<?php _e("System Check", 'WPDuplicator') ?>..." /></td>
				<td align="center"><input type="button" id="btn-logs-dialog" class="btn-log-dialog" onclick="Duplicator.openLog()" title="<?php _e("Show Create Log", 'WPDuplicator') ?>..." /></td>
				<!-- Help -->
				<td><img src="<?php echo DUPLICATOR_PLUGIN_URL ?>img/hdivider.png" class="toolbar-divider" /></td>
				<td><input type="button" id="btn-help-dialog" onclick='Duplicator.newWindow("<?php echo DUPLICATOR_HELPLINK ?>")' title="<?php _e("Help", 'WPDuplicator') ?>..." /></td>
				<td><input type="button" id="btn-contribute-dialog" onclick='Duplicator.newWindow("<?php echo DUPLICATOR_GIVELINK ?>")' title="<?php _e("Partner with us", 'WPDuplicator') ?>..." /></td>
			</tr>
		</table>

		<!-- STATUS BAR
		id comes from wp-themes: major-publishing-actions  keeps themeing correct -->
		<table width="100%"  class="widefat dup-pack-table" cellspacing="0" border="1">
			<tr>
				<td width="100%" style="font-size:14px; vertical-align:middle">
					<b><?php _e("Status", 'WPDuplicator') ?>:</b>
					<span id="span-status"><?php _e("Ready to create new package", 'WPDuplicator' ) ?>.</span>
					<img id="img-status-error" src="<?php echo DUPLICATOR_PLUGIN_URL ?>img/error.png" style="height:16px; width:16px; display:none; margin-top:3px; margin:0px" valign="bottom" />
					<img id="img-status-progress" src="<?php echo DUPLICATOR_PLUGIN_URL ?>img/progress.gif" style="height:10px; width:46px; display:none" />
				</td>
				<?php if($total_elements != 0)  :	?>
				<td style="white-space:nowrap; font-weight:bold">
					<a href="<?php echo DUPLICATOR_PLUGIN_URL .'files/installer.php?download'; ?>">
						<div id="duplicator-installer" class="dup-installer-btn"><?php _e("Installer", 'WPDuplicator') ?></div>
					</a>
				</td>
				<?php endif; ?>
			</tr>
		</table><div style="height:5px"></div>
		
		
		<!-- TABLE LIST -->
		<table class="widefat dup-pack-table">
			<thead>
				<tr>
					<?php if($total_elements == 0)  :	?>
						<th colspan="7">&nbsp;</th>
					<?php else : ?>	
						<th><input type="checkbox" id="select-all"  title="<?php _e("Select all packages", 'WPDuplicator') ?>" style="margin:0px;padding:0px 0px 0px 5px;" /></th>
						<th><?php _e("Information", 'WPDuplicator') ?></th>
						<th><?php _e("Owner", 'WPDuplicator') ?></th>
						<th><?php _e("Created", 'WPDuplicator') ?></th>
						<th><?php _e("Size", 'WPDuplicator') ?></th>
						<th><?php _e("Package Name", 'WPDuplicator') ?></th>
						<th style="width:90%; text-align:right"><?php _e("Package",  'WPDuplicator')?></th>
					<?php endif; ?>	
				</tr>
			</thead>
			<?php
			if($total_elements != 0)  {
				$ct = 0;
				while($ct < $total_elements) {
					$row	   = $result[$ct];
					$settings  = unserialize($row['settings']);
					$detail_id = "duplicator-detail-row-{$ct}";
					$packname  = empty($row['packname']) ? $row['zipname'] : $row['packname'];
					$uniqueid  = "{$row['token']}_{$row['packname']}";
					$packagepath = duplicator_snapshot_urlpath() . "{$uniqueid}_package.zip";
					?>
					<tr class="dup-pack-info">
						<td style="padding-right:20px !important"><input name="delete_confirm" type="checkbox" id="<?php echo $uniqueid ;?>" onclick="Duplicator.rowColor(this)" /></td>
						<td><a href="javascript:void(0);" onclick="return Duplicator.toggleDetail('<?php echo $detail_id ;?>');">[<?php echo __("View", 'WPDuplicator') . ' ' . $row['id'];?></a>]</td>
						<td><?php echo $row['owner'];?></td>
						<td><?php echo date( "m-d-y G:i", strtotime($row['created']));?></td>
						<td><?php echo duplicator_bytesize($row['zipsize']);?></td>
						<td><?php echo $packname ;?></td>
						<td style="width:90%; text-align:right; padding-right:5px !important">
							<a href="<?php echo $packagepath; ?>">
								<div id="<?php echo "{$uniqueid}_package.zip" ?>" class="dup-installer-btn"><?php _e("Package", 'WPDuplicator') ?></div>
							</a>
						</td>

					</tr>
					<tr>
						<td colspan="7" id="<?php echo $detail_id; ?>" class="dup-pack-details">
							<div class="dup-details-area">
								<?php 
									$plugin_version = empty($settings['plugin_version']) ? 'unknown' : $settings['plugin_version'];
									$secure_token   = empty($row['token'])               ? 'unknown' : $row['token'];
									$sqlfilepath = duplicator_snapshot_urlpath() . "{$uniqueid}_database.sql"
								?>
								<b><?php _e("Plugin Version", 'WPDuplicator') ?>:</b> <?php echo $plugin_version ?><br/>
								<b><?php _e("Secure Name", 'WPDuplicator')?>:</b> <?php echo "{$row['token']}_{$row['packname']}" ;?> <br/>
								<b><?php _e("Database File",  'WPDuplicator')?>:</b> <?php echo "<a href='{$sqlfilepath}' target='_blank'>[SQL Backup]</a>"; ?> <br/>
							</div>
						</td>
					</tr>
					<?php
					$ct++;
				}
			} else {
				$msg1 = __("No packages found", 'WPDuplicator');
				$msg2 = __("To create a new package, enter a name and click the create button ", 'WPDuplicator');
				$msg3 = sprintf("%s <a href='javascript:void(0)' onclick='Duplicator.getSystemCheck()'>%s</a> %s",
							__("Check Your", 'WPDuplicator'), 
							__("servers compatability", 'WPDuplicator'),
							__("with the duplicator", 'WPDuplicator'));
				$msg4 = __("This process will backup all your files and database", 'WPDuplicator');
				$msg5 = __("Creating a package may take several minutes if you have a large site", 'WPDuplicator');
				$msg6 = __("This window should remain open for the process to complete", 'WPDuplicator');
				$msg7 = __("Please be patient while we work through this Beta version", 'WPDuplicator');
				$msg8 = __("Please report any issues to", 'WPDuplicator');
				
				echo "<tr>
						<td colspan='7'>
							<div style='padding:60px 20px;text-align:center'>
								<b style='font-size:14px'>{$msg1}.<br/> {$msg2} <input type='submit' class='btn-create-pack'  ondblclick='javascript:return void(0);' value=''  /><br/> {$msg3}.</b><br/><br/>
								<i>{$msg4}.<br/> {$msg5}.<br/> {$msg6}. <br/><br/> {$msg7}.<br/> {$msg8} <a href='http://support.lifeinthegrid.com' target='_blank'>support.lifeinthegrid.com</a></i>
							</div>
							</td>
						</tr>";
			}
			?>
			<tfoot>
				<tr>
					<?php if($total_elements == 0)  :	?>
						<th colspan="7">&nbsp;</th>
					<?php else : ?>	
						<th></th>
						<th><?php _e("Information", 'WPDuplicator') ?></th>
						<th><?php _e("Owner", 'WPDuplicator') ?></th>
						<th><?php _e("Created", 'WPDuplicator') ?></th>
						<th><?php _e("Size", 'WPDuplicator') ?></th>
						<th><?php _e("Package Name", 'WPDuplicator') ?></th>
						<th></th>
					<?php endif; ?>	
				</tr>
			</tfoot>
		</table>
	</form>
</div>
