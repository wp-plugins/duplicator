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
	
	//Add logic
	$pack_passcount = get_option('duplicator_pack_passcount', 0);
	$add1_passcount	= get_option('duplicator_add1_passcount', 1);
	$add1_clicked 	= get_option('duplicator_add1_clicked', 0);
?>
<style>
div#dup-add1 {
	bottom: 2px; left:1px; width: 99%; height: 40px; background-color: #FFFFE0; z-index: 3; border-radius: 8px; border: 1px solid silver; 
	padding:4px 8px 6px 8px; line-height: 18px; font-size:13px;
}
button.dup-support-btn {height:26px; font-size:12px}
</style>


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

	<div id="dup-add1" style="display:none">
		<?php _e("We hope you are enjoying the Duplicator!  Would you consider helping in the continued development of the plugin", 'wpduplicator') ?>? <br/>
		<button class="dup-support-btn" onclick="Duplicator.Pack.Add1('donate'); return false;"><?php _e("Donate", 'wpduplicator') ?></button>
		<button class="dup-support-btn" onclick="Duplicator.Pack.Add1('rate'); return false;"><?php _e("Rate It", 'wpduplicator') ?> 5&#9733;'s</button>
		<button class="dup-support-btn" onclick="Duplicator.Pack.Add1('share'); return false;"><?php _e("Share It", 'wpduplicator') ?></button>
		<button class="dup-support-btn" onclick="Duplicator.Pack.Add1('notnow'); return false;"><?php _e("Not Now", 'wpduplicator') ?>!</button>
	</div>	

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
				$row			= $result[$ct];
				$settings		= unserialize($row['settings']);
				$detail_id		= "duplicator-detail-row-{$ct}";
				$packname		= empty($row['packname']) ? $row['zipname'] : $row['packname'];
				$total_size     = $total_size + $row['zipsize'];
				$plugin_version = empty($settings['plugin_version']) ? 'unknown' : $settings['plugin_version'];
				$plugin_compat  = version_compare($plugin_version, '0.4.5');
				$status         = $settings['status'];
				$notes          = empty($settings['notes']) ? __("No notes were given for this package", 'wpduplicator') : $settings['notes'];
				?>

				<?php if($plugin_compat >= 0)  : ?>
					<?php
						//Links
						$uniqueid  			= "{$row['token']}_{$row['packname']}";
						$sqlfilelink		= duplicator_snapshot_urlpath() . "{$uniqueid}_database.sql";
						$packagepath 		= duplicator_snapshot_urlpath() . "{$uniqueid}_package.zip";
						$installerpath		= duplicator_snapshot_urlpath() . "{$uniqueid}_installer.php";
						$installfilelink	= "{$installerpath}?get=1&file={$uniqueid}_installer.php";
						$logfilelink		= duplicator_snapshot_urlpath() . "{$uniqueid}.log";
						$logfilename	    = "{$uniqueid}.log";
					?>
		
					<?php if ($status == 'Pass') : ?>
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
									<b><?php _e("Notes", 'wpduplicator')?>:</b> <?php echo $notes ?> <br/>
									<button class='dup-dlg-quick-path-database-link no-select' onclick="window.open(<?php echo "'{$sqlfilelink}', '_blank'" ;?>); return false;"><?php _e("SQL File", 'wpduplicator')?></button>
									<button class='dup-dlg-quick-path-download-link no-select' onclick="Duplicator.Pack.ShowLinksDialog(<?php echo "'{$sqlfilelink}', '{$packagepath}', '{$installfilelink}', '{$logfilelink}' " ;?>); return false;"><?php _e("Show Links", 'wpduplicator')?></button>
									<button class='dup-dlg-quick-path-download-link no-select' onclick="Duplicator.OpenLogWindow(<?php echo "'{$logfilename}'" ;?>); return false;"><?php _e("View Log", 'wpduplicator')?></button>
								</div>
							</td>
						</tr>	
					<?php else : ?>	
						<tr class="dup-pack-info">
							<td style="padding-right:20px !important"><input name="delete_confirm" type="checkbox" id="<?php echo $uniqueid ;?>" /></td>
							<td><a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');">[<?php echo __("View", 'wpduplicator') . ' ' . $row['id'];?>]</a></td>
							<td><?php echo $row['owner'];?></td>
							<td><?php echo date( "m-d-y G:i", strtotime($row['created']));?></td>
							<td><?php echo duplicator_bytesize($row['zipsize']);?></td>
							<td class='pack-name'><?php echo $packname ;?></td>
							<td style="width:90%;" class="get-btns">	
								<span style='display:inline-block; padding:7px 40px 0px 0px'>
									<a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');"><?php _e("View Error Details", 'wpduplicator') ?>...</a>
								</span>									
							</td>
						</tr>
						<tr>
							<td colspan="8" id="<?php echo $detail_id; ?>" class="dup-pack-details">
								<div class="dup-details-area ui-state-error">
									<b><?php _e("Version", 'wpduplicator') ?>:</b> <?php echo $plugin_version ?>  &nbsp;
									<b><?php _e("Secure Name", 'wpduplicator')?>:</b> <?php echo "{$row['token']}_{$row['packname']}" ;?> <br/>
										<?php
										printf("%s <u><a href='http://lifeinthegrid.com/duplicator-docs' target='_blank'>%s</a></u>",
										__("This package has encountered errors.  Click 'View Log' for more details.  For additional support see the ", 'wpduplicator'),
										__("online knowledgebase", 'wpduplicator')); 
										?><br/>
										<button class='dup-dlg-quick-path-download-link no-select' onclick="Duplicator.OpenLogWindow(<?php echo "'{$logfilename}'" ;?>); return false;"><?php _e("View Log", 'wpduplicator')?></button>
								</div>
							</td>
						</tr>	
					<?php endif; ?>
				<?php else : ?>	
					 <!-- LEGACY PRE 0.4.4 -->
					<?php
						if (isset($row['token']) && isset($row['packname'])) {
							$uniqueid  	= "{$row['token']}_{$row['packname']}";
						} 
						//Pre 0.4.0
						else {
							$uniqueid = $row['zipname'];
						}
					?>
					<tr class="dup-pack-info">
						<td style="padding-right:20px !important"><input name="delete_confirm" type="checkbox" id="<?php echo $uniqueid ;?>" /></td>
						<td><a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');">[<?php echo __("View", 'wpduplicator') . ' ' . $row['id'];?></a>]</td>
						<td><?php echo $row['owner'];?></td>
						<td><?php echo date( "m-d-y G:i", strtotime($row['created']));?></td>
						<td><?php echo duplicator_bytesize($row['zipsize']);?></td>
						<td class='pack-name'><?php echo $packname ;?></td>
						<td style="width:90%;" class="get-btns">	
							<span style='display:inline-block; padding:7px 40px 0px 0px'>
								<a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');">[<?php _e("Not Supported", 'wpduplicator') ?>]</a>
							</span>
						</td>
					</tr>
					<tr>
						<td colspan="8" id="<?php echo $detail_id; ?>" class="dup-pack-details">
							<div class="dup-details-area ui-state-error">
								<b><?php _e("Legacy Version", 'wpduplicator') ?>:</b> <?php echo $plugin_version ?> <br/>
								<i style="color:#000"><?php
								printf("%s <a href='http://lifeinthegrid.com/duplicator-docs' target='_blank'>%s</a>",
								__("This package was built with a version that is no longer supported.  It is highly recommended that this package be deleted.  For more details see the", 'wpduplicator'),
								__("Change Log", 'wpduplicator')); 
								echo ".<br/>";
								_e('To recover older packages please check the wp-snapshots folder for the installer and package files.', 'wpduplicator');
								
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
			$msg4 = __("Creating a package may take several minutes", 'wpduplicator');
			$msg5 = __("This window should remain open for the process to complete", 'wpduplicator');
			$msg6 = sprintf("%s <a href='admin.php?page=duplicator_support_page'>%s</a> %s",
						__("Please visit the", 'wpduplicator'), 
						__("support page", 'wpduplicator'),
						__("for additional help topics", 'wpduplicator'));

			echo "<tr>
					<td colspan='7'>
						<div style='padding:100px 20px;text-align:center'>
							<b style='font-size:14px'>{$msg1}.<br/> {$msg2} <input type='button' id='dup-create-pack-zero-view' onclick='Duplicator.Pack.ShowCreateDialog()'  ondblclick='javascript:return void(0);' value=''  /><br/> {$msg3}.</b><br/><br/>
							<i> {$msg4}.<br/> {$msg5}. <br/>{$msg6}.</i>
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
					<th colspan="8" style='text-align:right; font-size:12px'>						
						<?php echo _e("Packages", 'wpduplicator') . ': ' . $total_elements; ?> |
						<?php echo _e("Total Size", 'wpduplicator') . ': ' . duplicator_bytesize($total_size); ?> 
					</th>
				<?php endif; ?>	
			</tr>
		</tfoot>
	</table>
