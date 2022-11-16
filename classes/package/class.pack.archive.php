<?php

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapOS;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapWP;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
// Exit if accessed directly
if (!defined('DUPLICATOR_VERSION')) {
    exit;
}

require_once(DUPLICATOR_PLUGIN_PATH . 'classes/package/duparchive/class.pack.archive.duparchive.php');
require_once(DUPLICATOR_PLUGIN_PATH . 'classes/package/class.pack.archive.file.list.php');
require_once(DUPLICATOR_PLUGIN_PATH . 'classes/package/class.pack.archive.filters.php');
require_once(DUPLICATOR_PLUGIN_PATH . 'classes/package/class.pack.archive.zip.php');
require_once(DUPLICATOR_PLUGIN_PATH . 'lib/forceutf8/Encoding.php');
/**
 * Class for handling archive setup and build process
 *
 * Standard: PSR-2 (almost)
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package DUP
 * @subpackage classes/package
 * @copyright (c) 2017, Snapcreek LLC
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 *
 */
class DUP_Archive
{
    const DIRS_LIST_FILE_NAME_SUFFIX  = '_dirs.txt';
    const FILES_LIST_FILE_NAME_SUFFIX = '_files.txt';

    //PUBLIC
    public $FilterDirs;
    public $FilterFiles;
    public $FilterExts;
    public $FilterDirsAll  = array();
    public $FilterFilesAll = array();
    public $FilterExtsAll  = array();
    public $FilterOn;
    public $ExportOnlyDB;
    public $File;
    public $Format;
    public $PackDir    = '';
    public $Size       = 0;
    public $Dirs       = array();
    public $dirsCount  = 0;
    public $Files      = array();
    public $filesCount = 0;
    /** @var DUP_Archive_Filter_Info */
    public $FilterInfo     = null;
    public $RecursiveLinks = array();
    public $file_count     = -1;
    /** @var DUP_Package */
    protected $Package;
    private $tmpFilterDirsAll   = array();
    private $wpCorePaths        = array();
    private $wpCoreExactPaths   = array();
    private $relativeFiltersDir = array();

    /** @var DUP_Archive_File_List */
    private $listFileObj = null;
    /** @var DUP_Archive_File_List */
    private $listDirObj = null;

    /**
     * Class constructor
     *
     * @param DUP_Package $package
     */
    public function __construct(DUP_Package $package)
    {
        $this->Package      = $package;
        $this->FilterOn     = false;
        $this->ExportOnlyDB = false;
        $this->FilterInfo   = new DUP_Archive_Filter_Info();
        $this->PackDir      = $this->getTargetRootPath();

        $paths                    = self::getArchiveListPaths();
        $this->wpCorePaths[]      = $paths['abs'] . '/wp-admin';
        $this->wpCorePaths[]      = $paths['abs'] . '/wp-includes';
        $this->wpCorePaths[]      = $paths['wpcontent'] . '/languages';
        $this->wpCoreExactPaths[] = $paths['home'];
        $this->wpCoreExactPaths[] = $paths['abs'];
        $this->wpCoreExactPaths[] = $paths['wpcontent'];
        $this->wpCoreExactPaths[] = $paths['uploads'];
        $this->wpCoreExactPaths[] = $paths['plugins'];
        $this->wpCoreExactPaths[] = $paths['muplugins'];
        $this->wpCoreExactPaths[] = $paths['themes'];

        $this->relativeFiltersDir = array(DUP_Settings::getSsdirTmpPath(), 'backups-dup-pro');
    }

