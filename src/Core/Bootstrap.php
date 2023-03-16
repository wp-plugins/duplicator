<?php

/**
 * Interface that collects the functions of initial duplicator Bootstrap
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Core;

use DUP_Constants;
use DUP_CTRL_Package;
use DUP_CTRL_Tools;
use DUP_CTRL_UI;
use DUP_Custom_Host_Manager;
use DUP_DB;
use DUP_LITE_Plugin_Upgrade;
use DUP_Log;
use DUP_Package;
use DUP_Package_Screen;
use DUP_Settings;
use DUP_UI_Notice;
use DUP_Util;
use DUP_Web_Services;
use Duplicator\Ajax\ServicesDashboard;
use Duplicator\Ajax\ServicesEducation;
use Duplicator\Ajax\ServicesExtraPlugins;
use Duplicator\Controllers\WelcomeController;
use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Core\Notifications\Notice;
use Duplicator\Core\Notifications\NoticeBar;
use Duplicator\Core\Notifications\Review;
use Duplicator\Core\Views\TplMng;
use Duplicator\Libs\Upsell;
use Duplicator\Views\DashboardWidget;
use Duplicator\Views\EducationElements;

class Bootstrap
{
    /**
     * Init plugin
     *
     * @return void
     */
    public static function init()
    {
        add_action('init', array(__CLASS__, 'hookWpInit'));

        if (is_admin()) {
            add_action('plugins_loaded', array(__CLASS__, 'update'));
            add_action('plugins_loaded', array(__CLASS__, 'wpfrontIntegrate'));
            add_action('plugins_loaded', array(__CLASS__, 'loadTextdomain'));

            /* ========================================================
            * ACTIVATE/DEACTIVE/UPDATE HOOKS
            * =====================================================  */
            register_activation_hook(DUPLICATOR_LITE_FILE, array('DUP_LITE_Plugin_Upgrade', 'onActivationAction'));
        }
    }

    /**
     * Method called on wordpress hook init action
     *
     * @return void
     */
    public static function hookWpInit()
    {
        if (is_admin()) {
            $GLOBALS['CTRLS_DUP_CTRL_UI']      = new DUP_CTRL_UI();
            $GLOBALS['CTRLS_DUP_CTRL_Tools']   = new DUP_CTRL_Tools();
            $GLOBALS['CTRLS_DUP_CTRL_Package'] = new DUP_CTRL_Package();

            add_action('admin_init', array(__CLASS__, 'adminInit'));
            add_action('admin_menu', array(__CLASS__, 'menuInit'));
            add_action('admin_footer', array(__CLASS__, 'adjustProMenuItemClass'));
            add_action('admin_enqueue_scripts', array(__CLASS__, 'adminEqueueScripts'));

            add_action('wp_ajax_duplicator_active_package_info', 'duplicator_active_package_info');
            add_action('wp_ajax_duplicator_package_scan', 'duplicator_package_scan');
            add_action('wp_ajax_duplicator_package_build', 'duplicator_package_build');
            add_action('wp_ajax_duplicator_package_delete', 'duplicator_package_delete');
            add_action('wp_ajax_duplicator_duparchive_package_build', 'duplicator_duparchive_package_build');

            add_filter('admin_body_class', array(__CLASS__, 'addBodyClass'));
            add_filter('plugin_action_links', array(__CLASS__, 'manageLink'), 10, 2);
            add_filter('plugin_row_meta', array(__CLASS__, 'metaLinks'), 10, 2);

            //Init Class
            DUP_Custom_Host_Manager::getInstance()->init();
            DUP_Settings::init();
            DUP_Log::Init();
            DUP_Util::init();
            DUP_DB::init();
            MigrationMng::init();
            Notice::init();
            NoticeBar::init();
            Review::init();
            DUP_UI_Notice::init();
            DUP_Web_Services::init();
            WelcomeController::init();
            DashboardWidget::init();
            EducationElements::init();
            $dashboardService = new ServicesDashboard();
            $dashboardService->init();
            $extraPlugin = new ServicesExtraPlugins();
            $extraPlugin->init();
            $educationService = new ServicesEducation();
            $educationService->init();
        }
    }

    /**
     * Return admin body classes
     *
     * @param string $classes classes
     *
     * @return string
     */
    public static function addBodyClass($classes)
    {
        if (ControllersManager::isDuplicatorPage()) {
            $classes .= ' duplicator-pages';
        }
        return $classes;
    }

    /**
     * Hooked into `plugins_loaded`.  Routines used to update the plugin
     *
     * @return null
     */
    public static function update()
    {
        if (DUPLICATOR_VERSION != get_option(DUP_LITE_Plugin_Upgrade::DUP_VERSION_OPT_KEY)) {
            DUP_LITE_Plugin_Upgrade::onActivationAction();
            // $snapShotDirPerm = substr(sprintf("%o", fileperms(DUP_Settings::getSsdirPath())),-4);
        }
        load_plugin_textdomain('duplicator');
    }

    /**
     * Load text domain for translation
     *
     * @return void
     */
    public static function loadTextdomain()
    {
        load_plugin_textdomain('duplicator', false, false);
    }

    /**
     * User role editor integration
     *
     * @return void
     */
    public static function wpfrontIntegrate()
    {
        if (DUP_Settings::Get('wpfront_integrate')) {
            do_action('wpfront_user_role_editor_duplicator_init', array('export', 'manage_options', 'read'));
        }
    }

    /**
     * Hooked into `admin_init`.  Init routines for all admin pages
     *
     * @return void
     */
    public static function adminInit()
    {
        add_action('in_admin_header', array('Duplicator\\Views\\ViewHelper', 'adminLogoHeader'), 100);

        /* CSS */
        wp_register_style('dup-jquery-ui', DUPLICATOR_PLUGIN_URL . 'assets/css/jquery-ui.css', null, "1.11.2");
        wp_register_style('dup-font-awesome', DUPLICATOR_PLUGIN_URL . 'assets/css/fontawesome-all.min.css', null, '5.7.2');
        wp_register_style('dup-plugin-global-style', DUPLICATOR_PLUGIN_URL . 'assets/css/global_admin_style.css', null, DUPLICATOR_VERSION);
        wp_register_style('dup-plugin-style', DUPLICATOR_PLUGIN_URL . 'assets/css/style.css', array('dup-plugin-global-style'), DUPLICATOR_VERSION);

        wp_register_style('dup-jquery-qtip', DUPLICATOR_PLUGIN_URL . 'assets/js/jquery.qtip/jquery.qtip.min.css', null, '2.2.1');
        wp_register_style('dup-parsley-style', DUPLICATOR_PLUGIN_URL . 'assets/css/parsley.css', null, '2.3.5');
        /* JS */
        wp_register_script('dup-handlebars', DUPLICATOR_PLUGIN_URL . 'assets/js/handlebars.min.js', array('jquery'), '4.0.10');
        wp_register_script('dup-parsley', DUPLICATOR_PLUGIN_URL . 'assets/js/parsley.min.js', array('jquery'), '1.1.18');
        wp_register_script('dup-jquery-qtip', DUPLICATOR_PLUGIN_URL . 'assets/js/jquery.qtip/jquery.qtip.min.js', array('jquery'), '2.2.1');

        add_action('admin_head', array('DUP_UI_Screen', 'getCustomCss'));
        // Clean tmp folder
        DUP_Package::not_active_files_tmp_cleanup();

        $unhook_third_party_js  = DUP_Settings::Get('unhook_third_party_js');
        $unhook_third_party_css = DUP_Settings::Get('unhook_third_party_css');
        if ($unhook_third_party_js || $unhook_third_party_css) {
            add_action('admin_enqueue_scripts', array(__CLASS__, 'unhookThirdPartyAssets'), 99999, 1);
        }
    }

    /**
     * Hooked into `admin_menu`.  Loads all of the wp left nav admin menus for Duplicator
     *
     * @return void
     */
    public static function menuInit()
    {
        //SVG Icon: See https://websemantics.uk/tools/image-to-data-uri-converter/
        $hook_prefix = add_menu_page('Duplicator Plugin', 'Duplicator', 'export', 'duplicator', null, DUP_Constants::ICON_SVG);
        add_action('admin_print_scripts-' . $hook_prefix, array(__CLASS__, 'scripts'));
        add_action('admin_print_styles-' . $hook_prefix, array(__CLASS__, 'styles'));

        //Submenus are displayed in the same order they have in the array
        $subMenuItems = self::getSubmenuItems();
        foreach ($subMenuItems as $k => $subMenuItem) {
            $subMenuItems[$k]['hook_prefix'] = add_submenu_page(
                $subMenuItem['parent_slug'],
                $subMenuItem['page_title'],
                $subMenuItem['menu_title'],
                $subMenuItem['capability'],
                $subMenuItem['menu_slug'],
                $subMenuItem['callback'],
                $k
            );
            add_action('admin_print_scripts-' . $subMenuItems[$k]['hook_prefix'], array(__CLASS__, 'scripts'));

            if (isset($subMenuItem['enqueue_style_callback'])) {
                add_action('admin_print_styles-' . $subMenuItems[$k]['hook_prefix'], $subMenuItem['enqueue_style_callback']);
            }
            add_action('admin_print_styles-' . $subMenuItems[$k]['hook_prefix'], array(__CLASS__, 'styles'));
        }
        $GLOBALS['DUP_Package_Screen'] = new DUP_Package_Screen($subMenuItems[0]['hook_prefix']);
    }

    /**
     * Submenu datas
     *
     * @return array[]
     */
    protected static function getSubmenuItems()
    {
        $proTitle   = '<span id="dup-link-upgrade-highlight">' . __('Upgrade to Pro', 'duplicator') . '</span>';
        $dupMenuNew = '<span class="dup-menu-new">&nbsp;' . __('NEW!', 'duplicator') . '</span>';
        return array(
            array(
                'parent_slug' => 'duplicator',
                'page_title'  => __('Packages', 'duplicator'),
                'menu_title'  => __('Packages', 'duplicator'),
                'capability'  => 'export',
                'menu_slug'   => ControllersManager::MAIN_MENU_SLUG,
                'callback'    => function () {
                    include(DUPLICATOR_PLUGIN_PATH . 'views/packages/controller.php');
                }
            ),
            array(
                'parent_slug'            => 'duplicator',
                'page_title'             => __('Import', 'duplicator'),
                'menu_title'             => __('Import', 'duplicator'),
                'capability'             => 'export',
                'menu_slug'              => ControllersManager::IMPORT_SUBMENU_SLUG,
                'callback'               => function () {
                    TplMng::getInstance()->render('mocks/import/import');
                },
                'enqueue_style_callback' => array(__CLASS__, 'mocksStyles')
            ),
            array(
                'parent_slug'            => 'duplicator',
                'page_title'             => __('Schedules', 'duplicator'),
                'menu_title'             => __('Schedules', 'duplicator') . $dupMenuNew,
                'capability'             => 'export',
                'menu_slug'              => ControllersManager::SCHEDULES_SUBMENU_SLUG,
                'callback'               => function () {
                    TplMng::getInstance()->render('mocks/schedule/schedules');
                },
                'enqueue_style_callback' => array(__CLASS__, 'mocksStyles')
            ),
            array(
                'parent_slug'            => 'duplicator',
                'page_title'             => __('Storage', 'duplicator'),
                'menu_title'             => '<span class="dup-storages-menu-highlight">' . __('Storage', 'duplicator') . '</span>',
                'capability'             => 'export',
                'menu_slug'              => ControllersManager::STORAGE_SUBMENU_SLUG,
                'callback'               => array('Duplicator\\Controllers\\StorageController', 'render'),
                'enqueue_style_callback' => array(__CLASS__, 'mocksStyles')
            ),
            array(
                'parent_slug'            => 'duplicator',
                'page_title'             => __('Tools', 'duplicator'),
                'menu_title'             => __('Tools', 'duplicator'),
                'capability'             => 'manage_options',
                'menu_slug'              => ControllersManager::TOOLS_SUBMENU_SLUG,
                'callback'               => function () {
                    include(DUPLICATOR_PLUGIN_PATH . 'views/tools/controller.php');
                },
                'enqueue_style_callback' => array(__CLASS__, 'mocksStyles')
            ),
            array(
                'parent_slug'            => 'duplicator',
                'page_title'             => __('Settings', 'duplicator'),
                'menu_title'             => __('Settings', 'duplicator'),
                'capability'             => 'manage_options',
                'menu_slug'              => ControllersManager::SETTINGS_SUBMENU_SLUG,
                'callback'               => function () {
                    include(DUPLICATOR_PLUGIN_PATH . 'views/settings/controller.php');
                }
            ),
            array(
                'parent_slug'            => 'duplicator',
                'page_title'             => __('About Duplicator', 'duplicator'),
                'menu_title'             => __('About Us', 'duplicator'),
                'capability'             => 'manage_options',
                'menu_slug'              => ControllersManager::ABOUT_US_SUBMENU_SLUG,
                'callback'               => array('Duplicator\\Controllers\\AboutUsController', 'render'),
                'enqueue_style_callback' => array('Duplicator\\Controllers\\AboutUsController', 'enqueues')
            ),
            array(
                'parent_slug' => 'duplicator',
                'page_title'  => $proTitle,
                'menu_title'  => $proTitle,
                'capability'  => 'manage_options',
                'menu_slug'   => Upsell::getCampaignUrl('admin-menu', 'Upgrade to Pro'),
                'callback'    => null,
            )
        );
    }

    /**
     * Hooked into `admin_enqueue_scripts`.  Init routines for all admin pages
     *
     * @access global
     * @return null
     */
    public static function adminEqueueScripts()
    {
        wp_enqueue_script('dup-global-script', DUPLICATOR_PLUGIN_URL . 'assets/js/global-admin-script.js', array('jquery'), DUPLICATOR_VERSION, true);
        wp_localize_script(
            'dup-global-script',
            'dup_global_script_data',
            array(
                'nonce_admin_notice_to_dismiss'              => wp_create_nonce('duplicator_admin_notice_to_dismiss'),
                'nonce_settings_callout_to_dismiss'          => wp_create_nonce('duplicator_settings_callout_cta_dismiss'),
                'nonce_packages_bottom_bar_dismiss'          => wp_create_nonce('duplicator_packages_bottom_bar_dismiss'),
                'nonce_email_subscribe'                      => wp_create_nonce('duplicator_email_subscribe'),
                'nonce_dashboard_widged_info'                => wp_create_nonce("duplicator_dashboad_widget_info"),
                'nonce_dashboard_widged_dismiss_recommended' => wp_create_nonce("duplicator_dashboad_widget_dismiss_recommended"),
                'ajaxurl'                                    => admin_url('admin-ajax.php')
            )
        );
        wp_enqueue_style('dup-plugin-global-style');
    }

    /**
     * Add the PRO badge to left sidebar menu item.
     *
     * @return void
     */
    public static function adjustProMenuItemClass()
    {
        //Add to footer so it's applied on hovered item too
        ?>
            <script>jQuery(function($) {
                $('#dup-link-upgrade-highlight').parent().attr('target','_blank');
                $('#dup-link-upgrade-highlight').closest('li').addClass('dup-submenu-upgrade-highlight')
            });
            </script>
            <style>
                .dup-submenu-upgrade-highlight,
                .dup-submenu-upgrade-highlight a,
                .dup-submenu-upgrade-highlight a span#dup-link-upgrade-highlight {
                    background-color: #1da867!important;
                    color: #fff!important;
                    border-color: #fff!important;
                    font-weight: 600!important;
                }

                .dup-storages-menu-highlight {
                    color: #27d584;
                }

                #adminmenu .dup-menu-new {
                    color: #f18200;
                    vertical-align: super;
                    font-size: 9px;
                    font-weight: 600;
                    padding-left: 2px;
                }
            </style>
        <?php
    }

    /**
     * Loads all required javascript libs/source for DupPro
     *
     * @return void
     */
    public static function scripts()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-progressbar');
        wp_enqueue_script('dup-parsley');
        wp_enqueue_script('dup-jquery-qtip');
    }

    /**
     * Loads all CSS style libs/source for DupPro
     *
     * @return void
     */
    public static function styles()
    {
        wp_enqueue_style('dup-jquery-ui');
        wp_enqueue_style('dup-font-awesome');
        wp_enqueue_style('dup-plugin-style');
        wp_enqueue_style('dup-jquery-qtip');
    }

    /**
     * Enqueue mock related styles
     *
     * @return void
     */
    public static function mocksStyles()
    {
        wp_enqueue_style(
            'dup-mocks-styles',
            DUPLICATOR_PLUGIN_URL . 'assets/css/mocks.css',
            array(),
            DUPLICATOR_VERSION
        );
    }

    /**
     * Adds the manage link in the plugins list
     *
     * @param string[] $links links
     * @param string   $file  file
     *
     * @return string The manage link in the plugins list
     */
    public static function manageLink($links, $file)
    {
        static $this_plugin;
        if (!$this_plugin) {
            $this_plugin = plugin_basename(DUPLICATOR_LITE_FILE);
        }

        if ($file == $this_plugin) {
            /*
              $settings_link = '<a href="admin.php?page=duplicator">' . esc_html__("Manage", 'duplicator') . '</a>';
              array_unshift($links, $settings_link);
             */
            $upgrade_link = '<a style="color: #1da867;" class="dup-plugins-list-pro-upgrade" href="' .
                esc_url(Upsell::getCampaignUrl('plugin-actions-link')) . '" target="_blank">' .
                '<strong style="display: inline;">' .
                esc_html__("Upgrade to Pro", 'duplicator') .
                '</strong></a>';
            array_unshift($links, $upgrade_link);
        }
        return $links;
    }

    /**
     * Adds links to the plugins manager page
     *
     * @param string[] $links links
     * @param string   $file  file
     *
     * @return string The meta help link data for the plugins manager
     */
    public static function metaLinks($links, $file)
    {
        $plugin = plugin_basename(DUPLICATOR_LITE_FILE);
        // create link
        if ($file == $plugin) {
            $links[] = '<a href="admin.php?page=duplicator" title="' . esc_attr__('Manage Packages', 'duplicator') . '" style="">' .
                esc_html__('Manage', 'duplicator') .
                '</a>';
            return $links;
        }
        return $links;
    }

    /**
     * Remove all external styles and scripts coming from other plugins
     * which may cause compatibility issue, especially with React
     *
     * @param string $hook hook
     *
     * @return void
     */
    public static function unhookThirdPartyAssets($hook)
    {
        /*
          $hook values in duplicator admin pages:
          toplevel_page_duplicator
          duplicator_page_duplicator-tools
          duplicator_page_duplicator-settings
          duplicator_page_duplicator-gopro
         */
        if (strpos($hook, 'duplicator') !== false && strpos($hook, 'duplicator-pro') === false) {
            $unhook_third_party_js  = DUP_Settings::Get('unhook_third_party_js');
            $unhook_third_party_css = DUP_Settings::Get('unhook_third_party_css');
            $assets                 = array();
            if ($unhook_third_party_css) {
                $assets['styles'] = wp_styles();
            }
            if ($unhook_third_party_js) {
                $assets['scripts'] = wp_scripts();
            }
            foreach ($assets as $type => $asset) {
                foreach ($asset->registered as $handle => $dep) {
                    $src = $dep->src;
                    // test if the src is coming from /wp-admin/ or /wp-includes/ or /wp-fsqm-pro/.
                    if (
                        is_string($src) && // For some built-ins, $src is true|false
                        strpos($src, 'wp-admin') === false &&
                        strpos($src, 'wp-include') === false &&
                        // things below are specific to your plugin, so change them
                        strpos($src, 'duplicator') === false &&
                        strpos($src, 'woocommerce') === false &&
                        strpos($src, 'jetpack') === false &&
                        strpos($src, 'debug-bar') === false
                    ) {
                        'scripts' === $type ? wp_dequeue_script($handle) : wp_dequeue_style($handle);
                    }
                }
            }
        }
    }
}
