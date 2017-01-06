<?php
DUP_Util::CheckPermissions('read');

require_once(DUPLICATOR_PLUGIN_PATH . '/assets/js/javascript.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php');


function DUP_DEBUG_Make_Keys($key, $test = true) 
{
	$nonce = wp_create_nonce($key);
	$test = $test ? '' : 'style="display:none"';
	$html = <<<EOT
		<div class="keys">
			<input type="hidden" name="action" value="{$key}" />
			<input type="hidden" name="nonce" value="{$nonce}" />
			<span class="result"><i class="fa fa-cube  fa-lg"></i></span>
			<input type='checkbox' id='{$key}' name='{$key}' {$test} /> 
			<label for='{$key}'>{$key}</label>
			<a href="javascript:void(0)" onclick="jQuery(this).closest('form').submit()">[Manual Test]</a>
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
	i.result-pass {color:green}
	i.result-fail {color:red}
</style>

<div class="wrap dup-wrap dup-support-all">
	<h1><?php _e('Service Debug', 'duplicator'); ?></h1>
    <hr size="1" />
	
	<table class="debug-toolbar">
		<tr>
			<td>
				<i class="fa fa-cube fa-lg"></i> <input id="test-checkall" type="checkbox" onclick="Duplicator.Debug.CheckAllTests()"> 
			</td>
			<td><input type="button" class="button button-small" value="<?php _e('Run Tests', 'duplicator'); ?>" onclick="Duplicator.Debug.RunTests()" /></td>
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
	Duplicator.Debug.RunTests = function() 
	{
		$("form.testable").each(function(index) 
		{
			var $form = $(this);
			var $result  = $form.find('span.result');
			var $title  = $form.find('div.keys label');
			var $check  = $form.find('div.keys input[type="checkbox"]');
			var input	= $form.serialize();

			if ($check.is(':checked')) 
			{
				console.log('==============================================');
				console.log('Starting Test: ' + $title.text() );
				console.log('Passing Data: ');
				console.log($form.serializeArray());
				
				$result.html('<i class="fa fa-circle-o-notch fa-spin fa-fw fa-lg"></i>');
				
				$.ajax({
					type: "POST",
					url: ajaxurl,
					dataType: "json",
					data: input,
					success: function(data) { Duplicator.Debug.ProcessResult(data, $result) },
					error: function(data) {console.log('error')},
					done: function(data) {console.log('done')}
				});
			}
		});
		
	}
	
	
	Duplicator.Debug.ProcessResult = function(data, result) 
	{	
		var status = data.Report.Status || 0;
		console.log(status);
		if (status > 0) {
			result.html('<i class="fa fa-check-circle fa-lg result-pass"></i>');
		} else {
			result.html('<i class="fa fa-check-circle fa-lg result-fail"></i>');
		}
		console.log('Result:');
		console.log(data);
	}
	
	Duplicator.Debug.CheckAllTests = function() 
	{
		var checkAll = $('#test-checkall').is(':checked');
		$("div.keys input[type='checkbox']").each(function() {
			(checkAll) 
				? $(this).attr('checked', '1')
				: $(this).removeAttr('checked');
		});
	}
});	
</script>
