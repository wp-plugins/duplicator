<?php

/**
 * Class used to control values about the package meta data
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\ArchiveConfig
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapDB;
use Duplicator\Libs\Snap\SnapString;
use Duplicator\Libs\WpConfig\WPConfigTransformer;

/**
 * singleton class
 */
class DUPX_ArchiveConfig
{
    const NOTICE_ID_PARAM_EMPTY = 'param_empty_to_validate';

    // READ-ONLY: COMPARE VALUES
    public $dup_type;
    public $created;
    public $version_dup;
    public $version_wp;
    public $version_db;
    public $version_php;
    public $version_os;
    public $packInfo;
    public $fileInfo;
    public $dbInfo;
    public $wpInfo;
    /** @var int<-1,max> */
    public $defaultStorageId = -1;
    /** @var string[] */
    public $components = array();
    // GENERAL
    public $secure_on;
    public $secure_pass;
    public $installer_base_name   = '';
    public $installer_backup_name = '';
    public $package_name;
    public $package_hash;
    public $package_notes;
    public $wp_tableprefix;
    public $blogname;
    public $blogNameSafe;
    public $exportOnlyDB;
    //ADV OPTS
    public $opts_delete;
    //MULTISITE
    public $mu_mode;
    public $mu_generation;
    /** @var mixed[] */
    public $subsites     = array();
    public $main_site_id = 1;
    public $mu_is_filtered;
    public $mu_siteadmins = array();
    //LICENSING
    /** @var int<0, max> */
    public $license_limit = 0;
    /** @var int ENUM LICENSE TYPE */
    public $license_type = 0;
    //PARAMS
    public $overwriteInstallerParams = array();
    /** @var ?string */
    public $dbhost = null;
    /** @var ?string */
    public $dbname = null;
    /** @var ?string */
    public $dbuser = null;
    /** @var object */
    public $brand = null;
    /** @var ?string */
    public $cpnl_host;
    /** @var ?string */
    public $cpnl_user;
    /** @var ?string */
    public $cpnl_pass;
    /** @var ?string */
    public $cpnl_enable;

    /** @var self */
    private static $instance = null;

    /**
     * Get instance
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
     * Singleton class constructor
     */
    protected function __construct()
    {
        $config_filepath = DUPX_Package::getPackageArchivePath();
        if (!file_exists($config_filepath)) {
            throw new Exception("Archive file $config_filepath doesn't exist");
        }

        if (($file_contents = file_get_contents($config_filepath)) === false) {
            throw new Exception("Can\'t read Archive file $config_filepath");
        }

        if (($data = json_decode($file_contents)) === null) {
            throw new Exception("Can\'t decode archive json");
        }

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }

