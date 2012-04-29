
/**
*  METHOD: Duplicator.setStatus  
*  Sets the status of the Duplicator status bar
*  @param msg	The message to display
*  @param img	The image to display for no image don't set
*/
Duplicator.setStatus = function(msg, img) {
	//Clean Status Bar
	$("#img-status-error").hide();
	$("#img-status-progress").hide();
	
	$('#span-status').html(msg);
	switch (img) {
		case 'error' 	: $("#img-status-error").show('slow'); break;
		case 'progress' : $("#img-status-progress").show('slow'); break;
	}
}

/**
*  METHOD: Duplicator.toggleToolbarState  
*  Disables or enables the toolbar
*  @param state		Disabled/Enabled
*/ 
Duplicator.toggleToolbarState = function(state) {
	if (state == "DISABLED") {
		$('#toolbar-table :input, #duplicator-installer').attr("disabled", "true");
		$('#toolbar-table :input, #duplicator-installer').css("background-color", "#efefef");
	} else {
		$('#toolbar-table :input, #duplicator-installer').removeAttr("disabled");
		$('#toolbar-table :input, #duplicator-installer').css("background-color", "#f9f9f9");
	}
}

/**
*  METHOD: Duplicator.reload  
*  Performs reloading the page and diagnotic handleing
*/
Duplicator.reload = function(data) {
	if ($('select#log_level').val() >= 1) {
		Duplicator.Log(data);
		Duplicator.Log("Logging Complete. (reload window to view results)<br/>\n", true); 
		<?php 
			$msg = sprintf("%s - <a href='javascript:window.location.reload()'>%s</a> %s." ,
					__("Logging Enabled", 'WPDuplicator'),
					__("reload page", 'WPDuplicator'),
					__("to view results", 'WPDuplicator')); 
		?>
		Duplicator.setStatus("<?php echo $msg ?>");
		Duplicator.toggleToolbarState("ENABLED");
	} else {
		Duplicator.toggleToolbarState("ENABLED");
		window.location.reload();
	}
}

/**
*  METHOD: Duplicator.createPackage  
*  Performs Ajax post to create a new package
*  Timeout (10000000 = 166 minutes)
*/
Duplicator.createPackage = function(packname) {
	Duplicator.toggleToolbarState("DISABLED");
	$.ajax({
		type: "POST",
		url: ajaxurl,
		timeout: 10000000,
		data: "package_name=" + packname +"&action=duplicator_create",
		beforeSend: function() { Duplicator.Log("<div id='duplicator-process-msg'>Processing please wait.  This may take several minutes...</div>");},
		complete:   function() { jQuery('#duplicator-process-msg').empty()},
		success:    function(data) { Duplicator.reload(data); },
		error:      function(data) { 
			Duplicator.Log(data.responseText); 
			Duplicator.toggleToolbarState("ENABLED");
			alert("<?php _e('An error occurred while creating a package!  Please try again.  If the problem persits please submit the logs in a support ticket.', 'WPDuplicator') ?>"); 
		}
	});
}

/**
 *  METHOD: Duplicator.overwrite 
 *  Performs Ajax post to overwrite an existing package
 */
Duplicator.overwrite = function(packname) {
	$.ajax({
		type: "POST",
		url: ajaxurl,
		data: "duplicator_new="+packname+"&action=duplicator_overwrite",
		success: function(data) {
			Duplicator.Log(data);
			Duplicator.createPackage(packname);
		},
		error: 	function(data) { 
				Duplicator.Log(data.responseText); 
				alert("<?php _e('An error occurred while overwriting a package!  Please try again.  If the problem persits please submit the logs in a support ticket.', 'WPDuplicator') ?>")
		}
	});
}

/**
 *  METHOD: Save Settings
 *  Saves the Settings
 */
