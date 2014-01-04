<!-- =========================================
META-BOX1: SYSTEM REQUIREMENTS -->
<div class="dup-box">
	<div class="dup-box-title dup-box-title-fancy">
		<i class="fa fa-check-square-o"></i>
		<?php 
			_e("Requirements:", 'wpduplicator');
			echo ($dup_tests['Success']) ? ' <div class="dup-sys-pass">Pass</div>' : ' <div class="dup-sys-fail">Fail</div>';		
		?>
		<div class="dup-box-arrow"></div>
	</div>
	
	<div class="dup-box-panel" style="<?php echo ($dup_tests['Success']) ? 'display:none' : ''; ?>">

		<div class="dup-sys-section">
			<i><?php _e("System requirments must pass for the Duplicator to work properly.  Click each link for details.", 'wpduplicator'); ?></i>
		</div>

		<!-- PERMISSIONS: SYS-100 -->
		<div class='dup-sys-req'>
			<div class='dup-sys-title'>
				<a><?php _e('Permissions', 'wpduplicator');?></a> <div><?php echo $dup_tests['SYS-100'];?></div>
			</div>
			<div class="dup-sys-info dup-info-box">
				<?php 
					echo "<b>";  _e("Required Paths", 'wpduplicator'); echo ":</b><br/>";

					$test = is_writeable(DUPLICATOR_WPROOTPATH) ? 'Pass' : 'Fail';
					printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_WPROOTPATH);
					$test = is_writeable(DUPLICATOR_SSDIR_PATH) ? 'Pass' : 'Fail';
					printf("<b>%s</b> [%s] <br/>", $test, DUPLICATOR_SSDIR_PATH);
					$test = is_writeable(DUPLICATOR_SSDIR_PATH_TMP) ? 'Pass' : 'Fail';
					printf("<b>%s</b> [%s] <br/><br/>", $test, DUPLICATOR_SSDIR_PATH_TMP);
					
					printf("<b>%s:</b> [%s] <br/><br/>", __('PHP Script Owner', 'wpduplicator'), get_current_user());
					_e("The above paths should have permissions of 755 for directories and 644 for files. You can temporarily try 777 if you continue to have issues.  Also be sure to check the owner/group settings.  For more details contact your host or server administrator.", 'wpduplicator');
				?>
				<small><?php _e('Status Code', 'wpduplicator');?>: SYS-100</small>
			</div>				
		</div>

		<!-- SYS-101 -->
		<div class='dup-sys-req'>
			<div class='dup-sys-title'>
				<a><?php _e('Reserved Files', 'wpduplicator');?></a> <div><?php echo $dup_tests['SYS-101'];?></div>
			</div>
			<div class="dup-sys-info dup-info-box">
				<form method="post" action="admin.php?page=duplicator-tools&tab=cleanup&action=installer">
					<?php _e('A reserved file(s) was found in the WordPress root directory. Reserved file names are installer.php, installer-data.sql and installer-log.txt.  To archive your data correctly please remove any of these files from your WordPress root directory.  Then try creating your package again.', 'wpduplicator');?>
					<br/><input type='submit' class='button action' value='<?php _e('Remove Files Now', 'wpduplicator')?>' style='font-size:10px; margin-top:5px;' />
				</form>
				<small><?php _e('Status Code', 'wpduplicator');?>: SYS-101</small>
			</div>
		</div>

		<!-- SYS-102 -->
		<div class='dup-sys-req'>
			<div class='dup-sys-title'>
				<a><?php _e('Zip Archive Enabled', 'wpduplicator');?></a> <div><?php echo $dup_tests['SYS-102'];?></div>
			</div> 
			<div class="dup-sys-info dup-info-box">
				<?php _e("The ZipArchive extension for PHP is required for compression.  Please contact your hosting provider if you're on a hosted server.  For additional information see our online documentation.", 'wpduplicator'); ?>
				<small><?php _e('Status Code', 'wpduplicator');?>: SYS-102</small>
			</div>
		</div>

		<!-- SYS-103 -->
		<div class='dup-sys-req'>
			<div class='dup-sys-title'>
				<a><?php _e('Safe Mode Off', 'wpduplicator');?></a>
				<div><?php echo $dup_tests['SYS-103'];?></div>
			</div>
			<div class="dup-sys-info dup-info-box">
				<?php
					$msg  = "Safe Mode should be set safe_mode=Off in you php.ini file. On hosted servers you may have to request this setting be turned off.  ";
					$msg .= "Please note that Safe Mode is deprecated as of PHP 5.3.0";
					_e($msg, 'wpduplicator'); 
				?>
				<small><?php _e('Status Code', 'wpduplicator');?>: SYS-103</small>
			</div>
		</div>

		<!-- SYS-104 -->
		<div class='dup-sys-req'>
			<div class='dup-sys-title'>
				<a><?php _e('MySQL Support', 'wpduplicator');?></a>
				<div><?php echo $dup_tests['SYS-104'];?></div>
			</div>
			<div class="dup-sys-info dup-info-box">
				<?php 
					printf("<b>%s:</b> [%s]<br/><br/>",	__("MySQL version", 'wpduplicator'), $wpdb->db_version());	
					$msg  = "MySQL version 5.0+ or better is required. If the MySQL version is valid and this requirement fails then the mysqli extension (note the trailing 'i') is not installed.  ";
					$msg .= "Contact your server administrator and request that mysqli extension and MySQL Server 5.0+ be installed. Please note in future version support for other databases and extensions will be added.";
					_e($msg, 'wpduplicator');
					echo "&nbsp;<i><a href='http://php.net/manual/en/mysqli.installation.php' target='_blank'>[" . __('more info', 'wpduplicator')  . "]</a></i>";
				?>
				<small><?php _e('Status Code', 'wpduplicator');?>: SYS-104</small>
			</div>
		</div>

		<!-- SYS-105 -->
		<div class='dup-sys-req'>
			<div class='dup-sys-title'>
				<a><?php _e('PHP Support', 'wpduplicator');?></a>
				<div><?php echo $dup_tests['SYS-105'];?></div>
			</div>
			<div class="dup-sys-info dup-info-box">
				<?php 
					$php_test1 = function_exists("file_get_contents") ? 'Pass' : 'Fail';
					$php_test2 = function_exists("file_put_contents") ? 'Pass' : 'Fail';
					printf("<b>%s:</b> [%s]<br/><br/>",	__("PHP version", 'wpduplicator'), phpversion());	
					printf("<b>%s</b>:<br/>", __("Requried Functions", 'wpduplicator'));
					printf("<b>%s</b> [file_get_contents] <br/>", $php_test1);
					printf("<b>%s</b> [file_put_contents] <br/><br/>", $php_test2);
					$msg  = "PHP versions 5.2.17+ or higher is required. Please note that in versioning logic a value such as 5.2.9 is less than 5.2.17.  ";
					$msg .= "Please contact your server administrator to upgrade to a stable and secure version of PHP";
					_e($msg, 'wpduplicator');
				?>
				<small><?php _e('Status Code', 'wpduplicator');?>: SYS-105</small>
			</div>
		</div>

		<!-- SYS-106 -->
		<div class='dup-sys-req'>
			<div class='dup-sys-title'>
				<a><?php _e('Web Server', 'wpduplicator');?></a>
				<div><?php echo $dup_tests['SYS-106'];?></div>
			</div>
			<div class="dup-sys-info dup-info-box">
				<?php 
					$web_servers = implode(', ', $GLOBALS['DUPLICATOR_SERVER_LIST']);
					printf("<b>%s:</b> [%s]<br/> %s",
						__("Web Server", 'wpduplicator'),
						$_SERVER['SERVER_SOFTWARE'],
						__("The Duplicator currently works with these web servers: {$web_servers}", 'wpduplicator')
					);
				?>
				<small><?php _e('Status Code', 'wpduplicator');?>: SYS-106</small>
			</div>
		</div>

		<!-- ONLINE SUPPORT -->
		<div class="dup-sys-contact">
			<?php 	
				printf("<i class='fa fa-info'></i> %s <i>%s</i>", 
						__("For additional online help please visit", 'wpduplicator'), 
						"<a href='" . DUPLICATOR_HELPLINK . "' target='_blank'>support.lifeinthegrid.com</a><br/>" );
				printf("<i class='fa fa-lightbulb-o'></i> %s <i><a href='%s' target='_blank'>%s</a></i>?", 
						__("Need a hosting provider that is", 'wpduplicator'), 
						DUPLICATOR_CERTIFIED,
						__("duplicator approved", 'wpduplicator'));
			?>
		</div>

	</div>
</div><br/>