        //Instance Updates:
        $this->blogNameSafe = preg_replace("/[^A-Za-z0-9?!]/", '', $this->blogname);
    }

    /**
     *
     * @return bool
     */
    public function isZipArchive()
    {
        $extension = strtolower(pathinfo($this->package_name, PATHINFO_EXTENSION));
        return ($extension == 'zip');
    }

    /**
     *
     * @param string $define
     *
     * @return bool             // return true if define value exists
     */
    public function defineValueExists($define)
    {
        return isset($this->wpInfo->configs->defines->{$define});
    }

    public function getUsersLists()
    {
        $result = array();
        foreach ($this->wpInfo->adminUsers as $user) {
            $result[$user->id] = $user->user_login;
        }
        return $result;
    }

    /**
     *
     * @param string $define
     * @param array $default
     *
     * @return array
     */
    public function getDefineArrayValue($define, $default = array(
            'value'      => false,
            'inWpConfig' => false
        ))
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define})) {
            return (array) $defines->{$define};
        } else {
            return $default;
        }
    }

    /**
     * return define value from archive or default value if don't exists
     *
     * @param string $define
     * @param mixed $default
     *
     * @return mixed
     */
    public function getDefineValue($define, $default = false)
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define})) {
            return $defines->{$define}->value;
        } else {
            return $default;
        }
    }

    /**
     * return define value from archive or default value if don't exists in wp-config
     *
     * @param string $define
     * @param mixed $default
     *
     * @return mixed
     */
    public function getWpConfigDefineValue($define, $default = false)
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define}) && $defines->{$define}->inWpConfig) {
            return $defines->{$define}->value;
        } else {
            return $default;
        }
    }

    public function inWpConfigDefine($define)
    {
        $defines = $this->wpInfo->configs->defines;
        if (isset($defines->{$define})) {
            return $defines->{$define}->inWpConfig;
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $key
     *
     * @return boolean
     */
    public function realValueExists($key)
    {
        return isset($this->wpInfo->configs->realValues->{$key});
    }

    /**
     * return read value from archive if exists of default if don't exists
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getRealValue($key, $default = false)
    {
        $values = $this->wpInfo->configs->realValues;
        if (isset($values->{$key})) {
            return $values->{$key};
        } else {
            return $default;
        }
    }

    /**
     * in hours
     *
     * @return int
     */
    public function getPackageLife()
    {
        $packageTime = strtotime($this->created);
        $currentTime = strtotime('now');
        return ceil(($currentTime - $packageTime) / 60 / 60);
    }

    /**
     *
     * @return int
     */
    public function totalArchiveItemsCount()
    {
        return $this->fileInfo->dirCount + $this->fileInfo->fileCount;
    }

    public function setNewPathsAndUrlParamsByMainNew()
    {
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_WP_CORE_NEW, PrmMng::PARAM_SITE_URL);
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_CONTENT_NEW, PrmMng::PARAM_URL_CONTENT_NEW);
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_UPLOADS_NEW, PrmMng::PARAM_URL_UPLOADS_NEW);
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_PLUGINS_NEW, PrmMng::PARAM_URL_PLUGINS_NEW);
        self::manageEmptyPathAndUrl(PrmMng::PARAM_PATH_MUPLUGINS_NEW, PrmMng::PARAM_URL_MUPLUGINS_NEW);

        $paramsManager = PrmMng::getInstance();
        $noticeManager = DUPX_NOTICE_MANAGER::getInstance();
        $noticeManager->addNextStepNotice(array(
            'shortMsg'    => '',
            'level'       => DUPX_NOTICE_ITEM::NOTICE,
            'longMsg'     => '<span class="green">If desired, you can change the default values in "Advanced install" &gt; "Other options"</span>.',
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND_IF_EXISTS, self::NOTICE_ID_PARAM_EMPTY);

        $paramsManager->save();
        $noticeManager->saveNotices();
    }

    protected static function manageEmptyPathAndUrl($pathKey, $urlKey)
    {
        $paramsManager = PrmMng::getInstance();
        $validPath     = (strlen($paramsManager->getValue($pathKey)) > 0);
        $validUrl      = (strlen($paramsManager->getValue($urlKey)) > 0);

        if ($validPath && $validUrl) {
            return true;
        }

        $paramsManager->setValue($pathKey, self::getDefaultPathUrlValueFromParamKey($pathKey));
        $paramsManager->setValue($urlKey, self::getDefaultPathUrlValueFromParamKey($urlKey));

        $noticeManager = DUPX_NOTICE_MANAGER::getInstance();
        $msg           = '<b>' . $paramsManager->getLabel($pathKey) . ' and/or ' . $paramsManager->getLabel($urlKey) . '</b> can\'t be generated automatically so they are set to their default value.' . "<br>\n";
        $msg          .= $paramsManager->getLabel($pathKey) . ': ' . $paramsManager->getValue($pathKey) . "<br>\n";
        $msg          .= $paramsManager->getLabel($urlKey) . ': ' . $paramsManager->getValue($urlKey) . "<br>\n";

        $noticeManager->addNextStepNotice(array(
            'shortMsg'    => 'URLs and/or PATHs set automatically to their default value.',
            'level'       => DUPX_NOTICE_ITEM::NOTICE,
            'longMsg'     => $msg . "<br>\n",
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, self::NOTICE_ID_PARAM_EMPTY);
    }

    public static function getDefaultPathUrlValueFromParamKey($paramKey)
    {
        $paramsManager = PrmMng::getInstance();
        switch ($paramKey) {
            case PrmMng::PARAM_SITE_URL:
                return $paramsManager->getValue(PrmMng::PARAM_URL_NEW);
            case PrmMng::PARAM_URL_CONTENT_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_URL_NEW) . '/wp-content';
            case PrmMng::PARAM_URL_UPLOADS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW) . '/uploads';
            case PrmMng::PARAM_URL_PLUGINS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW) . '/plugins';
            case PrmMng::PARAM_URL_MUPLUGINS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW) . '/mu-plugins';
            case PrmMng::PARAM_PATH_WP_CORE_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_NEW);
            case PrmMng::PARAM_PATH_CONTENT_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_NEW) . '/wp-content';
            case PrmMng::PARAM_PATH_UPLOADS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/uploads';
            case PrmMng::PARAM_PATH_PLUGINS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/plugins';
            case PrmMng::PARAM_PATH_MUPLUGINS_NEW:
                return $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/mu-plugins';
            default:
                throw new Exception('Invalid URL or PATH param');
        }
    }

    /**
     *
     * @param string $oldMain
     * @param string $newMain
     * @param string $subOld
     *
     * @return boolean|string  return false if cant generate new sub string
     */
    public static function getNewSubString($oldMain, $newMain, $subOld)
    {
        if (($relativePath = SnapIO::getRelativePath($subOld, $oldMain)) === false) {
            return false;
        }
        return $newMain . '/' . $relativePath;
    }

    /**
     *
     * @param string $oldMain
     * @param string $newMain
     * @param string $subOld
     *
     * @return boolean|string  return false if cant generate new sub string
     */
    public static function getNewSubUrl($oldMain, $newMain, $subOld)
    {

        $parsedOldMain = SnapURL::parseUrl($oldMain);
        $parsedNewMain = SnapURL::parseUrl($newMain);
        $parsedSubOld  = SnapURL::parseUrl($subOld);

        $parsedSubNew           = $parsedSubOld;
        $parsedSubNew['scheme'] = $parsedNewMain['scheme'];
        $parsedSubNew['port']   = $parsedNewMain['port'];

        if ($parsedOldMain['host'] !== $parsedSubOld['host']) {
            return false;
        }
        $parsedSubNew['host'] = $parsedNewMain['host'];

        if (($newPath = self::getNewSubString($parsedOldMain['path'], $parsedNewMain['path'], $parsedSubOld['path'])) === false) {
            return false;
        }
        $parsedSubNew['path'] = $newPath;
        return SnapURL::buildUrl($parsedSubNew);
    }

     /**
     * Returns case insensitive duplicate tables from source site
     *
     * @return array<string[]>
     */
    public function getDuplicateTableNames()
    {
        $tableList  = (array) $this->dbInfo->tablesList;
        $allTables  = array_keys($tableList);
        $duplicates = SnapString::getCaseInsesitiveDuplicates($allTables);

        return $duplicates;
    }

    /**
     * Returns list of redundant duplicates
     *
     * @return string[]
     */
    public function getRedundantDuplicateTableNames()
    {
        $duplicateTables = $this->getDuplicateTableNames();
        $prefix          = DUPX_ArchiveConfig::getInstance()->wp_tableprefix;
        $redundantTables = array();

        foreach ($duplicateTables as $tables) {
            $redundantTables = array_merge($redundantTables, SnapDB::getRedundantDuplicateTables($prefix, $tables));
        }

        return $redundantTables;
    }

    /**
     *
     * @return bool
     */
    public function isTablesCaseSensitive()
    {
        return $this->dbInfo->isTablesUpperCase;
    }

    public function isTablePrefixChanged()
    {
        return $this->wp_tableprefix != PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);
    }

    public function getTableWithNewPrefix($table)
    {
        $search  = '/^' . preg_quote($this->wp_tableprefix, '/') . '(.*)/';
        $replace = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX) . '$1';
        return preg_replace($search, $replace, $table, 1);
    }

    public function getOldUrlScheme()
    {
        static $oldScheme = null;
        if (is_null($oldScheme)) {
            $siteUrl   = $this->getRealValue('siteUrl');
            $oldScheme = parse_url($siteUrl, PHP_URL_SCHEME);
            if ($oldScheme === false) {
                $oldScheme = 'http';
            }
        }
        return $oldScheme;
    }

    /**
     * get relative path in archive of wordpress main paths
     *
     * @param string $pathKey (abs,home,plugins ...)
     *
     * @return string
     */
    public function getRelativePathsInArchive($pathKey = null)
    {
        static $realtviePaths = null;

        if (is_null($realtviePaths)) {
            $realtviePaths = (array) $this->getRealValue('archivePaths');
            foreach ($realtviePaths as $key => $path) {
                $realtviePaths[$key] = SnapIO::getRelativePath($path, $this->wpInfo->targetRoot);
            }
        }

        if (!empty($pathKey)) {
            if (array_key_exists($pathKey, $realtviePaths)) {
                return $realtviePaths[$pathKey];
            } else {
                return false;
            }
        } else {
            return $realtviePaths;
        }
    }

    /**
     *
     * @param string $path
     * @param string|string[] $pathKeys
     *
     * @return boolean
     */
    public function isChildOfArchivePath($path, $pathKeys = array())
    {
        if (is_scalar($pathKeys)) {
            $pathKeys = array($pathKeys);
        }

        $mainPaths = $this->getRelativePathsInArchive();
        foreach ($pathKeys as $key) {
            if (!isset($mainPaths[$key])) {
                continue;
            }

            if (strlen($mainPaths[$key]) == 0) {
                return true;
            }

            if (strpos($path, $mainPaths[$key]) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @staticvar string|bool $relativePath return false if PARAM_PATH_MUPLUGINS_NEW isn't a sub path of PARAM_PATH_NEW
     * @return    string
     */
    public function getRelativeMuPlugins()
    {
        static $relativePath = null;
        if (is_null($relativePath)) {
            $relativePath = SnapIO::getRelativePath(
                PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW),
                PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW)
            );
        }
        return $relativePath;
    }

    /**
     * return the mapping paths from relative path of archive zip and target folder
     * if exist only one entry return the target folter string
     *
     * @param bool $reset // if true recalculater path mappintg
     *
     * @return string|array
     */
    public function getPathsMapping($reset = false)
    {
        static $pathsMapping = null;

        if (is_null($pathsMapping) || $reset) {
            $paramsManager = PrmMng::getInstance();
            $pathsMapping  = array();

            $targeRootPath = $this->wpInfo->targetRoot;
            $paths         = (array) $this->getRealValue('archivePaths');

            foreach ($paths as $key => $path) {
                if (($relativePath = SnapIO::getRelativePath($path, $targeRootPath)) !== false) {
                    $paths[$key] = $relativePath;
                }
            }
            $pathsMapping[$paths['home']] = $paramsManager->getValue(PrmMng::PARAM_PATH_NEW);
            if ($paths['home'] !== $paths['abs']) {
                $pathsMapping[$paths['abs']] = $paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
            }
            $pathsMapping[$paths['wpcontent']] = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW);
            $pathsMapping[$paths['plugins']]   = $paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW);
            $pathsMapping[$paths['muplugins']] = $paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW);

            switch (DUPX_InstallerState::getInstType()) {
                case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
                    $pathsMapping[$paths['uploads']] = $paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);
                    break;
                case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
                case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                    throw new Exception('Mode not avaiable');
                case DUPX_InstallerState::INSTALL_NOT_SET:
                    throw new Exception('Cannot change setup with current installation type [' . DUPX_InstallerState::getInstType() . ']');
                default:
                    throw new Exception('Unknown mode');
            }

            // remove all empty values for safe,
            // This should never happen, but if it does, there is a risk that the installer will remove all the files in the server root.
            $pathsMapping = array_filter($pathsMapping, function ($value) {
                return strlen($value) > 0;
            });

            $pathsMapping = SnapIO::sortBySubfoldersCount($pathsMapping, true, false, true);

            $unsetKeys = array();
            foreach (array_reverse($pathsMapping) as $oldPathA => $newPathA) {
                foreach ($pathsMapping as $oldPathB => $newPathB) {
                    if ($oldPathA == $oldPathB) {
                        continue;
                    }

                    if (
                        ($relativePathOld = SnapIO::getRelativePath($oldPathA, $oldPathB)) === false ||
                        ($relativePathNew = SnapIO::getRelativePath($newPathA, $newPathB)) === false
                    ) {
                        continue;
                    }

                    if ($relativePathOld == $relativePathNew) {
                        $unsetKeys[] = $oldPathA;
                        break;
                    }
                }
            }
            foreach (array_unique($unsetKeys) as $unsetKey) {
                unset($pathsMapping[$unsetKey]);
            }

            $tempArray    = $pathsMapping;
            $pathsMapping = array();
            foreach ($tempArray as $key => $val) {
                $pathsMapping['/' . $key] = $val;
            }

            switch (count($pathsMapping)) {
                case 0:
                    throw new Exception('Paths archive mapping is inconsistent');
                    break;
                case 1:
                    $pathsMapping = reset($pathsMapping);
                    break;
                default:
            }

            Log::info("--------------------------------------");
            Log::info('PATHS MAPPING : ' . Log::v2str($pathsMapping));
            Log::info("--------------------------------------");
        }
        return $pathsMapping;
    }

    /**
     * get absolute target path from archive relative path
     *
     * @param string $pathInArchive
     *
     * @return string
     */
    public function destFileFromArchiveName($pathInArchive)
    {
        $pathsMapping = $this->getPathsMapping();

        if (is_string($pathsMapping)) {
            return $pathsMapping . '/' . ltrim($pathInArchive, '\\/');
        }

        if (strlen($pathInArchive) === 0) {
            $pathInArchive = '/';
        } elseif ($pathInArchive[0] != '/') {
            $pathInArchive = '/' . $pathInArchive;
        }

        foreach ($pathsMapping as $archiveMainPath => $newMainPath) {
            if (($relative = SnapIO::getRelativePath($pathInArchive, $archiveMainPath)) !== false) {
                return $newMainPath . '/' . $relative;
            }
        }

        // if don't find corrispondance in mapping get the path new as default (this should never happen)
        return PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . '/' . ltrim($pathInArchive, '\\/');
    }

    /**
     *
     * @return string[]
     */
    public function invalidCharsets()
    {
        return array_diff($this->dbInfo->charSetList, DUPX_DB_Functions::getInstance()->getCharsetsList());
    }

    /**
     *
     * @return string[]
     */
    public function invalidCollations()
    {
        return array_diff($this->dbInfo->collationList, DUPX_DB_Functions::getInstance()->getCollationsList());
    }

    /**
     *
     * @return string[] list of MySQL engines in source site not supported by the current database
     * @throws Exception
     */
    public function invalidEngines()
    {
        return array_diff($this->dbInfo->engineList, DUPX_DB_Functions::getInstance()->getSupportedEngineList());
    }

    /**
     *
     * @param WPConfigTransformer $confTrans
     * @param string $defineKey
     * @param string $paramKey
     */
    public static function updateWpConfigByParam(WPConfigTransformer $confTrans, $defineKey, $paramKey)
    {
        $paramsManager = PrmMng::getInstance();
        $wpConfVal     = $paramsManager->getValue($paramKey);
        return self::updateWpConfigByValue($confTrans, $defineKey, $wpConfVal);
    }

    /**
     *
     * @param WPConfigTransformer $confTrans
     * @param string $defineKey
     * @param mixed $wpConfVal
     */
    /**
     * Update wp conf
     *
     * @param WPConfigTransformer $confTrans
     * @param string              $defineKey
     * @param array               $wpConfVal
     * @param mixed               $customValue if is not null custom value overwrite value
     *
     * @return void
     */
    public static function updateWpConfigByValue(WPConfigTransformer $confTrans, $defineKey, $wpConfVal, $customValue = null)
    {
        if ($wpConfVal['inWpConfig']) {
            $stringVal = '';
            if ($customValue !== null) {
                $stringVal = $customValue;
                $updParam  = array('raw' => true, 'normalize' => true);
            } else {
                switch (gettype($wpConfVal['value'])) {
                    case "boolean":
                        $stringVal = $wpConfVal['value'] ? 'true' : 'false';
                        $updParam  = array('raw' => true, 'normalize' => true);
                        break;
                    case "integer":
                    case "double":
                        $stringVal = (string) $wpConfVal['value'];
                        $updParam  = array('raw' => true, 'normalize' => true);
                        break;
                    case "string":
                        $stringVal = $wpConfVal['value'];
                        $updParam  = array('raw' => false, 'normalize' => true);
                        break;
                    case "NULL":
                        $stringVal = 'null';
                        $updParam  = array('raw' => true, 'normalize' => true);
                        break;
                    case "array":
                    case "object":
                    case "resource":
                    case "resource (closed)":
                    case "unknown type":
                    default:
                        $stringVal = '';
                        $updParam  = array('raw' => true, 'normalize' => true);
                        break;
                }
            }

            Log::info('WP CONFIG UPDATE ' . $defineKey . ' ' . Log::v2str($stringVal));
            $confTrans->update('constant', $defineKey, $stringVal, $updParam);
        } else {
            if ($confTrans->exists('constant', $defineKey)) {
                Log::info('WP CONFIG REMOVE ' . $defineKey);
                $confTrans->remove('constant', $defineKey);
            }
        }
    }
}
