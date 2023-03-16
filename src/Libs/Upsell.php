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
     * @param string $medium  utm_medium flag
     * @param string $content utm_content flag
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

    /**
     * Get Pro features list
     *
     * @return string[]
     */
    public static function getProFeatureList()
    {
        if (function_exists('__')) {
            return array(
                __('Scheduled Backups', 'duplicator'),
                __('Recovery Points', 'duplicator'),
                __('Secure File Encryption', 'duplicator'),
                __('Server to Server Import', 'duplicator'),
                __('File & Database Table Filters', 'duplicator'),
                __('Cloud Storage - Google Drive', 'duplicator'),
                __('Cloud Storage - Amazon S3', 'duplicator'),
                __('Cloud Storage - DropBox', 'duplicator'),
                __('Cloud Storage - OneDrive', 'duplicator'),
                __('Cloud Storage - FTP/SFTP', 'duplicator'),
                __('Drag & Drop Installs', 'duplicator'),
                __('Larger Site Support', 'duplicator'),
                __('Multisite Network Support', 'duplicator'),
                __('Email Alerts', 'duplicator')
            );
        } else {
            return array(
                'Scheduled Backups',
                'Recovery Points',
                'Secure File Encryption',
                'Server to Server Import',
                'File & Database Table Filters',
                'Cloud Storage - Google Drive',
                'Cloud Storage - Amazon S3',
                'Cloud Storage - DropBox',
                'Cloud Storage - OneDrive',
                'Cloud Storage - FTP/SFTP',
                'Drag & Drop Installs',
                'Larger Site Support',
                'Multisite Network Support',
                'Email Alerts',
            );
        }
    }

    /**
     * Get Pro callout features list
     *
     * @return string[]
     */
    public static function getCalloutCTAFeatureList()
    {
        if (function_exists('__')) {
            return array(
                __('Scheduled Backups', 'duplicator'),
                __('Recovery Points', 'duplicator'),
                __('Secure File Encryption', 'duplicator'),
                __('Server to Server Import', 'duplicator'),
                __('File & Database Table Filters', 'duplicator'),
                __('Cloud Storage', 'duplicator'),
                __('Smart Migration Wizard', 'duplicator'),
                __('Drag & Drop Installs', 'duplicator'),
                __('Streamlined Installer', 'duplicator'),
                __('Developer Hooks', 'duplicator'),
                __('Managed Hosting Support', 'duplicator'),
                __('Larger Site Support', 'duplicator'),
                __('Installer Branding', 'duplicator'),
                __('Migrate Duplicator Settings', 'duplicator'),
                __('Regenerate SALTS', 'duplicator'),
                __('Multisite Network', 'duplicator'),
                __('Email Alerts', 'duplicator'),
                __('Custom Search & Replace', 'duplicator')
            );
        } else {
            return array(
                'Installer Branding',
                'Scheduled Backups',
                'Recovery Points',
                'Secure File Encryption',
                'Server to Server Import',
                'File & Database Table Filters',
                'Cloud Storage',
                'Smart Migration Wizard',
                'Drag & Drop Installs',
                'Streamlined Installer',
                'Developer Hooks',
                'Managed Hosting Support',
                'Larger Site Support',
                'Migrate Duplicator Settings',
                'Regenerate SALTS',
                'Multisite Network',
                'Email Alerts',
                'Custom Search & Replace',
            );
        }
    }
}
