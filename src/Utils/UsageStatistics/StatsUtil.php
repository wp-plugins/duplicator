<?php

namespace Duplicator\Utils\UsageStatistics;

use DUP_Archive_Build_Mode;
use DUP_DB;
use DUP_Settings;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapWP;
use Exception;

class StatsUtil
{
    /**
     * Get server type
     *
     * @return string
     */
    public static function getServerType()
    {
        if (empty($_SERVER['SERVER_SOFTWARE'])) {
            return 'unknown';
        }
        return SnapUtil::sanitizeNSCharsNewlineTrim(wp_unslash($_SERVER['SERVER_SOFTWARE']));
    }

    /**
     * Get db mode
     *
     * @return string
     */
    public static function getDbBuildMode()
    {
        switch (DUP_DB::getBuildMode()) {
            case DUP_DB::BUILD_MODE_MYSQLDUMP:
                return 'mysqldump';
            case DUP_DB::BUILD_MODE_PHP_SINGLE_THREAD:
                return 'php-single';
            default:
                throw new Exception('Unknown db build mode');
        }
    }

    /**
     * Get archive mode
     *
     * @return string
     */
    public static function getArchiveBuildMode()
    {
        if (DUP_Settings::Get('archive_build_mode') == DUP_Archive_Build_Mode::ZipArchive) {
            return 'zip-single';
        } else {
            return 'dup';
        }
    }

    /**
     * Return license types
     *
     * @param ?int $type License type, if null will use current license type
     *
     * @return string
     */
    public static function getLicenseType($type = null)
    {
        return 'unlicensed';
    }

    /**
     * Return license status
     *
     * @return string
     */
    public static function getLicenseStatus()
    {
        return 'invalid';
    }

    /**
     * Get install type
     *
     * @param int $type Install type
     *
     * @return string
     */
    public static function getInstallType($type)
    {
        switch ($type) {
            case -1:
                return 'single';
            case 4:
                return 'single_on_subdomain';
            case 5:
                return 'single_on_subfolder';
            case 8:
                return 'rbackup_single';
            default:
                return 'not_set';
        }
    }

    /**
     * Get stats components
     *
     * @param string[] $components Components
     *
     * @return string
     */
    public static function getStatsComponents($components)
    {
        $result = array();
        foreach ($components as $component) {
            switch ($component) {
                case 'package_component_db':
                    $result[] = 'db';
                    break;
                case 'package_component_core':
                    $result[] = 'core';
                    break;
                case 'package_component_plugins':
                    $result[] = 'plugins';
                    break;
                case 'package_component_plugins_active':
                    $result[] = 'plugins_active';
                    break;
                case 'package_component_themes':
                    $result[] = 'themes';
                    break;
                case 'package_component_themes_active':
                    $result[] = 'themes_active';
                    break;
                case 'package_component_uploads':
                    $result[] = 'uploads';
                    break;
                case 'package_component_other':
                    $result[] = 'other';
                    break;
            }
        }
        return implode(',', $result);
    }

    /**
     * Get am family plugins
     *
     * @return string
     */
    public static function getAmFamily()
    {
        $result   = array();
        $result[] = 'dup-pro';
        if (SnapWP::isPluginInstalled('duplicator/duplicator.php')) {
            $result[] = 'dup-lite';
        }

        return implode(',', $result);
    }

    /**
     * Get logic modes
     *
     * @param string[] $modes Logic modes
     *
     * @return string
     */
    public static function getLogicModes($modes)
    {
        $result = array();
        foreach ($modes as $mode) {
            switch ($mode) {
                case 'CLASSIC':
                    $result[] = 'CLASSIC';
                    break;
                case 'OVERWRITE':
                    $result[] = 'OVERWRITE';
                    break;
                case 'RESTORE_BACKUP':
                    $result[] = 'RESTORE';
                    break;
            }
        }
        return implode(',', $result);
    }

    /**
     * Get template
     *
     * @param string $template Template
     *
     * @return string
     */
    public static function getTemplate($template)
    {
        switch ($template) {
            case 'base':
                return 'CLASSIC_BASE';
            case 'import-base':
                return 'IMPORT_BASE';
            case 'import-advanced':
                return 'IMPORT_ADV';
            case 'recovery':
                return 'RECOVERY';
            case 'default':
            default:
                return 'CLASSIC_ADV';
        }
    }

    /**
     * Sanitize fields with rule string
     * [nullable][type][|max:number]
     * - ?string|max:25
     * - int
     *
     * @param array<string, mixed>  $data  Data
     * @param array<string, string> $rules Rules
     *
     * @return array<string, mixed>
     */
    public static function sanitizeFields($data, $rules)
    {
        foreach ($data as $key => $val) {
            if (!isset($rules[$key])) {
                continue;
            }

            $matches = null;
            if (preg_match('/(\??)(int|float|bool|string)(?:\|max:(\d+))?/', $rules[$key], $matches) !== 1) {
                throw new Exception("Invalid sanitize rule: {$rules[$key]}");
            }

            $nullable = $matches[1] === '?';
            $type     = $matches[2];
            $max      = isset($matches[3]) ? (int) $matches[3] : PHP_INT_MAX;

            if ($nullable && $val === null) {
                continue;
            }

            switch ($type) {
                case 'int':
                    $data[$key] = (int) $val;
                    break;
                case 'float':
                    $data[$key] = (float) $val;
                    break;
                case 'bool':
                    $data[$key] = (bool) $val;
                    break;
                case 'string':
                    $data[$key] = substr((string) $val, 0, $max);
                    break;
                default:
                    throw new Exception("Unknown sanitize rule: {$rules[$key]}");
            }
        }

        return $data;
    }
}
