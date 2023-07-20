<?php

/**
 * Template for lite vs pro page
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || exit;

use Duplicator\Controllers\AboutUsController;
use Duplicator\Utils\Upsell;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="wrap" id="dup-admin-about">
    <div class="dup-admin-about-section dup-admin-about-section-squashed">
        <h1 class="centered">
            <strong>Lite</strong> vs <strong>Pro</strong>
        </h1>

        <p class="centered">
            <?php esc_html_e('Get the most out of Duplicator by upgrading to Pro and unlocking all of the powerful features.', 'duplicator'); ?>
        </p>
    </div>

    <div class="dup-admin-about-section dup-admin-about-section-squashed dup-admin-about-section-hero dup-admin-about-section-table">

        <div class="dup-admin-about-section-hero-main dup-admin-columns">
            <div class="dup-admin-column-33">
                <h3 class="no-margin">
                    <?php esc_html_e('Feature', 'duplicator'); ?>
                </h3>
            </div>
            <div class="dup-admin-column-33">
                <h3 class="no-margin">
                    <?php echo __('Lite', 'duplicator'); ?>
                </h3>
            </div>
            <div class="dup-admin-column-33">
                <h3 class="no-margin">
                    <?php echo __('Pro', 'duplicator'); ?>
                </h3>
            </div>
        </div>
        <div class="dup-admin-about-section-hero-extra no-padding dup-admin-columns">

            <table>
                <?php foreach (AboutUsController::getLiteVsProFeatures() as $feature) : ?>
                    <tr class="dup-admin-columns">
                        <td class="dup-admin-column-33">
                            <p><?php echo $feature['title']; ?></p>
                        </td>
                        <td class="dup-admin-column-33">
                            <p class="features-<?php echo $feature['lite_enabled']; ?>">
                                <strong>
                                <?php
                                if (isset($feature['lite_text'])) {
                                    echo $feature['lite_text'];
                                } else {
                                    $feature['lite_enabled'] === AboutUsController::LITE_ENABLED_FULL ? _e('Included', 'duplicator')
                                        : _e('Not Available', 'duplicator');
                                }
                                ?>
                                </strong>
                            </p>
                        </td>
                        <td class="dup-admin-column-33">
                            <p class="features-full">
                                <strong>
                                <?php echo isset($feature['pro_text']) ? $feature['pro_text'] : __('Included', 'duplicator'); ?>
                                </strong>
                            </p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div class="dup-admin-about-section dup-admin-about-section-hero">
        <div class="dup-admin-about-section-hero-main no-border">
            <h3 class="call-to-action centered">
                <?php
                printf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">',
                    esc_url(Upsell::getCampaignUrl('about_duplicator_lite_vs_pro', 'Get Duplicator Pro Today'))
                );
                _e('Get Duplicator Pro Today and Unlock all the Powerful Features', 'duplicator')
                ?>
                </a>
            </h3>

            <p class="centered">
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
</div>
