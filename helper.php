<?php

defined('ABSPATH') || exit;

if (!function_exists('duplicator_cloned_get_home_path')) {
    /**
     * Cloned function of the get_home_path(). It is same code except two lines of code
     * Get the absolute filesystem path to the root of the WordPress installation
     *
     * @return string Full filesystem path to the root of the WordPress installation
     */
    function duplicator_cloned_get_home_path()
    {
        $home    = set_url_scheme(get_option('home'), 'http');
        $siteurl = set_url_scheme(get_option('siteurl'), 'http');

        // below two lines
        // extra added by snapcreek
        // when home is www. path and siteurl is non-www , the duplicator_get_home_psth() was  returning empty value
        $home    = str_ireplace('://www.', '://', $home);
        $siteurl = str_ireplace('://www.', '://', $siteurl);

        if (!empty($home) && 0 !== strcasecmp($home, $siteurl)  && $home !== $siteurl) {
            $wp_path_rel_to_home = str_ireplace($home, '', $siteurl); /* $siteurl - $home */
            $pos                 = strripos(str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']), trailingslashit($wp_path_rel_to_home));
            $home_path           = substr($_SERVER['SCRIPT_FILENAME'], 0, $pos);
            $home_path           = trailingslashit($home_path);
        } else {
            $home_path = ABSPATH;
        }
        return str_replace('\\', '/', $home_path);
    }
}

if (!function_exists('duplicator_get_home_path')) {
    /**
     * Get home path
     *
     * @return string
     */
    function duplicator_get_home_path()
    {
        static $homePath = null;
        if (is_null($homePath)) {
            if (!function_exists('get_home_path')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            $homePath = wp_normalize_path(duplicator_cloned_get_home_path());
            if ($homePath == '//' || $homePath == '') {
                $homePath = '/';
            } else {
                $homePath = rtrim($homePath, '/');
            }
        }
        return $homePath;
    }
}

if (!function_exists('duplicator_get_abs_path')) {

    /**
     * Get abspath
     *
     * @return string
     */
    function duplicator_get_abs_path()
    {
        static $absPath = null;
        if (is_null($absPath)) {
            $absPath = wp_normalize_path(ABSPATH);
            if ($absPath == '//' || $absPath == '') {
                $absPath = '/';
            } else {
                $absPath = rtrim($absPath, '/');
            }
        }
        return $absPath;
    }
}

if (!function_exists('sanitize_textarea_field')) {
    /**
     * Sanitizes a multiline string from user input or from the database.
     *
     * The function is like sanitize_text_field(), but preserves
     * new lines (\n) and other whitespace, which are legitimate
     * input in textarea elements.
     *
     * @see sanitize_text_field()
     *
     * @since 4.7.0
     *
     * @param string $str String to sanitize.
     *
     * @return string Sanitized string.
     */
    function sanitize_textarea_field($str)
    {
        $filtered = _sanitize_text_fields($str, true);

        /**
         * Filters a sanitized textarea field string.
         *
         * @since 4.7.0
         *
         * @param string $filtered The sanitized string.
         * @param string $str      The string prior to being sanitized.
         */
        return apply_filters('sanitize_textarea_field', $filtered, $str);
    }
}

if (!function_exists('_sanitize_text_fields')) {
    /**
     * Internal helper function to sanitize a string from user input or from the db
     *
     * @since  4.7.0
     * @access private
     *
     * @param string $str           String to sanitize.
     * @param bool   $keep_newlines optional Whether to keep newlines. Default: false.
     *
     * @return string Sanitized string.
     */
    function _sanitize_text_fields($str, $keep_newlines = false)
    {
        $filtered = wp_check_invalid_utf8($str);

        if (strpos($filtered, '<') !== false) {
            $filtered = wp_pre_kses_less_than($filtered);
            // This will strip extra whitespace for us.
            $filtered = wp_strip_all_tags($filtered, false);

            // Use html entities in a special case to make sure no later
            // newline stripping stage could lead to a functional tag
            $filtered = str_replace("<\n", "&lt;\n", $filtered);
        }

        if (! $keep_newlines) {
            $filtered = preg_replace('/[\r\n\t ]+/', ' ', $filtered);
        }
        $filtered = trim($filtered);

        $found = false;
        while (preg_match('/%[a-f0-9]{2}/i', $filtered, $match)) {
            $filtered = str_replace($match[0], '', $filtered);
            $found    = true;
        }

        if ($found) {
            // Strip out the whitespace that may now exist after removing the octets.
            $filtered = trim(preg_replace('/ +/', ' ', $filtered));
        }

        return $filtered;
    }
}

if (!function_exists('wp_normalize_path')) {
    /**
     * Normalize a filesystem path.
     *
     * On windows systems, replaces backslashes with forward slashes
     * and forces upper-case drive letters.
     * Allows for two leading slashes for Windows network shares, but
     * ensures that all other duplicate slashes are reduced to a single.
     *
     * @param string $path Path to normalize.
     *
     * @return string Normalized path.
     */
    function wp_normalize_path($path)
    {
        $wrapper = '';
        if (wp_is_stream($path)) {
            list( $wrapper, $path ) = explode('://', $path, 2);
            $wrapper               .= '://';
        }

        // Standardise all paths to use /
        $path = str_replace('\\', '/', $path);

        // Replace multiple slashes down to a singular, allowing for network shares having two slashes.
        $path = preg_replace('|(?<=.)/+|', '/', $path);

        // Windows paths should uppercase the drive letter
        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }

        return $wrapper . $path;
    }
}
