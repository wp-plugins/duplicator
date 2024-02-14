<?php

use Duplicator\Libs\Snap\SnapJson;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/**
 * Utility class for working with JSON data
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @subpackage classes/utilities
 * @copyright  (c) 2017, Snapcreek LLC
 * @license    https://opensource.org/licenses/GPL-3.0 GNU Public License
 */

// Exit if accessed directly
if (! defined('DUPLICATOR_VERSION')) {
    exit;
}

class DUP_JSON
{
    // PHP 5.3 doesn't allow concating of strings within an array initializer so accepting long string
    protected static $messages = array(
        JSON_ERROR_NONE => 'No error has occurred',
        JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
        JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
        JSON_ERROR_SYNTAX => 'Syntax error',
        JSON_ERROR_UTF8 => 'Malformed UTF-8 characters. To resolve see https://duplicator.com/knowledge-base/ how-to-resolve-malformed-utf-8-characters-possibly-incorrectly-encoded-issues/'
    );
    /**
     * Used on PHP 5.3+ to better handle calling the json_encode method
     *
     * Returns a string containing the JSON representation of the supplied value
     *
     * @return string
     */
    public static function customEncode($value, $iteration = 1)
    {
        if (DUP_Util::$on_php_53_plus) {
            $encoded = SnapJson::jsonEncodePPrint($value);
            switch (json_last_error()) {
                case JSON_ERROR_NONE:
                    return $encoded;
                case JSON_ERROR_DEPTH:
                    throw new RuntimeException('Maximum stack depth exceeded');
                case JSON_ERROR_STATE_MISMATCH:
                    throw new RuntimeException('Underflow or the modes mismatch');
                case JSON_ERROR_CTRL_CHAR:
                    throw new RuntimeException('Unexpected control character found');
                case JSON_ERROR_SYNTAX:
                    throw new RuntimeException('Syntax error, malformed JSON');
                case JSON_ERROR_UTF8:
                    if ($iteration == 1) {
                        $clean = self::makeUTF8($value);
                        return self::customEncode($clean, $iteration + 1);
                    } else {
                        throw new RuntimeException('UTF-8 error loop');
                    }
                default:
                    throw new RuntimeException('Unknown error');
            }
        } else {
            return self::oldCustomEncode($value);
        }
    }

    public static function safeEncode($data, $options = 0, $depth = 512)
    {
        try {
            $jsonString = SnapJson::jsonEncode($data, $options, $depth);
        } catch (Exception $e) {
            $jsonString = false;
        }

        if (($jsonString === false) || trim($jsonString) == '') {
            $jsonString = self::customEncode($data);
            if (($jsonString === false) || trim($jsonString) == '') {
                throw new Exception('Unable to generate JSON from object');
            }
        }
        return $jsonString;
    }

    /**
     * Attempts to only call the json_decode method directly
     *
     * Returns the value encoded in json in appropriate PHP type. Values true, false and null are returned as TRUE, FALSE and NULL respectively.
     * NULL is returned if the json cannot be decoded or if the encoded data is deeper than the recursion limit.
     *
     * @return object
     */
    public static function decode($json, $assoc = false)
    {
        $result = json_decode($json, $assoc);
        if ($result !== null) {
            return $result;
        }

        if (function_exists('json_last_error')) {
            throw new RuntimeException(self::$messages[json_last_error()]);
        } else {
            throw new RuntimeException("DUP_JSON decode error");
        }
    }

    private static function makeUTF8($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = self::makeUTF8($value);
            }
        } elseif (is_string($mixed)) {
            return utf8_encode($mixed);
        }
        return $mixed;
    }

    private static function escapeString($str)
    {
        return addcslashes($str, "\v\t\n\r\f\"\\/");
    }

    private static function oldCustomEncode($in)
    {
        $out = "";
        if (is_object($in)) {
            $in = get_object_vars($in);
        }

        if (is_array($in)) {
            $obj = false;
            $arr = array();
            foreach ($in as $key => $val) {
                if (!is_numeric($key)) {
                    $obj = true;
                }
                $arr[$key] = self::oldCustomEncode($val);
            }

            if ($obj) {
                foreach ($arr as $key => $val) {
                    $arr[$key] = "\"" . self::escapeString($key) . "\":{$val}";
                }
                $val  = implode(',', $arr);
                $out .= "{{$val}}";
            } else {
                $val  = implode(',', $arr);
                $out .= "[{$val}]";
            }
        } elseif (is_bool($in)) {
            $out .= $in ? 'true' : 'false';
        } elseif (is_null($in)) {
            $out .= 'null';
        } elseif (is_string($in)) {
            $out .= "\"" . self::escapeString($in) . "\"";
        } else {
            $out .= $in;
        }

        return "{$out}";
    }
}
