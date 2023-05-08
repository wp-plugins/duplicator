<?php

namespace Duplicator\Controllers;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Views\TplMng;

class AboutUsController
{
    const ABOUT_US_TAB    = 'about-info';
    const GETTING_STARTED = 'getting-started';
    const LITE_VS_PRO     = 'lite-vs-pro';

    const LITE_ENABLED_FULL    = 'full';
    const LITE_ENABLED_PARTIAL = 'partial';
    const LITE_ENABLED_NONE    = 'none';

    /**
     * Array containing all the lite vs pro features
     *
     * @var string[] $liteVsProfeatures
     */
    public static $liteVsProfeatures = array();

    /**
     * Enqueue assets.
     *
     * @return void
     */
    public static function enqueues()
    {
        wp_enqueue_script(
            'duplicator-extra-plugins',
            DUPLICATOR_PLUGIN_URL . "assets/js/extra-plugins.js",
            array('jquery'),
            DUPLICATOR_VERSION,
            true
        );

        wp_localize_script(
            'duplicator-extra-plugins',
            'duplicator_extra_plugins',
            array(
                'ajax_url'                   => admin_url('admin-ajax.php'),
                'extra_plugin_install_nonce' => wp_create_nonce('duplicator_install_extra_plugin'),
            )
        );

        wp_enqueue_style(
            'duplicator-about',
            DUPLICATOR_PLUGIN_URL . "assets/css/about.css",
            array(),
            DUPLICATOR_VERSION
        );
    }

    /**
     * Render welcome screen
     *
     * @return void
     */
    public static function render()
    {
        $levels = ControllersManager::getMenuLevels();
        TplMng::getInstance()->render(
            'admin_pages/about_us/tabs',
            array(
                'active_tab' => is_null($levels[ControllersManager::QUERY_STRING_MENU_KEY_L2]) ?
                    self::ABOUT_US_TAB : $levels[ControllersManager::QUERY_STRING_MENU_KEY_L2]
            ),
            true
        );

        switch ($levels[ControllersManager::QUERY_STRING_MENU_KEY_L2]) {
            case self::GETTING_STARTED:
                TplMng::getInstance()->render('admin_pages/about_us/getting_started/main', array(), true);
                break;
            case self::LITE_VS_PRO:
                TplMng::getInstance()->render('admin_pages/about_us/lite_vs_pro/main', array(), true);
                break;
            case self::ABOUT_US_TAB:
            default:
                TplMng::getInstance()->render('admin_pages/about_us/about_us/main', array(), true);
                break;
        }
    }

    /**
     * Returns the lite vs pro features as an array
     *
     * @return array
     */
    public static function getLiteVsProFeatures()
    {
        if (!empty(self::$liteVsProfeatures)) {
            return self::$liteVsProfeatures;
        }

        self::$liteVsProfeatures = array(
            array(
                'title' => __('Backup Files & Database', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_FULL,
            ),
            array(
                'title' => __('File & Database Table Filters', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_FULL,
            ),
            array(
                'title' => __('Migration Wizard', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_FULL,
            ),
            array(
                'title' => __('Overwrite Live Site', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_FULL,
            ),
            array(
                'title' => __('Drag & Drop Installs', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_PARTIAL,
                'lite_text' => __('Classic WordPress-less Installs Only', 'duplicator'),
                'pro_text' => __(
                    'Drag and Drop migrations and site restores! Simply drag the bundled site archive to the site you wish to overwrite.',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Scheduled Backups', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'Ensure that your important data is regularly and consistently backed up, allowing for quick and efficient recovery in case of data loss.',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Recovery Points', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'Recovery Points provide protection against mistakes and bad updates by letting you quickly rollback your system to a known, good state.',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Cloud Storage', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'Back up to Dropbox, FTP, Google Drive, OneDrive, Amazon S3 or any S3-compatible storage service for safe storage.',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Larger Site Support', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'We\'ve developed a new way to package backups especially tailored for larger site. No server timeouts or other restrictions!',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Server-to-Server Import', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'Direct Server Transfers allow you to build an archive, then directly transfer it from the source ' .
                    'server to the destination server for a lightning fast migration!',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Multisite support', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'Supports multisite network backup & migration. Subsite As Standalone Install, Standalone ' .
                    'Import Into Multisite and Import Subsite Into Multisite',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Installer Branding', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __('Create your own custom-configured WordPress site and "Brand" the installer file with your look and feel.', 'duplicator')
            ),
            array(
                'title' => __('Archive Encryption', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __('Protect and secure the archive file with industry-standard AES-256 encryption!', 'duplicator')
            ),
            array(
                'title' => __('Advanced Backup Permissions', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'Enjoy granular access control to ensure only authorized users can perform these critical functions.',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Enhanced Features', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'Enhanced features include: Managed Hosting Support, Shared Database Support, Streamlined Installer, Email Alerts and more...',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Advanced Features', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'pro_text' => __(
                    'Advanced features included: Hourly Schedules, Custom Search & Replace, Migrate Duplicator Settings, Regenerate Salts and Developer Hooks',
                    'duplicator'
                )
            ),
            array(
                'title' => __('Customer Support', 'duplicator'),
                'lite_enabled' => self::LITE_ENABLED_NONE,
                'lite_text' => __('Limited Support', 'duplicator'),
                'pro_text' => __('Priority Support', 'duplicator')
            )
        );

        return self::$liteVsProfeatures;
    }
}
