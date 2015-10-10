<?php
DUP_Util::CheckPermissions('read');

require_once(DUPLICATOR_PLUGIN_PATH . '/views/javascript.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php');
?>
<style>
    /*================================================
    PAGE-SUPPORT:*/
	div.dup-pro-area {
		padding:10px 70px; max-width:750px; width:90%; margin:auto; text-align: center;
		background: #fff; border-radius:20px;
		box-shadow: inset 0px 0px 67px 20px rgba(241,241,241,1);
	}
    div.dup-compare-area {width:400px;  float:left; border:1px solid #dfdfdf; border-radius:4px; margin:10px; line-height:18px;box-shadow: 0 8px 6px -6px #ccc;}
	div.feature {background: #fff; padding:15px; margin: 2px; text-align: center; min-height: 20px}
	div.feature a {font-size:18px; font-weight: bold;}
	div.dup-compare-area div.feature div.info {display:none; padding:7px 7px 5px 7px; font-style: italic; color: #555; font-size: 14px}
	div.dup-gopro-header {text-align: center; margin: 5px 0 15px 0; font-size:18px; line-height: 30px}
	div.dup-gopro-header b {font-size: 35px}
	a.dup-check-it-btn {box-shadow: 5px 5px 5px 0px #999 !important; font-size: 20px !important; height:50px !important;   padding:10px 40px 0 40px !important;}

	#comparison-table { margin-top:25px; border-spacing: 0px;  width: 100%}
	#comparison-table th { color: #E21906;}
	#comparison-table td, #comparison-table th { font-size: 1.2rem; padding: 15px; }
	#comparison-table .feature-column { text-align: left; width: 46%}
	#comparison-table .check-column { text-align: center; width: 27% }
	#comparison-table tr:nth-child(2n+2) {background-color: #f6f6f6; }
</style>

<script type="text/javascript">var switchTo5x = true;</script>
<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "1a44d92e-2a78-42c3-a32e-414f78f9f484"});</script> 

<div class="dup-pro-area">

	<div>
		<h1 style="font-size:34px; margin-top:50px">
			<img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/logo.png" style='text-align:top; margin:-8px 0; height:40px;'  />
			<?php DUP_Util::_e('Duplicator Professional') ?>
		</h1>
		<h3 style="margin-top:10px; font-size:18px; font-style: italic; color:gray">
			<?php DUP_Util::_e('The simplicity of Duplicator') ?>
			<?php DUP_Util::_e('with power for the professional.') ?>
		</h3>
	</div>

	<table id="comparison-table">
		<tr>
			<th class="feature-column">
				Feature
			</th>
			<th class="check-column">
				Free
			</th>
			<th class="check-column">
				Professional
			</th>
		</tr>
		<tr>
			<td class="feature-column">Backup Files & Database</td>
			<td class="check-column"><i class="fa fa-check"></i></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Directory Filters</td>
			<td class="check-column"><i class="fa fa-check"></i></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Database Table Filters</td>
			<td class="check-column"><i class="fa fa-check"></i></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Migration Wizard</td>
			<td class="check-column"><i class="fa fa-check"></i></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column"><img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/dropbox-64.png" style='height:16px; width:16px'  /> Dropbox Storage </td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column"><img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/google_drive_64px.png" style='height:16px; width:16px'  /> Google Drive Storage</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column"><img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/ftp-64.png" style='height:16px; width:16px'  /> Remote FTP Storage</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>			
		<tr>
			<td class="feature-column">Scheduled Backups</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>			
		<tr>
			<td class="feature-column">Enhanced Package Engine</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Email Alerts</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Custom Templates</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>			
		<tr>
			<td class="feature-column">File Filters</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Customer Support</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>			
	</table>

	<br style="clear:both" />
	<p style="text-align:center">
		<a href="http://snapcreek.com/duplicator?free-go-pro" target="_blank" class="button button-primary button-large dup-check-it-btn" >
			<?php DUP_Util::_e('Check It Out!') ?>
		</a>
	</p>
	<br/><br/>
</div>
<br/><br/>

<script type="text/javascript">
    jQuery(document).ready(function ($) {

    });
</script>