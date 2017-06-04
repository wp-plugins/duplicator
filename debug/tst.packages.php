<div class="section-hdr">PACKAGE CTRLS</div>

<form>
	<?php 
		$CTRL['Title']   = 'duplicator_package_scan';
		$CTRL['Action']  = 'duplicator_package_scan'; 
		$CTRL['Test']	 = false;
		DUP_DEBUG_TestSetup($CTRL); 
	?>
	<div class="params">
		No Params
	</div>
</form>

<form>
	<?php
		$CTRL['Title']   = 'DUP_CTRL_Package_addDirectoryFilter';
		$CTRL['Action']  = 'DUP_CTRL_Package_addDirectoryFilter';
		$CTRL['Test']	 = true;
		DUP_DEBUG_TestSetup($CTRL);
	?>
	<div class="params">
		<textarea style="width:200px; height: 50px" name="dir_paths">D:/path1/;
D:/path2/path/;
		</textarea>
	</div>
</form>

