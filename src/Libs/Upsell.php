<?php

/**
 * @package Duplicator
 */

namespace Duplicator\Libs;

class Upsell
{
    /**
     * Utils::getCampainUrl
     * Get upgrade campaign URL
     *
     * @param string utm_medium flag
     * @param string utm_content flag
     *
     * @return string
     */
    public static function getCampaignUrl($medium, $content = '')
    {
        $utmData = array(
            'utm_medium' => $medium,
            'utm_content' => $content,
            'utm_source'   => 'WordPress',
            'utm_campaign' => 'liteplugin'
        );

        return 'https://snapcreek.com/lite-upgrade/?' . http_build_query($utmData);
    }

    /**
     * getCampainUrlHtml
     * Get upgrade campaign HTML for tooltips
     *
     * @param string[] $utmData utm_content flag
     *
     * @return string
     */
    public static function getCampaignTooltipHTML($utmData)
    {
        $url = self::getCampaignUrl($utmData);
        if (function_exists('esc_url')) {
            $url = esc_url($url);
        } else {
            $url = \DUPX_U::esc_url($url);
        }

        ob_start();
        ?>
        <p class="pro-tip-link" >
            Upgrade to
            <a href="<?php echo $url; ?>" target="_blank">
                <b>Duplicator Pro!</b>
            </a>
        </p>
        <?php
        return ob_get_clean();
    }
}
