<?php

/**
 * NoticeBar Education template for Lite.
 *
 * @package Duplicator
 */

use Duplicator\Utils\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
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

                $('#dup-notice-bar').hide().remove();
            }
        );
    });
</script>
<div id="dup-notice-bar">
    <span class="dup-notice-bar-message">
    <?php
        printf(
            wp_kses(
            /* translators: %s - duplicator.com Upgrade page URL. */
                __(
                    '<strong>You\'re using Duplicator Lite.</strong> To unlock more features consider ' .
                    '<a href="%s" target="_blank" rel="noopener noreferrer">upgrading to Pro</a>',
                    'duplicator'
                ),
                array(
                    'a'      => array(
                        'href'   => array(),
                        'rel'    => array(),
                        'target' => array(),
                    ),
                    'strong' => array(),
                )
            ),
            esc_url(Upsell::getCampaignUrl('lite-upgrade-bar', $tplData['utm_content']))
        );
        ?>
        <a href="<?php echo esc_url(Upsell::getCampaignUrl('lite-upgrade-bar', $tplData['utm_content'])); ?>"
           class="dup-upgrade-arrow" target="_blank" rel="noopener noreferrer">→</a>
    </span>
    <button type="button" class="dup-dismiss-button"
            title="<?php esc_attr_e('Dismiss this message.', 'duplicator'); ?>">
    </button>
</div>
