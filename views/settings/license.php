<?php

use Duplicator\Libs\Upsell;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<h3 class="title"><?php esc_html_e("Activation"); ?> </h3>
<hr size="1" />
<table class="form-table">
<tr valign="top">
    <th scope="row"><?php esc_html_e("Manage") ?></th>
    <td>
        <?php
            echo sprintf(
                __('%1$sManage Licenses%2$s', 'duplicator'),
                '<a target="_blank" href="' . esc_url(Upsell::getCampaignUrl('license-tab', 'Manage Licenses')) . '">',
                '</a>'
            );
            ?>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><?php esc_html_e("Type") ?></th>
    <td class="dpro-license-type">
        <?php esc_html_e('Duplicator Lite'); ?>
        <div style="padding: 10px">
            <i class="far fa-check-square"></i> <?php esc_html_e('Basic Features'); ?> <br/>
            <i class="far fa-square"></i> 
            <a target="_blank" 
                href="<?php echo esc_url(Upsell::getCampaignUrl('license-tab', 'Pro Features')); ?>"
            >
                <?php esc_html_e('Pro Features'); ?>
            </a><br>
        </div>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><label><?php esc_html_e("License Key"); ?></label></th>
    <td>
        <div class="description" style="max-width:700px">

            <b><?php esc_html_e("Duplicator Lite:", 'duplicator');  ?></b>

            <ul style="list-style-type:circle; margin-left:40px">
                <li>
                     <?php esc_html_e("The free version of Duplicator does not require a license key.", 'duplicator');  ?>
                </li>
                <li>
                    <?php
                        esc_html_e("If you would like to purchase the professional version you can ", 'duplicator');
                        echo '<a href="' . esc_url(Upsell::getCampaignUrl('license-tab', 'get a copy here')) . '" target="_blank">' .
                            esc_html__("get a copy here", 'duplicator') .
                            '</a>!';
                    ?>
                </li>
            </ul>

            <b><?php esc_html_e("Duplicator Pro:", 'duplicator');  ?></b>
            <ul style="list-style-type:circle; margin-left: 40px">
                <li>
                    <?php esc_html_e("The professional version is a separate plugin that you download and install. ", 'duplicator');  ?>
                </li>
                <li>
                    <?php esc_html_e("Download professional from the email sent after purchase or login to snapcreek.com", 'duplicator');  ?>
                </li>

            </ul>
        </div>
    </td>
</tr>
</table>