    /**
     * Builds the archive based on the archive type
     *
     * @param obj $package The package object that started this process
     *
     * @return null
     */
    public function build($package, $rethrow_exception = false)
    {
        DUP_LOG::trace("b1");
        $this->Package = $package;
        if (!isset($this->PackDir) && !is_dir($this->PackDir)) {
            throw new Exception("The 'PackDir' property must be a valid directory.");
        }
        if (!isset($this->File)) {
            throw new Exception("A 'File' property must be set.");
        }

        DUP_LOG::trace("b2");
        $completed = false;

        switch ($this->Format) {
            case 'TAR':
                break;
            case 'TAR-GZIP':
                break;
            case 'DAF':
                $completed = DUP_DupArchive::create($this, $this->Package->BuildProgress, $this->Package);
                $this->Package->Update();
                break;
            default:
                if (class_exists('ZipArchive')) {
                    $this->Format = 'ZIP';
                    DUP_Zip::create($this, $this->Package->BuildProgress);
                    $completed = true;
                }

                break;
        }

        DUP_LOG::Trace("Completed build or build thread");
        if ($this->Package->BuildProgress === null) {
            // Zip path
            DUP_LOG::Trace("Completed Zip");
            $storePath  = DUP_Settings::getSsdirTmpPath() . "/{$this->File}";
            $this->Size = @filesize($storePath);
            $this->Package->setStatus(DUP_PackageStatus::ARCDONE);
        } elseif ($completed) {
            // Completed DupArchive path
            DUP_LOG::Trace("Completed DupArchive build");
            if ($this->Package->BuildProgress->failed) {
                DUP_LOG::Trace("Error building DupArchive");
                $this->Package->setStatus(DUP_PackageStatus::ERROR);
            } else {
                $filepath   = DUP_Settings::getSsdirTmpPath() . "/{$this->File}";
                $this->Size = @filesize($filepath);
                $this->Package->setStatus(DUP_PackageStatus::ARCDONE);
                DUP_LOG::Trace("Done building archive");
            }
        } else {
            DUP_Log::trace("DupArchive chunk done but package not completed yet");
        }
    }

    /**
     *
     * @return int return  DUP_Archive_Build_Mode
     */
    public function getBuildMode()
    {
        switch ($this->Format) {
            case 'TAR':
                break;
            case 'TAR-GZIP':
                break;
            case 'DAF':
                return DUP_Archive_Build_Mode::DupArchive;
            default:
                if (class_exists('ZipArchive')) {
                    return DUP_Archive_Build_Mode::ZipArchive;
                } else {
                    return DUP_Archive_Build_Mode::Unconfigured;
                }

                break;
        }
    }

    /**
     * Initializes the file list handles. Handles are set-up as properties for
     * performance improvement. Otherwise each handle would be opened and closed
     * with each path added.
     */
    private function initFileListHandles()
    {
        DUP_Log::trace('Inif list files');
        if (is_null($this->listDirObj)) {
            $this->listDirObj = new DUP_Archive_File_List(DUP_Settings::getSsdirTmpPath() . '/' . $this->Package->get_dirs_list_filename());
        }
        if (is_null($this->listFileObj)) {
            $this->listFileObj = new DUP_Archive_File_List(DUP_Settings::getSsdirTmpPath() . '/' . $this->Package->get_files_list_filename());
        }
        $this->listDirObj->open(true);
        $this->listFileObj->open(true);
    }

    /**
     * Closes file and dir list handles
     */
    private function closeFileListHandles()
    {
        $this->listDirObj->close();
        $this->listFileObj->close();
    }

    /**
     *  Builds a list of files and directories to be included in the archive
     *
     *  Get the directory size recursively, but don't calc the snapshot directory, exclusion directories
     *  @link http://msdn.microsoft.com/en-us/library/aa365247%28VS.85%29.aspx Windows filename restrictions
     *
     *  @return obj Returns a DUP_Archive object
     */
    public function getScannerData()
    {
        $this->initFileListHandles();
        $this->createFilterInfo();
        $rootPath = $this->getTargetRootPath();
        if (strlen($rootPath) == 0) {
            $rootPath = '/';
        }

        $this->RecursiveLinks = array();
        // If the root directory is a filter then skip it all
        if (in_array($rootPath, $this->FilterDirsAll) || $this->Package->Archive->ExportOnlyDB) {
            $this->Dirs       = array();
            $this->dirsCount  = 0;
            $this->Files      = array();
            $this->filesCount = 0;
        } else {
            $mainSize    = 0;
            $mainNodes   = 0;
            $pathsToScan = self::getScanPaths();
            foreach ($pathsToScan as $path) {
                DUP_Log::trace('START SCAN PATH: ' . $path);
                $relativePath = SnapIO::getRelativePath($path, $rootPath);
                if (($result = $this->getFileLists($path, $relativePath)) != false) {
                    $this->addToDirList($path, $relativePath, $result['size'], $result['nodes'] + 1);
                    $mainSize  += $result['size'];
                    $mainNodes += $result['nodes'] + 1;
                } else {
                    DUP_Log::trace('Can\'t scan the folder ' . $rootPath);
                }
            }

            if (!in_array($rootPath, $pathsToScan)) {
                $this->addToDirList($rootPath, '', $mainSize, $mainNodes + 1);
            }

            $this->setTreeFilters();
        }

        $this->closeFileListHandles();

        $this->FilterDirsAll  = array_merge($this->FilterDirsAll, $this->FilterInfo->Dirs->Unreadable);
        $this->FilterFilesAll = array_merge($this->FilterFilesAll, $this->FilterInfo->Files->Unreadable);
        sort($this->FilterDirsAll);
        return $this;
    }

