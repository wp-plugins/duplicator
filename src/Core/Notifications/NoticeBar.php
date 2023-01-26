<?php

namespace Duplicator\Core\Notifications;

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapWP;

/**
 * Admin/NoticeBar Education feature for Lite.
 *
 * @since 1.6.6
 */
class NoticeBar
{
    /**
     * Constant for the wp-options key handling the dismissed state
     */
    const NOTICE_BAR_DISMISSED_OPT_KEY = 'duplicator_notice_bar_dismissed';

    /**
     * Init.
     *
     * @return void
     */
    public static function init()
    {
        add_action('in_admin_header', array(__CLASS__, 'display'));
        add_action('wp_ajax_duplicator_notice_bar_dismiss', array(__CLASS__, 'dismissNoticeBar'));
    }

    /**
     * Notice bar display message.
     *
     * @return void
     */
    public static function display()
    {
        if (!ControllersManager::isDuplicatorPage()) {
            return;
        }

        //make sure it wasn't dismissed
        if (get_user_meta(get_current_user_id(), self::NOTICE_BAR_DISMISSED_OPT_KEY, true) != false) {
            return;
        }

        $umt_content = '';
        foreach (ControllersManager::getMenuLevels() as $key => $value) {
            if (strlen($value) > 0) {
                $umt_content .= ucfirst($key) . ' ' . $value . ' ';
            }
        }
        $umt_content = trim($umt_content);
        require DUPLICATOR_LITE_PATH . '/views/parts/notice-bar.php';
    }

    /**
     * Dismiss notice bar ajax action
     *
     * @return void
     */
    public static function dismissNoticeBar()
    {
        // Run a security check.
        check_ajax_referer('duplicator-notice-bar-dismiss', 'nonce');
        update_user_meta(get_current_user_id(), self::NOTICE_BAR_DISMISSED_OPT_KEY, true);
    }

    /**
     * Delete related option
     *
     * @return bool true on success, false on failure
     */
    public static function deleteOption()
    {
        return SnapWP::deleteUserMetaKey(self::NOTICE_BAR_DISMISSED_OPT_KEY);
    }
}
