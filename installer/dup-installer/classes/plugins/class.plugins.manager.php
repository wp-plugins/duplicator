<?php

/**
 * Original installer files manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;

require_once(DUPX_INIT . '/classes/plugins/class.plugin.item.php');
require_once(DUPX_INIT . '/classes/plugins/class.plugin.custom.actions.php');
require_once(DUPX_INIT . '/classes/utilities/class.u.remove.redundant.data.php');

/**
 * Original installer files manager
 * singleton class
 */
final class DUPX_Plugins_Manager
{
    const SLUG_WOO_ADMIN             = 'woocommerce-admin/woocommerce-admin.php';
    const SLUG_SIMPLE_SSL            = 'really-simple-ssl/rlrsssl-really-simple-ssl.php';
    const SLUG_ONE_CLICK_SSL         = 'one-click-ssl/ssl.php';
    const SLUG_WP_FORCE_SSL          = 'wp-force-ssl/wp-force-ssl.php';
    const SLUG_RECAPTCHA             = 'simple-google-recaptcha/simple-google-recaptcha.php';
    const SLUG_WPBAKERY_PAGE_BUILDER = 'js_composer/js_composer.php';
    const SLUG_DUPLICATOR_PRO        = 'duplicator-pro/duplicator-pro.php';
    const SLUG_DUPLICATOR_LITE       = 'duplicator/duplicator.php';
    const SLUG_DUPLICATOR_TESTER     = 'duplicator-tester-plugin/duplicator-tester.php';
    const SLUG_WPS_HIDE_LOGIN        = 'wps-hide-login/wps-hide-login.php';
    const SLUG_POPUP_MAKER           = 'popup-maker/popup-maker.php';
    const SLUG_JETPACK               = 'jetpack/jetpack.php';
    const SLUG_WP_ROCKET             = 'wp-rocket/wp-rocket.php';
    const SLUG_BETTER_WP_SECURITY    = 'better-wp-security/better-wp-security.php';
    const SLUG_HTTPS_REDIRECTION     = 'https-redirection/https-redirection.php';
    const OPTION_ACTIVATE_PLUGINS    = 'duplicator_activate_plugins_after_installation';

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @var DUPX_Plugin_item[]
     */
    private $plugins = array();

    /**
     *
     * @var DUPX_Plugin_item[]
     */
    private $pluginsToActivate = array();

    /**
     *
     * @var array
     */
    private $pluginsAutoDeactivate = array();

    /**
     *
     * @var DUPX_Plugin_item[]
     */
    private $unistallList = array();

    /**
     *
     * @var DUPX_Plugin_custom_actions[]
     */
    private $customPluginsActions = array();

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {

        foreach (DUPX_ArchiveConfig::getInstance()->wpInfo->plugins as $pluginInfo) {
            $this->plugins[$pluginInfo->slug] = new DUPX_Plugin_item((array) $pluginInfo);
        }

        $this->setCustomPluginsActions();

        Log::info('CONSTRUCT PLUGINS OBJECTS: ' . Log::v2str($this->plugins), Log::LV_HARD_DEBUG);
    }

