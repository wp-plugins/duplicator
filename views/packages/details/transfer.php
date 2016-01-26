<style>
	h3 {margin:10px 0 5px 0}
	div.transfer-panel {padding: 20px 5px 10px 10px;}
	div.transfer-hdr { border-bottom: 0px solid #dfdfdf; margin: -15px 0 0 0}
</style>

<div class="transfer-panel">
	<div class="transfer-hdr">
		<h2><i class="fa fa-arrow-circle-right"></i> <?php DUP_Util::_e('Manual Transfer'); ?></h2>
	</div>
	<br/>
	
	<div style="font-size:16px; text-align: center; line-height: 30px">
		<img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/logo-dpro-300x50.png"  /> 
		<?php 		
			echo '<h2>' . DUP_Util::__('This option is available only in Duplicator Professional.') . '</h2>';
			DUP_Util::_e('Manual transfer lets you copy a package to Amazon S3, Dropbox, Google Drive, FTP or another directory.');
			echo '<br/>';
			DUP_Util::_e('Simply choose your destination and hit the transfer button and your done.');
		?>
	</div>
	<p style="text-align:center">
		<a href="http://snapcreek.com/duplicator?free-manual-transfer" target="_blank" class="button button-primary button-large dup-check-it-btn" >
			<?php DUP_Util::_e('Learn More') ?>
		</a>
	</p>
</div>
