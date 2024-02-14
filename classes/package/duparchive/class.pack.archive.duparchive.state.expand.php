<?php

use Duplicator\Libs\DupArchive\States\DupArchiveExpandState;
use Duplicator\Libs\Snap\SnapJson;

class DUP_DupArchive_Expand_State extends DupArchiveExpandState
{
    public static function getInstance($reset = false)
    {
        $instance = new DUP_DupArchive_Expand_State();
        if ($reset) {
            $instance->initMembers();
        } else {
            $instance->loadMembers();
        }

        return $instance;
    }

    private function loadMembers()
    {
        /** @var object $data */
        $data = DUP_Settings::Get('duparchive_expand_state');
        DUP_LOG::traceObject("****RAW EXPAND STATE LOADED****", $data);
        if ($data->currentFileHeaderString != null) {
            $this->currentFileHeader = DUP_JSON::decode($data->currentFileHeaderString);
        } else {
            $this->currentFileHeader = null;
        }

        if ($data->archiveHeaderString != null) {
            $this->archiveHeader = DUP_JSON::decode($data->archiveHeaderString);
        } else {
            $this->archiveHeader = null;
        }

        if ($data->failuresString) {
            $this->failures = DUP_JSON::decode($data->failuresString);
        } else {
            $this->failures = array();
        }

        DUP_Util::objectCopy($data, $this, array('archiveHeaderString', 'currentFileHeaderString', 'failuresString'));
    }

    public function save()
    {
        $data = new stdClass();
        if ($this->currentFileHeader != null) {
            $data->currentFileHeaderString = SnapJson::jsonEncode($this->currentFileHeader);
        } else {
            $data->currentFileHeaderString = null;
        }

        if ($this->archiveHeader != null) {
            $data->archiveHeaderString = SnapJson::jsonEncode($this->archiveHeader);
        } else {
            $data->archiveHeaderString = null;
        }

        $data->failuresString = SnapJson::jsonEncode($this->failures);
        // Object members auto skipped
        DUP_Util::objectCopy($this, $data);
        DUP_LOG::traceObject("****SAVING EXPAND STATE****", $this);
        DUP_LOG::traceObject("****SERIALIZED STATE****", $data);
        DUP_Settings::Set('duparchive_expand_state', $data);
        DUP_Settings::Save();
    }

    private function initMembers()
    {
        $this->currentFileHeader     = null;
        $this->archiveOffset         = 0;
        $this->archiveHeader         = null;
        $this->archivePath           = null;
        $this->basePath              = null;
        $this->currentFileOffset     = 0;
        $this->failures              = array();
        $this->isCompressed          = false;
        $this->startTimestamp        = time();
        $this->timeSliceInSecs       = -1;
        $this->working               = false;
        $this->validateOnly          = false;
        $this->directoryModeOverride = -1;
        $this->fileModeOverride      = -1;
        $this->throttleDelayInUs     = 0;
    }
}
