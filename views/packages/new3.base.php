<?php

	require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.php');
	
	$Package = new DUP_Package();
	if (isset($_POST['dup-skip-step2'])) {
		$Package->SaveActive($_POST);
	}
	$Package = $Package->GetActive();

?>

<style>
	div#dup-progress-area {text-align:center; max-width:800px; min-height:200px;  border:1px solid silver; border-radius:5px; margin:25px auto 10px auto; padding:0px; box-shadow: 0 8px 6px -6px #999;}
	div#dup-progress-area div.inner {padding:10px; line-height:22px}
	div#dup-progress-area h2.title {background-color:#efefef; margin:0px}
	div#dup-progress-area span.label {font-weight:bold}
	div#dup-msg-success {color:#18592A; padding:5px;}
	div#dup-msg-success fieldset, 
	div#dup-msg-error fieldset {text-align:left; width:95%; border:1px solid #dfdfdf; border-radius:5px; padding:5px 15px 5px 15px}
	div.dup-msg-success-stats{color:#999;margin:10px 0px 0px 0px}
	div.dup-msg-success-links {margin:20px 5px 5px 5px}
	div#dup-msg-error {color:#A62426; padding:5px;}
	div#dup-progress-area div.inner div.done-title {font-size:22px; font-weight:bold; margin:0px 0px 10px 0px}
	div#dup-logs {text-align:center; margin:auto; padding:5px; width:350px;}
	div#dup-logs a {font-size:15px; text-decoration:none !important; display:inline-block; margin:20px 0px 5px 0px}
	div.dup-button-footer {text-align:right; margin:20px 10px 0px 0px}
	button.button {font-size:16px !important; height:30px !important; font-weight:bold; padding:0px 10px 5px 10px !important; min-width: 150px }
	span.dup-btn-size {font-size:11px;font-weight: normal}
</style>

<!-- =========================================
WIZARD STEP TABS -->
<div id="dup-wiz">
	<div id="dup-wiz-steps">
		<div class="completed-step"><a><span>1</span> <?php _e('Setup', 'wpduplicator'); ?></a></div>
		<div class="completed-step"><a><span>2</span> <?php _e('Scan', 'wpduplicator'); ?> </a></div>
		<div class="active-step"><a><span>3</span> <?php _e('Build', 'wpduplicator'); ?> </a></div>
	</div>
	<div id="dup-wiz-title">
		<?php _e('Step 3: Build Package', 'wpduplicator'); ?>
	</div> <hr/>
</div>

<form id="form-duplicator" method="post" action="?page=duplicator">

	<!--  PROGRESS BAR -->
	<div id="dup-progress-bar-area">
		<h2><i class="fa fa-cog fa-spin"></i> <?php _e('Building Package', 'wpduplicator'); ?></h2>
		<div id="dup-progress-bar"></div>
		<b><?php _e('Please Wait...', 'wpduplicator'); ?></b><br/>
		<i><?php _e('This may take several minutes', 'wpduplicator'); ?></i>
	</div>
	
	<div id="dup-progress-area" class="dup-panel" style="display:none">
		<div class="dup-panel-title dup-box-title-fancy"><b style="font-size:18px"><?php _e('Build Status', 'wpduplicator'); ?></b></div>
		<div class="dup-panel-panel">

			<!--  SUCCESS MESSAGE -->
			<div id="dup-msg-success" style="display:none">
				<div class="dup-hdr-success">
					<i class="fa fa-check-square-o fa-lg"></i> <?php _e('Package Completed', 'wpduplicator'); ?>
				</div>
				
				<div class="dup-msg-success-stats">
					<b><?php _e('Secure Name', 'wpduplicator'); ?>:</b> <span id="data-name-hash"></span><br/>
					<b><?php _e('Process Time', 'wpduplicator'); ?>:</b> <span id="data-time"></span><br/>
				</div><br/>
				
				<button id="dup-btn-installer" class="button button-primary button-large">
					<i class="fa fa-bolt"></i> <?php _e("Installer", 'wpduplicator') ?>
					<span id="dup-btn-installer-size" class="dup-btn-size"></span>
				</button> &nbsp;
				<button id="dup-btn-archive" class="button button-primary button-large">
					<i class="fa fa-bars"></i> <?php _e("Archive", 'wpduplicator') ?>
					<span id="dup-btn-archive-size" class="dup-btn-size"></span>
				</button><br/><br/>

	
				<div class="dup-msg-success-links">
					<?php printf("<a href='?page=duplicator'>[ %s ]</a>", 	__('All Packages', 'wpduplicator'));?> 
					<?php printf("<a href='?page=duplicator&tab=new1'>[ %s ]</a>", 	__('Create New', 'wpduplicator'));?> 
				</div><br/>
				
				<!-- Used for iMacros testing do not remove -->
				<div id="dup-automation-imacros"></div>
			</div>
			
			<!--  ERROR MESSAGE -->
			<div id="dup-msg-error" style="display:none">
				<div class="done-title"><i class="fa fa-exclamation-circle"></i> <?php _e('Build Error', 'wpduplicator'); ?></div>
				<i><?php _e('Please try again!', 'wpduplicator'); ?></i><br/>
				<fieldset>
					<legend><b><?php _e('Error Details', 'wpduplicator'); ?></b></legend>
					<div id="dup-msg-error-response-status">
						<span class="label"><?php _e("Server Status:", 'wpduplicator'); ?></span>
						<span class="data"></span>
					</div>
					<div id="dup-msg-error-response-text">
						<span class="label"><?php _e("Error Message:", 'wpduplicator'); ?></span>
						<span class="data"></span>
					</div>
				</fieldset>
			</div>
			
		</div>
	</div>
	
	<!-- LOGS -->
	<div id="dup-logs">
		<div>
			<b><?php printf(" &nbsp; <a href='javascript:void(0)' onclick='Duplicator.OpenLogWindow()'>[ <i class='fa fa-pencil-square-o'></i>  %s ]</a>", 	__('Preview Log', 'wpduplicator'));?> </b><br/>
			<i><?php _e('Some systems that are not multi-threaded will have to wait for the process to complete before reviewing the logs.', 'wpduplicator'); ?></i><br/>
		</div>
	</div>
	
</form>

<script type="text/javascript">
jQuery(document).ready(function($) {

	/*	----------------------------------------
	*	METHOD: Performs Ajax post to create a new package
	*	Timeout (10000000 = 166 minutes)  */
	Duplicator.Pack.Create = function() {
		
		var data = {action : 'duplicator_package_create'}

		$.ajax({
			type: "POST",
			url: ajaxurl,
			dataType: "json",
			timeout: 10000000,
			data: data,
			beforeSend: function() { },
			complete:   function() { },
			success:    function(data) { 
				$('#dup-progress-bar-area').hide(); 
				$('#dup-progress-area, #dup-msg-success').show(300);
				
				var Pack = data.Package;
				var InstallURL = Pack.StoreURL + Pack.Installer.File + "?get=1&file=" + Pack.Installer.File;
				var ArchiveURL = Pack.StoreURL + Pack.Archive.File   + "?get=1";
				
				//$('#dup-btn-installer-size').append('&nbsp; (' + data.ExeSize + ')')
				$('#dup-btn-archive-size').append('&nbsp; (' + data.ZipSize + ')')
				$('#data-name-hash').text(Pack.NameHash || 'error read');
				$('#data-time').text(data.Runtime || 'unable to read time');
				
				//Wire Up Downloads
				$('#dup-btn-installer').on("click", {name: InstallURL }, Duplicator.Pack.DownloadFile  );
				$('#dup-btn-archive').on("click",   {name: ArchiveURL }, Duplicator.Pack.DownloadFile  );
				//Imacros testing required
				$('#dup-automation-imacros').html('<input type="hidden" id="dup-finished" value="done" />');
					
			},
			error: function(data) { 
				$('#dup-progress-bar-area').hide(); 
				var status = data.status + ' -' + data.statusText;
				$('#dup-msg-error-response-status span.data').html(status)
				$('#dup-msg-error-response-text span.data').html(data.responseText);
				$('#dup-msg-error').show(200);
				console.log(data);
			}
		});
	}
		
	//Page Init:
	Duplicator.UI.AnimateProgressBar('dup-progress-bar');
	Duplicator.Pack.Create();

});
</script>