<?php

/**
 * Dup archvie expand state
 *
 * @package   Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 */

namespace Duplicator\Installer\Core\Deploy\DupArchive;

use Duplicator\Libs\DupArchive\States\DupArchiveExpandState;
use Duplicator\Libs\DupArchive\Utils\DupArchiveUtil;
use Duplicator\Libs\Snap\SnapIO;
use stdClass;

class DawsExpandState extends DupArchiveExpandState
{
    protected static $instance = null;

    const STATE_FILE = 'expandstate.json';

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->initMembers();
    }

    /**
     * Remove state file
     *
     * @return bool
     */
    public static function purgeStatefile()
    {
        $stateFilepath = dirname(__FILE__) . '/' . self::STATE_FILE;
        if (!file_exists($stateFilepath)) {
            return true;
        }
        return SnapIO::rm($stateFilepath, false);
    }

    /**
     *
     * @param boolean $reset reset state
     *
     * @return self
     */
    public static function getInstance($reset = false)
    {
        if ((self::$instance == null) && (!$reset)) {
            $stateFilepath = dirname(__FILE__) . '/' . self::STATE_FILE;

            self::$instance = new self();

            if (file_exists($stateFilepath)) {
                $stateHandle = SnapIO::fopen($stateFilepath, 'rb');

                // RSR we shouldn't need read locks and it seems to screw up on some boxes anyway.. SnapIO::flock($stateHandle, LOCK_EX);
                $stateString = fread($stateHandle, filesize($stateFilepath));
                $data        = json_decode($stateString, false);
                self::$instance->setFromData($data);
                self::$instance->fileRenames = (array) (self::$instance->fileRenames);

                //     SnapIO::flock($stateHandle, LOCK_UN);
                SnapIO::fclose($stateHandle);
            } else {
                $reset = true;
            }
        }

        if ($reset) {
            self::$instance = new self();
            self::$instance->reset();
        }

        return self::$instance;
    }

    /**
     * Init state from data
     *
     * @param stdClass $data data
     *
     * @return void
     */
    private function setFromData($data)
    {
        foreach ($data as $key => $val) {
            if (!property_exists($this, $key)) {
                continue;
            }
            $this->{$key} = $val;
        }
    }

    /**
     * Reset state
     *
     * @return void
     */
    public function reset()
    {
        $stateFilepath = dirname(__FILE__) . '/' . self::STATE_FILE;
        $stateHandle   = SnapIO::fopen($stateFilepath, 'w');
        SnapIO::flock($stateHandle, LOCK_EX);

        $this->initMembers();
        SnapIO::fwrite($stateHandle, json_encode($this));
        SnapIO::flock($stateHandle, LOCK_UN);
        SnapIO::fclose($stateHandle);
    }

    /**
     * Save state
     *
     * @return void
     */
    public function save()
    {
        $stateFilepath = dirname(__FILE__) . '/' . self::STATE_FILE;
        $stateHandle   = SnapIO::fopen($stateFilepath, 'w');
        SnapIO::flock($stateHandle, LOCK_EX);

        DupArchiveUtil::tlog("saving state");
        SnapIO::fwrite($stateHandle, json_encode($this));
        SnapIO::flock($stateHandle, LOCK_UN);
        SnapIO::fclose($stateHandle);
    }

    /**
     * Init props
     *
     * @return void
     */
    private function initMembers()
    {
        $this->currentFileHeader     = null;
        $this->archiveOffset         = 0;
        $this->archiveHeader         = 0;
        $this->archivePath           = null;
        $this->basePath              = null;
        $this->currentFileOffset     = 0;
        $this->failures              = array();
        $this->isCompressed          = false;
        $this->startTimestamp        = time();
        $this->timeSliceInSecs       = -1;
        $this->working               = false;
        $this->validateOnly          = false;
        $this->filteredDirectories   = array();
        $this->filteredFiles         = array();
        $this->fileRenames           = array();
        $this->directoryModeOverride = -1;
        $this->fileModeOverride      = -1;
        $this->lastHeaderOffset      = -1;
        $this->throttleDelayInUs     = 0;
        $this->timerEnabled          = true;
    }
}
