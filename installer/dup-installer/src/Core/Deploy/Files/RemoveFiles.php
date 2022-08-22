<?php

namespace Duplicator\Installer\Core\Deploy\Files;

use DUP_Extraction;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapWP;
use DUPX_ArchiveConfig;
use DUPX_Custom_Host_Manager;
use DUPX_InstallerState;
use DUPX_NOTICE_ITEM;
use DUPX_NOTICE_MANAGER;
use Error;
use Exception;

class RemoveFiles
{
    /** @var Filters */
    protected $removeFilters = null;

    /**
     * Class contructor
     *
     * @param Filters $filters fles filters
     */
    public function __construct(Filters $filters)
    {
        $this->removeFilters = $filters;
    }

    /**
     * Remove file if action is enableds
     *
     * @return void
     */
    public function remove()
    {
        $paramsManager = PrmMng::getInstance();

        switch ($paramsManager->getValue(PrmMng::PARAM_ARCHIVE_ACTION)) {
            case DUP_Extraction::ACTION_REMOVE_ALL_FILES:
                $this->removeAllFiles();
                break;
            case DUP_Extraction::ACTION_REMOVE_WP_FILES:
                $this->removeWpFiles();
                break;
            case DUP_Extraction::ACTION_REMOVE_UPLOADS:
                $this->removeUploads();
                break;
            case DUP_Extraction::ACTION_DO_NOTHING:
                break;
            default:
                throw new Exception('Invalid engine action ' . $paramsManager->getValue(PrmMng::PARAM_ARCHIVE_ACTION));
        }
    }

    /**
     * This function remove files before extraction
     *
     * @param string[] $folders Folders lists
     *
     * @return void
     */
    protected function removeFiles($folders = array())
    {
        Log::info('REMOVE FILES');

        $excludeFiles = array_map(function ($value) {
            return '/^' . preg_quote($value, '/') . '$/';
        }, $this->removeFilters->getFiles());

        $excludeFolders   = array_map(function ($value) {
            return '/^' . preg_quote($value, '/') . '(?:\/.*)?$/';
        }, $this->removeFilters->getDirs());
        $excludeFolders[] =  '/.+\/backups-dup-(lite|pro)$/';

        $excludeDirsWithoutChilds = $this->removeFilters->getDirsWithoutChilds();

        foreach ($folders as $folder) {
            Log::info('REMOVE FOLDER ' . Log::v2str($folder));
            SnapIO::regexGlobCallback($folder, function ($path) use ($excludeDirsWithoutChilds) {
                foreach ($excludeDirsWithoutChilds as $excludePath) {
                    if (SnapIO::isChildPath($excludePath, $path)) {
                        return;
                    }
                }

                $result = (is_dir($path) ? rmdir($path) : unlink($path));
                if ($result == false) {
                    $lastError = error_get_last();
                    $message   = (isset($lastError['message']) ? $lastError['message'] : 'Couldn\'t remove file');
                    RemoveFiles::reportRemoveNotices($path, $message);
                }
            }, array(
                'regexFile'     => $excludeFiles,
                'regexFolder'   => $excludeFolders,
                'checkFullPath' => true,
                'recursive'     => true,
                'invert'        => true,
                'childFirst'    => true
            ));
        }
    }

