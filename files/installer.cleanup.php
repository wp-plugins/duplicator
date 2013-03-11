<?php
	/** WordPress Administration Bootstrap 
	see: http://codex.wordpress.org/Roles_and_Capabilities#export
	Must be logged in from the WordPress Admin */
	require_once('../../../../wp-admin/admin.php');

	if (! current_user_can('level_8') ) {
		die("You must be a WordPress Administrator to clean the Duplicator install files.");
	} 
	
	$plugins_url = plugins_url();
	$admin_url   = admin_url();
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="robots" content="noindex,nofollow">
	<link rel="stylesheet" href="<?php echo $admin_url; ?>/load-styles.php?c=0&amp;dir=ltr&amp;load=admin-bar,wp-jquery-ui-dialog,wp-admin&amp;ver=63e8d12bee407fb9bdf078f542ef8b29" type="text/css" media="all">
	<link rel="stylesheet" id="colors-css" href="<?php echo $admin_url; ?>/assets/css/colors-fresh.css?ver=20111206" type="text/css" media="all">
	<link rel="stylesheet" id="jquery-ui-css" href="<?php echo $plugins_url; ?>/duplicator/assets/css/jquery-ui.css?ver=3.3.2" type="text/css" media="all">
	<link rel="stylesheet" id="duplicator_style-css" href="<?php echo $plugins_url; ?>/duplicator/assets/css/style.css?ver=3.3.2" type="text/css" media="all">
	<style type="text/css">
		div.success {color:#4A8254}
		div.failed {color:red}
	</style>
</head>
</body>

<div style="margin:auto; padding:40px;">
	<div style="padding:10px; border:1px solid silver; border-radius:5px">
	<h2>Duplicator Installer Cleanup</h2>
	<?php	
		
		$html = "";
		if ( isset($_GET['remove'])) {
			$installer_rescue 	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_BAK;
			$installer_file 	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP;
			$installer_sql  	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL;
			$installer_log  	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG;
			$package_name   	= DUPLICATOR_WPROOTPATH . $_GET['package'];
			
			
			//installer.rescue.php rarely is this file used
			if (file_exists($installer_rescue)) {
				$html .= (@unlink($installer_rescue)) ?  "<div class='success'>Successfully removed {$installer_rescue}</div>"	:  "<div class='failed'>Does not exsist or unable to remove file: {$installer_rescue}</div>";
			} 
			$html .= (@unlink($installer_file)) ?  "<div class='success'>Successfully removed {$installer_file}</div>"	:  "<div class='failed'>Does not exsist or unable to remove file: {$installer_file}</div>";
			$html .= (@unlink($installer_sql))  ?  "<div class='success'>Successfully removed {$installer_sql}</div>"  	:  "<div class='failed'>Does not exsist or unable to remove file: {$installer_sql}</div>";
			$html .= (@unlink($installer_log))  ?  "<div class='success'>Successfully removed {$installer_log}</div>"	:  "<div class='failed'>Does not exsist or unable to remove file: {$installer_log}</div>";
			
			
			$path_parts = pathinfo($package_name);
			if ($path_parts['extension'] == "zip"  && ! is_dir($package_name)) {
				$html .= (@unlink($package_name))   ?  "<div class='success'>Successfully removed {$package_name}</div>"   :  "<div class='failed'>Does not exsist or unable to remove file: {$package_name}</div>";
			} else {
				$html .= "<div class='failed'>Unable to remove zip file from {$package_name}.  Validate that a package file exists.</div>";
			}
			
			
		} else {
			$html .= "<div class='failed'>Unable to remove the installer files.</div>";
		}
		
		echo $html;
	 ?>
	 
	 <br/><i>If the installer files did not successfully get removed, then you WILL need to remove them manually. <br/>
	Please remove all installer files or else you will leave a security hole on your server.</i>
	 
	 </div>
 </div>
 
 
</body>
</html>