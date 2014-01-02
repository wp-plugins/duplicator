
<script type="text/javascript">	
	/** **********************************************
	* METHOD: Opens the tips dialog */	
	Duplicator.dlgTips = function() {
		$("#dup-step3-dialog").dialog({
			height:600, width:700, modal: true,
			position:['center', 150],
			buttons: {Close: function() {$(this).dialog( "close" );}}
		});	
	};
	
	/** **********************************************
	* METHOD: Posts to page to remove install files */	
	Duplicator.removeInstallerFiles = function(package_name) {
		var msg = "Delete all installer files now? \n\nThis will remove the page you are now viewing.\nThe page will stay active until you navigate away.";
		if (confirm(msg)) {
			var nurl = '<?php echo rtrim($_POST['url_new'], "/"); ?>/wp-admin/admin.php?page=duplicator-tools&tab=cleanup&action=installer&package=' + package_name;
			window.open(nurl, "_blank");
		}
	};
</script>


<!-- =========================================
VIEW: STEP 3- INPUT -->
<form id='dup-step3-input-form' method="post" class="content-form" style="line-height:20px">
	<input type="hidden" name="url_new" id="url_new" value="<?php echo rtrim($_POST['url_new'], "/"); ?>" />	
	<div class="dup-logfile-link"><a href="installer-log.txt" target="_blank">installer-log.txt</a></div>
	<h3>Step 3: Test Site</h3>
	<hr size="1" /><br />
	
	<div class="title-header">
		<div class="dup-step3-final-title">IMPORTANT FINAL STEPS!</div>
	</div>
		
	<table class="dup-step3-final-step">
		<tr>
			<td>1. <a href="javascript:void(0)" onclick="$('#dup-step3-install-report').toggle(400)">Read Install Report</a>
			</td>
			<td>
				<i style='font-size:11px; color:#BE2323'>
					<span data-bind="with: status.step1">Deploy Errors: <span data-bind="text: query_errs"></span></span> &nbsp; &nbsp;
					<span data-bind="with: status.step2">Update Errors: <span data-bind="text: err_all"></span></span> &nbsp; &nbsp;
					<span data-bind="with: status.step2">Warnings: <span data-bind="text: warn_all"></span></span>
				</i>
			</td>
		</tr>	
		<tr>
			<td style="width:170px">
				2. <a href='<?php echo rtrim($_POST['url_new'], "/"); ?>/wp-admin/options-permalink.php' target='_blank'> Resave Permalinks</a> 
			</td>
			<td><i style='font-size:11px'>Updates URL rewrite rules in .htaccess (requires login)</i></td>
		</tr>	
		<tr>
			<td>3. <a href='<?php echo $_POST['url_new']; ?>' target='_blank'>Test Entire Site</a></td>
			<td><i style='font-size:11px'>Validate all pages, links images and plugins</i></td>
		</tr>		
		<tr>
			<td>4. <a href="javascript:void(0)" onclick="Duplicator.removeInstallerFiles('<?php echo $_POST['package_name'] ?>')">File Cleanup</a></td>
			<td><i style='font-size:11px'>Removes all installer files (requires login)</i></td>
		</tr>	
	</table><br/>
	
	<div class="dup-step3-go-back">
		<i style='font-size:11px'>To re-install <a href="javascript:history.go(-2)">start over at step 1</a>.</i><br/>
		<i style="font-size:11px;">The .htaccess file was reset.  Resave plugins that write to this file.</i>
	</div>


	<!-- ========================
	INSTALL REPORT -->
	<div id="dup-step3-install-report" style='display:none'>
		<table class='dup-step3-report-results' style="width:100%">
			<tr><th colspan="4">Database Results</th></tr>
			<tr style="font-weight:bold">
				<td style="width:150px"></td>
				<td>Tables</td>
				<td>Rows</td>
				<td>Cells</td>
			</tr>
			<tr data-bind="with: status.step1">
				<td>Created</td>
				<td><span data-bind="text: table_count"></span></td>
				<td><span data-bind="text: table_rows"></span></td>
				<td>n/a</td>
			</tr>	
			<tr data-bind="with: status.step2">
				<td>Scanned</td>
				<td><span data-bind="text: scan_tables"></span></td>        
				<td><span data-bind="text: scan_rows"></span></td>
				<td><span data-bind="text: scan_cells"></span></td>
			</tr>
			<tr data-bind="with: status.step2">
				<td>Updated</td>
				<td><span data-bind="text: updt_tables"></span></td>        
				<td><span data-bind="text: updt_rows"></span></td>
				<td><span data-bind="text: updt_cells"></span></td>
			</tr>
		</table>
		
		<table class='dup-step3-report-errs' style="width:100%; border-top:none">
			<tr><th colspan="4">Errors &amp; Warnings <br/> <i style="font-size:10px; font-weight:normal">(click links below to view details)</i></th></tr>
			<tr>
				<td data-bind="with: status.step1">
					<a href="javascript:void(0);" onclick="$('#dup-step3-errs-create').toggle(400)">Deploy Errors (<span data-bind="text: query_errs"></span>)</a><br/>
				</td>
				<td data-bind="with: status.step2">
					<a href="javascript:void(0);" onclick="$('#dup-step3-errs-upd').toggle(400)">Update Errors (<span data-bind="text: err_all"></span>)</a>
				</td>
				<td data-bind="with: status.step2">
					<a href="#dup-step2-errs-warn-anchor" onclick="$('#dup-step3-warnlist').toggle(400)">General Warnings (<span data-bind="text: warn_all"></span>)</a>
				</td>
			</tr>
			<tr><td colspan="4"></td></tr>
		</table>
		
		
		<div id="dup-step3-errs-create" class="dup-step3-err-msg">
		
			<b data-bind="with: status.step1">STEP 1: DEPLOY ERRORS (<span data-bind="text: query_errs"></span>)</b><br/>
			<div class="info">Queries that error during the deploy process are logged to the <a href="installer-log.txt" target="_blank">install-log.txt</a> file.  
			To view the error result look under the section titled 'DATABASE RESULTS'.  If errors are present they will be marked with '**ERROR**'. <br/><br/>  For errors titled
			'Query size limit' you will need to manually post the values or update your mysql server with the max_allowed_packet setting to handle larger payloads.
			If your on a hosted server you will need to contact the server admin, for more details see: https://dev.mysql.com/doc/refman/5.5/en/packet-too-large.html. <br/><br/>
			</div>
			
		</div>
		

		<div id="dup-step3-errs-upd" class="dup-step3-err-msg">
		
			<!-- MYSQL QUERY ERRORS -->
			<b data-bind="with: status.step2">STEP2: UPDATE ERRORS (<span data-bind="text: errsql_sum"></span>) </b><br/>
			<div class="info">Errors that show here are the result of queries that could not be performed.</div>
			<div class="content">
				<div data-bind="foreach: status.step2.errsql"><div data-bind="text: $data"></div></div>
				<div data-bind="visible: status.step2.errsql.length == 0">No MySQL query errors found</div>
			</div>
			
			<!-- TABLE KEY ERRORS -->
			<b data-bind="with: status.step2">TABLE KEY ERRORS (<span data-bind="text: errkey_sum"></span>)</b><br/>
			<div class="info">
				A primary key is required on a table to efficiently run the update engine. Below is a list of tables and the rows that will need to 
				be manually updated.  Use the query below to find the data.<br/>
				<i>SELECT @row := @row + 1 as row, t.* FROM some_table t, (SELECT @row := 0) r</i>
			</div>
			<div class="content">
				<div data-bind="foreach: status.step2.errkey"><div data-bind="text: $data"></div></div>
				<div data-bind="visible: status.step2.errkey.length == 0">No missing primary key errors</div>
			</div>
			
			<!-- SERIALIZE ERRORS -->
			<b data-bind="with: status.step2">SERIALIZATION ERRORS  (<span data-bind="text: errser_sum"></span>)</b><br/>
			<div class="info">
				Use the SQL below to display data that may have not been updated correctly during the serialization process.
			</div>
			<div class="content">
				<div data-bind="foreach: status.step2.errser"><div data-bind="text: $data"></div></div>
				<div data-bind="visible: status.step2.errser.length == 0">No serialization errors found</div>
			</div>			
			
		</div>
		
		
		<!-- WARNINGS-->
		<div id="dup-step3-warnlist" class="dup-step3-err-msg">
			<a href="#" id="dup-step2-errs-warn-anchor"></a>
			<b>GENERAL WARNINGS</b><br/>
			<div class="info">
				The following is a list of warnings that may need to be fixed in order to finalize your setup.  For more details about
				warnings see the <a href="http://codex.wordpress.org/" target="_blank">wordpress codex.</a>.
			</div>
			<div class="content">
				<div data-bind="foreach: status.step2.warnlist">
					 <div data-bind="text: $data"></div>
				</div>
				<div data-bind="visible: status.step2.warnlist.length == 0">
					No warnings found
				</div>
			</div>
		</div><br/>
		
		
	</div><br/><br/>



		
	<div class='dup-step3-connect'>
		Please consider <a href='http://lifeinthegrid.com/partner/' target='_blank'>Partnering or a Donation</a>! <br/>
		<a href="javascript:void(0)" onclick="Duplicator.dlgTips()">Troubleshoot</a> | 
		<a href='http://support.lifeinthegrid.com/knowledgebase.php' target='_blank'>FAQs</a> | 
		<a href='http://support.lifeinthegrid.com' target='_blank'>Support</a>
	</div><br/>
