<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive\Processors;

use Duplicator\Libs\DupArchive\DupArchiveEngine;
use Duplicator\Libs\DupArchive\Headers\DupArchiveDirectoryHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveFileHeader;
use Duplicator\Libs\DupArchive\Headers\DupArchiveGlobHeader;
use Duplicator\Libs\DupArchive\Processors\DupArchiveProcessingFailure;
use Duplicator\Libs\DupArchive\States\DupArchiveCreateState;
use Duplicator\Libs\DupArchive\States\DupArchiveExpandState;
use Duplicator\Libs\DupArchive\Utils\DupArchiveUtil;
use Duplicator\Libs\Snap\SnapIO;
use Exception;

/**
 * Dup archive file processor
 */
class DupArchiveFileProcessor
{
    protected static $newFilePathCallback = null;

    /**
     * Set new file callback
     *
     * @param callable $callback callback function
     *
     * @return bool
     */
    public static function setNewFilePathCallback($callback)
    {
        if (!is_callable($callback)) {
            self::$newFilePathCallback = null;
            return false;
        }

        self::$newFilePathCallback = $callback;
        return true;
    }

    /**
     * get file from relatei path
     *
     * @param string $basePath     base path
     * @param string $relativePath relative path
     *
     * @return string
     */
    protected static function getNewFilePath($basePath, $relativePath)
    {
        if (is_null(self::$newFilePathCallback)) {
            return $basePath . '/' . $relativePath;
        } else {
            return call_user_func_array(self::$newFilePathCallback, array($relativePath));
        }
    }

    /**
     * Write file to archive
     *
     * @param DupArchiveCreateState $createState      dup archive create state
     * @param resource              $archiveHandle    archive resource
     * @param string                $sourceFilepath   source file path
     * @param string                $relativeFilePath relative file path
     *
     * @return void
     */
    public static function writeFilePortionToArchive(
        DupArchiveCreateState $createState,
        $archiveHandle,
        $sourceFilepath,
        $relativeFilePath
    ) {
        DupArchiveUtil::tlog("writeFileToArchive for {$sourceFilepath}");

        // switching to straight call for speed
        $sourceHandle = @fopen($sourceFilepath, 'rb');

        if (!is_resource($sourceHandle)) {
            $createState->archiveOffset = SnapIO::ftell($archiveHandle);
            $createState->currentFileIndex++;
            $createState->currentFileOffset = 0;
            $createState->skippedFileCount++;
            $createState->addFailure(DupArchiveProcessingFailure::TYPE_FILE, $sourceFilepath, "Couldn't open $sourceFilepath", false);
            return;
        }

        if ($createState->currentFileOffset > 0) {
            SnapIO::fseek($sourceHandle, $createState->currentFileOffset);
        } else {
            $fileHeader = DupArchiveFileHeader::createFromFile($sourceFilepath, $relativeFilePath);
            $fileHeader->writeToArchive($archiveHandle);
        }

        $sourceFileSize = filesize($sourceFilepath);

        $moreFileDataToProcess = true;

        while ((!$createState->timedOut()) && $moreFileDataToProcess) {
            if ($createState->throttleDelayInUs !== 0) {
                usleep($createState->throttleDelayInUs);
            }

            $moreFileDataToProcess      = self::appendGlobToArchive($createState, $archiveHandle, $sourceHandle, $sourceFilepath, $sourceFileSize);
            $createState->archiveOffset = SnapIO::ftell($archiveHandle);

            if ($moreFileDataToProcess) {
                $createState->currentFileOffset += $createState->globSize;
            } else {
                $createState->currentFileIndex++;
                $createState->currentFileOffset = 0;
            }

            // Only writing state after full group of files have been written - less reliable but more efficient
            // $createState->save();
        }

        SnapIO::fclose($sourceHandle);
    }

    /**
     * Write file to archive from source
     *
     * @param DupArchiveCreateState $createState      dup archive create state
     * @param resource              $archiveHandle    archive resource
     * @param string                $src              source string
     * @param string                $relativeFilePath relative file path
     * @param int                   $forceSize        if 0 size is auto of content is filled of \0 char to size
     *
     * @return void
     */
    public static function writeFileSrcToArchive(
        DupArchiveCreateState $createState,
        $archiveHandle,
        $src,
        $relativeFilePath,
        $forceSize = 0
    ) {
        DupArchiveUtil::tlog("writeFileSrcToArchive");

        $fileHeader = DupArchiveFileHeader::createFromSrc($src, $relativeFilePath, $forceSize);
        $fileHeader->writeToArchive($archiveHandle);

        self::appendFileSrcToArchive($createState, $archiveHandle, $src, $forceSize);
        $createState->currentFileIndex++;
        $createState->currentFileOffset = 0;
        $createState->archiveOffset     = SnapIO::ftell($archiveHandle);
    }

