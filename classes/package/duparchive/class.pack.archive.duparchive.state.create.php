<?php

use Duplicator\Libs\DupArchive\States\DupArchiveCreateState;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUP_DupArchive_Create_State extends DupArchiveCreateState
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->throttleDelayInUs = 10;
    }

    // Only one active package so straightforward
   // public static function createFromPackage(&$package)
    public static function get_instance()
    {
        $instance = new DUP_DupArchive_Create_State();
        $data     = DUP_Settings::Get('duparchive_create_state');
        DUP_Util::objectCopy($data, $instance);
        $instance->startTimestamp = time();
        DUP_Log::TraceObject("retrieving create state", $instance);
        return $instance;
    }

    public static function createNew($archivePath, $basePath, $timeSliceInSecs, $isCompressed, $setArchiveOffsetToEndOfArchive)
    {
        $instance = new DUP_DupArchive_Create_State();
        if ($setArchiveOffsetToEndOfArchive) {
            $instance->archiveOffset = filesize($archivePath);
        } else {
            $instance->archiveOffset = 0;
        }

        $instance->archivePath           = $archivePath;
        $instance->basePath              = $basePath;
        $instance->currentDirectoryIndex = 0;
        $instance->currentFileOffset     = 0;
        $instance->currentFileIndex      = 0;
        $instance->failures              = array();
        $instance->globSize              = DupArchiveCreateState::DEFAULT_GLOB_SIZE;
        $instance->isCompressed          = $isCompressed;
        $instance->timeSliceInSecs       = $timeSliceInSecs;
        $instance->working               = true;
        $instance->skippedDirectoryCount = 0;
        $instance->skippedFileCount      = 0;
        $instance->startTimestamp        = time();
        return $instance;
    }

    public function addFailure($type, $subject, $description, $isCritical = false)
    {
        parent::addFailure($type, $subject, $description, $isCritical);
    }

    public function save()
    {
        DUP_Log::TraceObject("Saving create state", $this);
        DUP_Settings::Set('duparchive_create_state', $this);
        DUP_Settings::Save();
    }
}
