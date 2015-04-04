<?php
DUP_Util::CheckPermissions('read');

require_once(DUPLICATOR_PLUGIN_PATH . '/views/javascript.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/views/inc.header.php');
?>
<style>
    /*================================================
    PAGE-SUPPORT:*/
    table.dup-support-hlp-hdrs {border-collapse:collapse; width:100%; border-bottom:1px solid #dfdfdf}
    table.dup-support-hlp-hdrs {background-color:#efefef;}
    table.dup-support-hlp-hdrs td {
        padding:6px; 
        font-weight:bold; font-size:22px;
        background-image:-ms-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
        background-image:-moz-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
        background-image:-o-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
        background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #FFFFFF), color-stop(1, #DEDEDE));
        background-image:-webkit-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
        background-image:linear-gradient(to bottom, #FFFFFF 0%, #DEDEDE 100%);
    }
    div.dup-compare-area {width:400px;  float:left; border:1px solid #dfdfdf; border-radius:4px; margin:10px; line-height:18px;box-shadow: 0 8px 6px -6px #ccc;}
	div.feature {background: #fff; padding:20px; margin: 2px; text-align: center; min-height: 30px}
	div.feature a {font-size:20px; font-weight: bold;}
	div.dup-compare-area div.feature div.info {display:none; padding:10px 15px 5px 15px; font-style: italic; color: #555; font-size: 14px}

</style>

<script type="text/javascript">var switchTo5x = true;</script>
<script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "1a44d92e-2a78-42c3-a32e-414f78f9f484"});</script> 


<div class="wrap dup-wrap">
	
    <?php duplicator_header(__("Go Pro!", 'wpduplicator')) ?>
    <hr size="1" />

    <div style="width:850px; margin:auto; margin-top: 20px">


        <!-- PARTNER WITH US -->
        <div class="dup-compare-area">
            <table class="dup-support-hlp-hdrs">
                <tr >
                    <td style="height:30px; text-align: center;">
                        <span style="display: inline-block; margin-top: 5px"><?php _e('Duplicator Free', 'wpduplicator') ?></span>
                    </td>
                </tr>
            </table>
 
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Backup Files &amp; Database', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Compresses all your WordPress files and database into a compressed snapshot archive file.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Directory Filters', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Filter out the directories and file extensions you want to include/exclude in your in your archive file.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Database Table Filters', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Filter out only the database tables you want to include/exclude in your database creation script.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Migration Wizard', 'wpduplicator') ?></a>
				<div class="info"><?php _e('With just two files (archive &amp; installer.php) move your site to a new location.', 'wpduplicator') ?></div>
			</div>
			<div class="feature"><i class="fa fa-times fa-2x"></i></div>
			<div class="feature"><i class="fa fa-times fa-2x"></i></div>
			<div class="feature"><i class="fa fa-times fa-2x"></i></div>
			<div class="feature"><i class="fa fa-times fa-2x"></i></div>
			
			
        </div> 


        <!-- SPREAD THE WORD  -->
        <div class="dup-compare-area">
            <table class="dup-support-hlp-hdrs">
                <tr>
                    <td style="height:30px; text-align: center;">
                        <span style="display: inline-block; margin-top: 5px"><?php _e('Duplicator Pro', 'wpduplicator') ?></span>
                    </td>
                </tr>
            </table>

			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Backup Files &amp; Database', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Compresses all your WordPress files and database into a compressed snapshot archive file.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Directory Filters', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Filter out the directories and file extensions you want to include/exclude in your in your archive file.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Database Table Filters', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Filter out only the database tables you want to include/exclude in your database creation script.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Migration Wizard', 'wpduplicator') ?></a>
				<div class="info"><?php _e('With just two files (archive &amp; installer.php) move your site to a new location.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Schedules', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Automate the creation of your packages to run at various scheduled intervals.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Custom Templates', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Customize how each package is created and built with a customized template.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Cloud Storage', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Backup up your entire package to the cloud with access to services like FTP and Dropbox.', 'wpduplicator') ?></div>
			</div>
			<div class="feature">
				<a href="javascript:void(0)" class="dup-info-click"><?php _e('Queued Processing', 'wpduplicator') ?></a>
				<div class="info"><?php _e('Enable the build of larger more complex packages and avoid server timeouts with queued processing.', 'wpduplicator') ?></div>
			</div>
			
			
        </div>
        <br style="clear:both" /><br/>
		
		
		<h1 style="text-align: center">
			<a href="http://duplicatorpro.com" target="_blank" class="button button-primary button-large" style="font-size:18px; height:40px; padding:5px 25px 0px 25px;">
				   <i class="fa fa-cog" style="margin-right:8px"></i><?php _e('Learn More', 'wpduplicator') ?>...
				</a>
		</h1>

    </div>
</div><br/><br/><br/><br/>

<script type="text/javascript">
	jQuery(document).ready(function($) {
		$( "a.dup-info-click" ).click(function() {
			$(this).parent().find('.info').toggle();
		});

	});
</script>