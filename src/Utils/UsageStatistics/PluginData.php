<?php

namespace Duplicator\Utils\UsageStatistics;

use DUP_DB;
use DUP_LITE_Plugin_Upgrade;
use DUP_Log;
use DUP_Package;
use DUP_PackageStatus;
use Duplicator\Libs\Snap\SnapDB;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapWP;
use ReflectionClass;
use stdClass;
use wpdb;

class PluginData
{
    const PLUGIN_DATA_OPTION_KEY = 'duplicator_plugin_data_stats';
    const IDENTIFIER_CHARS       = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-.,;=+&';

    const PLUGIN_STATUS_ACTIVE   = 'active';
    const PLUGIN_STATUS_INACTIVE = 'inactive';

    /**
     * @var ?self
     */
    private static $instance = null;

    /**
     * @var int
     */
    private $lastSendTime = 0;

    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $plugin = 'dup-lite';

    /**
     * @var string
     */
    private $pluginStatus = self::PLUGIN_STATUS_ACTIVE;

    /**
     * @var int
     */
    private $buildCount = 0;

    /**
     * @var int
     */
    private $buildLastDate = 0;

    /**
     * @var int
     */
    private $buildFailedCount = 0;

    /**
     * @var int
     */
    private $buildFailedLastDate = 0;

    /**
     * @var float
     */
    private $siteSizeMB = 0;

    /**
     * @var int
     */
    private $siteNumFiles = 0;

    /**
     * @var float
     */
    private $siteDbSizeMB = 0;

    /**
     * @var int
     */
    private $siteDbNumTables = 0;

    /**
     * Class constructor
     */
    private function __construct()
    {
        if (($data = get_option(self::PLUGIN_DATA_OPTION_KEY)) !== false) {
            $data    = json_decode($data, true);
            $reflect = new ReflectionClass(__CLASS__);
            $props   = $reflect->getProperties();
            foreach ($props as $prop) {
                if (isset($data[$prop->getName()])) {
                    $prop->setAccessible(true);
                    $prop->setValue($this, $data[$prop->getName()]);
                }
            }
        } else {
            $this->identifier = self::generateIdentifier();
            $this->save();
        }
    }

