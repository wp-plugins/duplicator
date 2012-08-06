<!-- ==========================================
DIALOG: PACKAGE CONFIRMATION-->
<div id="dup-dlg-package-confirm" title="<?php _e('Package Creation', 'WPDuplicator'); ?>">
	<span class="ui-icon ui-icon-disk" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
	<b><?php _e("Create a new Package Set?", 'WPDuplicator');	?></b>
	
	<p style="padding:10px 20px 10px 20px; line-height:26px; text-align:left; border:1px solid #efefef; border-radius:5px">
		<b><?php _e('Name', 'WPDuplicator') ?>:</b> <span id="dup-dlg-package-confirm-msg"></span><br/>

		<b><?php _e('Pre-Zip Overview', 'WPDuplicator'); ?>:</b>
		<span id='dup-dlg-package-confirm-scannow-data'>
			<a href="javascript:void(0)" onclick="Duplicator.getSystemDirectory()"><?php _e("Perform Scan", 'WPDuplicator') ?></a> 
		</span><br/>
		
		<div style='font-size:11px; line-height:15px'>
			<i>
				<?php printf("%s <a href='javascript:void(0)'  onclick='Duplicator.optionsOpen()'>%s</a>. %s",
						__('Note: A scan will provide an estimate on the size of your file system.  The scan will exclude items in the', 'WPDuplicator'),
						__('directory filter list'),
						__('Files that are not readable by the plugin will not be included in the overview.  Directories that are empty will not be included in the final package. ', 'WPDuplicator'));
				?>
			</i>
		</div>
	</p>
</div>

<!-- ==========================================
DIALOG: SYSTEM ERROR -->
<div id="dup-dlg-system-error" title="<?php _e('System Constraint', 'WPDuplicator'); ?>">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
		<?php _e("Please try again! An issue has occurred.", 'WPDuplicator');	?>
	</p>
	
	<div style="padding: 0px 20px 20px 20px;">
		<b><?php _e("Recommendations", 'WPDuplicator') ?></b><br/>
		<div id="dup-system-err-msg1">
			<li>
				<?php printf("<a href='javascript:void(0)' onclick='Duplicator.getSystemCheck()'>%s</a> %s &amp; 
							  <a href='javascript:void(0)' onclick='window.location.reload();'>%s</a> %s",
						__("Validate", 'WPDuplicator'),
					    __("your system", 'WPDuplicator'),
						__("refresh", 'WPDuplicator'),
						__("the dashboard.", 'WPDuplicator')); ?>
			</li>
			<li>
				<?php printf("<a href='javascript:void(0)' onclick='Duplicator.openLog()'>%s</a> %s.",
						__("Monitor", 'WPDuplicator'),
						__("your log file a few more minutes as processing may continue on some systems", 'WPDuplicator')); 
				?>
			</li>
			<li><?php _e('Contact your server admin to have the page timeout increased (see duration below)', 'WPDuplicator') ?>.</li>
			<li><?php _e('Consider adding a directory filter in the options dialog if the process continues to timeout', 'WPDuplicator') ?>.</li>
			<li><?php _e('Check your disk space.  For hosted sites review your providers help.', 'WPDuplicator') ?></li>
			<li>
				<?php printf("%s <a href='%s' target='_blank'>%s</a> %s", 
						__("Consider using a" , 'WPDuplicator'),
						__(DUPLICATOR_CERTIFIED, 'WPDuplicator'),
						__("certified" , 'WPDuplicator'),
						__("hosting provider.", 'WPDuplicator')	); ?>
			</li>
		</div><br/>
	
		<b><?php _e("Server Response", 'WPDuplicator') ?></b><br/>
		<div id="dup-system-err-msg2"></div>
		<i style='font-size:11px'>
			<?php 
				printf('%s %s', 
					__("See online help for more details at", 'WPDuplicator'), 
					"<a href='" . DUPLICATOR_HELPLINK . "' target='_blank'>support.lifeinthegrid.com</a>" );
			?>
		</i>
	</div>
</div>


