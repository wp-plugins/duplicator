<?php
DUP_Util::CheckPermissions('read');

require_once(DUPLICATOR_PLUGIN_PATH . '/assets/js/javascript.php');
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
	a.dup-check-it-btn {box-shadow: 5px 5px 5px 0px #999 !important; font-size: 20px !important; height:45px !important;   padding:7px 30px 0 30px !important;}

	#comparison-table { margin-top:25px; border-spacing: 0px;  width: 100%}
	#comparison-table th { color: #E21906;}
	#comparison-table td, #comparison-table th { font-size: 1.2rem; padding: 12px; }
	#comparison-table .feature-column { text-align: left; width: 46%}
	#comparison-table .check-column { text-align: center; width: 27% }
	#comparison-table tr:nth-child(2n+2) {background-color: #f6f6f6; }
</style>

<div class="dup-pro-area">
	<img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/logo-dpro-300x50-nosnap.png"  /> 
	<div style="font-size:18px; font-style: italic; color:gray">
		<?php DUP_Util::_e('The simplicity of Duplicator') ?>
		<?php DUP_Util::_e('with power for the professional.') ?>
	</div>

	<table id="comparison-table">
		<tr>
			<th class="feature-column">Feature</th>
			<th class="check-column">Free</th>
			<th class="check-column">Professional</th>
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
			<td class="feature-column"><img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/amazon-64.png" style='height:16px; width:16px'  /> Amazon S3 Storage </td>
			<td class="check-column"></td>
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
			<td class="feature-column">Large Package Support</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Multisite Backup</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Email Alerts</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">File Filters</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Custom Search & Replace</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>
		<tr>
			<td class="feature-column">Manual Transfers</td>
			<td class="check-column"></td>
			<td class="check-column"><i class="fa fa-check"></i></td>
		</tr>		
		<tr>
			<td class="feature-column">Active Customer Support</td>
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