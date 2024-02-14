<?php

use Duplicator\Utils\Upsell;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<h3 class="title"><?php esc_html_e('Activation', 'duplicator'); ?> </h3>
<hr size="1" />
<table class="form-table licenses-table">
<tr valign="top">
    <th scope="row"><?php esc_html_e('Manage', 'duplicator') ?></th>
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
    <th scope="row"><?php esc_html_e('Type', 'duplicator') ?></th>
    <td class="dpro-license-type">
        <?php esc_html_e('Duplicator Lite', 'duplicator'); ?>
        <div style="padding: 10px">
            <i class="far fa-check-square"></i> <?php esc_html_e('Basic Features', 'duplicator'); ?> <br/>
            <i class="far fa-square"></i> 
            <a target="_blank" 
                href="<?php echo esc_url(Upsell::getCampaignUrl('license-tab', 'Pro Features')); ?>"
            >
                <?php esc_html_e('Pro Features', 'duplicator'); ?>
            </a><br>
        </div>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><label><?php esc_html_e('License Key', 'duplicator'); ?></label></th>
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
            <hr>
            <p>
                <?php _e('Already purchased? Simply enter your license key below to enable <b>Duplicator PRO!</b>', 'duplicator'); ?></p>
            <p>
                <input type="text" id="dup-settings-upgrade-license-key" placeholder="<?php echo esc_attr__('Paste license key here', 'duplicator'); ?>" value="">
                <button type="button" class="dup-btn dup-btn-md dup-btn-orange" id="dup-settings-connect-btn"><?php echo esc_html__('One click Upgrade to Pro', 'duplicator'); ?></button>
            </p>
        </div>
    </td>
</tr>
</table>

<!-- An absolute position placed invisible form element which is out of browser window -->
<form action="placeholder_will_be_replaced" method="get" id="redirect-to-remote-upgrade-endpoint">
    <input type="hidden" name="oth" id="form-oth" value="">
    <input type="hidden" name="license_key" id="form-key" value="">
    <input type="hidden" name="version" id="form-version" value="">
    <input type="hidden" name="redirect" id="form-redirect" value="">
    <input type="hidden" name="endpoint" id="form-endpoint" value="">
    <input type="hidden" name="siteurl" id="form-siteurl" value="">
    <input type="hidden" name="homeurl" id="form-homeurl" value="">
    <input type="hidden" name="file" id="form-file" value="">
</form>

