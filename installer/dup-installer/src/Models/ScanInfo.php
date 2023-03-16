<?php

/**
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Installer\Models;

use DUPX_Package;
use Exception;

/**
 * Package scan info
 */
final class ScanInfo
{
    /** @var array */
    private $data = array();
    /** @var self */
    private static $instance = null;

    /**
     * Get instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Singleton class constructor
     */
    private function __construct()
    {
        $scanFile = DUPX_Package::getScanJsonPath();
        if (!file_exists($scanFile)) {
            throw new Exception("Archive file $scanFile doesn't exist");
        }

        if (($contents = file_get_contents($scanFile)) === false) {
            throw new Exception("Can\'t read Archive file $scanFile");
        }

        if (($this->data = json_decode($contents, true)) === null) {
            throw new Exception("Can\'t decode archive json");
        }
    }

    /**
     * Get uncompressed size, -1 unknown
     *
     * @return int
     */
    public function getUSize()
    {
        return isset($this->data['ARC']['Usize']) ? $this->data['ARC']['Usize'] : -1;
    }

    /**
     * Return true if package has filtered core folders
     *
     * @return bool
     */
    public function hasFilteredCoreFolders()
    {
        return $this->data['ARC']['Status']['HasFilteredCoreFolders'];
    }
}
