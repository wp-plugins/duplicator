<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Installer\Utils\InstallerUpsell;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Variables
 *
 * @var string $featuresHTML
 */
?>
<div class="dup-settings-lite-cta">
    <h5>Get Duplicator Pro and Unlock all the Powerful Features</h5>
    <p>
        Thanks for being a loyal Duplicator Lite user. Upgrade to Duplicator Pro to unlock all the awesome features and
        experience why Duplicator is consistently rated the best WordPress migration plugin.
    </p>
    <p>
        <?php
        printf(
            'We know that you will truly love Duplicator. It has over 4000+ five star ratings (%s) and is active on ' .
            'over 1 million websites.',
            str_repeat('<i class="fa fa-star" aria-hidden="true"></i>', 5)
        );
        ?>
    </p>
    <h6>Pro Features:</h6>
    <ul class="list">
        <?php
        foreach (InstallerUpsell::getCalloutCTAFeatureList() as $feature) {
            ?>
            <li class="item">
                <span>
                    <?php echo $feature; ?>
                </span>
            </li>
            <?php
        };
        ?>
    </ul>
    <p>
        <a href="<?php echo DUPX_U::esc_url(InstallerUpsell::getCampaignUrl('installer-footer-callout')); ?>" target="_blank" rel="noopener noreferrer">
            Get Duplicator Pro Today and Unlock all the Powerful Features »
        </a>
    </p>
    <p>
        <strong>Bonus:</strong> Duplicator Lite users get <span class="green"><?php echo DUPX_Constants::UPSELL_DEFAULT_DISCOUNT; ?>% off regular price</span>, 
        automatically applied at checkout.
    </p>
</div>