    /**
     * Save any property of this class through reflection
     *
     * @param $property     A valid public property in this class
     * @param $value        The value for the new dynamic property
     *
     * @return bool Returns true if the value has changed.
     */
    public function saveActiveItem($package, $property, $value)
    {
        $package         = DUP_Package::getActive();
        $reflectionClass = new ReflectionClass($package->Archive);
        $reflectionClass->getProperty($property)->setValue($package->Archive, $value);
        return update_option(DUP_Package::OPT_ACTIVE, $package);
    }

    /**
     *  Properly creates the directory filter list that is used for filtering directories
     *
     * @param string $dirs A semi-colon list of dir paths
     *  /path1_/path/;/path1_/path2/;
     *
     * @returns string A cleaned up list of directory filters
     * @return string
     */
    public function parseDirectoryFilter($dirs = "")
    {
        $filters     = "";
        $dir_array   = array_unique(explode(";", $dirs));
        $clean_array = array();
        foreach ($dir_array as $val) {
            $val = SnapIO::safePathUntrailingslashit(SnapUtil::sanitizeNSCharsNewlineTrim($val));
            if (strlen($val) >= 2 && is_dir($val)) {
                $clean_array[] = $val;
            }
        }

        if (count($clean_array)) {
            $clean_array = array_unique($clean_array);
            sort($clean_array);
            $filters = implode(';', $clean_array) . ';';
        }
        return $filters;
    }

    /**
     *  Properly creates the file filter list that is used for filtering files
     *
     * @param string $files A semi-colon list of file paths
     *  /path1_/path/file1.ext;/path1_/path2/file2.ext;
     *
     * @returns string A cleaned up list of file filters
     * @return string
     */
    public function parseFileFilter($files = "")
    {
        $filters     = "";
        $file_array  = array_unique(explode(";", $files));
        $clean_array = array();
        foreach ($file_array as $val) {
            $val = SnapIO::safePathUntrailingslashit(SnapUtil::sanitizeNSCharsNewlineTrim($val));
            if (strlen($val) >= 2 && file_exists($val)) {
                $clean_array[] = $val;
            }
        }

        if (count($clean_array)) {
            $clean_array = array_unique($clean_array);
            sort($clean_array);
            $filters = implode(';', $clean_array) . ';';
        }
        return $filters;
    }

    /**
     *  Properly creates the extension filter list that is used for filtering extensions
     *
     *  @param string $dirs A semi-colon list of dir paths
     *  .jpg;.zip;.gif;
     *
     *  @returns string A cleaned up list of extension filters
     */
    public function parseExtensionFilter($extensions = "")
    {
        $filter_exts = "";
        if (strlen($extensions) >= 1 && $extensions != ";") {
            $filter_exts = str_replace(array(' ', '.'), '', $extensions);
            $filter_exts = str_replace(",", ";", $filter_exts);
            $filter_exts = DUP_Util::appendOnce($extensions, ";");
        }
        return $filter_exts;
    }

