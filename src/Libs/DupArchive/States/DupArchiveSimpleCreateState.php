<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\States;

/**
 * Simple create state
 */
class DupArchiveSimpleCreateState extends DupArchiveCreateState
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->currentDirectoryIndex = 0;
        $this->currentFileIndex      = 0;
        $this->currentFileOffset     = 0;
    }

    /**
     * Save state
     *
     * @return void
     */
    public function save()
    {
    }
}
