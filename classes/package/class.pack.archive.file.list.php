<?php

/**
 * archive path file list object
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package name
 * @copyright (c) 2019, Snapcreek LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;

class DUP_Archive_File_List
{
    protected $path   = null;
    protected $handle = null;
    protected $cache  = null;

    public function __construct($path)
    {
        if (empty($path)) {
            throw new Exception('path can\'t be empty');
        }

        $this->path = SnapIO::safePath($path);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function getPath()
    {
        return $this->path;
    }

    public function open($truncate = false)
    {
        if (is_null($this->handle)) {
            if (($this->handle = fopen($this->path, 'a+')) === false) {
                DUP_Log::trace('Can\'t open ' . $this->path);
                $this->handle = null;
                return false;
            }
        }
        if ($truncate) {
            $this->emptyFile();
        }
        return true;
    }

    public function emptyFile()
    {
        if (!$this->open(false)) {
            return false;
        }
        if (($res = ftruncate($this->handle, 0)) === false) {
            DUP_Log::trace('Can\'t truncate file ' . $this->path);
            return false;
        }
        return true;
    }

    public function close()
    {
        if (!is_null($this->handle)) {
            if (($res = @fclose($this->handle)) === false) {
                DUP_Log::trace('Can\'t close ' . $this->path);
                return false;
            }
            $this->handle = null;
        }
        return true;
    }

    public function addEntry($path, $size, $nodes)
    {

        if (is_null($this->handle)) { // check to generate less overhead
            if (!$this->open()) {
                return false;
            }
        }
        $entry = array('p' => $path, 's' => $size, 'n' => $nodes);
        fwrite($this->handle, SnapJson::jsonEncode($entry) . "\n");
    }

    /**
     *
     * @param bool $pathOnly if true return only payth
     * @return boolean|array|string return false if is end of filer.
     */
    public function getEntry($pathOnly = false)
    {
        if (is_null($this->handle)) { // check to generate less overhead
            if (!$this->open()) {
                return false;
            }
        }

        if (($json = fgets($this->handle, 4196)) === false) {
            // end of file return false
            return false;
        }

        $result = json_decode($json, true);
        if ($pathOnly) {
            return $result['p'];
        } else {
            return $result;
        }
    }

    protected function cleanCache()
    {
        $this->cache = null;
        return true;
    }

    protected function loadCache($refreshCache = false)
    {
        if ($refreshCache || is_null($this->cache)) {
            if (!$this->open()) {
                return false;
            }
            $this->cache = array();
            if (@fseek($this->handle, 0) === -1) {
                DUP_Log::trace('Can\'t seek at 0 pos for file ' . $this->path);
                $this->cleanCache();
                return false;
            }
            while (($entry = $this->getEntry()) !== false) {
                $this->cache[$entry['p']] = $entry;
            }
            if (!feof($this->handle)) {
                DUP_Log::trace('Error: unexpected fgets() fail', '', false);
            }
        }
        return true;
    }

    public function getEntryFromPath($path, $refreshCache = false)
    {
        if (!$this->loadCache($refreshCache)) {
            return false;
        }

        if (array_key_exists($path, $this->cache)) {
            return $this->cache[$path];
        } else {
            return false;
        }
    }

    public function getEntriesFormPath($path, $refreshCache = false)
    {
        if (!$this->loadCache($refreshCache)) {
            return false;
        }

        if (array_key_exists($path, $this->cache)) {
            $result = array();
            foreach ($this->cache as $current => $entry) {
                if (preg_match('/^' . preg_quote($path, '/') . '\/[^\/]+$/', $current)) {
                    $result[] = $entry;
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    public function getArrayPaths($pathPrefix = '')
    {
        if (!$this->open()) {
            return false;
        }

        $result = array();
        if (@fseek($this->handle, 0) === -1) {
            DUP_Log::trace('Can\'t seek at 0 pos for file ' . $this->path);
            return false;
        }
        $safePrefix = SnapIO::safePathUntrailingslashit($pathPrefix);
        while (($path       = $this->getEntry(true)) !== false) {
            $result[] = $safePrefix . '/' . $path;
        }
        if (!feof($this->handle)) {
            DUP_Log::trace('Error: unexpected fgets() fail', '', false);
        }
        return $result;
    }
}