    /**
     * Creates the filter info setup data used for filtering the archive
     *
     * @return null
     */
    private function createFilterInfo()
    {
        //FILTER: INSTANCE ITEMS
        //Add the items generated at create time
        if ($this->FilterOn) {
            $this->FilterInfo->Dirs->Instance  = array_map('DUP_Util::safePath', explode(";", $this->FilterDirs, -1));
            $this->FilterInfo->Files->Instance = array_map('DUP_Util::safePath', explode(";", $this->FilterFiles, -1));
            $this->FilterInfo->Exts->Instance  = explode(";", $this->FilterExts, -1);
        }

        //FILTER: CORE ITMES
        //Filters Duplicator free packages & All pro local directories
        $wp_root                      = duplicator_get_abs_path();
        $upload_dir                   = wp_upload_dir();
        $upload_dir                   = isset($upload_dir['basedir']) ? basename($upload_dir['basedir']) : 'uploads';
        $wp_content                   = str_replace("\\", "/", WP_CONTENT_DIR);
        $wp_content_upload            = "{$wp_content}/{$upload_dir}";
        $this->FilterInfo->Dirs->Core = array(
            //WP-ROOT
            DUP_Settings::getSsdirPathLegacy(),
            DUP_Settings::getSsdirPathWpCont(),
            $wp_root . '/.opcache',
            //WP-CONTENT
            $wp_content . '/backups-dup-pro',
            $wp_content . '/ai1wm-backups',
            $wp_content . '/backupwordpress',
            $wp_content . '/content/cache',
            $wp_content . '/contents/cache',
            $wp_content . '/infinitewp/backups',
            $wp_content . '/managewp/backups',
            $wp_content . '/old-cache',
            $wp_content . '/plugins/all-in-one-wp-migration/storage',
            $wp_content . '/updraft',
            $wp_content . '/wishlist-backup',
            $wp_content . '/wfcache',
            $wp_content . '/bps-backup', // BulletProof Security backup folder
            $wp_content . '/cache',
            //WP-CONTENT-UPLOADS
            $wp_content_upload . '/aiowps_backups',
            $wp_content_upload . '/backupbuddy_temp',
            $wp_content_upload . '/backupbuddy_backups',
            $wp_content_upload . '/ithemes-security/backups',
            $wp_content_upload . '/mainwp/backup',
            $wp_content_upload . '/pb_backupbuddy',
            $wp_content_upload . '/snapshots',
            $wp_content_upload . '/sucuri',
            $wp_content_upload . '/wp-clone',
            $wp_content_upload . '/wp_all_backup',
            $wp_content_upload . '/wpbackitup_backups'
        );
        if (class_exists('BackWPup')) {
            $upload_dir                     = wp_upload_dir(null, false, true);
            $this->FilterInfo->Dirs->Core[] = trailingslashit(str_replace(
                '\\',
                '/',
                $upload_dir['basedir']
            )) . 'backwpup-' . BackWPup::get_plugin_data('hash') . '-backups/';
            $backwpup_cfg_logfolder         = get_site_option('backwpup_cfg_logfolder');
            if (false !== $backwpup_cfg_logfolder) {
                $this->FilterInfo->Dirs->Core[] = $wp_content . '/' . $backwpup_cfg_logfolder;
            }
        }
        if ($GLOBALS['DUPLICATOR_GLOBAL_FILE_FILTERS_ON']) {
            $duplicator_global_file_filters  = apply_filters('duplicator_global_file_filters', $GLOBALS['DUPLICATOR_GLOBAL_FILE_FILTERS']);
            $this->FilterInfo->Files->Global = $duplicator_global_file_filters;
        }

        $this->FilterDirsAll    = array_merge($this->FilterInfo->Dirs->Instance, $this->FilterInfo->Dirs->Core);
        $this->FilterExtsAll    = array_merge($this->FilterInfo->Exts->Instance, $this->FilterInfo->Exts->Core);
        $this->FilterFilesAll   = array_merge($this->FilterInfo->Files->Instance, $this->FilterInfo->Files->Global);
        $abs_path               = duplicator_get_abs_path();
        $this->FilterFilesAll[] = $abs_path . '/.htaccess';
        $this->FilterFilesAll[] = $abs_path . '/web.config';
        $this->FilterFilesAll[] = $abs_path . '/wp-config.php';
        $this->tmpFilterDirsAll = $this->FilterDirsAll;
        //PHP 5 on windows decode patch
        if (!DUP_Util::$PHP7_plus && DUP_Util::isWindows()) {
            foreach ($this->tmpFilterDirsAll as $key => $value) {
                if (preg_match('/[^\x20-\x7f]/', $value)) {
                    $this->tmpFilterDirsAll[$key] = utf8_decode($value);
                }
            }
        }
    }

