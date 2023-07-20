<?php

/**
 * Template for First Package section
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Utils\Upsell;

defined('ABSPATH') || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="dup-admin-about-section dup-admin-about-section-hero">

    <div class="dup-admin-about-section-hero-main">
        <h2>
            <?php _e('Get Duplicator Pro and Unlock all the Powerful Features', 'duplicator'); ?>
        </h2>

        <p class="bigger">
            <?php
            echo wp_kses(
                __(
                    'Thanks for being a loyal Duplicator Lite user. <strong>Upgrade to Duplicator Pro</strong> to unlock ' .
                    'all the awesome features and experience<br>why Duplicator is consistently rated the best WordPress migration plugin.',
                    'duplicator'
                ),
                array(
                    'br'     => array(),
                    'strong' => array(),
                )
            );
            ?>
        </p>

        <p>
            <?php
            printf(
                wp_kses( /* translators: %s - stars. */
                    __(
                        'We know that you will truly love Duplicator. It has over <strong>4000+ five star ratings</strong> ' .
                        '(%s) and is active on over 1 million websites.',
                        'duplicator'
                    ),
                    array(
                        'strong' => array(),
                    )
                ),
                '<i class="fa fa-star" aria-hidden="true"></i>' .
                '<i class="fa fa-star" aria-hidden="true"></i>' .
                '<i class="fa fa-star" aria-hidden="true"></i>' .
                '<i class="fa fa-star" aria-hidden="true"></i>' .
                '<i class="fa fa-star" aria-hidden="true"></i>'
            );
            ?>
        </p>
    </div>

    <div class="dup-admin-about-section-hero-extra">
        <div class="dup-admin-about-section-features">
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
        </div>

        <hr/>

        <h3 class="call-to-action">
            <?php
            printf(
                '<a href="%s" target="_blank" rel="noopener noreferrer">',
                esc_url(Upsell::getCampaignUrl('duplicator-about_getting-started', 'Get Duplicator Pro Today'))
            );

            _e('Get Duplicator Pro Today and Unlock all the Powerful Features', 'duplicator');
            ?>
            </a>
        </h3>

        <p>
            <?php
            printf(
                __(
                    'Bonus: Duplicator Lite users get <span class="price-20-off">%1$d%% off regular price</span>, ' .
                    'automatically applied at checkout.',
                    'duplicator'
                ),
                DUP_Constants::UPSELL_DEFAULT_DISCOUNT
            );
            ?>
        </p>
    </div>

</div>