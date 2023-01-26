<?php

namespace Duplicator\Core\Notifications;

use Duplicator\Core\MigrationMng;

/**
 * Ask for some love.
 *
 * @since 1.3.2
 */
class Review
{
    /**
     * Constant for the review request admin notice slug
     */
    const REVIEW_REQUEST_NOTICE_SLUG = 'review_request';

    /**
     * Primary class constructor.
     *
     * @return void
     */
    public static function init()
    {

        // Admin notice requesting review.
        add_action('admin_init', array(__CLASS__, 'review_request'));

        // Admin footer text.
        add_filter('admin_footer_text', array(__CLASS__, 'admin_footer'), 1, 2);
    }

    /**
     * Add admin notices as needed for reviews.
     *
     * @return void
     */
    public static function review_request()
    {

        // Only consider showing the review request to admin users.
        if (!is_super_admin()) {
            return;
        }

        // Get dismissed notices.
        $notices = get_option(Notice::DISMISSED_NOTICES_OPTKEY, array());

        //has already been dismissed, don't show again
        if (isset($notices[self::REVIEW_REQUEST_NOTICE_SLUG])) {
            return;
        }

        self::review_lite();
    }

    /**
     * Maybe show Lite review request.
     *
     * @return void
     */
    public static function review_lite()
    {
        $display = false;

        // Fetch when plugin was initially installed.
        $activated = get_option(\DUP_LITE_Plugin_Upgrade::DUP_ACTIVATED_OPT_KEY, array());
        if (empty($activated['lite'])) {
            \DUP_LITE_Plugin_Upgrade::setActivatedTime();
        } else {
            $numberOfPackages = \DUP_Package::count_by_status(array(
                array('op' => '=' , 'status' => \DUP_PackageStatus::COMPLETE )
            ));

            // Display if plugin has been installed for at least 3 days and has a package installed
            if ((($activated['lite'] + (DAY_IN_SECONDS * 3)) < time() && $numberOfPackages > 0)) {
                $display = true;
            }
        }

        //Display if it's been 3 days after a successful migration
        $migrationTime = MigrationMng::getMigrationData('time');
        if (!$display && $migrationTime !== false && (($migrationTime + (DAY_IN_SECONDS * 3)) < time())) {
            $display = true;
        }

        if (!$display) {
            return;
        }

        Notice::addMultistep(
            array(
                array(
                    "message" => "<p>" . sprintf(__('Are you enjoying %s?', 'duplicator'), 'Duplicator') . "</p>",
                    "links"   => array(
                        array(
                            "text"   => __('Yes', 'duplicator'),
                            "switch" => 1
                        ),
                        array(
                            "text"   => __('Not really', 'duplicator'),
                            "switch" => 2
                        )
                    )
                ),
                array(
                    "message" => "<p>" . __('That’s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'duplicator') . "</p>" .
                        "<p>" . wp_kses(__('~ John Turner<br>President of Duplicator', 'duplicator'), array('br' => array())) . "</p>",
                    "links"   => array(
                        array(
                            "url"  => self::getReviewUrl(),
                            "text" => __('Ok, you deserve it', 'duplicator'),
                            "dismiss" => true
                        ),
                        array(
                            "text"    => __('Nope, maybe later', 'duplicator'),
                            "dismiss" => true
                        ),
                        array(
                            "text"    => __('I already did', 'duplicator'),
                            "dismiss" => true
                        )
                    )
                ),
                array(
                    "message" => "<p>" . __('We\'re sorry to hear you aren\'t enjoying Duplicator. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'duplicator') . "</p>",
                    "links"   => array(
                        array(
                            "url"  => self::getFeedbackUrl(),
                            "text" => __('Give Feedback', 'duplicator'),
                            "dismiss" => true
                        ),
                        array(
                            "text"    => __('No thanks', 'duplicator'),
                            "dismiss" => true
                        )
                    )
                )
            ),
            self::REVIEW_REQUEST_NOTICE_SLUG,
            Notice::NOTICE_TYPE_INFO,
            array(
                'dismiss' => Notice::DISMISS_GLOBAL,
                'autop'   => false,
                'class'   => 'dup-review-notice',
            )
        );
    }

    /**
     * @return string The review url on wordpress.org
     */
    public static function getReviewUrl()
    {
        return "https://wordpress.org/support/plugin/duplicator/reviews/?filter=5#new-post";
    }

    /**
     * @return string The snapcreek feedback url
     */
    public static function getFeedbackUrl()
    {
        return "https://snapcreek.com/plugin-feedback";
    }

    /**
     * When user is on a WPForms related admin page, display footer text
     * that graciously asks them to rate us.
     *
     * @param string $text Footer text.
     *
     * @return string
     */
    public static function admin_footer($text)
    {
        //Show only on duplicator pages
        if (
            ! is_admin() ||
            empty( $_REQUEST['page'] ) ||
            strpos( $_REQUEST['page'], 'duplicator' ) === false
        ) {
            return false;
        }

        $text = sprintf(
            wp_kses( /* translators: $1$s - WPForms plugin name; $2$s - WP.org review link; $3$s - WP.org review link. */
                __('Please rate <strong>Duplicator</strong> <a href="%1$s" target="_blank" rel="noopener noreferrer">&#9733;&#9733;&#9733;&#9733;&#9733;</a> on <a href="%1$s" target="_blank" rel="noopener">WordPress.org</a> to help us spread the word. Thank you from the Duplicator team!', 'duplicator'),
                array(
                    'a' => array(
                        'href'   => array(),
                        'target' => array(),
                        'rel'    => array(),
                    ),
                    'strong' => array()
                )
            ),
            self::getReviewUrl()
        );

        return $text;
    }

}
