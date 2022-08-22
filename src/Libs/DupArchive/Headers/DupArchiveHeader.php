<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Duplicator\Libs\DupArchive\DupArchiveEngine;
use Duplicator\Libs\Snap\SnapIO;
use Exception;

/**
 * Dup archive header
 *
 * Format: #A#{version:5}#{isCompressed}!
 */
class DupArchiveHeader extends DupArchiveReaderHeader
{
    /** @var string */
    protected $version;
    /** @var bool */
    public $isCompressed;

    /**
     * Create new header
     *
     * @param bool $isCompressed true if is compressed
     *
     * @return self
     */
    public static function create($isCompressed)
    {
        $instance               = new self();
        $instance->version      = DupArchiveEngine::DUPARCHIVE_VERSION;
        $instance->isCompressed = $isCompressed;
        return $instance;
    }

    /**
     * Write header to archive
     *
     * @param resource $archiveHandle archive resource
     *
     * @return void
     */
    public function writeToArchive($archiveHandle)
    {
        SnapIO::fwrite($archiveHandle, '<A><V>' . $this->version . '</V><C>' . ($this->isCompressed ? 'true' : 'false') . '</C></A>');
    }
}
