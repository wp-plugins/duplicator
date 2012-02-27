<?php
	$package_name = date('Ymd') . '_' . get_bloginfo( 'name', 'display' );
	$package_name = substr(str_replace(' ', '', $package_name), 0 , 40);
	$package_name = sanitize_file_name($package_name);
	
	global $wpdb;
	$result = $wpdb->get_results('SELECT * FROM '. $wpdb->prefix . "duplicator ORDER BY bid DESC", ARRAY_A);
	$total_elements = count($result);
	
	//Settings
	$setup_link_enabled  = (strlen($GLOBALS['duplicator_opts']['nurl']) == "0")  ? false : true;
	$email_me_enabled    = $GLOBALS['duplicator_opts']['email-me'] == "0" 	 	 ? false : true;
	$duplicator_dbiconv	 = $GLOBALS['duplicator_opts']['dbiconv'] == "0" 		 ? false : true;
	
	
	function duplicator_get_download_link() {
		return get_home_url(null, '', is_ssl() ? 'https' : 'http') . '/' . DUPLICATOR_SSDIR_NAME . '/' ;
	}
	
?>

<script type="text/javascript">
	//Unique namespace to avoid conflicts
	Duplicator = new Object();

	//Protect from other plugins not using jQuery or other versions
	jQuery.noConflict()(function($){
		jQuery(document).ready(function() {
		
			/*  ============================================================================
			MAIN GRID
			Actions that revolve around the main grid */
			jQuery("input#select-all").click(function (event) {
				var state = jQuery('input#select-all').is(':checked') ? 1 : 0;
				jQuery("input[name=delete_confirm]").each(function() {
					 this.checked = (state) ? true : false;
					 Duplicator.rowColor(this);
				});
			});

			Duplicator.rowColor = function(chk) {
				if (chk.checked) {
					jQuery(chk).parent().parent().css("text-decoration", "line-through");
				} else {
					jQuery(chk).parent().parent().css("text-decoration", "none");
				}
			}
			
			Duplicator.downloadPackage = function(btn) {
				jQuery(btn).css('background-color', '#dfdfdf');
				window.location = '<?php echo duplicator_get_download_link(); ?>' + btn.id;
			}
			
			Duplicator.downloadInstaller = function(btn) {
				jQuery(btn).css('background-color', '#dfdfdf');
				window.location='<?php echo DUPLICATOR_PLUGIN_URL .'files/install.php?download'; ?>'
			}
			
			Duplicator.startCreate = function() {
				jQuery('span#span-status').html("Evaluating WordPress Setup. Please Wait...");
			}
			
			/*  ============================================================================
			DIALOG WINDOWS
			Browser Specific. IE9 does not support modal correctly this is a workaround  */
			Duplicator._dlgCreate = function(evt, ui) {
				if (! jQuery.browser.msie) {
					jQuery('#' + this.id).dialog('option', 'modal',  	true);
					jQuery('#' + this.id).dialog('option', 'draggable',  true);
				} else {
					jQuery('#' + this.id).dialog('option', 'draggable',  false);
					jQuery('#' + this.id).dialog('option', 'open',  function() {jQuery("div#wpwrap").addClass('ie-simulated-overlay');} );
				}
			}
			Duplicator._dlgClose = function(evt, ui) {
				if (jQuery.browser.msie) {jQuery("div#wpwrap").removeClass('ie-simulated-overlay');}
			}
			jQuery("#dialog-options").dialog( {autoOpen:false, height:610, width:750, create:Duplicator._dlgCreate, close:Duplicator._dlgClose });
			jQuery("#dialog-log-copy").dialog({autoOpen:false, height:600, width:700, create:Duplicator._dlgCreate, close:Duplicator._dlgClose });
				

			/*  ============================================================================
			LOG PANE: 
			Methods used to control and render the log pane visibility	*/
			jQuery('select#log_level').val(<?php echo DUPLICATOR_LOGLEVEL ?>);
			<?php 
				echo (DUPLICATOR_LOGLEVEL >= 1) ?  'jQuery("#log-pane").css("display", "block")' : 'jQuery("#log-pane").css("display", "none")';
			?>
		
			Duplicator.Log = function(msg, seperator) {
				jQuery('#log-msg').append(msg); 
				if (seperator) {
					jQuery('#log-msg').append('======================================================= <br/>\n');
				}
				jQuery("#log-msg").animate({ scrollTop: jQuery("#log-msg").attr("scrollHeight") }, 2000)
			}
			Duplicator.logToggle	= function() { jQuery("#log-pane").toggle(500);}			
			Duplicator.logClearPane	= function() { jQuery('#log-msg').empty();}
			Duplicator.logSizePane 	= function() { jQuery("#log-pane").css("width", jQuery("div#wpbody").width() - 7);}
			Duplicator.logCopy		= function() { 
				jQuery("textarea#log-msg-txt").val(jQuery('#log-msg').text());
				jQuery("#dialog-log-copy").dialog("open");
				jQuery("textarea#log-msg-txt").select();
			}
			
			var _sliderHeight = <?php echo isset($GLOBALS['duplicator_opts']['log_paneheight']) ? $GLOBALS['duplicator_opts']['log_paneheight'] : 300; ?>;
			jQuery("#slider-range-min").slider({
				range: "min", 
				value: _sliderHeight , 
				min: 100, 
				max: 400,
				slide: function( event, ui ) {
					jQuery( "#log_paneheight" ).val( ui.value );
					jQuery("#log-msg").css("height", ui.value);
				},
				stop: function(event, ui) {Duplicator.saveSettings(false); }
			});
			jQuery("#log_paneheight").val(jQuery("#slider-range-min").slider("value") )
			jQuery("#log-msg").css("height", _sliderHeight + "px");
			Duplicator.logSizePane();
			jQuery(window).bind('resize', function() {Duplicator.logSizePane();});
			
			
			/*  ============================================================================
			OPTIONS DIALOG
			Actions that revolve around the options dialog */
			jQuery("#tabs-opts").tabs();
			Duplicator.optionsSystemCheck = function() {
				jQuery("#dialog-options").dialog("open");
				jQuery('#tabs-opts').tabs('option', 'selected', 2);
			}
			Duplicator.optionsAppendByPassList = function(path) {
				 jQuery('#dir_bypass').append(path);
				 jQuery('#tabs-opts').tabs('option', 'selected', 0);
				 jQuery('#dir_bypass').animate({ borderColor: "blue", borderWidth: 2 }, 3000);
				 jQuery('#dir_bypass').animate({ borderColor: "#dfdfdf", borderWidth: 1  }, 100);
			}
			Duplicator.optionsOpen  = function() {jQuery("div#dialog-options").dialog("open");}
			Duplicator.optionsClose = function() {jQuery('div#dialog-options').dialog('close');}
			
			/* Row Details */
			Duplicator.toggleDetail = function(id) {
				jQuery('#' + id).toggle();
				return false;
			}
			
			//MISC
			jQuery("div#div-render-blanket").show();
			Duplicator.newWindow = function(url) {window.open(url);}
		});
	});
