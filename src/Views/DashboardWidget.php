<?php

namespace Duplicator\Views;

use DUP_Package;
use DUP_PackageStatus;
use Duplicator\Core\Views\TplMng;

/**
 * Dashboard widget
 */
class DashboardWidget
{
    const LAST_PACKAGE_TIME_WARNING            = 86400; // 24 hours
    const LAST_PACKAGES_LIMIT                  = 3;
    const RECOMMENDED_PLUGIN_ENABLED           = true;
    const RECOMMENDED_PLUGIN_DISMISSED_OPT_KEY = 'duplicator_recommended_plugin_dismissed';

    /**
     * Add the dashboard widget
     *
     * @return void
     */
    public static function init()
    {
        if (is_multisite()) {
            add_action('wp_network_dashboard_setup', array(__CLASS__, 'addDashboardWidget'));
        } else {
            add_action('wp_dashboard_setup', array(__CLASS__, 'addDashboardWidget'));
        }
    }

    /**
     * Render the dashboard widget
     *
     * @return void
     */
    public static function addDashboardWidget()
    {
        wp_add_dashboard_widget(
            'duplicator_dashboard_widget',
            __('Duplicator', 'duplicator'),
            array(__CLASS__, 'renderContent')
        );
    }

    /**
     * Render the dashboard widget content
     *
     * @return void
     */
    public static function renderContent()
    {
        TplMng::getInstance()->setStripSpaces(true);
        ?>
        <div class="dup-dashboard-widget-content">
            <?php self::renderPackageCreate(); ?>
            <hr class="separator" >
            <?php self::renderRecentlyPackages(); ?>
            <hr class="separator" >
            <?php
            self::renderSections();
            if (self::RECOMMENDED_PLUGIN_ENABLED) { // @phpstan-ignore-line
                self::renderRecommendedPluginSection();
            }
            ?>
        </div>
        <?php
    }

    /**
     * Render the package create button
     *
     * @return void
     */
    protected static function renderPackageCreate()
    {
        TplMng::getInstance()->render(
            'parts/DashboardWidget/package-create-section',
            array (
                'lastBackupString' => self::getLastBackupString()
            )
        );
    }

    /**
     * Render the last packages
     *
     * @return void
     */
    protected static function renderRecentlyPackages()
    {
        /** @var DUP_Package[] */
        $packages = DUP_Package::get_packages_by_status(
            array(
                array(
                    'op' => '>=',
                    'status' => DUP_PackageStatus::COMPLETE
                )
            ),
            self::LAST_PACKAGES_LIMIT,
            0,
            'created DESC'
        );

        $totalsIds = DUP_Package::get_ids_by_status(
            array(
                array(
                    'op' => '>=',
                    'status' => DUP_PackageStatus::COMPLETE
                )
            )
        );

        $failuresIds = DUP_Package::get_ids_by_status(
            array(
                array(
                    'op' => '<',
                    'status' => 0
                )
            )
        );

        TplMng::getInstance()->render(
            'parts/DashboardWidget/recently-packages',
            array(
                'packages'     => $packages,
                'totalPackages' => count($totalsIds),
                'totalFailures' => count($failuresIds)
            )
        );
    }

    /**
     * Render Duplicate sections
     *
     * @return void
     */
    protected static function renderSections()
    {
        TplMng::getInstance()->render(
            'parts/DashboardWidget/sections-section',
            array(
                'numSchedules'        => 0,
                'numSchedulesEnabled' => 0,
                'numTemplates'        => 1,
                'numStorages'         => 1,
                'nextScheduleString'  => '',
                'recoverDateString'   => ''
            )
        );
    }

