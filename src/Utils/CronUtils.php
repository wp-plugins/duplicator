<?php

namespace Duplicator\Utils;

class CronUtils
{
    const INTERVAL_DAILTY  = 'duplicator_daily_cron';
    const INTERVAL_WEEKLY  = 'duplicator_weekly_cron';
    const INTERVAL_MONTHLY = 'duplicator_monthly_cron';

    /**
     * Init WordPress hooks
     *
     * @return void
     */
    public static function init()
    {
        add_filter('cron_schedules', array(__CLASS__, 'defaultCronIntervals'));
    }

    /**
     * Add duplicator pro cron schedules
     *
     * @param array<string, array<string,int|string>> $schedules schedules
     *
     * @return array<string, array<string,int|string>>
     */
    public static function defaultCronIntervals($schedules)
    {
        $schedules[self::INTERVAL_DAILTY] = array(
            'interval' => DAY_IN_SECONDS,
            'display'  => __('Once a Day', 'duplicator'),
        );

        $schedules[self::INTERVAL_WEEKLY] = array(
            'interval' => WEEK_IN_SECONDS,
            'display'  => __('Once a Week', 'duplicator'),
        );

        $schedules[self::INTERVAL_MONTHLY] = array(
            'interval' => MONTH_IN_SECONDS,
            'display'  => __('Once a Month', 'duplicator'),
        );

        return $schedules;
    }
}
