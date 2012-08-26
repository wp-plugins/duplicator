<?php
	/*USED FOR DIAGNOSTICS FOR DEBUGGING*/
	ob_start();
	phpinfo();
	$serverinfo = ob_get_contents();
	ob_end_clean();
	
	$serverinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms',  '$1',  $serverinfo);
	$serverinfo = preg_replace( '%^.*<title>(.*)</title>.*$%ms','$1',  $serverinfo);

?>

<div class="wrap">
	<form id="form-duplicator" method="post">

		<div class="dup-header widget" style="margin-bottom:5px">
			<div style='float:left;height:45px'><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/logo.png" style='text-align:top'  /></div> 
			<div style='float:left;height:45px; text-align:center;'>
				<h2 style='margin:-12px 0px -7px 0px; text-align:center; width:100%;'>Duplicator &raquo;<span style="font-size:18px"> <?php _e("Diagnostics", 'wpduplicator') ?></span> </h2>
				<i style='font-size:0.8em'><?php _e("By", 'wpduplicator') ?> <a href='http://lifeinthegrid.com/duplicator' target='_blank'>lifeinthegrid.com</a></i>
			</div> 
			<div style='float:right; padding:5px 0px 0px 0px'>
				<input type="button" id="btn-contribute-dialog"  onclick="window.open('<?php echo DUPLICATOR_GIVELINK ?>', '_blank')" title="<?php _e("Partner with us", 'wpduplicator') ?>..." />
				<input type="button" id="dup-btn-about" onclick='window.location.href="?page=duplicator_about_page"' title="<?php _e("About Duplicator", 'wpduplicator') ?>" />
			</div>
			<br style='clear:both' />
		</div>
		

	<?php 	echo "<div id='dup-server-info-area'>{$serverinfo}</div>"; ?>

</div>