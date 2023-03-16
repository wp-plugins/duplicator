<?php

/**
 * Singlethon class that manages the various controllers of the administration of wordpress
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Core\Controllers;

use Duplicator\Libs\Snap\SnapUtil;

final class ControllersManager
{
    const MAIN_MENU_SLUG         = 'duplicator';
    const PACKAGES_SUBMENU_SLUG  = 'duplicator';
    const IMPORT_SUBMENU_SLUG    = 'duplicator-import';
    const SCHEDULES_SUBMENU_SLUG = 'duplicator-schedules';
    const STORAGE_SUBMENU_SLUG   = 'duplicator-storage';
    const ABOUT_US_SUBMENU_SLUG  = 'duplicator-about-us';
    const TEMPLATES_SUBMENU_SLUG = 'duplicator-templates';
    const TOOLS_SUBMENU_SLUG     = 'duplicator-tools';
    const SETTINGS_SUBMENU_SLUG  = 'duplicator-settings';
    const DEBUG_SUBMENU_SLUG     = 'duplicator-debug';
    const UPSELL_SUBMENU_SLUG    = 'duplicator-pro';

    const QUERY_STRING_MENU_KEY_L1     = 'page';
    const QUERY_STRING_MENU_KEY_L2     = 'tab';
    const QUERY_STRING_MENU_KEY_L3     = 'subtab';
    const QUERY_STRING_MENU_KEY_ACTION = 'action';

    /**
     * Return current menu levels
     *
     * @return string[]
     */
    public static function getMenuLevels()
    {
        $result  = array();
        $exChars = '-_';
        $result[self::QUERY_STRING_MENU_KEY_L1] = SnapUtil::sanitizeStrictInput(
            SnapUtil::INPUT_REQUEST,
            self::QUERY_STRING_MENU_KEY_L1,
            null,
            $exChars
        );
        $result[self::QUERY_STRING_MENU_KEY_L2] = SnapUtil::sanitizeStrictInput(
            SnapUtil::INPUT_REQUEST,
            self::QUERY_STRING_MENU_KEY_L2,
            null,
            $exChars
        );
        $result[self::QUERY_STRING_MENU_KEY_L3] = SnapUtil::sanitizeStrictInput(
            SnapUtil::INPUT_REQUEST,
            self::QUERY_STRING_MENU_KEY_L3,
            null,
            $exChars
        );
        return $result;
    }

    /**
     * Return true if current page is a duplicator page
     *
     * @return boolean
     */
    public static function isDuplicatorPage()
    {
        if (!is_admin()) {
            return false;
        }

        switch (SnapUtil::sanitizeStrictInput(SnapUtil::INPUT_REQUEST, 'page', '', '-_ ')) {
            case self::MAIN_MENU_SLUG:
            case self::PACKAGES_SUBMENU_SLUG:
            case self::IMPORT_SUBMENU_SLUG:
            case self::SCHEDULES_SUBMENU_SLUG:
            case self::STORAGE_SUBMENU_SLUG:
            case self::ABOUT_US_SUBMENU_SLUG:
            case self::TEMPLATES_SUBMENU_SLUG:
            case self::TOOLS_SUBMENU_SLUG:
            case self::SETTINGS_SUBMENU_SLUG:
            case self::DEBUG_SUBMENU_SLUG:
            case self::UPSELL_SUBMENU_SLUG:
                return true;
            default:
                return false;
        }
    }

    /**
     * Return current action key or false if not exists
     *
     * @return string|bool
     */
    public static function getAction()
    {
        return SnapUtil::sanitizeStrictInput(
            SnapUtil::INPUT_REQUEST,
            self::QUERY_STRING_MENU_KEY_ACTION,
            false,
            '-_'
        );
    }

    /**
     * Check current page
     *
     * @param string      $page  page key
     * @param null|string $tabL1 tab level 1 key, null not check
     * @param null|string $tabL2 tab level 12key, null not check
     *
     * @return boolean
     */
    public static function isCurrentPage($page, $tabL1 = null, $tabL2 = null)
    {
        $levels = self::getMenuLevels();

        if ($page !== $levels[self::QUERY_STRING_MENU_KEY_L1]) {
            return false;
        }

        if (!is_null($tabL1) && $tabL1 !== $levels[self::QUERY_STRING_MENU_KEY_L2]) {
            return false;
        }

        if (!is_null($tabL1) && !is_null($tabL2) && $tabL2 !== $levels[self::QUERY_STRING_MENU_KEY_L3]) {
            return false;
        }

        return true;
    }

    /**
     * Return current menu page URL
     *
     * @param array $extraData extra value in query string key=val
     *
     * @return string
     */
    public static function getCurrentLink($extraData = array())
    {
        $levels = self::getMenuLevels();
        return self::getMenuLink(
            $levels[self::QUERY_STRING_MENU_KEY_L1],
            $levels[self::QUERY_STRING_MENU_KEY_L2],
            $levels[self::QUERY_STRING_MENU_KEY_L3],
            $extraData
        );
    }

    /**
     * Return menu page URL
     *
     * @param string $page      page slug
     * @param string $subL2     tab level 1 slug, null not set
     * @param string $subL3     tab level 2 slug, null not set
     * @param array  $extraData extra value in query string key=val
     * @param bool   $relative  if true return relative path or absolute
     *
     * @return string
     */
    public static function getMenuLink($page, $subL2 = null, $subL3 = null, $extraData = array(), $relative = true)
    {
        $data = $extraData;

        $data[self::QUERY_STRING_MENU_KEY_L1] = $page;

        if (!empty($subL2)) {
            $data[self::QUERY_STRING_MENU_KEY_L2] = $subL2;
        }

        if (!empty($subL3)) {
            $data[self::QUERY_STRING_MENU_KEY_L3] = $subL3;
        }

        if ($relative) {
            $url = self_admin_url('admin.php', 'relative');
        } else {
            if (is_multisite()) {
                $url = network_admin_url('admin.php');
            } else {
                $url = admin_url('admin.php');
            }
        }
        return $url . '?' . http_build_query($data);
    }

    /**
     * Return create package link
     *
     * @return string
     */
    public static function getPackageBuildUrl()
    {
        return self::getMenuLink(
            self::PACKAGES_SUBMENU_SLUG,
            'new1',
            null,
            array(
                'inner_page' => 'new1',
                '_wpnonce' => wp_create_nonce('new1-package')
            )
        );
    }

    /**
     * Return package detail link
     *
     * @param int $packageId package id
     *
     * @return string
     */
    public static function getPackageDetailUrl($packageId)
    {
        return self::getMenuLink(
            self::PACKAGES_SUBMENU_SLUG,
            'detail',
            null,
            array(
                'action' => 'detail',
                'id'     => $packageId
            )
        );
    }
}
