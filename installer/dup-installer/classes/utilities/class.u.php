<?php

/**
 * Various Static Utility methods for working with the installer
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Bootstrap;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;

class DUPX_U
{
    const MAINTENANCE_INDEX_MARKER = '<!-- DUPLICATOR INSTALLER MAINTENANCE -->';

    public static function init()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            $_SERVER['REQUEST_URI'] = substr($_SERVER['SCRIPT_NAME'], 0);
            if (isset($_SERVER['QUERY_STRING']) and $_SERVER['QUERY_STRING'] != "") {
                $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
            }
        }
    }

    /**
     * Adds a slash to the end of a file or directory path
     *
     * @param string $path      A path
     *
     * @return string The original $path with a with '/' added to the end.
     */
    public static function addSlash($path)
    {
        $last_char = substr($path, strlen($path) - 1, 1);
        if ($last_char != '/') {
            $path .= '/';
        }
        return $path;
    }

    /**
     * Does one string contain other
     *
     * @param string $haystack      The full string to search
     * @param string $needle        The substring to search on
     *
     * @return bool Returns true if the $needle was found in the $haystack
     */
    public static function contains($haystack, $needle)
    {
        $pos = strpos($haystack, $needle);
        return ($pos !== false);
    }

    /**
     * move all folder content up to parent
     *
     * @param string $subFolderName full path
     * @param boolean $deleteSubFolder if true delete subFolder after moved all
     *
     * @return boolean
     */
    public static function moveUpfromSubFolder($subFolderName, $deleteSubFolder = false)
    {
        if (!is_dir($subFolderName)) {
            return false;
        }

        $parentFolder = dirname($subFolderName);
        if (!is_writable($parentFolder)) {
            return false;
        }

        $success = true;
        if (($subList = glob(rtrim($subFolderName, '/') . '/*', GLOB_NOSORT)) === false) {
            Log::info("Problem glob folder " . $subFolderName);
            return false;
        } else {
            foreach ($subList as $cName) {
                $destination = $parentFolder . '/' . basename($cName);
                if (file_exists($destination)) {
                    $success = self::deletePath($destination);
                }

                if ($success) {
                    $success = rename($cName, $destination);
                } else {
                    break;
                }
            }

            if ($success && $deleteSubFolder) {
                $success = self::deleteDirectory($subFolderName, true);
            }
        }

        if (!$success) {
            Log::info("Problem om moveUpfromSubFolder subFolder:" . $subFolderName);
        }

        return $success;
    }

    /**
     * @param string $archive_filepath  full path of zip archive
     *
     * @return boolean|string  path of dup-installer folder of false if not found
     */
    public static function findDupInstallerFolder($archive_filepath)
    {
        if (!class_exists('ZipArchive')) {
            return '';
        }
        $zipArchive    = new ZipArchive();
        $result        = false;
        $dupArchiveTxt = Bootstrap::ARCHIVE_PREFIX . Bootstrap::getPackageHash() . Bootstrap::ARCHIVE_EXTENSION;

        if ($zipArchive->open($archive_filepath) === true) {
            for ($i = 0; $i < $zipArchive->numFiles; $i++) {
                $stat     = $zipArchive->statIndex($i);
                $safePath = rtrim(self::setSafePath($stat['name']), '/');
                if (substr_count($safePath, '/') > 2) {
                    continue;
                }
                $exploded = explode('/', $safePath);
                if (
                    ($dup_index = array_search($dupArchiveTxt, $exploded)) !== false &&
                    $exploded[$dup_index - 1] === 'dup-installer'
                ) {
                    $result = implode('/', array_slice($exploded, 0, $dup_index - 1));
                    break;
                }
            }
            if ($zipArchive->close() !== true) {
                Log::info("Can't close ziparchive:" . $archive_filepath);
                return false;
            }
        } else {
            Log::info("Can't open zip archive:" . $archive_filepath);
            return false;
        }

        return $result;
    }

    /**
     * Safely remove a directory and recursively files only if needed
     *
     * @param string $directory The full path to the directory to remove
     * @param string $recursive recursively remove all items
     *
     * @return bool Returns true if all content was removed
     */
    public static function deleteDirectory($directory, $recursive)
    {
        $success = true;
        if ($excepted_subdirectories = null) {
            $excepted_subdirectories = array();
        }

        if (!file_exists($directory)) {
            return false;
        }

        $filenames = array_diff(scandir($directory), array('.', '..'));
        foreach ($filenames as $filename) {
            if (is_dir("$directory/$filename")) {
                if ($recursive) {
                    $success = self::deleteDirectory("$directory/$filename", true);
                }
            } else {
                $success = @unlink("$directory/$filename");
            }

            if ($success === false) {
                //self::log("Problem deleting $directory/$filename");
                break;
            }
        }

        return $success && rmdir($directory);
    }

    /**
     * Safely remove a file or directory and recursively if needed
     *
     * @param string $directory The full path to the directory to remove
     *
     * @return bool Returns true if all content was removed
     */
    public static function deletePath($path)
    {
        $success = true;
        if (is_dir($path)) {
            $success = self::deleteDirectory($path, true);
        } else {
            $success = @unlink($path);
            if ($success === false) {
                Log::info(__FUNCTION__ . ": Problem deleting file:" . $path);
            }
        }

        return $success;
    }

    /**
     * Dumps a variable for debugging
     *
     * @param string $var The variable to view
     * @param bool   $pretty Pretty print the var
     *
     * @return object A visual representation of an object
     */
    public static function dump($var, $pretty = false)
    {
        if ($pretty) {
            echo '<pre>';
            print_r($var);
            echo '</pre>';
        } else {
            print_r($var);
        }
    }

    /**
     * Return a string with the elapsed time
     *
     * @see getMicrotime()
     *
     * @param mixed number $end     The final time in the sequence to measure
     * @param mixed number $start   The start time in the sequence to measure
     *
     * @return string   The time elapsed from $start to $end
     */
    public static function elapsedTime($end, $start)
    {
        return sprintf("%.4f sec.", abs($end - $start));
    }

    /**
     *  Returns 256 spaces
     *
     *  PHP_SAPI for fcgi requires a data flush of at least 256
     *  bytes every 40 seconds or else it forces a script halt
     *
     * @return string A series of 256 spaces ' '
     */
    public static function fcgiFlush()
    {
        echo(str_repeat(' ', 256));
        @flush();
    }

    /**
     * Get current microtime as a float.  Method is used for simple profiling
     *
     * @see elapsedTime
     *
     * @return string   A float in the form "msec sec", where sec is the number of seconds since the Unix epoch
     */
    public static function getMicrotime()
    {
        return microtime(true);
    }

    /**
     *  Gets the size of a variable in memory
     *
     *  @param string $var     A valid PHP variable
     *
     *  @return int    The amount of memory the variable has consumed
     */
    public static function getVarSize($var)
    {
        $start_memory = memory_get_usage();
        $var          = unserialize(serialize($var));
        return memory_get_usage() - $start_memory - PHP_INT_SIZE * 8;
    }

    /**
     * Is the string JSON
     *
     * @param string $string Any string blob
     *
     * @return bool Returns true if the string is JSON encoded
     */
    public static function isJSON($string)
    {

        return is_string($string) && is_array(json_decode($string, true)) ? true : false;
    }

    /**
     * Display human readable byte sizes
     *
     * @param string $size  The size in bytes
     *
     * @return string Human readable bytes such as 50MB, 1GB
     */
    public static function readableByteSize($size)
    {
        try {
            $units = array('B', 'KB', 'MB', 'GB', 'TB');
            for ($i = 0; $size >= 1024 && $i < 4; $i++) {
                $size /= 1024;
            }
            return round($size, 2) . $units[$i];
        } catch (Exception $e) {
            return "n/a";
        }
    }

    /**
     * Converts shorthand memory notation value to bytes
     * From http://php.net/manual/en/function.ini-get.php
     *
     * @param $val Memory size shorthand notation string
     *
     * @return int  Returns the numeric byte from 1MB to 1024
     */
    public static function returnBytes($val)
    {
        $val  = trim($val);
        $last = strtolower($val[strlen($val) - 1]);
        $val  = intval($val);
        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
                // no break
            case 'm':
                $val *= 1024;
                // no break
            case 'k':
                $val *= 1024;
                break;
            default:
                $val = null;
        }
        return $val;
    }

    /**
     *  Makes path safe for any OS for PHP
     *
     *  Paths should ALWAYS READ be "/"
     *      uni:  /home/path/file.txt
     *      win:  D:/home/path/file.txt
     *
     *  @param string $path     The path to make safe
     *
     *  @return string The original $path with a with all slashes facing '/'.
     */
    public static function setSafePath($path)
    {
        return str_replace("\\", "/", $path);
    }

    /**
     * Tests a CDN URL to see if it responds
     *
     * @param string $url   The URL to ping
     * @param string $port  The URL port to use
     *
     * @return bool Returns true if the CDN URL is active
     */
    public static function tryCDN($url, $port)
    {
        if ($GLOBALS['FW_USECDN']) {
            return DUPX_HTTP::is_url_active($url, $port);
        } else {
            return false;
        }
    }

    /**
     *  Check PHP version
     *
     *  @param string $version      PHP version we looking for
     *
     *  @return boolean Returns true if version is same or above.
     */
    public static function isVersion($version)
    {
        return (version_compare(PHP_VERSION, $version) >= 0);
    }

    /**
     * Checks if ssl is enabled
     *
     * @return bool
     */
    public static function is_ssl()
    {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] )) {
            return true;
        }

        return false;
    }

    /**
     * @param $url string The URL whichs domain you want to get
     *
     * @return string The domain part of the given URL
     *                  www.myurl.co.uk     => myurl.co.uk
     *                  www.google.com      => google.com
     *                  my.test.myurl.co.uk => myurl.co.uk
     *                  www.myurl.localweb  => myurl.localweb
     */
    public static function getDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (strpos($domain, ".") !== false) {
            if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
                return $regs['domain'];
            } else {
                $exDomain = explode('.', $domain);
                return implode('.', array_slice($exDomain, -2, 2));
            }
        } else {
            return $domain;
        }
    }

    /**
     *
     * @param string $oldSubUrl
     * @param string $oldMainUrl
     *
     * @return string
     */
    public static function getDefaultURL($oldSubUrl, $oldMainUrl)
    {
        $paramsManager    = PrmMng::getInstance();
        $newMainUrl       = $paramsManager->getValue(PrmMng::PARAM_URL_NEW);
        $parsedNewMainUrl = parse_url($newMainUrl);
        $parsedOldMainUrl = parse_url($oldMainUrl);
        $parsedOldSubUrl  = parse_url($oldSubUrl);
        $oldMainDomain    = $parsedOldMainUrl['host'];
        $oldSubDomain     = $parsedOldSubUrl['host'];
        $newMainDomain    = $parsedNewMainUrl['host'];
// PARSE SCHEME
        $resultScheme = isset($parsedNewMainUrl['scheme']) ? $parsedNewMainUrl['scheme'] : 'http';
// PARSE HOST
        if ($oldMainDomain === $oldSubDomain) {
            $resultDomain = $newMainDomain;
        } else {
            $oldNoWwwMainDomain = (strpos($oldMainDomain, 'www.') === 0) ? substr($oldMainDomain, 4) : $oldMainDomain;
            $newNoWwwMainDomain = (strpos($newMainDomain, 'www.') === 0) ? substr($newMainDomain, 4) : $newMainDomain;
            if (($pos                = strrpos($oldSubDomain, $oldNoWwwMainDomain)) === strlen($oldSubDomain) - strlen($oldNoWwwMainDomain)) {
                $subDif       = substr($oldSubDomain, 0, $pos);
                $resultDomain = $subDif . $newNoWwwMainDomain;
            } else {
                // If I can't find a match it is a non-manageable url so I take the value of the old url.
                $resultDomain = $oldSubDomain;
            }
        }

        // PARSE PATH
        $oldMainPath = isset($parsedOldMainUrl['path']) ? $parsedOldMainUrl['path'] : '';
        $oldSubPath  = isset($parsedOldSubUrl['path']) ? $parsedOldSubUrl['path'] : '';
        $newMainPath = isset($parsedNewMainUrl['path']) ? $parsedNewMainUrl['path'] : '';
        if ($oldMainPath === $oldSubPath) {
            $resultPath = $newMainPath;
        } else {
            if (strpos($oldSubPath, $oldMainPath) === 0) {
                $subDif     = substr($oldSubPath, strlen($oldMainPath));
                $resultPath = $newMainPath . '/' . trim($subDif, '/');
            } else {
                // If I can't find a match it is a non-manageable path so I take the value of the old path.
                $resultPath = $oldSubPath;
            }
        }

        if (empty($resultPath) || $resultPath === '/') {
            $resultPath = '';
        }

        return $resultScheme . '://' . $resultDomain . '/' . trim($resultPath, '/');
    }

    /**
     * Get default chunk size in byte
     *
     * @param int $min_chunk_size Min minimum chunk size in bytes
     *
     * @return int An integer chunk size  byte value.
     */
    public static function get_default_chunk_size_in_byte($min_chunk_size = '')
    {

        if (empty($min_chunk_size)) {
            $min_chunk_size = 2 * MB_IN_BYTES;
        } // 2 MB;
        $post_max_size_in_bytes                  = self::get_bytes_from_shorthand(ini_get('post_max_size'));
        $considered_post_max_size_in_bytes       = $post_max_size_in_bytes - KB_IN_BYTES;
        $upload_max_filesize_in_bytes            = self::get_bytes_from_shorthand(ini_get('upload_max_filesize'));
        $considered_upload_max_filesize_in_bytes = $upload_max_filesize_in_bytes - KB_IN_BYTES;
        $memory_limit_in_bytes                   = self::get_bytes_from_shorthand(ini_get('memory_limit'));
        $considered_memory_limit_in_bytes        = $memory_limit_in_bytes - KB_IN_BYTES;
        $chunk_size_in_byte                      = min(
            $considered_post_max_size_in_bytes,
            $considered_upload_max_filesize_in_bytes,
            $considered_memory_limit_in_bytes, // In extraction process, 2 MB is improving speed, so we are using 5MB instead of 10 MB
            $min_chunk_size
        );
        return $chunk_size_in_byte;
    }

    /**
     * Converts a shorthand byte value to an integer byte value.
     *
     * @param string $value A (PHP ini) byte value, either shorthand or ordinary.
     *
     * @return int An integer byte value.
     */
    private static function get_bytes_from_shorthand($value)
    {
        $value = strtolower(trim($value));
        $bytes = (int) $value;
        if (false !== strpos($value, 'g')) {
            $bytes *= GB_IN_BYTES;
        } elseif (false !== strpos($value, 'm')) {
            $bytes *= MB_IN_BYTES;
        } elseif (false !== strpos($value, 'k')) {
            $bytes *= KB_IN_BYTES;
        }

        // For windows 32 bit int max limit
        if ($bytes < 0) {
            return PHP_INT_MAX;
        }

        return min($bytes, PHP_INT_MAX);
// Deal with large (float) values which run into the maximum integer size.
    }

    /**
     * Get default chunk size in KB
     *
     * @param int $min_chunk_size Min minimum chunk size in bytes
     *
     * @return int An integer chunk size KB value.
     */
    public static function get_default_chunk_size_in_kb($min_chunk_size = '')
    {
        if (empty($min_chunk_size)) {
            $min_chunk_size = 10 * MB_IN_BYTES;
        } // 10 MB;

        $chunk_size_in_byte = self::get_default_chunk_size_in_byte($min_chunk_size);
        $chunk_size_in_kb   = floor($chunk_size_in_byte / KB_IN_BYTES);
        return $chunk_size_in_kb;
    }

    /**
     * Get default chunk size in MB
     * Not used now, but for future use
     *
     * @param int $min_chunk_size Min minimum chunk size in bytes
     *
     * @return int An integer chunk size MB value.
     */
    public static function get_default_chunk_size_in_mb($min_chunk_size = '')
    {
        if (empty($min_chunk_size)) {
            $min_chunk_size = 10 * MB_IN_BYTES;
        } // 10 MB;

        $chunk_size_in_byte = self::get_default_chunk_size_in_byte($min_chunk_size);
        $chunk_size_in_mb   = floor($chunk_size_in_byte / MB_IN_BYTES);
        return $chunk_size_in_mb;
    }
    // START ESCAPING AND SANITIZATION

    /**
     * Escaping for HTML blocks.
     *
     * @param string $text
     *
     * @return string
     */
    public static function esc_html($text)
    {
        $safe_text = SnapJson::checkInvalidUTF8($text);
        $safe_text = self::wp_specialchars($safe_text, ENT_QUOTES);
        /**
         * Filters a string cleaned and escaped for output in HTML.
         *
         * Text passed to esc_html() is stripped of invalid or special characters
         * before output.
         *
         * @param string $safe_text The text after it has been escaped.
         * @param string $text      The text prior to being escaped.
         */
        return $safe_text;
    }

    /**
     * Escape single quotes, htmlspecialchar " < > &, and fix line endings.
     *
     * Escapes text strings for echoing in JS. It is intended to be used for inline JS
     * (in a tag attribute, for example onclick="..."). Note that the strings have to
     * be in single quotes. The {@see 'js_escape'} filter is also applied here.
     *
     * @param string $text The text to be escaped.
     *
     * @return string Escaped text.
     */
    public static function esc_js($text)
    {
        $safe_text = SnapJson::checkInvalidUTF8($text);
        $safe_text = self::wp_specialchars($safe_text, ENT_COMPAT);
        $safe_text = preg_replace('/&#(x)?0*(?(1)27|39);?/i', "'", stripslashes($safe_text));
        $safe_text = str_replace("\r", '', $safe_text);
        $safe_text = str_replace("\n", '\\n', addslashes($safe_text));
        /**
         * Filters a string cleaned and escaped for output in JavaScript.
         *
         * Text passed to esc_js() is stripped of invalid or special characters,
         * and properly slashed for output.
         *
         * @param string $safe_text The text after it has been escaped.
         * @param string $text      The text prior to being escaped.
         */
        return $safe_text;
    }

    /**
     * Escaping for HTML attributes.
     *
     * @param string $text
     *
     * @return string
     */
    public static function esc_attr($text)
    {
        $safe_text = SnapJson::checkInvalidUTF8($text);
        $safe_text = self::wp_specialchars($safe_text, ENT_QUOTES);
        /**
         * Filters a string cleaned and escaped for output in an HTML attribute.
         *
         * Text passed to esc_attr() is stripped of invalid or special characters
         * before output.
         *
         * @param string $safe_text The text after it has been escaped.
         * @param string $text      The text prior to being escaped.
         */
        return $safe_text;
    }

    /**
     * Escaping for textarea values.
     *
     * @param string $text
     *
     * @return string
     */
    public static function esc_textarea($text)
    {
        $safe_text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        /**
         * Filters a string cleaned and escaped for output in a textarea element.
         *
         * @param string $safe_text The text after it has been escaped.
         * @param string $text      The text prior to being escaped.
         */
        return $safe_text;
    }

    /**
     * Escape an HTML tag name.
     *
     * @param string $tag_name
     *
     * @return string
     */
    public function tag_escape($tag_name)
    {
        $safe_tag = strtolower(preg_replace('/[^a-zA-Z0-9_:]/', '', $tag_name));
        /**
         * Filters a string cleaned and escaped for output as an HTML tag.
         *
         * @param string $safe_tag The tag name after it has been escaped.
         * @param string $tag_name The text before it was escaped.
         */
        return $safe_tag;
    }

    /**
     * Converts a number of special characters into their HTML entities.
     *
     * Specifically deals with: &, <, >, ", and '.
     *
     * $quote_style can be set to ENT_COMPAT to encode " to
     * &quot;, or ENT_QUOTES to do both. Default is ENT_NOQUOTES where no quotes are encoded.
     *
     * @access private
     *
     * @staticvar string $_charset
     *
     * @param string     $string         The text which is to be encoded.
     * @param int|string $quote_style    Optional. Converts double quotes if set to ENT_COMPAT,
     *                                   both single and double if set to ENT_QUOTES or none if set to ENT_NOQUOTES.
     *                                   Also compatible with old values; converting single quotes if set to 'single',
     *                                   double if set to 'double' or both if otherwise set.
     *                                   Default is ENT_NOQUOTES.
     * @param string     $charset        Optional. The character encoding of the string. Default is false.
     * @param bool       $double_encode  Optional. Whether to encode existing html entities. Default is false.
     *
     * @return string The encoded text with HTML entities.
     */
    public static function wp_specialchars($string, $quote_style = ENT_NOQUOTES, $charset = false, $double_encode = false)
    {
        $string = (string) $string;
        if (0 === strlen($string)) {
            return '';
        }

        // Don't bother if there are no specialchars - saves some processing
        if (!preg_match('/[&<>"\']/', $string)) {
            return $string;
        }

        // Account for the previous behaviour of the function when the $quote_style is not an accepted value
        if (empty($quote_style)) {
            $quote_style = ENT_NOQUOTES;
        } elseif (!in_array($quote_style, array(0, 2, 3, 'single', 'double'), true)) {
            $quote_style = ENT_QUOTES;
        }

        // Store the site charset as a static to avoid multiple calls to wp_load_alloptions()
        if (!$charset) {
            static $_charset = null;
            if (!isset($_charset)) {
                $_charset = '';
            }
            $charset = $_charset;
        }

        if (in_array($charset, array('utf8', 'utf-8', 'UTF8'))) {
            $charset = 'UTF-8';
        }

        $_quote_style = $quote_style;
        if ($quote_style === 'double') {
            $quote_style  = ENT_COMPAT;
            $_quote_style = ENT_COMPAT;
        } elseif ($quote_style === 'single') {
            $quote_style = ENT_NOQUOTES;
        }

        if (!$double_encode) {
// Guarantee every &entity; is valid, convert &garbage; into &amp;garbage;
            // This is required for PHP < 5.4.0 because ENT_HTML401 flag is unavailable.
            $string = self::wp_kses_normalize_entities($string);
        }

        $string = @htmlspecialchars($string, $quote_style, $charset, $double_encode);
// Back-compat.
        if ('single' === $_quote_style) {
            $string = str_replace("'", '&#039;', $string);
        }

        return $string;
    }

    /**
     * Converts a number of HTML entities into their special characters.
     *
     * Specifically deals with: &, <, >, ", and '.
     *
     * $quote_style can be set to ENT_COMPAT to decode " entities,
     * or ENT_QUOTES to do both " and '. Default is ENT_NOQUOTES where no quotes are decoded.
     *
     * @param string     $string The text which is to be decoded.
     * @param string|int $quote_style Optional. Converts double quotes if set to ENT_COMPAT,
     *                                both single and double if set to ENT_QUOTES or
     *                                none if set to ENT_NOQUOTES.
     *                                Also compatible with old _wp_specialchars() values;
     *                                converting single quotes if set to 'single',
     *                                double if set to 'double' or both if otherwise set.
     *                                Default is ENT_NOQUOTES.
     *
     * @return string The decoded text without HTML entities.
     */
    public static function wp_specialchars_decode($string, $quote_style = ENT_NOQUOTES)
    {
        $string = (string) $string;
        if (0 === strlen($string)) {
            return '';
        }

        // Don't bother if there are no entities - saves a lot of processing
        if (strpos($string, '&') === false) {
            return $string;
        }

        // Match the previous behaviour of _wp_specialchars() when the $quote_style is not an accepted value
        if (empty($quote_style)) {
            $quote_style = ENT_NOQUOTES;
        } elseif (!in_array($quote_style, array(0, 2, 3, 'single', 'double'), true)) {
            $quote_style = ENT_QUOTES;
        }

        // More complete than get_html_translation_table( HTML_SPECIALCHARS )
        $single      = array('&#039;' => '\'', '&#x27;' => '\'');
        $single_preg = array('/&#0*39;/' => '&#039;', '/&#x0*27;/i' => '&#x27;');
        $double      = array('&quot;' => '"', '&#034;' => '"', '&#x22;' => '"');
        $double_preg = array('/&#0*34;/' => '&#034;', '/&#x0*22;/i' => '&#x22;');
        $others      = array('&lt;' => '<', '&#060;' => '<', '&gt;' => '>', '&#062;' => '>', '&amp;' => '&', '&#038;' => '&', '&#x26;' => '&');
        $others_preg = array('/&#0*60;/' => '&#060;', '/&#0*62;/' => '&#062;', '/&#0*38;/' => '&#038;', '/&#x0*26;/i' => '&#x26;');
        if ($quote_style === ENT_QUOTES) {
            $translation      = array_merge($single, $double, $others);
            $translation_preg = array_merge($single_preg, $double_preg, $others_preg);
        } elseif ($quote_style === ENT_COMPAT || $quote_style === 'double') {
            $translation      = array_merge($double, $others);
            $translation_preg = array_merge($double_preg, $others_preg);
        } elseif ($quote_style === 'single') {
            $translation      = array_merge($single, $others);
            $translation_preg = array_merge($single_preg, $others_preg);
        } elseif ($quote_style === ENT_NOQUOTES) {
            $translation      = $others;
            $translation_preg = $others_preg;
        }

        // Remove zero padding on numeric entities
        $string = preg_replace(array_keys($translation_preg), array_values($translation_preg), $string);
// Replace characters according to translation table
        return strtr($string, $translation);
    }

    /**
     * Perform a deep string replace operation to ensure the values in $search are no longer present
     *
     * Repeats the replacement operation until it no longer replaces anything so as to remove "nested" values
     * e.g. $subject = '%0%0%0DDD', $search ='%0D', $result ='' rather than the '%0%0DD' that
     * str_replace would return
     *
     * @access private
     *
     * @param string|array $search  The value being searched for, otherwise known as the needle.
     *                              An array may be used to designate multiple needles.
     * @param string       $subject The string being searched and replaced on, otherwise known as the haystack.
     *
     * @return string The string with the replaced svalues.
     */
    private static function deep_replace($search, $subject)
    {
        $subject = (string) $subject;
        $count   = 1;
        while ($count) {
            $subject = str_replace($search, '', $subject, $count);
        }

        return $subject;
    }

    /**
     * Converts and fixes HTML entities.
     *
     * This function normalizes HTML entities. It will convert `AT&T` to the correct
     * `AT&amp;T`, `&#00058;` to `&#58;`, `&#XYZZY;` to `&amp;#XYZZY;` and so on.
     *
     * @param string $string Content to normalize entities
     *
     * @return string Content with normalized entities
     */
    public static function wp_kses_normalize_entities($string)
    {
        // Disarm all entities by converting & to &amp;
        $string = str_replace('&', '&amp;', $string);
// Change back the allowed entities in our entity whitelist
        $string = preg_replace_callback('/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', array(__CLASS__, 'wp_kses_named_entities'), $string);
        $string = preg_replace_callback('/&amp;#(0*[0-9]{1,7});/', array(__CLASS__, 'wp_kses_normalize_entities2'), $string);
        $string = preg_replace_callback('/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', array(__CLASS__, 'wp_kses_normalize_entities3'), $string);
        return $string;
    }

    /**
     * Callback for wp_kses_normalize_entities() regular expression.
     *
     * This function only accepts valid named entity references, which are finite,
     * case-sensitive, and highly scrutinized by HTML and XML validators.
     *
     * @global array $allowedentitynames
     *
     * @param array $matches preg_replace_callback() matches array
     *
     * @return string Correctly encoded entity
     */
    public static function wp_kses_named_entities($matches)
    {
        if (empty($matches[1])) {
            return '';
        }

        $allowedentitynames = array(
            'nbsp', 'iexcl', 'cent', 'pound', 'curren', 'yen',
            'brvbar', 'sect', 'uml', 'copy', 'ordf', 'laquo',
            'not', 'shy', 'reg', 'macr', 'deg', 'plusmn',
            'acute', 'micro', 'para', 'middot', 'cedil', 'ordm',
            'raquo', 'iquest', 'Agrave', 'Aacute', 'Acirc', 'Atilde',
            'Auml', 'Aring', 'AElig', 'Ccedil', 'Egrave', 'Eacute',
            'Ecirc', 'Euml', 'Igrave', 'Iacute', 'Icirc', 'Iuml',
            'ETH', 'Ntilde', 'Ograve', 'Oacute', 'Ocirc', 'Otilde',
            'Ouml', 'times', 'Oslash', 'Ugrave', 'Uacute', 'Ucirc',
            'Uuml', 'Yacute', 'THORN', 'szlig', 'agrave', 'aacute',
            'acirc', 'atilde', 'auml', 'aring', 'aelig', 'ccedil',
            'egrave', 'eacute', 'ecirc', 'euml', 'igrave', 'iacute',
            'icirc', 'iuml', 'eth', 'ntilde', 'ograve', 'oacute',
            'ocirc', 'otilde', 'ouml', 'divide', 'oslash', 'ugrave',
            'uacute', 'ucirc', 'uuml', 'yacute', 'thorn', 'yuml',
            'quot', 'amp', 'lt', 'gt', 'apos', 'OElig',
            'oelig', 'Scaron', 'scaron', 'Yuml', 'circ', 'tilde',
            'ensp', 'emsp', 'thinsp', 'zwnj', 'zwj', 'lrm',
            'rlm', 'ndash', 'mdash', 'lsquo', 'rsquo', 'sbquo',
            'ldquo', 'rdquo', 'bdquo', 'dagger', 'Dagger', 'permil',
            'lsaquo', 'rsaquo', 'euro', 'fnof', 'Alpha', 'Beta',
            'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta',
            'Iota', 'Kappa', 'Lambda', 'Mu', 'Nu', 'Xi',
            'Omicron', 'Pi', 'Rho', 'Sigma', 'Tau', 'Upsilon',
            'Phi', 'Chi', 'Psi', 'Omega', 'alpha', 'beta',
            'gamma', 'delta', 'epsilon', 'zeta', 'eta', 'theta',
            'iota', 'kappa', 'lambda', 'mu', 'nu', 'xi',
            'omicron', 'pi', 'rho', 'sigmaf', 'sigma', 'tau',
            'upsilon', 'phi', 'chi', 'psi', 'omega', 'thetasym',
            'upsih', 'piv', 'bull', 'hellip', 'prime', 'Prime',
            'oline', 'frasl', 'weierp', 'image', 'real', 'trade',
            'alefsym', 'larr', 'uarr', 'rarr', 'darr', 'harr',
            'crarr', 'lArr', 'uArr', 'rArr', 'dArr', 'hArr',
            'forall', 'part', 'exist', 'empty', 'nabla', 'isin',
            'notin', 'ni', 'prod', 'sum', 'minus', 'lowast',
            'radic', 'prop', 'infin', 'ang', 'and', 'or',
            'cap', 'cup', 'int', 'sim', 'cong', 'asymp',
            'ne', 'equiv', 'le', 'ge', 'sub', 'sup',
            'nsub', 'sube', 'supe', 'oplus', 'otimes', 'perp',
            'sdot', 'lceil', 'rceil', 'lfloor', 'rfloor', 'lang',
            'rang', 'loz', 'spades', 'clubs', 'hearts', 'diams',
            'sup1', 'sup2', 'sup3', 'frac14', 'frac12', 'frac34',
            'there4',
        );
        $i                  = $matches[1];
        return (!in_array($i, $allowedentitynames) ) ? "&amp;$i;" : "&$i;";
    }

    /**
     * Helper function to determine if a Unicode value is valid.
     *
     * @param int $i Unicode value
     *
     * @return bool True if the value was a valid Unicode number
     */
    public static function wp_valid_unicode($i)
    {
        return ( $i == 0x9 || $i == 0xa || $i == 0xd ||
            ($i >= 0x20 && $i <= 0xd7ff) ||
            ($i >= 0xe000 && $i <= 0xfffd) ||
            ($i >= 0x10000 && $i <= 0x10ffff) );
    }

    /**
     * Callback for wp_kses_normalize_entities() regular expression.
     *
     * This function helps wp_kses_normalize_entities() to only accept 16-bit
     * values and nothing more for `&#number;` entities.
     *
     * @access private
     *
     * @param array $matches preg_replace_callback() matches array
     *
     * @return string Correctly encoded entity
     */
    public static function wp_kses_normalize_entities2($matches)
    {
        if (empty($matches[1])) {
            return '';
        }

        $i = $matches[1];
        if (self::wp_valid_unicode($i)) {
            $i = str_pad(ltrim($i, '0'), 3, '0', STR_PAD_LEFT);
            $i = "&#$i;";
        } else {
            $i = "&amp;#$i;";
        }

        return $i;
    }

    /**
     * Callback for wp_kses_normalize_entities() for regular expression.
     *
     * This function helps wp_kses_normalize_entities() to only accept valid Unicode
     * numeric entities in hex form.
     *
     * @access private
     *
     * @param array $matches preg_replace_callback() matches array
     *
     * @return string Correctly encoded entity
     */
    public static function wp_kses_normalize_entities3($matches)
    {
        if (empty($matches[1])) {
            return '';
        }

        $hexchars = $matches[1];
        return (!self::wp_valid_unicode(hexdec($hexchars)) ) ? "&amp;#x$hexchars;" : '&#x' . ltrim($hexchars, '0') . ';';
    }

    /**
     * Retrieve a list of protocols to allow in HTML attributes.
     *
     * @since 3.3.0
     * @since 4.3.0 Added 'webcal' to the protocols array.
     * @since 4.7.0 Added 'urn' to the protocols array.
     *
     * @see wp_kses()
     * @see esc_url()
     *
     * @staticvar array $protocols
     *
     * @return array Array of allowed protocols. Defaults to an array containing 'http', 'https',
     *               'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet',
     *               'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal', and 'urn'.
     */
    public static function wp_allowed_protocols()
    {
        static $protocols = array();
        if (empty($protocols)) {
            $protocols = array('http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn');
        }

        return $protocols;
    }

    /**
     * Checks and cleans a URL.
     *
     * A number of characters are removed from the URL. If the URL is for displaying
     * (the default behaviour) ampersands are also replaced. The {@see 'clean_url'} filter
     * is applied to the returned cleaned URL.
     *
     * @since 2.8.0
     *
     * @param string $url       The URL to be cleaned.
     * @param array  $protocols Optional. An array of acceptable protocols.
     *                          Defaults to return value of wp_allowed_protocols()
     * @param string $_context  Private. Use esc_url_raw() for database usage.
     *
     * @return string The cleaned $url after the {@see 'clean_url'} filter is applied.
     */
    public static function esc_url($url, $protocols = null, $_context = 'display')
    {
        $original_url = $url;
        if ('' == $url) {
            return $url;
        }

        $url = str_replace(' ', '%20', $url);
        $url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\[\]\\x80-\\xff]|i', '', $url);
        if ('' === $url) {
            return $url;
        }

        if (0 !== stripos($url, 'mailto:')) {
            $strip = array('%0d', '%0a', '%0D', '%0A');
            $url   = self::deep_replace($strip, $url);
        }

        $url = str_replace(';//', '://', $url);
        /* If the URL doesn't appear to contain a scheme, we
         * presume it needs http:// prepended (unless a relative
         * link starting with /, # or ? or a php file).
         */
        if (
            strpos($url, ':') === false && !in_array($url[0], array('/', '#', '?')) &&
            !preg_match('/^[a-z0-9-]+?\.php/i', $url)
        ) {
            $url = 'http://' . $url;
        }
