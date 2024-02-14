<?php

namespace Duplicator\Libs\DupArchive\Info;

class DupArchiveExpanderInfo
{
    public $archiveHandle       = null;
    public $currentFileHeader   = null;
    public $destDirectory       = null;
    public $directoryWriteCount = 0;
    public $fileWriteCount      = 0;
    public $isCompressed        = false;
    public $enableWrite         = false;

    /**
     * Get dest path
     *
     * @return string
     */
    public function getCurrentDestFilePath()
    {
        if ($this->destDirectory != null) {
            return "{$this->destDirectory}/{$this->currentFileHeader->relativePath}";
        } else {
            return null;
        }
    }
}