Duplicator.saveSettings = function(reload) {
	var q;
	var reload 	   = (reload === false) ? false : true;
	var email_me   = $('#email-me').is(':checked') ? 1 : 0;
	var dbiconv    = $('#dbiconv').is(':checked')  ? 1 : 0;
	var log_level  = $("select#log_level").val() ? $("select#log_level").val() : 0;
	var dir_bypass = $("textarea#dir_bypass").val();
	
	//append semicolon if user forgot
	if (dir_bypass.length > 1) {
		var has_semicolon	= dir_bypass.charAt(dir_bypass.length - 1) == ";";
		var dir_bypass		= (has_semicolon) ? dir_bypass : dir_bypass + ";";
		$("textarea#dir_bypass").val(dir_bypass);
	}

	q  = "dbhost=" 		 +  $("input#dbhost").val() + "&";
	q += "dbname=" 		 +  $("input#dbname").val() + "&";
	q += "dbuser=" 		 +  $("input#dbuser").val() + "&";
	q += "nurl="   		 +  $("input#nurl").val() + "&";
	q += "dbiconv="   	 +  dbiconv    + "&";
	q += "email-me="   	 +  email_me   + "&";
	q += "max_time="   	 +  $("input#max_time").val() + "&";
	q += "max_memory="   +  $("input#max_memory").val() + "&";
	q += "dir_bypass="   +  $("textarea#dir_bypass").val() + "&";
	q += "log_level="    +  log_level + "&";
	q += "log_paneheight=" +  $("input#log_paneheight").val() + "&";
	if (! reload) {
		Duplicator.Log("sending params:" + q + "<br/>");
	}
	Duplicator.logSizePane();
	$.ajax({
		type: "POST",
		url: ajaxurl,
		data: q + "action=duplicator_settings",
		success: function(data) { 
			if (reload) {
				$('#opts-save-btn').val("<?php _e('Saving', 'WPDuplicator') ?>...");
				window.location.reload();
			}
		},
		error: function(data) { 
			Duplicator.Log(data.responseText); 
			alert("<?php _e('An error occurred while saving your settings!  Please try again.  If the problem persits please submit the logs in a support ticket.', 'WPDuplicator') ?>")
		}
	});
 }


/**
 *  ATTACHED EVENT: Delete PackageSet
 *  Removes all selected package sets
 */
$("#btn-delete-pack").click(function (event) {
	var arr = new Array;
	var count = 0;
	$("input[name=delete_confirm]").each(function() {
		 if (this.checked) { arr[count++] = this.id; }
	});
	var list = arr.join(',');
	if (list.length == 0) {
		alert("<?php _e('Please select at least one package to delete.', 'WPDuplicator') ?>");
		return;
	}
	
	var answer = confirm("<?php _e('Are you sure, you want to delete the selected package(s)?', 'WPDuplicator') ?>");
	if (answer){
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: "duplicator_delid="+list+"&action=duplicator_delete",
			success: function(data) { 
				if (data.indexOf("log:act__unlink=>removed") != -1) {
					Duplicator.reload(data);
				} else {
					var msg = data + "\nlog:ajax.event-delete=>Error while deleting backup. File may not exsist.\n<br/>";
					Duplicator.reload(msg);
				}
			},
			error: function(data) { 
				Duplicator.Log(data.responseText); 
				alert("<?php _e('An error occurred while deleting a package!  Please try again.  If the problem persits please submit the logs in a support ticket.', 'WPDuplicator') ?>")
			}
		});
	} else {
		Duplicator.setStatus("<?php _e('Ready to create new package.', 'WPDuplicator') ?>");
	}
	event.preventDefault(); 
});

/**
 *  ATTACHED EVENT: Submit Main Form
 *  Process Package and Installer
 */
