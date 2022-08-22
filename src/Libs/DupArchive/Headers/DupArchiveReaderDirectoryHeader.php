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
 * Class dir header reader
 */
class DupArchiveReaderDirectoryHeader
{
    public $mtime              = 0;
    public $permissions        = '';
    public $relativePathLength = 1;
    public $relativePath       = '';

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * Read folder from archive
     *
     * @param resource $archiveHandle    archive resource
     * @param boolean  $skipStartElement if true sckip start element
     *
     * @return static
     */
    public static function readFromArchive($archiveHandle, $skipStartElement = false)
    {
        $instance = new static();

        if (!$skipStartElement) {
            // <A>
            $startElement = fread($archiveHandle, 3);

            if ($startElement === false) {
                if (feof($archiveHandle)) {
                    return false;
                } else {
                    throw new Exception('Error reading directory header');
                }
            }

            if ($startElement != '<D>') {
                throw new Exception("Invalid directory header marker found [{$startElement}] : location " . ftell($archiveHandle));
            }
        }

        $instance->mtime              = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'MT');
        $instance->permissions        = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'P');
        $instance->relativePathLength = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'RPL');

        // Skip the <RP>
        fread($archiveHandle, 4);

        $instance->relativePath = fread($archiveHandle, $instance->relativePathLength);

        // Skip the </RP>
        // fread($archiveHandle, 5);

        // Skip the </D>
        // fread($archiveHandle, 4);

        // Skip the </RP> and the </D>
        fread($archiveHandle, 9);

        return $instance;
    }
}
