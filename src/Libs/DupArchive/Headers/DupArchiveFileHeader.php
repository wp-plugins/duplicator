<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Duplicator\Libs\Snap\SnapIO;
use Exception;

/**
 * File header
 */
class DupArchiveFileHeader extends DupArchiveReaderFileHeader
{
    const MAX_SIZE_FOR_HASHING = 1000000000;

    public $fileSize           = 0;
    public $mtime              = 0;
    public $permissions        = '';
    public $hash               = '';
    public $relativePathLength = 0;
    public $relativePath       = '';

    /**
     * create header from file
     *
     * @param string $filepath         file path
     * @param string $relativeFilePath relative file path in archive
     *
     * @return static
     */
    public static function createFromFile($filepath, $relativeFilePath)
    {
        $instance = new static();


        $instance->fileSize    = SnapIO::filesize($filepath);
        $instance->permissions = substr(sprintf('%o', fileperms($filepath)), -4);
        $instance->mtime       = SnapIO::filemtime($filepath);

        if ($instance->fileSize > self::MAX_SIZE_FOR_HASHING) {
            $instance->hash = "00000000000000000000000000000000";
        } else {
            $instance->hash = hash_file('crc32b', $filepath);
        }

        $instance->relativePath       = $relativeFilePath;
        $instance->relativePathLength = strlen($instance->relativePath);

        return $instance;
    }

    /**
     * create header from src
     *
     * @param string $src              source string
     * @param string $relativeFilePath relative path in archvie
     * @param int    $forceSize        if 0 size is auto of content is filled of \0 char to size
     *
     * @return static
     */
    public static function createFromSrc($src, $relativeFilePath, $forceSize = 0)
    {
        $instance = new static();

        $instance->fileSize    = strlen($src);
        $instance->permissions = '0644';
        $instance->mtime       = time();

        $srcLen = strlen($src);

        if ($forceSize > 0 && $srcLen < $forceSize) {
            $charsToAdd = $forceSize - $srcLen;
            $src       .= str_repeat("\0", $charsToAdd);
        }

        if ($instance->fileSize > self::MAX_SIZE_FOR_HASHING) {
            $instance->hash = "00000000000000000000000000000000";
        } else {
            $instance->hash = hash('crc32b', $src);
        }

        $instance->relativePath       = $relativeFilePath;
        $instance->relativePathLength = strlen($instance->relativePath);

        return $instance;
    }

    /**
     * Write header in archive
     *
     * @param resource $archiveHandle archive resource
     *
     * @return int bytes written
     */
    public function writeToArchive($archiveHandle)
    {
        $headerString = '<F><FS>' .
            $this->fileSize . '</FS><MT>' .
            $this->mtime . '</MT><P>' .
            $this->permissions . '</P><HA>' .
            $this->hash . '</HA><RPL>' .
            $this->relativePathLength . '</RPL><RP>' .
            $this->relativePath . '</RP></F>';

        //SnapIO::fwrite($archiveHandle, $headerString);
        $bytes_written = @fwrite($archiveHandle, $headerString);

        if ($bytes_written === false) {
            throw new Exception('Error writing to file.');
        } else {
            return $bytes_written;
        }
    }
}