</form>


 <!-- =========================================
DIALOG: TROUBLSHOOTING DIALOG -->
<div id="dup-step3-dialog" title="Troubleshooting Tips" style="display:none">
	<div style="padding: 0px 10px 10px 10px;">		
		<b>Common Quick Fix Issues:</b>
		<ul>
			<li>Use an <a href='http://lifeinthegrid.com/duplicator-certified' target='_blank'>approved hosting provider</a></li>
			<li>Validate directory and file permissions (see below)</li>
			<li>Validate web server configuration file (see below)</li>
			<li>Clear your browsers cache</li>
			<li>Deactivate and reactivate all plugins</li>
			<li>Resave a plugins settings if it reports errors</li>
			<li>Make sure your root directory is empty</li>
		</ul>

		<b>Permissions:</b><br/> 
		Not all operating systems are alike.  Therefore, when you move a package (zip file) from one location to another the file and directory permissions may not always stick.  If this is the case then check your WordPress directories and make sure it's permissions are set to 755. For files make sure the permissions are set to 644 (this does not apply to windows servers).   Also pay attention to the owner/group attributes.  For a full overview of the correct file changes see the <a href='http://codex.wordpress.org/Hardening_WordPress#File_permissions' target='_blank'>WordPress permissions codex</a>
		<br/><br/>

		<b>Web server configuration files:</b><br/>
		For Apache web server the root .htaccess file was copied to .htaccess.orig. A new stripped down .htaccess file was created to help simplify access issues.  For IIS web server the web.config file was copied to web.config.orig, however no new web.config file was created.  If you have not altered this file manually then resaving your permalinks and resaving your plugins should resolve most all changes that were made to the root web configuration file.   If your still experiencing issues then open the .orig file and do a compare to see what changes need to be made. <br/><br/><b>Plugin Notes:</b><br/> It's impossible to know how all 3rd party plugins function.  The Duplicator attempts to fix the new install URL for settings stored in the WordPress options table.   Please validate that all plugins retained there settings after installing.   If you experience issues try to bulk deactivate all plugins then bulk reactivate them on your new duplicated site. If you run into issues were a plugin does not retain its data then try to resave the plugins settings.
		<br/><br/>
		 
		 <b>Cache Systems:</b><br/>
		 Any type of cache system such as Super Cache, W3 Cache, etc. should be emptied before you create a package.  Another alternative is to include the cache directory in the directory exclusion path list found in the options dialog. Including a directory such as \pathtowordpress\wp-content\w3tc\ (the w3 Total Cache directory) will exclude this directory from being packaged. In is highly recommended to always perform a cache empty when you first fire up your new site even if you excluded your cache directory.
		 <br/><br/>
		 
		 <b>Trying Again:</b><br/>
		 If you need to retry and reinstall this package you can easily run the process again by deleting all files except the installer.php and package file and then browse to the installer.php again.
		 <br/><br/>
		 
		 <b>Additional Notes:</b><br/>
		 If you have made changes to your PHP files directly this might have an impact on your duplicated site.  Be sure all changes made will correspond to the sites new location. 
		 Only the package (zip file) and the installer.php file should be in the directory where you are installing the site.  Please read through our knowledge base before submitting any issues. 
		 If you have a large log file that needs evaluated please email the file, or attach it to a help ticket.
		 <br/><br/>
		 
		 <b>Approved Hosts:</b><br/>
		 Please check out our <a href='http://lifeinthegrid.com/duplicator-certified' target='_blank'>approved hosts page</a> as it has a list of hosting providers and themes that have been tested
		 successfully with the Duplicator plugin.<br/><br/>
	</div>
</div>

<script type="text/javascript">
	MyViewModel = function() { this.status = <?php echo urldecode($_POST['json']); ?>;};
	ko.applyBindings(new MyViewModel());
</script>
 
 