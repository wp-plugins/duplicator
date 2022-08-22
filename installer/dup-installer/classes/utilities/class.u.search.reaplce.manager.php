<?php

/**
 * Search and reaplace manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;

/**
 * Search and replace manager
 * singleton class
 */
final class DUPX_S_R_MANAGER
{
    const GLOBAL_SCOPE_KEY = '___!GLOBAL!___!SCOPE!___';

    /**
     *
     * @var DUPX_S_R_MANAGER
     */
    private static $instance = null;

    /**
     * full list items not sorted
     * @var DUPX_S_R_ITEM[]
     */
    private $items = array();

    /**
     * items sorted by priority and scope
     * [
     *      10 => [
     *             '___!GLOBAL!___!SCOPE!___' => [
     *                  DUPX_S_R_ITEM
     *                  DUPX_S_R_ITEM
     *                  DUPX_S_R_ITEM
     *              ],
     *              'scope_one' => [
     *                  DUPX_S_R_ITEM
     *                  DUPX_S_R_ITEM
     *              ]
     *          ],
     *      20 => [
     *          .
     *          .
     *          .
     *      ]
     * ]
     *
     * @var array
     */
    private $prorityScopeItems = array();

    /**
     *
     * @return DUPX_S_R_MANAGER
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
    }

    /**
     *
     * @return array
     */
    public function getArrayData()
    {
        $data = array();

        foreach ($this->items as $item) {
            $data[] = $item->toArray();
        }

        return $data;
    }

    /**
     *
     * @param array $data
     */
    public function setFromArrayData($data)
    {

        foreach ($data as $itemArray) {
            $new_item = DUPX_S_R_ITEM::getItemFromArray($itemArray);
            $this->setNewItem($new_item);
        }
    }

    /**
     *
     * @param string $search
     * @param string $replace
     * @param string $type                  // item type DUPX_S_R_ITEM::[TYPE_STRING|TYPE_URL|TYPE_URL_NORMALIZE_DOMAIN|TYPE_PATH]
     * @param int $prority                  // lower first
     * @param bool|string|string[] $scope   // true = global scope | false = never | string signle scope | string[] scope list
     *
     * @return boolean|DUPX_S_R_ITEM        // false if fail or new DUPX_S_R_ITEM
     */
    public function addItem($search, $replace, $type = DUPX_S_R_ITEM::TYPE_STRING, $prority = 10, $scope = true)
    {
        $search  = (string) $search;
        $replace = (string) $replace;

        if (strlen($search) == 0 || $search === $replace) {
            return false;
        }

        if (is_bool($scope)) {
            $scope = $scope ? self::GLOBAL_SCOPE_KEY : '';
        }

        if (is_array($scope)) {
            $scopeStr = implode(',', $scope);
            $scopeStr = (strlen($scopeStr) > 50 ? substr($scopeStr, 0, 50) . "..." : $scopeStr);
        } else {
            $scopeStr = 'ALL';
        }

        Log::info(
            "SEARCH ITEM[T:" . str_pad($type, 5) . "|P:" . str_pad($prority, 2) . "]" .
            " SEARCH: " . $search .
            " REPLACE: " . $replace . " [SCOPE: " . $scopeStr . "]"
        );

        $new_item = new DUPX_S_R_ITEM($search, $replace, $type, $prority, $scope);

        return $this->setNewItem($new_item);
    }

    /**
     *
     * @param DUPX_S_R_ITEM $new_item
     *
     * @return boolean|DUPX_S_R_ITEM        // false if fail or new DUPX_S_R_ITEM
     */
    private function setNewItem($new_item)
    {
        $this->items[$new_item->getId()] = $new_item;

        // create priority array
        if (!isset($this->prorityScopeItems[$new_item->prority])) {
            $this->prorityScopeItems[$new_item->prority] = array();

            // sort by priority
            ksort($this->prorityScopeItems);
        }

        // create scope list
        foreach ($new_item->scope as $scope) {
            if (!isset($this->prorityScopeItems[$new_item->prority][$scope])) {
                $this->prorityScopeItems[$new_item->prority][$scope] = array();
            }
            $this->prorityScopeItems[$new_item->prority][$scope][] = $new_item;
        }

        return $new_item;
    }

