<?php
	/** WordPress Administration Bootstrap 
	see: http://codex.wordpress.org/Roles_and_Capabilities#export
	Must be logged in from the WordPress Admin */
	require_once('../../../../wp-admin/admin.php');

	if (! current_user_can('level_8') ) {
		die("You must be a WordPress Administrator to view the Duplicator logs.");
	} 
	
	$logs 	= glob(DUPLICATOR_SSDIR_PATH . '/*.log') ;
	if (count($logs)) {
		usort($logs, create_function('$a,$b', 'return filemtime($a) - filemtime($b);'));
	} 
	
	$logname = basename($logs[0]);
	$logurl  = get_site_url(null, '', is_ssl() ? 'https' : 'http') . '/' . DUPLICATOR_SSDIR_NAME . '/' . $logname;
	
	$plugins_url = plugins_url();
	$admin_url   = admin_url();

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="robots" content="noindex,nofollow">
	<link rel="stylesheet" href="<?php echo $admin_url; ?>/load-styles.php?c=1&dir=ltr&load=admin-bar,wp-jquery-ui-dialog,wp-admin,buttons&ver=3.5.1" type="text/css" media="all">
	<link rel="stylesheet" id="colors-css" href="<?php echo $admin_url; ?>/css/colors-fresh.min.css?ver=3.5.1" type="text/css" media="all">
	<link rel="stylesheet" id="jquery-ui-css" href="<?php echo $plugins_url; ?>/duplicator/assets/css/jquery-ui.css?ver=3.3.2" type="text/css" media="all">
	<link rel="stylesheet" id="duplicator_style-css" href="<?php echo $plugins_url; ?>/duplicator/assets/css/style.css?ver=3.3.2" type="text/css" media="all">
	<style type="text/css">
		iframe#log-data {
			background-color:#efefef;
			box-shadow: 4px 4px 3px #888; 
			border:1px solid silver; 
			border-radius:5px;
			width:98%; height:86%;
		}
		span#spanCount {display:inline-block !important; padding:0px 3px 0px 3px; width:15px; white-space:nowrap;}
	</style>
	<script type="text/javascript" src="<?php echo $admin_url; ?>/load-scripts.php?c=0&amp;load=jquery,utils&amp;ver=edec3fab0cb6297ea474806db1895fa7"></script>
	<script type="text/javascript">
	jQuery.noConflict()(function($) {
		jQuery(document).ready(function() {
			
			//Refresh Button
			$("#Refresh").click(function() { 
				$("#log-data").attr("src", "log-read.php") ;
				
				//Scroll to Bottom
				$("#log-data").load(function () {
					var $contents = $('#log-data').contents();
					$contents.scrollTop($contents.height());
				});
			});
			
			//Refresh Checkbox
			$("#AutoRefresh").click(function() { 
				if ( $("#AutoRefresh").is(":checked")) {
					startTimer();
				} 
			});
			
			
			var duration = 10;
			var count = duration;
			var timerInterval;
			function timer() {
				count = count - 1;
				$("#spanCount").html(count.toString());
				if (! $("#AutoRefresh").is(":checked")) {
					 clearInterval(timerInterval);
					 $("#spanCount").html(count.toString());
					 return;
				}
				
				if (count <= 0) {
					count = duration + 1;
					 $('#Refresh').trigger('click');
				}
			}
			
			function startTimer() {
				timerInterval = setInterval(timer, 1000); 
			}
			
			$("#spanCount").html(duration.toString());
		});
	});
	</script>
</head>
<body style="overflow:hidden">

	<div class="wp-core-ui" style="padding:0px 20px 10px 20px;">
		<table style="width:99%" border="0">
			<tr>
				<td><div id="icon-tools" class="icon32" style="height:34px; width:34px">&nbsp;</div></td>
				<td style='white-space:nowrap'>
					<b style='font-size:18px'><?php _e("Duplicator: Create Package Log", 'wpduplicator') ?></b> <br/>
					
					<i style='font-size:12px'>
						<?php _e("Processing may take several minutes, please wait for progress bar to complete on the main status bar", 'wpduplicator') ?>.<br/>
						<?php echo "log: {$logurl}" ?>
					</i>
				</td>
				<td style='width:100%; text-align:right; padding-right:20px; white-space:nowrap'>
					<input type='checkbox' id="AutoRefresh" /> <label for="AutoRefresh" style='white-space:nowrap'><?php _e("Auto Refresh", 'wpduplicator') ?> [<span id="spanCount"></span>]</label> <br/>
					<input type="button" id="Refresh" style="margin: 8px 5px 0px 0px" class="button action" value="<?php _e("Refresh", 'wpduplicator') ?>" />
				</td>
			</tr>
			<tr><td colspan='3'><hr size='1' /></td></tr>
		</table> 
		
		<iframe id="log-data" name="logData" src="log-read.php"></iframe><br/><br/>
		<i style='font-size:12px'>
			<a href="<?php echo DUPLICATOR_HELPLINK ?>" target="_blank"><?php _e("Support Center", 'wpduplicator') ?></a> &nbsp;
			<?php _e("Do NOT post this data to public sites like the WordPress.org forums as it contains sensitive data.", 'wpduplicator') ?>
		</i>
	</div>

</body>
</html>