    /**
     * Expand du archive
     *
     * Assumption is that this is called at the beginning of a glob header since file header already writtern
     *
     * @param DupArchiveExpandState $expandState   expand state
     * @param resource              $archiveHandle archive resource
     *
     * @return bool true on success
     */
    public static function writeToFile(DupArchiveExpandState $expandState, $archiveHandle)
    {
        if (isset($expandState->fileRenames[$expandState->currentFileHeader->relativePath])) {
            $destFilepath = $expandState->fileRenames[$expandState->currentFileHeader->relativePath];
        } else {
            $destFilepath = self::getNewFilePath($expandState->basePath, $expandState->currentFileHeader->relativePath);
        }
        $parentDir = dirname($destFilepath);

        $moreGlobstoProcess = true;

        SnapIO::dirWriteCheckOrMkdir($parentDir, 'u+rwx', true);

        if ($expandState->currentFileHeader->fileSize > 0) {
            if ($expandState->currentFileOffset > 0) {
                $destFileHandle = SnapIO::fopen($destFilepath, 'r+b');
                SnapIO::fseek($destFileHandle, $expandState->currentFileOffset);
            } else {
                $destFileHandle = SnapIO::fopen($destFilepath, 'w+b');
            }

            while (!$expandState->timedOut()) {
                $moreGlobstoProcess = $expandState->currentFileOffset < $expandState->currentFileHeader->fileSize;

                if ($moreGlobstoProcess) {
                    if ($expandState->throttleDelayInUs !== 0) {
                        usleep($expandState->throttleDelayInUs);
                    }

                    self::appendGlobToFile($expandState, $archiveHandle, $destFileHandle, $destFilepath);

                    $expandState->currentFileOffset = ftell($destFileHandle);
                    $expandState->archiveOffset     = SnapIO::ftell($archiveHandle);

                    $moreGlobstoProcess = $expandState->currentFileOffset < $expandState->currentFileHeader->fileSize;

                    if (!$moreGlobstoProcess) {
                        break;
                    }
                } else {
                    // rsr todo record fclose error
                    @fclose($destFileHandle);
                    $destFileHandle = null;

                    if ($expandState->validationType == DupArchiveExpandState::VALIDATION_FULL) {
                        self::validateExpandedFile($expandState);
                    }
                    break;
                }
            }

            DupArchiveUtil::tlog('Out of glob loop');

            if ($destFileHandle != null) {
                // rsr todo record file close error
                @fclose($destFileHandle);
                $destFileHandle = null;
            }

            if (!$moreGlobstoProcess && $expandState->validateOnly && ($expandState->validationType == DupArchiveExpandState::VALIDATION_FULL)) {
                if (!is_writable($destFilepath)) {
                    SnapIO::chmod($destFilepath, 'u+rw');
                }
                if (@unlink($destFilepath) === false) {
                    //      $expandState->addFailure(DupArchiveFailureTypes::File, $destFilepath, "Couldn't delete {$destFilepath} during validation", false);
                    // TODO: Have to know how to handle this - want to report it but don’t want to mess up validation -
                    // some non critical errors could be important to validation
                }
            }
        } else {
            // 0 length file so just touch it
            $moreGlobstoProcess = false;

            if (file_exists($destFilepath)) {
                @unlink($destFilepath);
            }

            if (touch($destFilepath) === false) {
                throw new Exception("Couldn't create {$destFilepath}");
            }
        }

        if (!$moreGlobstoProcess) {
            self::setFileMode($expandState, $destFilepath);
            self::setFileTimes($expandState, $destFilepath);
            DupArchiveUtil::tlog('No more globs to process');

            $expandState->fileWriteCount++;
            $expandState->resetForFile();
        }

        return !$moreGlobstoProcess;
    }

    /**
     * Create directory
     *
     * @param DupArchiveExpandState     $expandState     expand state
     * @param DupArchiveDirectoryHeader $directoryHeader directory header
     *
     * @return boolean
     */
    public static function createDirectory(DupArchiveExpandState $expandState, DupArchiveDirectoryHeader $directoryHeader)
    {
        /* @var $expandState DupArchiveExpandState */
        $destDirPath = self::getNewFilePath($expandState->basePath, $directoryHeader->relativePath);

        $mode = $directoryHeader->permissions;

        if ($expandState->directoryModeOverride != -1) {
            $mode = $expandState->directoryModeOverride;
        }

        if (!SnapIO::dirWriteCheckOrMkdir($destDirPath, $mode, true)) {
            $error_message = "Unable to create directory $destDirPath";
            $expandState->addFailure(DupArchiveProcessingFailure::TYPE_DIRECTORY, $directoryHeader->relativePath, $error_message, false);
            DupArchiveUtil::tlog($error_message);
            return false;
        } else {
            return true;
        }
    }