    /**
     * Get the last backup string
     *
     * @return string HTML string
     */
    public static function getLastBackupString()
    {
        if (DUP_Package::isPackageRunning()) {
            return '<span class="spinner"></span> <b>' . esc_html__('A package is currently running.', 'duplicator-pro') . '</b>';
        }

        /** @var DUP_Package[] */
        $lastPackage = DUP_Package::get_packages_by_status(
            array(
                array(
                    'op' => '>=',
                    'status' => DUP_PackageStatus::COMPLETE
                )
            ),
            1,
            0,
            'created DESC'
        );

        if (empty($lastPackage)) {
            return '<b>' . esc_html__('No packages have been created yet.', 'duplicator-pro') . '</b>';
        }

        $createdTime = date(get_option('date_format'), strtotime($lastPackage[0]->Created));

        if ($lastPackage[0]->getPackageLife() > self::LAST_PACKAGE_TIME_WARNING) {
            $timeDiffClass = 'maroon';
        } else {
            $timeDiffClass = 'green';
        }

        $timeDiff = sprintf(
            _x('%s ago', '%s represents the time diff, eg. 2 days', 'duplicator-pro'),
            $lastPackage[0]->getPackageLife('human')
        );

        return '<b>' . $createdTime . '</b> ' .
            " (" . '<span class="' . $timeDiffClass . '"><b>' .
            $timeDiff .
            '</b></span>' . ")";
    }

    /**
     * Return randomly chosen one of recommended plugins.
     *
     * @return false|array{name: string,slug: string,more: string,pro: array{file: string}}
     */
    protected static function getRecommendedPluginData()
    {
        $plugins = array(
            'google-analytics-for-wordpress/googleanalytics.php' => array(
                'name' => __('MonsterInsights', 'wpforms-lite'),
                'slug' => 'google-analytics-for-wordpress',
                'more' => 'https://www.monsterinsights.com/',
                'pro'  => array(
                    'file' => 'google-analytics-premium/googleanalytics-premium.php',
                ),
            ),
            'all-in-one-seo-pack/all_in_one_seo_pack.php' => array(
                'name' => __('AIOSEO', 'wpforms-lite'),
                'slug' => 'all-in-one-seo-pack',
                'more' => 'https://aioseo.com/',
                'pro'  => array(
                    'file' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
                ),
            ),
            'coming-soon/coming-soon.php'                 => array(
                'name' => __('SeedProd', 'wpforms-lite'),
                'slug' => 'coming-soon',
                'more' => 'https://www.seedprod.com/',
                'pro'  => array(
                    'file' => 'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
                ),
            ),
            'wp-mail-smtp/wp_mail_smtp.php'               => array(
                'name' => __('WP Mail SMTP', 'wpforms-lite'),
                'slug' => 'wp-mail-smtp',
                'more' => 'https://wpmailsmtp.com/',
                'pro'  => array(
                    'file' => 'wp-mail-smtp-pro/wp_mail_smtp.php',
                ),
            ),
        );

        $installed = get_plugins();

        foreach ($plugins as $id => $plugin) {
            if (isset($installed[$id])) {
                unset($plugins[$id]);
            }

            if (isset($installed[$plugin['pro']['file']])) {
                unset($plugins[$id]);
            }
        }
        return ($plugins ? $plugins[ array_rand($plugins) ] : false);
    }

    /**
     * Recommended plugin block HTML.
     *
     * @return void
     */
    public static function renderRecommendedPluginSection()
    {
        if (get_user_meta(get_current_user_id(), self::RECOMMENDED_PLUGIN_DISMISSED_OPT_KEY, true) != false) {
            return;
        }

        $plugin = self::getRecommendedPluginData();

        if (empty($plugin)) {
            return;
        }

        $installUrl = wp_nonce_url(
            self_admin_url('update.php?action=install-plugin&plugin=' . rawurlencode($plugin['slug'])),
            'install-plugin_' . $plugin['slug']
        );

        TplMng::getInstance()->render(
            'parts/DashboardWidget/recommended-section',
            array(
                'plugin'     => $plugin,
                'installUrl' => $installUrl,
            )
        );
    }
}