    /**
     * Recursive function to get all directories in a wp install
     *
     * @notes:
     *  Older PHP logic which is more stable on older version of PHP
     *  NOTE RecursiveIteratorIterator is problematic on some systems issues include:
     *  - error 'too many files open' for recursion
     *  - $file->getExtension() is not reliable as it silently fails at least in php 5.2.17
     *  - issues with when a file has a permission such as 705 and trying to get info (had to fallback to path-info)
     *  - basic conclusion wait on the SPL libs until after php 5.4 is a requirments
     *  - tight recursive loop use caution for speed
     *
     * @return array    Returns an array of directories to include in the archive
     */
    private function getFileLists($path, $relativePath)
    {
        if (($handle = opendir((strlen($path) === 0 ? '/' : $path))) === false) {
            DUP_Log::trace('Can\'t open dir: ' . $path);
            return false;
        }

        $result              = array(
            'size'  => 0,
            'nodes' => 1
        );
        $trimmedRelativePath = ltrim($relativePath . '/', '/');
        while (($currentName         = readdir($handle)) !== false) {
            if ($currentName == '.' || $currentName == '..') {
                continue;
            }
            $currentPath = $path . '/' . $currentName;
            //DUP_Log::trace(' ANALIZE PATH: '.$currentPath);

            if (is_dir($currentPath)) {
                DUP_Log::trace(' Scan dir: ' . $currentPath);
                $add = true;
                if (is_link($currentPath)) {
                    //Get real path of link
                    //trailing slash is essential!
                    $realPath = SnapIO::safePathTrailingslashit($currentPath, true);
                    //if $currentPath starts with $realPath and is link
                    //=> $currentPath is located in $realPath and points back to it
                    if (strpos($currentPath, $realPath) === 0) {
                        $this->RecursiveLinks[] = $currentPath;
                        continue;
                    }
                }

                if (in_array($currentName, $this->relativeFiltersDir)) {
                    $add = false;
                } else {
                    foreach ($this->tmpFilterDirsAll as $filteredDir) {
                        if (SnapIO::isChildPath($currentPath, $filteredDir)) {
                            $add = false;
                            break;
                        }
                    }
                }

                if ($add) {
                    $childResult      = $this->getFileLists($currentPath, $trimmedRelativePath . $currentName);
                    $result['size']  += $childResult['size'];
                    $result['nodes'] += $childResult['nodes'];
                    $this->addToDirList($currentPath, $trimmedRelativePath . $currentName, $childResult['size'], $childResult['nodes']);
                }
            } else {
                // Note: The last clause is present to perform just a filename check
                if (
                    !(in_array(pathinfo($currentName, PATHINFO_EXTENSION), $this->FilterExtsAll) ||
                    in_array($currentPath, $this->FilterFilesAll) ||
                    in_array($currentName, $this->FilterFilesAll))
                ) {
                    $fileSize         = (int) @filesize($currentPath);
                    $result['size']  += $fileSize;
                    $result['nodes'] += 1;
                    $this->addToFileList($currentPath, $trimmedRelativePath . $currentName, $fileSize);
                }
            }
        }
        closedir($handle);
        return $result;
    }

    public static function isValidEncoding($string)
    {
        return !preg_match('/([\/\*\?><\:\\\\\|]|[^\x20-\x7f])/', $string);
    }

    private function addToDirList($dirPath, $relativePath, $size, $nodes)
    {
        //Dir is not readble remove and flag
        if (!SnapOS::isWindows() && !is_readable($dirPath)) {
            $this->FilterInfo->Dirs->Unreadable[] = $dirPath;
            return;
        }

        // is relative path is empty is the root path
        if (strlen($relativePath) && !DUP_Settings::Get('skip_archive_scan')) {
            $name = basename($dirPath);
            // Locate invalid directories and warn
            $invalid_encoding = !self::isValidEncoding($name);
            if ($invalid_encoding) {
                $dirPath = DUP_Encoding::toUTF8($dirPath);
            }
            $trimmedName  = trim($name);
            $invalid_name = (
                $invalid_encoding ||
                (defined('PHP_MAXPATHLEN') && strlen($dirPath) > PHP_MAXPATHLEN) ||
                strlen($trimmedName) === 0 ||
                $name[strlen($name) - 1] === '.'
            );
            if ($invalid_name) {
                $this->FilterInfo->Dirs->Warning[] = $dirPath;
            }

            if ($size > DUPLICATOR_SCAN_WARN_DIR_SIZE) {
                $dirData                        = array(
                    'ubytes' => $size,
                    'bytes'  => DUP_Util::byteSize($size, 0),
                    'nodes'  => $nodes,
                    'name'   => $name,
                    'dir'    => pathinfo($dirPath, PATHINFO_DIRNAME),
                    'path'   => $dirPath
                );
                $this->FilterInfo->Dirs->Size[] = $dirData;
                DUP_Log::traceObject('ADD DIR SIZE:', $dirData);
            }

            //Check for other WordPress installs
            if (!self::isCurrentWordpressInstallPath($dirPath) && SnapWP::isWpHomeFolder($dirPath)) {
                $this->FilterInfo->Dirs->AddonSites[] = $dirPath;
            }
        }

        $this->listDirObj->addEntry($relativePath, $size, $nodes);
        $this->Dirs[] = $dirPath;
        $this->dirsCount ++;
    }