<!-- ==========================================
DIALOG: SYSTEM CHECK -->
<div id="dup-dlg-system-check" title="<?php _e('System Checks', 'WPDuplicator'); ?>">
	
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
	<?php _e("Please validate your system configuration.", 'WPDuplicator'); ?>
	<?php printf("<i>%s</i>", __("Click link for details", 'WPDuplicator')); ?>
	
	<div style="padding: 0px 20px 20px 20px;">
		<div id="dup-sys-check-data">
		
			<!-- =========================================
			SYSTEM REQUIRMENTS -->
			<ul id="dup-sys-check-data-reqs">
				<li>
					<b><?php _e("SYSTEM REQUIRMENTS", 'WPDuplicator') ?></b><hr size="1"/>
					<div class='dup-sys-check-title'><a> SYS-100: <?php _e('File Permissions', 'WPDuplicator');?></a></div> <span id='SYS-100'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							_e("The following directories should have permissions of 755 and files 644.  Keep in mind that PHP may be accessing the paths/files as the user id that the web server runs as.", 'WPDuplicator');
							echo "<br/><br/>";
							
							$test = is_readable(DUPLICATOR_WPROOTPATH) ? 'Pass' : 'Fail';
							printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_WPROOTPATH);
							
							$test = is_writeable(DUPLICATOR_SSDIR_PATH) ? 'Pass' : 'Fail';
							printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_SSDIR_PATH);
							
							$test = is_writeable(DUPLICATOR_PLUGIN_PATH . 'files/') ? 'Pass' : 'Fail';
							printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_PLUGIN_PATH . 'files/');
							
							$test = is_writeable(DUPLICATOR_PLUGIN_PATH . 'files/installer.rescue.php') ? 'Pass' : 'Fail';
							printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_PLUGIN_PATH . 'files/installer.rescue.php');
							
							echo "<br/>";
						?>
					</div>
				</li>
				<li>
					<div class='dup-sys-check-title'><a>SYS-101: <?php _e('Reserved Files', 'WPDuplicator');?></a></div> <span id='SYS-101'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							_e('If this check fails then a reserved file was found in the WordPress root directory. The following are reserved file names installer.php, installer-data.sql and installer-log.txt.  In order to archive your data correctly please remove any of these files from your WordPress root directory. Then try creating your package again.', 'WPDuplicator');
						?>
					</div>
				</li>
				<li>
					<div class='dup-sys-check-title'><a>SYS-102: <?php _e('Zip Archive Enabled', 'WPDuplicator');?></a></div> <span id='SYS-102'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							_e("The ZipArchive extension for PHP is required for compression.  Please contact your hosting provider if you're on a hosted server.  For additional information see our online documentation.", 'WPDuplicator');
						?>
					</div>
				</li>
				<li>
					<div class='dup-sys-check-title'><a>SYS-103: <?php _e('Safe Mode Off', 'WPDuplicator');?></a></div> <span id='SYS-103'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							_e("Safe Mode needs to be disabled in order for the Duplicator to operate correctly.  Please set safe_mode = Off in you php.ini file.  If you're on a hosted server and don't have access to the php.ini file then you will need to request this setting be updated. Safe Mode is no longer in future versions of PHP.  If your host will not work with you to resolve the issue then consider a higher reputable hosting provider.", 'WPDuplicator');
						?>
					</div>
				</li>	
				<li>
					<div class='dup-sys-check-title'><a>SYS-104: <?php _e('MySQLi Support', 'WPDuplicator');?></a></div> <span id='SYS-104'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							_e("In order to complete an install the mysqli extension for PHP is required. If you are on a hosted server please contact your host and request that mysqli extension be enabled. For more information visit: http://php.net/manual/en/mysqli.installation.php", 'WPDuplicator');
						?>
					</div>
				</li>	
			</ul>
	
		
			<!-- =========================================
			SYSTEM CHECKS -->
			<b><?php _e("SYSTEM CHECKS", 'WPDuplicator') ?></b><hr style='margin-top:-2px' size="1"/>
			
			<!-- PHP SERVER -->
			<div class='dup-sys-check-title'><b><?php _e("PHP Version", 'WPDuplicator');  echo ":</b> " . phpversion(); ?></div> 
			<?php if (phpversion() >= 5.3): ?>
				<div class='dup-sys-pass'><?php _e('Good', 'WPDuplicator'); ?></div>
			<?php elseif (phpversion() >= 5.2): ?>
				<div class='dup-sys-ok'><?php _e('OK', 'WPDuplicator'); ?></div>
				<i style="font-size:11px; font-weight:normal"> &nbsp; (<?php _e("PHP 5.2.17+ is required", 'WPDuplicator') ?>)</i>
			<?php else: ?> 
				<div class='dup-sys-fail'><?php _e('Fail', 'WPDuplicator'); ?></div>
			<?php endif; ?>	<br />
			
			
			<!-- WEB SERVER -->
			<?php if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') !== false): ?>
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'WPDuplicator') ?>:</b> Apache</div> 
				<div class='dup-sys-pass'><?php _e('Good', 'WPDuplicator'); ?></div>
			<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false): ?> 
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'WPDuplicator') ?>:</b> LiteSpeed</div> 
				<div class='dup-sys-ok'><?php _e('OK', 'WPDuplicator'); ?></div>
			<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false): ?> 
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'WPDuplicator') ?>:</b> Nginx</div> 
				<div class='dup-sys-ok'><?php _e('OK', 'WPDuplicator'); ?></div>
			<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false): ?> 
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'WPDuplicator') ?>:</b> Lighthttpd</div> 
				<div class='dup-sys-ok'><?php _e('OK', 'WPDuplicator'); ?></div>
			<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') !== false): ?> 
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'WPDuplicator') ?>:</b> Microsoft IIS</div> 
				<div class='dup-sys-ok'><?php _e('OK', 'WPDuplicator'); ?></div>
			<?php else: ?>
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'WPDuplicator') ?>:</b> <?php _e("Not detected", 'WPDuplicator') ?> </div> 
				<div class='dup-sys-fail'><?php _e('Fail', 'WPDuplicator'); ?></div>
			<?php endif; ?>	<br />
			

			<!-- OPEN BASE DIR -->
			<?php 
			$open_basedir_set = ini_get("open_basedir");
			if (empty($open_basedir_set)): ?>
				<b><div class='dup-sys-check-title'><?php _e("Open Base Dir", 'WPDuplicator') ?>:</b> <?php _e('Off', 'WPDuplicator'); ?></div> 
				<div class='dup-sys-pass'><?php _e('Good', 'WPDuplicator'); ?></div>
			<?php else: ?>
				<b><div class='dup-sys-check-title'><?php _e("Open Base Dir", 'WPDuplicator') ?>:</b> <?php _e('On', 'WPDuplicator'); ?></div> 
				<div class='dup-sys-fail'><?php _e('Fail', 'WPDuplicator'); ?></div>
			<?php endif; ?><br />
			
			
			<hr class='dup-dots' />
			<!-- SAPI -->
			<b><?php _e('PHP SAPI', 'WPDuplicator'); ?>:</b>  <?php echo php_sapi_name(); ?><br/>
			
			<!-- PRE-ZIP OVERVIEW -->
			<b><?php _e('Pre-Zip Overview', 'WPDuplicator'); ?>:</b> 
			<span id='dup-sys-scannow-data'>
				<a href="javascript:void(0)" onclick="Duplicator.getSystemDirectory()"><?php _e("Scan Now", 'WPDuplicator') ?></a> 
			</span><br/>
			
			
			<b>W3 Total Cache:</b>
			<?php 
				$w3tc_path = duplicator_safe_path(WP_CONTENT_DIR) .  '/w3tc';
				if (file_exists($w3tc_path) && ! strstr($GLOBALS['duplicator_opts']['dir_bypass'], $w3tc_path)):
			?>
				<div class="dup-sys-fail"><?php _e("Cache Directory Found", 'WPDuplicator') ?>.</div> 
				<a href="javascript:void(0)" onclick="Duplicator.optionsAppendByPassList('<?php echo addslashes($w3tc_path); ?>')"><?php _e("Add to exclusion path now", 'WPDuplicator') ?></a>
			<?php elseif (strstr($GLOBALS['duplicator_opts']['dir_bypass'], $w3tc_path)): ?>
				<div class="dup-sys-pass"><?php _e("Directory excluded", 'WPDuplicator') ?></div><br/>
				<i style='font-weight:normal;font-size:11px'><?php _e("Path", 'WPDuplicator') ?>: <?php echo addslashes($w3tc_path); ?></i>
			<?php else: ?>
				<div class="dup-sys-pass"><?php _e("Cache Directory Not Found", 'WPDuplicator') ?></div>
			<?php endif; ?><br /><br />

			
			<!-- =========================================
			CERTIFIED HOSTS -->
			<b><?php _e("ONLINE RESOURCES", 'WPDuplicator') ?></b><hr size="1"/>
			<div style='font-weight:normal'>
				<span class="ui-icon ui-icon-help" style="float:left; margin:0 2px 0px 0;"></span>
				<?php 
					printf('%s <i>%s</i>', 
						__("For additional online help please visit", 'WPDuplicator'), 
						"<a href='" . DUPLICATOR_HELPLINK . "' target='_blank'>support.lifeinthegrid.com</a>" );
				?><br/>	
				<span class="ui-icon ui-icon-lightbulb" style="float:left; margin:0 2px 0px 0;"></span>
				<?php 
					printf("%s <i><a href='%s' target='_blank'>%s</a></i>?", 
						__("Need a hosting provider that is", 'WPDuplicator'), 
						DUPLICATOR_CERTIFIED,
						__("Duplicator Certified") );
				?><br/>			
			</div>
			
		</div>
	</div>
</div>


<!-- ==========================================
DIALOG: QUICK PATH -->
<div id="dup-dlg-quick-path" title="<?php _e('Download Links', 'WPDuplicator'); ?>">
	<p>
		<span class="ui-icon ui-icon-locked" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
		<?php _e("The following links contain sensitive data.  Please share with caution!", 'WPDuplicator');	?>
	</p>
	
	<div style="padding: 0px 20px 20px 20px;">
		<a href="javascript:void(0)" style="display:inline-block; text-align:right" onclick="Duplicator.selectQuickPath()">[Select All]</a> <br/>
		<textarea id="dup-dlg-quick-path-data" style='border:1px solid silver; border-radius:5px; width:96%; height:205px; font-size:11px'></textarea><br/>
		<i style='font-size:11px'><?php _e("The database SQL script is a quick link to your database backup script.  An exact copy is also stored in the package.", 'WPDuplicator'); ?></i>
	</div>
</div>


