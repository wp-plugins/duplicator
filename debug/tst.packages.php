<div class="section-hdr">PACKAGE CTRLS</div>

<?php 
	$CTRL['Title']   = 'duplicator_package_scan';
	$CTRL['Action']  = 'duplicator_package_scan'; 
	$CTRL['Test']	 = false;
?>
<form action="admin-ajax.php" method="post" target="duplicator_debug" class="<?php echo $CTRL['Test'] ? 'testable' : 'not-testable';?>" >

	<?php DUP_DEBUG_Make_Keys($CTRL); ?>

	<div class="params">
		No Params
	</div>

</form>

