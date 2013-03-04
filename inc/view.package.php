<!-- ==========================================
DIALOG: PACKAGE CONFIRMATION-->
<div id="dup-dlg-package-confirm" title="<?php _e('New Package', 'wpduplicator'); ?>" style="display:none">
	
	<div id="dup-create-message" class="updated below-h2" style="padding:4px; display: none"></div>
	
	<fieldset style="padding:5px 20px 10px 20px; line-height:28px; text-align:left; border:1px solid #efefef; border-radius:5px">
		<legend><b><?php _e("Settings", 'wpduplicator');	?></b></legend>
		
		<table style="width:100%">
			<tr>
				<td style="white-space:nowrap"><b><?php _e('Package Name', 'wpduplicator') ?>: </b></td>
				<td style="width:100%"><input name="package_name" type="text" style="width:97%" value="<?php echo $package_name ?>" maxlength="40" /></td>
			</tr>
			<tr>
				<td><b><?php _e('Pre-Zip Size', 'wpduplicator'); ?>:</b></td>
				<td>
					<span id='dup-dlg-package-confirm-scannow-data'>
						<a href="javascript:void(0)" onclick="Duplicator.getSystemDirectory()"><?php _e("Check Application Size", 'wpduplicator') ?></a> 
					</span>
				</td>
			</tr>			
		</table>
		
		<div style='font-size:11px; line-height:15px; position: absolute; bottom:5px; left:15px; padding: 0px 15px 5px 0px'>
			<i>
				<?php 
				printf("%s <a href='javascript:void(0)'  onclick='Duplicator.optionsOpen()'>%s</a>.",
						__('Pre-Zip provides the size of your application and will exclude items in the', 'wpduplicator'),
						__('directory filter', 'wpduplicator'));
				echo '  ';
				printf("%s <a href='http://lifeinthegrid.com/duplicator-faq'  target='_blank'>%s</a>.",
						__('Please note that some hosts will kill any process after 45-60 seconds.  If your hosting provider performs this practice then you
							will need to ask them how to extend the PHP timeout.  For more details see the', 'wpduplicator'),
						__('Online FAQs', 'wpduplicator'));
				?>
			</i>
		</div>
	</fieldset>
</div>
