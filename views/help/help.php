<?php
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/javascript.php'); 
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php'); 
?>
<style>
div.dup-support-all {font-size:13px; line-height:20px}
div.dup-support-txts-links {width:100%;font-size:14px; font-weight:bold; line-height:26px; text-align:center}
div.dup-support-hlp-area {width:265px; height:175px; float:left; border:1px solid #dfdfdf; border-radius:4px; margin:6px; line-height:18px;box-shadow: 0 8px 6px -6px #ccc;}
table.dup-support-hlp-hdrs {border-collapse:collapse; width:100%; border-bottom:1px solid #dfdfdf}
table.dup-support-hlp-hdrs {background-color:#efefef;}
table.dup-support-hlp-hdrs td {
	padding:2px; height:52px;
	font-weight:bold; font-size:17px;
	background-image:-ms-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
	background-image:-moz-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
	background-image:-o-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
	background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #FFFFFF), color-stop(1, #DEDEDE));
	background-image:-webkit-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
	background-image:linear-gradient(to bottom, #FFFFFF 0%, #DEDEDE 100%);
}
table.dup-support-hlp-hdrs td img{margin-left:7px}
div.dup-support-hlp-txt{padding:10px 4px 4px 4px; text-align:center}
div.dup-support-give-area {width:400px; height:185px; float:left; border:1px solid #dfdfdf; border-radius:4px; margin:10px; line-height:18px;box-shadow: 0 8px 6px -6px #ccc;}
div.dup-spread-word {display:inline-block; border:1px solid red; text-align:center}
@-webkit-keyframes approve-keyframe  { 
    from {-webkit-transform:rotateX(0deg) rotateY(0deg) rotateZ(0deg);}
    to {-webkit-transform:rotateX(0deg) rotateY(0deg) rotateZ(30deg);}
}
</style>


<div class="wrap dup-wrap dup-support-all">

	<!-- h2 required here for general system messages -->
	<h2 style='display:none'></h2>

	<?php duplicator_header(__("Help", 'wpduplicator') ) ?>
	<hr size="1" />

	<div style="width:850px; margin:auto; margin-top: 20px">
		<table style="width:825px">
			<tr>
				<td style="width:70px"><i class="fa fa-question-circle fa-5x"></i></td>
				<td valign="top" style="padding-top:10px; font-size:14px">
				<?php 
					_e("Migrating WordPress is a complex process and the underlying logic to make all the magic happen smoothly does not always happen quickly with every site.  Each WordPress site has its own unique factors and with over 30,000 plugins and a very complex server eco-system some migrations may run into issues.   Therefore the Duplicator has also provided a very detailed and free ", 'wpduplicator');
					printf(" <a href='http://lifeinthegrid.com/duplicator-docs' target='_blank'>%s</a> ", __("knowledgebase", 'wpduplicator'));
					_e('that can help you with many common issues.', 'wpduplicator')
				?>
				</td>
				
			</tr>
		</table>
		<br/><br/>

		<!-- HELP LINKS -->
		<div class="dup-support-hlp-area">
			<table class="dup-support-hlp-hdrs">
				<tr>
					<td>&nbsp; <i class="fa fa-cube fa-2x"></i></td>
					<td><?php _e('Knowledgebase', 'wpduplicator') ?></td>
				</tr>
			</table>
			<div class="dup-support-hlp-txt">
				<?php  _e('Complete online documentation!', 'wpduplicator');?>
				<select id="dup-support-kb-lnks" style="margin-top:18px; font-size:14px; min-width: 170px">
					<option> <?php _e('Choose A Section', 'wpduplicator') ?> </option>
					<option value="http://lifeinthegrid.com/duplicator-quick"><?php _e('Quick Start', 'wpduplicator') ?></option>
					<option value="http://lifeinthegrid.com/duplicator-guide"><?php _e('User Guide', 'wpduplicator') ?></option>
					<option value="http://lifeinthegrid.com/duplicator-faq"><?php _e('FAQs', 'wpduplicator') ?></option>
					<option value="http://lifeinthegrid.com/duplicator-log"><?php _e('Change Log', 'wpduplicator') ?></option>
					<option value="http://lifeinthegrid.com/labs/duplicator"><?php _e('Product Page', 'wpduplicator') ?></option>
				</select>
			</div>
		</div>

		<!-- APPROVED HOSTING -->
		<div class="dup-support-hlp-area">
			<table class="dup-support-hlp-hdrs">
				<tr >
					<td>&nbsp; <i class="fa fa-bolt fa-2x"></i></td>
					<td><?php _e('Approved Hosting', 'wpduplicator') ?></td>
				</tr>
			</table>
			<div class="dup-support-hlp-txt">
				<?php _e('Servers that work with Duplicator!', 'wpduplicator'); ?>
				<br/><br/>
				<div class="dup-support-txts-links">
					<button class="button button-primary button-large" onclick="window.open('http://lifeinthegrid.com/duplicator-hosts', 'litg');"><?php _e('Get Hosting!', 'wpduplicator') ?></button> &nbsp; 
				</div>
			</div>
		</div>
		
		<!-- ONLINE SUPPORT -->
		<div class="dup-support-hlp-area">
			<table class="dup-support-hlp-hdrs">
				<tr>
					<td>&nbsp; <i class="fa fa-lightbulb-o fa-2x"></i></td>
					<td><?php _e('Online Support', 'wpduplicator') ?></td>
				</tr>
			</table>
			<div class="dup-support-hlp-txt">
				<?php _e("Work with IT Profressionals!" , 'wpduplicator');	?> 
				<br/><br/>
				
				<div class="dup-support-txts-links">
					<button class="button  button-primary button-large" onclick="Duplicator.OpenSupportWindow(); return false;"><?php _e('Get Support Now!', 'wpduplicator') ?></button> &nbsp; 
				</div>	
			</div>
		</div> <br style="clear:both" /><br/><br/><br/>
		
	</div>
</div><br/><br/><br/><br/>

<script type="text/javascript">
jQuery(document).ready(function($) {
	
	Duplicator.OpenSupportWindow = function() {
		var url = 'http://lifeinthegrid.com/duplicator/resources/';
		window.open(url, 'litg');
	}

	//ATTACHED EVENTS
	jQuery('#dup-support-kb-lnks').change(function() {
		if (jQuery(this).val() != "null") 
			window.open(jQuery(this).val())
	});
		
});
</script>