<div class="section-hdr">PACKAGE CONTROLLERS</div>

<?php 
	$ctrl_key = 'duplicator_package_scan'; 
	$ctrl_testable = false;
?>
<form action="admin-ajax.php" method="post" target="duplicator_debug" class="<?php echo $ctrl_testable ? 'testable' : 'not-testable';?>" >

	<?php DUP_DEBUG_Make_Keys($ctrl_key, $ctrl_testable); ?>

	<div class="params">
	</div>

</form>

