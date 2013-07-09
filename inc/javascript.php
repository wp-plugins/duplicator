<script type="text/javascript">
/* DESCRIPTION: Methods and Objects in this file are global and common in 
 * nature use this file to place all shared methods and varibles */	

//UNIQUE NAMESPACE
Duplicator      = new Object();
Duplicator.Pack = new Object();
Duplicator.UI	= new Object();

//GLOBAL CONSTANTS
Duplicator.DEBUG_AJAX_RESPONSE = false;
Duplicator.AJAX_TIMER = null;


/* ============================================================================
*  BASE NAMESPACE: All methods at the top of the Duplicator Namespace  
*  ============================================================================	*/

/*	----------------------------------------
*	METHOD: Starts a timer for Ajax calls */ 
Duplicator.StartAjaxTimer = function() {
	Duplicator.AJAX_TIMER = new Date();
};

/*	----------------------------------------
*	METHOD: Ends a timer for Ajax calls */ 
Duplicator.EndAjaxTimer = function() {
	var endTime = new Date();
	Duplicator.AJAX_TIMER =  (endTime.getTime()  - Duplicator.AJAX_TIMER) /1000;
};

/*	----------------------------------------
*	METHOD: Reloads the current window
*	@param data		An xhr object  */ 
Duplicator.ReloadWindow = function(data) {
	if (Duplicator.DEBUG_AJAX_RESPONSE) {
		Duplicator.Pack.ShowError('debug on', data);
	} else {
		Duplicator.Pack.SetToolbar("ENABLED");
		window.location.reload(true);
	}
};

//Basic Util Methods here:
Duplicator.OpenLogWindow = function(log) {
	var logFile = log || null;
	if (logFile == null) {
		window.open('<?php echo DUPLICATOR_PLUGIN_URL .'files/log-view.php'; ?>', 'duplicator_logs');
	} else {
		window.open('<?php echo DUPLICATOR_PLUGIN_URL .'files/log-view.php?logname='; ?>' + logFile, 'duplicator_logs');
	}
};




/* ============================================================================
*  UI NAMESPACE: All methods at the top of the Duplicator Namespace  
*  ============================================================================	*/

/*	----------------------------------------
*	METHOD: Dynamically sets the base options for a dialog winodw
*	@param evt		The event object
*	remarks: Browser Specific. IE9 does not support modal correctly this is a workaround   */ 
Duplicator.UI.CreateDialog = function(evt) {
	if (! jQuery.browser.msie) {
		jQuery('#' + this.id).dialog('option', 'modal',  	true);
		jQuery('#' + this.id).dialog('option', 'draggable',  true);
	} else {
		jQuery('#' + this.id).dialog('option', 'draggable',  false);
		jQuery('#' + this.id).dialog('option', 'open',  function() {jQuery("div#wpwrap").addClass('ie-simulated-overlay');} );
	}
};
//Cleanup method for IE 9
Duplicator.UI.CloseDialog = function(evt) {
	if (jQuery.browser.msie) {jQuery("div#wpwrap").removeClass('ie-simulated-overlay');}
};

/*	----------------------------------------
*	METHOD: Create a dynamic tab (no postback) interface using wordpress style tabs
*	@param obj		A valid tab label object  */ 
Duplicator.UI.WPTabsClick = function(obj) { 		
	if ( ! obj.hasClass('nav-tab-active')) {
		var id =  obj.attr('href').replace("#", "");

		//Tab Label
		jQuery('.nav-tab-active').removeClass('nav-tab-active');
		obj.addClass( 'nav-tab-active' );

		//Tab Panel
		jQuery('.dup-nav-tab-contents .ui-tabs').addClass('ui-tabs-hide');			
		jQuery("#" + id).removeClass('ui-tabs-hide');
	}
};

/*	----------------------------------------
*	METHOD: Initilize the tabs for dyanmic use  */ 
Duplicator.UI.WPTabsInit = function() {
	var defaultLabel = location.hash || jQuery('.nav-tab-wrapper a').first().attr("href");
	var defaultAnchor = null;
	jQuery('.nav-tab').click(function() { Duplicator.UI.WPTabsClick(jQuery(this));});

	jQuery(".nav-tab-wrapper a").each(function() {
		if (jQuery(this).attr('href') == defaultLabel) {
			defaultAnchor = jQuery(this);
			return;
		}
	});
	Duplicator.UI.WPTabsClick( defaultAnchor);
};


//Document load stuff
jQuery(document).ready(function() {
	if (jQuery('.dup-nav-tab-contents').length) {
		Duplicator.UI.WPTabsInit();
	}
});
</script>