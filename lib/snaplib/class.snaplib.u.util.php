<?php
/**
 * Utility functions
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package snaplib
 * @subpackage classes/utilities
 * @copyright (c) 2017, Snapcreek LLC
 * @license	https://opensource.org/licenses/GPL-3.0 GNU Public License
 *
 */
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

if (!interface_exists('JsonSerializable')) {
    define('SNAP_WP_JSON_SERIALIZE_COMPATIBLE', true);

    /**
     * JsonSerializable interface.
     *
     * Compatibility shim for PHP <5.4
     *
     * @link https://secure.php.net/jsonserializable
     *
     * @since 4.4.0
     */
    interface JsonSerializable
    {

        public function jsonSerialize();
    }
}

if (!class_exists('DupLiteSnapLibUtil', false)) {

    class DupLiteSnapLibUtil
    {

        public static function getArrayValue(&$array, $key, $required = true, $default = null)
        {
            if (array_key_exists($key, $array)) {
                return $array[$key];
            } else {
                if ($required) {
                    throw new Exception("Key {$key} not present in array");
                } else {
                    return $default;
                }
            }
        }

        public static function getCallingFunctionName()
        {
            $callers      = debug_backtrace();
            $functionName = $callers[2]['function'];
            $className    = isset($callers[2]['class']) ? $callers[2]['class'] : '';

            return "{$className}::{$functionName}";
        }

        public static function getWorkPercent($startingPercent, $endingPercent, $totalTaskCount, $currentTaskCount)
        {
            if ($totalTaskCount > 0) {
                $percent = floor($startingPercent + (($endingPercent - $startingPercent) * ($currentTaskCount / (float) $totalTaskCount)));
            } else {
                $percent = 0;
            }

            return $percent;
        }

        public static function make_hash()
        {
            // IMPORTANT!  Be VERY careful in changing this format - the FTP delete logic requires 3 segments with the last segment to be the date in YmdHis format.
            try {
                if (function_exists('random_bytes') && self::PHP53()) {
                    return bin2hex(random_bytes(8)).mt_rand(1000, 9999).'_'.date("YmdHis");
                } else {
                    return strtolower(md5(uniqid(rand(), true))).'_'.date("YmdHis");
                }
            } catch (Exception $exc) {
                return strtolower(md5(uniqid(rand(), true))).'_'.date("YmdHis");
            }
        }

        public static function PHP53()
        {
            return version_compare(PHP_VERSION, '5.3.2', '>=');
        }

        /**
         * Groups an array into arrays by a given key, or set of keys, shared between all array members.
         *
         * Based on {@author Jake Zatecky}'s {@link https://github.com/jakezatecky/array_group_by array_group_by()} function.
         * This variant allows $key to be closures.
         *
         * @param array $array   The array to have grouping performed on.
         * @param mixed $key,... The key to group or split by. Can be a _string_, an _integer_, a _float_, or a _callable_.
         *                       - If the key is a callback, it must return a valid key from the array.
         *                       - If the key is _NULL_, the iterated element is skipped.
         *                       - string|int callback ( mixed $item )
         *
         * @return array|null Returns a multidimensional array or `null` if `$key` is invalid.
         */
        public static function arrayGroupBy(array $array, $key)
        {
            if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
                trigger_error('array_group_by(): The key should be a string, an integer, or a callback', E_USER_ERROR);
                return null;
            }
            $func    = (!is_string($key) && is_callable($key) ? $key : null);
            $_key    = $key;
            // Load the new array, splitting by the target key
            $grouped = array();
            foreach ($array as $value) {
                $key = null;
                if (is_callable($func)) {
                    $key = call_user_func($func, $value);
                } elseif (is_object($value) && isset($value->{$_key})) {
                    $key = $value->{$_key};
                } elseif (isset($value[$_key])) {
                    $key = $value[$_key];
                }
                if ($key === null) {
                    continue;
                }
                $grouped[$key][] = $value;
            }
            // Recursively build a nested grouping if more parameters are supplied
            // Each grouped array value is grouped according to the next sequential key
            if (func_num_args() > 2) {
                $args = func_get_args();
                foreach ($grouped as $key => $value) {
                    $params        = array_merge(array($value), array_slice($args, 2, func_num_args()));
                    $grouped[$key] = call_user_func_array('DupLiteSnapLibUtil::arrayGroupBy', $params);
                }
            }
            return $grouped;
        }

        /**
         * Converts human readable types (10GB) to bytes
         *
         * @param string $from   A human readable byte size such as 100MB
         *
         * @return int	Returns and integer of the byte size
         */
        public static function convertToBytes($from)
        {
            if (is_numeric($from)) {
                return $from;
            }

            $number = substr($from, 0, -2);
            switch (strtoupper(substr($from, -2))) {
                case "KB": return $number * 1024;
                case "MB": return $number * pow(1024, 2);
                case "GB": return $number * pow(1024, 3);
                case "TB": return $number * pow(1024, 4);
                case "PB": return $number * pow(1024, 5);
            }

            $number = substr($from, 0, -1);
            switch (strtoupper(substr($from, -1))) {
                case "K": return $number * 1024;
                case "M": return $number * pow(1024, 2);
                case "G": return $number * pow(1024, 3);
                case "T": return $number * pow(1024, 4);
                case "P": return $number * pow(1024, 5);
            }
            return $from;
        }

        /**
         *  Sanitize input for XSS code
         *
         *  @param string $val		The value to sanitize
         *
         *  @return string Returns the input value cleaned up.
         */
        public static function sanitize($input)
        {
            return filter_var($input, FILTER_SANITIZE_STRING);
        }

        /**
         * Determines whether a PHP ini value is changeable at runtime.
         *
         * @since 4.6.0
         *
         * @staticvar array $ini_all
         *
         * @link https://secure.php.net/manual/en/function.ini-get-all.php
         *
         * @param string $setting The name of the ini setting to check.
         * @return bool True if the value is changeable at runtime. False otherwise.
         */
        public static function wp_is_ini_value_changeable($setting)
        {
            if (function_exists('wp_is_ini_value_changeable')) {
                return wp_is_ini_value_changeable($setting);
            }

            static $ini_all;

            if (!isset($ini_all)) {
                $ini_all = false;
                // Sometimes `ini_get_all()` is disabled via the `disable_functions` option for "security purposes".
                if (function_exists('ini_get_all')) {
                    $ini_all = ini_get_all();
                }
            }

            // Bit operator to workaround https://bugs.php.net/bug.php?id=44936 which changes access level to 63 in PHP 5.2.6 - 5.2.17.
            if (isset($ini_all[$setting]['access']) && ( INI_ALL === ( $ini_all[$setting]['access'] & 7 ) || INI_USER === ( $ini_all[$setting]['access'] & 7 ) )) {
                return true;
            }

            // If we were unable to retrieve the details, fail gracefully to assume it's changeable.
            if (!is_array($ini_all)) {
                return true;
            }

            return false;
        }

        /**
         * Encode a variable into JSON, with some sanity checks.
         *
         * @since 4.1.0
         *
         * @param mixed $data    Variable (usually an array or object) to encode as JSON.
         * @param int   $options Optional. Options to be passed to json_encode(). Default 0.
         * @param int   $depth   Optional. Maximum depth to walk through $data. Must be
         *                       greater than 0. Default 512.
         * @return string|false The JSON encoded string, or false if it cannot be encoded.
         */
        public static function wp_json_encode($data, $options = 0, $depth = 512)
        {
            if (function_exists('wp_json_encode')) {
                return wp_json_encode($data, $options, $depth);
            }

            /*
             * json_encode() has had extra params added over the years.
             * $options was added in 5.3, and $depth in 5.5.
             * We need to make sure we call it with the correct arguments.
             */
            if (version_compare(PHP_VERSION, '5.5', '>=')) {
                $args = array($data, $options, $depth);
            } elseif (version_compare(PHP_VERSION, '5.3', '>=')) {
                $args = array($data, $options);
            } else {
                $args = array($data);
            }

            // Prepare the data for JSON serialization.
            $args[0] = self::_wp_json_prepare_data($data);

            $json = @call_user_func_array('json_encode', $args);

            // If json_encode() was successful, no need to do more sanity checking.
            // ... unless we're in an old version of PHP, and json_encode() returned
            // a string containing 'null'. Then we need to do more sanity checking.
            if (false !== $json && ( version_compare(PHP_VERSION, '5.5', '>=') || false === strpos($json, 'null') )) {
                return $json;
            }

            try {
                $args[0] = self::_wp_json_sanity_check($data, $depth);
            } catch (Exception $e) {
                return false;
            }

            return call_user_func_array('json_encode', $args);
        }

        /**
         * wp_json_encode with pretty print if define exists
         *
         * @param mixed $data    Variable (usually an array or object) to encode as JSON.
         * @param int   $options Optional. Options to be passed to json_encode(). Default 0.
         * @param int   $depth   Optional. Maximum depth to walk through $data. Must be
         *                       greater than 0. Default 512.
         * @return string|false The JSON encoded string, or false if it cannot be encoded.
         */
        public static function wp_json_encode_pprint($data, $options = 0, $depth = 512)
        {
            if (defined('JSON_PRETTY_PRINT')) {
                return self::wp_json_encode($data, JSON_PRETTY_PRINT | $options, $depth);
            } else {
                return self::wp_json_encode($data, $options, $depth);
            }
        }

        /**
         * Prepares response data to be serialized to JSON.
         *
         * This supports the JsonSerializable interface for PHP 5.2-5.3 as well.
         *
         * @ignore
         * @since 4.4.0
         * @access private
         *
         * @param mixed $data Native representation.
         * @return bool|int|float|null|string|array Data ready for `json_encode()`.
         */
        private static function _wp_json_prepare_data($data)
        {
            if (!defined('SNAP_WP_JSON_SERIALIZE_COMPATIBLE') || SNAP_WP_JSON_SERIALIZE_COMPATIBLE === false || !defined('WP_JSON_SERIALIZE_COMPATIBLE') || WP_JSON_SERIALIZE_COMPATIBLE === false) {
                return $data;
            }

            switch (gettype($data)) {
                case 'boolean':
                case 'integer':
                case 'double':
                case 'string':
                case 'NULL':
                    // These values can be passed through.
                    return $data;

                case 'array':
                    // Arrays must be mapped in case they also return objects.
                    return array_map('self::_wp_json_prepare_data', $data);

                case 'object':
                    // If this is an incomplete object (__PHP_Incomplete_Class), bail.
                    if (!is_object($data)) {
                        return null;
                    }

                    if ($data instanceof JsonSerializable) {
                        $data = $data->jsonSerialize();
                    } else {
                        $data = get_object_vars($data);
                    }

                    // Now, pass the array (or whatever was returned from jsonSerialize through).
                    return self::_wp_json_prepare_data($data);

                default:
                    return null;
            }
        }

        /**
         * Perform sanity checks on data that shall be encoded to JSON.
         *
         * @ignore
         * @since 4.1.0
         * @access private
         *
         * @see wp_json_encode()
         *
         * @param mixed $data  Variable (usually an array or object) to encode as JSON.
         * @param int   $depth Maximum depth to walk through $data. Must be greater than 0.
         * @return mixed The sanitized data that shall be encoded to JSON.
         */
        private static function _wp_json_sanity_check($data, $depth)
        {
            if ($depth < 0) {
                throw new Exception('Reached depth limit');
            }

            if (is_array($data)) {
                $output = array();
                foreach ($data as $id => $el) {
                    // Don't forget to sanitize the ID!
                    if (is_string($id)) {
                        $clean_id = self::_wp_json_convert_string($id);
                    } else {
                        $clean_id = $id;
                    }

                    // Check the element type, so that we're only recursing if we really have to.
                    if (is_array($el) || is_object($el)) {
                        $output[$clean_id] = self::_wp_json_sanity_check($el, $depth - 1);
                    } elseif (is_string($el)) {
                        $output[$clean_id] = self::_wp_json_convert_string($el);
                    } else {
                        $output[$clean_id] = $el;
                    }
                }
            } elseif (is_object($data)) {
                $output = new stdClass;
                foreach ($data as $id => $el) {
                    if (is_string($id)) {
                        $clean_id = self::_wp_json_convert_string($id);
                    } else {
                        $clean_id = $id;
                    }

                    if (is_array($el) || is_object($el)) {
                        $output->$clean_id = self::_wp_json_sanity_check($el, $depth - 1);
                    } elseif (is_string($el)) {
                        $output->$clean_id = self::_wp_json_convert_string($el);
                    } else {
                        $output->$clean_id = $el;
                    }
                }
            } elseif (is_string($data)) {
                return self::_wp_json_convert_string($data);
            } else {
                return $data;
            }

            return $output;
        }

        private static function _wp_json_convert_string($string)
        {
            static $use_mb = null;
            if (is_null($use_mb)) {
                $use_mb = function_exists('mb_convert_encoding');
            }

            if ($use_mb) {
                $encoding = mb_detect_encoding($string, mb_detect_order(), true);
                if ($encoding) {
                    return mb_convert_encoding($string, 'UTF-8', $encoding);
                } else {
                    return mb_convert_encoding($string, 'UTF-8', 'UTF-8');
                }
            } else {
                return self::wp_check_invalid_utf8($string, true);
            }
        }

        /**
         * Checks for invalid UTF8 in a string.
         *
         * @since 2.8.0
         *
         * @staticvar bool $is_utf8
         * @staticvar bool $utf8_pcre
         *
         * @param string  $string The text which is to be checked.
         * @param bool    $strip Optional. Whether to attempt to strip out invalid UTF8. Default is false.
         * @return string The checked text.
         */
        public static function wp_check_invalid_utf8($string, $strip = false)
        {
            $string = (string) $string;

            if (0 === strlen($string)) {
                return '';
            }

            // Store the site charset as a static to avoid multiple calls to get_option()
            static $is_utf8 = null;
            if (!isset($is_utf8)) {
                $is_utf8 = in_array(get_option('blog_charset'), array('utf8', 'utf-8', 'UTF8', 'UTF-8'));
            }
            if (!$is_utf8) {
                return $string;
            }

            // Check for support for utf8 in the installed PCRE library once and store the result in a static
            static $utf8_pcre = null;
            if (!isset($utf8_pcre)) {
                $utf8_pcre = @preg_match('/^./u', 'a');
            }
            // We can't demand utf8 in the PCRE installation, so just return the string in those cases
            if (!$utf8_pcre) {
                return $string;
            }

            // preg_match fails when it encounters invalid UTF8 in $string
            if (1 === @preg_match('/^./us', $string)) {
                return $string;
            }

            // Attempt to strip the bad chars if requested (not recommended)
            if ($strip && function_exists('iconv')) {
                return iconv('utf-8', 'utf-8', $string);
            }

            return '';
        }
    }
}