<!-- ==========================================
#OPTIONS DIALOG -->
<div id="div-render-blanket" style="display:none;">
	<div id="dialog-options" title="<?php _e(" duplicator options", 'wpduplicator') ?>
		 ">
		<form id="form-duplicator-opts" method="post">
			<input type="hidden" name="action" value="settings"/>
			<div id="dup-tabs-opts">
				<ul>
					<li><a href="#dup-tabs-opts-1"><?php _e("Package", 'wpduplicator') ?>
					</a></li>
					<li><a href="#dup-tabs-opts-2"><?php _e("Installer", 'wpduplicator') ?>
					</a></li>
					<li><a href="#dup-tabs-opts-3"><?php _e("System", 'wpduplicator') ?>
					</a></li>
					<!--li><a href="#dup-tabs-opts-4"><?php _e("FTP", 'wpduplicator') ?></a></li>-->
				</ul>
				<!-- =============================================================================
			TAB 1 PACKAGE -->
				<div id="dup-tabs-opts-1">
					<!-- PROCESSING
					<fieldset style="width:97%;"> -->
					<!--	<legend><b><?php _e("Processing", 'wpduplicator') ?></b></legend> -->
					<?php
							$safe_value = ini_get('safe_mode');
							if( stristr($safe_value, 'on') ){
								$max_read_only = "readonly='true'";
							} else {
								$max_read_only = "";
							}
						?>
					<!-- Email and more options -->
					<div data-role="content">
						<div data-role="fieldcontain">
							<fieldset data-role="controlgroup">
								<legend><b><?php _e("Processing", 'wpduplicator') ?></b></legend>


								<!--  Not sure why this was set for the select title, but the list might be helpfull still
								Title="Excluded Videos: 3G2;3GP;ASF;ASX;AVI;BIK;DIV;DIVX;DVD;IVF;FLV;M1V;MOV;MP2V;MP4;MPA;MPE;MPEG;MPG;QT;QTL;RAD;RM;SRT;SWF;RPM;SMK;VOB;WM;WMV;WOB. Excluded Audio: AAC;AIF;IFF;M3U;M4A;MID;MP3;MPA;RA;WAV;WMA " -->
								
								<!--label for="package_mode">Package Profile:</label> 
								<select name="package_mode" id="package_mode" class="dup-select">
									<option value="1" <?php echo ($package_mode=="1") ? 'selected="selected"': '' ; ?> > Full - Files and Database</option>
									<option value="0" <?php echo ($package_mode=="0") ? 'selected="selected"': '' ; ?> > Quick - Database Only</option>
								</select><br/-->
								
								
								
								<div style="display:inline-block; line-height:16px; margin-top:4px">
								<input type="checkbox" name="email-me" id="email-me" <?php echo ($email_me_enabled) ? 'checked="checked"' : ''; ?> /> 
								<label for="email-me">
									<?php 
										printf("%s: <i style='font-size:11px'>%s</i>",
											__("Email when completed", 'wpduplicator'),
											__("WP-Admin email is included.  Add extra emails semicolon separated.", 'wpduplicator'));
									?>
								</label><br/>
								<input type="text" placeholder="mail1@gmail.com;mail2@gmail.com;support@hotmail.com" name="email_others" id="email_others"  value="<?php echo $GLOBALS['duplicator_opts']['email_others'] ?>" style="width:95%" /> <br/>
								</div>
								
					
							</fieldset>
						</div>
					</div><br/>
					
					<!-- FILTERS -->
					<div data-role="content">
						<div data-role="fieldcontain">
							<fieldset data-role="controlgroup">
								<legend><b>Exclusion filters:</b></legend>
								<label for="dir_bypass">Directories: </label>
								<br>
								<textarea style="width:95%" name="dir_bypass" id="dir_bypass" placeholder="/path1;/path2;/video;/images;/wp-content/themes/mytheme/upload"/><?php echo $GLOBALS['duplicator_opts']['dir_bypass'] ?></textarea>
								<div style='font-size:11px; margin:-6px 0px 5px 0px'>
									<i><?php printf("%s: <a href='javascript:void(0)' onclick='Duplicator.optionsAddRootPath(this)'>
									 %s</a>",__("Root Path", 'wpduplicator'), rtrim(DUPLICATOR_WPROOTPATH, '/')); ?></i>
								</div>
								<label for="skip_ext">File extensions: </label>
								<input style="width:95%" name="skip_ext" id="skip_ext" placeholder="exe;txt;avi;jpg" value="<?php echo $GLOBALS['duplicator_opts']['skip_ext'] ?>" type="text">
							</fieldset>
						</div>
					</div>
					<!--div style='position:absolute; bottom:5px'>
						<i style='font-size:10px'><?php _e("Having issues saving these options?  Temporarily disable all 'Object Caches' (i.e. W3C Total Object Cache)", 'wpduplicator') ?>.</i>
					</div-->
				</div>
				<!-- =============================================================================
			TAB 2 INSTALLER -->
				<div id="dup-tabs-opts-2">
					<!-- RESTORE INSTALL SETTINGS -->
					<div data-role="content">
						<div data-role="fieldcontain">
							<fieldset data-role="controlgroup">
								<legend>
								<b>Restore Settings defaults</b>
								</legend>
								<fieldset data-role="controlgroup">
									<legend>
									<b>WP Install Dir</b>
									</legend>
									<label for="url_new">
									Install Url </label>
									<input style="width:95%" name="url_new" id="url_new" placeholder="www.mysite/blog" value="<?php echo $globals['duplicator_opts']['url_new'] ?>" type="text">
								</fieldset>
								<div data-role="fieldcontain">
									<fieldset data-role="controlgroup">
										<legend>
										<b>Mysql Server</b>
										</legend>
										<label for="dbhost">
										Mysql Host (May change depending on Host Provider. e.g.: mysql2038.hostprovider.net)</label>
										<input title="Hostname. Server upon which MySQL resides or network address. e.g.: mysqli('localhost', 'my_user', 'my_password', 'my_db');" style="width:95%" name="dbhost" id="dbhost" placeholder="localhost" value="<?php echo $globals['duplicator_opts']['dbhost'] ?>" maxlength="2100" type="text"> <label for="dbuser">
										Database User </label>
										<input title="Mysql User Password will be set at restore time" style="width:95%" name="dbuser" id="dbuser" placeholder="Mysql_user" value="<?php echo $globals['duplicator_opts']['dbuser'] ?>" type="text"> <label for="dbname">
										Database Name </label>
										<input title="Database: Single DB container for all Wp tables. e.g.: mysql_select_db('Database_Name',$connection)" style="width:95%" name="dbname" id="dbname" placeholder="wp_db_name" value="<?php echo $globals['duplicator_opts']['dbname'] ?>" type="text">
									</fieldset>
								</div>
							</fieldset>
						</div>
						<i style="font-size:11px"><?php _e("The installer can have these fields pre-filled at install time.  These values are optional. ", 'wpduplicator') ?>
						</i>
					</div>
				</div>
				<!-- =============================================================================
			TAB 3 SYSTEM -->
				<div id="dup-tabs-opts-3">
					<fieldset style="height:100px">
						<legend><b><?php _e("Uninstall Options", 'wpduplicator') ?>
						</b></legend>
						<input type="checkbox" name="rm_snapshot" id="rm_snapshot" <?php echo ($rm_snapshot) ? 'checked="checked" ' : ''; ?> /> <label for="rm_snapshot"><?php _e("Delete entire snapshot directory when removing plugin", 'wpduplicator') ?>
						</label><br/>
						<i style='font-size:11px'><?php _e("Snapshot Directory", 'wpduplicator'); ?>
						 : <?php echo duplicator_safe_path(DUPLICATOR_SSDIR_PATH); ?>
						</i><br/>
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
			<input type="button" id="opts-save-btn" class="btn-save-opts" value="<?php _e("Save", 'wpduplicator') ?>" style="position:absolute;bottom:20px; right:115px" onclick="Duplicator.saveSettings()" /> <input type="button" id="opts-close-btn" class="btn-save-opts" value="<?php _e("Close", 'wpduplicator') ?>" style="position:absolute;bottom:20px; right:30px" onclick="Duplicator.optionsClose()" />
		</form>
	</div>