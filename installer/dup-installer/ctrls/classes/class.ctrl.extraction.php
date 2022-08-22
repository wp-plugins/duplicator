<?php

/**
 * Extraction class
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

use Duplicator\Installer\Core\Deploy\DupArchive\Daws;
use Duplicator\Installer\Core\Deploy\Files\FilterMng;
use Duplicator\Installer\Core\Deploy\Files\Filters;
use Duplicator\Installer\Core\Deploy\Files\RemoveFiles;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Utils\Log\LogHandler;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\DupArchive\DupArchive;
use Duplicator\Libs\Snap\JsonSerialize\AbstractJsonSerializable;
use Duplicator\Libs\Snap\JsonSerialize\JsonSerialize;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapLog;
use Duplicator\Libs\Snap\SnapWP;

class DUP_Extraction extends AbstractJsonSerializable
{
    const DUP_FOLDER_NAME               = 'dup-installer';
    const ENGINE_MANUAL                 = 'manual';
    const ENGINE_ZIP                    = 'ziparchive';
    const ENGINE_ZIP_CHUNK              = 'ziparchivechunking';
    const ENGINE_ZIP_SHELL              = 'shellexec_unzip';
    const ENGINE_DUP                    = 'duparchive';
    const ACTION_DO_NOTHING             = 'donothing';
    const ACTION_REMOVE_ALL_FILES       = 'removeall';
    const ACTION_REMOVE_WP_FILES        = 'removewpfiles';
    const ACTION_REMOVE_UPLOADS         = 'removeuoploads';
    const FILTER_SKIP_WP_CORE           = 'skip-wp-core';
    const FILTER_SKIP_CORE_PLUG_THEMES  = 'fil-c-p-l';
    const FILTER_ONLY_MEDIA_PLUG_THEMES = 'fil-only-m';
    const FILTER_NONE                   = 'none';
    const ZIP_THROTTLING_ITERATIONS     = 10;
    const ZIP_THROTTLING_SLEEP_TIME     = 100;

    public $zip_filetime                          = null;
    public $archive_action                        = self::ACTION_DO_NOTHING;
    public $archive_engine                        = null;
    public $extractonStart                        = 0;
    public $chunkStart                            = 0;
    public $root_path                             = null;
    public $archive_path                          = null;
    public $ajax1_error_level                     = E_ALL;
    public $dawn_status                           = null;
    public $archive_offset                        = 0;
    public $do_chunking                           = false;
    public $chunkedExtractionCompleted            = false;
    public $num_files                             = 0;
    public $sub_folder_archive                    = '';
    public $max_size_extract_at_a_time            = 0;
    public $zip_arc_chunk_notice_no               = -1;
    public $zip_arc_chunk_notice_change_last_time = 0;
    public $zip_arc_chunks_extract_rates          = array();
    public $archive_items_count                   = 0;
    /** @var Filters */
    public $filters = null;
    /** @var Filters */
    public $removeFilters = null;

    /**
     *
     * @var self
     */
    protected static $instance = null;

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Contructor
     */
    private function __construct()
    {
        if (!DUPX_Validation_manager::isValidated()) {
            throw new Exception('Installer isn\'t validated');
        }
        $this->initData();
    }

    /**
     * Inizialize extraction data
     *
     * @return void
     */
    public function initData()
    {
        // if data file exists load saved data
        if (file_exists(self::extractionDataFilePath())) {
            Log::info('LOAD EXTRACTION DATA FROM JSON', Log::LV_DETAILED);
            if ($this->loadData() == false) {
                throw new Exception('Can\'t load extraction data');
            }
        } else {
            Log::info('INIT EXTRACTION DATA', Log::LV_DETAILED);
            $this->constructData();
            $this->saveData();
            $this->logStart();
        }

        if (strlen($relativeAbsPth = DUPX_ArchiveConfig::getInstance()->getRelativePathsInArchive('abs')) > 0) {
            Log::info('SET RELATIVE ABSPATH: ' . Log::v2str($relativeAbsPth));
            SnapWP::setWpCoreRelativeAbsPath(DUPX_ArchiveConfig::getInstance()->getRelativePathsInArchive('abs'));
        }

        $this->chunkStart = DUPX_U::getMicrotime();
    }

    /**
     * Construct persistent data
     *
     * @return void
     */
    private function constructData()
    {
        $paramsManager = PrmMng::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        $this->extractonStart      = DUPX_U::getMicrotime();
        $this->zip_filetime        = $paramsManager->getValue(PrmMng::PARAM_FILE_TIME);
        $this->archive_action      = $paramsManager->getValue(PrmMng::PARAM_ARCHIVE_ACTION);
        $this->archive_engine      = $paramsManager->getValue(PrmMng::PARAM_ARCHIVE_ENGINE);
        $this->root_path           = SnapIO::trailingslashit($paramsManager->getValue(PrmMng::PARAM_PATH_NEW));
        $this->archive_path        = DUPX_Security::getInstance()->getArchivePath();
        $this->dawn_status         = null;
        $this->archive_items_count = $archiveConfig->totalArchiveItemsCount();
        $this->ajax1_error_level   = error_reporting();
        error_reporting(E_ERROR);
        $this->max_size_extract_at_a_time = DUPX_U::get_default_chunk_size_in_byte(MB_IN_BYTES * 2);

        if (self::ENGINE_DUP == $this->archive_engine || $this->archive_engine == self::ENGINE_MANUAL) {
            $this->sub_folder_archive = '';
        } elseif (($this->sub_folder_archive = DUPX_U::findDupInstallerFolder(DUPX_Security::getInstance()->getArchivePath())) === false) {
            Log::info("findDupInstallerFolder error; set no subfolder");
            // if not found set not subfolder
            $this->sub_folder_archive = '';
        }

        $this->filters       = FilterMng::getExtractFilters($this->sub_folder_archive);
        $this->removeFilters = FilterMng::getRemoveFilters($this->filters);
    }

    /**
     *
     * @return string
     */
    private static function extractionDataFilePath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = DUPX_INIT . '/dup-installer-extraction__' . DUPX_Package::getPackageHash() . '.json';
        }
        return $path;
    }

    /**
     *
     * @return boolean
     */
    public function saveData()
    {
        if (($json = SnapJson::jsonEncodePPrint($this)) === false) {
            Log::info('Can\'t encode json data');
            return false;
        }

        if (@file_put_contents(self::extractionDataFilePath(), $json) === false) {
            Log::info('Can\'t save extraction data file');
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    private function loadData()
    {
        if (!file_exists(self::extractionDataFilePath())) {
            return false;
        }

        if (($json = @file_get_contents(self::extractionDataFilePath())) === false) {
            throw new Exception('Can\'t load extraction data file');
        }

        JsonSerialize::unserializeToObj($json, $this);
        return true;
    }

    /**
     * reset extraction data
     *
     * @return boolean
     */
    public static function resetData()
    {
        $result = true;
        if (file_exists(self::extractionDataFilePath())) {
            if (@unlink(self::extractionDataFilePath()) === false) {
                throw new Exception('Can\'t delete extraction data file');
            }
        }
        return $result;
    }

    /**
     * Preliminary actions before the extraction.
     *
     * @return void
     */
    protected function beforeExtraction()
    {
        if (!$this->isFirst()) {
            return;
        }

        Log::info('BEFORE EXTRACION ACTIONS');

        if (DUPX_ArchiveConfig::getInstance()->exportOnlyDB) {
            Log::info('EXPORT DB ONLY CHECKS');
            $this->exportOnlyDB();
        }

        DUPX_ServerConfig::reset($this->root_path);

        $remover = new RemoveFiles($this->removeFilters);
        $remover->remove();

        //throw new Exception('FORCE FAIL');

        DUPX_U::maintenanceMode(true);

        $this->createFoldersAndPermissionPrepare();

        if (!empty($this->sub_folder_archive)) {
            Log::info("ARCHIVE dup-installer SUBFOLDER:" . Log::v2str($this->sub_folder_archive));
        } else {
            Log::info("ARCHIVE dup-installer SUBFOLDER:" . Log::v2str($this->sub_folder_archive), Log::LV_DETAILED);
        }
    }

    /**
     * Shows next step and final report notice files are found WP core folders
     *
     * @return void
     */
    protected function configFilesCheckNotice()
    {
        //Test if config files are present in main folders
        $folderList = array(
            PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . "/wp-admin",
            PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . "/wp-includes",
            PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_CONTENT_NEW)
        );

        $configFiles = array(
            'php.ini',
            '.user.ini',
            '.htaccess'
        );

        $foundConfigFiles = array();

        foreach ($folderList as $dir) {
            foreach ($configFiles as $file) {
                if (file_exists($dir . '/' . $file)) {
                    $foundConfigFiles[] = DUPX_U::esc_html('- ' . $dir . '/' . $file);
                    Log::info("WARNING: Found " . $file . " config file in " . $dir, Log::LV_DETAILED);
                }
            }
        }

        if (!empty($foundConfigFiles)) {
            $noticeManager = DUPX_NOTICE_MANAGER::getInstance();
            $msg           = "Config files in WordPress main folders may cause problems with accessing the site after the installation." .
                " The following config files were found: <br><br>" . implode("<br>", $foundConfigFiles) .
                "<br><br>Please consider removing those files in case you have problems with your site after the installation.";

            $noticeManager->addBothNextAndFinalReportNotice(array(
                    'shortMsg'    => 'One or multiple config files were found in main WordPress folders',
                    'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                    'longMsg'     => $msg,
                    'sections'    => 'general'
                ));
            $noticeManager->saveNotices();
        }
    }

    /**
     * Execute extraction
     *
     * @throws Exception
     *
     * @return void
     */
    public function runExtraction()
    {
        $this->beforeExtraction();

        switch ($this->archive_engine) {
            case self::ENGINE_ZIP_CHUNK:
                $this->runZipArchive(true);
                break;
            case self::ENGINE_ZIP:
                $this->runZipArchive(false);
                break;
            case self::ENGINE_MANUAL:
                break;
            case self::ENGINE_ZIP_SHELL:
                $this->runShellExec();
                break;
            case self::ENGINE_DUP:
                $this->runDupExtraction();
                break;
            default:
                throw new Exception('No valid engine ' . $this->archive_engine);
        }
    }

    /**
     *
     * @return boolean
     *
     * @throws Exception
     */
    protected function createFoldersAndPermissionPrepare()
    {
        Log::info("\n*** CREATE FOLDER AND PERMISSION PREPARE");

        switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE)) {
            case self::ENGINE_ZIP_CHUNK:
            case self::ENGINE_ZIP:
            case self::ENGINE_DUP:
                $filters = $this->filters;
                DUPX_Package::foreachDirCallback(function ($info) use ($filters) {
                    if ($filters->isFiltered($info->p)) {
                        return true;
                    }

                    $destPath = DUPX_ArchiveConfig::getInstance()->destFileFromArchiveName($info->p);

                    if (file_exists($destPath)) {
                        Log::info("PATH " . Log::v2str($destPath) . ' ALEADY EXISTS', Log::LV_DEBUG);
                    } else {
                        Log::info("PATH " . Log::v2str($destPath) . ' NOT EXISTS, CREATE IT', Log::LV_DEBUG);
                        if (SnapIO::mkdirP($destPath) === false) {
                            Log::info("ARCHIVE EXTRACION: can't create folder " . Log::v2str($destPath));
                        }
                    }

                    if (!SnapIO::dirAddFullPermsAndCheckResult($destPath)) {
                        Log::info("ARCHIVE EXTRACION: can't set writable " . Log::v2str($destPath));
                    }
                });
                break;
            case self::ENGINE_ZIP_SHELL:
                self::setPermsViaShell('u+rwx', 'u+rw');
                break;
            case self::ENGINE_MANUAL:
                break;
            default:
                throw new Exception('No valid engine ' . $this->archive_engine);
        }

        Log::info("FOLDER PREPARE DONE");
        return true;
    }

    /**
     *
     * @return boolean
     * @throws Exception
     */
    public static function setFolderPermissionAfterExtraction()
    {
        $paramManager = PrmMng::getInstance();
        if (!$paramManager->getValue(PrmMng::PARAM_SET_DIR_PERMS)) {
            Log::info('\n SKIP FOLDER PERMISSION AFTER EXTRACTION');
            return;
        }

        Log::info("\n*** SET FOLDER PERMISSION AFTER EXTRACTION");

        switch ($paramManager->getValue(PrmMng::PARAM_ARCHIVE_ENGINE)) {
            case self::ENGINE_ZIP_CHUNK:
            case self::ENGINE_ZIP:
            case self::ENGINE_DUP:
                DUPX_Package::foreachDirCallback(function ($info) {
                    $destPath = DUPX_ArchiveConfig::getInstance()->destFileFromArchiveName($info->p);
                    DUP_Extraction::setPermsFromParams($destPath);
                });
                break;
            case self::ENGINE_ZIP_SHELL:
                $dirPerms  = (
                    $paramManager->getValue(PrmMng::PARAM_SET_DIR_PERMS) ?
                    $paramManager->getValue(PrmMng::PARAM_DIR_PERMS_VALUE) :
                    false);
                $filePerms = (
                    $paramManager->getValue(PrmMng::PARAM_SET_FILE_PERMS) ?
                    $paramManager->getValue(PrmMng::PARAM_FILE_PERMS_VALUE) :
                    false);
                self::setPermsViaShell($dirPerms, $filePerms, true);
                break;
            case self::ENGINE_MANUAL:
                break;
            default:
                throw new Exception('No valid engine ');
        }

        Log::info("SET FOLDER PERMISSION DONE");
        return true;
    }

    /**
     * Extract package with duparchive
     *
     * @return void
     */
    protected function runDupExtraction()
    {
        $paramsManager = PrmMng::getInstance();
        $nManager      = DUPX_NOTICE_MANAGER::getInstance();

        SnapLog::init(Log::getLogFilePath());
        SnapLog::$logHandle = Log::getFileHandle();

        $params = array(
            'action'                   => $this->isFirst() ? 'start_expand' : 'expand',
            'archive_filepath'         => DUPX_Security::getInstance()->getArchivePath(),
            'restore_directory'        => $paramsManager->getValue(PrmMng::PARAM_PATH_NEW),
            'worker_time'              => DUPX_Constants::CHUNK_EXTRACTION_TIMEOUT_TIME_ZIP,
            'filtered_directories'     => $this->filters->getDirs(),
            'filtered_files'           => $this->filters->getFiles(),
            'excludedDirWithoutChilds' => $this->filters->getDirsWithoutChilds(),
            'includeFiles'             => array(), // ignore filtered
            'file_renames'             => array(),
            'file_mode_override'       => (
            $paramsManager->getValue(PrmMng::PARAM_SET_FILE_PERMS) ?
            $paramsManager->getValue(PrmMng::PARAM_FILE_PERMS_VALUE) :
            -1),
            'includedFiles'            => array(),
            'dir_mode_override'        => 'u+rwx',
        );

        $params['filtered_files'][] = DupArchive::INDEX_FILE_NAME;
        if (!file_exists(DUPX_Package::getSqlFilePath())) {
            Log::info('SQL FILE NOT FOUND SO ADD TO EXTRACTION');
            $params['includedFiles'][]                                      = DUPX_Package::getSqlFilePathInArchive();
            $params['fileRenames'][DUPX_Package::getSqlFilePathInArchive()] = DUPX_Package::getSqlFilePath();
        }

        $offset = $this->isFirst() ? 0 : $this->dawn_status->archive_offset;
        Log::info("ARCHIVE OFFSET " . $offset);

        $daws = new Daws();
        $daws->setFailureCallBack(function ($failure) {
            self::reportExtractionNotices($failure->subject, $failure->description);
        });
        $dupResult         = $daws->processRequest($params);
        $this->dawn_status = $dupResult->status;
        $nManager->saveNotices();
    }

    /**
     * extract package with ziparchive
     *
     * @param bool $chunk false no chunk system
     *
     * @return void
     *
     * @throws Exception
     */
    protected function runZipArchive($chunk = true)
    {
        if (!class_exists('ZipArchive')) {
            Log::info("ERROR: Stopping install process. " .
            "Trying to extract without ZipArchive module installed. " .
            "Please use the 'Manual Archive Extraction' mode to extract zip file.");
            Log::error(ERR_ZIPARCHIVE);
        }

        $nManager            = DUPX_NOTICE_MANAGER::getInstance();
        $archiveConfig       = DUPX_ArchiveConfig::getInstance();
        $dupInstallerZipPath = ltrim($this->sub_folder_archive . '/' . self::DUP_FOLDER_NAME, '/');

        $zip       = new ZipArchive();
        $time_over = false;

        Log::info("ARCHIVE OFFSET " . Log::v2str($this->archive_offset));
        Log::info('DUP INSTALLER ARCHIVE PATH:"' . $dupInstallerZipPath . '"', Log::LV_DETAILED);

        if ($zip->open($this->archive_path) !== true) {
            $zip_err_msg  = ERR_ZIPOPEN;
            $zip_err_msg .= "<br/><br/><b>To resolve error see <a href='" .
                DUPX_Constants::FAQ_URL . "/#faq-installer-130-q' target='_blank'>" .
                DUPX_Constants::FAQ_URL . "/#faq-installer-130-q</a></b>";
            Log::info($zip_err_msg);
            throw new Exception("Couldn't open zip archive.");
        }

        $this->num_files   = $zip->numFiles;
        $num_files_minus_1 = $this->num_files - 1;

        $extracted_size = 0;

        LogHandler::setMode(LogHandler::MODE_VAR, false, false);

        // Main chunk
        do {
            $extract_filename = null;

            $no_of_files_in_micro_chunk = 0;
            $size_in_micro_chunk        = 0;
            do {
                //rsr uncomment if debugging     Log::info("c ao " . $this->archive_offset);
                $stat_data = $zip->statIndex($this->archive_offset);
                $filename  = $stat_data['name'];

                if ($this->filters->isFiltered($filename)) {
                    if (Log::isLevel(Log::LV_DETAILED)) {
                        // optimization
                        Log::info("FILE EXTRACTION SKIP: " . Log::v2str($filename), Log::LV_DETAILED);
                    }
                } else {
                    $extract_filename     = $filename;
                    $size_in_micro_chunk += $stat_data['size'];
                    $no_of_files_in_micro_chunk++;
                }

                $this->archive_offset++;
            } while (
                $this->archive_offset < $num_files_minus_1 &&
                $no_of_files_in_micro_chunk < 1 &&
                $size_in_micro_chunk < $this->max_size_extract_at_a_time
            );

            if (!empty($extract_filename)) {
                // skip dup-installer folder. Alrady extracted in bootstrap
                if (
                    (strpos($extract_filename, $dupInstallerZipPath) === 0) ||
                    (strlen($this->sub_folder_archive) > 0 && strpos($extract_filename, $this->sub_folder_archive) !== 0)
                ) {
                    Log::info("SKIPPING NOT IN ZIPATH:\"" . Log::v2str($extract_filename) . "\"", Log::LV_DETAILED);
                } else {
                    $this->extractFile($zip, $extract_filename, $archiveConfig->destFileFromArchiveName($extract_filename));
                }
            }

            $extracted_size += $size_in_micro_chunk;
            if ($this->archive_offset == $this->num_files - 1) {
                if (!empty($this->sub_folder_archive)) {
                    DUPX_U::moveUpfromSubFolder($this->root_path . $this->sub_folder_archive, true);
                }

                Log::info("FILE EXTRACTION: done processing last file in list of {$this->num_files}");
                $this->chunkedExtractionCompleted = true;
                break;
            }

            if (PrmMng::getInstance()->getValue(PrmMng::PARAM_ZIP_THROTTLING)) {
                for ($i = 0; $i < self::ZIP_THROTTLING_ITERATIONS; $i++) {
                    usleep(self::ZIP_THROTTLING_SLEEP_TIME);
                }
            }

            if (($time_over = $chunk && (DUPX_U::getMicrotime() - $this->chunkStart) > DUPX_Constants::CHUNK_EXTRACTION_TIMEOUT_TIME_ZIP)) {
                Log::info("TIME IS OVER - CHUNK", 2);
            }
        } while ($this->archive_offset < $num_files_minus_1 && !$time_over);

        // set handler as default
        LogHandler::setMode();
        $zip->close();

        $chunk_time = DUPX_U::getMicrotime() - $this->chunkStart;

        $chunk_extract_rate                   = $extracted_size / $chunk_time;
        $this->zip_arc_chunks_extract_rates[] = $chunk_extract_rate;
        $zip_arc_chunks_extract_rates         = $this->zip_arc_chunks_extract_rates;
        $average_extract_rate                 = array_sum($zip_arc_chunks_extract_rates) / count($zip_arc_chunks_extract_rates);

        $expected_extract_time = $average_extract_rate > 0 ? DUPX_Conf_Utils::archiveSize() / $average_extract_rate : 0;

        /*
            Log::info("Expected total archive extract time: {$expected_extract_time}");
            Log::info("Total extraction elapsed time until now: {$expected_extract_time}");
            */

        $elapsed_time      = DUPX_U::getMicrotime() - $this->extractonStart;
        $max_no_of_notices = count($GLOBALS['ZIP_ARC_CHUNK_EXTRACT_NOTICES']) - 1;

        $zip_arc_chunk_extract_disp_notice_after                     = $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NOTICE_AFTER'];
        $zip_arc_chunk_extract_disp_notice_min_expected_extract_time = $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NOTICE_MIN_EXPECTED_EXTRACT_TIME'];
        $zip_arc_chunk_extract_disp_next_notice_interval             = $GLOBALS['ZIP_ARC_CHUNK_EXTRACT_DISP_NEXT_NOTICE_INTERVAL'];

        if ($this->zip_arc_chunk_notice_no < 0) { // -1
            if (
                (
                    $elapsed_time > $zip_arc_chunk_extract_disp_notice_after &&
                    $expected_extract_time > $zip_arc_chunk_extract_disp_notice_min_expected_extract_time
                ) ||
                $elapsed_time > $zip_arc_chunk_extract_disp_notice_min_expected_extract_time
            ) {
                $this->zip_arc_chunk_notice_no++;
                $this->zip_arc_chunk_notice_change_last_time = DUPX_U::getMicrotime();
            }
        } elseif ($this->zip_arc_chunk_notice_no > 0 && $this->zip_arc_chunk_notice_no < $max_no_of_notices) {
            $interval_after_last_notice = DUPX_U::getMicrotime() - $this->zip_arc_chunk_notice_change_last_time;
            Log::info("Interval after last notice: {$interval_after_last_notice}");
            if ($interval_after_last_notice > $zip_arc_chunk_extract_disp_next_notice_interval) {
                $this->zip_arc_chunk_notice_no++;
                $this->zip_arc_chunk_notice_change_last_time = DUPX_U::getMicrotime();
            }
        }

        $nManager->saveNotices();

        //rsr todo uncomment when debugging      Log::info("Zip archive chunk notice no.: {$this->zip_arc_chunk_notice_no}");
    }

    /**
     * Set files permission
     *
     * @param string  $path    Path
     * @param boolean $setDir  Folders permissions
     * @param boolean $setFile Files permissions
     *
     * @return boolean // false if fail, if file don't exists retur true
     */
    public static function setPermsFromParams($path, $setDir = true, $setFile = true)
    {
        static $permsSettings = null;

        if (is_null($permsSettings)) {
            $paramsManager = PrmMng::getInstance();

            $permsSettings = array(
                'fileSet' => $paramsManager->getValue(PrmMng::PARAM_SET_FILE_PERMS),
                'fileVal' => $paramsManager->getValue(PrmMng::PARAM_FILE_PERMS_VALUE),
                'dirSet'  => $paramsManager->getValue(PrmMng::PARAM_SET_DIR_PERMS),
                'dirVal'  => $paramsManager->getValue(PrmMng::PARAM_DIR_PERMS_VALUE)
            );
        }

        if (!file_exists($path)) {
            return true;
        }

        if (is_file($path) || is_link($path)) {
            if ($setFile && $permsSettings['fileSet']) {
                if (!SnapIO::chmod($path, $permsSettings['fileVal'])) {
                    Log::info('CHMOD FAIL: ' . $path . ' PERMS: ' . SnapIO::permsToString($permsSettings['fileVal']));
                    return false;
                }
            }
        } else {
            if ($setDir && $permsSettings['dirSet']) {
                if (!SnapIO::chmod($path, $permsSettings['dirVal'])) {
                    Log::info('CHMOD FAIL: ' . $path . ' PERMS: ' . SnapIO::permsToString($permsSettings['dirVal']));
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Extract file from zip archive
     *
     * @param ZipArchive $zipObj      Zip archive object
     * @param string     $zipFilename File name
     * @param string     $newFilePath Path to extract
     *
     * @return void
     */
    protected function extractFile(ZipArchive $zipObj, $zipFilename, $newFilePath)
    {
        try {
            //rsr uncomment if debugging     Log::info("Attempting to extract {$zipFilename}. Time:". time());
            $error = false;

            // IF EXIST SET READ WRITE PERMISSION
            if (is_file($newFilePath) || is_link($newFilePath)) {
                SnapIO::chmod($newFilePath, 'u+rw');
            } elseif (is_dir($newFilePath)) {
                SnapIO::chmod($newFilePath, 'u+rwx');
            }

            if ($this->root_path . ltrim($zipFilename, '\\/') === $newFilePath) {
                if (Log::isLevel(Log::LV_DEBUG)) {
                    Log::info('EXTRACT FILE [' . $zipFilename . '] TO [' . $newFilePath . ']', Log::LV_DEBUG);
                }
                if (!$zipObj->extractTo($this->root_path, $zipFilename)) {
                    $error = true;
                }
            } else {
                if (Log::isLevel(Log::LV_DEBUG)) {
                    Log::info('CUSTOM EXTRACT FILE [' . $zipFilename . '] TO [' . $newFilePath . ']', Log::LV_DEBUG);
                }
                if (substr($zipFilename, -1) === '/') {
                    SnapIO::mkdirP(dirname($newFilePath));
                } else {
                    if (($destStream = fopen($newFilePath, 'w')) === false) {
                        if (!file_exists(dirname($newFilePath))) {
                            SnapIO::mkdirP(dirname($newFilePath));
                            if (($destStream = fopen($newFilePath, 'w')) === false) {
                                $error = true;
                            }
                        } else {
                            $error = true;
                        }
                    }

                    if ($error || ($sourceStream = $zipObj->getStream($zipFilename)) === false) {
                        $error = true;
                    } else {
                        while (!feof($sourceStream)) {
                            fwrite($destStream, fread($sourceStream, 1048576)); // 1M
                        }

                        fclose($sourceStream);
                        fclose($destStream);
                    }
                }
            }

            if ($error) {
                self::reportExtractionNotices($zipFilename, LogHandler::getVarLogClean());
            } else {
                if (Log::isLevel(Log::LV_HARD_DEBUG)) {
                    Log::info("FILE EXTRACTION DONE: " . Log::v2str($zipFilename), Log::LV_HARD_DEBUG);
                }
                // SET ONLY FILES
                self::setPermsFromParams($newFilePath, false);
            }
        } catch (Exception $ex) {
            self::reportExtractionNotices($zipFilename, $ex->getMessage());
        }
    }

    /**
     *
     * @param string $fileName     package relative path
     * @param string $errorMessage error message
     *
     * @return void
     */
    protected static function reportExtractionNotices($fileName, $errorMessage)
    {
        if (DUPX_Custom_Host_Manager::getInstance()->skipWarningExtractionForManaged($fileName)) {
            // @todo skip warning for managed hostiong (it's a temp solution)
            return;
        }
        $nManager = DUPX_NOTICE_MANAGER::getInstance();

        if (SnapWP::isWpCore($fileName, SnapWP::PATH_RELATIVE)) {
            Log::info("FILE CORE EXTRACTION ERROR: {$fileName} | MSG:" . $errorMessage);
            $shortMsg      = 'Can\'t extract wp core files';
            $finalShortMsg = 'Wp core files not extracted';
            $errLevel      = DUPX_NOTICE_ITEM::CRITICAL;
            $idManager     = 'wp-extract-error-file-core';
        } else {
            Log::info("FILE EXTRACTION ERROR: {$fileName} | MSG:" . $errorMessage);
            $shortMsg      = 'Can\'t extract files';
            $finalShortMsg = 'Files not extracted';
            $errLevel      = DUPX_NOTICE_ITEM::SOFT_WARNING;
            $idManager     = 'wp-extract-error-file-no-core';
        }

        $longMsg = 'FILE: <b>' . htmlspecialchars($fileName) . '</b><br>Message: ' . htmlspecialchars($errorMessage) . '<br><br>';

        $nManager->addNextStepNotice(array(
            'shortMsg'    => $shortMsg,
            'longMsg'     => $longMsg,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
            'level'       => $errLevel
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, $idManager);
        $nManager->addFinalReportNotice(array(
            'shortMsg'    => $finalShortMsg,
            'longMsg'     => $longMsg,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
            'level'       => $errLevel,
            'sections'    => array('files'),
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, $idManager);
    }

    /**
     * Export db only
     *
     * @return void
     */
    protected function exportOnlyDB()
    {
        if ($this->archive_engine == self::ENGINE_MANUAL || $this->archive_engine == self::ENGINE_DUP) {
            $sql_file_path = DUPX_Package::getSqlFilePath();
            if (!file_exists(DUPX_Package::getWpconfigArkPath()) && !file_exists($sql_file_path)) {
                Log::error(ERR_ZIPMANUAL);
            }
        } else {
            if (!is_readable("{$this->archive_path}")) {
                Log::error("archive file path:<br/>" . ERR_ZIPNOTFOUND);
            }
        }
    }

    /**
     * Write extraction log header
     *
     * @return void
     */
    protected function logStart()
    {
        $paramsManager = PrmMng::getInstance();

        Log::info("********************************************************************************");
        Log::info('* DUPLICATOR LITE: Install-Log');
        Log::info('* STEP-1 START @ ' . @date('h:i:s'));
        Log::info('* NOTICE: Do NOT post to public sites or forums!!');
        Log::info("********************************************************************************");

        $labelPadSize = 20;
        Log::info("USER INPUTS");
        Log::info(str_pad('INSTALL TYPE', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . DUPX_InstallerState::installTypeToString());
        Log::info(str_pad('BLOG NAME', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_BLOGNAME)));

        Log::info(str_pad('HOME URL NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_URL_NEW)));
        Log::info(str_pad('SITE URL NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_SITE_URL)));
        Log::info(str_pad('CONTENT URL NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW)));
        Log::info(str_pad('UPLOAD URL NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_URL_UPLOADS_NEW)));
        Log::info(str_pad('PLUGINS URL NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_URL_PLUGINS_NEW)));
        Log::info(
            str_pad('MUPLUGINS URL NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_URL_MUPLUGINS_NEW))
        );

        Log::info(str_pad('HOME PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_PATH_NEW)));
        Log::info(str_pad('SITE PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW)));
        Log::info(str_pad('CONTENT PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW)));
        Log::info(str_pad('UPLOAD PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW)));
        Log::info(str_pad('PLUGINS PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW)));
        Log::info(
            str_pad('MUPLUGINS PATH NEW', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW))
        );

        Log::info(str_pad('ARCHIVE ACTION', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_ARCHIVE_ACTION)));
        Log::info(
            str_pad(
                'SKIP WP FILES',
                $labelPadSize,
                '_',
                STR_PAD_RIGHT
            ) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES))
        );
        Log::info(str_pad('ARCHIVE ENGINE', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_ARCHIVE_ENGINE)));
        Log::info(str_pad('SET DIR PERMS', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_SET_DIR_PERMS)));
        Log::info(
            str_pad(
                'DIR PERMS VALUE',
                $labelPadSize,
                '_',
                STR_PAD_RIGHT
            ) . ': ' . SnapIO::permsToString($paramsManager->getValue(PrmMng::PARAM_DIR_PERMS_VALUE))
        );
        Log::info(str_pad('SET FILE PERMS', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_SET_FILE_PERMS)));
        Log::info(
            str_pad(
                'FILE PERMS VALUE',
                $labelPadSize,
                '_',
                STR_PAD_RIGHT
            ) . ': ' . SnapIO::permsToString($paramsManager->getValue(PrmMng::PARAM_FILE_PERMS_VALUE))
        );
        Log::info(str_pad('SAFE MODE', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_SAFE_MODE)));
        Log::info(str_pad('LOGGING', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_LOGGING)));
        Log::info(str_pad('ZIP THROTTLING', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_ZIP_THROTTLING)));
        Log::info(str_pad('WP CONFIG', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_WP_CONFIG)));
        Log::info(str_pad('HTACCESS CONFIG', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_HTACCESS_CONFIG)));
        Log::info(str_pad('OTHER CONFIG', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_OTHER_CONFIG)));
        Log::info(str_pad('FILE TIME', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_FILE_TIME)));
        Log::info("********************************************************************************\n");
        Log::info('REMOVE FILTERS');
        Log::incIndent();
        foreach ($this->removeFilters->getDirs() as $path) {
            Log::info('DIR : ' . Log::v2str($path));
        }
        foreach ($this->removeFilters->getFiles() as $path) {
            Log::info('FILE: ' . Log::v2str($path));
        }
        foreach ($this->removeFilters->getDirsWithoutChilds() as $path) {
            Log::info('DIRS WITHOUT CHILDS: ' . Log::v2str($path));
        }
        Log::resetIndent();
        Log::info('EXTRACTION FILTERS');
        Log::incIndent();
        foreach ($this->filters->getDirs() as $path) {
            Log::info('DIR : ' . Log::v2str($path));
        }
        foreach ($this->filters->getFiles() as $path) {
            Log::info('FILE: ' . Log::v2str($path));
        }
        foreach ($this->filters->getDirsWithoutChilds() as $path) {
            Log::info('DIR WITHOUT CHILDS: ' . Log::v2str($path));
        }
        Log::resetIndent();
        Log::info("--------------------------------------\n");

        switch ($this->archive_engine) {
            case self::ENGINE_ZIP_CHUNK:
                Log::info("\nEXTRACTION: ZIP CHUNKING >>> START");
                break;
            case self::ENGINE_ZIP:
                Log::info("\nEXTRACTION: ZIP STANDARD >>> START");
                break;
            case self::ENGINE_MANUAL:
                Log::info("\nEXTRACTION: MANUAL MODE >>> START");
                break;
            case self::ENGINE_ZIP_SHELL:
                Log::info("\nEXTRACTION: ZIP SHELL >>> START");
                break;
            case self::ENGINE_DUP:
                Log::info("\nEXTRACTION: DUP ARCHIVE >>> START");
                break;
            default:
                throw new Exception('No valid engine ' . $this->archive_engine);
        }
    }

    /**
     * Write log extraction end
     *
     * @return void
     */
    protected function logComplete()
    {

        switch ($this->archive_engine) {
            case self::ENGINE_ZIP_CHUNK:
                Log::info("\nEXTRACTION: ZIP CHUNKING >>> DONE");
                break;
            case self::ENGINE_ZIP:
                Log::info("\nEXTRACTION: ZIP STANDARD >>> DONE");
                break;
            case self::ENGINE_MANUAL:
                Log::info("\nEXTRACTION: MANUAL MODE >>> DONE");
                break;
            case self::ENGINE_ZIP_SHELL:
                Log::info("\nEXTRACTION: ZIP SHELL >>> DONE");
                break;
            case self::ENGINE_DUP:
                $criticalPresent = false;
                if (count($this->dawn_status->failures) > 0) {
                    $log = '';
                    foreach ($this->dawn_status->failures as $failure) {
                        if ($failure->isCritical) {
                            $log            .= 'DUP EXTRACTION CRITICAL ERROR ' . $failure->description;
                            $criticalPresent = true;
                        }
                    }
                    if (!empty($log)) {
                        Log::info($log);
                    }
                }
                if ($criticalPresent) {
                    throw new Exception('Critical Errors present so stopping install.');
                }

                Log::info("\n\nEXTRACTION: DUP ARCHIVE >>> DONE");
                break;
            default:
                throw new Exception('No valid engine ' . $this->archive_engine);
        }
    }

    /**
     * Extract zip archive via shell
     *
     * @return void
     */
    protected function runShellExec()
    {
        $command = escapeshellcmd(DUPX_Server::get_unzip_filepath()) .
            " -o -qq " . escapeshellarg($this->archive_path) . " -d " .
            escapeshellarg($this->root_path) . " 2>&1";
        if ($this->zip_filetime == 'original') {
            Log::info("\nShell Exec Current does not support orginal file timestamp please use ZipArchive");
        }

        Log::info('SHELL COMMAND: ' . Log::v2str($command));
        $stderr = shell_exec($command);
        if ($stderr != '') {
            $zip_err_msg  = ERR_SHELLEXEC_ZIPOPEN . ": $stderr";
            $zip_err_msg .= "<br/><br/><b>To resolve error see <a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-130-q' " .
            "target='_blank'>https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-130-q</a></b>";
            Log::error($zip_err_msg);
        }
    }

    /**
     * Set file permission via shell
     *
     * @param boolean|string $dirPerm        folders permissions
     * @param boolean|string $filePerm       files permsission
     * @param boolean        $excludeDupInit if true dont set permsission on dup folder
     *
     * @return void
     */
    protected static function setPermsViaShell($dirPerm = false, $filePerm = false, $excludeDupInit = false)
    {
        $rootPath        = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW);
        $exludeDupFolder = ($excludeDupInit ? "! -path " . escapeshellarg(DUPX_INIT . '*') . " " : '');

        if ($filePerm !== false) {
            $command = "find " . escapeshellarg($rootPath) . " -type d " . $exludeDupFolder . "-exec chmod " . SnapIO::permsToString($dirPerm) . " {} \;";
            Log::info('SHELL COMMAND: ' . Log::v2str($command));
            shell_exec($command);
        }

        if ($dirPerm !== false) {
            $command = "find " . escapeshellarg($rootPath) . " -type f " . $exludeDupFolder . "-exec chmod " . SnapIO::permsToString($filePerm) . " {} \;";
            Log::info('SHELL COMMAND: ' . Log::v2str($command));
            shell_exec($command);
        }
    }

    /**
     *
     * @return string
     */
    public static function getInitialFileProcessedString()
    {
        return 'Files processed: 0 of ' . number_format(DUPX_ArchiveConfig::getInstance()->totalArchiveItemsCount());
    }

    /**
     * Get extraction result
     *
     * @param boolean $complete true if extraction is complate false if chunk is complete
     *
     * @return array
     */
    protected function getResultExtraction($complete = false)
    {
        $result = array(
            'pass'           => 0,
            'processedFiles' => '',
            'perc'           => ''
        );

        if ($complete) {
            $result['pass'] = 1;
            $result['perc'] = '100%';
            switch ($this->archive_engine) {
                case self::ENGINE_ZIP_CHUNK:
                case self::ENGINE_ZIP:
                case self::ENGINE_ZIP_SHELL:
                case self::ENGINE_DUP:
                    $result['processedFiles'] = 'Files processed: ' . number_format($this->archive_items_count) .
                        ' of ' . number_format($this->archive_items_count);
                    break;
                case self::ENGINE_MANUAL:
                    break;
                default:
                    throw new Exception('No valid engine ' . $this->archive_engine);
            }

            $deltaTime = DUPX_U::elapsedTime(DUPX_U::getMicrotime(), $this->extractonStart);
            Log::info("\nEXTRACTION COMPLETE @ " . @date('h:i:s') . " - RUNTIME: {$deltaTime} - " . $result['processedFiles']);
        } else {
            $result['pass'] = -1;
            switch ($this->archive_engine) {
                case self::ENGINE_ZIP_CHUNK:
                case self::ENGINE_ZIP:
                case self::ENGINE_ZIP_SHELL:
                    $result['processedFiles'] = 'Files processed: ' . number_format(min($this->archive_offset, $this->archive_items_count)) .
                        ' of ' . number_format($this->archive_items_count);
                    $result['perc']           = min(100, round(($this->archive_offset * 100 / $this->archive_items_count), 2)) . '%';
                    break;
                case self::ENGINE_DUP:
                    $result['processedFiles'] = 'Files processed: ' . number_format(min($this->dawn_status->file_index, $this->archive_items_count)) .
                        ' of ' . number_format($this->archive_items_count);
                    $result['perc']           = min(100, round(($this->dawn_status->file_index * 100 / $this->archive_items_count), 2)) . '%';
                    break;
                case self::ENGINE_MANUAL:
                    break;
                default:
                    throw new Exception('No valid engine ' . $this->archive_engine);
            }

            $deltaTime = DUPX_U::elapsedTime(DUPX_U::getMicrotime(), $this->chunkStart);
            Log::info("CHUNK COMPLETE - RUNTIME: {$deltaTime} - " . $result['processedFiles']);
        }
        return $result;
    }

    /**
     * End extraction
     *
     * @return array
     */
    protected function finishFullExtraction()
    {
        $this->configFilesCheckNotice();
        $this->logComplete();
        return $this->getResultExtraction(true);
    }

    /**
     * End chunked extraction
     *
     * @return array
     */
    protected function finishChunkExtraction()
    {
        $this->saveData();
        return $this->getResultExtraction(false);
    }

    /**
     * Finish extraction process
     *
     * @return array
     */
    public function finishExtraction()
    {
        $complete = false;

        switch ($this->archive_engine) {
            case self::ENGINE_ZIP_CHUNK:
                $complete = $this->chunkedExtractionCompleted;
                break;
            case self::ENGINE_DUP:
                $complete = $this->dawn_status->is_done;
                break;
            case self::ENGINE_ZIP:
            case self::ENGINE_MANUAL:
            case self::ENGINE_ZIP_SHELL:
                $complete = true;
                break;
            default:
                throw new Exception('No valid engine ' . $this->archive_engine);
        }

        if ($complete) {
            return $this->finishFullExtraction();
        } else {
            return $this->finishChunkExtraction();
        }
    }

    /**
     *
     * @return bool
     */
    protected function isFirst()
    {
        switch ($this->archive_engine) {
            case self::ENGINE_ZIP_CHUNK:
                return $this->archive_offset == 0 && $this->archive_engine == self::ENGINE_ZIP_CHUNK;
            case self::ENGINE_DUP:
                return is_null($this->dawn_status);
            case self::ENGINE_ZIP:
            case self::ENGINE_MANUAL:
            case self::ENGINE_ZIP_SHELL:
                return true;
            default:
                throw new Exception('No valid engine ' . $this->archive_engine);
        }
    }
}
