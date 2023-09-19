<?php

namespace Duplicator\Utils\UsageStatistics;

use DUP_DB;
use Duplicator\Core\MigrationMng;
use Duplicator\Libs\Snap\SnapDB;
use Duplicator\Libs\Snap\SnapUtil;
use wpdb;

class InstallerData
{
    /**
     * @var ?self
     */
    private static $instance = null;

    /**
     * Class constructor
     */
    private function __construct()
    {
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
     * Return usage tracking data
     *
     * @return array<string, mixed>
     */
    public function getDataToSend()
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $data = (object) MigrationMng::getMigrationData();

        $result = array(
            'api_version'     => CommStats::API_VERSION,
            'plugin'          => $data->plugin,
            'plugin_version'  => $data->installerVersion,
            'install_type'    => StatsUtil::getInstallType($data->installType),
            'logic_modes'     => StatsUtil::getLogicModes($data->logicModes),
            'template'        => StatsUtil::getTemplate($data->template),
            'wp_version'      => get_bloginfo('version'),
            'db_engine'       => SnapDB::getDBEngine($wpdb->dbh), // @phpstan-ignore-line
            'db_version'      => DUP_DB::getVersion(),
            // SOURCE SITE INFO
            'source_phpv'     => SnapUtil::getVersion($data->phpVersion, 3),
            // TARGET SITE INFO
            'target_phpv'     => SnapUtil::getVersion(phpversion(), 3),
            // PACKAGE INFO
            'license_type'    => StatsUtil::getLicenseType($data->licenseType),
            'archive_type'    => $data->archiveType,
            'site_size_mb'    => round(((int) $data->siteSize) / 1024 / 1024, 2),
            'site_num_files'  => $data->siteNumFiles,
            'site_db_size_mb' => round(((int) $data->siteDbSize) / 1024 / 1024, 2),
            'site_db_num_tbl' => $data->siteDBNumTables,
            'components'      => StatsUtil::getStatsComponents($data->components),
        );

        $rules = array(
            'api_version'    => 'string|max:7', // 1.0
            'plugin_version' => 'string|max:25',
            'wp_version'     => 'string|max:25',
            'db_engine'      => 'string|max:25',
            'db_version'     => 'string|max:25',
            // SOURCE SERVER INFO
            'source_phpv'    => 'string|max:25',
            // TARGET SERVER INFO
            'target_phpv'    => 'string|max:25',
        );
        return StatsUtil::sanitizeFields($result, $rules);
    }
}