    /**
     * get all search and reaple items by scpoe
     *
     * @param null|string $scope if scope is empty get only global scope
     * @return DUPX_S_R_ITEM[]
     */
    private function getSearchReplaceItems($scope = null, $globalScope = true)
    {
        $items_list = array();
        foreach ($this->prorityScopeItems as $priority => $priority_list) {
            // get scope list
            if (!empty($scope) && isset($priority_list[$scope])) {
                foreach ($priority_list[$scope] as $item) {
                    $items_list[] = $item;
                }
            }

            // get global scope
            if ($globalScope && isset($priority_list[self::GLOBAL_SCOPE_KEY])) {
                foreach ($priority_list[self::GLOBAL_SCOPE_KEY] as $item) {
                    $items_list[] = $item;
                }
            }
        }

        return $items_list;
    }

    /**
     * get replace list by scope
     * result
     * [
     *      ['search' => ...,'replace' => ...]
     *      ['search' => ...,'replace' => ...]
     * ]
     *
     * @param null|string $scope if scope is empty get only global scope
     * @param bool $unique_search If true it eliminates the double searches leaving the one with lower priority.
     *
     * @return array
     */
    public function getSearchReplaceList($scope = null, $unique_search = true, $globalScope = true)
    {
        Log::info('-- SEARCH LIST -- SCOPE: ' . Log::v2str($scope), Log::LV_DEBUG);

        $items_list = $this->getSearchReplaceItems($scope, $globalScope);
        if (Log::isLevel(Log::LV_HARD_DEBUG)) {
            Log::info('-- SEARCH LIST ITEMS --' . "\n" . print_r($items_list, true), Log::LV_HARD_DEBUG);
        }

        if ($unique_search) {
            $items_list = self::uniqueSearchListItem($items_list);
            if (Log::isLevel(Log::LV_HARD_DEBUG)) {
                Log::info('-- UNIQUE LIST ITEMS --' . "\n" . print_r($items_list, true), Log::LV_HARD_DEBUG);
            }
        }

        Log::info('--- BASE STRINGS ---');
        foreach ($items_list as $index => $item) {
            Log::info(
                'SEARCH[' . str_pad($item->type, 5, ' ', STR_PAD_RIGHT) . ']' . str_pad($index + 1, 3, ' ', STR_PAD_LEFT) . ":" .
                str_pad(Log::v2str($item->search) . " ", 50, '=', STR_PAD_RIGHT) .
                "=> " .
                Log::v2str($item->replace)
            );
        }

        $result = array();

        foreach ($items_list as $item) {
            $result = array_merge($result, $item->getPairsSearchReplace());
        }

        // remove empty search strings
        $result = array_filter($result, function ($val) {
            if (!empty($val['search'])) {
                return true;
            } else {
                Log::info('Empty search string remove, replace: ' . Log::v2str($val['replace']), Log::LV_DETAILED);
                return false;
            }
        });

        if (Log::isLevel(Log::LV_DEBUG)) {
            Log::info('--- REXEXES ---');
            foreach ($result as $index => $c_sr) {
                Log::info(
                    'SEARCH' . str_pad($index + 1, 3, ' ', STR_PAD_LEFT) . ":" .
                    str_pad(Log::v2str($c_sr['search']) . " ", 50, '=', STR_PAD_RIGHT) .
                    "=> " .
                    Log::v2str($c_sr['replace'])
                );
            }
        }

        return $result;
    }

    /**
     * remove duplicated search strings.
     * Leave the object at lower priority
     *
     * @param DUPX_S_R_ITEM[] $list
     * @return boolean|DUPX_S_R_ITEM[]
     */
    private static function uniqueSearchListItem($list)
    {
        $search_strings = array();
        $result         = array();

        if (!is_array($list)) {
            return false;
        }

        foreach ($list as $item) {
            if (!in_array($item->search, $search_strings)) {
                $result[]         = $item;
                $search_strings[] = $item->search;
            }
        }

        return $result;
    }

    private function __clone()
    {
    }
}

/**
 * search and replace item use in manager to creat the search and replace list.
 */
class DUPX_S_R_ITEM
{
    const PATH_SEPARATOR_REGEX_NORMAL   = '[\/\\\\]';
    const PATH_SEPARATOR_REGEX_JSON     = '(?:\\\\\/|\\\\\\\\)';
    const PATH_END_REGEX_MATCH_NORMAL   = '([\/\\\\"\'\n\r]|$)';
    const PATH_END_REGEX_MATCH_JSON     = '(\\\\\/|\\\\\\\\|["\'\n\r]|$)';
    const URL_END_REGEX_MATCH_NORMAL    = '([\/?"\'\n\r]|$)';
    const URL_END_REGEX_MATCH_JSON      = '(\\\\\/|[?"\'\n\r]|$)';
    const URL_END_REGEX_MATCH_URLENCODE = '(%2F|%3F|["\'\n\r]|$)';

