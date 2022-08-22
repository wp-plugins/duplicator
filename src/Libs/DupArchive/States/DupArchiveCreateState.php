<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\States;

/**
 * Dup archive create state
 */
abstract class DupArchiveCreateState extends DupArchiveStateBase
{
    const DEFAULT_GLOB_SIZE = 1048576;

    public $basepathLength        = 0;
    public $currentDirectoryIndex = -1;
    public $currentFileIndex      = -1;
    public $globSize              = self::DEFAULT_GLOB_SIZE;
    public $newBasePath           = null;
    public $skippedFileCount      = 0;
    public $skippedDirectoryCount = 0;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * State save
     *
     * @return void
     */
    abstract public function save();
}