    private function addToFileList($filePath, $relativePath, $fileSize)
    {
        if (!is_readable($filePath)) {
            $this->FilterInfo->Files->Unreadable[] = $filePath;
            return;
        }

        if (!DUP_Settings::Get('skip_archive_scan')) {
            $fileName = basename($filePath);
            //File Warnings
            $invalid_encoding = !self::isValidEncoding($fileName);
            $trimmed_name     = trim($fileName);
            $invalid_name     = $invalid_encoding || (defined('PHP_MAXPATHLEN') && strlen($filePath) > PHP_MAXPATHLEN) || strlen($trimmed_name) === 0;
            if ($invalid_encoding) {
                $filePath = DUP_Encoding::toUTF8($filePath);
                $fileName = DUP_Encoding::toUTF8($fileName);
            }

            if ($invalid_name) {
                $this->FilterInfo->Files->Warning[] = array(
                    'name' => $fileName,
                    'dir'  => pathinfo($filePath, PATHINFO_DIRNAME),
                    'path' => $filePath
                );
            }

            if ($fileSize > DUPLICATOR_SCAN_WARNFILESIZE) {
                $this->FilterInfo->Files->Size[] = array(
                    'ubytes' => $fileSize,
                    'bytes'  => DUP_Util::byteSize($fileSize, 0),
                    'nodes'  => 1,
                    'name'   => $fileName,
                    'dir'    => pathinfo($filePath, PATHINFO_DIRNAME),
                    'path'   => $filePath
                );
            }
        }


        $this->Size += $fileSize;
        $this->listFileObj->addEntry($relativePath, $fileSize, 1);
        $this->Files[] = $filePath;
        $this->filesCount++;
    }

    /**
     *  Builds a tree for both file size warnings and name check warnings
     *  The trees are used to apply filters from the scan screen
     *
     *  @return null
     */
    private function setTreeFilters()
    {
        //-------------------------
        //SIZE TREE
        //BUILD: File Size tree
        $dir_group = DUP_Util::array_group_by($this->FilterInfo->Files->Size, "dir");
        ksort($dir_group);
        foreach ($dir_group as $dir => $files) {
            $sum = 0;
            foreach ($files as $key => $value) {
                $sum += $value['ubytes'];
            }

            //Locate core paths, wp-admin, wp-includes, etc.
            $iscore = 0;
            foreach ($this->wpCorePaths as $core_dir) {
                if (strpos(DUP_Util::safePath($dir), DUP_Util::safePath($core_dir)) !== false) {
                    $iscore = 1;
                    break;
                }
            }
            // Check root and content exact dir
            if (!$iscore) {
                if (in_array($dir, $this->wpCoreExactPaths)) {
                    $iscore = 1;
                }
            }

            $this->FilterInfo->TreeSize[] = array(
                'size' => DUP_Util::byteSize($sum, 0),
                'dir' => $dir,
                'sdir' => str_replace(duplicator_get_abs_path(), '/', $dir),
                'iscore' => $iscore,
                'files' => $files
            );
        }

        //-------------------------
        //NAME TREE
        //BUILD: Warning tree for file names
        $dir_group = DUP_Util::array_group_by($this->FilterInfo->Files->Warning, "dir");
        ksort($dir_group);
        foreach ($dir_group as $dir => $files) {
        //Locate core paths, wp-admin, wp-includes, etc.
            $iscore = 0;
            foreach ($this->wpCorePaths as $core_dir) {
                if (strpos($dir, $core_dir) !== false) {
                    $iscore = 1;
                    break;
                }
            }
            // Check root and content exact dir
            if (!$iscore) {
                if (in_array($dir, $this->wpCoreExactPaths)) {
                    $iscore = 1;
                }
            }

            $this->FilterInfo->TreeWarning[] = array(
                'dir' => $dir,
                'sdir' => str_replace(duplicator_get_abs_path(), '/', $dir),
                'iscore' => $iscore,
                'count' => count($files),
                'files' => $files);
        }

        //BUILD: Warning tree for dir names
        foreach ($this->FilterInfo->Dirs->Warning as $dir) {
            $add_dir = true;
            foreach ($this->FilterInfo->TreeWarning as $key => $value) {
                if ($value['dir'] == $dir) {
                    $add_dir = false;
                    break;
                }
            }
            if ($add_dir) {
//Locate core paths, wp-admin, wp-includes, etc.
                $iscore = 0;
                foreach ($this->wpCorePaths as $core_dir) {
                    if (strpos(DUP_Util::safePath($dir), DUP_Util::safePath($core_dir)) !== false) {
                        $iscore = 1;
                        break;
                    }
                }
                // Check root and content exact dir
                if (!$iscore) {
                    if (in_array($dir, $this->wpCoreExactPaths)) {
                        $iscore = 1;
                    }
                }

                $this->FilterInfo->TreeWarning[] = array(
                    'dir' => $dir,
                    'sdir' => str_replace(duplicator_get_abs_path(), '/', $dir),
                    'iscore' => $iscore,
                    'count' => 0);
            }
        }

        function _sortDir($a, $b)
        {
            return strcmp($a["dir"], $b["dir"]);
        }
        usort($this->FilterInfo->TreeWarning, "_sortDir");
    }