</form>
</div>

<form id="dup-paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" style="display:none"> 
	<input name="cmd" type="hidden" value="_s-xclick" /> 
	<input name="hosted_button_id" type="hidden" value="EYJ7AV43RTZJL" /> 
	<img src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" /> 
</form>

<?php if ($total_elements >= 3 && $add1_passcount >= DUPLICATOR_ADD1_TRIPCOUNT &&  $add1_clicked == false) :?>
	<script>
		jQuery(document).ready(function($) {
			jQuery("#dup-add1").show(700);
		});
	</script>
<?php endif; ?>	

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
	
	/*  ----------------------------------------
	 *  METHOD: Add1 Logic*/
	Duplicator.Pack.Add1 = function (action) {
		var message1;
		var message2 = "<?php _e('Also check-out the support link for additional ways to help.', 'wpduplicator') ?>";
		var message3 = "<?php _e('This message will disappear after refreshing the page.', 'wpduplicator') ?>";
		

		$.post(ajaxurl, { action: "duplicator_add1_click", click : action} );
		var style = {backgroundColor : '#efefef', fontStyle : 'italic'};

		switch (action) {
			case "donate" :
				message1 = "<?php _e('Thanks for donating to the Duplicator!  Your contribution really does make a difference!', 'wpduplicator') ?>";
				$("#dup-add1").html(message1 + "<br/>" + message3).css(style).hide().show(600);				
				$("#dup-paypal").submit();
				break;
			case "rate" :
				message1 = "<?php _e('Thanks for giving a 5 star rating!  A huge amount of time and effort has gone into creating this plugin.', 'wpduplicator') ?>";
				$("#dup-add1").html(message1 + "<br/>" + message2 + '  ' + message3).css(style).hide().show(600);				
				window.open("http://wordpress.org/plugins/duplicator/", "_blank");
				break;
			case "share" :
				message1 = "<?php _e('Thanks for sharing the Duplicator and spreading the word!  We have definitly enjoyed helping this awesome community!', 'wpduplicator') ?>";
				$("#dup-add1").html(message1 + "<br/>" + message2 + '  ' + message3).css(style).hide().show(600);				
				window.open("https://www.paywithatweet.com/pay/?id=71e421d43020b6c4065487377e535f4e", "_blank");
				break;	
			case "notnow" :
				$("#dup-add1").hide(500);
				break;				
		}
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

