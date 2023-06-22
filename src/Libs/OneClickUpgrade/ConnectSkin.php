<?php

namespace Duplicator\Libs\OneClickUpgrade;

use Duplicator\Libs\OneClickUpgrade\PluginSilentUpgraderSkin;
use DUP_Log;

/**
 * Duplicator Connect Skin.
 *
 * Duplicator Connect is our service that makes it easy for non-techy users to
 * upgrade to Duplicator Pro without having to manually install Duplicator Pro plugin.
 *
 * @since 1.5.5
 * @since 1.5.6.1 Extend PluginSilentUpgraderSkin and clean up the class.
 */
class ConnectSkin extends PluginSilentUpgraderSkin
{
    /**
     * Instead of outputting HTML for errors, json_encode the errors and send them
     * back to the Ajax script for processing.
     *
     * @since 1.5.5
     *
     * @param array $errors Array of errors with the install process.
     *
     * @return void
     */
    public function error($errors)
    {
        if (! empty($errors)) {
            DUP_Log::traceObject("Array of errors with the install process:", $errors);
            echo \wp_json_encode(
                array(
                    'error' => \esc_html__('There was an error installing Duplicator Pro. Please try again.', 'duplicator'),
                )
            );
            die;
        }
    }
}
