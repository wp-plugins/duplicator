<?php

/**
 * @package Duplicator
 */

namespace Duplicator\Installer\Utils;

use DUPX_U;

class Utils
{
    /**
     * Get campain URL to snaapcreek site
     *
     * @param string $content utm_content flag
     * @param string $path    URL path
     *
     * @return string
     */
    public static function getCampainUrl($content = '', $path = 'duplicator')
    {
        $data = array(
            'utm_source' => 'duplicator_free',
            'utm_medium' => 'wordpress_plugin',
            'utm_campaign' => 'duplicator_pro',
            'utm_content' => $content
        );

        return 'https://snapcreek.com/' . $path . '?' . http_build_query($data);
    }

    /**
     * Get campain URL to snaapcreek site
     *
     * @param string $content utm_content flag
     * @param string $path    URL path
     *
     * @return string
     */
    public static function getCampainUrlHtml($content = '', $path = 'duplicator')
    {
        $url = self::getCampainUrl($content, $path);
        ob_start();
        ?>
        <p class="pro-tip-link" >
            Upgrade to
            <a href="<?php echo DUPX_U::esc_url($url); ?>" target="_blank">
                <b>Duplicator Pro!</b>
            </a>
        </p>
        <?php
        return ob_get_clean();
    }
}
