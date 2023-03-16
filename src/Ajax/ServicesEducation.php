<?php

/**
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Ajax;

use DUP_Package;
use Duplicator\Ajax\AjaxWrapper;
use Duplicator\Views\DashboardWidget;
use Duplicator\Views\EducationElements;

class ServicesEducation extends AbstractAjaxService
{
    const REMOTE_SUBSCRIBE_URL = 'http://snapcreek.com/?lite_email_signup=1';

    /**
     * Init ajax calls
     *
     * @return void
     */
    public function init()
    {
        $this->addAjaxCall('wp_ajax_duplicator_settings_callout_cta_dismiss', 'dismissCalloutCTA');
        $this->addAjaxCall('wp_ajax_duplicator_packages_bottom_bar_dismiss', 'dismissBottomBar');
        $this->addAjaxCall('wp_ajax_duplicator_email_subscribe', 'setEmailSubscribed');
    }

    /**
     * Set email subscribed
     *
     * @return bool
     */
    public static function setEmailSubscribedCallback()
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE);
        if (is_null($email)) {
            throw new \Exception('Invalid email');
        }

        $response = wp_remote_post(self::REMOTE_SUBSCRIBE_URL, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'body'        => array('email' => $email)
        ));

        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            $error_msg = $response->get_error_code() . ': ' . $response->get_error_message();
            error_log($error_msg);
            throw new \Exception($error_msg);
        }

        return (update_user_meta(get_current_user_id(), EducationElements::DUP_EMAIL_SUBSCRIBED_OPT_KEY, true) !== false);
    }

    /**
     * Set recovery action
     *
     * @return void
     */
    public function setEmailSubscribed()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'setEmailSubscribedCallback'),
            'duplicator_email_subscribe',
            $_POST['nonce'],
            'export'
        );
    }

    /**
     * Set dismiss callout CTA callback
     *
     * @return bool
     */
    public static function dismissCalloutCTACallback()
    {
        return (update_user_meta(get_current_user_id(), EducationElements::DUP_SETTINGS_FOOTER_CALLOUT_DISMISSED, true) !== false);
    }

    /**
     * Dismiss callout CTA
     *
     * @return void
     */
    public function dismissCalloutCTA()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'dismissCalloutCTACallback'),
            'duplicator_settings_callout_cta_dismiss',
            $_POST['nonce'],
            'export'
        );
    }

    /**
     * Dismiss bottom bar callback
     *
     * @return bool
     */
    public static function dismissBottomBarCallback()
    {
        return (update_user_meta(get_current_user_id(), EducationElements::DUP_PACKAGES_BOTTOM_BAR_DISMISSED, true) !== false);
    }

    /**
     * Dismiss bottom bar
     *
     * @return void
     */
    public function dismissBottomBar()
    {
        AjaxWrapper::json(
            array(__CLASS__, 'dismissBottomBarCallback'),
            'duplicator_packages_bottom_bar_dismiss',
            $_POST['nonce'],
            'export'
        );
    }
}
