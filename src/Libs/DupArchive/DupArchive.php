<?php

namespace Duplicator\Libs\DupArchive;

use Duplicator\Libs\DupArchive\Headers\DupArchiveReaderDirectoryHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveReaderFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveReaderGlobHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveReaderHeader;
use Error;
use Exception;

class DupArchive
{
    const DUPARCHIVE_VERSION  = '1.0.0';
    const INDEX_FILE_NAME     = '__dup__archive__index.json';
    const INDEX_FILE_SIZE     = 2000; // reserver 2K
    const EXTRA_FILES_POS_KEY = 'extraPos';

    const HEADER_TYPE_NONE = 0;
    const HEADER_TYPE_FILE = 1;
    const HEADER_TYPE_DIR  = 2;
    const HEADER_TYPE_GLOB = 3;

    /**
     * Get header type enum
     *
     * @param resource $archiveHandle archive resource
     *
     * @return int
     */
    protected static function getNextHeaderType($archiveHandle)
    {
        $retVal = self::HEADER_TYPE_NONE;
        $marker = fgets($archiveHandle, 4);

        if (feof($archiveHandle) === false) {
            switch ($marker) {
                case '<D>':
                    $retVal = self::HEADER_TYPE_DIR;
                    break;
                case '<F>':
                    $retVal = self::HEADER_TYPE_FILE;
                    break;
                case '<G>':
                    $retVal = self::HEADER_TYPE_GLOB;
                    break;
                default:
                    throw new Exception("Invalid header marker {$marker}. Location:" . ftell($archiveHandle));
            }
        }

        return $retVal;
    }

    /**
     * Get archive index data
     *
     * @param string $archivePath archive path
     *
     * @return bool|array return index data, false if don't exists
     */
    public static function getIndexData($archivePath)
    {
        try {
            $indexContent = self::getSrcFile($archivePath, self::INDEX_FILE_NAME, 0, 3000, false);
            if ($indexContent === false) {
                return false;
            }
            $indexData = json_decode(rtrim($indexContent, "\0"), true);

            if (!is_array($indexData)) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        } catch (Error $e) {
            return false;
        }

        return $indexData;
    }

    /**
     * Get extra files offset if set or 0
     *
     * @param string $archivePath archive path
     *
     * @return int
     */
    public static function getExtraOffset($archivePath)
    {
        if (($indexData = self::getIndexData($archivePath)) === false) {
            return 0;
        }
        return (isset($indexData[self::EXTRA_FILES_POS_KEY]) ? $indexData[self::EXTRA_FILES_POS_KEY] : 0);
    }

    /**
     * Add file in archive from src
     *
     * @param string $archivePath  archive path
     * @param string $relativePath relative path
     * @param int    $offset       start search location
     * @param int    $sizeToSearch max size where search
     *
     * @return bool|int false if file not found of path position
     */
    public static function seachPathInArchive($archivePath, $relativePath, $offset = 0, $sizeToSearch = 0)
    {
        if (($archiveHandle = fopen($archivePath, 'rb')) === false) {
            throw new Exception("Can’t open archive at $archivePath!");
        }
        $result = self::searchPath($archivePath, $relativePath, $offset, $sizeToSearch);
        @fclose($archiveHandle);
        return $result;
    }

    /**
     * Search path, if found set and return position
     *
     * @param resource $archiveHandle dup archive resource
     * @param string   $relativePath  relative path to extract
     * @param int      $offset        start search location
     * @param int      $sizeToSearch  max size where search
     *
     * @return bool|int false if file not found of path position
     */
    public static function searchPath($archiveHandle, $relativePath, $offset = 0, $sizeToSearch = 0)
    {
        if (!is_resource($archiveHandle)) {
            throw new Exception('Archive handle must be a resource');
        }

        if (fseek($archiveHandle, $offset, SEEK_SET) < 0) {
            return false;
        }

        if ($offset == 0) {
            DupArchiveReaderHeader::readFromArchive($archiveHandle);
        }

        $result   = false;
        $position = ftell($archiveHandle);
        $continue = true;

        do {
            switch (($type = self::getNextHeaderType($archiveHandle))) {
                case self::HEADER_TYPE_FILE:
                    $currentFileHeader = DupArchiveReaderFileHeader::readFromArchive($archiveHandle, true, true);
                    if ($currentFileHeader->relativePath == $relativePath) {
                        $continue = false;
                        $result   = $position;
                    }
                    break;
                case self::HEADER_TYPE_DIR:
                    $directoryHeader = DupArchiveReaderDirectoryHeader::readFromArchive($archiveHandle, true);
                    if ($directoryHeader->relativePath == $relativePath) {
                        $continue = false;
                        $result   = $position;
                    }
                    break;
                case self::HEADER_TYPE_NONE:
                    $continue = false;
                    break;
                default:
                    throw new Exception('Invali header type "' . $type . '"');
            }
            $position = ftell($archiveHandle);
            if ($sizeToSearch > 0 && ($position - $offset) >= $sizeToSearch) {
                break;
            }
        } while ($continue);

        if ($result !== false) {
            if (fseek($archiveHandle, $result, SEEK_SET) < 0) {
                return false;
            }
        }
        return $result;
    }