    private function setCustomPluginsActions()
    {
        $default    = DUPX_Plugin_custom_actions::BY_DEFAULT_ENABLED;
        $afterLogin = true;
        $longMsg    = '';

        $this->customPluginsActions[self::SLUG_DUPLICATOR_LITE]   = new DUPX_Plugin_custom_actions(
            self::SLUG_DUPLICATOR_LITE,
            $default,
            $afterLogin,
            $longMsg
        );
        $this->customPluginsActions[self::SLUG_DUPLICATOR_TESTER] = new DUPX_Plugin_custom_actions(
            self::SLUG_DUPLICATOR_TESTER,
            $default,
            $afterLogin,
            $longMsg
        );
        $this->customPluginsActions[self::SLUG_DUPLICATOR_PRO]    = new DUPX_Plugin_custom_actions(
            self::SLUG_DUPLICATOR_PRO,
            DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED,
            false,
            'Duplicator PRO has been deactivated because in the new versions it is not possible to ' .
            'have Duplicator PRO active at the same time as PRO.'
        );

        $longMsg = "This plugin is deactivated by default automatically. "
            . "<strong>You must reactivate from the WordPress admin panel after completing the installation</strong> "
            . "or from the plugins tab."
            . " Your site's frontend will render properly after reactivating the plugin.";
        $this->customPluginsActions[self::SLUG_WPBAKERY_PAGE_BUILDER] = new DUPX_Plugin_custom_actions(
            self::SLUG_WPBAKERY_PAGE_BUILDER,
            DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );
        $this->customPluginsActions[self::SLUG_JETPACK]               = new DUPX_Plugin_custom_actions(
            self::SLUG_JETPACK,
            DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );

        $longMsg = "This plugin is deactivated by default automatically due to issues that one may encounter when migrating. "
            . "<strong>You must reactivate from the WordPress admin panel after completing the installation</strong> "
            . "or from the plugins tab."
            . " Your site's frontend will render properly after reactivating the plugin.";

        $this->customPluginsActions[self::SLUG_POPUP_MAKER]    = new DUPX_Plugin_custom_actions(
            self::SLUG_POPUP_MAKER,
            DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );
        $this->customPluginsActions[self::SLUG_WP_ROCKET]      = new DUPX_Plugin_custom_actions(
            self::SLUG_WP_ROCKET,
            DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );
        $this->customPluginsActions[self::SLUG_WPS_HIDE_LOGIN] = new DUPX_Plugin_custom_actions(
            self::SLUG_WPS_HIDE_LOGIN,
            DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );

        $longMsg                                                   = "This plugin is deactivated by default automatically due to issues that one may encounter when migrating. "
            . "<strong>You must reactivate from the WordPress admin panel after completing the installation</strong> "
            . "or from the plugins tab.";
        $this->customPluginsActions[self::SLUG_WOO_ADMIN]          = new DUPX_Plugin_custom_actions(
            self::SLUG_WOO_ADMIN,
            DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );
        $this->customPluginsActions[self::SLUG_BETTER_WP_SECURITY] = new DUPX_Plugin_custom_actions(
            self::SLUG_BETTER_WP_SECURITY,
            DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED,
            true,
            $longMsg
        );
    }

    /**
     *
     * @return DUPX_Plugin_item[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }

    /**
     *
     * @staticvar string[] $dropInsPaths
     * @return string[]
     */
    public function getDropInsPaths()
    {
        static $dropInsPaths = null;

        if (is_null($dropInsPaths)) {
            $dropInsPaths = array();
            foreach ($this->plugins as $plugin) {
                if ($plugin->isDropIns()) {
                    $dropInsPaths[] = $plugin->getPluginArchivePath();
                }
            }
            Log::info('DROP INS PATHS: ' . Log::v2str($dropInsPaths));
        }
        return $dropInsPaths;
    }

    public function pluginExistsInArchive($slug)
    {
        return array_key_exists($slug, $this->plugins);
    }

