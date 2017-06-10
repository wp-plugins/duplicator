<?php
	function _duplicatorGetRootPath() {
		$txt   = __('Root Path', 'duplicator');
		$root  = rtrim(DUPLICATOR_WPROOTPATH, '//');
		$sroot = strlen($root) > 50 ? substr($root, 0, 50) . '...' : $root;
		echo "<div title='{$root}' class='divider'><i class='fa fa-folder-open'></i> {$sroot}</div>";
	}
?>

<!-- ================================================================
ARCHIVE -->
<div class="details-title">
	<i class="fa fa-file-archive-o"></i>&nbsp;<?php _e('Archive', 'duplicator');?>
	<div class="dup-more-details" onclick="Duplicator.Pack.showDetails()"><i class="fa fa-window-maximize"></i></div>
</div>

<div class="scan-header scan-item-first">
	<i class="fa fa-files-o"></i>
	<?php _e("Files", 'duplicator'); ?>
	<i class="fa fa-question-circle data-size-help"
		data-tooltip-title="<?php _e('Archive Size', 'duplicator'); ?>"
		data-tooltip="<?php _e('This size represents only the included files before compression is applied. It does not include the size of the '
					. 'database script or any applied filters.  In most cases the package size once completed will be smaller than this number.', 'duplicator'); ?>"></i>
	<div id="data-arc-size1"></div>
	<div class="dup-scan-filter-status">
		<?php
			if ($Package->Archive->ExportOnlyDB) {
				echo '<i class="fa fa-filter"></i> '; _e('Database Only', 'duplicator');
			} elseif ($Package->Archive->FilterOn) {
				echo '<i class="fa fa-filter"></i> '; _e('Enabled', 'duplicator');
			}
		?>
	</div>
</div>

