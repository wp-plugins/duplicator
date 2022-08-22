<?php

namespace Duplicator\Installer\Core\Deploy\Files;

use DUP_Extraction;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapWP;
use DUPX_ArchiveConfig;
use DUPX_InstallerState;
use DUPX_Package;
use DUPX_Security;
use DUPX_Server;
use Exception;

class FilterMng
{
    /**
     * Return filter (folder/files) for extraction
     *
     * @param string $subFolderArchive sub folder archive
     *
     * @return Filters
     */
    public static function getExtractFilters($subFolderArchive)
    {
        Log::info("INITIALIZE FILTERS");
        $paramsManager = PrmMng::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        $result = new Filters();

        $filterFilesChildOfFolders  = array();
        $acceptFolderOfFilterChilds = array();

        $result->addFile($archiveConfig->installer_backup_name);
        $result->addDir(ltrim($subFolderArchive . '/' . DUP_Extraction::DUP_FOLDER_NAME, '/'));

        if (self::filterWpCoreFiles()) {
            $relAbsPath      = $archiveConfig->getRelativePathsInArchive('abs');
            $relAbsPath     .= (strlen($relAbsPath) > 0 ? '/' : '');
            $rootWpCoreItems = SnapWP::getWpCoreFilesListInFolder();
            foreach ($rootWpCoreItems['dirs'] as $name) {
                $result->addDir($relAbsPath . $name);
            }

            foreach ($rootWpCoreItems['files'] as $name) {
                $result->addFile($relAbsPath . $name);
            }
        }

        if (self::filterAllExceptPlugingThemesMedia()) {
            Log::info('FILTER ALL EXCEPT MEDIA');
            $filterFilesChildOfFolders[] = $archiveConfig->getRelativePathsInArchive('home');
            $filterFilesChildOfFolders[] = $archiveConfig->getRelativePathsInArchive('wpcontent');

            $acceptFolderOfFilterChilds[] = $archiveConfig->getRelativePathsInArchive('uploads');
            $acceptFolderOfFilterChilds[] = $archiveConfig->getRelativePathsInArchive('wpcontent') . '/blogs.dir';
            $acceptFolderOfFilterChilds[] = $archiveConfig->getRelativePathsInArchive('plugins');
            $acceptFolderOfFilterChilds[] = $archiveConfig->getRelativePathsInArchive('muplugins');
            $acceptFolderOfFilterChilds[] = $archiveConfig->getRelativePathsInArchive('themes');
        }

        if (self::filterExistsPlugins()) {
            $newPluginDir = $paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW);
            if (is_dir($newPluginDir)) {
                $relPlugPath  = $archiveConfig->getRelativePathsInArchive('plugins');
                $relPlugPath .= (strlen($relPlugPath) > 0 ? '/' : '');

                SnapIO::regexGlobCallback($newPluginDir, function ($item) use ($relPlugPath, &$result) {
                    if (is_dir($item)) {
                        $result->addDir($relPlugPath . pathinfo($item, PATHINFO_BASENAME));
                    } else {
                        $result->addFile($relPlugPath . pathinfo($item, PATHINFO_BASENAME));
                    }
                }, array());
            }

            $newMuPluginDir = $paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW);
            if (is_dir($newMuPluginDir)) {
                $relMuPlugPath  = $archiveConfig->getRelativePathsInArchive('muplugins');
                $relMuPlugPath .= (strlen($relMuPlugPath) > 0 ? '/' : '');

                SnapIO::regexGlobCallback($newMuPluginDir, function ($item) use ($relMuPlugPath, &$result) {
                    if (is_dir($item)) {
                        $result->addDir($relMuPlugPath . pathinfo($item, PATHINFO_BASENAME));
                    } else {
                        $result->addFile($relMuPlugPath . pathinfo($item, PATHINFO_BASENAME));
                    }
                }, array());
            }

