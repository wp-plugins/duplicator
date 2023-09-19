<?php

namespace Duplicator\Utils\Email;

use DUP_Log;
use Exception;
use DUP_Package;
use DUP_Settings;
use DUP_PackageStatus;
use Duplicator\Utils\CronUtils;
use Duplicator\Libs\Snap\SnapWP;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Snap\JsonSerialize\JsonSerialize;

/**
 * Email Summary
 */
class EmailSummary
{
    const SEND_FREQ_NEVER   = 'never';
    const SEND_FREQ_DAILY   = 'daily';
    const SEND_FREQ_WEEKLY  = 'weekly';
    const SEND_FREQ_MONTHLY = 'monthly';

    const CRON_HOOK    = 'duplicator_email_summary_cron';
    const PEVIEW_SLUG  = 'duplicator-email-summary-preview';
    const INFO_OPT_KEY = 'duplicator-email-summary-info';

    /** @var self The singleton instance */
    private static $self = null;

    /** @var int[] Manual package ids */
    private $manualPackageIds = array();

    /** @var int[] info about created storages*/
    private $failedPackageIds = array();

    /**
     * Get the singleton instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (self::$self == null) {
            self::$self = new self();
        }

        return self::$self;
    }

    /**
     * Create Email Summary object
     */
    private function __construct()
    {
        if (($data = get_option(self::INFO_OPT_KEY)) !== false) {
            JsonSerialize::unserializeToObj($data, $this);
        }
    }

    /**
     * Init
     *
     * @return void
     */
    public function init()
    {
        //Package hooks
        add_action('duplicator_package_after_set_status', array($this, 'addPackage'), 10, 2);

        //Set cron action
        add_action(self::CRON_HOOK, array($this, 'send'));
    }

    /**
     * Init static hooks
     *
     * @return void
     */
    public static function initHooks()
    {
        //Init Cron hooks
        add_action('duplicator_after_activation', array(__CLASS__, 'activationAction'));
        add_action('duplicator_after_deactivation', array(__CLASS__, 'deactivationAction'));
    }

    /**
     * Updates the WP Cron job base on frequency or settings
     *
     * @param string $frequency The frequency
     *
     * @return bool True if the cron was updated or false on error
     */
    private static function updateCron($frequency = '')
    {
        if (strlen($frequency) === 0) {
            $frequency = DUP_Settings::Get('email_summary_frequency');
        }

        if ($frequency === self::SEND_FREQ_NEVER) {
            if (wp_next_scheduled(self::CRON_HOOK)) {
                return is_int(wp_clear_scheduled_hook(self::CRON_HOOK));
            } else {
                return true;
            }
        } else {
            if (
                wp_next_scheduled(self::CRON_HOOK)
                && !is_int(wp_clear_scheduled_hook(self::CRON_HOOK)) //make sure we clear the old cron
            ) {
                return false;
            }

            return (wp_schedule_event(
                self::getFirstRunTime($frequency),
                self::getCronSchedule($frequency),
                self::CRON_HOOK
            ) === true);
        }
    }

    /**
     * Init cron on activation
     *
     * @return void
     */
    public static function activationAction()
    {
        $frequency = DUP_Settings::Get('email_summary_frequency');
        if ($frequency === self::SEND_FREQ_NEVER) {
            return;
        }

        if (self::updateCron($frequency) == false) {
            DUP_Log::trace("FAILED TO INIT EMAIL SUMMARY CRON. Frequency: {$frequency}");
        }
    }

    /**
     * Removes cron on deactivation
     *
     * @return void
     */
    public static function deactivationAction()
    {
        if (self::updateCron(self::SEND_FREQ_NEVER) == false) {
            DUP_Log::trace("FAILED TO REMOVE EMAIL SUMMARY CRON.");
        }
    }

    /**
     * Update next send time on frequency setting change
     *
     * @param string $oldFrequency The old frequency
     * @param string $newFrequency The new frequency
     *
     * @return bool True if the cron was updated or false on error
     */
    public static function updateFrequency($oldFrequency, $newFrequency)
    {
        if ($oldFrequency === $newFrequency) {
            return true;
        }

        return self::updateCron($newFrequency);
    }

    /**
     * Returns the preview link
     *
     * @return string
     */
    public static function getPreviewLink()
    {
        return add_query_arg('page', self::PEVIEW_SLUG, admin_url('admin.php'));
    }

    /**
     * Add package id
     *
     * @param DUP_Package $package The package
     * @param int         $status  The status
     *
     * @return void
     */
    public function addPackage(DUP_Package $package, $status)
    {
        if ($status !== DUP_PackageStatus::COMPLETE && $status !== DUP_PackageStatus::ERROR) {
            return;
        }

        if ($status === DUP_PackageStatus::COMPLETE) {
            $this->manualPackageIds[] = $package->ID;
        } elseif ($status === DUP_PackageStatus::ERROR) {
            $this->failedPackageIds[] = $package->ID;
        }

        $this->save();
    }

