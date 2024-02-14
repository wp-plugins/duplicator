<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Libs\DupArchive\Headers;

use Exception;

class DupArchiveHeaderU
{
    const MAX_FILED_LEN = 128;

    /**
     * Undocumented function
     *
     * @param resource $archiveHandle archvie resource
     * @param int      $ename         header enum
     *
     * @return string
     */
    public static function readStandardHeaderField($archiveHandle, $ename)
    {
        $expectedStart = '<' . $ename . '>';
        $expectedEnd   = '</' . $ename . '>';

        $startingElement = fread($archiveHandle, strlen($expectedStart));

        if ($startingElement !== $expectedStart) {
            throw new Exception("Invalid starting element. Was expecting {$expectedStart} but got {$startingElement}");
        }

        $headerString = stream_get_line($archiveHandle, self::MAX_FILED_LEN, $expectedEnd);

        if ($headerString === false) {
            throw new Exception('Error reading line.');
        }

        return $headerString;
    }
}
