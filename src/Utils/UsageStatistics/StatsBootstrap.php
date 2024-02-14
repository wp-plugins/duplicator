<?php

namespace Duplicator\Utils\UsageStatistics;

use DUP_Log;
use DUP_Package;
use DUP_PackageStatus;
use DUP_Settings;
use Duplicator\Utils\CronUtils;

/**
 * StatsBootstrap
 */
class StatsBootstrap
{
    const USAGE_TRACKING_CRON_HOOK = 'duplicator_usage_tracking_cron';

    /**
     * Init WordPress hooks
     *
     * @return void
     */
    public static function init()
    {
        add_action('duplicator_after_activation', array(__CLASS__, 'activationAction'));
        add_action('duplicator_after_deactivation', array(__CLASS__, 'deactivationAction'));
        add_action('duplicator_package_after_set_status', array(__CLASS__, 'addPackageBuild'), 10, 2);
        add_action('duplicator_after_scan_report', array(__CLASS__, 'addSiteSizes'), 10, 2);
        add_action('duplicator_usage_tracking_cron', array(__CLASS__, 'sendPluginStatCron'));
    }

    /**
     * Activation action
     *
     * @return void
     */
    public static function activationAction()
    {
        // Set cron
        if (!wp_next_scheduled(self::USAGE_TRACKING_CRON_HOOK)) {
            $randomTracking = wp_rand(0, WEEK_IN_SECONDS);
            $timeToStart    = strtotime('next sunday') + $randomTracking;
            wp_schedule_event($timeToStart, CronUtils::INTERVAL_WEEKLY, self::USAGE_TRACKING_CRON_HOOK);
        }

        if (PluginData::getInstance()->getStatus() !== PluginData::PLUGIN_STATUS_ACTIVE) {
            PluginData::getInstance()->setStatus(PluginData::PLUGIN_STATUS_ACTIVE);
            CommStats::pluginSend();
        }
    }

    /**
     * Deactivation action
     *
     * @return void
     */
    public static function deactivationAction()
    {
        // Unschedule custom cron event for cleanup if it's scheduled
        if (wp_next_scheduled(self::USAGE_TRACKING_CRON_HOOK)) {
            $timestamp = wp_next_scheduled(self::USAGE_TRACKING_CRON_HOOK);
            wp_unschedule_event($timestamp, self::USAGE_TRACKING_CRON_HOOK);
        }

        PluginData::getInstance()->setStatus(PluginData::PLUGIN_STATUS_INACTIVE);
        CommStats::pluginSend();
    }

    /**
     * Add package build,
     * don't use PluginData::getInstance()->addPackageBuild() directly in hook to avoid useless init
     *
     * @param DUP_Package $package Package
     * @param int         $status  Status DUP_PRO_PackageStatus Enum
     *
     * @return void
     */
    public static function addPackageBuild(DUP_Package $package, $status)
    {
        if ($status >= DUP_PackageStatus::CREATED && $status < DUP_PackageStatus::COMPLETE) {
            return;
        }
        PluginData::getInstance()->addPackageBuild($package);
    }

    /**
     * Add site size statistics
     *
     * @param DUP_Package          $package Package
     * @param array<string, mixed> $report  Scan report
     *
     * @return void
     */
    public static function addSiteSizes(DUP_Package $package, $report)
    {
        if ($package->Archive->ExportOnlyDB) {
            return;
        }

        PluginData::getInstance()->setSiteSize(
            $report['ARC']['USize'],
            $report['ARC']['UFullCount'],
            $report['DB']['RawSize'],
            $report['DB']['TableCount']
        );
    }

    /**
     * Is tracking allowed
     *
     * @return bool
     */
    public static function isTrackingAllowed()
    {
        if (DUPLICATOR_USTATS_DISALLOW) { // @phpstan-ignore-line
            return false;
        }

        return DUP_Settings::Get('usage_tracking', false);
    }

    /**
     * Send plugin statistics
     *
     * @return void
     */
    public static function sendPluginStatCron()
    {
        if (!self::isTrackingAllowed()) {
            return;
        }

        DUP_Log::trace("CRON: Sending plugin statistics");
        CommStats::pluginSend();
    }
}
