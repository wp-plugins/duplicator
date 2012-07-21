<!-- ==========================================
#OPTIONS DIALOG -->
<div id="div-render-blanket" style="display:none;">
<div id="dialog-options" title="<?php _e("Duplicator Options", 'WPDuplicator') ?>">
	<form id="form-duplicator-opts" method="post">
		<input type="hidden" name="action" value="settings" />
		<div id="dup-tabs-opts">
			<ul>
				<li><a href="#dup-tabs-opts-1"><?php _e("Package", 'WPDuplicator') ?></a></li>
				<li><a href="#dup-tabs-opts-2"><?php _e("Installer", 'WPDuplicator') ?></a></li>
				<!--li><a href="#dup-tabs-opts-3"><?php _e("FTP", 'WPDuplicator') ?></a></li>-->
			</ul>
			
			<!-- =============================================================================
			TAB 1 PACKAGE -->
			<div id="dup-tabs-opts-1">
				<div style="text-align:left;">
					<fieldset style="width:97%; height:250px;">
						<legend><?php _e("Processing", 'WPDuplicator') ?></legend>
						
						<?php 
							$safe_value = ini_get('safe_mode');
							if( stristr($safe_value, 'on') ){
								$max_time_val  = ini_get('max_execution_time');
								$max_read_only = "readonly='true'";
							} else {
								$max_time_val  = $GLOBALS['duplicator_opts']['max_time'];
								$max_read_only = "";
							}
						?>
						<table width="100%" border="0">
							<tr>
								<td style="width:50%"><?php _e("Max Time", 'WPDuplicator') ?>: <input type="text" name="max_time" id="max_time" value="<?php echo $max_time_val ?>" <?php echo $max_read_only ?> maxlength="4" style="width:75px" /> <?php _e("seconds", 'WPDuplicator') ?></td>
								<td style="width:50%"><?php _e("Max Memory", 'WPDuplicator') ?>: <input type="text" name="max_memory" id="max_memory" value="<?php echo preg_replace('/\D/', '', $GLOBALS['duplicator_opts']['max_memory'] ) ?>" maxlength="4" style="width:45px" /> MB</td>
							</tr>
						</table>						
						
						<label for="dir_bypass" style="font-size:13px">
							<?php _e("Directory Filters", 'WPDuplicator') ?>: 
							<i style="font-size:11px;display:inline-block"><?php printf("%s (/path1/dir1;/path2/dir2)<br/>", __("Separate by semicolon", 'WPDuplicator')); ?></i>
						</label> 
						<textarea name="dir_bypass" id="dir_bypass" style="width:625px;height:50px;background-color:#efefef;font-size:12px" /><?php echo $GLOBALS['duplicator_opts']['dir_bypass'] ?></textarea>
						
						<div class="dup-opts-directory-info">
							<?php printf("%s: %s",__("Root Path", 'WPDuplicator'), rtrim(duplicator_safe_path(WP_CONTENT_DIR), 'wp-content'));	?><br/>
							<?php _e('Pre-Zip Overview', 'WPDuplicator'); ?>:
							<span id='dup-opts-scannow-data'>
								<a href="javascript:void(0)" id="dup-opts-scannow-lnk" onclick="Duplicator.getSystemDirectory()"><?php _e("Scan Size", 'WPDuplicator') ?></a> 
							</span><br/>
						</div>
						
						<input type="checkbox" name="email-me" id="email-me" <?php echo ($email_me_enabled) ? 'checked="checked"' : ''; ?> /> 
						<label for="email-me"><?php _e("Email when complete. Add optional emails separate by semicolon", 'WPDuplicator') ?></label><br/>
						<input type="text" name="email_others" id="email_others"  value="<?php echo $GLOBALS['duplicator_opts']['email_others'] ?>" style="width:95%" /> 
						<br/>
						
					</fieldset><br/>
					
					<!-- ENCODEING OPTIONS -->
					<fieldset style="width:97%; height:100px;"  class='no-select'>
						<legend><?php _e("Database Encoding", 'WPDuplicator') ?></legend>
						
							<input type="checkbox" name="dbiconv" id="dbiconv" <?php echo ($duplicator_dbiconv) ? 'checked="checked"' : ''; ?> /> 
							<label for="dbiconv"><?php _e("Enable character conversion encoding", 'WPDuplicator') ?></label><br/>
					
							<i style='font-size:11px'>
								<?php 
									printf("%s %s %s %s <br/> %s",
										__("From", 'WPDuplicator'), DUPLICATOR_DB_ICONV_IN,
										__("to", 'WPDuplicator'), DUPLICATOR_DB_ICONV_OUT,
										__("Please disable this option for international character sets", 'WPDuplicator')) ;
										
								?>
							</i><br/><br/>					
							
					</fieldset>
					<div style='position:absolute; bottom:5px'>	
						<i style='font-size:10px'><?php _e("Having issues saving these options?  Temporarily disable all 'Object Caches' (i.e. W3C Total Object Cache)", 'WPDuplicator') ?>.</i>
					</div>
				</div>
			</div>
			
			
			<!-- =============================================================================
			TAB 2 INSTALLER -->
			<div id="dup-tabs-opts-2">
				<fieldset style="height:55px">
					<legend><?php _e("Settings Defaults", 'WPDuplicator') ?></legend>
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
						<tr>
							<td style="width:130px"><?php _e("Install URL", 'WPDuplicator') ?></td>
							<td><input type="text" name="nurl" id="nurl" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['nurl'] ?>" /></td>
						</tr>
					</table>
				</fieldset><br/>
			
				<fieldset style="height:165px">
					<legend><?php _e("Database Defaults", 'WPDuplicator') ?></legend>
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
					<tr>
						<td style="width:130px"><?php _e("Host", 'WPDuplicator') ?></td>
						<td><input type="text" name="dbhost" id="dbhost" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbhost'] ?>"  maxlength="2100"/></td>
					</tr>
					<tr>
						<td><?php _e("Name", 'WPDuplicator') ?></td>
						<td><input type="text" name="dbname" id="dbname" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbname'] ?>" maxlength="100" /></td>
					</tr>
					<tr>
						<td><?php _e("User", 'WPDuplicator') ?></td>
						<td><input type="text" name="dbuser" id="dbuser" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbuser'] ?>"  maxlength="100" /></td>
					</tr>
					</table>
				</fieldset>
				<i style="font-size:11px"><?php _e("The installer can have these fields pre-filled at install time.  These values are optional.", 'WPDuplicator') ?></i>
			</div>

			
			<!--div id="dup-tabs-opts-3">
					FTP in Version 1.1
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
					<tr>
						<td style="width:130px">Host</td>
						<td><input type="text" name="ftp-host" id="ftp-host" value="<?php //echo $GLOBALS['duplicator_opts']['ftp-host'] ?>"  style="width:300px" /></td>
						<td>Port</td>
						<td><input type="text" name="ftp-port" id="ftp-port" value="<?php //echo $GLOBALS['duplicator_opts']['ftp-port'] ?>" style="width:60px" /></td>
					</tr>
					<tr>
						<td style="white-space:nowrap">User Name</td>
						<td colspan="3"><input type="text" name="ftp-user" id="ftp-user" value="<?php //echo $GLOBALS['duplicator_opts']['ftp-user'] ?>"  class="txt-settings"/></td>
					</tr>
					<tr>
						<td style="white-space:nowrap">Password</td>
						<td colspan="3"><input type="password" name="ftp-pass" id="ftp-pass" value="<?php //echo $GLOBALS['duplicator_opts']['ftp-pass'] ?>"  class="txt-settings"/></td>
					</tr>
					</table>
			</div>-->
			
		</div>
		
		<input type="button" id="opts-save-btn" class="btn-save-opts" value="<?php _e("Save", 'WPDuplicator') ?>" style="position:absolute;bottom:20px; right:115px" onclick="Duplicator.saveSettings()" />
		<input type="button" id="opts-close-btn" class="btn-save-opts" value="<?php _e("Close", 'WPDuplicator') ?>" style="position:absolute;bottom:20px; right:30px" onclick="Duplicator.optionsClose()" />
	</form>
</div>