    /**
     * Get file content
     *
     * @param string $archivePath  archvie path
     * @param string $relativePath relative path to extract
     * @param int    $offset       start search location
     * @param int    $sizeToSearch max size where search
     * @param bool   $isCompressed true if is compressed
     *
     * @return bool|string false if file not found
     */
    public static function getSrcFile($archivePath, $relativePath, $offset = 0, $sizeToSearch = 0, $isCompressed = null)
    {
        if (($archiveHandle = fopen($archivePath, 'rb')) === false) {
            throw new Exception("Can’t open archive at $archivePath!");
        }
        $archiveHeader = DupArchiveReaderHeader::readFromArchive($archiveHandle);
        if (is_null($isCompressed)) {
            $isCompressed = $archiveHeader->isCompressed;
        }

        if (self::searchPath($archiveHandle, $relativePath, $offset, $sizeToSearch) === false) {
            return false;
        }

        if (self::getNextHeaderType($archiveHandle) != self::HEADER_TYPE_FILE) {
            return false;
        }

        $header = DupArchiveReaderFileHeader::readFromArchive($archiveHandle, false, true);
        $result = self::getSrcFromHeader($archiveHandle, $header, $isCompressed);
        @fclose($archiveHandle);
        return $result;
    }

    /**
     * Get src file form header
     *
     * @param resource                   $archiveHandle archive handle
     * @param DupArchiveReaderFileHeader $fileHeader    file header
     * @param bool                       $isCompressed  true if is compressed
     *
     * @return string
     */
    protected static function getSrcFromHeader($archiveHandle, DupArchiveReaderFileHeader $fileHeader, $isCompressed)
    {
        if ($fileHeader->fileSize == 0) {
            return '';
        }
        $dataSize = 0;
        $result   = '';

        do {
            $globHeader = DupArchiveReaderGlobHeader::readFromArchive($archiveHandle);
            $result    .= DupArchiveReaderGlobHeader::readContent($archiveHandle, $globHeader, $isCompressed);
            $dataSize  += $globHeader->originalSize;
        } while ($dataSize < $fileHeader->fileSize);

        return $result;
    }

    /**
     * Skip file in archive
     *
     * @param resource             $archiveHandle dup archive resource
     * @param DupArchiveFileHeader $fileHeader    file header
     *
     * @return void
     */
    protected static function skipFileInArchive($archiveHandle, DupArchiveReaderFileHeader $fileHeader)
    {
        if ($fileHeader->fileSize == 0) {
            return;
        }
        $dataSize = 0;

        do {
            $globHeader = DupArchiveReaderGlobHeader::readFromArchive($archiveHandle, true);
            $dataSize  += $globHeader->originalSize;
        } while ($dataSize < $fileHeader->fileSize);
    }

    /**
     * Assumes we are on one header and just need to get to the next
     *
     * @param resource $archiveHandle dup archive resource
     *
     * @return void
     */
    protected static function skipToNextHeader($archiveHandle)
    {
        $headerType = self::getNextHeaderType($archiveHandle);
        switch ($headerType) {
            case self::HEADER_TYPE_FILE:
                $fileHeader = DupArchiveReaderFileHeader::readFromArchive($archiveHandle, false, true);
                self::skipFileInArchive($archiveHandle, $fileHeader);
                break;
            case self::HEADER_TYPE_DIR:
                DupArchiveReaderDirectoryHeader::readFromArchive($archiveHandle, true);
                break;
            case self::HEADER_TYPE_NONE:
                false;
        }
    }
}
