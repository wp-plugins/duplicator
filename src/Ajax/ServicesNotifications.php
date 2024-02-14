<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Core\Notifications\Notifications;

class ServicesNotifications extends AbstractAjaxService
{
    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall('wp_ajax_duplicator_notification_dismiss', 'setDissmisedNotifications');
    }

    /**
     * Dismiss notification
     *
     * @return bool
     */
    public static function dismissNotifications()
    {
        $id     = sanitize_key($_POST['id']);
        $type   = is_numeric($id) ? 'feed' : 'events';
        $option = Notifications::getOption();

        $option['dismissed'][] = $id;
        $option['dismissed']   = array_unique($option['dismissed']);

        // Remove notification.
        if (!is_array($option[$type]) || empty($option[$type])) {
            throw new \Exception('Notification type not set.');
        }

        foreach ($option[$type] as $key => $notification) {
            if ((string)$notification['id'] === (string)$id) {
                unset($option[$type][$key]);

                break;
            }
        }

        return update_option(Notifications::DUPLICATOR_NOTIFICATIONS_OPT_KEY, $option);
    }

    /**
     * Set dismiss notification action
     *
     * @return void
     */
    public function setDissmisedNotifications()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'dismissNotifications'),
            Notifications::DUPLICATOR_NOTIFICATION_NONCE_KEY,
            $_POST['nonce'],
            'manage_options'
        );
    }
}
