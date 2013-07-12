<!-- ==========================================
#OPTIONS DIALOG -->
<div id="div-render-blanket" style="display:none;">
<div id="dialog-options" title="<?php _e("Package Options", 'wpduplicator') ?>">
	<form id="form-duplicator-opts" method="post">
		<input type="hidden" name="action" value="task_save" />
		<div id="dup-tabs-opts">
			<ul>
				<li><a href="#dup-tabs-opts-1"><?php _e("Package", 'wpduplicator') ?></a></li>
				<li><a href="#dup-tabs-opts-2"><?php _e("Installer", 'wpduplicator') ?></a></li>
			</ul>

			<!-- =============================================================================
			TAB 1 PACKAGE -->
			<div id="dup-tabs-opts-1">
				<div style="text-align:left;">

					<!-- PROCESSING -->
					<fieldset>
						<legend><b><?php _e("Processing", 'wpduplicator') ?></b></legend>
						<input type="checkbox" name="email-me" id="email-me" <?php echo ($email_me_enabled) ? 'checked="checked"' : ''; ?>  />
						<label for="email-me"><?php _e("Email a notice when completed", 'wpduplicator');?></label><br/>
						<input type="text" name="email_others" id="email_others"  value="<?php echo esc_html($GLOBALS['duplicator_opts']['email_others']); ?>" placeholder="mail1@mysite.com;mail2@mysite.com;" title="<?php _e("WP-admin email is included.  Add extra emails semicolon separated.", 'wpduplicator') ?>"  /> <br />
					</fieldset><br/>

					<!-- FILTERS -->
					<?php
						$uploads = wp_upload_dir();
						$upload_dir = duplicator_safe_path($uploads['basedir']);
					?>
					<fieldset>
						<legend><b><?php _e("Exclusion Filters", 'wpduplicator') ?></b></legend>
						<label for="dir_bypass" title="<?php _e("Separate all filters by semicolon", 'wpduplicator'); ?>"><?php _e("Directories", 'wpduplicator') ?>: </label>
						<div class='dup-quick-links'>
							<a href="javascript:void(0)" onclick="Duplicator.Pack.OptionsAddExcludePath('<?php echo rtrim(DUPLICATOR_WPROOTPATH, '/'); ?>')">[<?php _e("root path", 'wpduplicator') ?>]</a>
							<a href="javascript:void(0)" onclick="Duplicator.Pack.OptionsAddExcludePath('<?php echo rtrim($upload_dir , '/'); ?>')">[<?php _e("wp-uploads", 'wpduplicator') ?>]</a>
							<a href="javascript:void(0)" onclick="jQuery('#dir_bypass').val('')"><?php _e("(clear)", 'wpduplicator') ?></a>
						</div>
						<textarea name="dir_bypass" id="dir_bypass" style="height:110px;" placeholder="/root/path1;/root/path2;"><?php echo esc_textarea($GLOBALS['duplicator_opts']['dir_bypass']); ?></textarea><br/>
						<label class="no-select" title="<?php _e("Separate all filters by semicolon", 'wpduplicator'); ?>"><?php _e("File extensions", 'wpduplicator') ?>:</label>
						<div class='dup-quick-links'>
							<a href="javascript:void(0)" onclick="Duplicator.Pack.OptionsAddExcludeExts('avi;mov;mp4;mpeg;mpg;swf;wmv;aac;m3u;mp3;mpa;wav;wma')">[<?php _e("media", 'wpduplicator') ?>]</a>
							<a href="javascript:void(0)" onclick="Duplicator.Pack.OptionsAddExcludeExts('zip;rar;tar;gz;bz2;7z')">[<?php _e("archive", 'wpduplicator') ?>]</a>
							<a href="javascript:void(0)" onclick="jQuery('#skip_ext').val('')"><?php _e("(clear)", 'wpduplicator') ?></a>
						</div>
						<textarea name="skip_ext" id="skip_ext" style="height:60px;" placeholder="ext1;ext2;ext3;"><?php echo esc_textarea($GLOBALS['duplicator_opts']['skip_ext']); ?></textarea>

						<p class="help"><?php _e("This will exclude all directories and extensions from the package file. This will slow down package creation times.", 'wpduplicator'); ?></p>
					</fieldset><br/>

					<!--div style='position:absolute; bottom:5px'>
						<i style='font-size:10px'><?php _e("Having issues saving these options?  Temporarily disable all 'Object Caches' (i.e. W3C Total Object Cache)", 'wpduplicator') ?>.</i>
					</div-->
				</div>
			</div>


			<!-- =============================================================================
			TAB 2 INSTALLER -->
			<div id="dup-tabs-opts-2">
				
				<div class="dup-tabs-opts-header-1"><?php echo _e('STEP 1 - INPUTS'); ?></div><br/>
				<table style="width:95%; margin-left:20px">
					<tr>
						<td colspan="2"><div class="dup-tabs-opts-header-2"><?php _e("MySQL Server", 'wpduplicator') ?></div></td>
					</tr>
					<tr>
						<td style="width:130px"><?php _e("Host", 'wpduplicator') ?></td>
						<td><input type="text" name="dbhost" id="dbhost" class="txt-settings" value="<?php echo esc_html($GLOBALS['duplicator_opts']['dbhost']); ?>"  maxlength="200" placeholder="localhost"/></td>
					</tr>
					<tr>
						<td><?php _e("Database", 'wpduplicator') ?></td>
						<td><input type="text" name="dbname" id="dbname" class="txt-settings" value="<?php echo esc_html($GLOBALS['duplicator_opts']['dbname']); ?>" maxlength="100" placeholder="mydatabaseName" /></td>
					</tr>							
					<tr>
						<td><?php _e("User", 'wpduplicator') ?></td>
						<td><input type="text" name="dbuser" id="dbuser" class="txt-settings" value="<?php echo esc_html($GLOBALS['duplicator_opts']['dbuser']); ?>"  maxlength="100" placeholder="databaseUserName" /></td>
					</tr>
					<tr>
						<td colspan="2"><div class="dup-tabs-opts-header-2"><?php _e("Advanced Options", 'wpduplicator') ?></div></td>
					</tr>						
					<tr>
						
						<td colspan="2">
							<table>
								<tr>
									<td style="width:130px"><?php _e("SSL", 'wpduplicator') ?></td>
									<td style="padding-right: 20px; white-space: nowrap">
										<input type="checkbox" name="ssl_admin" id="ssl_admin" <?php echo ($GLOBALS['duplicator_opts']['ssl_admin']) ? "checked='checked'" : ""; ?>  />
										<label class="chk-labels" for="ssl_admin"><?php _e("Enforce on Admin", 'wpduplicator') ?></label>
									</td>
									<td>
										<input type="checkbox" name="ssl_login" id="ssl_login" <?php echo ($GLOBALS['duplicator_opts']['ssl_login']) ? "checked='checked'" : ""; ?>  />
										<label class="chk-labels" for="ssl_login"><?php _e("Enforce on Logins", 'wpduplicator') ?></label>
									</td>
								</tr>
								<tr>
									<td><?php _e("Cache", 'wpduplicator') ?></td>									
									<td style="padding-right: 20px; white-space: nowrap">
										<input type="checkbox" name="cache_wp" id="cache_wp" <?php echo ($GLOBALS['duplicator_opts']['cache_wp']) ? "checked='checked'" : ""; ?>  />
										<label class="chk-labels" for="cache_wp"><?php _e("Keep Enabled", 'wpduplicator') ?></label>	
									</td>
									<td>
										<input type="checkbox" name="cache_path" id="cache_path" <?php echo ($GLOBALS['duplicator_opts']['cache_path']) ? "checked='checked'" : ""; ?>  />
										<label class="chk-labels" for="cache_path"><?php _e("Keep Home Path", 'wpduplicator') ?></label>			
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table><br /><br />
				
				<div class="dup-tabs-opts-header-1"><?php echo _e('STEP 2 - INPUTS'); ?></div><br/>
				
				<table style="width:95%; margin-left:20px">
					<tr>
						<td style="width:130px"><?php _e("New URL", 'wpduplicator') ?></td>
						<td><input type="text" name="url_new" id="url_new" class="txt-settings" value="<?php echo esc_html($GLOBALS['duplicator_opts']['url_new']); ?>" placeholder="http://mynewsite.com" /></td>
					</tr>
				</table>
				
				
				<p class="help" style="position:absolute; bottom:20px"><?php _e("The installer can have these fields pre-filled at install time."); ?> <strong><?php _e('All values are optional.'); ?></strong></p>
			</div>
		</div>
	</form>
