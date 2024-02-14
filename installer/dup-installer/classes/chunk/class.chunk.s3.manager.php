<?php

/**
 * Chunk manager step 3
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Chunk
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Deploy\Database\DbCleanup;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Utils\Log\LogHandler;
use Duplicator\Libs\Snap\SnapJson;

require_once(DUPX_INIT . '/classes/chunk/class.chunkingmanager_file.php');
require_once(DUPX_INIT . '/classes/chunk/Iterators/class.s3.iterator.php');

/**
 * Chunk manager step 3
 *
 * @author andrea
 */
class DUPX_chunkS3Manager extends DUPX_ChunkingManager_file
{
    /**
     *  Exectute action for every iteration
     *
     * @param string $key
     * @param array  $current
     */
    protected function action($key, $current)
    {
        $s3FuncsManager = DUPX_S3_Funcs::getInstance();

        Log::info('CHUNK ACTION: CURRENT [' . implode('][', $current) . ']');

        switch ($current['l0']) {
            case DUPX_s3_iterator::STEP_START:
                $s3FuncsManager->initLog();
                $s3FuncsManager->initChunkLog($this->maxIteration, $this->timeOut, $this->throttling, $GLOBALS['DATABASE_PAGE_SIZE']);
                break;
            case DUPX_s3_iterator::STEP_CLEANUP_OPTIONS:
                DbCleanup::cleanupOptions();
                break;
            case DUPX_s3_iterator::STEP_CLEANUP_EXTREA:
                DbCleanup::cleanupExtra();
                break;
            case DUPX_s3_iterator::STEP_CLEANUP_PACKAGES:
                DbCleanup::cleanupPackages();
                break;
            case DUPX_s3_iterator::STEP_SEARCH_AND_REPLACE_INIT:
                break;
            case DUPX_s3_iterator::STEP_SEARCH_AND_REPLACE:
                DUPX_UpdateEngine::evaluateTableRows($current['l1'], $current['l2']);
                DUPX_UpdateEngine::commitAndSave();
                break;
            case DUPX_s3_iterator::STEP_REMOVE_MAINTENACE:
                $s3FuncsManager->removeMaintenanceMode();
                break;
            case DUPX_s3_iterator::STEP_CREATE_ADMIN:
                $s3FuncsManager->createNewAdminUser();
                break;
            case DUPX_s3_iterator::STEP_CONF_UPDATE:
                $s3FuncsManager->configFilesUpdate();
                break;
            case DUPX_s3_iterator::STEP_GEN_UPD:
                $s3FuncsManager->generalUpdate();
                break;
            case DUPX_s3_iterator::STEP_GEN_CLEAN:
                $s3FuncsManager->generalCleanup();
                $s3FuncsManager->forceLogoutOfAllUsers();
                $s3FuncsManager->duplicatorMigrationInfoSet();
                break;
            case DUPX_s3_iterator::STEP_NOTICE_TEST:
                $s3FuncsManager->checkForIndexHtml();
                $s3FuncsManager->noticeTest();
                break;
            case DUPX_s3_iterator::STEP_CLEANUP_TMP_FILES:
                $s3FuncsManager->cleanupTmpFiles();
                break;
            case DUPX_s3_iterator::STEP_SET_FILE_PERMS:
                $s3FuncsManager->setFilePermsission();
                break;
            case DUPX_s3_iterator::STEP_FINAL_REPORT_NOTICES:
                $s3FuncsManager->finalReportNotices();
                break;
            default:
        }

        /**
         * At each iteration save the status in case of exit with timeout
         */
        $this->saveData();
    }

    protected function getIterator()
    {
        return new DUPX_s3_iterator();
    }

    public function getStoredDataKey()
    {
        return $GLOBALS["CHUNK_DATA_FILE_PATH"];
    }

    /**
     * stop iteration without save data.
     * It is already saved every iteration.
     *
     * @return mixed
     */
    public function stop($saveData = false)
    {
        return parent::stop(false);
    }

    /**
     * load data from previous step if exists adn restore _POST and GLOBALS
     *
     * @param string $key file name
     *
     * @return mixed
     */
    protected function getStoredData($key)
    {
        if (($data = parent::getStoredData($key)) != null) {
            Log::info("CHUNK LOAD DATA: POSITION " . implode(' / ', $data['position']), 2);
            return $data['position'];
        } else {
            Log::info("CHUNK LOAD DATA: IS NULL ");
            return null;
        }
    }

    /**
     * delete stored data if exists
     */
    protected function deleteStoredData($key)
    {
        Log::info("CHUNK DELETE STORED DATA FILE:" . Log::v2str($key), 2);
        return parent::deleteStoredData($key);
    }

    /**
     * save data for next step
     */
    protected function saveStoredData($key, $data)
    {
        // store s3 func data
        $s3Funcs                          = DUPX_S3_Funcs::getInstance();
        $s3Funcs->report['chunk']         = 1;
        $s3Funcs->report['chunkPos']      = $data;
        $s3Funcs->report['pass']          = 0;
        $s3Funcs->report['progress_perc'] = $this->getProgressPerc();
        $s3Funcs->saveData();

        // managed output for timeout shutdown
        LogHandler::setShutdownReturn(LogHandler::SHUTDOWN_TIMEOUT, SnapJson::jsonEncode($s3Funcs->getJsonReport()));

        /**
         * store position post and globals
         */
        $gData = array(
            'position' => $data
        );

        Log::info("CHUNK SAVE DATA: POSITION " . implode(' / ', $data), 2);
        return parent::saveStoredData($key, $gData);
    }

    /**
     *
     * @return float progress in %
     */
    public function getProgressPerc()
    {
        $result   = 0;
        $position = $this->it->getPosition();
        $s3Func   = DUPX_S3_Funcs::getInstance();

        switch ($position['l0']) {
            case DUPX_s3_iterator::STEP_SEARCH_AND_REPLACE_INIT:
                $result = 5;
                break;
            case DUPX_s3_iterator::STEP_SEARCH_AND_REPLACE:
                $lowLimit      = 10;
                $higthLimit    = 90;
                $stepDelta     = $higthLimit - $lowLimit;
                $tables        = DUPX_DB_Tables::getInstance()->getReplaceTablesNames();
                $tableDelta    = $stepDelta / (count($tables) + 1);
                $singePagePerc = $tableDelta / ($s3Func->cTableParams['pages'] + 1);
                $result        = round($lowLimit + ($tableDelta * (int) $position['l1']) + ($singePagePerc * (int) $position['l2']), 2);
                break;
            case DUPX_s3_iterator::STEP_REMOVE_MAINTENACE:
                $result = 90;
                break;
            case DUPX_s3_iterator::STEP_CREATE_ADMIN:
                $result = 92;
                break;
            case DUPX_s3_iterator::STEP_CONF_UPDATE:
                $result = 93;
                break;
            case DUPX_s3_iterator::STEP_GEN_UPD:
                $result = 94;
                break;
            case DUPX_s3_iterator::STEP_GEN_CLEAN:
                $result = 95;
                break;
            case DUPX_s3_iterator::STEP_NOTICE_TEST:
                $result = 96;
                break;
            case DUPX_s3_iterator::STEP_CLEANUP_TMP_FILES:
                $result = 97;
                break;
            case DUPX_s3_iterator::STEP_SET_FILE_PERMS:
                $result = 98;
                break;
            case DUPX_s3_iterator::STEP_FINAL_REPORT_NOTICES:
                $result = 100;
                break;
            default:
        }
        return $result;
    }
}