    private static $uniqueIdCount = 0;

    const TYPE_STRING               = 'str';
    const TYPE_URL                  = 'url';
    const TYPE_URL_NORMALIZE_DOMAIN = 'urlnd';
    const TYPE_PATH                 = 'path';

    /**
     *
     * @var int
     */
    private $id = 0;

    /**
     *
     * @var int prority lower first
     */
    public $prority = 10;

    /**
     *
     * @var string[] scope list
     */
    public $scope = array();

    /**
     *
     * @var string type of string
     */
    public $type = self::TYPE_STRING;

    /**
     *
     * @var string search string
     */
    public $search = '';

    /**
     *
     * @var string replace string
     */
    public $replace = '';

    /**
     *
     * @param string $search
     * @param string $replace
     * @param string $type
     * @param int $prority
     * @param string|string[] $scope if empty never used
     */
    public function __construct($search, $replace, $type = DUPX_S_R_ITEM::TYPE_STRING, $prority = 10, $scope = array())
    {
        if (!is_array($scope)) {
            $this->scope = empty($scope) ? array() : array((string) $scope);
        } else {
            $this->scope = $scope;
        }
        $this->prority = (int) $prority;
        switch ($type) {
            case DUPX_S_R_ITEM::TYPE_URL:
            case DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN:
                $this->search  = rtrim($search, '/');
                $this->replace = rtrim($replace, '/');
                break;
            case DUPX_S_R_ITEM::TYPE_PATH:
                $this->search  = SnapIO::safePathUntrailingslashit($search);
                $this->replace = SnapIO::safePathUntrailingslashit($replace);
                break;
            case DUPX_S_R_ITEM::TYPE_STRING:
            default:
                $this->search  = (string) $search;
                $this->replace = (string) $replace;
                break;
        }
        $this->type = $type;
        $this->id   = self::$uniqueIdCount;
        self::$uniqueIdCount++;
    }

    public function toArray()
    {
        return array(
            'id'      => $this->id,
            'prority' => $this->prority,
            'scope'   => $this->scope,
            'type'    => $this->type,
            'search'  => $this->search,
            'replace' => $this->replace
        );
    }

    public static function getItemFromArray($array)
    {
        $result = new self($array['search'], $array['replace'], $array['type'], $array['prority'], $array['scope']);
        return $result;
    }

    /**
     * return search an replace string
     *
     * result
     * [
     *      ['search' => ...,'replace' => ...]
     *      ['search' => ...,'replace' => ...]
     * ]
     *
     * @return array
     */
    public function getPairsSearchReplace()
    {
        switch ($this->type) {
            case self::TYPE_URL:
                return self::searchReplaceUrl($this->search, $this->replace);
            case self::TYPE_URL_NORMALIZE_DOMAIN:
                return self::searchReplaceUrl($this->search, $this->replace, true, true);
            case self::TYPE_PATH:
                return self::searchReplacePath($this->search, $this->replace);
            case self::TYPE_STRING:
            default:
                return self::searchReplaceWithEncodings($this->search, $this->replace);
        }
    }

    /**
     * Get search and replace strings with encodings
     * prevents unnecessary substitution like when search and reaplace are the same.
     *
     * result
     * [
     *      ['search' => ...,'replace' => ...]
     *      ['search' => ...,'replace' => ...]
     * ]
     *
     * @param string $search
     * @param string $replace
     * @param bool $json add json encode string
     * @param bool $urlencode add urlencode string
     *
     * @return array pairs search and replace
     */
    public static function searchReplaceWithEncodings($search, $replace, $json = true, $urlencode = true)
    {
        $result = array();
        if ($search != $replace) {
            $result[] = array(
                'search'  => '/' . preg_quote($search, '/') . '/m',
                'replace' => addcslashes($replace, '\\$')
            );
        } else {
            return array();
        }

        // JSON ENCODE
        if ($json) {
            $search_json  = SnapJson::getJsonWithoutQuotes($search);
            $replace_json = SnapJson::getJsonWithoutQuotes($replace);

            if ($search != $search_json && $search_json != $replace_json) {
                $result[] = array(
                    'search'  => '/' . preg_quote($search_json, '/') . '/m',
                    'replace' => addcslashes($replace_json, '\\$')
                );
            }
        }

        // URL ENCODE
        if ($urlencode) {
            $search_urlencode  = urlencode($search);
            $replace_urlencode = urlencode($replace);

            if ($search != $search_urlencode && $search_urlencode != $replace_urlencode) {
                $result[] = array(
                    'search'  => '/' . preg_quote($search_urlencode, '/') . '/m',
                    'replace' => addcslashes($replace_urlencode, '\\$')
                );
            }
        }

        return $result;
    }

