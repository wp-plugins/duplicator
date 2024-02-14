<?php

namespace Duplicator\Utils\UsageStatistics;

use DUP_Log;
use Duplicator\Libs\Snap\SnapLog;
use Error;
use Exception;
use WP_Error;

class CommStats
{
    const API_VERSION            = '1.0';
    const DEFAULT_REMOTE_HOST    = 'https://connect.duplicator.com';
    const END_POINT_PLUGIN_STATS = '/api/ustats/addLiteStats';
    const END_POINT_DISABLE      = '/api/ustats/disable';
    const END_POINT_INSTALLER    = '/api/ustats/installer';

    /**
     * Send plugin statistics
     *
     * @return bool true if data was sent successfully, false otherwise
     */
    public static function pluginSend()
    {
        if (!StatsBootstrap::isTrackingAllowed()) {
            return false;
        }

        $data = PluginData::getInstance()->getDataToSend();

        if (self::request(self::END_POINT_PLUGIN_STATS, $data)) {
            PluginData::getInstance()->updateLastSendTime();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Disabled usage tracking
     *
     * @return bool true if data was sent successfully, false otherwise
     */
    public static function disableUsageTracking()
    {
        if (DUPLICATOR_USTATS_DISALLOW) { // @phpstan-ignore-line
            // Don't use StatsBootstrap::isTrackingAllowed beacause on disalbe usage tracking i necessary disable the tracking on server
            return false;
        }

        // Remove usage tracking data on server
        $data = PluginData::getInstance()->getDisableDataToSend();
        return self::request(self::END_POINT_DISABLE, $data, 'Disable usage tracking error');
    }

    /**
     * Sent installer statistics
     *
     * @return bool true if data was sent successfully, false otherwise
     */
    public static function installerSend()
    {
        if (!StatsBootstrap::isTrackingAllowed()) {
            return false;
        }

        $data = InstallerData::getInstance()->getDataToSend();
        return self::request(self::END_POINT_INSTALLER, $data, 'Installer usage tracking error');
    }

    /**
     * Request to usage tracking server
     *
     * @param string               $endPoint            end point
     * @param array<string, mixed> $data                data to send
     * @param string               $traceMessagePerefix trace message prefix
     *
     * @return bool true if data was sent successfully, false otherwise
     */
    protected static function request($endPoint, $data, $traceMessagePerefix = 'Error sending usage tracking')
    {
        try {
            global $wp_version;

            $agent_string = "WordPress/" . $wp_version;
            $postParams   = array(
                'method'      => 'POST',
                'timeout'     => 10,
                'redirection' => 5,
                'sslverify'   => false,
                'httpversion' => '1.1',
            //'blocking'    => false,
                'user-agent'  => $agent_string,
                'body'        => $data,
            );

            $url      = self::getRemoteHost() . $endPoint . '/';
            $response = wp_remote_post($url, $postParams);

            if (is_wp_error($response)) {
                /** @var WP_Error $response */
                DUP_Log::trace('URL Request: ' . $url);
                DUP_Log::trace($traceMessagePerefix . ' code: ' . $response->get_error_code());
                DUP_Log::trace('Error message: ' . $response->get_error_message());
                return false;
            } elseif ($response['response']['code'] < 200 || $response['response']['code'] >= 300) {
                DUP_Log::trace('URL Request: ' . $url);
                DUP_Log::trace($traceMessagePerefix . ' code: ' . $response['response']['code']);
                DUP_Log::trace('Error message: ' . $response['response']['message']);
                DUP_Log::traceObject('Data', $data);
                return false;
            } else {
                DUP_Log::trace('Usage tracking updated successfully');
                return true;
            }
        } catch (Exception $e) {
            DUP_Log::trace($traceMessagePerefix . '  trace msg: ' . $e->getMessage() . "\n" . SnapLog::getTextException($e, false));
            return false;
        } catch (Error $e) {
            DUP_Log::trace($traceMessagePerefix . '  trace msg: ' . $e->getMessage() . "\n" . SnapLog::getTextException($e, false));
            return false;
        }
    }

    /**
     * Get remote host
     *
     * @return string
     */
    public static function getRemoteHost()
    {
        if (DUPLICATOR_CUSTOM_STATS_REMOTE_HOST != '') {  // @phpstan-ignore-line
            return DUPLICATOR_CUSTOM_STATS_REMOTE_HOST;
        } else {
            return self::DEFAULT_REMOTE_HOST;
        }
    }
}