</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function($) {
	
	/*	----------------------------------------
	*	METHOD: Saves the Package settings */ 
	Duplicator.Pack.OptionsSave = function() {
		var log_level  		= $("select#log_level").val() ? $("select#log_level").val() : 0;
		var email_others	= $("input#email_others").val();
		var dir_bypass 		= $("textarea#dir_bypass").val();

		//append semicolon if user forgot
		if (dir_bypass.length > 1) {
			var has_semicolon	= dir_bypass.charAt(dir_bypass.length - 1) == ";";
			dir_bypass		= (has_semicolon) ? dir_bypass : dir_bypass + ";";
			$("textarea#dir_bypass").val(dir_bypass);
		}
		
		var buttons = Duplicator.Pack.OptionsDialog.dialog( "option", "buttons" );
		buttons.save.text = '<?php _e("Saving", 'wpduplicator') ?>...';
		Duplicator.Pack.OptionsDialog.dialog( "option", "buttons", buttons );
		
		$.ajax({
			type: "POST",
			url: ajaxurl,
			timeout: 10000000,
			data: 
			{
				'action'  					: 'duplicator_task_save',
				'dbhost' 					: $("input#dbhost").val(),
				'dbname'  					: $("input#dbname").val(),
				'dbuser'  					: $("input#dbuser").val(),
				'url_new'  					: $("input#url_new").val(),
				'ssl_admin'  				: $('#ssl_admin').is(':checked') ? 1 : 0,
				'ssl_login'					: $('#ssl_login').is(':checked') ? 1 : 0,
				'cache_wp'					: $('#cache_wp').is(':checked') ? 1 : 0,
				'cache_path'				: $('#cache_path').is(':checked') ? 1 : 0,
				'email-me'  				: $('#email-me').is(':checked') ? 1 : 0,
				'email_others'  			: email_others,
				'skip_ext'  				: $("#skip_ext").val(),
				'dir_bypass'  				: $("#dir_bypass").val(),
				'log_level'  				: log_level
			},
			beforeSend: function() {
				Duplicator.StartAjaxTimer(); },
			complete: function() {Duplicator.EndAjaxTimer(); },
			success: function(data) { 
				$('#opts-save-btn').val("<?php _e('Saving', 'wpduplicator') ?>...");
				window.location.reload();
			},
			error: function(data) { 
				Duplicator.Pack.ShowError('Duplicator.Pack.OptionsSave', data);
			}
		});
	 };
	 
	/*	----------------------------------------
	*	OBJECT: Create the package options dialog */ 
	Duplicator.Pack.OptionsDialog = $("#dialog-options").dialog({
		autoOpen:false, height:650, width:775, create:Duplicator.UI.CreateDialog, close:Duplicator.UI.CloseDialog, 
		buttons: {
			'save' : {
				'text' : "<?php _e("Save", 'wpduplicator') ?>",
				'class': "button action",
				'click' : Duplicator.Pack.OptionsSave
				},
			'cancel' : {
				'text' : "<?php _e("Cancel", 'wpduplicator') ?>",
				'class': "button action",
				'click' : function() { $(this).dialog("close");}
			}
		}	
	});
	
	/*	----------------------------------------
	*	METHOD: Appends the Cache plugin path to the directory filter
	*	@param path		The path to add to the filter */ 
	Duplicator.Pack.OptionsAppendCachePath = function(path) {
		Duplicator.Pack.ShowOptionsDialog();
		$('#dir_bypass').append(path + ";");
		$('#dup-tabs-opts').tabs('option', 'selected', 0);
		$('#dir_bypass').animate({ borderColor: "blue", borderWidth: 2 }, 3000);
		$('#dir_bypass').animate({ borderColor: "#dfdfdf", borderWidth: 1  }, 100);
	};
	
	/*	----------------------------------------
	*	METHOD: Appends a path to the directory filter 
	*	@param path		The path to add to the filter */ 
	Duplicator.Pack.OptionsAddExcludePath = function(path) {
		var text = $("#dir_bypass").val() + path + ';';
		$("#dir_bypass").val(text);
	};
	
	/*	----------------------------------------
	*	METHOD: Appends a path to the extention filter 
	*	@param path	The list of files to add to the filter */ 
	Duplicator.Pack.OptionsAddExcludeExts = function(path) {
		var text = $("#skip_ext").val() + path + ';';
		$("#skip_ext").val(text);
	};
	
	Duplicator.Pack.ShowOptionsDialog  = function() {$("#dialog-options").dialog("open");};
	$("#div-render-blanket").show();
	$("#dup-tabs-opts").tabs({ heightStyle: "auto" });
});
</script>