    /**
     * Remove worpdress core files
     *
     * @return void
     */
    protected function removeWpFiles()
    {
        try {
            Log::info('REMOVE WP FILES');
            Log::resetTime(Log::LV_DEFAULT, false);

            $paramsManager = PrmMng::getInstance();
            $absDir        = SnapIO::safePathTrailingslashit($paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW));
            if (!is_dir($absDir) || !is_readable($absDir)) {
                return false;
            }

            $removeFolders = array();

            if (!FilterMng::filterWpCoreFiles() && ($dh = opendir($absDir))) {
                while (($elem = readdir($dh)) !== false) {
                    if ($elem === '.' || $elem === '..') {
                        continue;
                    }

                    if (SnapWP::isWpCore($elem, SnapWP::PATH_RELATIVE)) {
                        $fullPath = $absDir . $elem;
                        if (is_dir($fullPath)) {
                            $removeFolders[] = $fullPath;
                        } else {
                            if (is_writable($fullPath)) {
                                unlink($fullPath);
                            }
                        }
                    }
                }
                closedir($dh);
            }

            $removeFolders[] = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW);
            $removeFolders[] = $paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);
            $removeFolders[] = $paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW);
            $removeFolders[] = $paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW);

            $this->removeFiles(array_unique($removeFolders));
            Log::logTime('FOLDERS REMOVED', Log::LV_DEFAULT, false);
        } catch (Exception $e) {
            Log::logException($e);
        } catch (Error $e) {
            Log::logException($e);
        }
    }

    /**
     * Remove ony uploads files
     *
     * @return void
     */
    protected function removeUploads()
    {
        try {
            Log::info('REMOVE UPLOADS FILES');
            Log::resetTime(Log::LV_DEFAULT, false);

            $paramsManager = PrmMng::getInstance();

            $removeFolders   = array();
            $removeFolders[] = $paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);

            $this->removeFiles(array_unique($removeFolders));
            Log::logTime('FOLDERS REMOVED', Log::LV_DEFAULT, false);
        } catch (Exception $e) {
            Log::logException($e);
        } catch (Error $e) {
            Log::logException($e);
        }
    }

    /**
     * Remove all files before extraction
     *
     * @return void
     */
    protected function removeAllFiles()
    {
        try {
            Log::info('REMOVE ALL FILES');
            Log::resetTime(Log::LV_DEFAULT, false);
            $pathsMapping = DUPX_ArchiveConfig::getInstance()->getPathsMapping();
            $folders      = is_string($pathsMapping) ? array($pathsMapping) : array_values($pathsMapping);

            $this->removeFiles($folders);
            Log::logTime('FOLDERS REMOVED', Log::LV_DEFAULT, false);
        } catch (Exception $e) {
            Log::logException($e);
        } catch (Error $e) {
            Log::logException($e);
        }
    }


    /**
     *
     * @param string $fileName     package relative path
     * @param string $errorMessage error message
     *
     * @return void
     */
    public static function reportRemoveNotices($fileName, $errorMessage)
    {
        if (DUPX_Custom_Host_Manager::getInstance()->skipWarningExtractionForManaged($fileName)) {
            // @todo skip warning for managed hostiong (it's a temp solution)
            return;
        }

        Log::info('Remove ' . $fileName . ' error message: ' . $errorMessage);
        if (is_dir($fileName)) {
            // Skip warning message for folders
            return;
        }

        $nManager = DUPX_NOTICE_MANAGER::getInstance();

        if (SnapWP::isWpCore($fileName, SnapWP::PATH_RELATIVE)) {
            Log::info("FILE CORE REMOVE ERROR: {$fileName} | MSG:" . $errorMessage);
            $shortMsg  = 'Can\'t remove wp core files';
            $errLevel  = DUPX_NOTICE_ITEM::CRITICAL;
            $idManager = 'wp-remove-error-file-core';
        } else {
            Log::info("FILE REMOVE ERROR: {$fileName} | MSG:" . $errorMessage);
            $shortMsg  = 'Can\'t remove files';
            $errLevel  = DUPX_NOTICE_ITEM::HARD_WARNING;
            $idManager = 'wp-remove-error-file-no-core';
        }

        $longMsg = 'FILE: <b>' . htmlspecialchars($fileName) . '</b><br>Message: ' . htmlspecialchars($errorMessage) . '<br><br>';

        $nManager->addBothNextAndFinalReportNotice(
            array(
                'shortMsg'    => $shortMsg,
                'longMsg'     => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                'level'       => $errLevel,
                'sections'    => array('files')
            ),
            DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND,
            $idManager
        );
    }
}
