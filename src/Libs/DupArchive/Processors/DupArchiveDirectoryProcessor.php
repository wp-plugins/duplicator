<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\Processors;

use Duplicator\Libs\DupArchive\Headers\DupArchiveDirectoryHeader;
use Duplicator\Libs\DupArchive\States\DupArchiveCreateState;
use Duplicator\Libs\Snap\SnapIO;

class DupArchiveDirectoryProcessor
{
    /**
     * Undocumented function
     *
     * @param DupArchiveCreateState $createState           create state
     * @param resource              $archiveHandle         archive resource
     * @param string                $sourceDirectoryPath   source directory path
     * @param string                $relativeDirectoryPath relative dirctory path
     *
     * @return void
     */
    public static function writeDirectoryToArchive(
        DupArchiveCreateState $createState,
        $archiveHandle,
        $sourceDirectoryPath,
        $relativeDirectoryPath
    ) {
        $directoryHeader = new DupArchiveDirectoryHeader();

        $directoryHeader->permissions        = substr(sprintf('%o', fileperms($sourceDirectoryPath)), -4);
        $directoryHeader->mtime              = SnapIO::filemtime($sourceDirectoryPath);
        $directoryHeader->relativePath       = $relativeDirectoryPath;
        $directoryHeader->relativePathLength = strlen($directoryHeader->relativePath);

        $directoryHeader->writeToArchive($archiveHandle);

        // Just increment this here - the actual state save is on the outside after timeout or completion of all directories
        $createState->currentDirectoryIndex++;
    }
}
