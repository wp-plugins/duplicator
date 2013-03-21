<?php

	$package_name = date('Ymd') . '_' . sanitize_title(get_bloginfo( 'name', 'display' ));
	$package_name = substr(str_replace('-', '', $package_name), 0 , 40);
	$package_name = sanitize_file_name($package_name);
	
	global $wpdb;
	$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}duplicator` ORDER BY id DESC", ARRAY_A);
	$total_elements = count($result);
	
	//Settings
	$email_me_enabled    = $GLOBALS['duplicator_opts']['email-me'] == "0" 	 	 ? false : true;

	//COMMON HEADER DISPLAY
	require_once('javascript.php'); 
	require_once('inc.header.php'); 
?>

<!-- ==========================================
MAIN FORM: Lists all the backups 			-->
<div class="wrap">
<form id="form-duplicator" method="post">
	<!-- h2 required here for general system messages -->
	<h2 style='display:none'></h2>

	<?php duplicator_header(__("Packages", 'wpduplicator') ) ?>

	<!-- TOOL BAR -->
	<table border="0" id="toolbar-table" cellspacing="0" style="margin-top:15px">
		<tr valign="top">
			<td style="width:100%">
				<div class="alignleft actions">
					<select id="dup-pack-bulk-actions">
						<option value="-1" selected="selected"><?php _e("Bulk Actions", 'wpduplicator') ?></option>
						<option value="delete" title="<?php _e("Delete selected package(s)", 'wpduplicator') ?>"><?php _e("Delete", 'wpduplicator') ?></option>
					</select>
					<input type="button" name="" id="dup-pack-bulk-apply" class="button action" value="<?php _e("Apply", 'wpduplicator') ?>" onclick="Duplicator.Pack.Delete()">
				</div>
				<br class="clear">
			</td>
			<!-- Create/Delete -->
			<td><input type="button" id="btn-create-pack" class="btn-create-pack" title="<?php _e("Create Package", 'wpduplicator') ?>" onclick="Duplicator.Pack.ShowCreateDialog()" ondblclick="javascript:return void(0);"  /></td>	
			<!-- Options/Logs -->
			<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/hdivider.png" class="toolbar-divider" /></td>
			<td align="center"><input type="button" id="btn-opts-dialog" class="btn-opts-dialog" title="<?php _e("Options", 'wpduplicator') ?>..." onclick="Duplicator.Pack.ShowOptionsDialog()" /></td>	
			<td align="center"><input type="button" id="btn-sys-dialog"  class="btn-sys-dialog" onclick="Duplicator.Pack.RunSystemCheck()" title="<?php _e("System Check", 'wpduplicator') ?>..." /></td>
			<td align="center"><input type="button" id="btn-logs-dialog" class="btn-log-dialog" onclick="Duplicator.OpenLogWindow()" title="<?php _e("Show Create Log", 'wpduplicator') ?>..." /></td>
			<td align="center"></td>
		</tr>
	</table>

	<!-- STATUS BAR -->
	<div class="widget" style="padding:6px; margin: 2px 0px 0px 0px; border-bottom: none; font-size:13px">
		<b><?php _e("Status", 'wpduplicator') ?>:</b>
		<span id="span-status"><?php _e("Ready to create new package", 'wpduplicator' ) ?>.</span>
		<img id="img-status-error" src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/error.png" style="height:16px; width:16px; display:none; margin-top:3px; margin:0px" valign="bottom" />
		<img id="img-status-progress" src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/progress.gif" style="height:10px; width:46px; display:none" />
		<span id="span-status-post" style="display:inline-block"></span>
	</div>

	<!-- TABLE LIST -->
	<table class="widefat dup-pack-table">
		<thead>
			<tr>
				<?php if($total_elements == 0)  :	?>
					<th colspan="7">&nbsp;</th>
				<?php else : ?>	
					<th><input type="checkbox" id="dup-bulk-action-all"  title="<?php _e("Select all packages", 'wpduplicator') ?>" style="margin:0px;padding:0px 0px 0px 5px;" onclick="Duplicator.Pack.SetDeleteAll()" /></th>
					<th><?php _e("Details", 'wpduplicator') ?></th>
					<th><?php _e("User", 'wpduplicator') ?></th>
					<th><?php _e("Created", 'wpduplicator') ?></th>
					<th><?php _e("Size", 'wpduplicator') ?></th>
					<th><?php _e("Package Name", 'wpduplicator') ?></th>
					<th style="width:90%; text-align:right; padding-right:10px" colspan="2">
						<?php _e("Package Set",  'wpduplicator')?>
						<i style='font-size:10px'><?php _e("(Download Both)",  'wpduplicator')?></i>
					</th>
				<?php endif; ?>	
			</tr>
		</thead>
		<?php
		if($total_elements != 0)  {
			$ct = 0;
			$total_size = 0;
			while($ct < $total_elements) {
				$row	   = $result[$ct];
				$settings  = unserialize($row['settings']);
				$detail_id = "duplicator-detail-row-{$ct}";
				$packname  = empty($row['packname']) ? $row['zipname'] : $row['packname'];
				$total_size = $total_size + $row['zipsize'];
				$plugin_version = empty($settings['plugin_version']) ? 'unknown' : $settings['plugin_version'];
				$plugin_compat  = version_compare($plugin_version, '0.3.1');
				?>


				<?php if($plugin_compat >= 0)  :	?>
					<?php
						//Links
						$uniqueid  			= "{$row['token']}_{$row['packname']}";
						$sqlfilelink		= duplicator_snapshot_urlpath() . "{$uniqueid}_database.sql";
						$packagepath 		= duplicator_snapshot_urlpath() . "{$uniqueid}_package.zip";
						$installerpath		= duplicator_snapshot_urlpath() . "{$uniqueid}_installer.php";
						$installfilelink	= "{$installerpath}?get=1&file={$uniqueid}_installer.php";
					?>
					<tr class="dup-pack-info">
						<td style="padding-right:20px !important"><input name="delete_confirm" type="checkbox" id="<?php echo $uniqueid ;?>" /></td>
						<td><a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');">[<?php echo __("View", 'wpduplicator') . ' ' . $row['id'];?>]</a></td>
						<td><?php echo $row['owner'];?></td>
						<td><?php echo date( "m-d-y G:i", strtotime($row['created']));?></td>
						<td><?php echo duplicator_bytesize($row['zipsize']);?></td>
						<td class='pack-name'><?php echo $packname ;?></td>
						<td style="width:90%;" class="get-btns">	
							<button id="<?php echo "{$uniqueid}_installer.php" ?>" class="dup-installer-btn no-select" onclick="Duplicator.Pack.DownloadFile('<?php echo $installfilelink; ?>', this); return false;"><?php _e("Installer", 'wpduplicator') ?></button> &nbsp;
							<button id="<?php echo "{$uniqueid}_package.zip" ?>" class="dup-installer-btn no-select" onclick="Duplicator.Pack.DownloadFile('<?php echo $packagepath; ?>', this); return false;"><?php _e("Package", 'wpduplicator') ?></button>
						</td>
					</tr>
					<tr>
						<td colspan="8" id="<?php echo $detail_id; ?>" class="dup-pack-details">
							<div class="dup-details-area">
								<b><?php _e("Version", 'wpduplicator') ?>:</b> <?php echo $plugin_version ?>  &nbsp;
								<b><?php _e("Secure Name", 'wpduplicator')?>:</b> <?php echo "{$row['token']}_{$row['packname']}" ;?> <br/>
								<button class='dup-dlg-quick-path-database-link no-select' onclick="window.open(<?php echo "'{$sqlfilelink}', '_blank'" ;?>); return false;"><?php _e("Download SQL File", 'wpduplicator')?></button>
								<button class='dup-dlg-quick-path-download-link no-select' onclick="Duplicator.Pack.ShowLinksDialog(<?php echo "'{$sqlfilelink}', '{$packagepath}', '{$installfilelink}' " ;?>); return false;"><?php _e("Show Download Links", 'wpduplicator')?></button>
							</div>
						</td>
					</tr>	

				<!-- LEGACY PRE 0.3.1 PACKS -->
				<?php else : ?>	
					<?php
						$legacy_package = duplicator_snapshot_urlpath() . "{$row['zipname']}";
					?>
					<tr class="dup-pack-info">
						<td style="padding-right:20px !important"><input name="delete_confirm" type="checkbox" id="<?php echo $row['zipname'] ;?>" /></td>
						<td><a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');">[<?php echo __("View", 'wpduplicator') . ' ' . $row['id'];?></a>]</td>
						<td><?php echo $row['owner'];?></td>
						<td><?php echo date( "m-d-y G:i", strtotime($row['created']));?></td>
						<td><?php echo duplicator_bytesize($row['zipsize']);?></td>
						<td class='pack-name'><?php echo $packname ;?></td>
						<td style="width:90%;" class="get-btns">	
							<span style='display:inline-block; padding:7px 10px 0px 0px'>
							<a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');">[Not Supported]</a></span>
							<button id="<?php echo "{$row['zipname']}" ?>" class="dup-installer-btn no-select" onclick="Duplicator.Pack.DownloadFile('<?php echo $legacy_package; ?>', this); return false;"><?php _e("Package", 'wpduplicator') ?></button>
						</td>
					</tr>
					<tr>
						<td colspan="8" id="<?php echo $detail_id; ?>" class="dup-pack-details">
							<div class="dup-details-area ui-state-error">
								<b><?php _e("Legacy Version", 'wpduplicator') ?>:</b> <?php echo $plugin_version ?> <br/>
								<i style="color:#000"><?php
								printf("%s <a href='http://lifeinthegrid.com/duplicator-docs' target='_blank'>%s</a>",
								__("This package was built with a version that is no longer supported.  It is highly recommended that this package be deleted.  For more details see the", 'wpduplicator'),
								__("Online FAQs", 'wpduplicator')); 
								?></i>
							</div>
						</td>
					</tr>	
				<?php endif; ?>	


				<?php
				$ct++;
			}
		} else {
			$msg1 = __("No packages found", 'wpduplicator');
			$msg2 = __("To create a new package click the create button ", 'wpduplicator');
			$msg3 = sprintf("%s <a href='javascript:void(0)' onclick='Duplicator.Pack.RunSystemCheck()'>%s</a> %s",
						__("Check your", 'wpduplicator'), 
						__("server's compatibility", 'wpduplicator'),
						__("with the Duplicator", 'wpduplicator'));
			//$msg4 = __("This process will backup all your files and database", 'wpduplicator');
			$msg5 = __("Creating a package may take several minutes", 'wpduplicator');
			$msg6 = __("This window should remain open for the process to complete", 'wpduplicator');
			$msg7 = __("Please be patient while we work through this Beta version", 'wpduplicator');

			echo "<tr>
					<td colspan='7'>
						<div style='padding:100px 20px;text-align:center'>
							<b style='font-size:14px'>{$msg1}.<br/> {$msg2} <input type='button' id='dup-create-pack-zero-view' onclick='Duplicator.Pack.ShowCreateDialog()'  ondblclick='javascript:return void(0);' value=''  /><br/> {$msg3}.</b><br/><br/>
							<i> {$msg5}.<br/> {$msg6}. <br/>{$msg7}.</i>
						</div>
						</td>
					</tr>";
		}
		?>
		<tfoot>
			<tr>
				<?php if($total_elements == 0)  :	?>
					<th colspan="8">&nbsp;</th>
				<?php else : ?>	
					<th colspan="8" style='text-align:right; font-size:12px'><?php echo _e("Total Storage Used", 'wpduplicator') . ': ' . duplicator_bytesize($total_size); ?></th>
				<?php endif; ?>	
			</tr>
		</tfoot>
	</table>
</form>
</div>

<?php
	//INLINE DIALOG WINDOWS
	require_once('pack.win.create.php');
	require_once('pack.win.links.php');
	require_once('pack.win.options.php');
	require_once('pack.win.system.php');
	require_once('pack.win.error.php');
?>

<script type="text/javascript">
jQuery(document).ready(function($) {
		
	/*	----------------------------------------
	 *	METHOD: Sets the status of the Duplicator status bar */
	Duplicator.Pack.SetStatus = function(msg, img, postmsg) {
		//Clean Status Bar
		$("#img-status-error").hide();
		$("#img-status-progress").hide();
		
		$('#span-status').html(msg);
		switch (img) {
			case 'error' 	: $("#img-status-error").show('slow'); break;
			case 'progress' : $("#img-status-progress").show('slow'); break;
		}
		$('#span-status-post').html(postmsg);
	}
	
	/*	----------------------------------------
	 *	METHOD: Disables or enables the toolbar
	 *  @param state		Disabled/Enabled */ 
	Duplicator.Pack.SetToolbar = function(state) {
		if (state == "DISABLED") {
			$('#toolbar-table input, div#duplicator-installer').attr("disabled", "true");
			$('#toolbar-table input, div#duplicator-installer').css("background-color", "#efefef");
		} else {
			$('#toolbar-table input, div#duplicator-installer').removeAttr("disabled");
			$('#toolbar-table input, div#duplicator-installer').css("background-color", "#f9f9f9");
		}	
	}

	/*	----------------------------------------
	 *	METHOD: Shows hides the package detail items
	 *	@param id	The id to toggle */
	Duplicator.Pack.ToggleDetail = function(id) {
		$('#' + id).toggle();
		return false;
	}
	
	/*	----------------------------------------
	 *	METHOD: Triggers the download of an installer/package file
	 *	@param name		Window name to open
	 *	@param button	Button to change color */
	Duplicator.Pack.DownloadFile = function(name, button) {
		$(button).addClass('dup-button-selected');
		window.open(name, '_self'); 
		return false;
	}
	
	/*  ----------------------------------------
	 *  METHOD: Starts the create process by performing a system check */
	Duplicator.Pack.ShowCreateDialog = function (event) {
		if (event)
			event.preventDefault();   

		var packname = $("input[name=package_name]").val();

		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: "duplicator_new="+ packname +"&action=duplicator_system_check",
			beforeSend: function() {
				Duplicator.Pack.SetStatus("<?php _e("Evaluating WordPress Setup. Please Wait", 'wpduplicator') ?>...", 'progress');
			},
			success: function(data) {
				Duplicator.Pack.SetStatus("<?php _e('Ready to create new package.', 'wpduplicator') ?>");
				if (data.Success) {
					$("#dup-create-message").hide();
					$("#dup-dlg-package-confirm").dialog('open');
				} else {
					Duplicator.Pack.ShowSystemDialog(data);
				}
			},
			error: function(data) { 
				Duplicator.Pack.ShowError('form-duplicator submit', data);
			}
		});
	}
	
	/*	----------------------------------------
	 *	METHOD: Removes all selected package sets 
	 *	@param event	To prevent bubbling */
	Duplicator.Pack.Delete = function (event) {
		var arr = new Array;
		var count = 0;
		
		if ($("#dup-pack-bulk-actions").val() != "delete") {
			alert("<?php _e('Please select an action from the bulk action drop down menu to perform a specific action.', 'wpduplicator') ?>");
			return;
		}
		
		$("input[name=delete_confirm]").each(function() {
			 if (this.checked) { arr[count++] = this.id; }
		});
		var list = arr.join(',');
		if (list.length == 0) {
			alert("<?php _e('Please select at least one package to delete.', 'wpduplicator') ?>");
			return;
		}
		
		var answer = confirm("<?php _e('Are you sure, you want to delete the selected package(s)?', 'wpduplicator') ?>");
		if (answer){
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: "duplicator_delid="+list+"&action=duplicator_delete",
				beforeSend: function() {Duplicator.StartAjaxTimer(); },
				complete: function() {Duplicator.EndAjaxTimer(); },
				success: function(data) { 
					Duplicator.ReloadWindow(data); 
				},
				error: function(data) { 
					Duplicator.Pack.ShowError('Duplicator.Pack.Delete', data);
				}
			});
		} else {
			Duplicator.Pack.SetStatus("<?php _e('Ready to create new package.', 'wpduplicator') ?>");
		}
		if (event)
			event.preventDefault(); 
	};
	
	/*  ----------------------------------------
	 *  METHOD: Toogles the Bulk Action Check boxes */
	Duplicator.Pack.SetDeleteAll = function() {
		var state = $('input#dup-bulk-action-all').is(':checked') ? 1 : 0;
		$("input[name=delete_confirm]").each(function() {
			 this.checked = (state) ? true : false;
		});
	}
	
});
</script>

