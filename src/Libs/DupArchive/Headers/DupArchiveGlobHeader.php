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
class DupArchiveGlobHeader extends DupArchiveReaderGlobHeader
{
    //  public $marker;
    public $originalSize;
    public $storedSize;
    public $hash;

    /**
     * Write header in archive
     *
     * @param resource $archiveHandle archive file resource
     *
     * @return int
     */
    public function writeToArchive($archiveHandle)
    {
        // <G><OS>x</OS>x<SS>x</SS><HA>x</HA></G>

        $headerString = '<G><OS>' . $this->originalSize . '</OS><SS>' . $this->storedSize . '</SS><HA>' . $this->hash . '</HA></G>';

        //SnapIO::fwrite($archiveHandle, $headerString);
        $bytes_written = @fwrite($archiveHandle, $headerString);

        if ($bytes_written === false) {
            throw new Exception('Error writing to file.');
        } else {
            return $bytes_written;
        }
    }
}
