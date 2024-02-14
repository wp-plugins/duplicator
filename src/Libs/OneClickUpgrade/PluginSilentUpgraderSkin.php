<?php // phpcs:ignore

namespace Duplicator\Libs\OneClickUpgrade;

// class \WP_Upgrader_Skin
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader-skin.php';

/**
 * Class PluginSilentUpgraderSkin.
 *
 * @internal Please do not use this class outside of core Duplicator development. May be removed at any time.
 *
 * @since 1.5.6.1
 */
class PluginSilentUpgraderSkin extends \WP_Upgrader_Skin
{
    /**
     * Empty out the header of its HTML content and only check to see if it has
     * been performed or not.
     *
     * @since 1.5.6.1
     *
     * @return void
     */
    public function header()
    {
    }

    /**
     * Empty out the footer of its HTML contents.
     *
     * @since 1.5.6.1
     *
     * @return void
     */
    public function footer()
    {
    }

    /**
     * Instead of outputting HTML for errors, just return them.
     * Ajax request will just ignore it.
     *
     * @since 1.5.6.1
     *
     * @param array $errors Array of errors with the install process.
     *
     * @return array
     */
    public function error($errors)
    {
        return $errors;
    }

    /**
     * Empty out JavaScript output that calls function to decrement the update counts.
     *
     * @since 1.5.6.1
     *
     * @param string $type Type of update count to decrement.
     *
     * @return void
     */
    public function decrementUpdateCount($type)
    {
    }
}