    public function getWPConfigFilePath()
    {
        $wpconfig_filepath = '';
        $abs_path          = duplicator_get_abs_path();
        if (file_exists($abs_path . '/wp-config.php')) {
            $wpconfig_filepath = $abs_path . '/wp-config.php';
        } elseif (@file_exists(dirname($abs_path) . '/wp-config.php') && !@file_exists(dirname($abs_path) . '/wp-settings.php')) {
            $wpconfig_filepath = dirname($abs_path) . '/wp-config.php';
        }
        return $wpconfig_filepath;
    }

    /**
     * get the main target root path to make archive
     *
     * @staticvar type $targerRoorPath
     * @return string
     */
    public static function getTargetRootPath()
    {
        static $targetRoorPath = null;
        if (is_null($targetRoorPath)) {
            $paths = self::getArchiveListPaths();
            unset($paths['wpconfig']);
            $targetRoorPath = SnapIO::getCommonPath($paths);
        }
        return $targetRoorPath;
    }

    /**
     * @param null|string $urlKey if set will only return the url identified by that key
     * @return array|string|bool
     */
    public static function getOriginalUrls($urlKey = null)
    {
        static $origUrls = null;
        if (is_null($origUrls)) {
            $restoreMultisite = false;
            if (is_multisite() && SnapWP::getMainSiteId() !== get_current_blog_id()) {
                $restoreMultisite = true;
                restore_current_blog();
                switch_to_blog(SnapWP::getMainSiteId());
            }

            $updDirs = wp_upload_dir(null, false, true);
            if (($wpConfigDir = SnapWP::getWPConfigPath()) !== false) {
                $wpConfigDir = dirname($wpConfigDir);
            }

            $homeUrl   = home_url();
            $homeParse = SnapURL::parseUrl(home_url());
            $absParse  = SnapURL::parseUrl(site_url());
            if ($homeParse['host'] === $absParse['host'] && SnapIO::isChildPath($homeParse['path'], $absParse['path'], false, false)) {
                $homeParse['path'] = $absParse['path'];
                $homeUrl           = SnapURL::buildUrl($homeParse);
            }

            $origUrls = array(
                'home'      => $homeUrl,
                'abs'       => site_url(),
                'login'     => wp_login_url(),
                'wpcontent' => content_url(),
                'uploads'   => $updDirs['baseurl'],
                'plugins'   => plugins_url(),
                'muplugins' => WPMU_PLUGIN_URL,
                'themes'    => get_theme_root_uri()
            );
            if ($restoreMultisite) {
                restore_current_blog();
            }
        }

        if ($urlKey === null) {
            return $origUrls;
        }

        if (isset($origUrls[$urlKey])) {
            return $origUrls[$urlKey];
        } else {
            return false;
        }
    }

    /**
     * return all paths to scan
     *
     * @return string[]
     */
    public static function getScanPaths()
    {
        static $scanPaths = null;
        if (is_null($scanPaths)) {
            $paths = self::getArchiveListPaths();
            // The folder that contains wp-config must not be scanned in full but only added
            unset($paths['wpconfig']);
            $scanPaths = array(
                $paths['home']
            );
            unset($paths['home']);
            foreach ($paths as $path) {
                $addPath = true;
                foreach ($scanPaths as $resPath) {
                    if (SnapIO::getRelativePath($path, $resPath) !== false) {
                        $addPath = false;
                        break;
                    }
                }
                if ($addPath) {
                    $scanPaths[] = $path;
                }
            }
            $scanPaths = array_values(array_unique($scanPaths));
        }
        return $scanPaths;
    }