            $newWpContentDir = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/';
            if (is_dir($newWpContentDir)) {
                $relContentPath  = $archiveConfig->getRelativePathsInArchive('wpcontent');
                $relContentPath .= (strlen($relContentPath) > 0 ? '/' : '');
                foreach (SnapWP::getDropinsPluginsNames() as $dropinsPlugin) {
                    if (file_exists($newWpContentDir . $dropinsPlugin)) {
                        $result->addFile($relContentPath . $dropinsPlugin);
                    }
                }
            }
        }

        if (self::filterExistsThemes()) {
            $newThemesDir = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/themes';
            if (is_dir($newThemesDir)) {
                $relThemesPath  = $archiveConfig->getRelativePathsInArchive('themes');
                $relThemesPath .= (strlen($relContentPath) > 0 ? '/' : '');

                SnapIO::regexGlobCallback($newThemesDir, function ($item) use ($relThemesPath, &$result) {
                    if (is_dir($item)) {
                        $result->addDir($relThemesPath . pathinfo($item, PATHINFO_BASENAME));
                    } else {
                        $result->addFile($relThemesPath . pathinfo($item, PATHINFO_BASENAME));
                    }
                }, array());
            }
        }

        self::filterAllChildsOfPathExcept($result, $filterFilesChildOfFolders, $acceptFolderOfFilterChilds);
        $result->optmizeFilters();

        return $result;
    }

    /**
     * Create filters for remove files
     *
     * @param Filters|null $baseFilters base extraction filters
     *
     * @return Filters
     */
    public static function getRemoveFilters(Filters $baseFilters = null)
    {
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $security      = DUPX_Security::getInstance();

        $result = new Filters();
        if (!is_null($baseFilters)) {
            // convert all relative path from archive to absolute destination path
            foreach ($baseFilters->getDirs() as $dir) {
                $result->addDir($archiveConfig->destFileFromArchiveName($dir));
            }
            foreach ($baseFilters->getDirsWithoutChilds() as $dir) {
                $result->addDir($archiveConfig->destFileFromArchiveName($dir), true);
            }
            foreach ($baseFilters->getFiles() as $file) {
                $result->addFile($archiveConfig->destFileFromArchiveName($file));
            }
        }

        $result->addFile($security->getArchivePath());
        $result->addFile($security->getBootFilePath());
        $result->addFile($security->getBootLogFile());

        $result->addDir(DUPX_INIT);
        foreach (DUPX_Server::getWpAddonsSiteLists() as $addonPath) {
            $result->addDir($addonPath);
        }

        $result->optmizeFilters();

        return $result;
    }

    /**
     * This function update filters from $filterFilesChildOfFolders and  $acceptFolders values
     *
     * @param Filters  $filters                   Filters
     * @param string[] $filterFilesChildOfFolders Filter contents of these paths
     * @param string[] $acceptFolders             Folders not to filtered
     *
     * @return void
     *
     */
    private static function filterAllChildsOfPathExcept(Filters $filters, $filterFilesChildOfFolders, $acceptFolders = array())
    {
        //No sense adding filters if not folders specified
        if (!is_array($filterFilesChildOfFolders) || count($filterFilesChildOfFolders) == 0) {
            return;
        }

        $acceptFolders             = array_unique($acceptFolders);
        $filterFilesChildOfFolders = array_unique($filterFilesChildOfFolders);

        Log::info('ACCEPT FOLDERS ' . Log::v2str($acceptFolders), Log::LV_DETAILED);
        Log::info('CHILDS FOLDERS ' . Log::v2str($filterFilesChildOfFolders), Log::LV_DETAILED);

        DUPX_Package::foreachDirCallback(function ($info) use ($acceptFolders, $filterFilesChildOfFolders, &$filters) {
            if (in_array($info->p, $filterFilesChildOfFolders)) {
                return;
            }

            foreach ($acceptFolders as $acceptFolder) {
                if (SnapIO::isChildPath($info->p, $acceptFolder, true)) {
                    return;
                }
            }

            $parentFolder = SnapIO::getRelativeDirname($info->p);

            if (in_array($parentFolder, $filterFilesChildOfFolders)) {
                $filters->addDir($info->p);
            }
        });

        DUPX_Package::foreachFileCallback(function ($info) use ($filterFilesChildOfFolders, &$filters) {
            $parentFolder = SnapIO::getRelativeDirname($info->p);
            if (in_array($parentFolder, $filterFilesChildOfFolders)) {
                $filters->addFile($info->p);
            }
        });

        Log::info('FILTERS RESULT ' . Log::v2str($filters), log::LV_DETAILED);
    }

    /**
     *
     * @return boolean
     * @throws Exception
     */
    public static function filterWpCoreFiles()
    {
        switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES)) {
            case DUP_Extraction::FILTER_NONE:
                return false;
            case DUP_Extraction::FILTER_SKIP_WP_CORE:
            case DUP_Extraction::FILTER_SKIP_CORE_PLUG_THEMES:
            case DUP_Extraction::FILTER_ONLY_MEDIA_PLUG_THEMES:
                return true;
            default:
                throw new Exception('Unknown filter type');
        }
    }

    /**
     *
     * @return boolean
     * @throws Exception
     */
    protected static function filterExistsPlugins()
    {
        switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES)) {
            case DUP_Extraction::FILTER_NONE:
            case DUP_Extraction::FILTER_SKIP_WP_CORE:
                return false;
            case DUP_Extraction::FILTER_SKIP_CORE_PLUG_THEMES:
            case DUP_Extraction::FILTER_ONLY_MEDIA_PLUG_THEMES:
                return true;
            default:
                throw new Exception('Unknown filter type');
        }
    }

    /**
     *
     * @return boolean
     * @throws Exception
     */
    protected static function filterExistsThemes()
    {
        switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES)) {
            case DUP_Extraction::FILTER_NONE:
            case DUP_Extraction::FILTER_SKIP_WP_CORE:
                return false;
            case DUP_Extraction::FILTER_SKIP_CORE_PLUG_THEMES:
            case DUP_Extraction::FILTER_ONLY_MEDIA_PLUG_THEMES:
                return true;
            default:
                throw new Exception('Unknown filter type');
        }
    }

    /**
     *
     * @return boolean
     * @throws Exception
     */
    protected static function filterAllExceptPlugingThemesMedia()
    {
        switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES)) {
            case DUP_Extraction::FILTER_NONE:
            case DUP_Extraction::FILTER_SKIP_WP_CORE:
            case DUP_Extraction::FILTER_SKIP_CORE_PLUG_THEMES:
                return false;
            case DUP_Extraction::FILTER_ONLY_MEDIA_PLUG_THEMES:
                return true;
            default:
                throw new Exception('Unknown filter type');
        }
    }
}