</script>


<!-- ==========================================
MAIN FORM: Lists all the backups 			-->
<div class="wrap">
	<form id="form-duplicator" method="post">
		<?php screen_icon(); ?><h2> Duplicator</h2>
		
		<!-- TOOLBAR -->
		<table border="0" id="toolbar-table">
			<tr valign="top">
				<td style="white-space:nowrap; vertical-align:middle"><label style="font-size:14px !important;">Package Name:</label></td>
				<td style="white-space:nowrap;width:100%; vertical-align:middle"><input name="package_name" type="text" style="width:250px" value="<?php echo $package_name ?>" maxlength="40" /></td>
				<td><input type="submit" id="btn-create-pack" class="btn-create-pack" value="..." name="submit" title="Create Package" onclick="Duplicator.startCreate()" ondblclick="javascript:return void(0);" /></td>
				<td><input type="button" id="btn-delete-pack" title="Delete selected package(s)"/></td>
				<?php if ($setup_link_enabled) : ?>
					<td align="center"><input type="button" class="btn-setup-link" onclick="window.open('<?php echo $GLOBALS['duplicator_opts']['nurl'] ?>/install.php', '_blank')" title="Launch the installer window." /></td>
				<?php endif; ?>			
				<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/hdivider.png" style="height:26px; padding:0px 5px 0px 5px"/></td>
				<td><input type="button"  id="btn-opts-dialog" class="btn-opts-dialog" title="Options..." onclick="Duplicator.optionsOpen()" /></td>	
				<?php if (DUPLICATOR_LOGLEVEL > 1) : ?>
					<td align="center"><input type="button" class="btn-diag-dialog" onclick="Duplicator.logToggle()" title="Show Log Pane..." /></td>
				<?php endif; ?>		
				<td><img src="<?php echo DUPLICATOR_PLUGIN_URL ?>img/hdivider.png" style="height:26px; padding:0px 5px 0px 5px"/></td>
				<td><input type="button" id="btn-help-dialog" onclick='Duplicator.newWindow("<?php echo DUPLICATOR_HELPLINK ?>")' title="Help..." /></td>
				<td><input type="button" id="btn-contribute-dialog" onclick='Duplicator.newWindow("<?php echo DUPLICATOR_GIVELINK ?>")' title="Partner with us..." /></td>
				<td>
				<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://lifeinthegrid.com/duplicator" data-text="Duplicate Your Efforts! Tools for Online Entrepreneurs" data-via="lifeinthegrid" data-size="large" data-related="lifeinthegrid" data-count="none" data-hashtags="Duplicator">Tweet</a>
				<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
				</td>
			</tr>
		</table>

		<!-- STATUS 
		id comes from wp-themes: major-publishing-actions  keeps themeing correct -->
		<table width="100%"  class="widefat pack-table" cellspacing="0" border="1">
			<tr>
				<td width="100%" style="font-size:14px; vertical-align:middle">
					<b>Status:</b> 
					<span id="span-status">Ready to create new package.</span>
					<img id="img-status-error" src="<?php echo DUPLICATOR_PLUGIN_URL ?>img/error.png" style="height:16px; width:16px; display:none; margin-top:3px; margin:0px" valign="bottom" />
					<img id="img-status-progress" src="<?php echo DUPLICATOR_PLUGIN_URL ?>img/progress.gif" style="height:10px; width:46px; display:none" />
				</td>
				<?php if($total_elements!=0)  :	?>
					<td style="white-space:nowrap;">
						<input type="button" id="duplicator-installer" value="Installer" class="btn-save-packitem" onclick="Duplicator.downloadInstaller(this)" title="Download this installer" />
					</td>
				<?php endif; ?>	
			</tr>
		</table><div style="height:5px"></div>
		
		
		<!-- TABLE LIST -->
		<table width="100%" class="widefat pack-table" border="1" >
			<thead>
				<tr style="font-weight:bold">
					<th><input type="checkbox" id="select-all"  title="Select all packages" style="margin:0px;padding:0px 0px 0px 5px;" /></th>
					<th title="The unique number of this package set">ID</th>
					<th title="Creator of this package">Owner</th>
					<th title="Server time when package was created">Created</th>
					<th title="Compressed size of the package">Size</th>
					<th title="The name of the package" style="width:100%">Package Name</th>
					<th title="Your database and WordPress files." style="text-align:center;" >Package</th>
				</tr>
			</thead>
			<?php
			if($total_elements != 0)  {
				$ct = 0;
				$token_len = (DUPLICATOR_SECURE_TOKEN_LEN + 5);
				while($ct < $total_elements) {
					$row	   = $result[$ct];
					$settings  = unserialize($row['settings']);
					$detail_id = "duplicator-detail-row-{$ct}";
					$packname  = empty($row['packname']) ? $row['zipname'] : $row['packname'];
					?>
					<tr class="pack-information">
						<td><input name="delete_confirm" type="checkbox" id="<?php echo $row['zipname'];?>" onclick="Duplicator.rowColor(this)" /></td>
						<td><a href="javascript:void(0);" onclick="return Duplicator.toggleDetail('<?php echo $detail_id ;?>');">[<?php echo $row['bid'];?>]</a></td>
						<td><?php echo $row['owner'];?></td>
						<td><?php echo date( "m-d-y G:i", strtotime($row['created']));?></td>
						<td><?php echo duplicator_bytesize($row['zipsize']);?></td>
						<td><?php echo $packname ;?></td>
						<td><input type="button" value="Package" id="<?php echo $row['zipname'];?>" class="btn-save-packitem" onclick="Duplicator.downloadPackage(this)" title="Download this package" /></td>
					</tr>
					<tr>
						<td colspan="7" id="<?php echo $detail_id; ?>" class="pack-details-row">
							<?php 
								$plugin_version = empty($settings['plugin_version']) ? 'unknown' : $settings['plugin_version'];
								$secure_token   = empty($settings['secure_token'])   ? 'unknown' : $settings['secure_token'];
								$download_link  = duplicator_get_download_link() . $row['zipname'];
								
								echo "<b>Plugin Version:</b> {$plugin_version} <br />" ;
								echo "<b>Security Token:</b> {$secure_token} <br />" ;
								echo "<b>File Name:</b> {$row['zipname']} <br />" ;
								echo "<b>File Path:</b> " . DUPLICATOR_SSDIR_PATH . "/{$row['zipname']} <br />" ;
								echo "<b>URL Path:</b> <a href='{$download_link}'>{$download_link}</a>"  ;
							?>
						</td>
					</tr>
					<?php
					$ct++;
				}
			} else {
				echo "<tr><td colspan='7' <div style='padding:60px 20px;text-align:center'><b style='font-size:14px'>No packages found.<br/> To create a new package, enter a name click the create button <input type='button' class='btn-create-pack' /><br/> Check your <a href='javascript:void(0)' onclick='Duplicator.optionsSystemCheck()'>servers compatability</a> with the duplicator.</b><br/><br/><i>This process will backup all your files and database.<br/> Creating a package may take several minutes if you have a large site.<br/> This window should remain open for the process to complete.<br/><br/> Please be patient while we work through this Beta version.<br/>Please report any issues to <a href='http://support.lifeinthegrid.com' target='_blank'>support.lifeinthegrid.com</a> </i></div></td></tr>";
			}
			?>
			<tfoot>
				<tr style="background-color:#F1F1F1;font-weight:bold">
					<th></th>
					<th title="The unique number of this package set">ID</th>
					<th title="Creator of this package">Owner</th>
					<th title="Server time when package was created">Created</th>
					<th title="Compressed size of the package">Size</th>
					<th title="The name of the package" style="width:100%">Package Name</th>
					<th title="Your database and WordPress files." style="text-align:center;" >Package</th>
				</tr>
			</tfoot>
		</table>
	</form>