<!-- ============
TOTAL SIZE -->
<div class="scan-item">
	<div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
		<div class="text"><i class="fa fa-caret-right"></i> <?php _e('Size Check', 'duplicator');?></div>
		<div id="data-arc-status-size"></div>
	</div>
	<div class="info" id="scan-itme-file-size">
		<b><?php _e('Size', 'duplicator');?>:</b> <span id="data-arc-size2"></span>  &nbsp; | &nbsp;
		<b><?php _e('File Count', 'duplicator');?>:</b> <span id="data-arc-files"></span>  &nbsp; | &nbsp;
		<b><?php _e('Directory Count', 'duplicator');?>:</b> <span id="data-arc-dirs"></span> <br/><br/>
		<?php
			_e('Compressing larger sites on <i>some budget hosts</i> may cause timeouts.  ' , 'duplicator');
			echo "<i>&nbsp; <a href='javascipt:void(0)' onclick='jQuery(\"#size-more-details\").toggle(100)'>[" . __('more details...', 'duplicator') . "]</a></i>";
		?>
		<div id="size-more-details">
			<?php
				echo "<b>" . __('Overview', 'duplicator') . ":</b><br/>";

				printf(__('This notice is triggered at <b>%s</b> and can be ignored on most hosts.  If during the build process you see a "Host Build Interrupt" message then this '
					. 'host has strict processing limits.  Below are some options you can take to overcome constraints setup on this host.', 'duplicator'),
					DUP_Util::byteSize(DUPLICATOR_SCAN_SIZE_DEFAULT));

				echo '<br/><br/>';

				echo "<b>" . __('Timeout Options', 'duplicator') . ":</b><br/>";
				echo '<ul>';
				echo '<li>' . __('Apply the "Quick Filters" below or click the back button to apply on previous page.', 'duplicator') . '</li>';
				echo '<li>' . __('See the FAQ link to adjust this hosts timeout limits: ', 'duplicator') . "&nbsp;<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-100-q' target='_blank'>" . __('What can I try for Timeout Issues?', 'duplicator') . '</a></li>';
				echo '<li>' . __('Consider trying multi-threaded support in ', 'duplicator');
					echo "<a href='https://snapcreek.com/duplicator/?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_content=free_size_warn&utm_campaign=duplicator_pro' target='_blank'>" . __('Duplicator Pro.', 'duplicator') . "</a>";
				echo '</li>';
				echo '</ul>';

				$hlptxt = sprintf(__('Files over %1$s are listed below. Larger files such as movies or zipped content can cause timeout issues on some budget hosts.  If you are having '
				. 'issues creating a package try excluding the directory paths below or go back to Step 1 and add them.', 'duplicator'),
				DUP_Util::byteSize(DUPLICATOR_SCAN_WARNFILESIZE));
			?>
		</div>
		<script id="hb-files-large" type="text/x-handlebars-template">
			<div class="container">
				<div class="hdrs">
					<span style="font-weight:bold">
						<?php _e('Quick Filters', 'duplicator'); ?>
						<sup><i class="fa fa-question-circle" data-tooltip-title="<?php _e("Large Files", 'duplicator'); ?>" data-tooltip="<?php echo $hlptxt; ?>"></i></sup>
					</span>
					<div class='hdrs-up-down'>
						<i class="fa fa-caret-up fa-lg dup-nav-toggle" onclick="Duplicator.Pack.toggleAllDirPath(this, 'close')"></i>
						<i class="fa fa-caret-down fa-lg dup-nav-toggle" onclick="Duplicator.Pack.toggleAllDirPath(this, 'open')"></i>
					</div>
				</div>
				<div class="data">
					<?php _duplicatorGetRootPath();	?>
					{{#if ARC.FilterInfo.Files.Size}}
						{{#each ARC.FilterInfo.TreeSize as |directory|}}
							<div class="directory">
								<i class="fa fa-caret-right fa-lg dup-nav" onclick="Duplicator.Pack.toggleDirPath(this)"></i> &nbsp;
								<input type="checkbox" name="dir_paths[]" value="{{directory.dir}}" id="lf_dir_{{@index}}" />
								<label for="lf_dir_{{@index}}" title="{{directory.dir}}">
									<i class="size">[{{directory.size}}]</i> /{{directory.sdir}}/
								</label> <br/>
								<div class="files">
									{{#each directory.files as |file|}}
										<div class="file" title="{{file.name}}"><i class="size">[{{file.bytes}}]</i> {{file.sname}}</div>
									{{/each}}
								</div>
							</div>
						{{/each}}
					{{else}}
						 <?php 
							if (! isset($_GET['retry'])) {
								_e('No large files found during this scan.', 'duplicator');
							} else {
								echo "<div style='color:maroon'>";
								_e('No large files found during this scan.  If your having issues building a package click the back button and try '
									. 'adding the following file filters to non-essential files paths like wp-conent/uploads.   These filtered files can then '
									. 'be manually moved to the new location after you have ran the migration installer.', 'duplicator');
								echo "</div>";
							}

						?>
					{{/if}}
				</div>
			</div>
			<div class="apply-btn">
				<button type="button" class="button-small" onclick="Duplicator.Pack.applyFilters(this, 'large')">
					<i class="fa fa-filter"></i> <?php _e('Apply Filters &amp; Rescan', 'duplicator');?>
				</button>
			</div>
		</script>
		<div id="hb-files-large-result" class="hb-files-style"></div>
	</div>
</div>

<!-- ============
FILE NAME CHECKS -->
<div class="scan-item scan-item-last">
	<div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
		<div class="text"><i class="fa fa-caret-right"></i> <?php _e('Name Check', 'duplicator');?></div>
		<div id="data-arc-status-names"></div>
	</div>
	<div class="info">
		<?php
			_e('Unicode and special characters such as "*?><:/\|", can be problematic on some hosts.', 'duplicator');
            _e('<b>');
            _e('  Only consider using this filter if the package build is failing. Select files that are not important to your site or you can migrate manually.', 'duplicator');
            _e('</b>');
			$txt = __('If this environment/system and the system where it will be installed are setup to support Unicode and long paths then these filters can be ignored.  '
				. 'If you run into issues with creating or installing a package, then is recommended to filter these paths.', 'duplicator');
		?>
		<script id="hb-files-utf8" type="text/x-handlebars-template">
			<div class="container">
				<div class="hdrs">
					<span style="font-weight:bold"><?php _e('Quick Filters', 'duplicator');?></span>
						<sup><i class="fa fa-question-circle" data-tooltip-title="<?php _e("Name Checks", 'duplicator'); ?>" data-tooltip="<?php echo $txt; ?>"></i></sup>
					<div class='hdrs-up-down'>
						<i class="fa fa-caret-up fa-lg dup-nav-toggle" onclick="Duplicator.Pack.toggleAllDirPath(this, 'close')"></i>
						<i class="fa fa-caret-down fa-lg dup-nav-toggle" onclick="Duplicator.Pack.toggleAllDirPath(this, 'open')"></i>
					</div>
				</div>
				<div class="data">
					<?php _duplicatorGetRootPath();	?>
					{{#if  ARC.FilterInfo.TreeWarning}}
						{{#each ARC.FilterInfo.TreeWarning as |directory|}}
							<div class="directory">
								{{#if  directory.count}}
									<i class="fa fa-caret-right fa-lg dup-nav" onclick="Duplicator.Pack.toggleDirPath(this)"></i> &nbsp;
								{{else}}
									<i class="empty"></i>
								{{/if}}
								<input type="checkbox" name="dir_paths[]" value="{{directory.dir}}" id="nc1_dir_{{@index}}" />
								<label for="nc1_dir_{{@index}}" title="{{directory.dir}}">
									<i class="count">({{directory.count}})</i>
									/{{directory.sdir}}/
								</label> <br/>
								<div class="files">
									{{#each directory.files}}
										<div class="file" title="{{name}}">- {{sname}}</div>
									{{/each}}
								</div>
							</div>
						{{/each}}
					{{else}}
						<?php _e('No file/directory name warnings found.', 'duplicator');?>
					{{/if}}
				</div>
			</div>
			<div class="apply-btn">
				<button type="button" class="button-small" onclick="Duplicator.Pack.applyFilters(this, 'utf8')">
					<i class="fa fa-filter"></i> <?php _e('Apply Filters &amp; Rescan', 'duplicator');?>
				</button>
			</div>
		</script>
		<div id="hb-files-utf8-result" class="hb-files-style"></div>
	</div>
</div>
<br/><br/>


<!-- ============
DATABASE -->
<div id="dup-scan-db">
	<div class="scan-header scan-item-first">
		<i class="fa fa-table"></i>
		<?php _e("Database", 'duplicator');	?>
		<i class="fa fa-question-circle data-size-help"
			data-tooltip-title="<?php _e("Database Size:", 'duplicator'); ?>"
			data-tooltip="<?php _e('The database size represents only the included tables. The process for gathering the size uses the query SHOW TABLE STATUS.  '
				. 'The overall size of the database file can impact the final size of the package.', 'duplicator'); ?>"></i>
		<div id="data-db-size1"></div>
		<div class="dup-scan-filter-status">
			<?php
				if ($Package->Database->FilterOn) {
					echo '<i class="fa fa-filter"></i> '; _e('Enabled', 'duplicator');
				}
			?>
		</div>
	</div>

	<!-- ============
	DB: TOTAL SIZE -->
	<div class="scan-item">
		<div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
			<div class="text"><i class="fa fa-caret-right"></i> <?php _e('Total Size', 'duplicator');?></div>
			<div id="data-db-status-size"></div>
		</div>
		<div class="info">
			<b><?php _e('Size', 'duplicator');?>:</b> <span id="data-db-size2"></span> &nbsp; | &nbsp;
			<b><?php _e('Tables', 'duplicator');?>:</b> <span id="data-db-tablecount"></span> &nbsp; | &nbsp;
			<b><?php _e('Records', 'duplicator');?>:</b> <span id="data-db-rows"></span>
			 <br/><br/>
			<?php
				//OVERVIEW
				echo '<b>' . __('Overview:', 'duplicator') . '</b><br/>';
				printf(__('Total size and row count for all database tables are approximate values.  The thresholds that trigger warnings are %1$s OR %2$s records total for the entire database.  The larger the databases the more time it takes to process and execute.  This can cause issues with budget hosts that have cpu/memory limits, and timeout constraints.', 'duplicator'),
						DUP_Util::byteSize(DUPLICATOR_SCAN_DB_ALL_SIZE),
						number_format(DUPLICATOR_SCAN_DB_ALL_ROWS));

				//OPTIONS
				echo '<br/><br/>';
				echo '<b>' . __('Options:', 'duplicator') . '</b><br/>';
				$lnk = '<a href="maint/repair.php" target="_blank">' . __('Repair and Optimization', 'duplicator') . '</a>';
				printf(__('1. Running a %1$s on your database will help to improve the overall size, performance and efficiency of the database.', 'duplicator'), $lnk);
				echo '<br/><br/>';
				$lnk = '<a href="?page=duplicator-settings" target="_blank">' . __('Duplicator Settings', 'duplicator') . '</a>';
				printf(__('2. If your host supports shell_exec and mysqldump it is recommended to enable this option from the %1$s menu.', 'duplicator'), $lnk);
				echo '<br/><br/>';
				_e('3. Consider removing data from tables that store logging, statistical  or other non-critical information about your site.', 'duplicator');
			?>
		</div>
	</div>

	<!-- ============
	DB: TABLE DETAILS -->
	<div class="scan-item scan-item-last">
		<div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
			<div class="text"><i class="fa fa-caret-right"></i> <?php _e('Table Details', 'duplicator');?></div>
			<div id="data-db-status-details"></div>
		</div>
		<div class="info">
			<?php
				//OVERVIEW
				echo '<b>' . __('Overview:', 'duplicator') . '</b><br/>';
				printf(__('The thresholds that trigger warnings for individual tables are %1$s OR %2$s records OR tables names with upper-case characters.  The larger '
					. 'the table the more time it takes to process and execute.  This can cause issues with budget hosts that have cpu/memory limits, and timeout constraints.', 'duplicator'),
						DUP_Util::byteSize(DUPLICATOR_SCAN_DB_TBL_SIZE),
						number_format(DUPLICATOR_SCAN_DB_TBL_ROWS));

				//OPTIONS
				echo '<br/><br/>';
				echo '<b>' . __('Options:', 'duplicator') . '</b><br/>';
				$lnk = '<a href="maint/repair.php" target="_blank">' . __('Repair and Optimization', 'duplicator') . '</a>';
				printf(__('1. Run a %1$s on the table to improve the overall size and performance.', 'duplicator'), $lnk);
				echo '<br/><br/>';
				_e('2. Remove stale date from tables such as logging, statistical or other non-critical data.', 'duplicator');
				echo '<br/><br/>';
				$lnk = '<a href="http://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_lower_case_table_names" target="_blank">' . __('lower_case_table_names', 'duplicator') . '</a>';
				printf(__('3. For table name case sensitivity issues either rename the table with lower case characters or be prepared to work with the %1$s system variable setting.', 'duplicator'), $lnk);
				echo '<br/><br/>';

				echo '<b>' . __('Tables:', 'duplicator') . '</b><br/>';
			?>

			<div id="dup-scan-db-info">
				<div id="data-db-tablelist"></div>
			</div>
		</div>
	</div>
	<br/>

</div><!-- end .dup-scan-db -->


<!-- ==========================================
DETAILS DIALOG:
========================================== -->
<?php
	$alert1 = new DUP_UI_Dialog();
	$alert1->height     = 600;
	$alert1->width      = 600;
	$alert1->title		= __('Scan Details', 'duplicator');
	$alert1->message	= "<div id='dup-arc-details-dlg'></div>";
	$alert1->initAlert();
?>

<div id="dup-archive-details" style="display:none">

	<b><i class="fa fa-archive"></i> PACKAGE</b>
	<hr size="1" />

	<b><?php _e('Name', 'duplicator');?>:</b> <?php echo $_POST['package-name']; ?><br/>
	<b><?php _e('Notes', 'duplicator');?>:</b> <?php echo strlen($_POST['package-notes']) ? $_POST['package-notes'] : __('- no notes -', 'duplicator') ; ?>
	<br/><br/>

	<b><i class="fa fa-files-o"></i> FILE SETTINGS</b>
	<hr size="1" />

	<b><?php _e('Filters State', 'duplicator');?>:</b> <?php echo ($Package->Archive->FilterOn) ? __('Enabled', 'duplicator') : __('Disabled', 'duplicator') ;?> <br/>
	<div class="filter-area">
		<b>[<?php _e('Excluded Directories', 'duplicator');?>]</b><br/>
		<i class="fa fa-folder-open"></i> <?php echo rtrim(DUPLICATOR_WPROOTPATH, "//");?>
		<div class="file-info">
			<?php
				if (strlen( $Package->Archive->FilterDirs)) {
					$data =  str_replace(";", "/<br/>", $Package->Archive->FilterDirs);
					$data =  str_replace(DUPLICATOR_WPROOTPATH, '/', $data);
					echo $data;
				} else {
					_e('No directory filters have been set.', 'duplicator');
				}
			?>
		</div>

		<b>[<?php _e('Excluded File Extensions', 'duplicator');?>]</b><br/>
		<?php
			if (strlen( $Package->Archive->FilterExts)) {
				echo $Package->Archive->FilterExts;
			} else {
				_e('No file extension filters have been set.', 'duplicator');
			}
		?>
	</div>
	<br/>

	<b><i class="fa fa-table"></i> DATABASE SETTINGS</b>
	<hr size="1" />
	<table id="db-area">
		<tr><td><b><?php _e('Name:', 'duplicator');?></b></td><td><?php echo DB_NAME ;?> </td></tr>
		<tr><td><b><?php _e('Host:', 'duplicator');?></b></td><td><?php echo DB_HOST ;?> </td></tr>
		<tr>
			<td style="vertical-align: top"><b><?php _e('Build Mode:', 'duplicator');?></b></td>
			<td style="line-height:18px">
				<a href="?page=duplicator-settings" target="_blank"><?php echo $dbbuild_mode ;?></a>
				<?php if ($mysqlcompat_on) :?>
					<br/>
					<small style="font-style:italic; color:maroon">
						<i class="fa fa-exclamation-circle"></i> <?php _e('MySQL Compatibility Mode Enabled', 'duplicator'); ?>
						<a href="https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html#option_mysqldump_compatible" target="_blank">[<?php _e('details', 'duplicator'); ?>]</a>
					</small>
				<?php endif;?>
			</td>
		</tr>
	</table><br/>

	<small>
		<?php
			_e('The root directory is where Duplicator starts archiving files.  The excluded sections will be skipped during the archive process.  ', 'duplicator');
			_e('All results are stored in a json file. ', 'duplicator');
		?>
		<a href="<?php echo DUPLICATOR_SITE_URL ?>/wp-admin/admin-ajax.php?action=duplicator_package_scan" target="dup_report"><?php _e('[view json report]', 'duplicator');?></a>
	</small><br/>
</div>

<script>
jQuery(document).ready(function($)
{
	Handlebars.registerHelper('dirSize', function(path) {
		return  (path.length > 70) ? path.slice(0, 70) + '...' : path;
	});

	//Opens a dialog to show scan details
	Duplicator.Pack.showDetails = function ()
	{
		$('#dup-arc-details-dlg').html($('#dup-archive-details').html());
		<?php $alert1->showAlert(); ?>
		return;
	}

	//Toggles a directory path to show files
	Duplicator.Pack.toggleDirPath = function(item)
	{
		var $dir   = $(item).parents('div.directory');
		var $files = $dir.find('div.files');
		var $arrow = $dir.find('i.dup-nav');
		if ($files.is(":hidden")) {
			$arrow.addClass('fa-caret-down').removeClass('fa-caret-right');
			$files.show();
		} else {
			$arrow.addClass('fa-caret-right').removeClass('fa-caret-down');
			$files.hide(250);
		}
	}

	//Toggles a directory path to show files
	Duplicator.Pack.toggleAllDirPath = function(item, toggle)
	{
		var $dirs  = $(item).parents('div.container').find('div.data div.directory');
		 (toggle == 'open')
			? $.each($dirs, function() {$(this).find('div.files').show(100);})
			: $.each($dirs, function() {$(this).find('div.files').hide(100);});
	}

	Duplicator.Pack.applyFilters = function(btn, type)
	{
		var $btn = $(btn);
		$btn.html('<i class="fa fa-circle-o-notch fa-spin"></i> <?php _e('Initializing Please Wait...', 'duplicator');?>');
		$btn.attr('disabled', 'true');

		var id = (type == 'large') ? '#hb-files-large-result' : '#hb-files-utf8-result'
		var filters = [];
		$(id + " input[name='dir_paths[]']:checked").each(function (){
			filters.push($(this).val());
		});

		var data = {
			action: 'DUP_CTRL_Package_addDirectoryFilter',
			nonce: '<?php echo wp_create_nonce('DUP_CTRL_Package_addDirectoryFilter'); ?>',
			dir_paths : filters.join(";")
		};
		//console.log(filters);

		$.ajax({
			type: "POST",
			cache: false,
			url: ajaxurl,
			dataType: "json",
			timeout: 100000,
			data: data,
			complete: function() { },
			success:  function() {Duplicator.Pack.rescan();},
			error: function(data) {
				console.log(data);
				alert("<?php _e('Error applying filters.  Please go back to Step 1 to add filter manually!', 'duplicator');?>");
			}
		});
	}

	Duplicator.Pack.initArchiveFilesData = function(data)
	{
		//TOTAL SIZE
		//var sizeChecks = data.ARC.Status.Size == 'Warn' || data.ARC.Status.Big == 'Warn' ? 'Warn' : 'Good';
		$('#data-arc-status-size').html(Duplicator.Pack.setScanStatus(data.ARC.Status.Size));
		$('#data-arc-status-names').html(Duplicator.Pack.setScanStatus(data.ARC.Status.Names));
		$('#data-arc-size1').text(data.ARC.Size || errMsg);
		$('#data-arc-size2').text(data.ARC.Size || errMsg);
		$('#data-arc-files').text(data.ARC.FileCount || errMsg);
		$('#data-arc-dirs').text(data.ARC.DirCount || errMsg);

		//LARGE FILES
		var template = $('#hb-files-large').html();
		var templateScript = Handlebars.compile(template);
		var html = templateScript(data);
		$('#hb-files-large-result').html(html);

		//NAME CHECKS
		var template = $('#hb-files-utf8').html();
		var templateScript = Handlebars.compile(template);
		var html = templateScript(data);
		$('#hb-files-utf8-result').html(html);
		Duplicator.UI.loadQtip();
	}


	Duplicator.Pack.initArchiveDBData = function(data)
	{
		var errMsg = "unable to read";
		var html = "";
		var DB_TotalSize = 'Good';
		var DB_TableDetails = 'Good';
		var DB_TableRowMax  = <?php echo DUPLICATOR_SCAN_DB_TBL_ROWS; ?>;
		var DB_TableSizeMax = <?php echo DUPLICATOR_SCAN_DB_TBL_SIZE; ?>;
		if (data.DB.Status.Success)
		{
			DB_TotalSize = data.DB.Status.DB_Rows == 'Warn' || data.DB.Status.DB_Size == 'Warn' ? 'Warn' : 'Good';
			DB_TableDetails = data.DB.Status.TBL_Rows == 'Warn' || data.DB.Status.TBL_Size == 'Warn' || data.DB.Status.TBL_Case == 'Warn' ? 'Warn' : 'Good';

			$('#data-db-status-size').html(Duplicator.Pack.setScanStatus(DB_TotalSize));
			$('#data-db-status-details').html(Duplicator.Pack.setScanStatus(DB_TableDetails));
			$('#data-db-size1').text(data.DB.Size || errMsg);
			$('#data-db-size2').text(data.DB.Size || errMsg);
			$('#data-db-rows').text(data.DB.Rows || errMsg);
			$('#data-db-tablecount').text(data.DB.TableCount || errMsg);
			//Table Details
			if (data.DB.TableList == undefined || data.DB.TableList.length == 0) {
				html = '<?php _e("Unable to report on any tables", 'duplicator') ?>';
			} else {
				$.each(data.DB.TableList, function(i) {
					html += '<b>' + i  + '</b><br/>';
					$.each(data.DB.TableList[i], function(key,val) {
						html += (key == 'Case' && val == 1) || (key == 'Rows' && val > DB_TableRowMax) || (key == 'Size' && parseInt(val) > DB_TableSizeMax)
								? '<div style="color:red"><span>' + key  + ':</span>' + val + '</div>'
								: '<div><span>' + key  + ':</span>' + val + '</div>';
					});
				});
			}
			$('#data-db-tablelist').append(html);
		} else {
			html = '<?php _e("Unable to report on database stats", 'duplicator') ?>';
			$('#dup-scan-db').html(html);
		}
	}

	<?php
		if (isset($_GET['retry'])) {
			echo "$('#scan-itme-file-size').show(300)";
		}
	?>
	
});
</script>