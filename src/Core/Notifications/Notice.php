<?php

namespace Duplicator\Core\Notifications;

class Notice
{
    /**
     * Not dismissible.
     *
     * Constant attended to use as the value of the $args['dismiss'] argument.
     * DISMISS_NONE means that the notice is not dismissible.
     */
    const DISMISS_NONE = 0;

    /**
     * Dismissible global.
     *
     * Constant attended to use as the value of the $args['dismiss'] argument.
     * DISMISS_GLOBAL means that the notice will have the dismiss button, and after clicking this button, the notice will be dismissed for all users.
     */
    const DISMISS_GLOBAL = 1;

    /**
     * Dismissible per user.
     *
     * Constant attended to use as the value of the $args['dismiss'] argument.
     * DISMISS_USER means that the notice will have the dismiss button, and after clicking this button, the notice will be dismissed only for the current user..
     */
    const DISMISS_USER = 2;

    /**
     * Constant for notice type info with gray left border
     */
    const NOTICE_TYPE_INFO = 'info';

    /**
     * Constant for notice type warning with yellow left border
     */
    const NOTICE_TYPE_WARNING = 'warning';

    /**
     * Constant for notice type warning with red left border
     */
    const NOTICE_TYPE_ERROR = 'error';

    /**
     * Constant for notice type warning with green left border
     */
    const NOTICE_TYPE_SUCCESS = 'success';

    /**
     * Constant for notice id default prefix
     */
    const DEFAULT_PREFIX = 'dup-notice-';

    /**
     * Constant for addition notice id prefix in case it's a global notice
     */
    const GLOBAL_PREFIX = 'global-';

    /**
     * The wp-options key in which the notices are stored
     */
    const DISMISSED_NOTICES_OPTKEY = 'duplicator_dismissed_admin_notices';

    /**
     * Notices.
     *
     * @var array
     */
    private static $notices = array();

    /**
     * Init.
     *
     * @return void
     */
    public static function init()
    {

        static::hooks();
    }

    /**
     * Hooks.
     *
     * @return void
     */
    public static function hooks()
    {

        add_action('admin_notices', array(__CLASS__, 'display'), PHP_INT_MAX);
        add_action('wp_ajax_dup_notice_dismiss', array(__CLASS__, 'dismiss_ajax'));
    }

