<?php
	$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}duplicator_packages` ORDER BY id DESC", ARRAY_A);
	$totalElements = count($result);
?>

<style>
	div#dup-list-alert-nodata {padding:50px 20px;text-align:center; font-size:16px; line-height:22px}
	div.dup-notice-msg {border:1px solid silver; padding: 10px; border-radius: 5px; width: 550px; 
		margin:40px auto 0px auto; font-size:12px; text-align: left; word-break:normal;
		background: #fefcea; 
		background: -moz-linear-gradient(top,  #fefcea 0%, #efe5a2 100%);
		background: -ms-linear-gradient(top,  #fefcea 0%,#efe5a2 100%);
		background: linear-gradient(to bottom,  #fefcea 0%,#efe5a2 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fefcea', endColorstr='#efe5a2',GradientType=0 );
	}
	input#dup-bulk-action-all {margin:0px;padding:0px 0px 0px 5px;}
	button.dup-button-selected {border:1px solid #000 !important; background-color:#555 !important;}
	
	table#toolbar-table td {padding:0px; white-space:nowrap;}
	table#toolbar-table input {border:1px solid silver; border-radius:4px}
	table#toolbar-table input:hover {border:1px solid gray;}
	td.toolbar-label label {font-size:14px !important; margin-top:7px; display:inline-block; font-weight:bold}
	td.toolbar-textbox {white-space:nowrap;width:100%; vertical-align:middle}
	img.toolbar-divider {height:26px; padding:0px 5px 0px 5px}	
	
	/* Table package details */
	table.dup-pack-table {word-break:break-all;}
	table.dup-pack-table th {white-space:nowrap !important;}
	table.dup-pack-table td.pack-name {text-overflow:ellipsis; white-space:nowrap}
	tr.dup-pack-info td {white-space:nowrap; padding:12px 30px 0px 7px;}
	tr.dup-pack-info td.get-btns {text-align:right; padding:3px 5px 6px 0px !important;}
	td.dup-pack-details {display:none; padding:5px 0px 15px 15px; line-height:22px; 
		background: #f9f9f9;
		background: -moz-linear-gradient(top,  #f9f9f9 0%, #ffffff 100%);
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#f9f9f9), color-stop(100%,#ffffff));
		background: -webkit-linear-gradient(top,  #f9f9f9 0%,#ffffff 100%);
		background: -o-linear-gradient(top,  #f9f9f9 0%,#ffffff 100%);
		background: -ms-linear-gradient(top,  #f9f9f9 0%,#ffffff 100%);
		background: linear-gradient(to bottom,  #f9f9f9 0%,#ffffff 100%);
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#f9f9f9', endColorstr='#ffffff',GradientType=0 );
	}
</style>



<form id="form-duplicator" method="post">


<?php if($totalElements == 0)  :	?>
	
	<!-- ====================
	NO-DATA MESSAGES-->
	<table class="widefat dup-pack-table" style="margin-top:20px">
		<thead><tr><th>&nbsp;</th></tr></thead>
		<tbody><tr><td><?php	include (DUPLICATOR_PLUGIN_PATH .  "views/packages/list-nodata.php") ?> </td></tr></tbody>
		<tfoot><tr><th>&nbsp;</th></tr></tfoot>
	</table>
	
<?php else : ?>	
	
	<!-- ====================
	TOOL-BAR -->
	<table border="0" id="toolbar-table" cellspacing="0" style="margin-top:15px">
		<tr valign="top">
			<td>
				<div class="alignleft actions">
					<select id="dup-pack-bulk-actions">
						<option value="-1" selected="selected"><?php _e("Bulk Actions", 'wpduplicator') ?></option>
						<option value="delete" title="<?php _e("Delete selected package(s)", 'wpduplicator') ?>"><?php _e("Delete", 'wpduplicator') ?></option>
					</select>
					<input type="button" id="dup-pack-bulk-apply" class="button action" value="<?php _e("Apply", 'wpduplicator') ?>" onclick="Duplicator.Pack.Delete()">
				</div>
				<br class="clear">
			</td>
			<!-- Logs -->
			<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/hdivider.png" class="toolbar-divider" /></td>
			<td align="center">
				<button id="btn-logs-dialog" class="button" onclick="Duplicator.OpenLogWindow(); return false;" title="<?php _e("Show Create Log", 'wpduplicator') ?>..." ><i class="fa fa-pencil-square-o"></i> </button>
			</td>
		</tr>
	</table>	
	
	<!-- ====================
	LIST ALL PACKAGES -->
	<table class="widefat dup-pack-table">
		<thead>
			<tr>
				<th><input type="checkbox" id="dup-bulk-action-all"  title="<?php _e("Select all packages", 'wpduplicator') ?>" style="" onclick="Duplicator.Pack.SetDeleteAll()" /></th>
				<th><?php _e("Details", 'wpduplicator') ?></th>
				<th><?php _e("Created", 'wpduplicator') ?></th>
				<th><?php _e("Size", 'wpduplicator') ?></th>
				<th><?php _e("Name", 'wpduplicator') ?></th>
				<th style="width:90%; text-align:right; padding-right:10px" colspan="2">
					<i style='font-size:10px'><?php _e("(Download Both)",  'wpduplicator')?></i>
				</th>
			</tr>
		</thead>
		<?php

		$rowCount = 0;
		$totalSize = 0;
		while($rowCount < $totalElements) {
			$row			= $result[$rowCount];
			$Package		= unserialize($row['package']);
			$detail_id		= "duplicator-detail-row-{$rowCount}";
			$totalSize      = $totalSize + $Package->Archive->Size;
			$plugin_version = empty($Package->Version) ? 'unknown' : $Package->Version;
			$plugin_compat  = version_compare($plugin_version, '0.4.5');
			$status         = $Package->Status;
			$notes          = empty($Package->Notes) ? __("No notes were given for this package", 'wpduplicator') : $Package->Notes;

			//Links
			$uniqueid  			= "{$row['name']}_{$row['hash']}";
			$sqlfilelink		= $Package->StoreURL . "{$uniqueid}_database.sql";
			$packagepath 		= $Package->StoreURL . "{$uniqueid}_archive.zip";
			$installerpath		= $Package->StoreURL . "{$uniqueid}_installer.php";
			$logfilelink		= $Package->StoreURL . "{$uniqueid}.log";
			$installfilelink	= "{$installerpath}?get=1&file={$uniqueid}_installer.php";
			$logfilename	    = "{$uniqueid}.log";

			?>

			<!-- COMPLETE -->
			<?php if ($status == 3) : ?>
				<tr class="dup-pack-info">
					<td style="padding-right:20px !important"><input name="delete_confirm" type="checkbox" id="<?php echo $row['id'] ;?>" /></td>
					<td><a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');">[<?php echo __("View", 'wpduplicator') . ' ' . $row['id'];?>]</a></td>
					<td><?php echo date( "m-d-y G:i", strtotime($row['created']));?></td>
					<td><?php echo DUP_Util::ByteSize($Package->Archive->Size); ?></td>
					<td class='pack-name'><?php	echo $Package->Name ;?></td>
					<td style="width:90%;" class="get-btns">	
						<button id="<?php echo "{$uniqueid}_installer.php" ?>" class="button no-select" onclick="Duplicator.Pack.DownloadFile('<?php echo $installfilelink; ?>', this); return false;"><i class="fa fa-bolt"></i> <?php _e("Installer", 'wpduplicator') ?></button> &nbsp;
						<button id="<?php echo "{$uniqueid}_archive.zip" ?>" class="button no-select" onclick="Duplicator.Pack.DownloadFile('<?php echo $packagepath; ?>', this); return false;"><i class="fa fa-bars"></i> <?php _e("Archive", 'wpduplicator') ?></button>
					</td>
				</tr>
				<tr>
					<td colspan="8" id="<?php echo $detail_id; ?>" class="dup-pack-details">
						<b><?php _e("Version", 'wpduplicator') ?>:</b> <?php echo $plugin_version ?>  &nbsp;
						<b><?php _e("User", 'wpduplicator') ?>:</b> <?php echo $row['owner']; ?>  &nbsp;
						<b><?php _e("Secure Name", 'wpduplicator')?>:</b> <?php echo $Package->NameHash ;?> <br/>
						<b><?php _e("Notes", 'wpduplicator')?>:</b> <?php echo $notes ?> 
						<div style='margin:5px 0px 0px 0px'>
							
							<button class="button" onclick="Duplicator.Pack.ShowLinksDialog(<?php echo "'{$sqlfilelink}', '{$packagepath}', '{$installfilelink}', '{$logfilelink}' " ;?>); return false;" class="thickbox"><i class="fa fa-lock"></i> <?php _e("Show Links", 'wpduplicator')?></button> &nbsp; 
							<button class="button" onclick="window.open(<?php echo "'{$sqlfilelink}', '_blank'" ;?>); return false;"><i class="fa fa-file"></i> &nbsp; <?php _e("SQL File", 'wpduplicator')?></button> &nbsp; 
							<button class="button" onclick="Duplicator.OpenLogWindow(<?php echo "'{$logfilename}'" ;?>); return false;"><i class="fa fa-lock"></i> &nbsp; <?php _e("View Log", 'wpduplicator')?></button> 
						</div>
					</td>
				</tr>	
			<!-- NOT COMPLETE -->				
			<?php else : ?>	
				<tr class="dup-pack-info">
					<td style="padding-right:20px !important"><input name="delete_confirm" type="checkbox" id="<?php echo $uniqueid ;?>" /></td>
					<td><a href="javascript:void(0);" onclick="return Duplicator.Pack.ToggleDetail('<?php echo $detail_id ;?>');">[<?php echo __("View", 'wpduplicator') . ' ' . $row['id'];?>]</a></td>
					<td><?php echo $row['owner'];?></td>
					<td><?php echo date( "m-d-y G:i", strtotime($row['created']));?></td>
					<td><?php echo $Package->Archive->Size;?></td>
					<td class='pack-name'><?php echo $Package->Name ;?></td>
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
							<b><?php _e("Secure Name", 'wpduplicator')?>:</b> <?php echo $Package->NameHash ;?> <br/>
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
			<?php
			$rowCount++;
		}
	?>
	<tfoot>
		<tr>
			<th colspan="8" style='text-align:right; font-size:12px'>						
				<?php echo _e("Packages", 'wpduplicator') . ': ' . $totalElements; ?> |
				<?php echo _e("Total Size", 'wpduplicator') . ': ' . DUP_Util::ByteSize($totalSize); ?> 
			</th>
		</tr>
	</tfoot>
	</table>
<?php endif; ?>	
</form>


<form id="dup-paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" style="display:none"> 
	<input name="cmd" type="hidden" value="_s-xclick" /> 
	<input name="hosted_button_id" type="hidden" value="EYJ7AV43RTZJL" /> 
	<img src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" /> 
</form>


<!-- ==========================================
DIALOG: QUICK PATH -->
<?php add_thickbox(); ?>
<div id="dup-dlg-quick-path" title="<?php _e('Download Links', 'wpduplicator'); ?>" style="display:none">
	<p>
		<i class="fa fa-lock"></i>
		<?php _e("The following links contain sensitive data.  Please share with caution!", 'wpduplicator');	?>
	</p>
	
	<div style="padding: 0px 15px 15px 15px;">
		<a href="javascript:void(0)" style="display:inline-block; text-align:right" onclick="Duplicator.Pack.GetLinksText()">[Select All]</a> <br/>
		<textarea id="dup-dlg-quick-path-data" style='border:1px solid silver; border-radius:3px; width:99%; height:225px; font-size:11px'></textarea><br/>
		<i style='font-size:11px'><?php _e("The database SQL script is a quick link to your database backup script.  An exact copy is also stored in the package.", 'wpduplicator'); ?></i>
	</div>
</div>



<script type="text/javascript">
jQuery(document).ready(function($) {
		
	/*	----------------------------------------
	 *	METHOD: Shows hides the package detail items
	 *	@param id	The id to toggle */
	Duplicator.Pack.ToggleDetail = function(id) {
		$('#' + id).toggle();
		return false;
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
		
		if (confirm("<?php _e('Are you sure, you want to delete the selected package(s)?', 'wpduplicator') ?>")){
			
			$.ajax({
				type: "POST",
				url: ajaxurl,
				dataType: "json",
				data: {action : 'duplicator_package_delete', duplicator_delid : list },
				success: function(data) { 
					//console.log(data); //Debug return
					Duplicator.ReloadWindow(data); 
				}
			});
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
	
	/*	----------------------------------------
	 *	METHOD: Shows the 'Download Links' dialog
	 *	@param db		The path to the sql file
	 *	@param install	The path to the install file 
	 *	@param pack		The path to the package file */
	Duplicator.Pack.ShowLinksDialog = function(db, install, pack, log) {
		
		var url = '#TB_inline?width=650&height=350&inlineId=dup-dlg-quick-path';
		tb_show("<?php _e('Package File Links', 'wpduplicator') ?>", url);
		
		var msg = <?php printf('"%s:\n" + db + "\n\n%s:\n" + install + "\n\n%s:\n" + pack + "\n\n%s:\n" + log;', 
			__("DATABASE",  'wpduplicator'), 
			__("PACKAGE", 'wpduplicator'), 
			__("INSTALLER",   'wpduplicator'),
			__("LOG", 'wpduplicator')); 
		?>
		$("#dup-dlg-quick-path-data").val(msg);
		return false;
	}
	
	//LOAD: 'Download Links' Dialog and other misc setup
	Duplicator.Pack.GetLinksText = function() {$('#dup-dlg-quick-path-data').select();};	
	
});
</script>