    /**
     * Add replace strings to substitute old url to new url
     * 1) no protocol old url to no protocol new url (es. //www.hold.url  => //www.new.url)
     * 2) wrong protocol new url to right protocol new url (es. http://www.new.url => https://www.new.url)
     *
     * result
     * [
     *      ['search' => ...,'replace' => ...]
     *      ['search' => ...,'replace' => ...]
     * ]
     *
     * @param string $search_url
     * @param string $replace_url
     * @param bool $force_new_protocol if true force http or https protocol (work only if replace url have http or https scheme)
     *
     * @return array
     */
    public static function searchReplaceUrl($search_url, $replace_url, $force_new_protocol = true, $normalizeWww = false)
    {
        $result = array();

        if (($parse_search_url = parse_url($search_url)) !== false && isset($parse_search_url['scheme'])) {
            $search_url_raw = substr($search_url, strlen($parse_search_url['scheme']) + 1);
        } else {
            $search_url_raw = $search_url;
        }
        $search_url_raw = trim($search_url_raw, '/');

        if (($parse_replace_url = parse_url($replace_url)) !== false && isset($parse_replace_url['scheme'])) {
            $replace_url_raw = substr($replace_url, strlen($parse_replace_url['scheme']) + 1);
        } else {
            $replace_url_raw = $replace_url;
        }
        $replace_url_raw = trim($replace_url_raw, '/');

        // (?<!https:|http:)\/\/(?:www\.|)aaaa\.it([?\/'"]|$)
        if ($normalizeWww && self::domainCanNormalized($search_url)) {
            if (self::isWww($search_url_raw)) {
                $baseSearchUrl = substr($search_url_raw, strlen('www.'));
            } else {
                $baseSearchUrl = $search_url_raw;
            }

            $regExSearchUrlNormal = '\/\/(?:www\.)?' . preg_quote($baseSearchUrl, '/');
            $regExSearchUrlJson   = '\\\\\/\\\\\/(?:www\.)?' . preg_quote(SnapJson::getJsonWithoutQuotes($baseSearchUrl), '/');
            $regExSearchUrlEncode = '%2F%2F(?:www\.)?' . preg_quote(urlencode($baseSearchUrl), '/');
            //'/https?:\/\/(?:www\.|)aaaa\.it(?<end>[?\/\'"]|$)/m'
            //$searchRawRegEx = '/(?<!https:|http:)\/\/(?:www\.|)'.preg_quote($baseSearchUrl, '/').'([?\/\'"]|$)/m';
        } else {
            $regExSearchUrlNormal = '\/\/' . preg_quote($search_url_raw, '/');
            $regExSearchUrlJson   = '\\\\\/\\\\\/' . preg_quote(SnapJson::getJsonWithoutQuotes($search_url_raw), '/');
            $regExSearchUrlEncode = '%2F%2F' . preg_quote(urlencode($search_url_raw), '/');
        }

        // NORMALIZE source protocol
        if ($force_new_protocol && $parse_replace_url !== false && isset($parse_replace_url['scheme'])) {
            $result[] = array(
                'search'  => '/(?<!https:|http:)' . $regExSearchUrlNormal . self::URL_END_REGEX_MATCH_NORMAL . '/m',
                'replace' => addcslashes('//' . $replace_url_raw, '\\$') . '$1'
            );

            $result[] = array(
                'search'  => '/https?:' . $regExSearchUrlNormal . self::URL_END_REGEX_MATCH_NORMAL . '/m',
                'replace' => addcslashes($replace_url, '\\$') . '$1'
            );

            $result[] = array(
                'search'  => '/(?<!https:|http:)' . $regExSearchUrlJson . self::URL_END_REGEX_MATCH_JSON . '/m',
                'replace' => addcslashes(SnapJson::getJsonWithoutQuotes('//' . $replace_url_raw), '\\$') . '$1'
            );

            $result[] = array(
                'search'  => '/https?:' . $regExSearchUrlJson . self::URL_END_REGEX_MATCH_JSON . '/m',
                'replace' => addcslashes(SnapJson::getJsonWithoutQuotes($replace_url), '\\$') . '$1'
            );

            $result[] = array(
                'search'  => '/(?<!https%3A|http%3A)' . $regExSearchUrlEncode . self::URL_END_REGEX_MATCH_URLENCODE . '/m',
                'replace' => addcslashes(urlencode('//' . $replace_url_raw), '\\$') . '$1'
            );

            $result[] = array(
                'search'  => '/https?%3A' . $regExSearchUrlEncode . self::URL_END_REGEX_MATCH_URLENCODE . '/m',
                'replace' => addcslashes(urlencode($replace_url), '\\$') . '$1'
            );
        } else {
            $result[] = array(
                'search'  => '/' . $regExSearchUrlNormal . self::URL_END_REGEX_MATCH_NORMAL . '/m',
                'replace' => addcslashes('//' . $replace_url_raw, '\\$') . '$1'
            );

            $result[] = array(
                'search'  => '/' . $regExSearchUrlJson . self::URL_END_REGEX_MATCH_JSON . '/m',
                'replace' => addcslashes(SnapJson::getJsonWithoutQuotes('//' . $replace_url_raw), '\\$') . '$1'
            );

            $result[] = array(
                'search'  => '/' . $regExSearchUrlEncode . self::URL_END_REGEX_MATCH_URLENCODE . '/m',
                'replace' => addcslashes(urlencode('//' . $replace_url_raw), '\\$') . '$1'
            );
        }

        return $result;
    }

