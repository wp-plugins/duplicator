<?php
	/** WordPress Administration Bootstrap 
	see: http://codex.wordpress.org/Roles_and_Capabilities#export
	Must be logged in from the WordPress Admin */
	require_once('../../../../wp-admin/admin.php');
	require_once('../define.php');
	
	if (! current_user_can('level_8') ) {
		die("You must be a WordPress Administrator to view the Duplicator logs.");
	} 
	
	$logs 	= glob(DUPLICATOR_SSDIR_PATH . '/*.log') ;
	if (count($logs)) 
	@chmod(duplicator_safe_path($logs[0]), 0644);
	
	if (count($logs)) {
		@usort($logs, create_function('$a,$b', 'return filemtime($a) - filemtime($b);'));
	} 
	
	$logname  = basename($logs[0]);
	$logurl   = get_site_url(null, '', is_ssl() ? 'https' : 'http') . '/' . DUPLICATOR_SSDIR_NAME . '/' . $logname;
	$logfound = (strlen($logname) > 0) ? true :false;
	$file     = ($logfound) ? file_get_contents($logurl) : null;
	
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="robots" content="noindex,nofollow">
	<link rel="stylesheet" href="../../../../wp-admin/load-styles.php?c=0&amp;dir=ltr&amp;load=admin-bar,wp-jquery-ui-dialog,wp-admin&amp;ver=63e8d12bee407fb9bdf078f542ef8b29" type="text/css" media="all">
	<link rel="stylesheet" id="duplicator_style-css" href="../../../../wp-content/plugins/duplicator/css/style.css?ver=3.3.2" type="text/css" media="all">
</head>
<body>

	<?php if (! $logfound)  :	?>
		<div style="padding:20px">
			<h2><?php _e("No log file found", 'WPDuplicator') ?>.</h2>
			
			<?php _e("The log file for the Duplicator Plugin can be found in the directory wp-snapshots with the extension *.log", 'WPDuplicator') ?>.
			<?php _e("If no log file is present the try to create a package", 'WPDuplicator') ?>.<br/><br/>
			
			<?php _e("Reasons for log file not showing", 'WPDuplicator') ?>: <br/>
			- <?php _e("The snapshots directory does not have the correct permissions to write files.  Try setting the permissions to 755", 'WPDuplicator') ?>. <br/>
			- <?php _e("The process that PHP runs under does not have enough permissions to create files.  Please contact your hosting provider for more details", 'WPDuplicator') ?>. <br/>
		</div>
	
	<?php else: ?>	
		<pre style="padding:5px"><?php echo $file ?></pre>
	<?php endif; ?>	

</body>
</html>