</div>

<?php 
	//INLINE DIALOG WINDOWS
	require_once('view.options.php');
?>

<!-- ==========================================
#LOG PANE-->
<div id="log-pane">
	<div id="log-area">
	<table id="log-table" class="widefat" align="center">
		<thead>
		<tr>
			<th style="width:100%"><b>Logging Pane:</b> </th>
			<th style="text-align:right;white-space:nowrap">
				<a href="javascript:Duplicator.logClearPane();" title="clear the logging pane">[clear]</a>&nbsp;
				<a href='javascript:Duplicator.logCopy()' title="report this issue">[copy]</a>&nbsp;
				<a href='javascript:window.location.reload()' title="reload this window">[reload page]</a>&nbsp;
			</th>
		</tr>
		</thead>
		<tbody>
			<tr>
				<td id="post-status-info" colspan="2">
					<div id="log-area">
					<div id="log-msg"></div>
						<table  style="width:100%">
							<tr>
								<td style="width:100%"><i style="font-size:11px">To disable this pane, set reporting level to 'none' in the <a href="javascript:void(0)" onclick="Duplicator.optionsOpen()">options dialog</a>. </i></td>
								<td><div id="slider-range-min" style="width:150px; margin-top:8px"></div></td>
								<td style="white-space:nowrap">Height:<input type="text" id="log_paneheight" style="border:0;font-size:11px;background-color:transparent;width:50px;padding-top:5px" /></td>
							</tr>
						</table>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	</div>
</div>
</div>

<div id="dialog-log-copy" title="Logging - Copy (Ctrl + C)">
	<textarea id="log-msg-txt" style="width:97%;min-height:500px; height:95%;font-size:11px"></textarea><br/>
	<a href="<?php echo DUPLICATOR_HELPLINK ?>" target="_blank">Visit Support Center</a>
</div>



