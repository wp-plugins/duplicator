<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\States;

use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveHeader;

/**
 * Dup archive expand state
 */
abstract class DupArchiveExpandState extends DupArchiveStateBase
{
    const VALIDATION_NONE     = 0;
    const VALIDATION_STANDARD = 1;
    const VALIDATION_FULL     = 2;

    /** @var DupArchiveHeader */
    public $archiveHeader = null;
    /** @var DupArchiveFileHeader */
    public $currentFileHeader        = null;
    public $validateOnly             = false;
    public $validationType           = self::VALIDATION_STANDARD;
    public $fileWriteCount           = 0;
    public $directoryWriteCount      = 0;
    public $expectedFileCount        = -1;
    public $expectedDirectoryCount   = -1;
    public $filteredDirectories      = array();
    public $excludedDirWithoutChilds = array();
    public $filteredFiles            = array();
    /** @var string[] relative path list to inclue files, overwrite filters */
    public $includedFiles = array();
    /** @var string[] relativePath => fullNewPath */
    public $fileRenames           = array();
    public $directoryModeOverride = -1;
    public $fileModeOverride      = -1;
    public $lastHeaderOffset      = -1;

    /**
     * Reset state for file
     *
     * @return void
     */
    public function resetForFile()
    {
        $this->currentFileHeader = null;
        $this->currentFileOffset = 0;
    }

    /**
     * save expand state
     *
     * @return void
     */
    abstract public function save();
}
