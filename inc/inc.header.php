<?php function duplicator_header($title) { ?>
<!-- !!DO NOT CHANGE OR EDIT PRODUCT NAME!!
If your interested in Private Label Rights please contact us at the URL below to discuss
customizations to product labeling: lifeinthegrid.com/services/	-->

<div class="dup-header widget" style="margin-bottom:5px">
	<div style='float:left;height:45px'><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/logo.png" style='text-align:top'  /></div> 
	<div style='float:left;height:45px; text-align:center;'>
		<h2 style='margin:-12px 0px -7px 0px; text-align:center; width:100%;'>Duplicator &raquo; <?php echo $title ?> </h2>
		<i style='font-size:0.8em'><?php _e("By", 'wpduplicator') ?> <a href='http://lifeinthegrid.com/duplicator' target='_blank'>lifeinthegrid.com</a></i>
	</div> 
	<br style='clear:both' />
</div>
<?php } ?>
