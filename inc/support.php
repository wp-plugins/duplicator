<?php
	require_once('javascript.php'); 
	require_once('inc.header.php');
?>
<style>
/*================================================
PAGE-SUPPORT:*/
div.dup-support-all {font-size:13px; line-height:20px}
h2.dup-support-headers {margin:-20px 0px -5px 0px; font-weight:bold; font-size:1.5em}
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
img#dup-support-approved { -webkit-animation:approve-keyframe 12s 1s infinite alternate backwards}
form#dup-donate-form input {opacity:0.7;}
form#dup-donate-form input:hover {opacity:1.0;}
img#dup-img-5stars {opacity:0.7;}
img#dup-img-5stars:hover {opacity:1.0;}
</style>
<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "1a44d92e-2a78-42c3-a32e-414f78f9f484"}); </script> 

<div class="wrap dup-wrap dup-support-all">

	<!-- h2 required here for general system messages -->
	<h2 style='display:none'></h2>

	<?php duplicator_header(__("Support", 'wpduplicator') ) ?>

	<div style="width:850px; margin:auto; margin-top: 20px">
		<table>
			<tr>
				<td valign="top" class="dup-drop-cap">
				<?php 
					_e("Created for Admins, Developers and Designers the Duplicator will streamline your workflows and help you quickly clone a WordPress application.  If you run into an issue please read through the", 'wpduplicator');
					printf(" <a href='http://lifeinthegrid.com/duplicator-docs' target='_blank'>%s</a> ", __("knowledgebase", 'wpduplicator'));
					_e('in detail as it will have answers to most of your questions and issues.', 'wpduplicator')
				?>
				</td>
				<td>
					<a href="http://lifeinthegrid.com/labs/duplicator" target="_blank">
						<img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/logo-box.png" style='text-align:top; margin:-10px 0px 0px 20px'  />
					</a>
				</td>
			</tr>
		</table><br/>
		
		
		<!--  =================================================
		NEED HELP?
		==================================================== -->
		<h2 class="dup-support-headers" style="margin-top:-35px"><?php _e('Need Help?', 'wpduplicator') ?></h2>

		<!-- HELP LINKS -->
		<div class="dup-support-hlp-area">
			<table class="dup-support-hlp-hdrs">
				<tr >
					<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/books.png" /></td>
					<td><?php _e('Knowledgebase', 'wpduplicator') ?></td>
				</tr>
			</table>
			<div class="dup-support-hlp-txt">
				<?php  _e('Please review the online documentation for complete usage of the plugin.', 'wpduplicator');?>
				<select id="dup-support-kb-lnks" style="margin-top:10px; font-size:14px; min-width: 170px">
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
					<td><img id="dup-support-approved" src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/approved.png"  /></td>
					<td><?php _e('Approved Hosting', 'wpduplicator') ?></td>
				</tr>
			</table>
			<div class="dup-support-hlp-txt">
				<?php _e('Need a solid hosting provider that will work well with the Duplicator?', 'wpduplicator'); ?>
				<div class="dup-support-txts-links" style="margin-top:10px">
					<?php printf("<a href='http://lifeinthegrid.com/duplicator-hosts' target='_blank'>%s</a>", __("Approved Hosting Services", 'wpduplicator')); ?>
				</div>
			</div>
		</div>
		

		<!-- ONLINE SUPPORT -->
		<div class="dup-support-hlp-area">
			<table class="dup-support-hlp-hdrs">
				<tr >
					<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/support.png" /></td>
					<td><?php _e('Online Support', 'wpduplicator') ?></td>
				</tr>
			</table>
			<div class="dup-support-hlp-txt">
				<?php _e("Online support  is available for issues not covered in the knowledgebase." , 'wpduplicator');	?>
				<div class="dup-support-txts-links">
					<a href="http://www.freelancer.com/affiliates/lifeinthegrid/" target="_blank"><?php _e('Professional', 'wpduplicator') ?></a> &nbsp; | &nbsp;
					<a href="javascript:void(0)" onclick="Duplicator.ShowSupportDialog()"><?php _e('Basic', 'wpduplicator') ?></a>
					
				</div>	
				<i style="font-size:11px">
					<?php _e('Pro: Hire at Freelancer.com', 'wpduplicator') ?> <br/>
					<?php _e('Basic: 3-5 business days', 'wpduplicator') ?> 
				 
				</i>
			</div>
		</div> <br style="clear:both" /><br/><br/><br/>
		
		
		
		
		<!--  ==================================================
		SUPPORT DUPLICATOR
		==================================================== -->
		<h2 class="dup-support-headers"><?php _e('Support Duplicator', 'wpduplicator') ?></h2>
		
		
		<!-- PARTNER WITH US -->
		<div class="dup-support-give-area">
			<table class="dup-support-hlp-hdrs">
				<tr >
					<td style="height:30px; text-align: center;">
						<img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/check.png" align="left" />
						<span style="display: inline-block; margin-top: 5px"><?php _e('Partner with Us', 'wpduplicator') ?></span>
					</td>
				</tr>
			</table>
			<table style="text-align: center;width:100%; font-size:11px; font-style:italic; margin-top:15px">
				<tr>
					<td class="dup-support-grid-img" style="padding-left:40px">
						<div class="dup-support-cell" onclick="jQuery('#dup-donate-form').submit()">
							<form id="dup-donate-form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" > 
								<input name="cmd" type="hidden" value="_s-xclick" /> 
								<input name="hosted_button_id" type="hidden" value="EYJ7AV43RTZJL" /> 
								<input alt="PayPal - The safer, easier way to pay online!" name="submit" src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/paypal.png" type="image" />
								<div style="margin-top:-5px"><?php _e('Keep Active and Online', 'wpduplicator') ?></div>
								<img src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" /> 
							</form>
						</div>
					</td>
					<td style="padding-right:40px;" valign="top">
						<a href="http://wordpress.org/extend/plugins/duplicator" target="_blank"><img id="dup-img-5stars" src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/5star.png" /></a>
						<div  style="margin-top:-4px"><?php _e('Leave 5 Stars', 'wpduplicator') ?></div></a>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<a href="http://lifeinthegrid.com/duplicator-survey" target="_blank">
						<?php _e('Take A Quick 60 Second Survey', 'wpduplicator') ?></a>
					</td>
				</tr>
			</table>
		</div> 
		 

		<!-- SPREAD THE WORD  -->
		<div class="dup-support-give-area">
			<table class="dup-support-hlp-hdrs">
				<tr>
					<td style="height:30px; text-align: center;">
						<img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/mega.png" align="left" />
						<span style="display: inline-block; margin-top: 5px"><?php _e('Spread the Word', 'wpduplicator') ?></span>
					</td>
				</tr>
			</table>
			<div class="dup-support-hlp-txt">
				<?php
					$title = __("Duplicate Your WordPress", 'wpduplicator');
					$summary = __("Rapid WordPress Duplication by LifeInTheGrid.com", 'wpduplicator');
					$share_this_data = "st_url='" . DUPLICATOR_HOMEPAGE . "' st_title='{$title}' st_summary='{$summary}'";
				?>
				<div style="width:100%; padding:20px 10px 0px 10px" align="center">
					<span class='st_facebook_vcount' displayText='Facebook' <?php echo $share_this_data; ?> ></span>
					<span class='st_twitter_vcount' displayText='Tweet' <?php echo $share_this_data; ?> ></span>
					<span class='st_googleplus_vcount' displayText='Google +' <?php echo $share_this_data; ?> ></span>
					<span class='st_linkedin_vcount' displayText='LinkedIn' <?php echo $share_this_data; ?> ></span>
					<span class='st_email_vcount' displayText='Email' <?php echo $share_this_data; ?> ></span>
				</div><br/>
			</div>
		</div>
		<br style="clear:both" /><br/>
		
		<!--  ========================
		VISIT US -->
		
		<div style="width:100%; padding:10px 10px 0px 10px; font-size:11px; font-style: italic; color:gray" align="center">
			<a href="http://lifeinthegrid.com" target="_blank">LifeInTheGrid</a> &nbsp; | &nbsp;
			<a href="http://lifeinthegrid.com/labs" target="_blank"><?php _e('Labs', 'wpduplicator') ?></a> &nbsp; | &nbsp; 
			<a href="http://www.youtube.com/lifeinthegridtv" target="_blank">YouTube</a>
		</div>
		
	</div>
