<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Exception;

// Format
class DupArchiveDirectoryHeader extends DupArchiveReaderDirectoryHeader
{
    public $mtime              = 0;
    public $permissions        = '';
    public $relativePathLength = 1;
    public $relativePath       = '';

    /**
     * Write header in archive
     *
     * @param resource $archiveHandle archive resource
     *
     * @return int bytes written
     */
    public function writeToArchive($archiveHandle)
    {
        if ($this->relativePathLength == 0) {
            // Don't allow a base path to be written to the archive
            return;
        }

        $headerString = '<D><MT>' .
            $this->mtime . '</MT><P>' .
            $this->permissions . '</P><RPL>' .
            $this->relativePathLength . '</RPL><RP>' .
            $this->relativePath . '</RP></D>';

        //SnapIO::fwrite($archiveHandle, $headerString);
        $bytes_written = @fwrite($archiveHandle, $headerString);

        if ($bytes_written === false) {
            throw new Exception('Error writing to file.');
        } else {
            return $bytes_written;
        }
    }
}
