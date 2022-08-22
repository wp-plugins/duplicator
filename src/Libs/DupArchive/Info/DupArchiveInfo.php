<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\Info;

class DupArchiveInfo
{
    public $archiveHeader;
    public $fileHeaders;
    public $directoryHeaders;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->fileHeaders      = array();
        $this->directoryHeaders = array();
    }
}
