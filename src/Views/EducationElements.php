<?php

/**
 * @package Duplicator
 */

namespace Duplicator\Views;

use Duplicator\Core\Views\TplMng;
use Duplicator\Utils\Upsell;

class EducationElements
{
    const DUP_SETTINGS_FOOTER_CALLOUT_DISMISSED = 'duplicator_settings_footer_callout_dismissed';
    const DUP_PACKAGES_BOTTOM_BAR_DISMISSED     = 'duplicator_packages_bottom_bar_dismissed';
    const DUP_EMAIL_SUBSCRIBED_OPT_KEY          = 'duplicator_email_subscribed';

    /**
     * Init hooks
     *
     * @return void
     */
    public static function init()
    {
        add_action('duplicator_settings_page_footer', array(__CLASS__, 'displayCalloutCTA'));
        add_action('duplicator_scan_progress_header', array(__CLASS__, 'didYouKnow'));
        add_action('duplicator_scan_progress_footer', array(__CLASS__, 'emailForm'));
        add_action('duplicator_build_progress_header', array(__CLASS__, 'didYouKnow'));
        add_action('duplicator_build_progress_footer', array(__CLASS__, 'emailForm'));
        add_action('duplicator_build_success_footer', array(__CLASS__, 'emailForm'));
        add_action('duplicator_before_packages_footer', array(__CLASS__, 'bottomBar'));
    }

    /**
     * Display callout CTA
     *
     * @return void
     */
    public static function displayCalloutCTA()
    {
        if (get_user_meta(get_current_user_id(), self::DUP_SETTINGS_FOOTER_CALLOUT_DISMISSED, false) == true) {
            return;
        }

        TplMng::getInstance()->render('parts/Education/callout-cta');
    }

    /**
     * Display did you know
     *
     * @return void
     */
    public static function didYouKnow()
    {
        $features = Upsell::getProFeatureList();
        TplMng::getInstance()->render('parts/Education/did-you-know-blurb', array(
            'feature' => $features[array_rand($features)]
        ));
    }

    /**
     * Display email form
     *
     * @return void
     */
    public static function emailForm()
    {
        if (self::userIsSubscribed()) {
            return;
        }

        TplMng::getInstance()->render('parts/Education/subscribe-form');
    }

    /**
     * Display did you know
     *
     * @return void
     */
    public static function bottomBar()
    {
        if (get_user_meta(get_current_user_id(), self::DUP_PACKAGES_BOTTOM_BAR_DISMISSED, false) == true) {
            return;
        }

        $numberOfPackages = \DUP_Package::count_by_status(array(
            array('op' => '=' , 'status' => \DUP_PackageStatus::COMPLETE )
        ));

        if ($numberOfPackages < 1) {
            return;
        }

        $features = self::getBottomBarFeatures();
        TplMng::getInstance()->render('parts/Education/packages-bottom-bar', array(
            'feature' => $features[array_rand($features)]
        ));
    }

    /**
     * Get packages bottom bar feature list
     *
     * @return string[]
     */
    private static function getBottomBarFeatures()
    {
        return array(
            __('Scheduled Backups - Ensure that important data is regularly and consistently backed up, allowing for quick ' .
                'and efficient recovery in case of data loss.', 'duplicator'),
            __('Cloud Backups - Back up to Dropbox, FTP, Google Drive, OneDrive, or Amazon S3 and more for safe storage.', 'duplicator'),
            __('Recovery Points - Recovery Points provides protection against mistakes and bad updates by letting you ' .
                'quickly rollback your system to a known, good state.', 'duplicator'),
            __('Secure File Encryption - Protect and secure the archive file with industry-standard AES-256 encryption', 'duplicator'),
            __('Server to Server Import - Direct package import from source server or cloud storage using URL. No need ' .
                'to download the package to your desktop machine first.', 'duplicator'),
            __('File & Database Table Filters - Use file and database filters to pick and choose exactly what you want to ' .
                'backup or transfer. No bloat!', 'duplicator'),
            __('Large Site Support - Duplicator Pro has developed a new way to package backups especially tailored for ' .
                'larger site. No server timeouts or other restrictions.', 'duplicator'),
            __('Mulstisite Support - Duplicator Pro supports multisite network backup & migration. You can even install ' .
                'a subsite as a standalone site.', 'duplicator'),
        );
    }

    /**
     * True if user is subscribed
     *
     * @return bool
     */
    public static function userIsSubscribed()
    {
        return get_user_meta(get_current_user_id(), self::DUP_EMAIL_SUBSCRIBED_OPT_KEY, false);
    }
}
