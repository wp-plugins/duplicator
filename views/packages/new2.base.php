<?php
	require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.php');
	$Package = new DUP_Package();
	$Package->SaveActive($_POST);
	$Package = $Package->GetActive();
	
	$package_mysqldump	= DUP_Settings::Get('package_mysqldump');
	$mysqlDumpPath = DUP_Database::GetMySqlDumpPath();
	$build_mode = ($mysqlDumpPath && $package_mysqldump) ? 'mysqldump (fast)' : 'PHP (slow)';
?>

<style>
	/* -----------------------------
	PROGRESS ARES-CHECKS */
	div#dup-progress-area {text-align:center; max-width:650px; min-height:200px; margin:0px auto 0px auto; padding:0px;}
	div#dup-msg-success {color:#18592A; padding:5px; text-align: left}	
	div#dup-msg-success-subtitle {font-style: italic; margin:7px 0px}	
	div#dup-msg-error {color:#A62426; padding:5px; max-width: 790px;}
	div#dup-msg-error-response-text { max-height:350px; overflow-y:scroll; border:1px solid silver; border-radius: 3px; padding:8px;background:#fff}

	div.dup-panel {margin-bottom: 25px}
	div.dup-scan-filter-status {display:inline; float: right; font-size:11px; margin-right:10px; color:#AF0000; font-style: italic}
	/* 	SERVER-CHECKS */
	div.dup-scan-title {display:inline-block;  padding:1px; font-weight: bold;}
	div.dup-scan-title a {display:inline-block; width:225px; padding:1px; }
	div.dup-scan-title div {display:inline-block;  }
	div.dup-scan-info {display:none;}
	div.dup-scan-good {display:inline-block; color:green;font-weight: bold;}
	div.dup-scan-warn {display:inline-block; color:#AF0000;font-weight: bold;}
	span.dup-toggle {float:left; margin:0 2px 2px 0; }
	/*DATABASE*/
	table#dup-scan-db-details {line-height: 14px; margin:5px 0px 0px 15px; border-top:1px dashed silver; width:98%}
	table#dup-scan-db-details td {padding:0px;}
	table#dup-scan-db-details td:first-child {font-weight: bold;  white-space: nowrap; width:90px}
	div#dup-scan-db-info {margin:0px 0px 0px 10px}
	div#data-db-tablelist {max-height: 300px; overflow-y: scroll}
	div#data-db-tablelist div{padding:0px 0px 0px 15px;}
	div#data-db-tablelist span{display:inline-block; min-width: 75px}
	div#data-db-size1 {display: inline-block; float:right; font-size:11px; margin-right: 15px; font-style: italic}
	/*FILES */
	div#data-arc-size1 {display: inline-block; float:right; font-size:11px; margin-right: 15px; font-style: italic}
	div#data-arc-names-data, div#data-arc-big-data
		{word-wrap: break-word;font-size:10px; border:1px dashed silver; padding:5px; display: none}
	
	/*Footer*/
	div.dup-button-footer {text-align:center; margin:5px 0px 0px 0px}
	button.button {font-size:15px !important; height:30px !important; font-weight:bold; padding:3px 5px 5px 5px !important;}
</style>

<!-- =========================================
WIZARD STEP TABS -->
<div id="dup-wiz">
	<div id="dup-wiz-steps">
		<div class="completed-step"><a><span>1</span> <?php _e('Setup', 'wpduplicator'); ?></a></div>
		<div class="active-step"><a><span>2</span> <?php _e('Scan', 'wpduplicator'); ?> </a></div>
		<div><a><span>3</span> <?php _e('Build', 'wpduplicator'); ?> </a></div>
	</div>
	<div id="dup-wiz-title">
		<?php _e('Step 2: System Scan', 'wpduplicator'); ?>
	</div> <hr />
</div>	

<form id="form-duplicator" method="post" action="?page=duplicator&tab=new3">
<div id="dup-progress-area">
	<!--  PROGRESS BAR -->
	<div id="dup-progress-bar-area">
		<h2><i class="fa fa-spinner fa-spin"></i> <?php _e('Scanning Site', 'wpduplicator'); ?></h2>
		<div id="dup-progress-bar"></div>
		<b><?php _e('Please Wait...', 'wpduplicator'); ?></b>
	</div>

	<!--  SUCCESS MESSAGE -->
	<div id="dup-msg-success" style="display:none">
		<div style="text-align:center">
			<div class="dup-hdr-success"><i class="fa fa-check-square-o fa-lg"></i> <?php _e('Scan Complete', 'wpduplicator'); ?></div>
			<div id="dup-msg-success-subtitle"><?php _e("Scan checks are not required to pass, however they could cause issues on some systems.", 'wpduplicator'); ?></div>
		</div><br/>
		
		<!-- ================================================================
		META-BOX: SERVER
		================================================================ -->
		<div class="dup-panel">
		<div class="dup-panel-title">
			<i class="fa fa-hdd-o"></i> <?php 	_e("Server", 'wpduplicator');	?>
			<div style="float:right; margin:-1px 10px 0px 0px">
				<small><a href="?page=duplicator-settings&tab=diagnostics" target="_blank">[<?php _e('Diagnostics', 'wpduplicator');?>]</a> <i class="fa fa-external-link"></i> </small>	
			</div>
		
		</div>
		<div class="dup-panel-panel">
			<!-- -------------------
			OPEN BASE DIRECTORY: 100 -->
			<div>
				<div class='dup-scan-title'>
					<a><?php _e('PHP Settings', 'wpduplicator');?></a> <div id="data-srv-openbase"></div>
				</div>
				<div class='dup-scan-info dup-info-box'>
					<?php 
						$test = ini_get("open_basedir");
						echo '<b>' . __('Open Base Dir', 'wpduplicator') . ':</b> ';
						echo (empty($test)) ? 'Off' : 'On';  echo '<br/><br/>';
						_e('The Duplicator has been known to have issues with some of the settings above. Please work with your host or server administrator to disable this value in the php.ini file if you’re having issues with building a package.', 'wpduplicator');
						echo "&nbsp;<i><a href='http://www.php.net/manual/en/ini.core.php#ini.open-basedir' target='_blank'>[" . __('more info', 'wpduplicator')  . "]</a></i>";
					?>
					<small><?php _e('Status Code', 'wpduplicator');?>: CHK-SRV-100</small>
				</div>
			</div>
			
			<!-- -------------------
			CACHED DATA: 101 -->
			<div>
				<div class='dup-scan-title'>
					<a><?php _e('Cached Data', 'wpduplicator');?></a> <div id="data-srv-cacheon"></div>
				</div>
				<div class='dup-scan-info dup-info-box'>
					<?php 
						$cache_path = $cache_path = DUP_Util::SafePath(WP_CONTENT_DIR) .  '/cache';
						echo '<b>' . __('Cache Path', 'wpduplicator') . ":</b> {$cache_path} <br/><br/>";
						_e("Cached data will lead to issues at install time and increases your archive size. It is highly recommended to empty your cache directory at build time. Use caution when removing data from the cache directory. If you’re using a cache plugin please read the directions for how to properly clean the cache directory; simply removing the files can cause errors with some cache plugins.", 'wpduplicator');
					?>
					<small><?php _e('Status Code', 'wpduplicator');?>: CHK-SRV-101</small>
				</div>
			</div>
			
			<!-- -------------------
			TIMEOUTS: 102 -->
			<div>
				<div class='dup-scan-title'>
					<a><?php _e('Timeouts', 'wpduplicator');?></a> <div id="data-srv-timeouts"></div>
				</div>
				<div class='dup-scan-info dup-info-box'>
					<?php 
						$test = ini_get("max_execution_time");		
						echo '<b>' . __('max_execution_time', 'wpduplicator') . ':</b> ';
						echo (empty($test)) ? 'Off' : "{$test}";  
						echo '<br/><br/>';
						
						printf(__('Timeouts effect how long a process is allowed to run.  The recommended timeout is "%1$s" seconds. An attempt is made to override this value if the enviroment allows it.  A "Warn" status will not be an issue unless your host kills PHP processes after a certain amount of time. ', 'wpduplicator'), DUPLICATOR_SCAN_TIMEOUT); 
						echo '<br/><br/>';
						
						_e('Timeouts can also be set at the web server layer, please work with your host or server administrator to make sure there are not restrictions for how long a PHP process is allowed to run.  If you are limited on processing time, consider using the database or file filters to shrink the size of your overall package.   However use caution as excluding the wrong resources can cause your install to not work properly.', 'wpduplicator');
						echo "&nbsp;<i><a href='http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time' target='_blank'>[" . __('more info', 'wpduplicator')  . "]</a></i>";
					?>
					<small><?php _e('Status Code', 'wpduplicator');?>: CHK-SRV-102</small>
				</div>
			</div>	
			

		</div><!-- end .dup-panel -->
		</div><!-- end .dup-panel-panel -->
		
	
		<b style="font-size:16px"><i class="fa fa-bars"></i>&nbsp;<?php _e('Archive', 'wpduplicator');?> </b>
		<hr size="1" />
		
		<!-- ================================================================
		FILES
		================================================================ -->
		<div class="dup-panel">
		<div class="dup-panel-title">
			<i class="fa fa-files-o"></i>
			<?php _e("Files", 'wpduplicator'); ?> 
			<div id="data-arc-size1"></div>
			<div class="dup-scan-filter-status">
				<?php 
					if ($Package->Archive->FilterOn) {
						echo '<i class="fa fa-filter"></i> '; _e('Enabled', 'wpduplicator');
					} 
				?> 
			</div>
		</div>
		<div class="dup-panel-panel">
			
			<!-- -------------------
			TOTAL SIZE: CHK-FILE-100 -->
			<div>
				<div class='dup-scan-title'>
					<a><?php _e('Total Size', 'wpduplicator');?></a> <div id="data-arc-status-size"></div>
				</div>
				<div class='dup-scan-info  dup-info-box'>
					<b><?php _e('Size', 'wpduplicator');?>:</b> <span id="data-arc-size2"></span>  &nbsp; | &nbsp;
					<b><?php _e('File Count', 'wpduplicator');?>:</b> <span id="data-arc-files"></span>  &nbsp; | &nbsp;
					<b><?php _e('Directory Count', 'wpduplicator');?>:</b> <span id="data-arc-dirs"></span> <br/><br/>
					<?php 
						printf(__('Total size reprents all files minus any filters that have been setup.  The current thresholds that trigger warnings are %1$s for the entire site and %2$s for large files.', 'wpduplicator'), DUP_Util::ByteSize(DUPLICATOR_SCAN_SITE), DUP_Util::ByteSize(DUPLICATOR_SCAN_BIGFILE));
					?>
					<small><?php _e('Status Code', 'wpduplicator');?>: CHK-FILE-100</small>
				</div>
			</div>		
			
			<!-- -------------------
			FILE NAME LENGTHS: CHK-FILE-101 -->
			<div>
				<div class='dup-scan-title'>
					<a><?php _e('Invalid Names', 'wpduplicator');?></a> <div id="data-arc-status-names"></div>
				</div>
				<div class='dup-scan-info dup-info-box'>
					<?php 
						_e('Invalid file or folder names can cause issues when extracting an archive across different environments.  Invalid file names consist of lengths over 200 characters and illegal characters that may not work on all operating systems such as * ? > < : / \ |  .  It is recommended to remove or filter these files before building the archive or else you might have issues at install time.', 'wpduplicator');
					?><br/><br/>
					<a href="javascript:void(0)" onclick="jQuery('#data-arc-names-data').toggle()">[<?php _e('Show File Locations', 'wpduplicator');?>]</a>
					<div id="data-arc-names-data"></div>
					<small><?php _e('Status Code', 'wpduplicator');?>: CHK-FILE-101</small>
				</div>
			</div>		
		
			<!-- -------------------
			LARGE FILES: CHK-FILE-102 -->
			<div>
				<div class='dup-scan-title'>
					<a><?php _e('Large Files', 'wpduplicator');?></a> <div id="data-arc-status-big"></div>
				</div>
				<div class='dup-scan-info  dup-info-box'>
					<?php 
						printf(__('Large files such as movies or other backuped data can cause issues with timeouts.  The current check for large files is %1$s per file.  If your having issues creating a package consider excluding these files with the files filter and manually moving them to your new location.', 'wpduplicator'), DUP_Util::ByteSize(DUPLICATOR_SCAN_BIGFILE));
					?><br/><br/>
					<a href="javascript:void(0)" onclick="jQuery('#data-arc-big-data').toggle()">[<?php _e('Show File Locations', 'wpduplicator');?>]</a>
					<div id="data-arc-big-data"></div>
					<small><?php _e('Status Code', 'wpduplicator');?>: CHK-FILE-102</small>
				</div>
			</div>		

		</div><!-- end .dup-panel -->
		</div><!-- end .dup-panel-panel -->
		
		<!-- ================================================================
		DATABASE
		================================================================ -->
		<div class="dup-panel">
		<div class="dup-panel-title">
			<i class="fa fa-table"></i>
			<?php _e("Database", 'wpduplicator');	?>
			<div id="data-db-size1"></div>
			<div class="dup-scan-filter-status">
				<?php 
					if ($Package->Database->FilterOn) {
						echo '<i class="fa fa-filter"></i> '; _e('Enabled', 'wpduplicator');
					} 
				?> 
			</div>
		</div>
		<div class="dup-panel-panel" id="dup-scan-db">
				
			<!-- -------------------
			TOTAL SIZE: 100 -->
			<div>
				<div class='dup-scan-title'>
					<a><?php _e('Total Size', 'wpduplicator');?></a>
					<div id="data-db-status-size1"></div>
				</div>
				<div class='dup-scan-info  dup-info-box'>
					<b><?php _e('Tables', 'wpduplicator');?>:</b> <span id="data-db-tablecount"></span> &nbsp; | &nbsp;
					<b><?php _e('Records', 'wpduplicator');?>:</b> <span id="data-db-rows"></span> &nbsp; | &nbsp;
					<b><?php _e('Size', 'wpduplicator');?>:</b> <span id="data-db-size2"></span> <br/><br/>
					<?php 
						$lnk = '<a href="maint/repair.php" target="_blank">' . __('repair and optimization', 'wpduplicator') . '</a>';
						printf(__('Total size and row count for all database tables are approximate values.  The thresholds that trigger warnings are %1$s and %2$s records.  Large databases take time to process and can cause issues with server timeout and memory settings.  Running a %3$s on your database can also help improve the overall size and performance.  If your server supports shell_exec and mysqldump you can try to enable this option from the settings menu.', 'wpduplicator'), 
								DUP_Util::ByteSize(DUPLICATOR_SCAN_DBSIZE), 
								number_format(DUPLICATOR_SCAN_DBROWS),
								$lnk);
					?>
					<small><?php _e('Status Code', 'wpduplicator');?>: CHK-DB-100</small>
				</div>
			</div>

			<!-- -------------------
			TABLE DETAILS -->
			<div>
				<div class='dup-scan-title'>
					<a><?php _e('Table Details', 'wpduplicator');?></a>
					<div id="data-db-status-size2"></div>
				</div>
				<div class='dup-scan-info dup-info-box'>
					<div id="dup-scan-db-info">
						<div id="data-db-tablelist"></div>
					</div>
				</div>
			</div>
			
			<table id="dup-scan-db-details">
				<tr><td><b><?php _e('Name:', 'wpduplicator');?></b></td><td><?php echo DB_NAME ;?> </td></tr>
				<tr><td><b><?php _e('Host:', 'wpduplicator');?></b></td><td><?php echo DB_HOST ;?> </td></tr>
				<tr><td><b><?php _e('Build Mode:', 'wpduplicator');?></b></td><td><a href="?page=duplicator-settings"><?php echo $build_mode ;?></a> </td></tr>
			</table>	

		</div><!-- end .dup-panel -->
		</div><!-- end .dup-panel-panel -->
		
		
	</div>

	<!--  ERROR MESSAGE -->
	<div id="dup-msg-error" style="display:none">
		<div class="dup-hdr-error"><i class="fa fa-exclamation-circle"></i> <?php _e('Scan Error', 'wpduplicator'); ?></div>
		<i><?php _e('Please try again!', 'wpduplicator'); ?></i><br/>
		<div style="text-align:left">
			<b><?php _e("Server Status:", 'wpduplicator'); ?></b> &nbsp;
			<div id="dup-msg-error-response-status" style="display:inline-block"></div><br/>

			<b><?php _e("Error Message:", 'wpduplicator'); ?></b>
			<div id="dup-msg-error-response-text"></div>
		</div>
	</div>			

</div> <!-- end #dup-progress-area -->
<div class="dup-button-footer" style="display:none">
	<input type="button" value="&#9668; <?php _e("Back", 'wpduplicator') ?>" onclick="window.location.assign('?page=duplicator&tab=new1')" class="button button-large" />
	<input type="button" value="<?php _e("Rescan", 'wpduplicator') ?>" onclick="Duplicator.Pack.Rescan()" class="button button-large" />
	<input type="submit" value="<?php _e("Build", 'wpduplicator') ?> &#9658" class="button button-primary button-large" />
</div>
</form>

<script type="text/javascript">
jQuery(document).ready(function($) {
		
	/*	----------------------------------------
	*	METHOD: Performs Ajax post to create check system  */
	Duplicator.Pack.Scan = function() {
		var data = {action : 'duplicator_package_scan'}

		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: data,
			complete: function() {$('.dup-button-footer').show()},
			success:    function(data) { Duplicator.Pack.LoadScanData(data)},
			error: function(data) { 
				$('#dup-progress-bar-area').hide(); 
				var status = data.status + ' -' + data.statusText;
				$('#dup-msg-error-response-status').html(status)
				$('#dup-msg-error-response-text').html(data.responseText);
				$('#dup-msg-error').show(200);
				console.log(data);
			}
		});
	}
	
	Duplicator.Pack.Rescan = function() {
		$('#dup-msg-success,#dup-msg-error,.dup-button-footer').hide();
		$('#dup-progress-bar-area').show(); 
		Duplicator.Pack.Scan();
	}
	
	/*	----------------------------------------
	*	METHOD:    */
	Duplicator.Pack.LoadScanData = function(data) {
		
		var errMsg = "unable to read";
		$('#dup-progress-bar-area').hide(); 
		$('#dup-msg-success').show();
		
		//****************
		//SERVER
		$('#data-srv-openbase').text(data.SRV.OpenBase || errMsg);
		$('#data-srv-cacheon').text(data.SRV.CacheOn   || errMsg);
		$('#data-srv-timeouts').text(data.SRV.TimeOuts || errMsg);

		//****************
		//DATABASE
		var html = "";
		if (data.DB.Status.Success) {
			$('#data-db-status-size1').text(data.DB.Status.Size || errMsg);
			$('#data-db-status-size2').text(data.DB.Status.Size || errMsg);
			$('#data-db-size1').text(data.DB.Size || errMsg);
			$('#data-db-size2').text(data.DB.Size || errMsg);
			$('#data-db-rows').text(data.DB.Rows || errMsg);
			$('#data-db-tablecount').text(data.DB.TableCount || errMsg);
			//Table Details
			if (data.DB.TableList == undefined || data.DB.TableList.length == 0) {
				html = '<?php _e("Unable to report on any tables", 'wpduplicator') ?>';
			} else {
				$.each(data.DB.TableList, function(i) {
					html += '<b>' + i  + '</b><br/>';
					$.each(data.DB.TableList[i], function(key,val) {html += '<div><span>' + key  + ':</span>' + val + '</div>'; })
				});					
			}
			$('#data-db-tablelist').append(html);
		} else {
			html = '<?php _e("Unable to report on database stats", 'wpduplicator') ?>';
			$('#dup-scan-db').html(html);
		}
		
		//****************
		//FILES
		$('#data-arc-status-size').text(data.ARC.Status.Size || errMsg);
		$('#data-arc-status-names').text(data.ARC.Status.Names|| errMsg);
		$('#data-arc-status-big').text(data.ARC.Status.Big || errMsg);
		$('#data-arc-size1').text(data.ARC.Size || errMsg);
		$('#data-arc-size2').text(data.ARC.Size || errMsg);
		$('#data-arc-files').text(data.ARC.FileCount || errMsg);
		$('#data-arc-dirs').text(data.ARC.DirCount || errMsg);
	
		//Long Names
		html = '<?php _e("No name length issues.", 'wpduplicator') ?>';
		if (data.ARC.InvalidFiles != undefined && data.ARC.InvalidFiles.length > 0) {
			html = '';
			$.each(data.ARC.InvalidFiles, function(key, val) {html += '<?php _e("FILE", 'wpduplicator') ?> ' + key + ':<br/>' + val  + '<br/>';});	
		}
		$('#data-arc-names-data').html(html);
		
		//Big Files
		html = '<?php _e("No large files found.", 'wpduplicator') ?>';
		if (data.ARC.BigFiles != undefined && data.ARC.BigFiles.length > 0) {
			html = '';
			$.each(data.ARC.BigFiles, function(key, val) {html += '<?php _e("FILE", 'wpduplicator') ?> ' + key + ':<br/>' + val  + '<br/>' ;});	
		}
		$('#data-arc-big-data').html(html);
		

		//Color Code Good/Warn
		$('.dup-scan-title div').each(function() {
			$(this).addClass( ( $(this).text() == 'Good') ? 'dup-scan-good' : 'dup-scan-warn');
		});
	}
	
	//Page Init:
	Duplicator.UI.AnimateProgressBar('dup-progress-bar');
	Duplicator.Pack.Scan();
	
	//Init: Toogle for system requirment detial links
	$('.dup-scan-title a').each(function() {
		$(this).attr('href', 'javascript:void(0)');
		$(this).click({selector : '.dup-scan-info'}, Duplicator.Pack.ToggleSystemDetails);
		$(this).prepend("<span class='ui-icon ui-icon-triangle-1-e dup-toggle' />");
	});
	
});
</script>