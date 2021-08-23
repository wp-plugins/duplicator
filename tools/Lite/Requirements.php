<?php
/**
 * Class that collects the functions of initial checks on the requirements to run the plugin
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Lite;

defined('ABSPATH') || exit;

class Requirements
{
    const DUP_PRO_PLUGIN_KEY = 'duplicator-pro/duplicator-pro.php';

    /**
     * 
     * @var string // current plugin file full path
     */
    protected static $pluginFile = '';

    /**
     * 
     * @var string // message on deactivation
     */
    protected static $deactivationMessage = '';

    /**
     * This function checks the requirements to run Duplicator.
     * At this point WordPress is not yet completely initialized so functionality is limited.
     * It need to hook into "admin_init" to get the full functionality of WordPress.
     * 
     * @param string $pluginFile    // main plugin file path
     * @return boolean              // true if plugin can be executed
     */
    public static function canRun($pluginFile)
    {
        $result           = true;
        self::$pluginFile = $pluginFile;

        if ($result === true && self::isPluginActive(self::DUP_PRO_PLUGIN_KEY)) {
            add_action('admin_init', array(__CLASS__, 'addProEnableNotice'));
            $pluginUrl = (is_multisite() ? network_admin_url('plugins.php') : admin_url('plugins.php'));
            self::$deactivationMessage = __('Can\'t enable Duplicator LITE if the PRO version is enabled.', 'duplicator') . '<br/>'
                . __('Please deactivate Duplicator PRO, then reactivate LITE version from the ', 'duplicator')
                . "<a href='" . $pluginUrl . "'>" . __('plugins page', 'duplicator') . ".</a>";
            $result = false;
        }

        if ($result === false) {
            register_activation_hook($pluginFile, array(__CLASS__, 'deactivateOnActivation'));
        }

        return $result;
    }

    /**
     * 
     * @param string $plugin
     * @return boolean // return true if plugin key is active and plugin file exists
     */
    protected static function isPluginActive($plugin)
    {
        $isActive = false;
        if (in_array($plugin, (array) get_option('active_plugins', array()))) {
            $isActive = true;
        }

        if (is_multisite()) {
            $plugins = get_site_option('active_sitewide_plugins');
            if (isset($plugins[$plugin])) {
                $isActive = true;
            }
        }

        return ($isActive && file_exists(WP_PLUGIN_DIR . '/' . $plugin));
    }

    /**
     * display admin notice only if user can manage plugins.
     */
    public static function addProEnableNotice()
    {
        if (current_user_can('activate_plugins')) {
            add_action('admin_notices', array(__CLASS__, 'proEnabledNotice'));
        }
    }

    /**
     * display admin notice 
     */
    public static function addMultisiteNotice()
    {
        if (current_user_can('activate_plugins')) {
            add_action('admin_notices', array(__CLASS__, 'multisiteNotice'));
        }
    }

    /**
     * deactivate current plugin on activation
     */
    public static function deactivateOnActivation()
    {
        deactivate_plugins(plugin_basename(self::$pluginFile));
        wp_die(self::$deactivationMessage);
    }

    /**
     * Display admin notice if duplicator pro is enabled
     */
    public static function proEnabledNotice()
    {
        $pluginUrl = (is_multisite() ? network_admin_url('plugins.php') : admin_url('plugins.php'));
        ?>
        <div class="error notice">
            <p>
                <span class="dashicons dashicons-warning"></span>
                <b><?php _e('Duplicator Notice:', 'duplicator'); ?></b>
                <?php _e('The "Duplicator Lite" and "Duplicator Pro" plugins cannot both be active at the same time.  ', 'duplicator'); ?>
            </p>
            <p>
                <?php _e('To use "Duplicator LITE" please deactivate "Duplicator PRO" from the ', 'duplicator-pro'); ?>
                <a href="<?php echo esc_url($pluginUrl); ?>">
                    <?php _e('plugins page', 'duplicator'); ?>.
                </a>
            </p>
        </div>
        <?php
    }
}
