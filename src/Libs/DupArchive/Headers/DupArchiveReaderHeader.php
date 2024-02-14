<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Exception;

/**
 * Dup archive read header
 *
 * Format: #A#{version:5}#{isCompressed}!
 */
class DupArchiveReaderHeader
{
    /** @var string */
    protected $version;
    /** @var bool */
    public $isCompressed;

    /**
     * Class Contructor
     */
    protected function __construct()
    {
        // Prevent instantiation
    }

    /**
     * Get header from archive
     *
     * @param resource $archiveHandle archive resource
     *
     * @return static
     */
    public static function readFromArchive($archiveHandle)
    {
        $instance     = new static();
        $startElement = fgets($archiveHandle, 4);
        if ($startElement != '<A>') {
            throw new Exception("Invalid archive header marker found {$startElement}");
        }

        $instance->version      = DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'V');
        $instance->isCompressed = filter_var(DupArchiveHeaderU::readStandardHeaderField($archiveHandle, 'C'), FILTER_VALIDATE_BOOLEAN);

        // Skip the </A>
        fgets($archiveHandle, 5);
        return $instance;
    }
}
