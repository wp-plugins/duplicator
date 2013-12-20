<?php
	global $wpdb;
	
	//COMMON HEADER DISPLAY
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/javascript.php'); 
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php'); 
	$current_tab = isset($_REQUEST['tab']) ? esc_html($_REQUEST['tab']) : 'general';
?>

<style>

</style>

<div class="wrap">
	<!-- h2 required here for general system messages  -->
	<h2 style='display:none'></h2>
	
	<?php duplicator_header(__("Settings", 'wpduplicator') ) ?>
	
	<h2 class="nav-tab-wrapper">  
		<a href="?page=duplicator-settings" class="nav-tab <?php echo ($current_tab == 'general') ? 'nav-tab-active' : '' ?>"> <?php _e('General', 'wpduplicator'); ?></a>  
		<a href="?page=duplicator-settings&tab=diagnostics" class="nav-tab <?php echo ($current_tab != 'general') ? 'nav-tab-active' : '' ?>"> <?php _e('Diagnostics', 'wpduplicator'); ?></a>  
	</h2> 	
	
	<?php
		switch ($current_tab) {
			case 'general':	include('general.php');	break;
			case 'diagnostics':	include('diagnostics.php');	break;
		}	
	?>
</div>
