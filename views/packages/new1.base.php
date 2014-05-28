<?php
	require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.php');
	
	global $wpdb;	
	
	//POST BACK
	$action_updated = null;
	if (isset($_POST['action'])) {
		$action_result = DUP_Settings::DeleteWPOption($_POST['action']);
		switch ($_POST['action']) {
			case 'duplicator_package_active' : 	$action_response = __('Package settings have been reset.', 'wpduplicator'); break;		
		}
	} 
	
	
	DUP_Util::InitSnapshotDirectory();

	$Package = DUP_Package::GetActive();
	$package_hash = $Package->MakeHash();

	$dup_tests  = array();
	$dup_tests  = DUP_Server::GetRequirments();
	$default_name = DUP_Package::GetDefaultName();
	
	$view_state = DUP_UI::GetViewStateArray();
	$ui_css_archive   = (isset($view_state['dup-pack-archive-panel']) && $view_state['dup-pack-archive-panel'])   ? 'display:block' : 'display:none';
	$ui_css_installer = (isset($view_state['dup-pack-installer-panel']) && $view_state['dup-pack-installer-panel']) ? 'display:block' : 'display:none';

?>

<style>
	/* -----------------------------
	REQUIRMENTS*/
	div.dup-sys-section {margin:1px 0px 5px 0px}
	div.dup-sys-title {display:inline-block; width:250px; padding:1px; }
	div.dup-sys-title div {display:inline-block;float:right; }
	div.dup-sys-info {display:none; max-width: 800px}	
	div.dup-sys-pass {display:inline-block; color:green;}
	div.dup-sys-fail {display:inline-block; color:#AF0000;}
	div.dup-sys-contact {padding:5px 0px 0px 10px; font-size:11px; font-style:italic}
	span.dup-toggle {float:left; margin:0 2px 2px 0; }

	/* -----------------------------
	PACKAGE OPTS*/
	form#dup-form-opts label {line-height:22px}
	form#dup-form-opts input[type=checkbox] {margin-top:3px}
	form#dup-form-opts fieldset {border-radius:4px; border:1px solid #ccc;  line-height:20px}
	form#dup-form-opts fieldset{padding:10px 15px 15px 15px; min-height:275px}
	form#dup-form-opts textarea, input[type="text"] {width:100%}
	form#dup-form-opts textarea#filter-dirs {height:85px}
	form#dup-form-opts textarea#filter-exts {height:27px}
	textarea#package_notes {height:37px;}
	
	/*ARCHIVE SECTION*/
	form#dup-form-opts div.tabs-panel{max-height:550px; padding:10px; min-height:280px}
	form#dup-form-opts ul li.tabs{font-weight:bold}
	ul.category-tabs li {padding:4px 15px 4px 15px}
	select#archive-format {min-width:100px; margin:1px 0px 4px 0px}
	span#dup-archive-filter-file {color:#A62426; display:none}
	span#dup-archive-filter-db {color:#A62426; display:none}
	div#dup-file-filter-items, div#dup-db-filter-items {padding:5px 0px 0px 0px}
	label.dup-enable-filters {display:inline-block; margin:-5px 0px 5px 0px}
	div.dup-quick-links {font-size:11px; float:right; display:inline-block; margin-top:2px; font-style:italic}
	div.dup-tabs-opts-help {font-style:italic; font-size:11px; margin:10px 0px 0px 10px; color:#777}
	table#dup-dbtables td {padding:1px 15px 1px 4px}
	
	/*INSTALLER SECTION*/
	div.dup-installer-header-1 {font-weight:bold; padding-bottom:2px; width:100%}
	div.dup-installer-header-2 {font-weight:bold; border-bottom:1px solid #dfdfdf; padding-bottom:2px; width:100%}
	label.chk-labels {display:inline-block; margin-top:1px}
	table.dup-installer-tbl {width:95%; margin-left:20px}
</style>

<!-- =========================================
WIZARD STEP TABS -->			
<div id="dup-wiz">
	<div id="dup-wiz-steps">
		<div class="active-step"><a><span>1</span> <?php _e('Setup', 'wpduplicator'); ?></a></div>
		<div><a><span>2</span> <?php _e('Scan', 'wpduplicator'); ?> </a></div>
		<div><a><span>3</span> <?php _e('Build', 'wpduplicator'); ?> </a></div>
	</div>
	<div id="dup-wiz-title">
		<?php _e('Step 1: Package Setup', 'wpduplicator'); ?>
	</div> <hr/>
</div>	

<?php if (! empty($action_response))  :	?>
	<div id="message" class="updated below-h2"><p><?php echo $action_response; ?></p></div>
<?php endif; ?>	

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
					
					printf("<b>%s:</b> [%s] <br/><br/>", __('PHP Script Owner', 'wpduplicator'), DUP_Util::GetCurrentUser());
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
					_e("Safe Mode should be set safe_mode=Off in you php.ini file. On hosted servers you may have to request this setting be turned off. Please note that Safe Mode is deprecated as of PHP 5.3.0" , 'wpduplicator'); 
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
					_e("MySQL version 5.0+ or better is required. If the MySQL version is valid and this requirement fails then the mysqli extension (note the trailing 'i') is not installed.  Contact your server administrator and request that mysqli extension and MySQL Server 5.0+ be installed. Please note in future version support for other databases and extensions will be added.", 'wpduplicator');
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
					_e("PHP versions 5.2.17+ or higher is required. Please note that in versioning logic a value such as 5.2.9 is less than 5.2.17.  Please contact your server administrator to upgrade to a stable and secure version of PHP", 'wpduplicator');
				?>
				<small><?php _e('Status Code', 'wpduplicator');?>: SYS-105</small>
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


<!-- =========================================
FORM PACKAGE OPTIONS -->
<div style="padding:2px 5px 2px 5px">
	<?php include('new1.inc.form.php'); ?>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
		
	/*	----------------------------------------
	* METHOD: Toggle Options tabs*/ 
	Duplicator.Pack.ToggleOptTabs = function(tab, label) {
		$('.category-tabs li').removeClass('tabs');
		$(label).parent().addClass('tabs');
		if (tab == 1) {
			$('#dup-pack-opts-tabs-panel-1').show();
			$('#dup-pack-opts-tabs-panel-2').hide();
		} else {
			$('#dup-pack-opts-tabs-panel-2').show();
			$('#dup-pack-opts-tabs-panel-1').hide();
		}
	}
	
	/*	----------------------------------------
	* METHOD: */ 
	Duplicator.Pack.ToggleFileFilters = function() {
		var $filterItems = $('#dup-file-filter-items');
		if($("#filter-on").is(':checked')) {
			$filterItems.removeAttr('disabled').css({color:'#000'});
			$('#filter-exts,#filter-dirs').removeAttr('readonly').css({color:'#000'});
			$('#dup-archive-filter-file').show();
		} else {
			$filterItems.attr('disabled', 'disabled').css({color:'#999'});
			$('#filter-dirs, #filter-exts').attr('readonly', 'readonly').css({color:'#999'});
			$('#dup-archive-filter-file').hide();
		}
	};
	
	/*	----------------------------------------
	*	METHOD: Appends a path to the directory filter */ 
	Duplicator.Pack.ToggleDBFilters = function() {
		var $filterItems = $('#dup-db-filter-items');
		
		if($("#dbfilter-on").is(':checked')) {
			$filterItems.removeAttr('disabled').css({color:'#000'});
			$('#dup-dbtables input').removeAttr('readonly').css({color:'#000'});
			$('#dup-archive-filter-db').show();
		} else {
			$filterItems.attr('disabled', 'disabled').css({color:'#999'});
			$('#dup-dbtables input').attr('readonly', 'readonly').css({color:'#999'});
			$('#dup-archive-filter-db').hide();
		}
	};

	
	/*	----------------------------------------
	*	METHOD: Appends a path to the directory filter  */ 
	Duplicator.Pack.AddExcludePath = function(path) {
		var text = $("#filter-dirs").val() + path + ';';
		$("#filter-dirs").val(text);
	};
	
	/*	----------------------------------------
	*	METHOD: Appends a path to the extention filter  */ 
	Duplicator.Pack.AddExcludeExts = function(path) {
		var text = $("#filter-exts").val() + path + ';';
		$("#filter-exts").val(text);
	};	
	

	//Init: Toogle for system requirment detial links
	$('.dup-sys-title a').each(function() {
		$(this).attr('href', 'javascript:void(0)');
		$(this).click({selector : '.dup-sys-info'}, Duplicator.Pack.ToggleSystemDetails);
		$(this).prepend("<span class='ui-icon ui-icon-triangle-1-e dup-toggle' />");
	});
	
	//Init: Color code Pass/Fail/Warn items
	$('.dup-sys-title div').each(function() {
		$(this).addClass( ( $(this).text() == 'Pass') ? 'dup-sys-pass' : 'dup-sys-fail');
	});
	
	//Init: Toggle OptionTabs
	Duplicator.Pack.ToggleFileFilters();
	Duplicator.Pack.ToggleDBFilters();
	
});
</script>

