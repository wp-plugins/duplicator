<?php

namespace Duplicator\Utils\Email;

use DUP_Log;
use DUP_Package;
use DUP_Settings;
use DUP_PackageStatus;
use Duplicator\Utils\CronUtils;
use Duplicator\Libs\Snap\SnapWP;
use Duplicator\Core\Views\TplMng;

/**
 * Email summary bootstrap
 */
class EmailSummaryBootstrap
{
    const CRON_HOOK = 'duplicator_email_summary_cron';

    /**
     * Init Email Summaries
     *
     * @return void
     */
    public static function init()
    {
        //Package hooks
        add_action('duplicator_package_after_set_status', array(__CLASS__, 'addPackage'), 10, 2);

        //Set cron action
        add_action(self::CRON_HOOK, array(__CLASS__, 'send'));

        //Activation/deactivation hooks
        add_action('duplicator_after_activation', array(__CLASS__, 'activationAction'));
        add_action('duplicator_after_deactivation', array(__CLASS__, 'deactivationAction'));
    }

    /**
     * Add package to summary
     *
     * @param DUP_Package $package The package
     * @param int         $status  The status
     *
     * @return void
     */
    public static function addPackage(DUP_Package $package, $status)
    {
        EmailSummary::getInstance()->addPackage($package, $status);
    }

    /**
     * Send email summary
     *
     * @return bool True if email was sent
     */
    public static function send()
    {
        $frequency = DUP_Settings::Get('email_summary_frequency');
        if (($recipient = get_option('admin_email')) === false || $frequency === EmailSummary::SEND_FREQ_NEVER) {
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
            'packages' => EmailSummary::getInstance()->getPackagesInfo(),
        ), false);

        add_filter('wp_mail_content_type', array(__CLASS__, 'getMailContentType'));
        if (!wp_mail($recipient, $subject, $content)) {
            DUP_Log::Trace("FAILED TO SEND EMAIL SUMMARY.");
            DUP_Log::Trace("Recipients: " . $recipient);
            return false;
        } elseif (!EmailSummary::getInstance()->resetData()) {
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
    public static function getMailContentType()
    {
        return 'text/html';
    }

    /**
     * Activation action
     *
     * @return void
     */
    public static function activationAction()
    {
        $frequency = DUP_Settings::Get('email_summary_frequency');
        if ($frequency === EmailSummary::SEND_FREQ_NEVER) {
            return;
        }

        if (self::updateCron($frequency) == false) {
            DUP_Log::Trace("FAILED TO INIT EMAIL SUMMARY CRON. Frequency: {$frequency}");
        }
    }

    /**
     * Deactivation action
     *
     * @return void
     */
    public static function deactivationAction()
    {
        if (self::updateCron(EmailSummary::SEND_FREQ_NEVER) == false) {
            DUP_Log::Trace("FAILED TO REMOVE EMAIL SUMMARY CRON.");
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

        if ($frequency === EmailSummary::SEND_FREQ_NEVER) {
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
     * Set next send time based on frequency
     *
     * @param string $frequency Frequency
     *
     * @return int
     */
    private static function getFirstRunTime($frequency)
    {
        switch ($frequency) {
            case EmailSummary::SEND_FREQ_DAILY:
                $firstRunTime = strtotime('tomorrow 14:00');
                break;
            case EmailSummary::SEND_FREQ_WEEKLY:
                $firstRunTime = strtotime('next monday 14:00');
                break;
            case EmailSummary::SEND_FREQ_MONTHLY:
                $firstRunTime = strtotime('first day of next month 14:00');
                break;
            case EmailSummary::SEND_FREQ_NEVER:
                return 0;
            default:
                throw new \Exception("Unknown frequency: " . $frequency);
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
            case EmailSummary::SEND_FREQ_DAILY:
                return CronUtils::INTERVAL_DAILTY;
            case EmailSummary::SEND_FREQ_WEEKLY:
                return CronUtils::INTERVAL_WEEKLY;
            case EmailSummary::SEND_FREQ_MONTHLY:
                return CronUtils::INTERVAL_MONTHLY;
            default:
                throw new Exception("Unknown frequency: " . $frequency);
        }
    }
}
