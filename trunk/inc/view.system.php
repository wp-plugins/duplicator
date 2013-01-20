<!-- ==========================================
DIALOG: PACKAGE CONFIRMATION-->
<div id="dup-dlg-package-confirm" title="<?php _e('Package Creation', 'wpduplicator'); ?>">
	<span class="ui-icon ui-icon-disk" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
	<b><?php _e("Create a new Package Set?", 'wpduplicator');	?></b>
	
	<p style="padding:10px 20px 10px 20px; line-height:26px; text-align:left; border:1px solid #efefef; border-radius:5px">
		<b><?php _e('Name', 'wpduplicator') ?>:</b> <span id="dup-dlg-package-confirm-msg"></span><br/>

		<b><?php _e('Pre-Zip Overview', 'wpduplicator'); ?>:</b>
		<span id='dup-dlg-package-confirm-scannow-data'>
			<a href="javascript:void(0)" onclick="Duplicator.getSystemDirectory()"><?php _e("Perform Scan", 'wpduplicator') ?></a> 
		</span><br/>
		<i style='font-size:11px'><?php _e("Scan sizes over 1GB may not finish processing on some hosting providers.", 'wpduplicator') ?></i>
		
		<div style='font-size:11px; line-height:15px'>
			<i>
				<?php printf("%s <a href='javascript:void(0)'  onclick='Duplicator.optionsOpen()'>%s</a>. %s",
						__('Note: A scan will provide an estimate on the size of your file system.  The scan will exclude items in the', 'wpduplicator'),
						__('directory filter list', 'wpduplicator'),
						__('Files that are not readable by the plugin will not be included in the overview.', 'wpduplicator'));
				?>
			</i>
		</div>
	</p>
</div>

<!-- ==========================================
DIALOG: SYSTEM ERROR -->
<div id="dup-dlg-system-error" title="<?php _e('System Constraint', 'wpduplicator'); ?>">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
		<?php _e("Please try again! An issue has occurred.", 'wpduplicator');	?>
	</p>
	
	<div style="padding: 0px 20px 20px 20px;">
		<b><?php _e("Recommendations", 'wpduplicator') ?></b><br/>
		<div id="dup-system-err-msg1">
			<li>
				<?php printf("<a href='javascript:void(0)' onclick='Duplicator.getSystemCheck()'>%s</a> %s &amp; 
							  <a href='javascript:void(0)' onclick='window.location.reload();'>%s</a> %s",
						__("Validate", 'wpduplicator'),
					    __("your system", 'wpduplicator'),
						__("refresh", 'wpduplicator'),
						__("the dashboard.", 'wpduplicator')); ?>
			</li>
			<li>
				<?php printf("<a href='javascript:void(0)' onclick='Duplicator.openLog()'>%s</a> %s.",
						__("Monitor", 'wpduplicator'),
						__("your log file a few more minutes as processing may continue on some systems", 'wpduplicator')); 
				?>
			</li>
			<li><?php _e('Contact your server admin to have the page timeout increased (see duration below)', 'wpduplicator') ?>.</li>
			<li><?php _e('Consider adding a directory filter in the options dialog if the process continues to timeout', 'wpduplicator') ?>.</li>
			<li><?php _e('Check your disk space.  For hosted sites review your providers help.', 'wpduplicator') ?></li>
			<li>
				<?php printf("%s <a href='%s' target='_blank'>%s</a> %s", 
						__("Consider using an" , 'wpduplicator'),
						__(DUPLICATOR_CERTIFIED, 'wpduplicator'),
						__("approved" , 'wpduplicator'),
						__("hosting provider.", 'wpduplicator')	); ?>
			</li>
		</div><br/>
	
		<b><?php _e("Server Response", 'wpduplicator') ?></b><br/>
		<div id="dup-system-err-msg2"></div>
		<i style='font-size:11px'>
			<?php 
				printf('%s %s', 
					__("See online help for more details at", 'wpduplicator'), 
					"<a href='" . DUPLICATOR_HELPLINK . "' target='_blank'>support.lifeinthegrid.com</a>" );
			?>
		</i>
	</div>
