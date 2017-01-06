<?php
DUP_Util::CheckPermissions('read');

require_once(DUPLICATOR_PLUGIN_PATH . '/assets/js/javascript.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php');


function DUP_DEBUG_Make_Keys($CTRL) 
{
	$title	= $CTRL['Title'];
	$action = $CTRL['Action'];
	$test   = $CTRL['Test'] ? '' : 'style="display:none"';
	$nonce = wp_create_nonce($action);
	
	$html = <<<EOT
		<div class="keys">
			<input type="hidden" name="action" value="{$action}" />
			<input type="hidden" name="nonce" value="{$nonce}" />
			<span class="result"><i class="fa fa-cube  fa-lg"></i></span>
			<input type='checkbox' id='{$action}' name='{$action}' {$test} /> 
			<label for='{$action}'>{$title}</label> &nbsp;
			<a href="javascript:void(0)" onclick="jQuery(this).closest('form').find('div.params').toggle()">Params</a> |
			<a href="javascript:void(0)" onclick="jQuery(this).closest('form').submit()">Test</a>
		</div>
EOT;
	echo $html;
}

?>
<style>
	div.debug-area {line-height: 26px}
	table.debug-toolbar {width:100%; border: 1px solid silver; border-radius: 5px; background: #dfdfdf; margin: 3px 0 0 -5px }
	table.debug-toolbar td {padding:3px; white-space: nowrap}
	table.debug-toolbar td:last-child {width: 100%}
	
	div.debug-area form {margin: 15px 0 0 0; border-top: 1px solid #dfdfdf; padding-top: 5px}
	div.debug-area div.keys label {font-weight: bold; font-size: 14px; padding-right: 5px }
	div.debug-area div.params label {width:150px; display:inline-block}
	div.debug-area input[type=text] {width:400px}
	
	div.section-hdr {margin: 25px 0 0 0; font-size: 18px; font-weight: bold}
	div.params {display:none}
	i.result-pass {color:green}
	i.result-fail {color:red}
</style>

<div class="wrap dup-wrap dup-support-all">
	<h1><?php _e('Service Debug', 'duplicator'); ?></h1>
    <hr size="1" />
	
	<table class="debug-toolbar">
		<tr>
			<td>
				<span id="results-all"><i class="fa fa-cube fa-lg"></i></span> 
				<input id="test-checkall" type="checkbox" onclick="Duplicator.Debug.CheckAllTests()"> 
			</td>
			<td>
				<input type="button" class="button button-small" value="<?php _e('Run Tests', 'duplicator'); ?>" onclick="Duplicator.Debug.RunTests()" />
				<input type="button" class="button button-small" value="<?php _e('Refresh Tests', 'duplicator'); ?>" onclick="window.location.reload();" />
			</td>
		</tr>
	</table>

	<div class="debug-area">
		<?php
			include_once 'tst.tools.php';
			include_once 'tst.packages.php';	
		?>
	</div>
</div>

<script>	
jQuery(document).ready(function($) 
{
	var STATUS_PASS;
	var STATUS_CHKS;
	var STATUS_RUNS;
	
	Duplicator.Debug.RunTests = function() 
	{
		STATUS_PASS = true;
		STATUS_RUNS = 0;
		STATUS_CHKS = $("div.keys input[type='checkbox']:checked").length;

		$("form.testable").each(function(index) 
		{
			var $form = $(this);
			var $result = $form.find('span.result');
			var $check  = $form.find('div.keys input[type="checkbox"]');
			var input	= $form.serialize();
			
			//validate input
			//console.log($form.serializeArray());

			if ($check.is(':checked')) 
			{
				$('#results-all').html('<i class="fa fa-cog fa-spin fa-fw fa-lg"></i>');
				$result.html('<i class="fa fa-circle-o-notch fa-spin fa-fw fa-lg"></i>');
				
				$.ajax({
					type: "POST",
					url: ajaxurl,
					dataType: "json",
					data: input,
					success: function(data) { Duplicator.Debug.ProcessResult(data, $result) },
					error: function(data) {},
					done: function(data) {}
				});
			}
		});
	}
	
	
	Duplicator.Debug.ProcessResult = function(data, result) 
	{	
		STATUS_RUNS++;
		var status = data.Report.Status || 0;
		
		if (status > 0) {
			result.html('<i class="fa fa-check-circle fa-lg result-pass"></i>');
		} else {
			STATUS_PASS = false;
			result.html('<i class="fa fa-check-circle fa-lg result-fail"></i>');
		}
		
		//Set after all tests have ran
		if (STATUS_RUNS >= STATUS_CHKS) {
			(STATUS_PASS)
				? $('#results-all').html('<i class="fa fa-check-circle fa-lg result-pass"></i>')
				: $('#results-all').html('<i class="fa fa-check-circle fa-lg result-fail"></i>');
		}
	}
	
	Duplicator.Debug.CheckAllTests = function() 
	{
		var checkAll = $('#test-checkall').is(':checked');
		$("div.keys input[type='checkbox']:visible").each(function() {
			(checkAll) 
				? $(this).attr('checked', '1')
				: $(this).removeAttr('checked');
		});
	}
});	
</script>