    /**
     * Enqueue assets.
     *
     * @return void
     */
    private static function enqueues()
    {

        wp_enqueue_script(
            'dup-admin-notices',
            DUPLICATOR_PLUGIN_URL . "assets/js/notifications/notices.js",
            array('jquery'),
            DUPLICATOR_VERSION,
            true
        );

        wp_localize_script(
            'dup-admin-notices',
            'dup_admin_notices',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('duplicator-admin-notice'),
            )
        );
    }

    /**
     * Display the notices.
     *
     * @return void
     */
    public static function display()
    {

        $dismissed_notices = get_user_meta(get_current_user_id(), self::DISMISSED_NOTICES_OPTKEY, true);
        $dismissed_notices = is_array($dismissed_notices) ? $dismissed_notices : array();
        $dismissed_notices = array_merge($dismissed_notices, (array)get_option(self::DISMISSED_NOTICES_OPTKEY, array()));

        foreach (self::$notices as $slug => $notice) {
            if (isset($dismissed_notices[$slug])) {
                unset(self::$notices[$slug]);
            }
        }

        $output = implode('', self::$notices);

        echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

        // Enqueue script only when it's needed.
        if (strpos($output, 'is-dismissible') !== false) {
            self::enqueues();
        }
    }

    /**
     * Add notice to the registry.
     *
     * @param string $message Message to display.
     * @param string $slug    Unique slug identifying the notice
     * @param string $type    Type of the notice. Can be [ '' (default) | 'info' | 'error' | 'success' | 'warning' ].
     * @param array  $args    The array of additional arguments. Please see the $defaults array below.
     *
     * @return void
     */
    public static function add($message, $slug, $type = '', $args = array())
    {
        $defaults = array(
            'dismiss' => self::DISMISS_NONE, // Dismissible level: one of the self::DISMISS_* const. By default notice is not dismissible.
            'autop'   => true,               // `false` if not needed to pass message through wpautop().
            'class'   => ''                 // Additional CSS class.
        );

        $args    = wp_parse_args($args, $defaults);
        $dismiss = (int)$args['dismiss'];
        $classes = array();

        if (!empty($type)) {
            $classes[] = 'notice-' . esc_attr(sanitize_key($type));
        }

        if (!empty($args['class'])) {
            $classes[] = esc_attr(sanitize_key($args['class']));
        }

        if ($dismiss > self::DISMISS_NONE) {
            $classes[] = 'is-dismissible';
        }

        $id      = $dismiss === self::DISMISS_GLOBAL ? self::DEFAULT_PREFIX . self::GLOBAL_PREFIX . $slug : self::DEFAULT_PREFIX . $slug;
        $message = $args['autop'] ? wpautop($message) : $message;
        $notice  = sprintf(
            '<div class="notice dup-notice %s" id="%s">%s</div>',
            esc_attr(implode(' ', $classes)),
            esc_attr($id),
            $message
        );

        self::$notices[$slug] = $notice;
    }

    /**
     * Add multistep notice.
     *
     * @param array  $steps Array of info for each step.
     * @param string $slug  Unique slug identifying the notice
     * @param array  $args  Array of additional arguments. Details in the self::add() method.
     *
     * @return void
     */
    public static function addMultistep($steps, $slug, $type = '', $args = array())
    {
        $content = '<div class="dup-multi-notice">';
        foreach ($steps as $i => $step) {
            $hide     = $i === 0 ? '' : ' style="display: none;"';
            $content .= '<div class="dup-multi-notice-step dup-multi-notice-step-' . $i . '"' . $hide . '>';

            $content .= $step['message'];
            $content .= "<p>";
            foreach ($step["links"] as $link) {
                $url     = isset($link['url']) ? $link['url'] : "#";
                $target  = isset($link['url']) ? 'target="_blank"' : '';
                $switch  = isset($link['switch']) ? ' data-step="' . $link['switch'] . '"' : '';
                $dismiss = isset($link['dismiss']) && $link['dismiss'] ? ' class="dup-notice-dismiss"' : '';

                $content .= '<a href="' . $url . '"' . $dismiss . $switch . $target . '>' . $link['text'] . '</a><br>';
            }
            $content .= "</p>";
            $content .= "</div>";
        }
        $content .= "</div>";

        self::add($content, $slug, $type, $args);
    }

    /**
     * Add info notice.
     *
     * @param string $message Message to display.
     * @param string $slug    Unique slug identifying the notice
     * @param array  $args    Array of additional arguments. Details in the self::add() method.
     *
     * @return void
     */
    public static function info($message, $slug, $args = array())
    {

        self::add($message, $slug, self::NOTICE_TYPE_INFO, $args);
    }

    /**
     * Add error notice.
     *
     * @param string $message Message to display.
     * @param string $slug    Unique slug identifying the notice
     * @param array  $args    Array of additional arguments. Details in the self::add() method.
     *
     * @return void
     */
    public static function error($message, $slug, $args = array())
    {

        self::add($message, $slug, self::NOTICE_TYPE_ERROR, $args);
    }

    /**
     * Add success notice.
     *
     * @param string $message Message to display.
     * @param string $slug    Unique slug identifying the notice
     * @param array  $args    Array of additional arguments. Details in the self::add() method.
     *
     * @return void
     */
    public static function success($message, $slug, $args = array())
    {

        self::add($message, $slug, self::NOTICE_TYPE_SUCCESS, $args);
    }

    /**
     * Add warning notice.
     *
     * @param string $message Message to display.
     * @param string $slug    Unique slug identifying the notice
     * @param array  $args    Array of additional arguments. Details in the self::add() method.
     *
     * @return void
     */
    public static function warning($message, $slug, $args = array())
    {

        self::add($message, $slug, self::NOTICE_TYPE_WARNING, $args);
    }

    /**
     * AJAX routine that updates dismissed notices meta data.
     *
     * @return void
     */
    public static function dismiss_ajax()
    {

        // Run a security check.
        check_ajax_referer('duplicator-admin-notice', 'nonce');

        // Sanitize POST data.
        $post = array_map('sanitize_key', wp_unslash($_POST));

        // Update notices meta data.
        if (strpos($post['id'], self::GLOBAL_PREFIX) !== false) {
            // Check for permissions.
            if (!current_user_can('manage_options')) {
                wp_send_json_error();
            }

            $notices = self::dismiss_global($post['id']);
            $level   = self::DISMISS_GLOBAL;
        } else {
            $notices = self::dismiss_user($post['id']);
            $level   = self::DISMISS_USER;
        }

        /**
         * Allows developers to apply additional logic to the dismissing notice process.
         * Executes after updating option or user meta (according to the notice level).
         *
         * @param string  $notice_id Notice ID (slug).
         * @param integer $level     Notice level.
         * @param array   $notices   Dismissed notices.
         */
        do_action('duplicator_admin_notice_dismiss_ajax', $post['id'], $level, $notices);

        wp_send_json_success(
            array(
                'id'      => $post['id'],
                'time'    => time(),
                'level'   => $level,
                'notices' => $notices
            )
        );
    }

    /**
     * AJAX sub-routine that updates dismissed notices option.
     *
     * @param string $id Notice Id.
     *
     * @return array Notices.
     */
    private static function dismiss_global($id)
    {

        $id           = str_replace(self::GLOBAL_PREFIX, '', $id);
        $notices      = get_option(self::DISMISSED_NOTICES_OPTKEY, array());
        $notices[$id] = array(
            'time' => time()
        );

        update_option(self::DISMISSED_NOTICES_OPTKEY, $notices, true);

        return $notices;
    }

    /**
     *  AJAX sub-routine that updates dismissed notices user meta.
     *
     * @param string $id Notice Id.
     *
     * @return array Notices.
     */
    private static function dismiss_user($id)
    {

        $user_id      = get_current_user_id();
        $notices      = get_user_meta($user_id, self::DISMISSED_NOTICES_OPTKEY, true);
        $notices      = !is_array($notices) ? array() : $notices;
        $notices[$id] = array(
            'time' => time()
        );

        update_user_meta($user_id, self::DISMISSED_NOTICES_OPTKEY, $notices);

        return $notices;
    }

    /**
     * Delete related option
     *
     * @return void
     */
    public static function deleteOption()
    {
        delete_option(self::DISMISSED_NOTICES_OPTKEY);
    }
}
