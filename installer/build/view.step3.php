<?php
	// Exit if accessed directly
	if (! defined('DUPLICATOR_INIT')) {
		$_baseURL =  strlen($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
		$_baseURL =  "http://" . $_baseURL;
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $_baseURL");
		exit; 
	}
?>
<script type="text/javascript">		
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
			<td>1. <a href="javascript:void(0)" onclick="$('#dup-step3-install-report').toggle(400)">Install Report</a>
			</td>
			<td>
				<i id="dup-step3-install-report-count">
					<b>Errors:</b>
					<span data-bind="with: status.step1">Deploy (<span data-bind="text: query_errs"></span>)</span> &nbsp;
					<span data-bind="with: status.step2">Update (<span data-bind="text: err_all"></span>)</span> &nbsp; &nbsp;
					<span data-bind="with: status.step2" style="color:#888"><b>Warnings:</b> (<span data-bind="text: warn_all"></span>)</span>
				</i>
			</td>
		</tr>	
		<tr>
			<td style="width:170px">
				2. <a href='<?php echo rtrim($_POST['url_new'], "/"); ?>/wp-admin/options-permalink.php' target='_blank'> Save Permalinks</a> 
			</td>
			<td><i>Updates URL rewrite rules in .htaccess (requires login)</i></td>
		</tr>	
		<tr>
			<td>3. <a href='<?php echo $_POST['url_new']; ?>' target='_blank'>Test Site</a></td>
			<td><i>Validate all pages, links images and plugins</i></td>
		</tr>		
		<tr>
			<td>4. <a href="javascript:void(0)" onclick="Duplicator.removeInstallerFiles('<?php echo $_POST['package_name'] ?>')">File Cleanup</a></td>
			<td><i>Removes all installer files (requires login)</i></td>
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
					<a href="javascript:void(0);" onclick="$('#dup-step3-errs-create').toggle(400)">Step1: Deploy Errors (<span data-bind="text: query_errs"></span>)</a><br/>
				</td>
				<td data-bind="with: status.step2">
					<a href="javascript:void(0);" onclick="$('#dup-step3-errs-upd').toggle(400)">Step2: Update Errors (<span data-bind="text: err_all"></span>)</a>
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
		<a href="installer.php?help=1#troubleshoot" target="_blank">Troubleshoot</a> | 
		<a href='http://support.lifeinthegrid.com/knowledgebase.php' target='_blank'>FAQs</a> | 
		<a href='http://lifeinthegrid.com/duplicator' target='_blank'>Support</a> | 
		<a href='http://lifeinthegrid.com/partner/' target='_blank'>Donate</a>
	</div><br/>
</form>

<script type="text/javascript">
	MyViewModel = function() { 
		this.status = <?php echo urldecode($_POST['json']); ?>;
		var errorCount =  this.status.step2.err_all || 0;
		(errorCount >= 1 )
			? $('#dup-step3-install-report-count').css('color', '#BE2323')
			: $('#dup-step3-install-report-count').css('color', '#197713')
	};
	ko.applyBindings(new MyViewModel());
</script>
 
 