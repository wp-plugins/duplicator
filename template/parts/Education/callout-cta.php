<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Libs\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="dup-settings-lite-cta">
    <a href="#" class="dismiss" title="<?php esc_attr_e('Dismiss this message', 'duplicator'); ?>"><i class="fa fa-times-circle" aria-hidden="true"></i></a>
    <h5><?php esc_html_e('Get Duplicator Pro and Unlock all the Powerful Features', 'duplicator'); ?></h5>
    <p>
        <?php esc_html_e(
            'Thanks for being a loyal Duplicator Lite user. Upgrade to Duplicator Pro to unlock all the ' .
            'awesome features and experience why Duplicator is consistently rated the best WordPress migration plugin.',
            'duplicator'
        ); ?>
    </p>
    <p>
        <?php
        printf(
            wp_kses( /* translators: %s - star icons. */
                __(
                    'We know that you will truly love Duplicator. It has over 4000+ five star ratings (%s) and is active on ' .
                    'over 1 million websites.',
                    'duplicator'
                ),
                array(
                    'i' => array(
                        'class'       => array(),
                        'aria-hidden' => array(),
                    ),
                )
            ),
            str_repeat('<i class="fa fa-star" aria-hidden="true"></i>', 5) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
        ?>
    </p>
    <h6><?php esc_html_e('Pro Features:', 'duplicator'); ?></h6>
    <ul class="list">
        <?php
        foreach (Upsell::getCalloutCTAFeatureList() as $feature) {
            ?>
            <li class="item">
                <span>
                  <?php echo esc_html($feature); ?>
                </span>
            </li>
            <?php
        };
        ?>
    </ul>
    <p>
        <a href="<?php echo esc_url(Upsell::getCampaignUrl('settings-upgrade')); ?>" target="_blank" rel="noopener noreferrer">
            <?php esc_html_e('Get Duplicator Pro Today and Unlock all the Powerful Features »', 'duplicator'); ?>
        </a>
    </p>
    <p>
        <?php
        printf(
            __(
                '<strong>Bonus:</strong> Duplicator Lite users get <span class="green">%1$d%% off regular price</span>,' .
                'automatically applied at checkout.',
                'duplicator'
            ),
            DUP_Constants::UPSELL_DEFAULT_DISCOUNT
        );
        ?>
    </p>
</div>