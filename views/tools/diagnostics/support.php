<?php

use Duplicator\Installer\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
    div.dup-support-all {font-size:13px; line-height:20px}
    div.dup-support-txts-links {width:100%;font-size:14px; font-weight:bold; line-height:26px; text-align:center}
    div.dup-support-hlp-area {width:375px; height:180px; float:left; border:1px solid #dfdfdf; border-radius:4px; margin:10px; line-height:18px;box-shadow: 0 8px 6px -6px #ccc;}
    table.dup-support-hlp-hdrs {border-collapse:collapse; width:100%; border-bottom:1px solid #dfdfdf}
    table.dup-support-hlp-hdrs {background-color:#efefef;}
    div.dup-support-hlp-hdrs {
        font-weight:bold; font-size:17px; height: 35px; padding:5px 5px 5px 10px;
        background-image:-ms-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
        background-image:-moz-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
        background-image:-o-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
        background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #FFFFFF), color-stop(1, #DEDEDE));
        background-image:-webkit-linear-gradient(top, #FFFFFF 0%, #DEDEDE 100%);
        background-image:linear-gradient(to bottom, #FFFFFF 0%, #DEDEDE 100%);
    }
    div.dup-support-hlp-hdrs div {padding:5px; margin:4px 20px 0px -20px;  text-align: center;}
    div.dup-support-hlp-txt{padding:10px; text-align:center}
</style>


<div class="wrap dup-wrap dup-support-all">

    <div style="width:800px; margin:auto; margin-top: 20px">
        <table>
            <tr>
                <td style="width:70px"><i class="fa fa-question-circle fa-5x"></i></td>
                <td valign="top" style="padding-top:10px; font-size:13px">
                    <?php
                    esc_html_e(
                        "Migrating WordPress is a complex process and the logic to make all the magic happen smoothly may not work quickly with every site. " .
                        " With over 30,000 plugins and a very complex server eco-system some migrations may run into issues.  This is why the Duplicator includes a detailed knowledgebase that can help with many common issues. " .
                        " Resources to additional support, approved hosting, and alternatives to fit your needs can be found below.",
                        'duplicator'
                    );
                    ?>
                </td>
            </tr>
        </table>
        <br/><br/>

        <!-- HELP LINKS -->
        <div class="dup-support-hlp-area">
            <div class="dup-support-hlp-hdrs">
                <i class="fas fa-cube fa-2x fa-pull-left"></i>
                <div><?php esc_html_e('Knowledgebase', 'duplicator') ?></div>
            </div>
            <div class="dup-support-hlp-txt">
                <?php esc_html_e('Complete Online Documentation', 'duplicator'); ?>
                <br/>
                <select id="dup-support-kb-lnks" style="margin-top:18px; font-size:16px; min-width: 170px">
                    <option disabled selected>
                        <?php esc_html_e('Choose A Section', 'duplicator') ?>
                    </option>
                    <option 
                        value="<?php echo esc_url(LinkManager::getCategoryUrl(LinkManager::QUICK_START_CAT, 'tools_support', 'Quick Start')); ?>
                    ">
                        <?php esc_html_e('Quick Start', 'duplicator') ?>
                    </option>
                    <option value="<?php echo esc_url(LinkManager::getDocUrl('', 'tools_support', 'User Guide')); ?>">
                        <?php esc_html_e('User Guide', 'duplicator'); ?>
                    </option>
                    <option 
                        value="<?php echo esc_url(LinkManager::getCategoryUrl(LinkManager::TROUBLESHOOTING_CAT, 'tools_support', 'FAQs')); ?>
                    ">
                        <?php esc_html_e('FAQs', 'duplicator'); ?>
                    </option>
                    <option value="<?php echo esc_url(LinkManager::getDocUrl('changelog', 'tools_support', 'Change Log')); ?>">
                        <?php esc_html_e('Change Log', 'duplicator') ?>
                    </option>
                </select>
            </div>
        </div>

        <!-- ONLINE SUPPORT -->
        <div class="dup-support-hlp-area">
            <div class="dup-support-hlp-hdrs">
                <i class="far fa-lightbulb fa-2x fa-pull-left"></i>
                <div><?php esc_html_e('Premium Support', 'duplicator') ?></div>
            </div>
            <div class="dup-support-hlp-txt">
<?php esc_html_e("Having a problem with your back up or migrations? Upgrade to get our Premium Support.", 'duplicator'); ?>
                <br/>
                <div class="dup-support-txts-links" style="margin:10px 0 10px 0">
                    <a href="<?php echo esc_url(\Duplicator\Utils\Upsell::getCampaignUrl('duplicator_tools-support_tab', 'Upgrade Now')); ?>" target="_blank" class="dup-btn dup-btn-md dup-btn-green" >
<?php esc_html_e('Upgrade Now', 'duplicator') ?>
                    </a> <br/>
                </div>
                <small>Free Users <a href="https://wordpress.org/support/plugin/duplicator/" target="_blank">Support Forum</a></small>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function ($) {
        //ATTACHED EVENTS
        jQuery('#dup-support-kb-lnks').change(function () {
            if (jQuery(this).val() != "null")
                window.open(jQuery(this).val())
        });

    });
</script>