    /**
     * Returns info about created packages
     *
     * @return array<int|string, array<string, string|int>>
     */
    public function getPackagesInfo()
    {
        $packagesInfo           = array();
        $packagesInfo['manual'] = array(
            'name'     => __('Successful', 'duplicator'),
            'count'    => count($this->manualPackageIds),
        );

        $packagesInfo['failed'] = array(
            'name'     => __('Failed', 'duplicator'),
            'count'    => count($this->failedPackageIds),
        );

        return $packagesInfo;
    }

    /**
     * Send email
     *
     * @return bool True if email was sent
     */
    public function send()
    {
        $frequency = DUP_Settings::Get('email_summary_frequency');
        if (($recipient = get_option('admin_email')) === false || $frequency === self::SEND_FREQ_NEVER) {
            return false;
        }

        $parsedHomeUrl = wp_parse_url(home_url());
        $siteDomain    = $parsedHomeUrl['host'];

        if (is_multisite() && isset($parsedHomeUrl['path'])) {
            $siteDomain .= $parsedHomeUrl['path'];
        }

        $subject = sprintf(
            esc_html_x(
                'Your Weekly Duplicator Summary for %s',
                '%s is the site domain',
                'duplicator'
            ),
            $siteDomain
        );

        $content = TplMng::getInstance()->render('mail/email_summary', array(
            'packages' => $this->getPackagesInfo(),
        ), false);

        add_filter('wp_mail_content_type', array($this, 'getMailContentType'));
        if (!wp_mail($recipient, $subject, $content)) {
            DUP_Log::Trace("FAILED TO SEND EMAIL SUMMARY.");
            DUP_Log::Trace("Recipients: " . $recipient);
            return false;
        } elseif (!$this->resetData()) {
            DUP_Log::Trace("FAILED TO RESET EMAIL SUMMARY DATA.");
            return false;
        }

        return true;
    }

    /**
     * Get mail content type
     *
     * @return string
     */
    public function getMailContentType()
    {
        return 'text/html';
    }

    /**
     * Get all frequency options
     *
     * @return array<int, string>
     */
    public static function getAllFrequencyOptions()
    {
        return array(
            self::SEND_FREQ_NEVER   => esc_html__('Never', 'duplicator'),
            self::SEND_FREQ_DAILY   => esc_html__('Daily', 'duplicator'),
            self::SEND_FREQ_WEEKLY  => esc_html__('Weekly', 'duplicator'),
            self::SEND_FREQ_MONTHLY => esc_html__('Monthly', 'duplicator'),
        );
    }

     /**
     * Get the frequency text displayed in the email
     *
     * @return string
     */
    public static function getFrequencyText()
    {
        $frequency = DUP_Settings::Get('email_summary_frequency');
        switch ($frequency) {
            case self::SEND_FREQ_DAILY:
                return esc_html__('day', 'duplicator');
            case self::SEND_FREQ_MONTHLY:
                return esc_html__('month', 'duplicator');
            case self::SEND_FREQ_WEEKLY:
            default:
                return esc_html__('week', 'duplicator');
        }
    }

    /**
     * Set next send time based on frequency
     *
     * @param string $frequency Frequency
     *
     * @return int
     */
    private static function getFirstRunTime($frequency)
    {
        switch ($frequency) {
            case self::SEND_FREQ_DAILY:
                $firstRunTime = strtotime('tomorrow 14:00');
                break;
            case self::SEND_FREQ_WEEKLY:
                $firstRunTime = strtotime('next monday 14:00');
                break;
            case self::SEND_FREQ_MONTHLY:
                $firstRunTime = strtotime('first day of next month 14:00');
                break;
            case self::SEND_FREQ_NEVER:
                return 0;
            default:
                throw new Exception("Unknown frequency: " . $frequency);
        }

        return $firstRunTime - SnapWP::getGMTOffset();
    }

    /**
     * Get the cron schedule
     *
     * @param string $frequency The frequency
     *
     * @return string
     */
    private static function getCronSchedule($frequency)
    {
        switch ($frequency) {
            case self::SEND_FREQ_DAILY:
                return CronUtils::INTERVAL_DAILTY;
            case self::SEND_FREQ_WEEKLY:
                return CronUtils::INTERVAL_WEEKLY;
            case self::SEND_FREQ_MONTHLY:
                return CronUtils::INTERVAL_MONTHLY;
            default:
                throw new Exception("Unknown frequency: " . $frequency);
        }
    }

    /**
     * Reset plugin data
     *
     * @return bool True if data has been reset, false otherwise
     */
    private function resetData()
    {
        $this->manualPackageIds = array();
        $this->failedPackageIds = array();

        return $this->save();
    }

    /**
     * Save plugin data
     *
     * @return bool True if data has been saved, false otherwise
     */
    private function save()
    {
        return update_option(self::INFO_OPT_KEY, JsonSerialize::serialize($this));
    }
}