    /**
     * Set file mode if is enabled
     *
     * @param DupArchiveExpandState $expandState dup expand state
     * @param string                $filePath    file path
     *
     * @return bool
     */
    public static function setFileMode(DupArchiveExpandState $expandState, $filePath)
    {
        if ($expandState->fileModeOverride === -1) {
            return;
        }
        return SnapIO::chmod($filePath, $expandState->fileModeOverride);
    }

    /**
     * Set original file times if enabled
     *
     * @param DupArchiveExpandState $expandState dup expand state
     * @param string                $filePath    File path
     *
     * @return bool true if success, false otherwise
     */
    protected static function setFileTimes(DupArchiveExpandState $expandState, $filePath)
    {
        if (!$expandState->keepFileTime) {
            return true;
        }
        if (!file_exists($filePath)) {
            return false;
        }
        return touch($filePath, $expandState->currentFileHeader->mtime);
    }

    /**
     * Validate file entry
     *
     * @param DupArchiveExpandState $expandState   dup expand state
     * @param resource              $archiveHandle dup archive resource
     *
     * @return bool
     */
    public static function standardValidateFileEntry(DupArchiveExpandState $expandState, $archiveHandle)
    {
        $moreGlobstoProcess = $expandState->currentFileOffset < $expandState->currentFileHeader->fileSize;

        if (!$moreGlobstoProcess) {
            // Not a 'real' write but indicates that we actually did fully process a file in the archive
            $expandState->fileWriteCount++;
        } else {
            while ((!$expandState->timedOut()) && $moreGlobstoProcess) {
                // Read in the glob header but leave the pointer at the payload
                $globHeader   = DupArchiveGlobHeader::readFromArchive($archiveHandle, false);
                $globContents = fread($archiveHandle, $globHeader->storedSize);

                if ($globContents === false) {
                    throw new Exception("Error reading glob from archive");
                }

                $hash = hash('crc32b', $globContents);

                if ($hash != $globHeader->hash) {
                    $expandState->addFailure(
                        DupArchiveProcessingFailure::TYPE_FILE,
                        $expandState->currentFileHeader->relativePath,
                        'Hash mismatch on DupArchive file entry',
                        true
                    );
                    DupArchiveUtil::tlog("Glob hash mismatch during standard check of {$expandState->currentFileHeader->relativePath}");
                } else {
                    //    DupArchiveUtil::tlog("Glob MD5 passes");
                }

                $expandState->currentFileOffset += $globHeader->originalSize;
                $expandState->archiveOffset      = SnapIO::ftell($archiveHandle);
                $moreGlobstoProcess              = $expandState->currentFileOffset < $expandState->currentFileHeader->fileSize;

                if (!$moreGlobstoProcess) {
                    $expandState->fileWriteCount++;
                    $expandState->resetForFile();
                }
            }
        }

        return !$moreGlobstoProcess;
    }

    /**
     * Validate file
     *
     * @param DupArchiveExpandState $expandState dup expand state
     *
     * @return void
     */
    private static function validateExpandedFile(DupArchiveExpandState $expandState)
    {
        /* @var $expandState DupArchiveExpandState */
        $destFilepath = self::getNewFilePath($expandState->basePath, $expandState->currentFileHeader->relativePath);

        if ($expandState->currentFileHeader->hash !== '00000000000000000000000000000000') {
            $hash = hash_file('crc32b', $destFilepath);

            if ($hash !== $expandState->currentFileHeader->hash) {
                $expandState->addFailure(DupArchiveProcessingFailure::TYPE_FILE, $destFilepath, "MD5 mismatch for {$destFilepath}", false);
            } else {
                DupArchiveUtil::tlog('MD5 Match for ' . $destFilepath);
            }
        } else {
            DupArchiveUtil::tlog('MD5 non match is 0\'s');
        }
    }