    /**
     * result
     * [
     *      ['search' => ...,'replace' => ...]
     *      ['search' => ...,'replace' => ...]
     * ]
     *
     * @param string $search_path
     * @param string $replace_path
     *
     * @return array
     */
    public static function searchReplacePath($search_path, $replace_path)
    {
        $result = array();
        if ($search_path == $replace_path) {
            return $result;
        }

        $explodeSearch = explode('/', $search_path);

        $normaSearchArray = array_map(function ($val) {
            return preg_quote(SnapJson::getJsonWithoutQuotes($val), '/');
        }, $explodeSearch);
        $normalPathSearch = '/' . implode(self::PATH_SEPARATOR_REGEX_NORMAL, $normaSearchArray) . self::PATH_END_REGEX_MATCH_NORMAL . '/m';
        $result[]         = array('search' => $normalPathSearch, 'replace' => addcslashes($replace_path, '\\$') . '$1');

        $jsonSearchArray = array_map(function ($val) {
            return preg_quote(SnapJson::getJsonWithoutQuotes($val), '/');
        }, $explodeSearch);
        $jsonPathSearch  = '/' . implode(self::PATH_SEPARATOR_REGEX_JSON, $jsonSearchArray) . self::PATH_END_REGEX_MATCH_JSON . '/m';
        $result[]        = array('search' => $jsonPathSearch, 'replace' => addcslashes(SnapJson::getJsonWithoutQuotes($replace_path), '\\$') . '$1');

        return $result;
    }

    /**
     * get unique item id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $url string The URL whichs domain you want to get
     * @return string The domain part of the given URL
     *                  www.myurl.co.uk     => myurl.co.uk
     *                  www.google.com      => google.com
     *                  my.test.myurl.co.uk => myurl.co.uk
     *                  www.myurl.localweb  => myurl.localweb
     *
     */
    public static function getDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        $regs   = null;
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

    public static function domainCanNormalized($url)
    {
        $pieces = parse_url($url);

        if (!isset($pieces['host'])) {
            return false;
        }

        if (strpos($pieces['host'], ".") === false) {
            return false;
        }

        $dLevels = explode('.', $pieces['host']);
        if ($dLevels[0] == 'www') {
            return true;
        }

        switch (count($dLevels)) {
            case 1:
                return false;
            case 2:
                return true;
            case 3:
                if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $pieces['host'], $regs)) {
                    return $regs['domain'] == $pieces['host'];
                }
                return false;
            default:
                return false;
        }
    }

    public static function isWww($url)
    {
        $pieces = parse_url($url);
        if (!isset($pieces['host'])) {
            return false;
        } else {
            return strpos($pieces['host'], 'www.') === 0;
        }
    }
}
