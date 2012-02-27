jQuery(document).ready( function($) {

	/**
	*  METHOD: Duplicator.setStatus  
	*  Sets the status of the Duplicator status bar
	*  @param msg	The message to display
	*  @param img	The image to display for no image don't set
	*
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
	*
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
			Duplicator.setStatus("Logging Enabled - &gt; <a href='javascript:window.location.reload()'>reload page</a> to view results.");
			Duplicator.toggleToolbarState("ENABLED");
		} else {
			Duplicator.toggleToolbarState("ENABLED");
			window.location.reload();
		}
	}
	
	/**
	*  METHOD: Duplicator.createPackage  
	*  Performs Ajax post to create a new package
	*/
	Duplicator.createPackage = function(packname) {
		Duplicator.toggleToolbarState("DISABLED");
		$.ajax({
			type: "POST",
			url: ajaxurl,
			beforeSend: function() { Duplicator.Log("<div id='duplicator-process-msg'>Processing please wait. &nbsp; This may take several minutes...</div>");},
			complete: function() { jQuery('#duplicator-process-msg').empty()},
			data: "package_name=" + packname +"&action=duplicator_create",
			success: function(data){Duplicator.reload(data);},
			error:   function(data){ 
				Duplicator.Log(data.responseText); 
				Duplicator.toggleToolbarState("ENABLED");
				alert('An error occurred while creating a package!  Please try again.  If the problem persits please submit a support ticket.'); 
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
			success: function(data){
				Duplicator.Log(data);
				Duplicator.createPackage(packname);
			},
			error:   function(data){ alert('An error occurred while overwriting a package!  Please try again.  If the problem persits please submit a support ticket.')}
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
		var dbiconv    = $('#dbiconv').is(':checked') ? 1 : 0;
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
		q += "log_level="  +  log_level + "&";
		q += "log_paneheight=" +  $("input#log_paneheight").val() + "&";
		if (! reload) {
			Duplicator.Log("sending params:" + q + "<br/>");
		}
		Duplicator.logSizePane();
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: q + "action=duplicator_settings",
			success: function(data){ 
				if (reload) {
					$('#opts-save-btn').val('Saving...');
					window.location.reload();
				}
			},
			error:   function(data){ alert('An error occurred while saving your settings!  Please try again.  If the problem persits please submit a support ticket.')}
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
			alert('Please select at least one package to delete.');
			return;
		}
		
		var answer = confirm("Are you sure, you want to delete the selected package(s)?");
		if (answer){
			$.ajax({
				type: "POST",
				url: ajaxurl,
				data: "duplicator_delid="+list+"&action=duplicator_delete",
				success: function(data){ 
					if (data.indexOf("log:act__unlink=>removed") != -1) {
						Duplicator.reload(data);
					} else {
						var msg = data + "\nlog:ajax.event-delete=>Error while deleting backup. File may not exsist.\n<br/>";
						Duplicator.reload(msg);
					}
				},
				error:   function(data){ alert('An error occurred while deleting a package!  Please try again.  If the problem persits please submit a support ticket.')}
			});
		} else {
			Duplicator.setStatus("Ready to create new package.");
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
			Duplicator.setStatus("Please enter a backup name.", "error");
			return;
		}
		//Vatlidate alphanumeric test:  TODO fix multiple dash checks -, –, or — 
		var newstring = $("input[name=package_name]").val().replace(/ /g, "");
		$("input[name=package_name]").val(newstring)
		if ( ! /^[0-9A-Za-z|_]+$/.test($("input[name=package_name]").val())) {
			Duplicator.setStatus("Alpanumeric characters only on package name", "error");
			return;
		}
		
		var packname = $("input[name=package_name]").val();
		
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: "duplicator_new="+ packname +"&action=duplicator_system_check",
			success: function(data) {
				Duplicator.Log(data);
				var size = $('input.dir-size:last').val();
				var msgDetails = "Uncompressed Size:  " + size + "\nName:  " + packname  ;

				//Invalid file		
				if (data.indexOf("log:act__system_check=>reserved-file") != -1)	{
					var validate_msg = "WARNING:\nA reserved file was found in the WordPress root directory.\n";
					validate_msg 	+= "The Duplicator uses the following reserved file names:\n\n";
					validate_msg 	+= "install.php, install-data.sql, and install-log.txt\n\n";
					validate_msg 	+= "In order to archive your data correctly please remove any of\n";
					validate_msg 	+= "these reserved files from your WordPress root directory.\n";
					validate_msg 	+= "Then try creating a your package again."
					alert(validate_msg);
					return false;
				//Overwrite	Message			
				} else if (data.indexOf("log:act__system_check=>overwrite") != -1)	{
					var validate_msg = "Package name already exists! Overwrite with newer package?\n\n" + msgDetails;

					if (confirm(validate_msg)) {
						Duplicator.setStatus("This may take several minutes. (compressing "  + size + ")", 'progress');
						Duplicator.overwrite(packname);
					} else {
						Duplicator.Log("Action canceled, logging complete.<br/>\n", true);
						Duplicator.setStatus("Ready to create new package.");
					}
				//Size Limit
				} else if (data.indexOf("log:act__system_check=>size_limit") != -1) {
					alert('2GB archive size reached!  Currently the Duplicator only\nsupports achieving sites under 2GB.  We are currently\nworking on this limitation in future releases.\n\nYou can easily overcome this limitation by temporarily\nmoving large files outside of your root WordPress directory.\nOnce you have created your package and installed it you can\nthen move those files back to the same location.');
					Duplicator.setStatus("2GB Size limit reached!");
					return false
				//Create
				} else {
					var validate_msg = "Create this new package?\n\n"  + msgDetails;
					if (confirm(validate_msg)) {
						Duplicator.setStatus("This may take several minutes. (compressing "  + size + ")", 'progress');
						Duplicator.createPackage($("input[name=package_name]").val(), $("#email-me").is(':checked'));
					} else {
						Duplicator.Log("Action canceled, logging complete.<br/>\n", true);
						Duplicator.setStatus("Ready to create new package.");
					}
				}
			},
			error:   function(data){ alert('An error occurred while submitting a package!  Please try again.  If the problem persits please submit a support ticket.')}
		});

	});
});