    /**
     * Append file to archive
     *
     * @param DupArchiveCreateState $createState      create state
     * @param resource              $archiveHandle    archive resource
     * @param resource              $sourceFilehandle file resource
     * @param string                $sourceFilepath   file path
     * @param int                   $fileSize         file size
     *
     * @return bool true if more file remaning
     */
    private static function appendGlobToArchive(
        DupArchiveCreateState $createState,
        $archiveHandle,
        $sourceFilehandle,
        $sourceFilepath,
        $fileSize
    ) {
        DupArchiveUtil::tlog("Appending file glob to archive for file {$sourceFilepath} at file offset {$createState->currentFileOffset}");

        if ($fileSize == 0) {
            return false;
        }

        $fileSize    -= $createState->currentFileOffset;
        $globContents = @fread($sourceFilehandle, $createState->globSize);

        if ($globContents === false) {
            throw new Exception("Error reading $sourceFilepath");
        }

        $originalSize = strlen($globContents);

        if ($createState->isCompressed) {
            $globContents = gzdeflate($globContents, 2);    // 2 chosen as best compromise between speed and size
            $storeSize    = strlen($globContents);
        } else {
            $storeSize = $originalSize;
        }

        $globHeader               = new DupArchiveGlobHeader();
        $globHeader->originalSize = $originalSize;
        $globHeader->storedSize   = $storeSize;
        $globHeader->hash         = hash('crc32b', $globContents);
        $globHeader->writeToArchive($archiveHandle);

        if (@fwrite($archiveHandle, $globContents) === false) {
            // Considered fatal since we should always be able to write to the archive -
            // plus the header has already been written (could back this out later though)
            throw new Exception(
                "Error writing $sourceFilepath to archive. Ensure site still hasn't run out of space.",
                DupArchiveEngine::EXCEPTION_FATAL
            );
        }

        $fileSizeRemaining = $fileSize - $createState->globSize;
        $moreFileRemaining = $fileSizeRemaining > 0;

        return $moreFileRemaining;
    }

    /**
     * Append file in dup archvie from source string
     *
     * @param DupArchiveCreateState $createState   create state
     * @param resource              $archiveHandle archive handle
     * @param string                $src           source to add
     * @param int                   $forceSize     if 0 size is auto of content is filled of \0 char to size
     *
     * @return bool
     */
    private static function appendFileSrcToArchive(
        DupArchiveCreateState $createState,
        $archiveHandle,
        $src,
        $forceSize = 0
    ) {
        DupArchiveUtil::tlog("Appending file glob to archive from src");

        if (($originalSize = strlen($src)) == 0 && $forceSize == 0) {
            return false;
        }

        if ($forceSize == 0 && $createState->isCompressed) {
            $src       = gzdeflate($src, 2); // 2 chosen as best compromise between speed and size
            $storeSize = strlen($src);
        } else {
            $storeSize = $originalSize;
        }

        if ($forceSize > 0 && $storeSize < $forceSize) {
            $charsToAdd = $forceSize - $storeSize;
            $src       .= str_repeat("\0", $charsToAdd);
            $storeSize  = $forceSize;
        }

        $globHeader               = new DupArchiveGlobHeader();
        $globHeader->originalSize = $originalSize;
        $globHeader->storedSize   = $storeSize;
        $globHeader->hash         = hash('crc32b', $src);
        $globHeader->writeToArchive($archiveHandle);


        if (SnapIO::fwriteChunked($archiveHandle, $src) === false) {
            // Considered fatal since we should always be able to write to the archive -
            // plus the header has already been written (could back this out later though)
            throw new Exception(
                "Error writing SRC to archive. Ensure site still hasn't run out of space.",
                DupArchiveEngine::EXCEPTION_FATAL
            );
        }

        return true;
    }

    /**
     * Extract file from dup archive
     * Assumption is that archive handle points to a glob header on this call
     *
     * @param DupArchiveExpandState $expandState    dup archive expand state
     * @param resource              $archiveHandle  archvie resource
     * @param resource              $destFileHandle file resource
     * @param string                $destFilePath   file path
     *
     * @return void
     */
    private static function appendGlobToFile(
        DupArchiveExpandState $expandState,
        $archiveHandle,
        $destFileHandle,
        $destFilePath
    ) {
        DupArchiveUtil::tlog('Appending file glob to file ' . $destFilePath . ' at file offset ' . $expandState->currentFileOffset);

        // Read in the glob header but leave the pointer at the payload
        $globHeader = DupArchiveGlobHeader::readFromArchive($archiveHandle, false);
        if (($globContents = DupArchiveGlobHeader::readContent($archiveHandle, $globHeader, $expandState->archiveHeader->isCompressed)) === false) {
            throw new Exception("Error reading glob from $destFilePath");
        }

        if (@fwrite($destFileHandle, $globContents) === false) {
            throw new Exception("Error writing glob to $destFilePath");
        } else {
            DupArchiveUtil::tlog('Successfully wrote glob');
        }
    }
}