    /**
     * This function performs status checks on plugins and disables those that must disable creating user messages
     */
    public function preViewChecks()
    {
        $noticeManager = DUPX_NOTICE_MANAGER::getInstance();
        $paramsManager = PrmMng::getInstance();

        if (DUPX_InstallerState::isRestoreBackup()) {
            return;
        }

        $activePlugins = $paramsManager->getValue(PrmMng::PARAM_PLUGINS);
        $saveParams    = false;

        foreach ($this->customPluginsActions as $slug => $customPlugin) {
            if (!isset($this->plugins[$slug])) {
                continue;
            }

            switch ($customPlugin->byDefaultStatus()) {
                case DUPX_Plugin_custom_actions::BY_DEFAULT_DISABLED:
                    if (($delKey = array_search($slug, $activePlugins)) !== false) {
                        $saveParams = true;
                        unset($activePlugins[$delKey]);

                        $noticeManager->addNextStepNotice(array(
                            'shortMsg'    => 'Plugin ' . $this->plugins[$slug]->name . ' disabled by default',
                            'level'       => DUPX_NOTICE_ITEM::NOTICE,
                            'longMsg'     => $customPlugin->byDefaultMessage(),
                            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                            'sections'    => 'plugins'
                            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'custom_plugin_action' . $slug);
                    }
                    break;
                case DUPX_Plugin_custom_actions::BY_DEFAULT_ENABLED:
                    if (!in_array($slug, $activePlugins)) {
                        $saveParams      = true;
                        $activePlugins[] = $slug;

                        $noticeManager->addNextStepNotice(array(
                            'shortMsg'    => 'Plugin ' . $this->plugins[$slug]->name . ' enabled by default',
                            'level'       => DUPX_NOTICE_ITEM::NOTICE,
                            'longMsg'     => $customPlugin->byDefaultMessage(),
                            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                            'sections'    => 'plugins'
                            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'custom_plugin_action' . $slug);
                    }
                    break;
                case DUPX_Plugin_custom_actions::BY_DEFAULT_AUTO:
                default:
                    break;
            }
        }

        if ($saveParams) {
            $paramsManager->setValue(PrmMng::PARAM_PLUGINS, $activePlugins);
            $paramsManager->save();
            $noticeManager->saveNotices();
        }
    }

    public function getStatusCounts($subsiteId = -1)
    {
        $result = array(
            DUPX_Plugin_item::STATUS_MUST_USE       => 0,
            DUPX_Plugin_item::STATUS_DROP_INS       => 0,
            DUPX_Plugin_item::STATUS_NETWORK_ACTIVE => 0,
            DUPX_Plugin_item::STATUS_ACTIVE         => 0,
            DUPX_Plugin_item::STATUS_INACTIVE       => 0
        );

        foreach ($this->plugins as $plugin) {
            $result[$plugin->getOrgiStatus($subsiteId)]++;
        }

        return $result;
    }

    public function getDefaultActivePluginsList($subsiteId = -1)
    {
        $result = array();
        foreach ($this->plugins as $plugin) {
            if (!$plugin->isInactive($subsiteId)) {
                $result[] = $plugin->slug;
            }
        }
        return $result;
    }

    /**
     * return alla plugins slugs list
     *
     * @return string[]
     */
    public function getAllPluginsSlugs()
    {
        return array_keys($this->plugins);
    }

    public function setActions($plugins, $subsiteId = -1)
    {
        Log::info('FUNCTION [' . __FUNCTION__ . ']: plugins ' . Log::v2str($plugins), Log::LV_DEBUG);

        foreach ($this->plugins as $slug => $plugin) {
            $deactivate = false;

            if ($plugin->isForceDisabled()) {
                $deactivate = true;
            } else {
                if (!$this->plugins[$slug]->isInactive($subsiteId) && !in_array($slug, $plugins)) {
                    $deactivate = true;
                }
            }

            if ($deactivate) {
                $this->plugins[$slug]->setDeactivateAction($subsiteId, null, null, false);
            }
        }

        foreach ($plugins as $slug) {
            if (isset($this->plugins[$slug])) {
                $this->plugins[$slug]->setActivationAction($subsiteId, false);
            }
        }

        $this->setAutoActions($subsiteId);
        DUPX_NOTICE_MANAGER::getInstance()->saveNotices();
    }

    public function executeActions($dbh, $subsiteId = -1)
    {
        $activePluginsList          = array();
        $activateOnLoginPluginsList = array();
        $this->unistallList         = array();

        $escapedTablePrefix = mysqli_real_escape_string($dbh, PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX));

        $noticeManager = DUPX_NOTICE_MANAGER::getInstance();

        Log::info('PLUGINS OBJECTS: ' . Log::v2str($this->plugins), Log::LV_HARD_DEBUG);

        foreach ($this->plugins as $plugin) {
            $deactivated = false;
            if ($plugin->deactivateAction) {
                $plugin->deactivate();
                // can't remove deactivate after login
                $deactivated = true;
            } else {
                if ($plugin->isActive($subsiteId)) {
                    $activePluginsList[] = $plugin->slug;
                }
            }

            if ($plugin->activateAction) {
                $activateOnLoginPluginsList[] = $plugin->slug;
                $noticeManager->addFinalReportNotice(array(
                    'shortMsg' => 'Activate ' . $plugin->name . ' after you login.',
                    'level'    => DUPX_NOTICE_ITEM::NOTICE,
                    'sections' => 'plugins'
                ));
            }
        }

        // force duplicator lite activation
        if (!array_key_exists(self::SLUG_DUPLICATOR_LITE, $activePluginsList)) {
            $activePluginsList[] = self::SLUG_DUPLICATOR_LITE;
        }

        // force duplicator tester activation if exists
        if ($this->pluginExistsInArchive(self::SLUG_DUPLICATOR_TESTER) && !array_key_exists(self::SLUG_DUPLICATOR_TESTER, $activePluginsList)) {
            $activePluginsList[] = self::SLUG_DUPLICATOR_TESTER;
        }

        Log::info('Active plugins: ' . Log::v2str($activePluginsList), Log::LV_DEBUG);

        $value       = mysqli_real_escape_string($dbh, @serialize($activePluginsList));
        $optionTable = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getOptionsTableName());
        $query       = "UPDATE `" . $optionTable . "` SET option_value = '" . $value . "'  WHERE option_name = 'active_plugins' ";

        if (!DUPX_DB::mysqli_query($dbh, $query)) {
            $noticeManager->addFinalReportNotice(array(
                'shortMsg'    => 'QUERY ERROR: MySQL',
                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsg'     => "Error description: " . mysqli_error($dbh),
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'sections'    => 'database'
            ));
            throw new Exception("Database error description: " . mysqli_error($dbh));
        }

        $value       = mysqli_real_escape_string($dbh, @serialize($activateOnLoginPluginsList));
        $optionTable = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getOptionsTableName());
        $query       = "INSERT INTO `" . $optionTable . "` (option_name, option_value) VALUES('" . self::OPTION_ACTIVATE_PLUGINS . "','" . $value . "') ON DUPLICATE KEY UPDATE option_name=\"" . self::OPTION_ACTIVATE_PLUGINS . "\"";
        if (!DUPX_DB::mysqli_query($dbh, $query)) {
            $noticeManager->addFinalReportNotice(array(
                'shortMsg'    => 'QUERY ERROR: MySQL',
                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                'longMsg'     => "Error description: " . mysqli_error($dbh),
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'sections'    => 'database'
            ));
            throw new Exception("Database error description: " . mysqli_error($dbh));
        }

