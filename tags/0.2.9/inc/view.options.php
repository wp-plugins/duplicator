<!-- ==========================================
#OPTIONS DIALOG -->
<div id="div-render-blanket" style="display:none;">
<div id="dialog-options" title="<?php _e("Duplicator Options", 'WPDuplicator') ?>">
	<form id="form-duplicator-opts" method="post">
		<input type="hidden" name="action" value="settings" />
		<div id="tabs-opts">
			<ul>
				<li><a href="#tabs-opts-1"><?php _e("Package", 'WPDuplicator') ?></a></li>
				<li><a href="#tabs-opts-2"><?php _e("Installer", 'WPDuplicator') ?></a></li>
				<li><a href="#tabs-opts-3"><?php _e("System", 'WPDuplicator') ?></a></li>
				<!--li><a href="#tabs-opts-4"><?php _e("FTP", 'WPDuplicator') ?></a></li>-->
			</ul>
			
			
			<!-- =============================================================================
			TAB 1 PACKAGE -->
			<div id="tabs-opts-1">
				<div style="text-align:left;">
				<fieldset style="padding:10px; width:97%">
					<legend><?php _e("Processing", 'WPDuplicator') ?></legend>
					
					<input type="checkbox" name="email-me" id="email-me" <?php echo ($email_me_enabled) ? 'checked="checked"' : ''; ?> /> 
					<label for="email-me"><?php _e("Email when complete", 'WPDuplicator') ?></label><br/><br/>
					
					<input type="checkbox" name="dbiconv" id="dbiconv" <?php echo ($duplicator_dbiconv) ? 'checked="checked"' : ''; ?> /> 
					<label for="dbiconv"><?php _e("Enable database encoding", 'WPDuplicator') ?></label><br/><br/>
					
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
					</table><br/>
					
					
					<label for="dir_bypass" style="font-size:13px"><?php _e("Directory Exclusion", 'WPDuplicator') ?>: <i style="font-size:11px"></i></label> <br/>
					<textarea name="dir_bypass" id="dir_bypass" style="width:625px;height:50px;background-color:#efefef;font-size:12px" /><?php echo $GLOBALS['duplicator_opts']['dir_bypass'] ?></textarea>
					
					<div style="font-style:italic; font-size:11px">
						<?php 
							printf("%s (i.e. /path1/dir1;/path2/dir2) %s.<br/>",
								__("Apply path separated by a semicolon", 'WPDuplicator'),
								__("paths will exclude all sub-directories",   'WPDuplicator'));
								
							printf("%s: %s",
								__("Your root path is", 'WPDuplicator'),
								rtrim(duplicator_safe_path(WP_CONTENT_DIR), 'wp-content'));	
						?>
					</div><br/>	
				</fieldset><br/>
					
	
				<fieldset style="padding:10px; width:97%">
					<legend><?php _e("Logging", 'WPDuplicator') ?></legend>
					<table width="100%" border="0">
						<tr valign="top">
							<td>
								<label for="log_level"><?php _e("Reporting Level", 'WPDuplicator') ?>:</label><br/>
								<select name="log_level" id="log_level">
									<option value="0"><?php _e("Level 0 - None", 'WPDuplicator') ?></option>
									<option value="1"><?php _e("Level 1 - Light", 'WPDuplicator') ?></option>
									<option value="2"><?php _e("Level 2 - Detailed", 'WPDuplicator') ?></option>
									<option value="3"><?php _e("Level 3 - Debug", 'WPDuplicator') ?></option>
								</select>
							</td>
							<td style="width:95%">
							<div style="padding:5px 3px 3px 25px; font-style:italic; font-size:12px">
								<?php _e("Information in the logging panel can be used to contact support.  When the logging pane is enabled you will have to manually refresh the window.", 'WPDuplicator') ?>
								
							</div>
							</td>
						</tr>
					</table>
				</fieldset>
				<i style='font-size:10px'><?php _e("Having issues saving these options?  Temporarily disable all 'Object Caches' (i.e. W3C Total Object Cache)", 'WPDuplicator') ?>.</i>
				</div>
			</div>
			
			
			<!-- =============================================================================
			TAB 2 INSTALLER -->
			<div id="tabs-opts-2">
				<fieldset style="height:55px">
					<legend><?php _e("Settings Defaults", 'WPDuplicator') ?></legend>
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
						<tr>
							<td style="width:130px"><?php _e("Install URL", 'WPDuplicator') ?></td>
							<td><input type="text" name="nurl" id="nurl" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['nurl'] ?>" /></td>
							<td align="center"><input type="button" class="btn-setup-link" onclick="window.open('<?php echo $GLOBALS['duplicator_opts']['nurl'] ?>\install.php', '_blank')" title="<?php _e("Launch installer window", 'WPDuplicator') ?>." <?php if(! $setup_link_enabled) { echo "style='display:none'"; }?> /></td>
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

			
			<!-- =============================================================================
			TAB 3 SYSTEM -->
			<div id="tabs-opts-3">		
				<?php _e("Key", 'WPDuplicator') ?>:
				<span class="dup-test-good" style="padding:10px"><?php _e("Good", 'WPDuplicator') ?> <sup>[1]</sup></span>
				<span class="dup-test-ok" style="padding:10px"><?php _e("OK", 'WPDuplicator') ?> <sup>[0]</sup></span>
				<span class="dup-test-bad" style="padding:10px"><?php _e("Bad", 'WPDuplicator') ?> <sup>[-1]</sup></span><br/><hr size="1" />
				
				<div style="line-height:24px">
					<b><?php _e("SERVER CHECKS", 'WPDuplicator') ?></b><br/>
					<?php _e("PHP Version", 'WPDuplicator') ?>: 
					<?php if (phpversion() >= 5.3): ?>
						<span class="dup-test-good"><?php echo phpversion(); ?> <sup>[1]</sup></span>
					<?php elseif (phpversion() >= 5.2): ?>
						<span class="dup-test-ok"><?php echo phpversion(); ?> <sup>[0]</sup></span> 
						<i style="font-size:11px">(<?php _e("PHP 5.2.17+ is required", 'WPDuplicator') ?>)</i>
					<?php else: ?> 
						<span class="dup-test-bad"><?php echo phpversion(); ?> <sup>[-1]</sup></span>
					<?php endif; ?>	<br />
					
					
					<?php _e("Web Server", 'WPDuplicator') ?>: 
					<?php if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') !== false): ?>
						<span class="dup-test-good">Apache <sup>[1]</sup></span>
					<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false): ?> 
						<span class="dup-test-ok">Lite Speed <sup>[0]</sup></span>
					<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false): ?> 
						<span class="dup-test-ok">nginx <sup>[0]</sup></span>
					<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false): ?> 
						<span class="dup-test-ok">lighttpd <sup>[0]</sup></span>
					<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') !== false): ?> 
						<span class="dup-test-ok">Microsoft IIS <sup>[0]</sup></span>
					<?php else: ?>
						<span><?php _e("Not detected", 'WPDuplicator') ?> <sup>[-1]</sup></span>
					<?php endif; ?>	<br />
					
					
					<?php _e("Safe Mode", 'WPDuplicator') ?>: 
					<?php if (stristr($safe_value, 'on')): ?>
						<span class="dup-test-bad"><?php _e("On", 'WPDuplicator') ?> <sup>[-1]</sup></span>
					<?php else: ?>
						<span class="dup-test-good"><?php _e("Off", 'WPDuplicator') ?> <sup>[1]</sup></span>
					<?php endif; ?>	<br />
					
					
					<?php _e("Open Base Dir", 'WPDuplicator') ?>: 
					<?php 
					$open_basedir_set = ini_get("open_basedir");
					if (empty($open_basedir_set)): ?>
						<span class="dup-test-good"><?php _e("Not Enabled", 'WPDuplicator') ?> <sup>[1]</sup></span>
					<?php else: ?>
						<span class="dup-test-ok"><?php _e("Enabled", 'WPDuplicator') ?> <sup>[0]</sup></span> <i style="font-size:11px">(<?php _e("contact your host to temporarily disable", 'WPDuplicator') ?>)</i>
					<?php endif; ?>	<br />
					
					
					<?php _e("Compression", 'WPDuplicator') ?>:
					<?php if (class_exists('ZipArchive')): ?>
						<span class="dup-test-good"><?php _e("Pass", 'WPDuplicator') ?> <sup>[1]</sup></span>
					<?php else: ?>
						<span class="dup-test-bad"><?php _e("Not installed", 'WPDuplicator') ?> <sup>[-1]</sup></span>
					<?php endif; ?>
					<i style="font-size:11px">(<?php _e("ZipArchive extension required for compression", 'WPDuplicator') ?>)</i>
					<br /><br />
					

					<b><?php _e("CACHE STORE CHECKS", 'WPDuplicator') ?></b> <br/>
					W3 Total Cache:
					<?php 
						$w3tc_path = DUPLICATOR_WPROOTPATH . 'wp-content/w3tc';
						if (file_exists($w3tc_path) && ! strstr($GLOBALS['duplicator_opts']['dir_bypass'], $w3tc_path)):
					?>
						<span class="dup-test-bad"><?php _e("Cache Directory Found", 'WPDuplicator') ?>.</span> 
						<div style="padding-left:10px; font-style:italic; font-size:11px">
							- <?php _e("Full Path:", 'WPDuplicator') ?> '<?php echo $w3tc_path; ?>'<br/>
							- <a href="javascript:void(0)" onclick="Duplicator.optionsAppendByPassList('<?php echo addslashes($w3tc_path); ?>')"><?php _e("Add to directory exclusion list for me.", 'WPDuplicator') ?></a>
						</div>
					<?php elseif (strstr($GLOBALS['duplicator_opts']['dir_bypass'], $w3tc_path)): ?>
						<span class="dup-test-good"><?php _e("Cache Directory is being excluded", 'WPDuplicator') ?></span>
						<br/><?php _e("Full Path:", 'WPDuplicator') ?> <?php echo $w3tc_path;  ?>
						<br/><i style="font-size:11px"><?php _e("See Package Tab -&gt; Directory Exclusion", 'WPDuplicator') ?></i>
					<?php else: ?>
						<span class="dup-test-good"><?php _e("Cache Directory Not Found", 'WPDuplicator') ?></span>
					<?php endif; ?>
					<br /><br /><hr size="1"/>
					<?php _e("Please exclude all cache store directories. This will help for a faster and cleaner install.", 'WPDuplicator') ?>
					

					<!--FTP functions:
					<?php if (function_exists('ftp_connect')): ?>
					<span class="dup-test-good">OK</span>
					<?php else: ?>
					<span>Not installed</span>
					<?php endif; ?>
					<br />-->
				
				</div>
			</div>
			
			<!--div id="tabs-opts-4">
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