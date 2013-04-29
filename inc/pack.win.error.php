<!-- ==========================================
DIALOG: SYSTEM ERROR -->
<div id="dup-dlg-system-error" title="<?php _e('System Constraint', 'wpduplicator'); ?>" style="display:none">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0; line-height:18px;"></span>
		<?php _e("Please try again! An issue has occurred.", 'wpduplicator');	?>
	</p>
	
	<div style="padding: 0px 20px 20px 20px;">
		<b><?php _e("Recommendations", 'wpduplicator') ?></b><br/>
		<div id="dup-system-err-msg1">
			<li>
				<?php printf("<a href='javascript:void(0)' onclick='Duplicator.Pack.RunSystemCheck()'>%s</a> %s &amp; 
							  <a href='javascript:void(0)' onclick='window.location.reload();'>%s</a> %s",
						__("Validate", 'wpduplicator'),
					    __("your system", 'wpduplicator'),
						__("refresh", 'wpduplicator'),
						__("the dashboard.", 'wpduplicator')); ?>
			</li>
			<li>
				<?php printf("<a href='javascript:void(0)' onclick='Duplicator.OpenLogWindow()'>%s</a> %s.",
						__("Monitor", 'wpduplicator'),
						__("your log file a few more minutes as processing may continue on some systems", 'wpduplicator')); 
				?>
			</li>
			<li><?php _e('Contact your server admin to have the page timeout increased (see duration below)', 'wpduplicator') ?>.</li>
			<li><?php _e('Consider adding a directory filter in the options dialog if the process continues to timeout', 'wpduplicator') ?>.</li>
			<li><?php _e('Check your disk space.  For hosted sites review your providers help.', 'wpduplicator') ?></li>
			<li>
				<?php printf("%s <a href='%s' target='_blank'>%s</a> %s", 
						__("Consider using an" , 'wpduplicator'),
						__(DUPLICATOR_CERTIFIED, 'wpduplicator'),
						__("approved" , 'wpduplicator'),
						__("hosting provider.", 'wpduplicator')	); ?>
			</li>
		</div><br/>
	
		<b><?php _e("Server Response", 'wpduplicator') ?></b><br/>
		<div id="dup-system-err-msg2"></div>
		<i style='font-size:11px'>
			<?php 
				printf('%s %s', 
					__("See online help for more details at", 'wpduplicator'), 
					"<a href='" . DUPLICATOR_HELPLINK . "' target='_blank'>support.lifeinthegrid.com</a>" );
			?>
		</i>
	</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

	//To validate this dialog just place the code below into an $ajax:success callback
	//Duplicator.Pack.ShowError('Testing Error UI', data);

	$("#dup-dlg-system-error").dialog({autoOpen:false, height:550, width:650, create:Duplicator.UI.CreateDialog, close:Duplicator.UI.CloseDialog });	
	
	/*	----------------------------------------
	*	METHOD: Show the Sytem Error Dialog */ 
	Duplicator.Pack.ShowError = function(action, xhrData) {
		Duplicator.EndAjaxTimer();
		var time = Duplicator.AJAX_TIMER || 'not set';
		var msg  = '<?php _e('AJAX Response', 'wpduplicator') ?>' + ' ' + action + '<br/>';
		msg += "duration: " + time + " secs<br/>code: " + xhrData.status + "<br/>status: " + xhrData.statusText + "<br/>response: " +  xhrData.responseText;
		$("#dup-system-err-msg2").html(msg);
		$("#dup-dlg-system-error").dialog("open");
		Duplicator.Pack.SetStatus("<?php _e('Ready to create new package.', 'wpduplicator'); ?>");
	}
	
});
</script>