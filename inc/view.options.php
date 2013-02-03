<!-- ==========================================
#OPTIONS DIALOG -->
<div id="div-render-blanket" style="display:none;">
<div id="dialog-options" title="<?php _e("Duplicator Options", 'wpduplicator') ?>">
	<form id="form-duplicator-opts" method="post">
		<input type="hidden" name="action" value="settings" />
		<div id="dup-tabs-opts">
			<ul>
				<li><a href="#dup-tabs-opts-1"><?php _e("Package", 'wpduplicator') ?></a></li>
				<li><a href="#dup-tabs-opts-2"><?php _e("Installer", 'wpduplicator') ?></a></li>
				<li><a href="#dup-tabs-opts-3"><?php _e("System", 'wpduplicator') ?></a></li>
				<!--li><a href="#dup-tabs-opts-4"><?php _e("FTP", 'wpduplicator') ?></a></li>-->
			</ul>

			<!-- =============================================================================
			TAB 1 PACKAGE -->
			<div id="dup-tabs-opts-1">
				<div style="text-align:left;">

					<!-- PROCESSING -->
					<fieldset>
						<legend><b><?php _e("Processing", 'wpduplicator') ?></b></legend>
						<input type="checkbox" name="email-me" id="email-me" <?php echo ($email_me_enabled) ? 'checked="checked"' : ''; ?>  />
						<label for="email-me"><?php _e("Email when completed", 'wpduplicator');?></label><br/>
						<input type="text" name="email_others" id="email_others"  value="<?php echo $GLOBALS['duplicator_opts']['email_others'] ?>" placeholder="mail1@mysite.com;mail2@mysite.com;" title="<?php _e("WP-admin email is included.  Add extra emails semicolon separated.", 'wpduplicator') ?>"  /> <br/>
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
							<a href="javascript:void(0)" onclick="Duplicator.optionsAddExcludePath('<?php echo rtrim(DUPLICATOR_WPROOTPATH, '/'); ?>')">[<?php _e("root path", 'wpduplicator') ?>]</a>
							<a href="javascript:void(0)" onclick="Duplicator.optionsAddExcludePath('<?php echo rtrim($upload_dir , '/'); ?>')">[<?php _e("wp-uploads", 'wpduplicator') ?>]</a>
							<a href="javascript:void(0)" onclick="jQuery('#dir_bypass').val('')"><?php _e("(clear)", 'wpduplicator') ?></a>
						</div>
						<textarea name="dir_bypass" id="dir_bypass" style="height:120px;" placeholder="/root/path1;/root/path2" /><?php echo $GLOBALS['duplicator_opts']['dir_bypass'] ?></textarea><br/>


						<label class="no-select" title="<?php _e("Separate all filters by semicolon", 'wpduplicator'); ?>"><?php _e("File extensions", 'wpduplicator') ?>:</label>
						<div class='dup-quick-links'>
							<a href="javascript:void(0)" onclick="Duplicator.optionsAddExcludeExts('avi;mov;mp4;mpeg;mpg;swf;wmv;aac;m3u;mp3;mpa;wav;wma')">[<?php _e("media", 'wpduplicator') ?>]</a>
							<a href="javascript:void(0)" onclick="Duplicator.optionsAddExcludeExts('zip;rar;tar;gz;bz2;7z')">[<?php _e("archive", 'wpduplicator') ?>]</a>
							<a href="javascript:void(0)" onclick="jQuery('#skip_ext').val('')"><?php _e("(clear)", 'wpduplicator') ?></a>
						</div>
						<textarea type="text" name="skip_ext" id="skip_ext" style="height:60px;" placeholder="ext1;ext2;ext3"><?php echo $GLOBALS['duplicator_opts']['skip_ext'] ?></textarea>

						<p class="help"><?php _e("All directory paths and extensions in this section will be excluded from the package file.", 'wpduplicator'); ?></p>
					</fieldset><br/>

					<!--div style='position:absolute; bottom:5px'>
						<i style='font-size:10px'><?php _e("Having issues saving these options?  Temporarily disable all 'Object Caches' (i.e. W3C Total Object Cache)", 'wpduplicator') ?>.</i>
					</div-->
				</div>
			</div>


			<!-- =============================================================================
			TAB 2 INSTALLER -->
			<div id="dup-tabs-opts-2">
				<fieldset style="height:50px">
					<legend><b><?php _e("Settings Defaults", 'wpduplicator') ?></b></legend>
					<table width="100%">
						<tr>
							<td style="width:130px"><?php _e("Install URL", 'wpduplicator') ?></td>
							<td><input type="text" name="url_new" id="url_new" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['url_new'] ?>" placeholder="http://mynewsite.com" /></td>
						</tr>
					</table>
				</fieldset><br/>

				<fieldset style="height:110px">
					<legend><b><?php _e("Database Defaults", 'wpduplicator') ?></b></legend>
					<table width="100%">
					<tr>
						<td style="width:130px"><?php _e("Host", 'wpduplicator') ?></td>
						<td><input type="text" name="dbhost" id="dbhost" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbhost'] ?>"  maxlength="200" placeholder="localhost"/></td>
					</tr>
					<tr>
						<td><?php _e("Name", 'wpduplicator') ?></td>
						<td><input type="text" name="dbname" id="dbname" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbname'] ?>" maxlength="100" placeholder="mydatabsename" /></td>
					</tr>
					<tr>
						<td><?php _e("User", 'wpduplicator') ?></td>
						<td><input type="text" name="dbuser" id="dbuser" class="txt-settings" value="<?php echo $GLOBALS['duplicator_opts']['dbuser'] ?>"  maxlength="100" placeholder="databaseusername" /></td>
					</tr>
					</table>
				</fieldset><br/>
				<p class="help"><?php _e("The installer can have these fields pre-filled at install time.  These values are optional.", 'wpduplicator') ?></p>
			</div>

			<!-- =============================================================================
			TAB 3 SYSTEM -->
			<div id="dup-tabs-opts-3">
				<fieldset style="height:100px">
					<legend><b><?php _e("Uninstall Options", 'wpduplicator') ?></b></legend>

						<input type="checkbox" name="rm_snapshot" id="rm_snapshot" <?php echo ($rm_snapshot) ? 'checked="checked"' : ''; ?> />
						<label for="rm_snapshot"><?php _e("Delete entire snapshot directory when removing plugin", 'wpduplicator') ?></label><br/>
						<p class="help"><?php _e("Snapshot Directory", 'wpduplicator'); ?>: <?php echo duplicator_safe_path(DUPLICATOR_SSDIR_PATH); ?></p><br/>

				</fieldset>
			</div>


			<!--div id="dup-tabs-opts-4">
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

		<input type="button" id="opts-save-btn" class="btn-save-opts" value="<?php _e("Save", 'wpduplicator') ?>" style="position:absolute;bottom:20px; right:115px" onclick="Duplicator.saveSettings()" />
		<input type="button" id="opts-close-btn" class="btn-save-opts" value="<?php _e("Close", 'wpduplicator') ?>" style="position:absolute;bottom:20px; right:30px" onclick="Duplicator.optionsClose()" />
	</form>
</div>