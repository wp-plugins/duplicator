<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Exception;

/**
 * Dup archive glob header
 *
 * Format
 * #C#{$originalSize}#{$storedSize}!
 */
class DupArchiveReaderGlobHeader
{
    //  public $marker;
    public $originalSize;
    public $storedSize;
    public $hash;

    /**
     * Class constructor
     */
    public function __construct()
    {
    }

    /**
     * Read chunk file header from archive
     *
     * @param resource $archiveHandle archive file resource
     * @param bool     $skipGlob      if true skip glob content
     *
     * @return static
     */
    public static function readFromArchive($archiveHandle, $skipGlob = false)
    {
        $instance     = new static();
        $startElement = fread($archiveHandle, 3);

        //if ($marker != '?G#') {
        if ($startElement !== '<G>') {
            throw new Exception("Invalid glob header marker found {$startElement}. location:" . ftell($archiveHandle));
        }

        $instance->originalSize = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'OS');
        $instance->storedSize   = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'SS');
        $instance->hash         = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'HA');

        // Skip the </G>
        fread($archiveHandle, 4);

        if ($skipGlob) {
            if (fseek($archiveHandle, $instance->storedSize, SEEK_CUR) === -1) {
                throw new Exception("Can't fseek when skipping glob at location:" . ftell($archiveHandle));
            }
        }

        return $instance;
    }

    /**
     * Get glob content from header
     *
     * @param resource $archiveHandle archive hadler
     * @param self     $header        chunk glob header
     * @param bool     $isCompressed  true if is compressed
     *
     * @return string
     */
    public static function readContent($archiveHandle, self $header, $isCompressed)
    {
        if ($header->storedSize == 0) {
            return 0;
        }

        if (($globContents = fread($archiveHandle, $header->storedSize)) === false) {
            throw new Exception("Error reading glob content");
        }

        return ($isCompressed ? gzinflate($globContents) : $globContents);
    }
}