</div><br/><br/><br/><br/>

<!-- ==========================================
DIALOG: QUICK PATH -->
<div id="dup-dlg-basic-support" title="<?php _e('Basic Support', 'wpduplicator'); ?>" style="display:none; width:535; height:300px; line-height: 18px">
	

	<?php _e("This is a free courtesy we offer to the WordPress community, but it is clear that some people do abuse the service. Please put time and thought into what you’re asking and do not ask every question that comes to mind without trying to find an answer first.  Thank you for being thoughtful of this free service!", 'wpduplicator');	?>

	<br/><br/><b><?php _e("Support Tips", 'wpduplicator');	?></b><br/>

	- <?php _e("If your issue is time sensitive please use the Professional service link", 'wpduplicator');	?> <br/>
	- <?php _e("Read all knowledgebase articles before asking a question", 'wpduplicator');	?> <br/>
	- <?php _e("Check the WordPress forums and Google for similar issues", 'wpduplicator');	?> <br/>
	- <?php _e("Read the Duplicator log files for clues", 'wpduplicator');	?> <br/>
	- <?php _e("Contact your hosting provider for permission and timeout issues", 'wpduplicator');	?> <br/>
	

	
	<div style="padding: 20px 0px 0px 0px; text-align: right">
		<a href="https://support.lifeinthegrid.com" target="_blank"><?php _e('Continue with Basic Support', 'wpduplicator') ?></a>
	</div>
</div>


<script type="text/javascript">
jQuery(document).ready(function($) {
		
		/*	----------------------------------------
		 *	METHOD: Shows the 'Basic Support' dialog */
		Duplicator.ShowSupportDialog = function() {
			jQuery("#dup-dlg-basic-support").dialog("open");
			return false;
		}

		//ATTACHED EVENTS
		jQuery('#dup-support-kb-lnks').change(function() {
			if (jQuery(this).val() != "null") 
				window.open(jQuery(this).val())
		});
		
		//INIT CALLS
		jQuery("#dup-dlg-basic-support").dialog({autoOpen:false, height:350, width:500, create:Duplicator.UI.CreateDialog, close:Duplicator.UI.CloseDialog });
});
</script>