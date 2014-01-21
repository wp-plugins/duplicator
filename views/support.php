<?php
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/javascript.php'); 
	require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php'); 
?>
<style>
/*================================================
PAGE-SUPPORT:*/
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
	<hr size="1" />

	<div style="width:850px; margin:auto; margin-top: 20px">
		<table style="width:825px">
			<tr>
				<td valign="top" style="padding-top:10px; font-size:14px">
				<?php 
					_e("Created for Admins, Developers and Designers the Duplicator will streamline your workflows and help you quickly clone a WordPress application.  If you run into an issue please read through the", 'wpduplicator');
					printf(" <a href='http://lifeinthegrid.com/duplicator-docs' target='_blank'>%s</a> ", __("knowledgebase", 'wpduplicator'));
					_e('in detail for many of the quick and common answers.', 'wpduplicator')
				?>
				</td>
				<td >
					<a href="http://lifeinthegrid.com/labs/duplicator" target="_blank">
						<img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/logo-box.png" style='text-align:top; margin:-15px 0px 0px 0px'  />
					</a>
				</td>
			</tr>
		</table><br/>
		
		
		<!--  =================================================
		NEED HELP?
		==================================================== -->
		<h2><?php _e('Need Help?', 'wpduplicator') ?></h2>

		<!-- HELP LINKS -->
		<div class="dup-support-hlp-area">
			<table class="dup-support-hlp-hdrs">
				<tr >
					<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/books.png" /></td>
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
					<td><img id="dup-support-approved" src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/approved.png"  /></td>
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
				<tr >
					<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>assets/img/support.png" /></td>
					<td><?php _e('Online Support', 'wpduplicator') ?></td>
				</tr>
			</table>
			<div class="dup-support-hlp-txt">
				<?php _e("Work with IT Profressionals!" , 'wpduplicator');	?> 
				<br/><br/>
				
				<div class="dup-support-txts-links">
					<button class="button  button-primary button-large" onclick="Duplicator.OpenSupportWindow(); return false;"><?php _e('Get Help!', 'wpduplicator') ?></button> &nbsp; 
				</div>	
			</div>
		</div> <br style="clear:both" /><br/><br/><br/>
		
		
		
		
		<!--  ==================================================
		SUPPORT DUPLICATOR
		==================================================== -->
		<h2><?php _e('Support Duplicator', 'wpduplicator') ?></h2>
		
		<!-- PARTNER WITH US -->
		<div class="dup-support-give-area">
			<table class="dup-support-hlp-hdrs">
				<tr >
					<td style="height:30px; text-align: center;">
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