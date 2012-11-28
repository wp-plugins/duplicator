<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "1a44d92e-2a78-42c3-a32e-414f78f9f484"}); </script> 
<script>
	jQuery(function() {	jQuery("#dup-survey").button();	});
</script>



<div class="wrap dup-wrap">

	<!-- h2 requred here for general system messages -->
	<h2 style='display:none'></h2>
	<div class="dup-header widget">
		<!-- !!DO NOT CHANGE/EDIT OR REMOVE PRODUCT NAME!!
		If your interested in Private Label Rights please contact us at the URL below to discuss
		customizations to product labeling: http://lifeinthegrid.com/services/	-->
		<div style='float:left;height:45px'><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/logo.png" style='text-align:top'  /></div> 
		<div style='float:left;height:45px; text-align:center;'>
			<h2 style='margin:-12px 0px -7px 0px; text-align:center; width:100%;'>Duplicator &raquo;<span style="font-size:18px"> <?php _e("About", 'wpduplicator') ?></span> </h2>
			<i style='font-size:0.8em'><?php _e("By", 'wpduplicator') ?> <a href='http://lifeinthegrid.com/duplicator' target='_blank'>lifeinthegrid.com</a></i>
		</div> 
		<div style='float:right; padding:5px 0px 0px 0px; white-space:nowrap'>
			<input type="button" id="btn-help-dialog" onclick='window.location.href="?page=duplicator_support_page"' title="<?php _e("Support", 'wpduplicator') ?>" />
		</div>
		<br style='clear:both' />
	</div>

	<div style="width:850px; margin:auto">
		<table>
			<tr>
				<td valign="top" class="dup-drop-cap">
				<?php 
					_e("Your LifeInTheGrid should be about working smarter not harder.  With the Duplicator you can streamline your workflows and quickly clone a WordPress site in minutes.  From Novice to Guru this plugin is designed for Bloggers, Admins, Developers, Designers, Entrepreneurs and anyone who uses WordPress.", 'wpduplicator');
					echo '<br/><br/>';
					_e("If you run into an issue feel free to submit a support ticket and visit our" , 'wpduplicator');
					
					printf(" <a href='http://lifeinthegrid.com/duplicator-docs' target='_blank'>%s</a>.",
								__("knowledgebase", 'wpduplicator'));
					
					printf("  %s <a href='http://lifeinthegrid.com/services' target='_blank'>%s</a> %s",
								__("We also offer premium priority support for those who need feedback within 24-48hrs.  Please visit our ", 'wpduplicator'),
								__("service page", 'wpduplicator'),
								__("for more details.", 'wpduplicator')); 
				?>
				</td>
				<td><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/logo-box.png" style='text-align:top; margin:-10px 0px 0px 20px'  /></td>
			</tr>
		</table>
		
		<!--  ========================
		SUPPORT DUPLICATOR -->
		<h2 style="margin-top:-40px"><?php _e('Support Duplicator', 'wpduplicator') ?></h2><hr size="1" />

		<!-- DONTATE -->
		<div class="dup-support-cell" onclick="jQuery('#dup-donate-form').submit()">
			<form id="dup-donate-form" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" style="display:inline-block;height:50px; margin:5px 0px 0px -2px"> 
				<input name="cmd" type="hidden" value="_s-xclick" /> 
				<input name="hosted_button_id" type="hidden" value="EYJ7AV43RTZJL" /> 
				<input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/btn/btn_donateCC_LG.gif" type="image" /> 
				<img src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" /> 
			</form>
		</div>
		<div class="dup-support-txts">
			<?php _e('Consider a donation of any amount; it will take less than 60 seconds or about as fast as you can duplicate a site.  These proceeds help to cover development and support costs.  Thanks for your generosity!!', 'wpduplicator') ?>
		</div>
		<div style="clear:both; height:15px"></div>
					
					
		<!-- FIVE STAR -->			
		<div class="dup-support-cell no-select" onclick="window.open('http://wordpress.org/extend/plugins/duplicator', '_blank')">
			<img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/5star.png" style='text-align:top; margin-left:20px'   />
		</div> 
		<div class="dup-support-txts">
			<?php 
				printf("%s <a href='http://wordpress.org/extend/plugins/duplicator' target='_blank'>%s</a>  ",
				__("Help out by leaving a 5 star review on the", 'wpduplicator'),
				__("WordPress plugins directory", 'wpduplicator')); 
				_e('and by giving your opinion on the survey below.', 'wpduplicator');
			?>
			<div style="text-align:center; padding:10px">
				<input id="dup-survey" type="button" onclick="window.open('http://lifeinthegrid.com/duplicator-survey')" value="<?php _e('Give us your Opinion?', 'wpduplicator') ?>" />
			</div>
		</div>
		<div style="clear:both; height:15px"></div>





		
		<!--  ========================
		SPREAD THE WORD -->
		<h2><?php _e('Spread the Word', 'wpduplicator') ?></h2><hr size="1" />
		<?php _e('Spreading the word gives the plugin a wider audience which helps to pool more resources to test/support/develop and participate to improve the plugin.  By getting the word out the Duplicator will continue to improve at a faster rate and harness the power of the open community to push the plugin further.', 'wpduplicator'); ?>		

		<?php
			$title = __("Duplicate Your WordPress", 'wpduplicator');
			$summary = __("Rapid WordPress Duplication by LifeInTheGrid.com", 'wpduplicator');
			$share_this_data = "st_url='" . DUPLICATOR_HOMEPAGE . "' st_title='{$title}' st_summary='{$summary}'";
		?>

		<div style="width:100%; padding:10px 10px 0px 10px" align="center">
			<span class='st_facebook_vcount' displayText='Facebook' <?php echo $share_this_data; ?> ></span>
			<span class='st_twitter_vcount' displayText='Tweet' <?php echo $share_this_data; ?> ></span>
			<span class='st_googleplus_vcount' displayText='Google +' <?php echo $share_this_data; ?> ></span>
			<span class='st_linkedin_vcount' displayText='LinkedIn' <?php echo $share_this_data; ?> ></span>
			<span class='st_email_vcount' displayText='Email' <?php echo $share_this_data; ?> ></span>
		</div>
		
		<div style="width:100%; padding:10px 10px 0px 10px" align="center">
			<table>
				<tr style="text-align:center">
					<td>
						<b style="font-size:16px">Social News</b><br/>
						<span class='st_reddit_large' displayText='Reddit' <?php echo $share_this_data; ?> ></span>
						<span class='st_slashdot_large' displayText='Slashdot' <?php echo $share_this_data; ?> ></span>
						<span class='st_stumbleupon_large' displayText='StumbleUpon' <?php echo $share_this_data; ?> ></span>
						<span class='st_technorati_large' displayText='Technorati' <?php echo $share_this_data; ?> ></span>
						<span class='st_digg_large' displayText='Digg' <?php echo $share_this_data; ?> ></span>
						<span class='st_blogger_large' displayText='Blogger' <?php echo $share_this_data; ?> ></span> 
						<span class='st_wordpress_large' displayText='WordPress' <?php echo $share_this_data; ?> ></span>		
						<span class='st_dzone_large' displayText='DZone' <?php echo $share_this_data; ?> ></span>					
					</td>
					<td><br/> &nbsp; <img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/hdivider.png" class="toolbar-divider" /> &nbsp; </td>
					<td>
						<b style="font-size:16px">Bookmarks</b><br/>
						<span class='st_evernote_large' displayText='Evernote' <?php echo $share_this_data; ?> ></span>
						<span class='st_delicious_large' displayText='Delicious' <?php echo $share_this_data; ?> ></span>
						<span class='st_blogmarks_large' displayText='Blogmarks' <?php echo $share_this_data; ?> ></span>
						<span class='st_connotea_large' displayText='Connotea' <?php echo $share_this_data; ?> ></span>
						<span class='st_google_bmarks_large' displayText='Bookmarks' <?php echo $share_this_data; ?> ></span>
						<span class='st_email_large' displayText='Email' <?php echo $share_this_data; ?> ></span>					
					</td>
				</tr>
			</table>
		</div><br/>

		<!--  ========================
		VISIT US -->
		<hr size="1" />
		<div style="width:100%; padding:10px 10px 0px 10px" align="center">
			<a href="http://lifeinthegrid.com/duplicator-docs" target="_blank"><?php _e('Knowledge Base', 'wpduplicator') ?></a> &nbsp; | &nbsp; 
			<a href="http://lifeinthegrid.com/duplicator-faq" target="_blank"><?php _e('FAQ', 'wpduplicator') ?></a> &nbsp; | &nbsp; 
			<a href="http://lifeinthegrid.com" target="_blank"><?php _e('Blog', 'wpduplicator') ?></a> &nbsp; | &nbsp;
			<a href="http://lifeinthegrid.com/labs" target="_blank"><?php _e('Labs', 'wpduplicator') ?></a> &nbsp; | &nbsp; 
			<a href="http://www.youtube.com/lifeinthegridtv" target="_blank">YouTube</a>
		</div>
		
	</div>

</div><br/><br/><br/><br/>




