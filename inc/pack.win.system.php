<!-- ==========================================
DIALOG: SYSTEM CHECK -->
<div id="dup-dlg-system-check" title="<?php _e('System Status', 'wpduplicator'); ?>" style="display:none">
	
	<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
	<?php _e("Please validate your system configuration.", 'wpduplicator'); ?>
	<?php printf("<i>%s</i>", __("Click link for details", 'wpduplicator')); ?>
	
	<div style="padding: 0px 20px 20px 20px;">
		<div id="dup-sys-check-data">
		
			<!-- =========================================
			SYSTEM REQUIREMENTS -->
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
						<form method="post" action="admin.php?page=duplicator_cleanup_page&remove=1">
							<?php _e('A reserved file(s) was found in the WordPress root directory. Reserved file names are installer.php, installer-data.sql and installer-log.txt.  To archive your data correctly please remove any of these files from your WordPress root directory.  Then try creating your package again.', 'wpduplicator');?>
							<br/><input type='submit' class='button action' value='<?php _e('Remove Files Now', 'wpduplicator')?>' style='font-size:10px; margin-top:5px;' />
						</form>
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
								__("The Duplicator requires PHP version 5.2.17+ or higher.  Please contact your host and have them upgrade to this stable secure version.", 'wpduplicator')
							);
							
							printf("<br/><b>%s</b>:<br/>", __("Requried PHP functions", 'wpduplicator'));
							
							$php_test2 = function_exists("file_get_contents") ? 'Pass' : 'Fail';
							printf("<b>%s</b> [file_get_contents] <br/>", $php_test2);
							$php_test3 = function_exists("file_put_contents") ? 'Pass' : 'Fail';
							printf("<b>%s</b> [file_put_contents] <br/>", $php_test3);
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

			
			<!-- PRE-ZIP OVERVIEW -->
			<b><?php _e('Pre-Zip Overview', 'wpduplicator'); ?>:</b> 
			<span id='dup-sys-scannow-data'>
				<a href="javascript:void(0)" onclick="Duplicator.Pack.ScanRootDirectory()"><?php _e("Scan Now", 'wpduplicator') ?></a> 
			</span><br/>
			
			
			<b><?php _e('Cached Data', 'wpduplicator'); ?>:</b>
			<?php 
				$cache_path = duplicator_safe_path(WP_CONTENT_DIR) .  '/cache';
				if (file_exists($cache_path) && ! strstr($GLOBALS['duplicator_opts']['dir_bypass'], $cache_path)):
			?>
				<div class="dup-sys-fail"><?php _e("Cache Directory Found", 'wpduplicator') ?>.</div> 
				<a href="javascript:void(0)" onclick="Duplicator.Pack.OptionsAppendCachePath('<?php echo addslashes($cache_path); ?>')"><?php _e("Add to exclusion path now", 'wpduplicator') ?></a>
			<?php elseif (strstr($GLOBALS['duplicator_opts']['dir_bypass'], $cache_path)): ?>
				<div class="dup-sys-pass"><?php _e("Directory excluded", 'wpduplicator') ?></div><br/>
				<i style='font-weight:normal;font-size:11px'><?php _e("Path", 'wpduplicator') ?>: <?php echo addslashes($cache_path); ?></i>
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