        return true;
    }

    /**
     * remove inactive plugins
     * this method must calle after wp-config set
     *
     */
    public function unistallInactivePlugins()
    {
        Log::info('FUNCTION [' . __FUNCTION__ . ']: unistall inactive plugins');

        foreach ($this->unistallList as $plugin) {
            if ($plugin->uninstall()) {
                Log::info("UNINSTALL PLUGIN " . Log::v2str($plugin->slug) . ' DONE');
            } else {
                Log::info("UNINSTALL PLUGIN " . Log::v2str($plugin->slug) . ' FAILED');
            }
        }
    }

    /**
     * Get Automatic actions for plugins
     *
     * @return array key as plugin slug and val as plugin title
     */
    public function setAutoActions($subsiteId = -1)
    {
        $paramManager                = PrmMng::getInstance();
        $this->pluginsAutoDeactivate = array();

        if (!DUPX_U::is_ssl() && isset($this->plugins[self::SLUG_SIMPLE_SSL]) && $this->plugins[self::SLUG_SIMPLE_SSL]->isActive($subsiteId)) {
            Log::info('Really Simple SSL [as Non-SSL installation] will be deactivated', Log::LV_DEBUG);
            $shortMsg = "Deactivated plugin: " . $this->plugins[self::SLUG_SIMPLE_SSL]->name;
            $longMsg  = "This plugin is deactivated because you are migrating from SSL (HTTPS) to Non-SSL (HTTP).<br>" .
                "If it was not deactivated, you would not be able to login.";

            $this->plugins[self::SLUG_SIMPLE_SSL]->setDeactivateAction($subsiteId, $shortMsg, $longMsg);
        }

        if (!DUPX_U::is_ssl() && isset($this->plugins[self::SLUG_ONE_CLICK_SSL]) && $this->plugins[self::SLUG_ONE_CLICK_SSL]->isActive($subsiteId)) {
            Log::info('One Click SSL [as Non-SSL installation] will be deactivated', Log::LV_DEBUG);
            $shortMsg = "Deactivated plugin: " . $this->plugins[self::SLUG_ONE_CLICK_SSL]->name;
            $longMsg  = "This plugin is deactivated because you are migrating from SSL (HTTPS) to Non-SSL (HTTP).<br>" .
                "If it was not deactivated, you would not be able to login.";

            $this->plugins[self::SLUG_ONE_CLICK_SSL]->setDeactivateAction($subsiteId, $shortMsg, $longMsg);
        }

        if (!DUPX_U::is_ssl() && isset($this->plugins[self::SLUG_WP_FORCE_SSL]) && $this->plugins[self::SLUG_WP_FORCE_SSL]->isActive($subsiteId)) {
            Log::info('WP Force SSL & HTTPS Redirect [as Non-SSL installation] will be deactivated', Log::LV_DEBUG);
            $shortMsg = "Deactivated plugin: " . $this->plugins[self::SLUG_WP_FORCE_SSL]->name;
            $longMsg  = "This plugin is deactivated because you are migrating from SSL (HTTPS) to Non-SSL (HTTP).<br>" .
                "If it was not deactivated, you would not be able to login.";

            $this->plugins[self::SLUG_WP_FORCE_SSL]->setDeactivateAction($subsiteId, $shortMsg, $longMsg);
        }

        if (!DUPX_U::is_ssl() && isset($this->plugins[self::SLUG_HTTPS_REDIRECTION]) && $this->plugins[self::SLUG_HTTPS_REDIRECTION]->isActive($subsiteId)) {
            Log::info('Easy HTTPS Redirection (SSL) [as Non-SSL installation] will be deactivated', Log::LV_DEBUG);
            $shortMsg = "Deactivated plugin: " . $this->plugins[self::SLUG_HTTPS_REDIRECTION]->name;
            $longMsg  = "This plugin is deactivated because you are migrating from SSL (HTTPS) to Non-SSL (HTTP).<br>" .
                "If it was not deactivated, you would not be able to login.";

            $this->plugins[self::SLUG_HTTPS_REDIRECTION]->setDeactivateAction($subsiteId, $shortMsg, $longMsg);
        }

        if (
            $paramManager->getValue(PrmMng::PARAM_SITE_URL_OLD) != $paramManager->getValue(PrmMng::PARAM_SITE_URL) &&
            isset($this->plugins[self::SLUG_RECAPTCHA]) && $this->plugins[self::SLUG_RECAPTCHA]->isActive($subsiteId)
        ) {
            Log::info('Simple Google reCAPTCHA [as package creation site URL and the installation site URL are different] will be deactivated', Log::LV_DEBUG);
            $shortMsg = "Deactivated plugin: " . $this->plugins[self::SLUG_RECAPTCHA]->name;
            $longMsg  = "It is deactivated because the Google Recaptcha required reCaptcha site key which is bound to the site's address." .
                "Your package site's address and installed site's address doesn't match. " .
                "You can reactivate it from the installed site login panel after completion of the installation.<br>" .
                "<strong>Please do not forget to change the reCaptcha site key after activating it.</strong>";

            $this->plugins[self::SLUG_RECAPTCHA]->setDeactivateAction($subsiteId, $shortMsg, $longMsg);
        }

        foreach ($this->customPluginsActions as $slug => $customPlugin) {
            if (!isset($this->plugins[$slug])) {
                continue;
            }
            if (!$this->plugins[$slug]->isInactive($subsiteId) && $customPlugin->isEnableAfterLogin()) {
                $this->plugins[$slug]->setActivationAction($subsiteId);
            }
        }

        Log::info('Activated plugins listed here will be deactivated: ' . Log::v2str(array_keys($this->pluginsAutoDeactivate)));
    }

    private function __clone()
    {
    }
}
