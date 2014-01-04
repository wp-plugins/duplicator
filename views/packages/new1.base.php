<?php
	require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.php');
	
	global $wpdb;	

	DUP_Util::InitSnapshotDirectory();
	$Package = new DUP_Package();
	$Package = $Package->GetActive();

	$dup_tests  = array();
	$dup_tests  = DUP_Package::GetSystemRequirments();
	
	$view_state = DUP_UI::GetViewStateArray();
	$ui_css_archive   = (isset($view_state['dup-pack-archive-panel']) && $view_state['dup-pack-archive-panel'])   ? 'display:block' : 'display:none';
	$ui_css_installer = (isset($view_state['dup-pack-installer-panel']) && $view_state['dup-pack-installer-panel']) ? 'display:block' : 'display:none';
	$package_skip_scanner	= DUP_Settings::Get('package_skip_scanner');


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

<?php include('new1.inc-a.reqs.php'); ?>

<div style="padding:2px 5px 2px 5px">
	<?php include('new1.inc-b.form.php'); ?>
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
			$('#dup-archive-filter-file').show(800);
		} else {
			$filterItems.attr('disabled', 'disabled').css({color:'#999'});
			$('#filter-dirs, #filter-exts').attr('readonly', 'readonly').css({color:'#999'});
			$('#dup-archive-filter-file').hide(800);
		}
	};
	
	/*	----------------------------------------
	*	METHOD: Appends a path to the directory filter */ 
	Duplicator.Pack.ToggleDBFilters = function() {
		var $filterItems = $('#dup-db-filter-items');
		if($("#dbfilter-on").is(':checked')) {
			$filterItems.removeAttr('disabled').css({color:'#000'});
			$('#dup-dbtables input').removeAttr('readonly').css({color:'#000'});
			$('#dup-archive-filter-db').show(800);
		} else {
			$filterItems.attr('disabled', 'disabled').css({color:'#999'});
			$('#dup-dbtables input').attr('readonly', 'readonly').css({color:'#999'});
			$('#dup-archive-filter-db').hide(800);
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
	
	/*	----------------------------------------
	*	METHOD: Auto Skip Step 2*/ 
	Duplicator.Pack.SkipStep2 = function() {
		var $chkbox = jQuery('#dup-skip-step2');
		if ($chkbox.prop('checked') ) {
			jQuery('#dup-form-opts').attr('action', '?page=duplicator&tab=new3');
		} else {
			jQuery('#dup-form-opts').attr('action', '?page=duplicator&tab=new2');
		}
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
	<?php echo ($package_skip_scanner) ? 'Duplicator.Pack.SkipStep2();' : ''; ?>
	
});
</script>

