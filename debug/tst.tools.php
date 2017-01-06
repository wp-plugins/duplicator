
<div id="section-tools">
	<div class="section-hdr">TOOLS CONTROLLERS</div>

	<!-- UNIT TEST -->
	<?php 
		$ctrl_key = 'DUP_CTRL_Tools_RunScanValidator'; 
		$ctrl_testable = true;
	?>
	<form action="admin-ajax.php" method="post" target="duplicator_debug" class="<?php echo $ctrl_testable ? 'testable' : 'not-testable';?>" >
		<?php DUP_DEBUG_Make_Keys($ctrl_key, $ctrl_testable); ?>

		<div class="params">
			<label>Allow Recursion:</label>
			<input type="checkbox" name="scan-recursive" /><br/>

			<label>Search Path:</label> 
			<input type="text" name="scan-path" value="<?php echo DUPLICATOR_WPROOTPATH ?>" /> <br/>
		</div>
	</form>
</div>
