<form id="dup-settings-form" action="<?php echo admin_url( 'admin.php?page=duplicator-tools&tab=diagnostics&section=diagnostic' ); ?>" method="post">
	<?php wp_nonce_field( 'duplicator_settings_page', '_wpnonce', false ); ?>
	<input type="hidden" id="dup-settings-form-action" name="action" value="">
	<br/>

	<?php if (! empty($action_response))  :	?>
		<div id="message" class="updated below-h2"><p><?php echo $action_response; ?></p></div>
	<?php endif; ?>

	<?php
		include_once 'inc.data.php';
		include_once 'inc.settings.php';
		include_once 'inc.validator.php';
		include_once 'inc.phpinfo.php';
	?>
</form>
