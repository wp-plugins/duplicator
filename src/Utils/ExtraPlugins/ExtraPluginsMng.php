<?php

namespace Duplicator\Utils\ExtraPlugins;

use Exception;

final class ExtraPluginsMng
{
    /** @var ?self */
    private static $instance = null;

    /** @var array<string, ExtraItem> key slug item */
    protected $plugins = array();

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

    /**
     * Singleton constructor
     */
    protected function __construct()
    {
        $this->plugins = self::getInitList();
    }

    /**
     * Execute callback for each plugin
     *
     * @param callable $callback callback function
     *
     * @return void
     */
    public function foreachCallback($callback)
    {
        if (!is_callable($callback)) {
            return;
        }

        foreach ($this->plugins as $plugin) {
            call_user_func($callback, $plugin);
        }
    }

    /**
     * Returns plugin by slug
     *
     * @param string $slug plugin slug
     *
     * @return false|ExtraItem plugin item or false if not found
     */
    protected function getBySlug($slug)
    {
        if (strlen($slug) === 0) {
            return false;
        }

        if (!isset($this->plugins[$slug])) {
            return false;
        }

        return (isset($this->plugins[$slug]) ? $this->plugins[$slug] : false);
    }

    /**
     * Install plugin slug
     *
     * @param string $slug    plugin slug
     * @param string $message message
     *
     * @return bool true if plugin installed and activated or false on failure
     */
    public function install($slug, &$message = '')
    {
        if (strlen($slug) === 0) {
            $message = __('Plugin slug is empty', 'duplicator');
            return false;
        }

        if (($plugin = $this->getBySlug($slug)) == false) {
            $message = __('Plugin not found', 'duplicator');
            return false;
        }

        $result = true;
        ob_start();
        if ($plugin->install() == false) {
            $result = false;
        } elseif ($plugin->activate() == false) {
            $result = false;
        }
        $message = ob_get_clean();
        return $result;
    }

