<!-- ==========================================
#OPTIONS DIALOG -->
<div id="div-render-blanket" style="display:none;">
<div id="dialog-options" title="Duplicator - Options">
	<form id="form-duplicator-opts" method="post">
		<input type="hidden" name="action" value="settings" />
		<div id="tabs-opts">
			<ul>
				<li><a href="#tabs-opts-1">Package</a></li>
				<li><a href="#tabs-opts-2">Installer</a></li>
				<!--lli><a href="#tabs-opts-3">FTP</a></li>
				<i><a href="#tabs-opts-4">Schdeuler</a></li-->
				<li><a href="#tabs-opts-3">System</a></li>
			</ul>
			
			
			<!-- =============================================================================
			TAB 1 PACKAGE -->
			<div id="tabs-opts-1">
				<div style="text-align:left;">
				<fieldset style="padding:10px; width:97%">
					<legend>Processing</legend>
					<input type="checkbox" name="email-me" id="email-me" <?php echo ($email_me_enabled) ? 'checked="checked"' : ''; ?> /> <label for="email-me">Email when complete</label>&nbsp; 
					<span style=" font-style:italic; font-size:11px">(usefull on large sites)</span><br/>
					<br/>
					
					<input type="checkbox" name="dbiconv" id="dbiconv" <?php echo ($duplicator_dbiconv) ? 'checked="checked"' : ''; ?> /> <label for="dbiconv">Enable database encoding</label>&nbsp; 
					<span style=" font-style:italic; font-size:11px">(recommended)</span><br/>
					<br/>
					

					
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
							<td style="width:50%">Max Time: <input type="text" name="max_time" id="max_time" value="<?php echo $max_time_val ?>" <?php echo $max_read_only ?> maxlength="4" style="width:75px" /> seconds</td>
							<td style="width:50%">Max Memory: <input type="text" name="max_memory" id="max_memory" value="<?php echo preg_replace('/\D/', '', $GLOBALS['duplicator_opts']['max_memory'] ) ?>" maxlength="4" style="width:45px" /> MB <i style='font-size:11px'>(minimum 128)</i></td>
						</tr>
					</table><br/>
					
					
					<label for="dir_bypass" style="font-size:13px">Directory Exclusion: <i style="font-size:11px"></i></label> <br/>
					<textarea name="dir_bypass" id="dir_bypass" style="width:625px;height:50px;background-color:#efefef;font-size:12px" /><?php echo $GLOBALS['duplicator_opts']['dir_bypass'] ?></textarea>
					
					<div style="font-style:italic; font-size:11px">
						Apply path seperated by a semicolon i.e. /path1/dir1;/path2/dir2.  Path will exclude all sub-directories<br/>
						Your root directory is: <?php echo rtrim(duplicator_safe_path(WP_CONTENT_DIR), 'wp-content') ?>
					</div><br/>	
				</fieldset><br/>
					
	
				<fieldset style="padding:10px; width:97%">
					<legend>Logging</legend>
					<table width="100%" border="0">
						<tr valign="top">
							<td>
								<label for="log_level">Reporting Level:</label> <br/>
								<select name="log_level" id="log_level">
									<option value="0">Level 0 - none</option>
									<option value="1">Level 1 - light</option>
									<option value="2">Level 2 - detailed</option>
									<option value="3">Level 3 - debug</option>
								</select>
							</td>
							<td style="width:95%">
							<div style="padding:5px 3px 3px 25px; font-style:italic; font-size:12px">
								Information gathered in the logging pane can be used to contact support with issues.  When the logging pane is enabled you will have to manually refresh the window. Higher levels are more verbose.
							</div>
							</td>
						</tr>
					</table>
				</fieldset>
				<i style='font-size:10px'>Having issues saving these options?  Temporarily disable all "Object Caches" (i.e. W3C Total Object Cache).</i>
				</div>
			</div>
			
			
			<!-- =============================================================================
			TAB 2 INSTALLER -->
			<div id="tabs-opts-2">
				<fieldset style="height:55px">
					<legend>Settings Defaults</legend>
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
						<tr>
							<td style="width:130px">Install URL</td>
							<td><input type="text" name="nurl" id="nurl" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['nurl'] ?>" /></td>
							<td align="center"><input type="button" class="btn-setup-link" onclick="window.open('<?php echo $GLOBALS['duplicator_opts']['nurl'] ?>\install.php', '_blank')" title="Launch the installer window." <?php if(! $setup_link_enabled) { echo "style='display:none'"; }?> /></td>
						</tr>
					</table>
				</fieldset><br/>
			
				<fieldset style="height:190px">
					<legend>Database Defaults</legend>
					<table width="100%" border="0" cellspacing="5" cellpadding="5">
					<tr>
						<td style="width:130px">Host</td>
						<td><input type="text" name="dbhost" id="dbhost" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbhost'] ?>"  maxlength="2100"/></td>
					</tr>
					<tr>
						<td>Name</td>
						<td><input type="text" name="dbname" id="dbname" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbname'] ?>" maxlength="100" /></td>
					</tr>
					<tr>
						<td>User</td>
						<td><input type="text" name="dbuser" id="dbuser" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbuser'] ?>"  maxlength="100" /></td>
					</tr>
					<tr>
						<td>Password</td>
						<td><input type="text" name="" id="" class="txt-settings" value="disabled for security" disabled="true" style="background-color:#efefef"  maxlength="100" /></td>
					</tr>
					</table>
				</fieldset>
				<i style="font-size:11px">The installer can have these fields pre-filled with these optional settings.  These values are only applied to packages after the data is saved and a package is created.</i>
			</div>
			<!--div id="tabs-opts-3">
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
			</div>
			<div id="tabs-opts-4">Scheduler in Version 1.2</div-->
			
			
			<!-- =============================================================================
			TAB 3 SYSTEM -->
			<div id="tabs-opts-3">		
				Key:
				<span class="dup-test-good" style="padding:10px">Good <sup>[1]</sup></span>
				<span class="dup-test-ok" style="padding:10px">OK <sup>[0]</sup></span>
				<span class="dup-test-bad" style="padding:10px">Bad <sup>[-1]</sup></span><br/><hr size="1" />
				
				<div style="line-height:24px">
				<b>SERVER CHECKS</b> <br/>
				PHP Version: 
				<?php if (phpversion() >= 5.3): ?>
					<span class="dup-test-good"><?php echo phpversion(); ?> <sup>[1]</sup></span>
				<?php elseif (phpversion() >= 5.2): ?>
					<span class="dup-test-ok"><?php echo phpversion(); ?> <sup>[0]</sup></span> <i style="font-size:11px">(5.3+ is highly recommended. 5.2.17+ is required)</i>
				<?php else: ?> 
					<span class="dup-test-bad"><?php echo phpversion(); ?> <sup>[-1]</sup></span>
				<?php endif; ?>
				<br />
				
				 Web Server: 
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
					<span>Not detected <sup>[-1]</sup></span>
				<?php endif; ?>	<br />
				
				Safe Mode: 
				<?php if (stristr($safe_value, 'on')): ?>
					<span class="dup-test-bad">On <sup>[-1]</sup></span>
				<?php else: ?>
					<span class="dup-test-good">Off <sup>[1]</sup></span>
				<?php endif; ?>	<br />
				
				
				Open Base Dir: 
				<?php 
				$open_basedir_set = ini_get("open_basedir");
				if (empty($open_basedir_set)): ?>
					<span class="dup-test-good">Not Enabled <sup>[1]</sup></span>
				<?php else: ?>
					<span class="dup-test-ok">Enabled <sup>[0]</sup></span> <i style="font-size:11px">(contact your host to temporarily disable)</i>
				<?php endif; ?>	<br />
				
			    Compression:
				<?php if (class_exists('ZipArchive')): ?>
					<span class="dup-test-good">Pass <sup>[1]</sup></span>
				<?php else: ?>
					<span class="dup-test-bad">Not installed <sup>[-1]</sup></span>
				<?php endif; ?>
				<i style="font-size:11px">(ZipArchive extension required for compression)</i>
				<br /><br />

				<b>CACHE STORE CHECKS</b> <br/>
				W3 Total Cache:
				<?php 
					$w3tc_path = DUPLICATOR_WPROOTPATH . 'wp-content/w3tc';
					if (file_exists($w3tc_path) && ! strstr($GLOBALS['duplicator_opts']['dir_bypass'], $w3tc_path)):
				?>
					<span class="dup-test-bad">Cache Directory Found.</span> 
					<div style="padding-left:10px; font-style:italic; font-size:11px">
						- Full Path: '<?php echo $w3tc_path;  ?>'<br/>
						- <a href="javascript:void(0)" onclick="Duplicator.optionsAppendByPassList('<?php echo addslashes($w3tc_path); ?>')">Add to Directory exclusion list for me.</a>
					</div>
				<?php elseif (strstr($GLOBALS['duplicator_opts']['dir_bypass'], $w3tc_path)): ?>
					<span class="dup-test-good">Cache Directory is being excluded</span>
					<br/>Full Path: <?php echo $w3tc_path;  ?>
					<br/><i style="font-size:11px">See Package Tab -&gt; Directory Exclusion</i>
				<?php else: ?>
					<span class="dup-test-good">Cache Directory Not Found</span>
				<?php endif; ?>
				<br /><br /><hr size="1"/>
				It is highly recommended to exclude all cache store directories. <br/> This will help for a faster and cleaner install.
				
			
				<!--FTP functions:
				<?php if (function_exists('ftp_connect')): ?>
				<span class="dup-test-good">OK</span>
				<?php else: ?>
				<span>Not installed</span>
				<?php endif; ?>
				<br />-->
				
				</div>
			</div>
		</div>
		
		<input type="button" id="opts-save-btn" class="btn-save-opts" value="Save" style="position:absolute;bottom:20px; right:115px" onclick="Duplicator.saveSettings()" />
		<input type="button" id="opts-close-btn" class="btn-save-opts" value="Close" style="position:absolute;bottom:20px; right:30px" onclick="Duplicator.optionsClose()" />
	</form>
</div>