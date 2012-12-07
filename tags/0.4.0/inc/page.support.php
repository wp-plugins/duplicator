

<div class="wrap dup-wrap">

	<!-- h2 requred here for general system messages -->
	<h2 style='display:none'></h2>
	<div class="dup-header widget">
		<div style='float:left;height:45px'><img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/logo.png" style='text-align:top'  /></div> 
		<div style='float:left;height:45px; text-align:center;'>
			<h2 style='margin:-12px 0px -7px 0px; text-align:center; width:100%;'>Duplicator &raquo;<span style="font-size:18px"> <?php _e("Support", 'wpduplicator') ?></span> </h2>
			<i style='font-size:0.8em'><?php _e("By", 'wpduplicator') ?> <a href='http://lifeinthegrid.com/duplicator' target='_blank'>lifeinthegrid.com</a></i>
		</div> 
		<div style='float:right; padding:5px 0px 0px 0px; white-space:nowrap'>
			<input type="button" id="dup-btn-about" onclick='window.location.href="?page=duplicator_about_page"' title="<?php _e("All About", 'wpduplicator') ?>" />
		</div>
		<br style='clear:both' />
	</div> <br/><br/>

	<div style="width:850px; margin:auto">

		
		<!-- KNOWLEDGE BASE -->
		<h2><?php _e('Knowledge Base', 'wpduplicator') ?></h2><hr size="1" />
		<div class="dup-support-cell no-select"  onclick="window.open('<?php echo DUPLICATOR_HELPLINK  ?>', '_blank')">
			<img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/books.png" style='text-align:top; margin:0px 0px 0px 40px' />
		</div>
		<div class="dup-support-txts">		
			<?php  _e('For a complete rundown of the Duplicator see the online knowledgebase by clicking the question button or any link below.   If you experience issues with creating or installing a package check out the FAQ page first.', 'wpduplicator');?>
		</div>
		
		<div style="width:100%;font-size:14px; padding-left:10px; font-weight:bold; text-align:center; line-height:26px;" >
			<a href="http://lifeinthegrid.com/duplicator-quick" target="_blank"><?php _e('Quick Start', 'wpduplicator') ?></a> &nbsp; | &nbsp;
			<a href="http://lifeinthegrid.com/duplicator-guide" target="_blank"><?php _e('User Guide', 'wpduplicator') ?></a> &nbsp; | &nbsp; 
			<a href="http://lifeinthegrid.com/duplicator-faq" target="_blank"><?php _e('FAQs', 'wpduplicator') ?></a>   &nbsp; | &nbsp; 
			<a href="http://lifeinthegrid.com/duplicator-log" target="_blank"><?php _e('Changelog', 'wpduplicator') ?></a> &nbsp; | &nbsp; 
			<a href="http://lifeinthegrid.com/labs/duplicator" target="_blank"><?php _e('Product Page', 'wpduplicator') ?></a>
		</div>		
		<div style="clear:both; height:35px"></div>


		<!-- APPROVED QA -->
		<h2><?php _e('Approved Hosting', 'wpduplicator') ?></h2><hr size="1" />
		<div class="dup-support-cell no-select"  onclick="window.open('<?php echo DUPLICATOR_CERTIFIED  ?>', '_blank')">
			<img id="dup-about-approved" src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/approved.png" style='text-align:top; margin:2px 0px 0px 25px' />
		</div>
		<div class="dup-support-txts">		
			<?php 
				_e('Not all hosting companies are created equal and to get the most out of the plugin we recommend using hosting providers that we do our own testing on.  Please visit our', 'wpduplicator');
					
				printf(" <a href='http://lifeinthegrid.com/duplicator-hosts' target='_blank'>%s</a> %s.",
					__("Approved Affiliate Hosting Program", 'wpduplicator'),
					__("and consider making a switch to hosts that we trust and have experienced good success with when using the Duplicator", 'wpduplicator')); 			
			?>
		</div>
		<div style="clear:both; height:35px"></div>
		
		
		<!-- ONLINE SUPPORT -->
		<h2><?php _e('Online Support', 'wpduplicator') ?></h2><hr size="1" />
		<div class="dup-support-cell no-select"  onclick="window.open('http://lifeinthegrid.com/services', '_blank')">
			<img src="<?php echo DUPLICATOR_PLUGIN_URL  ?>img/clock.png" style='text-align:top; margin:0px 0px 0px 40px' />
			
		</div>
		<div class="dup-support-txts">		
			<?php 
					_e("If you run into an issue and have already read the FAQs feel free to submit a support ticket and we will try our best to respond within 5-10 business days or sooner if possible." , 'wpduplicator');

					printf("  %s <a href='http://lifeinthegrid.com/services' target='_blank'>%s</a> %s",
								__("If you need priority support within 24-48hrs please visit our ", 'wpduplicator'),
								__("service page", 'wpduplicator'),
								__("for more details.  Premium support is 100% refundable if we are unable to resolve your issue.", 'wpduplicator')); 	
					
					echo "<br/> <br/>";
			?>
			<i>
				<?php 
					_e("Most issues that occur with the Duplicator revolve around how a server is configured.  In order to diagnose your issues we will require temporary admin accounts to your cPanel and WordPress Admin.  Please fill out the ", 'wpduplicator'); 
					printf(" <a href='http://lifeinthegrid.com/services' target='_blank'>%s</a> %s",
						__("Request Quote", 'wpduplicator'),
						__("form and explain your issue in detail.", 'wpduplicator')); 	
				?>
				
			</i>
		</div>
		<div style="clear:both; height:35px"></div>
		


		
	</div>

</div><br/><br/><br/><br/>




