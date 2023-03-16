<?php

use Duplicator\Libs\Upsell;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<h3 class="title"><?php esc_html_e("Activation"); ?> </h3>
<hr size="1" />
<table class="form-table licenses-table">
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
            <p><?php esc_html_e('You\'re using Duplicator Lite - no license needed. Enjoy!', 'duplicator'); ?> 🙂</p>
            <p>
                <?php printf(
                    wp_kses(
                        __('To unlock more features consider <strong><a href="%s" target="_blank" rel="noopener noreferrer">upgrading to PRO</a></strong>.', 'duplicator'),
                        array(
                            'a'      => array(
                                'href'   => array(),
                                'class'  => array(),
                                'target' => array(),
                                'rel'    => array(),
                            ),
                            'strong' => array(),
                        )
                    ),
                    esc_url(Upsell::getCampaignUrl('license-tab', 'upgrading to PRO'))
                ); ?>
            </p>
            <p class="discount-note">
                <?php
                printf(
                    __(
                        'As a valued Duplicator Lite user you receive <strong>%1$d%% off</strong>, automatically applied at checkout!',
                        'duplicator'
                    ),
                    DUP_Constants::UPSELL_DEFAULT_DISCOUNT
                );
                ?>
                </p>
        </div>
    </td>
</tr>
</table>



