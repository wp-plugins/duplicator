<!-- ==========================================
DIALOG: QUICK PATH -->
<div id="dup-dlg-quick-path" title="<?php _e('Download Links', 'wpduplicator'); ?>" style="display:none">
	<p>
		<span class="ui-icon ui-icon-locked" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
		<?php _e("The following links contain sensitive data.  Please share with caution!", 'wpduplicator');	?>
	</p>
	
	<div style="padding: 0px 20px 20px 20px;">
		<a href="javascript:void(0)" style="display:inline-block; text-align:right" onclick="Duplicator.Pack.GetLinksText()">[Select All]</a> <br/>
		<textarea id="dup-dlg-quick-path-data" style='border:1px solid silver; border-radius:5px; width:98%; height:250px; font-size:11px'></textarea><br/>
		<i style='font-size:11px'><?php _e("The database SQL script is a quick link to your database backup script.  An exact copy is also stored in the package.", 'wpduplicator'); ?></i>
	</div>
</div>


<script type="text/javascript">
jQuery(document).ready(function($) {
	
	/*	----------------------------------------
	 *	METHOD: Shows the 'Download Links' dialog
	 *	@param db		The path to the sql file
	 *	@param install	The path to the install file 
	 *	@param pack		The path to the package file */
	Duplicator.Pack.ShowLinksDialog = function(db, install, pack, log) {
		$("#dup-dlg-quick-path").dialog("open");
		var msg = <?php printf('"%s:\n" + db + "\n\n%s:\n" + install + "\n\n%s:\n" + pack + "\n\n%s:\n" + log;', 
			__("DATABASE",  'wpduplicator'), 
			__("PACKAGE", 'wpduplicator'), 
			__("INSTALLER",   'wpduplicator'),
			__("LOG", 'wpduplicator')); 
		?>
		$("#dup-dlg-quick-path-data").val(msg);
		return false;
	}
	
	//LOAD: 'Download Links' Dialog and other misc setup
	Duplicator.Pack.GetLinksText = function() {$('#dup-dlg-quick-path-data').select();};
	$("#dup-dlg-quick-path").dialog({autoOpen:false, height:450, width:750, create:Duplicator.UI.CreateDialog, close:Duplicator.UI.CloseDialog });
	$(".dup-dlg-quick-path-download-link").button({ icons: {primary: "ui-icon-locked"} });
	$(".dup-dlg-quick-path-database-link").button({ icons: {primary: "ui-icon-script"} });
	$(".dup-installer-btn").button({ icons: {primary: "ui-icon-disk"} });

});
</script>