</div>


<!-- ==========================================
DIALOG: SYSTEM CHECK -->
<div id="dup-dlg-system-check" title="<?php _e('System Status', 'wpduplicator'); ?>">
	
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
	<?php _e("Please validate your system configuration.", 'wpduplicator'); ?>
	<?php printf("<i>%s</i>", __("Click link for details", 'wpduplicator')); ?>
	
	<div style="padding: 0px 20px 20px 20px;">
		<div id="dup-sys-check-data">
		
			<!-- =========================================
			SYSTEM REQUIRMENTS -->
			<ul id="dup-sys-check-data-reqs">
				<li>
					<b><?php _e("SYSTEM REQUIRMENTS", 'wpduplicator') ?></b><hr size="1"/>
					<div class='dup-sys-check-title'><a> SYS-100: <?php _e('File Permissions', 'wpduplicator');?></a></div> <span id='SYS-100'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							echo "<b>";  _e("Required permissions", 'wpduplicator'); echo ":</b>";
							echo "<br/>";

							$test = is_writeable(DUPLICATOR_WPROOTPATH) ? 'Pass' : 'Fail';
							printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_WPROOTPATH);
							
							$test = is_writeable(DUPLICATOR_SSDIR_PATH) ? 'Pass' : 'Fail';
							printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_SSDIR_PATH);
							
							$test = is_writeable(DUPLICATOR_PLUGIN_PATH . 'files/') ? 'Pass' : 'Fail';
							printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_PLUGIN_PATH . 'files/');
							
							$test = is_writeable(DUPLICATOR_PLUGIN_PATH . 'files/installer.rescue.php') ? 'Pass' : 'Fail';
							printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_PLUGIN_PATH . 'files/installer.rescue.php');
							
							echo "<br/>";	
							
							_e("The above paths should have permissions of 755 for directories and 644 for files. On some hosts the permission set requires 777.  Setting items to 777 is a security issue and should only be set temporarily.  Please avoid any hosting company that requires this kind of setup.  See the 'Duplicator Approved' link at the bottom of this dialog for a list of approved hosting providers.", 'wpduplicator');
							echo "<br/><br/>";
							
							_e("Also be sure to check the Owner/Group settings and validate they are correct and match other successful directories/files that are accessible.  For more details contact your host or visit their help pages for more information on how they implement permissions and group settings.", 'wpduplicator');		
							
							echo "<br/>";
						?>
					</div>
				</li>
				<li>
					<div class='dup-sys-check-title'><a>SYS-101: <?php _e('Reserved Files', 'wpduplicator');?></a></div> <span id='SYS-101'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							_e('If this check fails then a reserved file was found in the WordPress root directory. The following are reserved file names installer.php, installer-data.sql and installer-log.txt.  In order to archive your data correctly please remove any of these files from your WordPress root directory. Then try creating your package again.', 'wpduplicator');
						?>
					</div>
				</li>
				<li>
					<div class='dup-sys-check-title'><a>SYS-102: <?php _e('Zip Archive Enabled', 'wpduplicator');?></a></div> <span id='SYS-102'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							_e("The ZipArchive extension for PHP is required for compression.  Please contact your hosting provider if you're on a hosted server.  For additional information see our online documentation.", 'wpduplicator');
						?>
					</div>
				</li>
				<li>
					<div class='dup-sys-check-title'><a>SYS-103: <?php _e('Safe Mode Off', 'wpduplicator');?></a></div> <span id='SYS-103'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							_e("Safe Mode needs to be disabled in order for the Duplicator to operate correctly.  Please set safe_mode = Off in you php.ini file.  If you're on a hosted server and don't have access to the php.ini file then you will need to request this setting be updated. Safe Mode is no longer in future versions of PHP.  If your host will not work with you to resolve the issue then consider a higher reputable hosting provider.", 'wpduplicator');
						?>
					</div>
				</li>	
				<li>
					<div class='dup-sys-check-title'><a>SYS-104: <?php _e('MySQL Support', 'wpduplicator');?></a></div> <span id='SYS-104'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
						
							printf("%s <b>[%s]</b>. %s",
								__("The version of MySQL on this server is ", 'wpduplicator'),
								$wpdb->db_version(),
								__("The Duplicator requires MySQL version 4.1+ or better.", 'wpduplicator')
							);				
							echo '  ';
							_e("If the MySQL server version is valid and this requirement fails then the mysqli extension is not installed. If you are on a hosted server please contact your host and request that mysqli extension and MySQL Server 4.1+ be installed. For more info visit: http://php.net/manual/en/mysqli.installation.php", 'wpduplicator');
						?>
					</div>
				</li>
				<li>
					<div class='dup-sys-check-title'><a>SYS-105: <?php _e('PHP Support', 'wpduplicator');?></a></div> <span id='SYS-105'></span>
					<div class='dup-sys-check-data-details'>
						<?php 
							printf("%s <b>[%s]</b>. %s",
								__("The version of PHP on this server is ", 'wpduplicator'),
								phpversion(),
								__("The Duplicator requires at least PHP version 5.2.17+ on this server.  Please contact your host and have them upgrade to this stable secure version if the test fails.", 'wpduplicator')
							);
						?>
					</div>
				</li>	
			</ul>
	
		
			<!-- =========================================
			SYSTEM CHECKS -->
			<b><?php _e("SYSTEM CHECKS", 'wpduplicator') ?></b><hr style='margin-top:-2px' size="1"/>
			
			
			<!-- WEB SERVER -->
			<?php if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') !== false): ?>
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'wpduplicator') ?>:</b> Apache</div> 
				<div class='dup-sys-pass'><?php _e('Good', 'wpduplicator'); ?></div>
			<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false): ?> 
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'wpduplicator') ?>:</b> LiteSpeed</div> 
				<div class='dup-sys-ok'><?php _e('OK', 'wpduplicator'); ?></div>
			<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false): ?> 
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'wpduplicator') ?>:</b> Nginx</div> 
				<div class='dup-sys-ok'><?php _e('OK', 'wpduplicator'); ?></div>
			<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'lighttpd') !== false): ?> 
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'wpduplicator') ?>:</b> Lighthttpd</div> 
				<div class='dup-sys-ok'><?php _e('OK', 'wpduplicator'); ?></div>
			<?php elseif (stristr($_SERVER['SERVER_SOFTWARE'], 'iis') !== false): ?> 
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'wpduplicator') ?>:</b> Microsoft IIS</div> 
				<div class='dup-sys-ok'><?php _e('OK', 'wpduplicator'); ?></div>
			<?php else: ?>
				<b><div class='dup-sys-check-title'><?php _e("Web Server", 'wpduplicator') ?>:</b> <?php _e("Not detected", 'wpduplicator') ?> </div> 
				<div class='dup-sys-fail'><?php _e('Fail', 'wpduplicator'); ?></div>
			<?php endif; ?>	<br />
			

			<!-- OPEN BASE DIR -->
			<?php 
			$open_basedir_set = ini_get("open_basedir");
			if (empty($open_basedir_set)): ?>
				<b><div class='dup-sys-check-title'><?php _e("Open Base Dir", 'wpduplicator') ?>:</b> <?php _e('Off', 'wpduplicator'); ?></div> 
				<div class='dup-sys-pass'><?php _e('Good', 'wpduplicator'); ?></div>
			<?php else: ?>
				<b><div class='dup-sys-check-title'><?php _e("Open Base Dir", 'wpduplicator') ?>:</b> <?php _e('On', 'wpduplicator'); ?></div> 
				<div class='dup-sys-fail'><?php _e('Fail', 'wpduplicator'); ?></div>
			<?php endif; ?><br />
			
			
			<hr class='dup-dots' />
			<!-- SAPI -->
			<b><?php _e('PHP SAPI', 'wpduplicator'); ?>:</b>  <?php echo php_sapi_name(); ?><br/>
			
			<!-- PRE-ZIP OVERVIEW -->
			<b><?php _e('Pre-Zip Overview', 'wpduplicator'); ?>:</b> 
			<span id='dup-sys-scannow-data'>
				<a href="javascript:void(0)" onclick="Duplicator.getSystemDirectory()"><?php _e("Scan Now", 'wpduplicator') ?></a> 
			</span><br/>
			
			
			<b>W3 Total Cache:</b>
			<?php 
				$w3tc_path = duplicator_safe_path(WP_CONTENT_DIR) .  '/w3tc';
				if (file_exists($w3tc_path) && ! strstr($GLOBALS['duplicator_opts']['dir_bypass'], $w3tc_path)):
			?>
				<div class="dup-sys-fail"><?php _e("Cache Directory Found", 'wpduplicator') ?>.</div> 
				<a href="javascript:void(0)" onclick="Duplicator.optionsAppendByPassList('<?php echo addslashes($w3tc_path); ?>')"><?php _e("Add to exclusion path now", 'wpduplicator') ?></a>
			<?php elseif (strstr($GLOBALS['duplicator_opts']['dir_bypass'], $w3tc_path)): ?>
				<div class="dup-sys-pass"><?php _e("Directory excluded", 'wpduplicator') ?></div><br/>
				<i style='font-weight:normal;font-size:11px'><?php _e("Path", 'wpduplicator') ?>: <?php echo addslashes($w3tc_path); ?></i>
			<?php else: ?>
				<div class="dup-sys-pass"><?php _e("Cache Directory Not Found", 'wpduplicator') ?></div>
			<?php endif; ?><br /><br />

			
			<!-- =========================================
			CERTIFIED HOSTS -->
			<b><?php _e("ONLINE RESOURCES", 'wpduplicator') ?></b><hr size="1"/>
			<div style='font-weight:normal'>
				<span class="ui-icon ui-icon-help" style="float:left; margin:0 2px 0px 0;"></span>
				<?php 
					printf('%s <i>%s</i>', 
						__("For additional online help please visit", 'wpduplicator'), 
						"<a href='" . DUPLICATOR_HELPLINK . "' target='_blank'>support.lifeinthegrid.com</a>" );
				?><br/>	
				<span class="ui-icon ui-icon-lightbulb" style="float:left; margin:0 2px 0px 0;"></span>
				<?php 
					printf("%s <i><a href='%s' target='_blank'>%s</a></i>?", 
						__("Need a hosting provider that is", 'wpduplicator'), 
						DUPLICATOR_CERTIFIED,
						__("Duplicator Approved", 'wpduplicator') );
				?><br/>			
			</div>
			
		</div>
	</div>
</div>


<!-- ==========================================
DIALOG: QUICK PATH -->
<div id="dup-dlg-quick-path" title="<?php _e('Download Links', 'wpduplicator'); ?>">
	<p>
		<span class="ui-icon ui-icon-locked" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
		<?php _e("The following links contain sensitive data.  Please share with caution!", 'wpduplicator');	?>
	</p>
	
	<div style="padding: 0px 20px 20px 20px;">
		<a href="javascript:void(0)" style="display:inline-block; text-align:right" onclick="Duplicator.selectQuickPath()">[Select All]</a> <br/>
		<textarea id="dup-dlg-quick-path-data" style='border:1px solid silver; border-radius:5px; width:96%; height:205px; font-size:11px'></textarea><br/>
		<i style='font-size:11px'><?php _e("The database SQL script is a quick link to your database backup script.  An exact copy is also stored in the package.", 'wpduplicator'); ?></i>
	</div>
</div>