<script type="text/javascript">
jQuery(document).ready(function($) {
	
	/*	----------------------------------------
	*	METHOD: Sets up and diplays the System Status dialog */ 
	Duplicator.Pack.ShowSystemDialog = function(data) {
		//Set Pass/Fail Flags
		for (key in data) {
			var html = (data[key] == 'Fail') ? "<div class='dup-sys-fail'>Fail</div>" : "<div class='dup-sys-pass'>Pass</div>";
			$("#" + key).html(html)
		}

		$('#system-check-msg').animate({ scrollTop: $('#system-check-msg').attr("scrollHeight") }, 2000)
		$("#dup-dlg-system-check").dialog("open");
		Duplicator.Pack.SetStatus("<?php _e('Ready to create new package.', 'wpduplicator'); ?>");
	}	

	/*	----------------------------------------
	*	METHOD: Performs the ajax request for a system check */ 
	Duplicator.Pack.RunSystemCheck = function() {
		Duplicator.Pack.SetStatus("<?php _e('Checking System Status.  Please Wait!', 'wpduplicator'); ?>", 'progress');
		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: "action=duplicator_system_check",
			beforeSend: function() {Duplicator.StartAjaxTimer(); },
			complete: function() {Duplicator.EndAjaxTimer(); },			
			success: function(data) {Duplicator.Pack.ShowSystemDialog(data);},
			error: function(data)   {
				Duplicator.Pack.ShowError('Duplicator.Pack.RunSystemCheck', data);
			}
		});
	}
	
	/*	----------------------------------------
	*	METHOD: Show the size/count of the directory to be zipped */ 
	Duplicator.Pack.ScanRootDirectory = function() {
		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: "action=duplicator_system_directory",
			beforeSend: function() { 
				Duplicator.StartAjaxTimer(); 
				var html = "<?php _e('Scanning Please Wait', 'wpduplicator'); ?>... " + "<img src='<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/progress.gif' style='height:7px; width:46px;'  />" ;
				$('#dup-sys-scannow-data, #dup-dlg-package-confirm-scannow-data').html(html);	
			},
			complete: function() {Duplicator.EndAjaxTimer(); },
			success: function(data) {
				var size    =  data.size 	|| "<?php _e('unreadable', 'wpduplicator'); ?>";
				var count   =  data.count 	|| "<?php _e('unreadable', 'wpduplicator'); ?>";
				var folders =  data.folders || "<?php _e('unreadable', 'wpduplicator'); ?>";
				var flag    =  (data.flag || size.indexOf("-") != -1) ? "<?php _e('*Scan Error', 'wpduplicator'); ?>" : "";
				var html    =  size + " " + count +  " <?php _e('Files', 'wpduplicator'); ?>, " + folders +  " <?php _e('Folders', 'wpduplicator'); ?> " + flag; 
				$('#dup-sys-scannow-data, #dup-dlg-package-confirm-scannow-data').html("<i>" + html + "</i>");
				
			},
			error: function(data)   {
				$('#dup-sys-scannow-data, #dup-dlg-package-confirm-scannow-data').html("<?php _e('error scanning directory', 'wpduplicator'); ?>");
				Duplicator.Pack.ShowError('Duplicator.Pack.ScanRootDirectory', data);
			}
		});
	}
	
	/*	----------------------------------------
	*	METHOD: Toggle the system requirment details*/ 
	Duplicator.Pack.ToggleSystemDetails = function() {
		if ($(this).parents('li').children('div.dup-sys-check-data-details').is(":hidden")) {
			$(this).children('span').addClass('ui-icon-triangle-1-s').removeClass('ui-icon-triangle-1-e');;
			$(this).parents('li').children('div.dup-sys-check-data-details').show(250);
		} else {
			$(this).children('span').addClass('ui-icon-triangle-1-e').removeClass('ui-icon-triangle-1-s');
			$(this).parents('li').children('div.dup-sys-check-data-details').hide(250);
		}
	}


	//LOAD: 'System Status' Dialog
	$("#dup-dlg-system-check").dialog({
		autoOpen:false, height:575, width:550, create:Duplicator.UI.CreateDialog, close:Duplicator.UI.CloseDialog, modal: true, 
		buttons: {
			'close' : {
					'text' : "<?php _e("Close", 'wpduplicator') ?>",
					'class': "button action",
					'click' : function() { $(this).dialog("close");}
				}
			}
	});

	//Make the system requirments toggle
	$('#dup-sys-check-data-reqs a').each(function() {
		$(this).attr('href', 'javascript:void(0)');
		$(this).click(Duplicator.Pack.ToggleSystemDetails);
		$(this).prepend("<span class='ui-icon ui-icon-triangle-1-e dup-toggle' />");
	});
		
});
</script>
