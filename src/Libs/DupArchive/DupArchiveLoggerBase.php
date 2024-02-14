<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive;

abstract class DupArchiveLoggerBase
{
    /**
     * Log function
     *
     * @param string        $s                       string to log
     * @param boolean       $flush                   if true flish log
     * @param callback|null $callingFunctionOverride call back function
     *
     * @return void
     */
    abstract public function log($s, $flush = false, $callingFunctionOverride = null);
}