    /**
     * Init addon plugins
     *
     * @return void
     */
    private static function getInitList()
    {
        $result = array();

        $item = new ExtraItem(
            __('OptinMonster', 'duplicator'),
            'optinmonster/optin-monster-wp-api.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-om.png',
            __('Instantly get more subscribers, leads, and sales with the #1 conversion optimization toolkit. Create ' .
                'high converting popups, announcement bars, spin a wheel, and more with smart targeting and personalization.', 'duplicator'),
            'https://downloads.wordpress.org/plugin/optinmonster.zip',
            'https://wordpress.org/plugins/optinmonster/'
        );

        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('MonsterInsights', 'duplicator'),
            'google-analytics-for-wordpress/googleanalytics.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-mi.png',
            __(
                'The leading WordPress analytics plugin that shows you how people find and use your website, so you can ' .
                'make data driven decisions to grow your business. Properly set up Google Analytics without writing code.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
            'https://wordpress.org/plugins/google-analytics-for-wordpress/'
        );
        $item->setPro(
            __('MonsterInsights Pro', 'duplicator'),
            'google-analytics-premium/googleanalytics-premium.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-mi.png',
            __(
                'The leading WordPress analytics plugin that shows you how people find and use your website, so you ' .
                'can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.',
                'duplicator'
            ),
            'https://www.monsterinsights.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('WPForms', 'duplicator'),
            'wpforms-lite/wpforms.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-wpforms.png',
            __(
                'The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment ' .
                'forms, and more with our 100+ form templates. Trusted by over 4 million websites as the best forms plugin.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/wpforms-lite.zip',
            'https://wordpress.org/plugins/wpforms-lite/'
        );
        $item->setPro(
            __('WPForms Pro', 'duplicator'),
            'wpforms/wpforms.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-wpforms.png',
            __(
                'The easiest drag & drop WordPress form builder plugin to create beautiful contact forms, subscription ' .
                'forms, payment forms, and more in minutes. No coding skills required.',
                'duplicator'
            ),
            'https://wpforms.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('WP Mail SMTP', 'duplicator'),
            'wp-mail-smtp/wp_mail_smtp.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-smtp.png',
            __(
                'Improve your WordPress email deliverability and make sure that your website emails reach user\'s inbox ' .
                'with the #1 SMTP plugin for WordPress. Over 3 million websites use it to fix WordPress email issues.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
            'https://wordpress.org/plugins/wp-mail-smtp/'
        );
        $item->setPro(
            __('WP Mail SMTP Pro', 'duplicator'),
            'wp-mail-smtp-pro/wp_mail_smtp.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-smtp.png',
            __(
                'Improve your WordPress email deliverability and make sure that your website emails reach user\'s inbox ' .
                'with the #1 SMTP plugin for WordPress. Over 3 million websites use it to fix WordPress email issues.',
                'duplicator'
            ),
            'https://wpmailsmtp.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('AIOSEO', 'duplicator'),
            'all-in-one-seo-pack/all_in_one_seo_pack.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-aioseo.png',
            __(
                'The original WordPress SEO plugin and toolkit that improves your website\'s search rankings. Comes with ' .
                'all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
            'https://wordpress.org/plugins/all-in-one-seo-pack/'
        );
        $item->setPro(
            __('AIOSEO Pro', 'duplicator'),
            'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-aioseo.png',
            __(
                'The original WordPress SEO plugin and toolkit that improves your website\'s search rankings. Comes ' .
                'with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.',
                'duplicator'
            ),
            'https://aioseo.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item =  new ExtraItem(
            __('SeedProd', 'duplicator'),
            'coming-soon/coming-soon.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-seedprod.png',
            __('The best WordPress coming soon page plugin to create a beautiful coming soon page, maintenance mode page, ' .
                'or landing page. No coding skills required.', 'duplicator'),
            'https://downloads.wordpress.org/plugin/coming-soon.zip',
            'https://wordpress.org/plugins/coming-soon/'
        );
        $item->setPro(
            __('SeedProd Pro', 'duplicator'),
            'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-seedprod.png',
            __('The best WordPress coming soon page plugin to create a beautiful coming soon page, maintenance mode ' .
                'page, or landing page. No coding skills required.', 'duplicator'),
            'https://www.seedprod.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('RafflePress', 'duplicator'),
            'rafflepress/rafflepress.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-rp.png',
            __(
                'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social ' .
                'media followers with the most powerful giveaways & contests plugin for WordPress.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/rafflepress.zip',
            'https://wordpress.org/plugins/rafflepress/'
        );
        $item->setPro(
            __('RafflePress Pro', 'duplicator'),
            'rafflepress-pro/rafflepress-pro.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-rp.png',
            __(
                'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and ' .
                'social media followers with the most powerful giveaways & contests plugin for WordPress.',
                'duplicator'
            ),
            'https://rafflepress.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('PushEngage', 'duplicator'),
            'pushengage/main.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-pushengage.png',
            __(
                'Connect with your visitors after they leave your website with the leading web push notification software. ' .
                'Over 10,000+ businesses worldwide use PushEngage to send 9 billion notifications each month.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/pushengage.zip',
            'https://wordpress.org/plugins/pushengage/'
        );

        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('Smash Balloon Instagram Feeds', 'duplicator'),
            'instagram-feed/instagram-feed.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sb-instagram.png',
            __(
                'Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ' .
                'ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/instagram-feed.zip',
            'https://wordpress.org/plugins/instagram-feed/'
        );
        $item->setPro(
            __('Smash Balloon Instagram Feeds Pro', 'duplicator'),
            'instagram-feed-pro/instagram-feed.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sb-instagram.png',
            __(
                'Easily display Instagram content on your WordPress site without writing any code. Comes with multiple ' .
                'templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.',
                'duplicator'
            ),
            'https://smashballoon.com/instagram-feed/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('Smash Balloon Facebook Feeds', 'duplicator'),
            'custom-facebook-feed/custom-facebook-feed.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sb-fb.png',
            __(
                'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ' .
                'ability to embed albums, group content, reviews, live videos, comments, and reactions.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
            'https://wordpress.org/plugins/custom-facebook-feed/'
        );
        $item->setPro(
            __('Smash Balloon Facebook Feeds Pro', 'duplicator'),
            'custom-facebook-feed-pro/custom-facebook-feed.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sb-fb.png',
            __(
                'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ' .
                'ability to embed albums, group content, reviews, live videos, comments, and reactions.',
                'duplicator'
            ),
            'https://smashballoon.com/custom-facebook-feed/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('Smash Balloon Twitter Feeds', 'duplicator'),
            'custom-twitter-feeds/custom-twitter-feed.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sb-twitter.png',
            __(
                'Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability ' .
                'to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/custom-twitter-feeds.zip',
            'https://wordpress.org/plugins/custom-twitter-feeds/'
        );
        $item->setPro(
            __('Smash Balloon Twitter Feeds Pro', 'duplicator'),
            'custom-twitter-feeds-pro/custom-twitter-feeds.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sb-twitter.png',
            __(
                'Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ' .
                'ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.',
                'duplicator'
            ),
            'https://smashballoon.com/custom-twitter-feeds/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('Smash Balloon YouTube Feeds', 'duplicator'),
            'feeds-for-youtube/youtube-feed.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sb-youtube.png',
            __(
                'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ' .
                'ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
            'https://wordpress.org/plugins/feeds-for-youtube/'
        );
        $item->setPro(
            __('Smash Balloon YouTube Feeds Pro', 'duplicator'),
            'youtube-feed-pro/youtube-feed.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sb-youtube.png',
            __(
                'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple ' .
                'layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.',
                'duplicator'
            ),
            'https://smashballoon.com/youtube-feed/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item =  new ExtraItem(
            __('TrustPulse', 'duplicator'),
            'trustpulse-api/trustpulse.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-trustpulse.png',
            __(
                'Boost your sales and conversions by up to 15% with real-time social proof notifications. TrustPulse helps ' .
                'you show live user activity and purchases to help convince other users to purchase.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/trustpulse-api.zip',
            'https://wordpress.org/plugins/trustpulse-api/'
        );

        $result[$item->getSlug()] = $item;


        $item = new ExtraItem(
            __('SearchWP', 'duplicator'),
            'searchwp/index.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-searchwp.png',
            __(
                'The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, ' .
                'track search metrics, and everything you need to leverage search to grow your business.',
                'duplicator'
            ),
            'https://searchwp.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );

        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('AffiliateWP', 'duplicator'),
            'affiliate-wp/affiliate-wp.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-affwp.png',
            __(
                'The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce ' .
                'store or membership site within minutes and start growing your sales with the power of referral marketing.',
                'duplicator'
            ),
            'https://affiliatewp.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator',
            false
        );

        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('WP Simple Pay', 'duplicator'),
            'stripe/stripe-checkout.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-wp-simple-pay.png',
            __(
                'The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your ' .
                'WordPress site without setting up a shopping cart. No code required.',
                'duplicator'
            ),
            'https://downloads.wordpress.org/plugin/stripe.zip',
            'https://wordpress.org/plugins/stripe/'
        );
        $item->setPro(
            __('WP Simple Pay Pro', 'duplicator'),
            'wp-simple-pay-pro-3/simple-pay.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-wp-simple-pay.png',
            __(
                'The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your ' .
                'WordPress site without setting up a shopping cart. No code required.',
                'duplicator'
            ),
            'https://wpsimplepay.com/lite-upgrade/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('Easy Digital Downloads', 'duplicator'),
            'easy-digital-downloads/easy-digital-downloads.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-edd.png',
            __('The best WordPress eCommerce plugin for selling digital downloads. Start selling eBooks, software, music, ' .
                'digital art, and more within minutes. Accept payments, manage subscriptions, advanced access control, and more.', 'duplicator'),
            'https://downloads.wordpress.org/plugin/easy-digital-downloads.zip',
            'https://wordpress.org/plugins/easy-digital-downloads/'
        );

        $result[$item->getSlug()] = $item;

        $item = new ExtraItem(
            __('Sugar Calendar', 'duplicator'),
            'sugar-calendar-lite/sugar-calendar-lite.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sugarcalendar.png',
            __('A simple & powerful event calendar plugin for WordPress that comes with all the event management features ' .
                'including payments, scheduling, timezones, ticketing, recurring events, and more.', 'duplicator'),
            'https://downloads.wordpress.org/plugin/sugar-calendar-lite.zip',
            'https://wordpress.org/plugins/sugar-calendar-lite/'
        );
        $item->setPro(
            __('Sugar Calendar Pro', 'duplicator'),
            'sugar-calendar/sugar-calendar.php',
            DUPLICATOR_PLUGIN_URL . 'assets/img/about/plugin-sugarcalendar.png',
            __('A simple & powerful event calendar plugin for WordPress that comes with all the event management features ' .
                'including payments, scheduling, timezones, ticketing, recurring events, and more.', 'duplicator'),
            'https://sugarcalendar.com/?utm_source=duplicatorplugin&utm_medium=link&utm_campaign=About%20Duplicator'
        );
        $result[$item->getSlug()] = $item;

        return $result;
    }
}
