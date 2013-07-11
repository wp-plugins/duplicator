<?php
	require_once('javascript.php'); 
	require_once('inc.header.php');
	$plugins_url = plugins_url();
	$admin_url   = admin_url();
?>

<style type="text/css">
	div.success {color:#4A8254}
	div.failed {color:red}
</style>

<div class="wrap">
	<!-- h2 required here for general system messages -->
	<h2 style='display:none'></h2>
	<?php duplicator_header(__("Installer Cleanup", 'wpduplicator') ) ?>
		
	<form id="dup-settings-form" action="<?php echo admin_url( 'admin.php?page=duplicator_cleanup_page' ); ?>" method="post">
		<?php wp_nonce_field( 'duplicator_cleanup_page' ); ?>
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="page"   value="duplicator_cleanup_page">

		<div style="margin:auto; padding:10px;">
			<h3><?php _e('Installer Cleanup', 'wpduplicator')?></h3>
			<hr size="1"/>
			<?php	

				$html = "";
				if ( isset($_GET['remove'])) {

					$installer_file 	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_PHP;
					$installer_sql  	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_SQL;
					$installer_log  	= DUPLICATOR_WPROOTPATH . DUPLICATOR_INSTALL_LOG;
					$package_name   	= DUPLICATOR_WPROOTPATH . esc_html($_GET['package']);

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

			
			 <i> <br/>
				 <?php _e('If the installer files did not successfully get removed, then you WILL need to remove them manually', 'wpduplicator')?>. <br/>
				 <?php _e('Please remove all installer files to avoid leaving open security issues on your server', 'wpduplicator')?>. <br/>
			 </i>
		 </div>
	</form>
</div>