    /**
     * Get instance
     *
     * @return self
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Save plugin data
     *
     * @return bool True if data has been saved, false otherwise
     */
    public function save()
    {
        $values = get_object_vars($this);
        return update_option(self::PLUGIN_DATA_OPTION_KEY, SnapJson::jsonEncodePPrint($values));
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Update from migrate data
     *
     * @param StdClass $data Migration data
     *
     * @return bool
     */
    public function updateFromMigrateData(stdClass $data)
    {
        $save = false;
        if (
            isset($data->ustatIdentifier) &&
            strlen($data->ustatIdentifier) > 0 &&
            $data->ustatIdentifier !== $this->identifier
        ) {
            $this->identifier = $data->ustatIdentifier;
            $save             = true;
        }

        return ($save ? $this->save() : true);
    }

    /**
     * Return usage tracking data
     *
     * @return array<string, mixed>
     */
    public function getDataToSend()
    {
        $result = $this->getBasicInfos();
        $result = array_merge($result, $this->getPluginInfos());
        $result = array_merge($result, $this->getSiteInfos());
        $result = array_merge($result, $this->getManualPackageInfos());
        $result = array_merge($result, $this->getSettingsInfos());

        $rules = array(
            'api_version'      => 'string|max:7', // 1.0
            'identifier'       => 'string|max:44',
            // BASIC INFO
            'plugin_version'   => 'string|max:25',
            'php_version'      => 'string|max:25',
            'wp_version'       => 'string|max:25',
            // PLUGIN INFO
            'pinstall_version' => '?string|max:25',
            // SITE INFO
            'servertype'       => 'string|max:25',
            'db_engine'        => 'string|max:25',
            'db_version'       => 'string|max:25',
            'timezoneoffset'   => 'string|max:10',
            'locale'           => 'string|max:10',
            'themename'        => 'string|max:255',
            'themeversion'     => 'string|max:25',
        );

        return StatsUtil::sanitizeFields($result, $rules);
    }

    /**
     * Get disable tracking data
     *
     * @return array<string, mixed>
     */
    public function getDisableDataToSend()
    {
        $result = $this->getBasicInfos();

        $rules = array(
            'api_version'    => 'string|max:7', // 1.0
            'identifier'     => 'string|max:44',
            // BASIC INFO
            'plugin_version' => 'string|max:25',
            'php_version'    => 'string|max:25',
            'wp_version'     => 'string|max:25',
        );

        return StatsUtil::sanitizeFields($result, $rules);
    }

    /**
     * Set status
     *
     * @param string $status Status: active, inactive or uninstalled
     *
     * @return void
     */
    public function setStatus($status)
    {
        if ($this->pluginStatus === $status) {
            return;
        }

        switch ($status) {
            case self::PLUGIN_STATUS_ACTIVE:
            case self::PLUGIN_STATUS_INACTIVE:
                $this->pluginStatus = $status;
                $this->save();
                break;
        }
    }

    /**
     * Get status
     *
     * @return string Enum: self::PLUGIN_STATUS_ACTIVE, self::PLUGIN_STATUS_INACTIVE or self::PLUGIN_STATUS_UNINSTALLED
     */
    public function getStatus()
    {
        return $this->pluginStatus;
    }

    /**
     * Add paackage build count and date for manual and schedule build
     *
     * @param DUP_Package $package Package
     *
     * @return void
     */
    public function addPackageBuild(DUP_Package $package)
    {
        if ($package->Status == DUP_PackageStatus::COMPLETE) {
            $this->buildCount++;
            $this->buildLastDate = time();
        } else {
            $this->buildFailedCount++;
            $this->buildFailedLastDate = time();
        }

        $this->save();
    }

    /**
     * Set site size
     *
     * @param int $size      Site size in bytes
     * @param int $numFiles  Number of files
     * @param int $dbSize    Database size in bytes
     * @param int $numTables Number of tables
     *
     * @return void
     */
    public function setSiteSize($size, $numFiles, $dbSize, $numTables)
    {
        $this->siteSizeMB      = round(((int) $size) / 1024 / 1024, 2);
        $this->siteNumFiles    = (int) $numFiles;
        $this->siteDbSizeMB    = round(((int) $dbSize) / 1024 / 1024, 2);
        $this->siteDbNumTables = (int) $numTables;
        $this->save();
    }

    /**
     * Update last send time
     *
     * @return void
     */
    public function updateLastSendTime()
    {
        $this->lastSendTime = time();
        $this->save();
    }

    /**
     * Get last send time
     *
     * @return int
     */
    public function getLastSendTime()
    {
        return $this->lastSendTime;
    }

    /**
     * Get basic infos
     *
     * @return array<string, mixed>
     */
    protected function getBasicInfos()
    {
        return array(
            'api_version'    => CommStats::API_VERSION,
            'identifier'     => $this->identifier,
            'plugin'         => $this->plugin,
            'plugin_status'  => $this->pluginStatus,
            'plugin_version' => DUPLICATOR_VERSION,
            'php_version'    => SnapUtil::getVersion(phpversion(), 3),
            'wp_version'     => get_bloginfo('version'),
        );
    }

    /**
     * Return plugin infos
     *
     * @return array<string, mixed>
     */
    protected function getPluginInfos()
    {
        if (($installInfo = DUP_LITE_Plugin_Upgrade::getNewInstallInfo()) === false) {
            $installInfo = array(
                'version' => null,
                'time'    => null,
            );
        }

        return array(
            'pinstall_date'    => ($installInfo['time'] == null ? null : date('Y-m-d H:i:s', $installInfo['time'])),
            'pinstall_version' => ($installInfo['version'] == null ? null : $installInfo['version']),
            'license_type'     => StatsUtil::getLicenseType(),
            'license_status'   => StatsUtil::getLicenseStatus(),
        );
    }

    /**
     * Return site infos
     *
     * @return array<string, mixed>
     */
    protected function getSiteInfos()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $theme_data = wp_get_theme();

        return array(
            'servertype'      => StatsUtil::getServerType(),
            'db_engine'       => SnapDB::getDBEngine($wpdb->dbh), // @phpstan-ignore-line
            'db_version'      => DUP_DB::getVersion(),
            'is_multisite'    => is_multisite(),
            'sites_count'     => count(SnapWP::getSitesIds()),
            'user_count'      => SnapWp::getUsersCount(),
            'timezoneoffset'  => get_option('gmt_offset'), /** @todo evaluate use wp or server timezone offset */
            'locale'          => get_locale(),
            'am_family'       => StatsUtil::getAmFamily(),
            'themename'       => $theme_data->get('Name'),
            'themeversion'    => $theme_data->get('Version'),
            'site_size_mb'    => ($this->siteSizeMB == 0 ? null : $this->siteSizeMB),
            'site_num_files'  => ($this->siteNumFiles == 0 ? null : $this->siteNumFiles),
            'site_db_size_mb' => ($this->siteDbSizeMB == 0 ? null : $this->siteDbSizeMB),
            'site_db_num_tbl' => ($this->siteDbNumTables == 0 ? null : $this->siteDbNumTables),
        );
    }

    /**
     * Return manal package infos
     *
     * @return array<string, mixed>
     */
    protected function getManualPackageInfos()
    {
        return array(
            'packages_build_count'                         => $this->buildCount,
            'packages_build_last_date'                     => ($this->buildLastDate == 0 ? null : date('Y-m-d H:i:s', $this->buildLastDate)),
            'packages_build_failed_count'                  => $this->buildFailedCount,
            'packages_build_failed_last_date'              => ($this->buildFailedLastDate == 0 ? null : date('Y-m-d H:i:s', $this->buildFailedLastDate)),
            'packages_count'                               => DUP_Package::getNumCompletePackages(),
        );
    }

    /**
     * Return granular permissions infos
     *
     * @return array<string, mixed>
     */
    protected function getSettingsInfos()
    {
        return array(
            'settings_archive_build_mode' => StatsUtil::getArchiveBuildMode(),
            'settings_db_build_mode'      => StatsUtil::getDbBuildMode(),
            'settings_usage_enabled'      =>  StatsBootstrap::isTrackingAllowed(),
        );
    }

    /**
     * Return unique identifier
     *
     * @return string
     */
    protected static function generateIdentifier()
    {
        $maxRand = strlen(self::IDENTIFIER_CHARS) - 1;

        $result = '';
        for ($i = 0; $i < 44; $i++) {
            $result .= substr(self::IDENTIFIER_CHARS, wp_rand(0, $maxRand), 1);
        }

        return $result;
    }
}
