<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
/*================================================
PAGE-SUPPORT:*/
div.dup-support-all {font-size:13px; line-height:20px}
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
div.dup-support-give-area {width:265px; height:225px; float:left; margin:10px; line-height:18px;}
div.dup-spread-word {display:inline-block; border:1px solid red; text-align:center}

img#dup-support-approved { -webkit-animation:approve-keyframe 12s 1s infinite alternate backwards}
img#dup-img-5stars {opacity:0.7;}
img#dup-img-5stars:hover {opacity:1.0;}
div.social-item {float:right; width: 170px; padding:10px; border:0px solid red; text-align: left; font-size:20px}

/* EMAIL AREA */
div.dup-support-email-area {width:825px; height:355px; margin:10px; line-height:18px;}
#mce-EMAIL {font-size:20px; height:40px; width:500px}
#mce-responses {width:300px}
#mc-embedded-subscribe { height: 35px; font-size: 16px; font-weight: bold}
div.mce_inline_error {width:300px; margin: auto !important}
div#mce-responses {margin: auto; padding: 10px; width:500px; font-weight: bold;}
</style>


<div class="wrap dup-wrap dup-support-all">
<div style="width:850px; margin:auto; margin-top:30px">
    <table style="width:825px">
        <tr>
            <td style="width:230px;">
                <img src="<?php echo esc_url(DUPLICATOR_PLUGIN_URL . "assets/img/logo-box.png"); ?>" style='margin-top:-60px; height:176px; width:176px'  />
            </td>
            <td valign="top" style="padding-top:10px; font-size:15px; text-align:justify;">
                <?php
                    esc_html_e("Duplicator can streamline your workflow and quickly clone/migrate a WordPress site. The plugin helps admins, designers and "
                        . "developers speed up the migration process of moving a WordPress site. Please help us continue development by giving the plugin a "
                        . "5 star and consider purchasing our Pro product.", 'duplicator');
                ?>
                <br/><br/>

                <!-- PARTNER WITH US -->
                <div class="dup-support-give-area">
                    <table class="dup-support-hlp-hdrs">
                        <tr >
                            <td style="height:30px; text-align: center;">
                                <span><?php esc_html_e('Rate Duplicator', 'duplicator') ?></span>
                            </td>
                        </tr>
                    </table>
                    <table style="text-align: center;width:100%; font-size:11px; font-style:italic; margin-top:35px">
                        <tr>
                            <td valign="top">
                                <a href="<?php echo esc_url(\Duplicator\Core\Notifications\Review::getReviewUrl()); ?>" target="vote-wp"><img id="dup-img-5stars" src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/5star.png" /></a>
                                <div  style=" font-size: 16px; font-weight: bold; line-height: 22px">
                                    <a href="<?php echo esc_url(\Duplicator\Core\Notifications\Review::getReviewUrl()); ?>" target="vote-wp">
                                        <?php
                                            esc_html_e('Support Duplicator', 'duplicator');
                                            echo '<br/>';
                                            esc_html_e('with a 5 star review!', 'duplicator')
                                        ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- SPREAD THE WORD  -->
                <div class="dup-support-give-area">
                    <table class="dup-support-hlp-hdrs">
                        <tr>
                            <td style="height:30px; text-align: center;">
                                <span><?php esc_html_e('Spread the Word', 'duplicator') ?></span>
                            </td>
                        </tr>
                    </table>
                    <div class="dup-support-hlp-txt">
                        <br/>
                        <div class="social-images">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=https%3A//snapcreek.com/duplicator/duplicator-free/" target="_blank">
                                <div class="social-item"><i class="fab fa-facebook-square fa-lg"></i> <?php esc_html_e('Facebook', 'duplicator') ?></div>
                            </a>
                            <a href="https://twitter.com/home?status=Checkout%20the%20WordPress%20Duplicator%20plugin!%20%0Ahttps%3A//snapcreek.com/duplicator/duplicator-free/"  target="_blank">
                                <div class="social-item"><i class="fab fa-twitter-square fa-lg"></i> <?php esc_html_e('Twitter', 'duplicator') ?></div>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=https%3A//snapcreek.com/duplicator/duplicator-free/&title=WordPress%20Duplicator%20Plugin&summary=&source=" target="_blank">
                                <div class="social-item"><i class="fab fa-linkedin fa-lg"></i> <?php esc_html_e('LinkedIn', 'duplicator') ?></div>
                            </a>
                        </div>
                    </div>
                </div>
                <br style="clear:both" /><br/>
            </td>
        </tr>
    </table>

</div>
</div><br/><br/><br/><br/>
<script>
    jQuery(document).ready(function($)
    {
        $('input[type="checkbox"][name="privacy"]').change(function() {
        if(this.checked) {
             $("#mc-embedded-subscribe").prop("disabled", false);
         } else {
             $("#mc-embedded-subscribe").prop("disabled", true);
         }

        });
    });
</script>
