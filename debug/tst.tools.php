
<div id="section-tools">
	<div class="section-hdr">TOOLS CTRLS</div>

	<!-- METHOD TEST -->
	<?php 
		$CTRL['Title']  = 'DUP_CTRL_Tools_RunScanValidator';
		$CTRL['Action'] = 'DUP_CTRL_Tools_RunScanValidator'; 
		$CTRL['Test']	= true;
	?>
	<form action="admin-ajax.php" method="post" target="duplicator_debug" class="<?php echo $CTRL['Test'] ? 'testable' : 'not-testable';?>" >
		<?php DUP_DEBUG_Make_Keys($CTRL); ?>
		<div class="params">
			<label>Allow Recursion:</label>
			<input type="checkbox" name="scan-recursive" /><br/>
			<label>Search Path:</label> 
			<input type="text" name="scan-path" value="<?php echo DUPLICATOR_WPROOTPATH ?>" /> <br/>
		</div>
	</form>
	
	<!-- METHOD TEST -->
	<?php 
		$CTRL['Title']  = 'DUP_CTRL_Tools_RunScanValidatorFull';
		$CTRL['Action'] = 'DUP_CTRL_Tools_RunScanValidator'; 
		$CTRL['Test']	= true;
	?>
	<form action="admin-ajax.php" method="post" target="duplicator_debug" class="<?php echo $CTRL['Test'] ? 'testable' : 'not-testable';?>" >
		<?php DUP_DEBUG_Make_Keys($CTRL); ?>
		<div class="params">
			<label>Recursion:</label> True
			<input type="hidden" name="scan-recursive" value="true" /><br/>
			<label>Search Path:</label> 
			<input type="text" name="scan-path" value="<?php echo DUPLICATOR_WPROOTPATH ?>" /> <br/>
		</div>
	</form>
	

</div>
