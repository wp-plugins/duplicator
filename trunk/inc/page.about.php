<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "1a44d92e-2a78-42c3-a32e-414f78f9f484"}); </script> 
<script>
	jQuery(function() { console.log(jQuery("#dup-survey"));	jQuery("#dup-survey").button();	});
</script>



<div class="wrap dup-wrap">

		<div class="dup-header widget">
			<div style='float:left;height:45px'><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/logo.png" style='text-align:top'  /></div> 
			<div style='float:left;height:45px; text-align:center;'>
				<h2 style='margin:-12px 0px -7px 0px; text-align:center; width:100%;'>Duplicator &raquo;<span style="font-size:18px"> <?php _e("About", 'wpduplicator') ?></span> </h2>
				<i style='font-size:0.8em'><?php _e("By", 'wpduplicator') ?> <a href='http://lifeinthegrid.com/duplicator' target='_blank'>lifeinthegrid.com</a></i>
			</div> 
			<div style='float:right; padding:5px 0px 0px 0px'>
				<input type="button" id="btn-contribute-dialog" onclick='Duplicator.newWindow("<?php echo DUPLICATOR_GIVELINK ?>")' title="<?php _e("Partner with us", 'wpduplicator') ?>..." />
			</div>
			<br style='clear:both' />
		</div>
		


	<div style="width:900px; margin:auto">

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
		<table border="0" class="dup-support-table">
			<tr>
				<td class="dup-support-cell" style="height:80px">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" style="display:inline-block;height:50px"> 
						<input name="cmd" type="hidden" value="_s-xclick" /> 
						<input name="hosted_button_id" type="hidden" value="EYJ7AV43RTZJL" /> 
						<input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/btn/btn_donateCC_LG.gif" type="image" title="<?php _e('Donate via Pay-Pal', 'wpduplicator') ?>" /> 
						<img src="https://www.paypalobjects.com/WEBSCR-640-20110401-1/en_US/i/scr/pixel.gif" border="0" alt="" width="1" height="1" /> 
					</form>
				</td>
				<td >
					<?php _e('Consider a donation of any amount; it will take less than 60 seconds or about as fast as you can duplicate a site.  These proceeds help to cover development and support costs.  Thanks for your generosity!!', 'wpduplicator') ?>
				</td>
			</tr>
			<tr>
				<td class="dup-support-cell">
					<a href='http://wordpress.org/extend/plugins/duplicator/' target='_blank'><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/5star.png" style='text-align:top; margin-left:20px' title="<?php _e('Vote at WordPress.org', 'wpduplicator') ?>"   /></a>
				</td>
				<td>
					<?php 
						printf("%s <a href='http://wordpress.org/extend/plugins/duplicator' target='_blank'>%s</a>  ",
						__("Help out by leaving a 5 star review on the", 'wpduplicator'),
						__("WordPress plugins directory", 'wpduplicator')
						); 
						_e('and by giving your opinion on the survey below.', 'wpduplicator');
					?><br/>
					<div style="text-align:center; padding:10px">
						<input id="dup-survey" type="button" onclick="window.open('http://lifeinthegrid.com/duplicator-survey')" value="<?php _e('Give us your Opinion?', 'wpduplicator') ?>" />
					</div>
				</td>
			</tr>
			<tr>
				<td class="dup-support-cell">
					<a href='http://lifeinthegrid.com/duplicator-hosts' target='_blank'><img id="dup-about-approved" src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/approved.png" style='text-align:top; margin-left:20px' title="<?php _e('Preview Approved Hosts', 'wpduplicator') ?>"   /></a>
				</td>
				<td valign="top">
					<?php 
						_e('Not all hosting companies are created equal and to get the most out of the plugin we recommend using hosting providers that we do our own testing on.  Please visit our', 'wpduplicator');
							
						printf(" <a href='http://lifeinthegrid.com/duplicator-hosts' target='_blank'>%s</a> %s.",
							__("Approved Affiliate Hosting Program", 'wpduplicator'),
							__("and consider making a switch to hosts that we trust and have experienced good success with when testing and using the plugin", 'wpduplicator')); 			
					?>
				</td>
			</tr>			
		</table><br/>

		
		<!--  ========================
		SPREAD THE WORD -->
		<h2><?php _e('Spread the Word', 'wpduplicator') ?></h2><hr size="1" />
		<?php _e('Spreading the word gives the plugin a wider audience and that in-effect helps to pool more resources to test/support/develop and participate to improve the plugin.  By getting the word out the Duplicator will continue to improve at a faster rate, and harness the power of the open community to push the plugin further.', 'wpduplicator'); ?>		

		<div style="width:100%; padding:10px 10px 0px 10px" align="center">
			<span class='st_facebook_vcount' displayText='Facebook' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
			<span class='st_twitter_vcount' displayText='Tweet' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
			<span class='st_googleplus_vcount' displayText='Google +' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
			<span class='st_linkedin_vcount' displayText='LinkedIn' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
			<span class='st_email_vcount' displayText='Email' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
		</div>
		
		<div style="width:100%; padding:10px 10px 0px 10px" align="center">
			<table>
				<tr style="text-align:center">
					<td>
						<b style="font-size:16px">Social News</b><br/>
						<span class='st_reddit_large' displayText='Reddit' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_slashdot_large' displayText='Slashdot' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_stumbleupon_large' displayText='StumbleUpon' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_technorati_large' displayText='Technorati' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_digg_large' displayText='Digg' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_blogger_large' displayText='Blogger' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span> 
						<span class='st_wordpress_large' displayText='WordPress' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>		
						<span class='st_dzone_large' displayText='DZone' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>					
					</td>
					<td><br/> &nbsp; <img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/hdivider.png" class="toolbar-divider" /> &nbsp; </td>
					<td>
						<b style="font-size:16px">Bookmarks</b><br/>
						<span class='st_evernote_large' displayText='Evernote' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_delicious_large' displayText='Delicious' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_blogmarks_large' displayText='Blogmarks' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_connotea_large' displayText='Connotea' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_google_bmarks_large' displayText='Bookmarks' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>
						<span class='st_email_large' displayText='Email' st_url="<?php echo DUPLICATOR_HOMEPAGE ?>"></span>					
					</td>
				</tr>
			</table>
		</div><br/>



		<!--  ========================
		VISIT US -->
		<hr size="1" />
		<div style="width:100%; padding:10px 10px 0px 10px" align="center">
			<a href="http://lifeinthegrid.com/duplicator-docs" target="_blank">Knowledge Base</a> &nbsp; | &nbsp; 
			<a href="http://lifeinthegrid.com/duplicator-faq" target="_blank">FAQ</a> &nbsp; | &nbsp; 
			<a href="http://lifeinthegrid.com" target="_blank">Blog</a> &nbsp; | &nbsp;
			<a href="http://lifeinthegrid.com/labs" target="_blank">Labs</a> &nbsp; | &nbsp; 
			<a href="http://www.youtube.com/lifeinthegridtv" target="_blank">YouTube</a>
		</div>
		
	</div>

</div><br/><br/><br/><br/>