$("#form-duplicator").submit(function (event) {
	event.preventDefault();   
	
	//Validate length test
	if ($("input[name=package_name]").val().length <= 0) 	{
		Duplicator.setStatus("<?php _e('Please enter a backup name.', 'WPDuplicator') ?>", "error");
		return;
	}
	//Vatlidate alphanumeric test:  TODO fix multiple dash checks -, –, or — 
	var newstring = $("input[name=package_name]").val().replace(/ /g, "");
	$("input[name=package_name]").val(newstring)
	if ( ! /^[0-9A-Za-z|_]+$/.test($("input[name=package_name]").val())) {
		Duplicator.setStatus("<?php _e('Alpanumeric characters only on package name', 'WPDuplicator') ?>", "error");
		return;
	}
	
	var packname = $("input[name=package_name]").val();
	
	$.ajax({
		type: "POST",
		url: ajaxurl,
		timeout: 10000000,
		data: "duplicator_new="+ packname +"&action=duplicator_system_check",
		success: function(data) {
			Duplicator.Log(data);
			
			var status_msg;
			var validate_msg;
			var size_msg;
			
			var dir_size    = $('input.dir-size:last').val();
			var details_msg = "<?php _e('Uncompressed Size', 'WPDuplicator') ?>:  " + dir_size + "\n<?php _e('Name', 'WPDuplicator') ?>:  " + packname  ;
			status_msg  =  "<?php _e('This may take several minutes', 'WPDuplicator') ?>. ";
			status_msg  += "<?php _e('Compressing', 'WPDuplicator') ?> " + dir_size + ".";

			//INVALID FILES FOUND		
			if (data.indexOf("log:act__system_check=>reserved-file") != -1)	{
				
				validate_msg     = "<?php _e('WARNING', 'WPDuplicator') ?>:\n";
				validate_msg    += "<?php _e('A reserved file was found in the WordPress root directory.', 'WPDuplicator') ?>\n";
				validate_msg 	+= "<?php _e('The Duplicator uses the following reserved file names', 'WPDuplicator') ?>:\n";
				validate_msg 	+= "<?php _e('install.php, install-data.sql, and install-log.txt', 'WPDuplicator') ?>\n\n";
				validate_msg 	+= "<?php _e('In order to archive your data correctly please remove any of', 'WPDuplicator') ?>\n";
				validate_msg 	+= "<?php _e('these reserved files from your WordPress root directory.', 'WPDuplicator') ?>\n";
				validate_msg 	+= "<?php _e('Then try creating a your package again.', 'WPDuplicator') ?>";
				alert(validate_msg);
				Duplicator.setStatus("<?php _e('Ready to create new package.', 'WPDuplicator') ?>");
				return false;
				
			//OVERWRITE	MESSAGE			
			} else if (data.indexOf("log:act__system_check=>overwrite") != -1)	{
	
				validate_msg = "<?php _e('Package name already exists! Overwrite with newer package?', 'WPDuplicator') ?>\n\n" + details_msg;
			
				if (confirm(validate_msg)) {
					Duplicator.setStatus(status_msg, 'progress');
					Duplicator.overwrite(packname);
				} else {
					Duplicator.Log("Action canceled, logging complete.<br/>\n", true);
					Duplicator.setStatus("<?php _e('Ready to create new package.', 'WPDuplicator') ?>");
				}
				
			//SIZE LIMIT
			} else if (data.indexOf("log:act__system_check=>size_limit") != -1) {
				
				size_msg     =  "<?php _e('2GB archive size reached!  Currently the Duplicator only', 'WPDuplicator') ?>\n";
				size_msg     += "<?php _e('supports achieving sites under 2GB.  We are currently', 'WPDuplicator') ?>\n";
				size_msg     += "<?php _e('working on this limitation in future releases', 'WPDuplicator') ?>.\n\n";
				size_msg     += "<?php _e('You can easily overcome this limitation by temporarily', 'WPDuplicator') ?>\n";
				size_msg     += "<?php _e('moving large files outside of your root WordPress directory', 'WPDuplicator') ?>.\n";
				size_msg     += "<?php _e('Once you have created your package and installed it you can', 'WPDuplicator') ?>\n";
				size_msg     += "<?php _e('then move those files back to the same location', 'WPDuplicator') ?>.";
				alert(size_msg);
				Duplicator.setStatus("<?php _e('2GB Size limit reached', 'WPDuplicator') ?>!");
				return false;
				
			//CREATE PACKAGE
			} else {
				validate_msg = "<?php _e('Create this new package?', 'WPDuplicator') ?>\n\n"  + details_msg;
				if (confirm(validate_msg)) {
					Duplicator.setStatus(status_msg, 'progress');
					Duplicator.createPackage($("input[name=package_name]").val(), $("#email-me").is(':checked'));
				} else {
					Duplicator.Log("Action canceled, logging complete.<br/>\n", true);
					Duplicator.setStatus("<?php _e('Ready to create new package.', 'WPDuplicator') ?>");
				}
			}
		},
		error: function(data) { 
			Duplicator.Log(data.responseText); 
			alert("<?php _e('An error occurred while submitting a package!  Please try again.  If the problem persits please submit the logs in a support ticket.', 'WPDuplicator') ?>")
		}
	});

});