// Replace ampersands and single quotes only when displaying.
        if ('display' == $_context) {
            $url = self::wp_kses_normalize_entities($url);
            $url = str_replace('&amp;', '&#038;', $url);
            $url = str_replace("'", '&#039;', $url);
        }

        if (( false !== strpos($url, '[') ) || ( false !== strpos($url, ']') )) {
            $parsed = wp_parse_url($url);
            $front  = '';
            if (isset($parsed['scheme'])) {
                $front .= $parsed['scheme'] . '://';
            } elseif ('/' === $url[0]) {
                $front .= '//';
            }

            if (isset($parsed['user'])) {
                $front .= $parsed['user'];
            }

            if (isset($parsed['pass'])) {
                $front .= ':' . $parsed['pass'];
            }

            if (isset($parsed['user']) || isset($parsed['pass'])) {
                $front .= '@';
            }

            if (isset($parsed['host'])) {
                $front .= $parsed['host'];
            }

            if (isset($parsed['port'])) {
                $front .= ':' . $parsed['port'];
            }

            $end_dirty = str_replace($front, '', $url);
            $end_clean = str_replace(array('[', ']'), array('%5B', '%5D'), $end_dirty);
            $url       = str_replace($end_dirty, $end_clean, $url);
        }

        if ('/' === $url[0]) {
            $good_protocol_url = $url;
        } else {
            if (!is_array($protocols)) {
                $protocols = self::wp_allowed_protocols();
            }
            $good_protocol_url = self::wp_kses_bad_protocol($url, $protocols);
            if (strtolower($good_protocol_url) != strtolower($url)) {
                return '';
            }
        }

        return $good_protocol_url;
    }

    /**
     * Removes any invalid control characters in $string.
     *
     * Also removes any instance of the '\0' string.
     *
     * @param string $string
     * @param array $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
     *
     * @return string
     */
    public static function wp_kses_no_null($string, $options = null)
    {
        if (!isset($options['slash_zero'])) {
            $options = array('slash_zero' => 'remove');
        }

        $string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string);
        if ('remove' == $options['slash_zero']) {
            $string = preg_replace('/\\\\+0+/', '', $string);
        }

        return $string;
    }

    /**
     * Sanitize string from bad protocols.
     *
     * This function removes all non-allowed protocols from the beginning of
     * $string. It ignores whitespace and the case of the letters, and it does
     * understand HTML entities. It does its work in a while loop, so it won't be
     * fooled by a string like "javascript:javascript:alert(57)".
     *
     * @param string $string            Content to filter bad protocols from
     * @param array  $allowed_protocols Allowed protocols to keep
     *
     * @return string Filtered content
     */
    public static function wp_kses_bad_protocol($string, $allowed_protocols)
    {
        $string     = self::wp_kses_no_null($string);
        $iterations = 0;
        do {
            $original_string = $string;
            $string          = self::wp_kses_bad_protocol_once($string, $allowed_protocols);
        } while ($original_string != $string && ++$iterations < 6);
        if ($original_string != $string) {
            return '';
        }

        return $string;
    }

    /**
     * Sanitizes content from bad protocols and other characters.
     *
     * This function searches for URL protocols at the beginning of $string, while
     * handling whitespace and HTML entities.
     *
     * @param string $string            Content to check for bad protocols
     * @param string $allowed_protocols Allowed protocols
     *
     * @return string Sanitized content
     */
    public static function wp_kses_bad_protocol_once($string, $allowed_protocols, $count = 1)
    {
        $string2 = preg_split('/:|&#0*58;|&#x0*3a;/i', $string, 2);
        if (isset($string2[1]) && !preg_match('%/\?%', $string2[0])) {
            $string   = trim($string2[1]);
            $protocol = self::wp_kses_bad_protocol_once2($string2[0], $allowed_protocols);
            if ('feed:' == $protocol) {
                if ($count > 2) {
                    return '';
                }
                $string = wp_kses_bad_protocol_once($string, $allowed_protocols, ++$count);
                if (empty($string)) {
                    return $string;
                }
            }
            $string = $protocol . $string;
        }

        return $string;
    }

    /**
     * Convert all entities to their character counterparts.
     *
     * This function decodes numeric HTML entities (`&#65;` and `&#x41;`).
     * It doesn't do anything with other entities like &auml;, but we don't
     * need them in the URL protocol whitelisting system anyway.
     *
     * @param string $string Content to change entities
     *
     * @return string Content after decoded entities
     */
    public static function wp_kses_decode_entities($string)
    {
        $string = preg_replace_callback('/&#([0-9]+);/', array(__CLASS__, 'wp_kses_decode_entities_chr'), $string);
        $string = preg_replace_callback('/&#[Xx]([0-9A-Fa-f]+);/', array(__CLASS__, 'wp_kses_decode_entities_chr_hexdec'), $string);
        return $string;
    }

    /**
     * Regex callback for wp_kses_decode_entities()
     *
     * @param array $match preg match
     *
     * @return string
     */
    public static function wp_kses_decode_entities_chr($match)
    {
        return chr($match[1]);
    }

    /**
     * Regex callback for wp_kses_decode_entities()
     *
     * @param array $match preg match
     *
     * @return string
     */
    public static function wp_kses_decode_entities_chr_hexdec($match)
    {
        return chr(hexdec($match[1]));
    }

    /**
     * Callback for wp_kses_bad_protocol_once() regular expression.
     *
     * This function processes URL protocols, checks to see if they're in the
     * white-list or not, and returns different data depending on the answer
     *
     * @param string $string            URI scheme to check against the whitelist
     * @param string $allowed_protocols Allowed protocols
     *
     * @return string Sanitized content
     */
    public static function wp_kses_bad_protocol_once2($string, $allowed_protocols)
    {
        $string2 = self::wp_kses_decode_entities($string);
        $string2 = preg_replace('/\s/', '', $string2);
        $string2 = self::wp_kses_no_null($string2);
        $string2 = strtolower($string2);
        $allowed = false;
        foreach ((array) $allowed_protocols as $one_protocol) {
            if (strtolower($one_protocol) == $string2) {
                $allowed = true;
                break;
            }
        }

        if ($allowed) {
            return "$string2:";
        } else {
            return '';
        }
    }

    /**
     * Performs esc_url() for database usage.
     *
     * @param string $url       The URL to be cleaned.
     * @param array  $protocols An array of acceptable protocols.
     *
     * @return string The cleaned URL.
     */
    public static function esc_url_raw($url, $protocols = null)
    {
        return self::esc_url($url, $protocols, 'db');
    }

    /**
     * Toggle maintenance mode for the site.
     *
     * Creates/deletes the maintenance file to enable/disable maintenance mode.
     *
     * @param bool $enable True to enable maintenance mode, false to disable.
     *
     * @return void
     */
    public static function maintenanceMode($enable = false)
    {
        $homePath = SnapIO::safePathTrailingslashit(PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW));
        if (!is_writable($homePath)) {
            Log::info('CAN\'T ' . ($enable ? 'SET' : 'REMOVE') . ' MAINTENANCE MODE, ROOT FOLDER NOT WRITABLE');
            return;
        }

        $maintenanceFile = $homePath . '.maintenance';
        $indexFile       = $homePath . 'index.html';

        if (file_exists($indexFile)) {
            $indexContent = file_get_contents($indexFile);
            $manageIndex  = (strpos($indexContent, self::MAINTENANCE_INDEX_MARKER) !== false);
        } else {
            $manageIndex = true;
        }

        if ($enable) {
            Log::info('MAINTENANCE MODE ENABLE');
            if (file_put_contents($maintenanceFile, '<?php $upgrading = ' . time() . '; ?>') == false) {
                Log::info('CAN\'T SET MAINTENANCE MODE FILE \"' . $maintenanceFile . "\"");
            }
            if ($manageIndex && SnapIo::copy(DUPX_INIT . '/assets/maintenance.html', $indexFile) == false) {
                Log::info('CAN\'T SET MAINTENANCE INDEX FILE \"' . $indexFile . "\"");
            }
        } else {
            Log::info('MAINTENANCE MODE DISABLE');
            if (file_exists($maintenanceFile) && unlink($maintenanceFile) == false) {
                Log::info('CAN\'T REMOVE MAINTENANCE MODE FILE \"' . $maintenanceFile . "\"");
            }
            if ($manageIndex && file_exists($indexFile) && unlink($indexFile) == false) {
                Log::info('CAN\'T REMOVE MAINTENANCE INDEX FILE \"' . $indexFile . "\"");
            }
        }
    }

    /**
     * Check if string is base64 encoded
     *
     * @param string $str
     *
     * @return boolean|string return false if isn't base64 string or decoded string
     */
    public static function is_base64($str)
    {
        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $str)) {
            return false;
        }

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($str, true);
        if (false === $decoded) {
            return false;
        }

        // Encode the string again
        if (base64_encode($decoded) != $str) {
            return false;
        }

        return $decoded;
    }

    /**
     *
     * @param array $matches
     *
     * @return string
     */
    public static function encodeUtf8CharFromRegexMatch($matches)
    {
        if (empty($matches) || !is_array($matches)) {
            return '';
        } else {
            return json_decode('"' . $matches[0] . '"');
        }
    }

    /**
     * this function escape generic string to prevent security issue.
     * Used to replace string in wp transformer
     *
     * for example
     * abc'" become "abc'\""
     *
     * @param string $str input string
     * @param bool $addQuote if true add " before and after string
     *
     * @return string
     */
    public static function getEscapedGenericString($str, $addQuote = true)
    {
        $result = SnapJson::jsonEncode(trim($str));
        $result = str_replace(array('\/', '$'), array('/', '\\$'), $result);
        $result = preg_replace_callback(
            '/\\\\u[a-fA-F0-9]{4}/m',
            array(__CLASS__, 'encodeUtf8CharFromRegexMatch'),
            $result
        );
        if (!$addQuote) {
            $result = substr($result, 1, strlen($result) - 2);
        }
        return $result;
    }
}
