<?php

namespace Duplicator\Core\Notifications;

use Duplicator\Ajax\ServicesNotifications;
use Duplicator\Core\Views\TplMng;

/**
 * Notifications.
 */
class Notifications
{
    /**
     * Source of notifications content.
     *
     * @var string
     */
    const SOURCE_URL = 'https://notifications.duplicator.com/dp-notifications.json';

    /**
     * WordPress option key containing notification data
     *
     * @var string
     */
    const DUPLICATOR_NOTIFICATIONS_OPT_KEY = 'duplicator_notifications';

    /**
     * WordPress option key containing notification data
     *
     * @var string
     */
    const DUPLICATOR_BEFORE_PACKAGES_HOOK = 'duplicator_before_packages_table_action';

    /**
     * Duplicator notifications dismiss nonce key
     *
     * @var string
     */
    const DUPLICATOR_NOTIFICATION_NONCE_KEY = 'duplicator-notification-dismiss';

    /**
     * Option value.
     *
     * @var bool|array
     */
    public static $option = false;

    /**
     * Initialize class.
     *
     * @return void
     */
    public static function init()
    {
        self::hooks();
        self::update();
        $notificationsService = new ServicesNotifications();
        $notificationsService->init();
    }

    /**
     * Register hooks.
     *
     * @return void
     */
    public static function hooks()
    {
        add_action(self::DUPLICATOR_BEFORE_PACKAGES_HOOK, array(__CLASS__, 'output'));
        add_action('deactivate_plugin', array(__CLASS__, 'delete'), 10, 2);
    }

    /**
     * Check if user has access and is enabled.
     *
     * @return bool
     */
    public static function hasAccess()
    {
        return current_user_can('manage_options');
    }

    /**
     * Get option value.
     *
     * @param bool $cache Reference property cache if available.
     *
     * @return array
     */
    public static function getOption($cache = true)
    {
        if (self::$option && $cache) {
            return self::$option;
        }

        $option = get_option(self::DUPLICATOR_NOTIFICATIONS_OPT_KEY, array());

        self::$option = array(
            'update'    => !empty($option['update']) ? (int)$option['update'] : 0,
            'feed'      => !empty($option['feed']) ? (array)$option['feed'] : array(),
            'events'    => !empty($option['events']) ? (array)$option['events'] : array(),
            'dismissed' => !empty($option['dismissed']) ? (array)$option['dismissed'] : array()
        );

        return self::$option;
    }

    /**
     * Fetch notifications from feed.
     *
     * @return array
     */
    public static function fetchFeed()
    {
        $response = wp_remote_get(
            self::SOURCE_URL,
            array(
                'timeout'    => 10,
                'user-agent' => self::getUserAgent(),
            )
        );

        if (is_wp_error($response)) {
            return array();
        }

        $body = wp_remote_retrieve_body($response);

        if (empty($body)) {
            return array();
        }

        return self::verify(json_decode($body, true));
    }

    /**
     * Verify notification data before it is saved.
     *
     * @param array $notifications Array of notifications items to verify.
     *
     * @return array
     */
    public static function verify($notifications)
    {
        $data = array();
        if (!is_array($notifications) || empty($notifications)) {
            return $data;
        }

        foreach ($notifications as $notification) {
            // Ignore if one of the conditional checks is true:
            //
            // 1. notification message is empty.
            // 2. license type does not match.
            // 3. notification is expired.
            // 4. notification has already been dismissed.
            // 5. notification existed before installing Duplicator.
            // (Prevents bombarding the user with notifications after activation).
            if (
                empty($notification['content']) ||
                !self::isLicenseTypeMatch($notification) ||
                self::isExpired($notification) ||
                self::isDismissed($notification) ||
                self::isExisted($notification)
            ) {
                continue;
            }

            $data[] = $notification;
        }

        return $data;
    }

    /**
     * Verify saved notification data for active notifications.
     *
     * @param array $notifications Array of notifications items to verify.
     *
     * @return array
     */
    public static function verifyActive($notifications)
    {
        if (!is_array($notifications) || empty($notifications)) {
            return array();
        }

        $current_timestamp = time();

        // Remove notifications that are not active.
        foreach ($notifications as $key => $notification) {
            if (
                (!empty($notification['start']) && $current_timestamp < strtotime($notification['start'])) ||
                (!empty($notification['end']) && $current_timestamp > strtotime($notification['end']))
            ) {
                unset($notifications[$key]);
            }
        }

        return $notifications;
    }

