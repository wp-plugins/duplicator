<!-- ==========================================
DIALOG: NEW PACKAGE -->
<div id="dup-dlg-package-confirm" title="<?php _e('Create New Package', 'wpduplicator'); ?>" style="display:none">
	
	<div id="dup-create-message" class="updated below-h2" style="padding:4px; display: none"></div>
	
	<fieldset style="padding:5px 20px 10px 20px; line-height:28px; text-align:left; border:1px solid #efefef; border-radius:5px">
		<legend><b><?php _e("Package", 'wpduplicator');	?></b></legend>
		
		<table style="width:100%;">			
			<tr>
				<td style="white-space:nowrap"><b><?php _e('Name', 'wpduplicator') ?>: &nbsp; </b></td>
				<td style="width:100%"><input name="package_name" type="text" style="width:97%" value="<?php echo $package_name ?>" maxlength="40" /></td>
			</tr>
			<tr>
				<td style="white-space:nowrap"><b><?php _e('Notes', 'wpduplicator') ?>:  &nbsp; </b></td>
				<td style="width:100%"><textarea name="package_notes" type="text" maxlength="300" style="width:97%; height:40px; line-height:14px" placeholder="<?php _e('Purpose of this package', 'wpduplicator') ?>" /></textarea></td>
			</tr>
			<tr>
				<td colspan="2">
					<b><?php _e('Pre-Zip Size', 'wpduplicator'); ?>:</b> &nbsp;
					<span id='dup-dlg-package-confirm-scannow-data'>
						<a href="javascript:void(0)" onclick="Duplicator.Pack.ScanRootDirectory()"><?php _e("Check Application Size", 'wpduplicator') ?></a> 
					</span>
				</td>
			</tr>
		</table>
		
		<div style='font-size:11px; line-height:15px; position: absolute; bottom:5px; left:15px; padding: 0px 15px 5px 0px'>
			<i>
				<?php 
				printf("%s <a href='javascript:void(0)'  onclick='Duplicator.Pack.ShowOptionsDialog()'>%s</a>.",
						__('Pre-Zip provides the size of your application and will exclude items in the', 'wpduplicator'),
						__('directory filter', 'wpduplicator'));
				echo '  ';
				printf("%s %s <a href='http://lifeinthegrid.com/duplicator-faq'  target='_blank'>%s</a>.",
						__('Please note that some hosts will kill any process after 45-60 seconds.', 'wpduplicator'),
						__('If your hosting provider performs this practice ask them how to extend the PHP timeout.  For more details see the', 'wpduplicator'),
						__('Online FAQs', 'wpduplicator'));
				?>
			</i>
		</div>
	</fieldset>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

	/*	----------------------------------------
	*	METHOD: Validate the Package New inputs */ 
	Duplicator.Pack.CreateValidation = function() {
		//Validate length test
		if ($("input[name=package_name]").val().length <= 0) 	{
			$("#dup-create-message").fadeIn().html('<?php _e('Please enter a backup name.', 'wpduplicator') ?>');
			return false;
		}

		//Vatlidate alphanumeric test
		var newstring = $("input[name=package_name]").val().replace(/ /g, "");
		$("input[name=package_name]").val(newstring)
		if ( ! /^[0-9A-Za-z|_]+$/.test($("input[name=package_name]").val())) {
			$("#dup-create-message").fadeIn().html('<?php _e('Alpanumeric characters only on package name.', 'wpduplicator') ?>');
			return false;
		}
		return true;
	}
	
	/*	----------------------------------------
	*	METHOD: Performs Ajax post to create a new package
	*	Timeout (10000000 = 166 minutes) 
	*	"package_name=" + packname +"&action=duplicator_create",
	*	*/
	Duplicator.Pack.Create = function(name, notes) {
		Duplicator.Pack.SetToolbar("DISABLED");

		$.ajax({
			type: "POST",
			url: ajaxurl,
			timeout: 10000000,
			data: {action : 'duplicator_create', package_name : name, package_notes : notes},
			beforeSend: function() {Duplicator.StartAjaxTimer(); },
			complete: function() {Duplicator.EndAjaxTimer(); },
			success:    function(data) { 
				Duplicator.ReloadWindow(data);
			},
			error: function(data) { 
				Duplicator.Pack.ShowError('Duplicator.Pack.Create', data);
				Duplicator.Pack.SetToolbar("ENABLED");
			}
		});
	}
	
	/*	----------------------------------------
	*	METHOD: Set StatusBar and Call create */ 
	Duplicator.Pack.StartCreate = function() {
		var msg = "<?php _e('Creating package may take several minutes. Please Wait... ', 'wpduplicator'); ?>";
		var postmsg = "<?php printf(" &nbsp; <a href='javascript:void(0)' onclick='Duplicator.OpenLogWindow()'>[%s]</a>", 	__('Preview Log', 'wpduplicator'));?>";
		
		var name  = $("input[name=package_name]").val();
		var notes = $("textarea[name=package_notes]").val();
		Duplicator.Pack.SetStatus(msg, 'progress', postmsg);
		
		Duplicator.Pack.Create(name, notes);
	}
	
	//LOAD: 'New Package' Dialog
	$("#dup-dlg-package-confirm").dialog(
		{autoOpen:false, height:350, width:650, create:Duplicator.UI.CreateDialog, close:Duplicator.UI.CloseDialog,
		buttons: {
				'create' : { 
						'text': "<?php _e('Create Package Set', 'wpduplicator') ?>",
						'class': "button action",
						'click': function() {
						if (Duplicator.Pack.CreateValidation()) {
							$(this).dialog("close");
							Duplicator.Pack.StartCreate();
						}
					}
				},
				'cancel' : {
					'text' : "<?php _e('Cancel', 'wpduplicator') ?>",
					'class': "button action",
					'click': function() {$(this).dialog("close");}
				}
			}
		}
	);	
		
});
</script>