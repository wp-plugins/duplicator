<script type="text/javascript">
jQuery.noConflict()(function($) {
jQuery(document).ready(function() {
	
	
	//Unique namespace
	Duplicator      = new Object();
	Duplicator.Pack = new Object();
	Duplicator.DEBUG_AJAX_RESPONSE = false;
	Duplicator.AJAX_TIMER = null;
	
	Duplicator.startAjaxTimer = function() {
		Duplicator.AJAX_TIMER = new Date();
	}
	
	Duplicator.endAjaxTimer = function() {
		var endTime = new Date();
		Duplicator.AJAX_TIMER =  (endTime.getTime()  - Duplicator.AJAX_TIMER) /1000;
	}

	/*  ============================================================================
	MISC ROUTINES */
	Duplicator.newWindow = function(url) {window.open(url);}

	Duplicator.openLog = function() { 				
		window.open('<?php echo DUPLICATOR_PLUGIN_URL .'files/log-view.php'; ?>', 'duplicator_logs');
	}
	
		/*	METHOD: Duplicator.reload  
	*  Performs reloading the page and diagnotic handleing */
	Duplicator.reload = function(data) {
		if (Duplicator.DEBUG_AJAX_RESPONSE) {
			Duplicator.Pack.ShowError('debug on', data);
		} else {
			Duplicator.Pack.SetToolbar("ENABLED");
			window.location.reload();
		}
	}
	
		
	/*  ============================================================================
	DIALOG: WINDOWS
	Browser Specific. IE9 does not support modal correctly this is a workaround  */
	Duplicator._dlgCreate = function(evt, ui) {
		if (! $.browser.msie) {
			$('#' + this.id).dialog('option', 'modal',  	true);
			$('#' + this.id).dialog('option', 'draggable',  true);
		} else {
			$('#' + this.id).dialog('option', 'draggable',  false);
			$('#' + this.id).dialog('option', 'open',  function() {$("div#wpwrap").addClass('ie-simulated-overlay');} );
		}
	}
	Duplicator._dlgClose = function(evt, ui) {
		if ($.browser.msie) {$("div#wpwrap").removeClass('ie-simulated-overlay');}
	}
	
	
	
	/* WP DYNAMIC TABS
	 * Use these methods to create a dynamic tab interface
	 * Currently used in the Settings section
	 */
	Duplicator.dynamicWPTabsClick = function(obj) { 		
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
	
	Duplicator.dynamicWPTabsInit = function() {
		
		var defaultLabel = location.hash || $('.nav-tab-wrapper a').first().attr("href");
		var $defaultAnchor = null;
		jQuery('.nav-tab').click(function() { Duplicator.dynamicWPTabsClick($(this))});
		
		$(".nav-tab-wrapper a").each(function() {
			if ($(this).attr('href') == defaultLabel) {
				$defaultAnchor = $(this);
				return;
			}
		});
	    Duplicator.dynamicWPTabsClick( $defaultAnchor);
	}
	
	if ($('.dup-nav-tab-contents').length) {
		Duplicator.dynamicWPTabsInit();
	}
	

});
});
</script>