    /**
     * Get notification data.
     *
     * @return array
     */
    public static function get()
    {
        if (!self::hasAccess()) {
            return array();
        }

        $option = self::getOption();

        $feed   = !empty($option['feed']) ? self::verifyActive($option['feed']) : array();
        $events = !empty($option['events']) ? self::verifyActive($option['events']) : array();

        return array_merge($feed, $events);
    }

    /**
     * Get notification count.
     *
     * @return int
     */
    public static function getCount()
    {
        return count(self::get());
    }

    /**
     * Add a new Event Driven notification.
     *
     * @param array $notification Notification data.
     *
     * @return void
     */
    public static function add($notification)
    {
        if (!self::isValid($notification)) {
            return;
        }

        $option = self::getOption();

        // Notification ID already exists.
        if (!empty($option['events'][$notification['id']])) {
            return;
        }

        $notification = self::verify(array($notification));
        update_option(
            self::DUPLICATOR_NOTIFICATIONS_OPT_KEY,
            array(
                'update'    => $option['update'],
                'feed'      => $option['feed'],
                'events'    => array_merge($notification, $option['events']),
                'dismissed' => $option['dismissed'],
            )
        );
    }

    /**
     * Determine if notification data is valid.
     *
     * @param array $notification Notification data.
     *
     * @return bool
     */
    public static function isValid($notification)
    {
        if (empty($notification['id'])) {
            return false;
        }

        return count(self::verify(array($notification))) > 0;
    }

    /**
     * Determine if notification has already been dismissed.
     *
     * @param array $notification Notification data.
     *
     * @return bool
     */
    private static function isDismissed($notification)
    {
        $option = self::getOption();

        return !empty($option['dismissed']) && in_array($notification['id'], $option['dismissed']);
    }

    /**
     * Determine if license type is match.
     *
     * @param array $notification Notification data.
     *
     * @return bool
     */
    private static function isLicenseTypeMatch($notification)
    {
        // A specific license type is not required.
        $notification['type'] = (array)$notification['type'];
        if (empty($notification['type'])) {
            return false;
        }

        if (in_array('any', $notification['type'])) {
            return true;
        }

        return in_array(self::getLicenseType(), (array)$notification['type'], true);
    }

    /**
     * Determine if notification is expired.
     *
     * @param array $notification Notification data.
     *
     * @return bool
     */
    private static function isExpired($notification)
    {
        return !empty($notification['end']) && time() > strtotime($notification['end']);
    }

    /**
     * Determine if notification existed before installing Duplicator.
     *
     * @param array $notification Notification data.
     *
     * @return bool
     */
    private static function isExisted($notification)
    {
        $activated = get_option(\DUP_LITE_Plugin_Upgrade::DUP_ACTIVATED_OPT_KEY, false);

        return $activated['lite'] !== false &&
            !empty($notification['start']) &&
            $activated['lite'] > strtotime($notification['start']);
    }

    /**
     * Update notification data from feed.
     *
     * @return void
     */
    public static function update()
    {
        $option = self::getOption();

        //Only update twice daily
        if ($option['update'] !== 0 && time() < $option['update'] + DAY_IN_SECONDS / 2) {
            return;
        }

        $data = array(
            'update'    => time(),
            'feed'      => self::fetchFeed(),
            'events'    => $option['events'],
            'dismissed' => $option['dismissed'],
        );

        /**
         * Allow changing notification data before it will be updated in database.
         *
         * @param array $data New notification data.
         */
        $data = (array)apply_filters('duplicator_admin_notifications_update_data', $data);

        // Flush the cache after the option has been updated
        // for the case when it earlier returns an old value without the new data from DB.
        if (update_option(self::DUPLICATOR_NOTIFICATIONS_OPT_KEY, $data)) {
            wp_cache_flush();
        }
    }

    /**
     * Remove notification data from database before a plugin is deactivated.
     *
     * @param string $plugin Path to the plugin file relative to the plugins directory.
     *
     * @return void
     */
    public static function delete($plugin)
    {
        $duplicator_plugins = array(
            'duplicator-lite/duplicator.php',
            'duplicator/duplicator.php',
        );

        if (!in_array($plugin, $duplicator_plugins, true)) {
            return;
        }

        delete_option(self::DUPLICATOR_NOTIFICATIONS_OPT_KEY);
    }

