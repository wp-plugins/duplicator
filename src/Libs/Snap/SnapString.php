<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Snap;

class SnapString
{
    /**
     * Return true or false in string
     *
     * @param mixed $b input value
     *
     * @return string
     */
    public static function boolToString($b)
    {
        return ($b ? 'true' : 'false');
    }

    /**
     * Truncate string and add ellipsis
     *
     * @param string $s        string to truncate
     * @param int    $maxWidth max length
     *
     * @return string
     */
    public static function truncateString($s, $maxWidth)
    {
        if (strlen($s) > $maxWidth) {
            $s = substr($s, 0, $maxWidth - 3) . '...';
        }

        return $s;
    }

    /**
     * Returns true if the $haystack string starts with the $needle
     *
     * @param string $haystack The full string to search in
     * @param string $needle   The string to for
     *
     * @return bool Returns true if the $haystack string starts with the $needle
     */
    public static function startsWith($haystack, $needle)
    {
        return (strpos($haystack, $needle) === 0);
    }

    /**
     * Returns true if the $haystack string end with the $needle
     *
     * @param string $haystack The full string to search in
     * @param string $needle   The string to for
     *
     * @return bool Returns true if the $haystack string starts with the $needle
     */
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    /**
     * Returns true if the $needle is found in the $haystack
     *
     * @param string $haystack The full string to search in
     * @param string $needle   The string to for
     *
     * @return bool
     */
    public static function contains($haystack, $needle)
    {
        $pos = strpos($haystack, $needle);
        return ($pos !== false);
    }

    /**
     * Implode array key values to a string
     *
     * @param string  $glue   separator
     * @param mixed[] $pieces array fo implode
     * @param string  $format format
     *
     * @return string
     */
    public static function implodeKeyVals($glue, $pieces, $format = '%s="%s"')
    {
        $strList = array();
        foreach ($pieces as $key => $value) {
            if (is_scalar($value)) {
                $strList[] = sprintf($format, $key, $value);
            } else {
                $strList[] = sprintf($format, $key, print_r($value, true));
            }
        }
        return implode($glue, $strList);
    }

    /**
     * Replace last occurrence
     *
     * @param string  $search        The value being searched for
     * @param string  $replace       The replacement value that replaces found search values
     * @param string  $str           The string or array being searched and replaced on, otherwise known as the haystack
     * @param boolean $caseSensitive Whether the replacement should be case sensitive or not
     *
     * @return string
     */
    public static function strLastReplace($search, $replace, $str, $caseSensitive = true)
    {
        $pos = $caseSensitive ? strrpos($str, $search) : strripos($str, $search);
        if (false !== $pos) {
            $str = substr_replace($str, $replace, $pos, strlen($search));
        }
        return $str;
    }

    /**
     * Check if passed string have html tags
     *
     * @param string $string input string
     *
     * @return boolean
     */
    public static function isHTML($string)
    {
        return ($string != strip_tags($string));
    }

    /**
     * Safe way to get number of characters
     *
     * @param ?string $string input string
     *
     * @return int
     */
    public static function stringLength($string)
    {
        if (!isset($string) || $string == "") { // null == "" is also true
            return 0;
        }
        return strlen($string);
    }
}
