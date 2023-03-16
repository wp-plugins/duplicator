<?php

/**
 * wpengine custom hosting class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\DB
 * @link    http://www.php-fig.org/psr/psr-2/
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

/**
 * class for wpengine managed hosting
 *
 * @todo not yet implemneted
 */
class DUPX_WPEngine_Host implements DUPX_Host_interface
{
    /**
     * return the current host itentifier
     *
     * @return string
     */
    public static function getIdentifier()
    {
        return DUPX_Custom_Host_Manager::HOST_WPENGINE;
    }

    /**
     * @return bool true if is current host
     */
    public function isHosting()
    {
        // check only mu plugin file exists

        $file = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW) . '/wpengine-security-auditor.php';
        return file_exists($file);
    }

    /**
     * the init function.
     * is called only if isHosting is true
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     *
     * @return string
     */
    public function getLabel()
    {
        return 'WP Engine';
    }

    /**
     * this function is called if current hosting is this
     */
    public function setCustomParams()
    {
        PrmMng::getInstance()->setValue(PrmMng::PARAM_IGNORE_PLUGINS, array(
            'mu-plugin.php',
            'advanced-cache.php',
            'wpengine-security-auditor.php',
            'stop-long-comments.php',
            'slt-force-strong-passwords.php'
        ));

        $this->force_disable_plugins();
    }

    /**
     * force disable disallowed plugins
     *
     * @link https://wpengine.com/support/disallowed-plugins/
     *
     * @return void
     */
    private function force_disable_plugins()
    {
        $fdPlugins = PrmMng::getInstance()->getValue(PrmMng::PARAM_FORCE_DIABLE_PLUGINS);

        if (!is_array($fdPlugins)) {
            $fdPlugins = array();
        }

        $fdPlugins = array_merge($fdPlugins, array(
            'gd-system-plugin.php',
            'hcs.php',
            'hello.php',
            'adminer/adminer.php',
            'async-google-analytics/asyncgoogleanalytics.php',
            'backup/backup.php',
            'backup-scheduler/backup-scheduler.php',
            'backupwordpress/backupwordpress.php',
            'backwpup/backwpup.php',
            'bad-behavior/bad-behavior-wordpress.php',
            'broken-link-checker/broken-link-checker.php',
            'content-molecules/emc2_content_molecules.php',
            'contextual-related-posts/contextual-related-posts.php',
            'dynamic-related-posts/drpp.php',
            'ewww-image-optimizer/ewww-image-optimizer.php',
            'ezpz-one-click-backup/ezpz-ocb.php',
            'file-commander/wp-plugin-file-commander.php',
            'fuzzy-seo-booster/seoqueries.php',
            'google-xml-sitemaps-with-multisite-support/sitemap.php',
            'hc-custom-wp-admin-url/hc-custom-wp-admin-url.php',
            'jr-referrer/jr-referrer.php',
            'jumpple/sweetcaptcha.php',
            'missed-schedule/missed-schedule.php',
            'no-revisions/norevisions.php',
            'ozh-who-sees-ads/wp_ozh_whoseesads.php',
            'pipdig-power-pack/p3.php',
            'portable-phpmyadmin/wp-phpmyadmin.php',
            'quick-cache/quick-cache.php',
            'quick-cache-pro/quick-cache-pro.php',
            'recommend-a-friend/recommend-to-a-friend.php',
            'seo-alrp/seo-alrp.php',
            'si-captcha-for-wordpress/si-captcha.php',
            'similar-posts/similar-posts.php',
            'spamreferrerblock/spam_referrer_block.php',
            'super-post/super-post.php',
            'superslider/superslider.php',
            'sweetcaptcha-revolutionary-free-captcha-service/sweetcaptcha.php',
            'the-codetree-backup/codetree-backup.php',
            'ToolsPack/ToolsPack.php',
            'tweet-blender/tweet-blender.php',
            'versionpress/versionpress.php',
            'w3-total-cache/w3-total-cache.php',
            'wordpress-gzip-compression/ezgz.php',
            'wp-cache/wp-cache.php',
            'wp-database-optimizer/wp_database_optimizer.php',
            'wp-db-backup/wp-db-backup.php',
            'wp-dbmanager/wp-dbmanager.php',
            'wp-engine-snapshot/plugin.php',
            'wp-file-cache/file-cache.php',
            'wp-mailinglist/wp-mailinglist.php',
            'wp-phpmyadmin/wp-phpmyadmin.php',
            'wp-postviews/wp-postviews.php',
            'wp-slimstat/wp-slimstat.php',
            'wp-super-cache/wp-cache.php',
            'wp-symposium-alerts/wp-symposium-alerts.php',
            'wponlinebackup/wponlinebackup.php',
            'yet-another-featured-posts-plugin/yafpp.php',
            'yet-another-related-posts-plugin/yarpp.php'
        ));

        PrmMng::getInstance()->setValue(PrmMng::PARAM_FORCE_DIABLE_PLUGINS, $fdPlugins);
    }
}