    /**
     * Enqueue assets on Form Overview admin page.
     *
     * @return void
     */
    public static function enqueues()
    {
        if (!self::getCount()) {
            return;
        }

        wp_enqueue_style(
            'dup-admin-notifications',
            DUPLICATOR_PLUGIN_URL . "assets/css/admin-notifications.css",
            array('dup-lity'),
            DUPLICATOR_VERSION
        );

        wp_enqueue_script(
            'dup-admin-notifications',
            DUPLICATOR_PLUGIN_URL . "assets/js/notifications/admin-notifications.js",
            array('jquery', 'dup-lity'),
            DUPLICATOR_VERSION,
            true
        );

        wp_localize_script(
            'dup-admin-notifications',
            'dup_admin_notifications',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce(self::DUPLICATOR_NOTIFICATION_NONCE_KEY),
            )
        );

        // Lity.
        wp_enqueue_style(
            'dup-lity',
            DUPLICATOR_PLUGIN_URL . 'assets/lib/lity/lity.min.css',
            array(),
            DUPLICATOR_VERSION
        );

        wp_enqueue_script(
            'dup-lity',
            DUPLICATOR_PLUGIN_URL . 'assets/lib/lity/lity.min.js',
            array('jquery'),
            DUPLICATOR_VERSION,
            true
        );
    }

    /**
     * Output notifications on Form Overview admin area.
     *
     * @return void
     */
    public static function output()
    {
        $notificationsData = self::get();

        if (empty($notificationsData)) {
            return;
        }


        $content_allowed_tags = array(
            'br'     => array(),
            'em'     => array(),
            'strong' => array(),
            'span'   => array(
                'style' => array()
            ),
            'p'      => array(
                'id'    => array(),
                'class' => array()
            ),
            'a'      => array(
                'href'   => array(),
                'target' => array(),
                'rel'    => array()
            )
        );

        $notifications = array();
        foreach ($notificationsData as $notificationData) {
            // Prepare required arguments.
            $notificationData = wp_parse_args(
                $notificationData,
                array(
                    'id'      => 0,
                    'title'   => '',
                    'content' => '',
                    'video'   => ''
                )
            );

            $title   = self::getComponentData($notificationData['title']);
            $content = self::getComponentData($notificationData['content']);

            if (!$title && !$content) {
                continue;
            }

            $notifications[] = array(
                'id'        => $notificationData['id'],
                'title'     => $title,
                'btns'      => self::getButtonsData($notificationData),
                'content'   => wp_kses(wpautop($content), $content_allowed_tags),
                'video_url' => wp_http_validate_url(self::getComponentData($notificationData['video'])),
            );
        }

        self::enqueues();
        TplMng::getInstance()->render(
            'parts/Notifications/main',
            array(
                'notifications' => $notifications
            )
        );
    }

    /**
     * Retrieve notification's buttons.
     *
     * @param array $notification Notification data.
     *
     * @return array
     */
    private static function getButtonsData($notification)
    {
        if (empty($notification['btn']) || !is_array($notification['btn'])) {
            return array();
        }

        $buttons = array();
        if (!empty($notification['btn']['main_text']) && !empty($notification['btn']['main_url'])) {
            $buttons[] = array(
                'type'   => 'primary',
                'text'   => $notification['btn']['main_text'],
                'url'    => self::prepareBtnUrl($notification['btn']['main_url']),
                'target' => '_blank'
            );
        }

        if (!empty($notification['btn']['alt_text']) && !empty($notification['btn']['alt_url'])) {
            $buttons[] = array(
                'type'   => 'secondary',
                'text'   => $notification['btn']['alt_text'],
                'url'    => self::prepareBtnUrl($notification['btn']['alt_url']),
                'target' => '_blank'
            );
        }

        return $buttons;
    }

    /**
     * Retrieve notification's component data by a license type.
     *
     * @param mixed $data Component data.
     *
     * @return false|mixed
     */
    private static function getComponentData($data)
    {
        if (empty($data['license'])) {
            return $data;
        }

        $license_type = self::getLicenseType();
        return !empty($data['license'][$license_type]) ? $data['license'][$license_type] : false;
    }

    /**
     * Retrieve the current installation license type (always lowercase).
     *
     * @return string
     */
    private static function getLicenseType()
    {
        return 'lite';
    }

    /**
     * Prepare button URL.
     *
     * @param string $btnUrl Button url.
     *
     * @return string
     */
    private static function prepareBtnUrl($btnUrl)
    {
        if (empty($btnUrl)) {
            return '';
        }

        $replace_tags = array(
            '{admin_url}' => admin_url()
        );

        return wp_http_validate_url(str_replace(array_keys($replace_tags), array_values($replace_tags), $btnUrl));
    }

    /**
     * User agent that will be used for the request
     *
     * @return string
     */
    private static function getUserAgent()
    {
        return 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo('url') . '; Duplicator/Lite-' . DUPLICATOR_VERSION;
    }
}
