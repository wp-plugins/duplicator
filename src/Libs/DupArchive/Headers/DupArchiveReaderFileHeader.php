<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Exception;

/**
 * File header
 */
class DupArchiveReaderFileHeader
{
    public $fileSize           = 0;
    public $mtime              = 0;
    public $permissions        = '';
    public $hash               = '';
    public $relativePathLength = 0;
    public $relativePath       = '';

    /**
     * Class constructor
     */
    protected function __construct()
    {
        // Prevent direct instantiation
    }


    /**
     * Read header form archive
     * delta = 84-22 = 62 bytes per file -> 20000 files -> 1.2MB larger
     * <F><FS>x</FS><MT>x</<MT><FP>x</FP><HA>x</HA><RFPL>x</RFPL><RFP>x</RFP></F>
     * # F#x#x#x#x#x#x!
     *
     * @param resource $archiveHandle archive resource
     * @param boolean  $skipContents  if true skip contents
     * @param boolean  $skipMarker    if true skip marker
     *
     * @return static
     */
    public static function readFromArchive($archiveHandle, $skipContents = false, $skipMarker = false)
    {
        // RSR TODO Read header from archive handle and populate members
        // TODO: return null if end of archive or throw exception if can read something but its not a file header

        $instance = new static();

        if (!$skipMarker) {
            $marker = @fread($archiveHandle, 3);

            if ($marker === false) {
                if (feof($archiveHandle)) {
                    return false;
                } else {
                    throw new Exception('Error reading file header');
                }
            }

            if ($marker != '<F>') {
                throw new Exception("Invalid file header marker found [{$marker}] : location " . ftell($archiveHandle));
            }
        }

        $instance->fileSize           = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'FS');
        $instance->mtime              = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'MT');
        $instance->permissions        = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'P');
        $instance->hash               = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'HA');
        $instance->relativePathLength = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'RPL');

        // Skip <RP>
        fread($archiveHandle, 4);
        $instance->relativePath = fread($archiveHandle, $instance->relativePathLength);

        // Skip </RP>
        // fread($archiveHandle, 5);

        // Skip the </F>
        // fread($archiveHandle, 4);

        // Skip the </RP> and the </F>
        fread($archiveHandle, 9);

        if ($skipContents && ($instance->fileSize > 0)) {
            $dataSize  = 0;
            $moreGlobs = true;
            while ($moreGlobs) {
                $globHeader = DupArchiveReaderGlobHeader::readFromArchive($archiveHandle, true);
                $dataSize  += $globHeader->originalSize;
                $moreGlobs  = ($dataSize < $instance->fileSize);
            }
        }

        return $instance;
    }
}
