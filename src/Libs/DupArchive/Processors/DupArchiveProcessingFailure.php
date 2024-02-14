<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive\Processors;

/**
 *Failure class
 */
class DupArchiveProcessingFailure
{
    const TYPE_UNKNOWN   = 0;
    const TYPE_FILE      = 1;
    const TYPE_DIRECTORY = 2;

    public $type        = self::TYPE_UNKNOWN;
    public $description = '';
    public $subject     = '';
    public $isCritical  = false;
}
