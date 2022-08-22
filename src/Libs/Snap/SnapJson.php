<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\Snap;

// phpcs:disable
require_once(__DIR__ . '/JsonSerializable.php'); 
// phpcs:enable

class SnapJson
{
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
    public static function jsonEncode($data, $options = 0, $depth = 512)
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

        $preparedData = self::jsonPrepareData($data);
        // Prepare the data for JSON serialization.
        $args[0] = $preparedData;

        $json = call_user_func_array('json_encode', $args);

        // If json_encode() was successful, no need to do more sanity checking.
        // ... unless we're in an old version of PHP, and json_encode() returned
        // a string containing 'null'. Then we need to do more sanity checking.
        if (false !== $json && ( version_compare(PHP_VERSION, '5.5', '>=') || false === strpos($json, 'null') )) {
            return $json;
        }

        try {
            $args[0] = self::jsonSanityCheck($preparedData, $depth);
        } catch (\Exception $e) {
            return false;
        }

        $json = null;
        $preparedData = null;
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
    public static function jsonEncodePPrint($data, $options = 0, $depth = 512)
    {
        if (defined('JSON_PRETTY_PRINT')) {
            return self::jsonEncode($data, JSON_PRETTY_PRINT | $options, $depth);
        } else {
            return self::jsonEncode($data, $options, $depth);
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
    private static function jsonPrepareData($data)
    {
        if (
            !defined('WP_JSON_SERIALIZE_COMPATIBLE') ||
            WP_JSON_SERIALIZE_COMPATIBLE === false
        ) {
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
                return array_map(array(__CLASS__, 'jsonPrepareData'), $data);

            case 'object':
                // If this is an incomplete object (__PHP_Incomplete_Class), bail.
                if (!is_object($data)) {
                    return null;
                }

                if ($data instanceof \JsonSerializable) {
                    $data = $data->jsonSerialize();
                } else {
                    $data = get_object_vars($data);
                }

                // Now, pass the array (or whatever was returned from jsonSerialize through).
                return self::jsonPrepareData($data);

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
    private static function jsonSanityCheck($data, $depth)
    {
        if ($depth < 0) {
            throw new \Exception('Reached depth limit');
        }

        if ($data instanceof \JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        if (is_array($data)) {
            $output = array();
            foreach ($data as $id => $el) {
                // Don't forget to sanitize the ID!
                if (is_string($id)) {
                    $clean_id = self::jsonConvertString($id);
                } else {
                    $clean_id = $id;
                }

                // Check the element type, so that we're only recursing if we really have to.
                if (is_array($el) || is_object($el)) {
                    $output[$clean_id] = self::jsonSanityCheck($el, $depth - 1);
                } elseif (is_string($el)) {
                    $output[$clean_id] = self::jsonConvertString($el);
                } else {
                    $output[$clean_id] = $el;
                }
            }
        } elseif (is_object($data)) {
            $output = new \stdClass();
            foreach ($data as $id => $el) {
                if (is_string($id)) {
                    $clean_id = self::jsonConvertString($id);
                } else {
                    $clean_id = $id;
                }

                if (is_array($el) || is_object($el)) {
                    $output->$clean_id = self::jsonSanityCheck($el, $depth - 1);
                } elseif (is_string($el)) {
                    $output->$clean_id = self::jsonConvertString($el);
                } else {
                    $output->$clean_id = $el;
                }
            }
        } elseif (is_string($data)) {
            return self::jsonConvertString($data);
        } else {
            return $data;
        }

        return $output;
    }

    /**
     * Return json string
     *
     * @param string $string data
     *
     * @return string
     */
    private static function jsonConvertString($string)
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
            return self::checkInvalidUTF8($string, true);
        }
    }

    /**
     * Checks for invalid UTF8 in a string.
     *
     * @param string $string The text which is to be checked.
     * @param bool   $strip  Optional. Whether to attempt to strip out invalid UTF8. Default is false.
     *
     * @return string The checked text.
     */
    public static function checkInvalidUTF8($string, $strip = false)
    {
        $string = (string) $string;

        if (0 === strlen($string)) {
            return '';
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

    /**
     *
     * todo remove esc_attr wp function
     *
     * @param mixed $val object to be encoded
     * @return string escaped json string
     */
    public static function jsonEncodeEscAttr($val)
    {
        return esc_attr(json_encode($val));
    }

    /**
     * this function return a json encoded string without quotes at the beginning and the end
     *
     * @param string $string json string
     * @return string
     * @throws \Exception
     */
    public static function getJsonWithoutQuotes($string)
    {
        if (!is_string($string)) {
            throw new \Exception('the function getJsonStringWithoutQuotes take only strings');
        }

        return substr(self::jsonEncode($string), 1, -1);
    }
}
