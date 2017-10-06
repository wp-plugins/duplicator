
<h3 class="title"><?php _e("Activation"); ?> </h3>
<hr size="1" />
<table class="form-table">
	<tr valign="top">
		<th scope="row"><?php _e("Manage") ?></th>
		<td><?php echo sprintf(__('%1$sManage Licenses%2$s'), '<a target="_blank" href="https://snapcreek.com/dashboard?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_campaign=duplicator_pro&utm_content=settings_license_manage_licenses">', '</a>'); ?></td>
	</tr>
	<tr valign="top">
		<th scope="row"><?php _e("Type") ?></th>
		<td class="dpro-license-type">
			<?php _e('Duplicator Free'); ?>
			<div style="padding: 10px">
				<i class="fa fa-check-square-o"></i> <?php _e('Basic Features'); ?> <br/>
				<i class="fa fa-square-o"></i> <a href="admin.php?page=duplicator-gopro"><?php _e('Pro Features'); ?></a><br>
			</div>
		</td>
	</tr>
	<tr valign="top">
		<th scope="row"><label><?php _e("License Key"); ?></label></th>
		<td>

			<p class="description" style="max-width:700px">
			   <?php
					_e("The free version of Duplicator does not require a license key. ");
					echo '<br/><br/>';
					_e("<b>Professional Users:</b> Please note that if you have already purchased the Professional version it is a separate plugin that you download and install.  "
						. "You can download the Professional version  from the email sent after your purchase or click on the 'Manage Licenses' link above to "
						. "download the plugin from your snapcreek.com dashboard.  ");
					_e("If you would like to purchase the professional version you can ");
					echo '<a href="https://snapcreek.com/duplicator?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_campaign=duplicator_pro&utm_content=settings_license_get_copy_here" target="_blank">' .  __("get a copy here") . '</a>!';
				?>
			</p>
			<br/><br/>

		</td>
	</tr>
</table>