    /**
     * return the wordpress original dir paths
     *
     * @staticvar string[] $origPaths if is null retur the array of paths or the single key path
     * @param string|null $pathKey
     *
     * @return string[]|string|bool return false if key doesn\'t exist
     */
    public static function getOriginalPaths($pathKey = null)
    {
        static $origPaths = null;
        if (is_null($origPaths)) {
            $restoreMultisite = false;
            if (is_multisite() && SnapWP::getMainSiteId() !== get_current_blog_id()) {
                $restoreMultisite = true;
                restore_current_blog();
                switch_to_blog(SnapWP::getMainSiteId());
            }

            $updDirs = wp_upload_dir(null, false, true);
            // fix for old network installation
            $baseDir = preg_replace('/^(.+\/blogs\.dir)\/\d+\/files$/', '$1', $updDirs['basedir']);
            if (($wpConfigDir = SnapWP::getWPConfigPath()) !== false) {
                $wpConfigDir = dirname($wpConfigDir);
            }
            $origPaths = array(
                'home'      => SnapWP::getHomePath(),
                'abs'       => ABSPATH,
                'wpconfig'  => $wpConfigDir,
                'wpcontent' => WP_CONTENT_DIR,
                'uploads'   => $baseDir,
                'plugins'   => WP_PLUGIN_DIR,
                'muplugins' => WPMU_PLUGIN_DIR,
                'themes'    => get_theme_root()
            );
            if ($restoreMultisite) {
                restore_current_blog();
            }
        }

        if (!empty($pathKey)) {
            if (array_key_exists($pathKey, $origPaths)) {
                return $origPaths[$pathKey];
            } else {
                return false;
            }
        } else {
            return $origPaths;
        }
    }

    /**
     * return the wordpress original dir paths
     *
     * @staticvar string[] $paths if is null retur the array of paths or the single key path
     * @param string|null $pathKey
     *
     * @return string[]|string|bool return false if key doesn\'t exist
     */
    public static function getArchiveListPaths($pathKey = null)
    {
        static $archivePaths = null;
        if (is_null($archivePaths)) {
            $archivePaths  = array();
            $originalPaths = self::getOriginalPaths();

            $archivePaths = array(
                'home' => SnapIO::safePathUntrailingslashit($originalPaths['home'], true)
            );
            unset($originalPaths['home']);

            foreach ($originalPaths as $key => $originalPath) {
                $path     = SnapIO::safePathUntrailingslashit($originalPath, false);
                $realPath = SnapIO::safePathUntrailingslashit($originalPath, true);

                if ($path == $realPath) {
                    $archivePaths[$key] = $path;
                } elseif (
                    !SnapIO::isChildPath($realPath, $archivePaths['home']) &&
                    SnapIO::isChildPath($path, $archivePaths['home'])
                ) {
                    $archivePaths[$key] = $path;
                } else {
                    $archivePaths[$key] = $realPath;
                }
            }
        }

        if (!empty($pathKey)) {
            if (array_key_exists($pathKey, $archivePaths)) {
                return $archivePaths[$pathKey];
            } else {
                return false;
            }
        } else {
            return $archivePaths;
        }
    }

    /**
     * return true if path is child of duplicator backup path
     *
     * @param string $path
     * @return boolean
     */
    public static function isBackupPathChild($path)
    {
        return (preg_match('/[\/]' . preg_quote(DUP_Settings::getSsdirTmpPath(), '/') . '[\/][^\/]+$/', $path) === 1);
    }

    /**
     *
     * @param string $path
     *
     * @return bool return true if path is a path of current wordpress installation
     */
    public static function isCurrentWordpressInstallPath($path)
    {
        static $currentWpPaths = null;

        if (is_null($currentWpPaths)) {
            $currentWpPaths = array_merge(self::getOriginalPaths(), self::getArchiveListPaths());
            $currentWpPaths = array_map('trailingslashit', $currentWpPaths);
            $currentWpPaths = array_values(array_unique($currentWpPaths));
        }
        return in_array(trailingslashit($path), $currentWpPaths);
    }

    public function wpContentDirNormalizePath()
    {
        if (!isset($this->wpContentDirNormalizePath)) {
            $this->wpContentDirNormalizePath = trailingslashit(wp_normalize_path(WP_CONTENT_DIR));
        }
        return $this->wpContentDirNormalizePath;
    }

    public function getUrl()
    {
        return DUP_Settings::getSsdirUrl() . "/" . $this->File;
    }

    public function getLocalDirPath($dir, $basePath = '')
    {
        $safeDir = SnapIO::safePathUntrailingslashit($dir);
        return ltrim($basePath . preg_replace('/^' . preg_quote($this->PackDir, '/') . '(.*)/m', '$1', $safeDir), '/');
    }

    public function getLocalFilePath($file, $basePath = '')
    {
        $safeFile = SnapIO::safePathUntrailingslashit($file);
        return ltrim($basePath . preg_replace('/^' . preg_quote($this->PackDir, '/') . '(.*)/m', '$1', $safeFile), '/');
    }
}
