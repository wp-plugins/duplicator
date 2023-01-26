<?php

/**
 * NoticeBar Education template for Lite.
 *
 *
 * @var string $upgrade_link Upgrade to Pro page URL.
 */

use Duplicator\Libs\Upsell;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var string $umt_content
 */

?>
<div id="dup-notice-bar">
    <span class="dup-notice-logo" >
        <img src="<?php echo esc_url(DUPLICATOR_PLUGIN_URL . 'assets/img/logo-white.svg'); ?>" alt="Duplicaator Logo"/>
    </span>
    <span class="dup-notice-bar-message">
    <?php
        printf(
            wp_kses(
            /* translators: %s - WPForms.com Upgrade page URL. */
                __('<strong>You\'re using Duplicator Lite.</strong> To unlock more features consider <a href="%s" target="_blank" rel="noopener noreferrer">upgrading to Pro</a>', 'duplicator'),
                array(
                    'a'      => array(
                        'href'   => array(),
                        'rel'    => array(),
                        'target' => array(),
                    ),
                    'strong' => array(),
                )
            ),
            esc_url(Upsell::getCampaignUrl('lite-upgrade-bar', $umt_content))
        );
        ?>
        <a href="<?php echo esc_url(Upsell::getCampaignUrl('lite-upgrade-bar', $umt_content)); ?>"
           class="dup-upgrade-arrow" target="_blank" rel="noopener noreferrer">→</a>
    </span>
    <button type="button" class="dup-dismiss-button"
            title="<?php esc_attr_e('Dismiss this message.', 'duplicator'); ?>">
    </button>
</div>
<script>
    jQuery(document).ready(function ($) {
        $(document).on(
            'click',
            '#dup-notice-bar .dup-dismiss-button',
            function (e) {
                e.preventDefault();

                $.post(ajaxurl, {
                    action: 'duplicator_notice_bar_dismiss',
                    nonce: '<?php echo wp_create_nonce('duplicator-notice-bar-dismiss'); ?>'
                });

                $('#dup-notice-bar').fadeOut();
            }
        );
    });
</script>
