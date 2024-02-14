<?php

use Duplicator\Libs\Snap\SnapDB;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapWP;

class DUP_MU
{
    public static function networkMenuPageUrl($menu_slug, $echo = true)
    {
        global $_parent_pages;

        if (isset($_parent_pages[$menu_slug])) {
            $parent_slug = $_parent_pages[$menu_slug];
            if ($parent_slug && !isset($_parent_pages[$parent_slug])) {
                $url = network_admin_url(add_query_arg('page', $menu_slug, $parent_slug));
            } else {
                $url = network_admin_url('admin.php?page=' . $menu_slug);
            }
        } else {
            $url = '';
        }

        $url = esc_url($url);

        if ($echo) {
            echo esc_url($url);
        }

        return $url;
    }

    /**
     * return multisite mode
     * 0 = single site
     * 1 = multisite subdomain
     * 2 = multisite subdirectory
     *
     * @return int
     */
    public static function getMode()
    {
        if (is_multisite()) {
            if (defined('SUBDOMAIN_INSTALL') && SUBDOMAIN_INSTALL) {
                return 1;
            } else {
                return 2;
            }
        } else {
            return 0;
        }
    }

    /**
     * This function is wrong because it assumes that if the folder sites exist, blogs.dir cannot exist.
     * This is not true because if the network is old but a new site is created after the WordPress update both blogs.dir and sites folders exist.
     *
     * @deprecated since version 3.8.4
     *
     * @return int
     */
    public static function getGeneration()
    {
        if (self::getMode() == 0) {
            return 0;
        } else {
            $sitesDir = WP_CONTENT_DIR . '/uploads/sites';

            if (file_exists($sitesDir)) {
                return 2;
            } else {
                return 1;
            }
        }
    }

    /**
     *
     * @param array $filteredSites
     * @param array $filteredTables
     * @param array $filteredPaths
     *
     * @return array
     */
    public static function getSubsites($filteredSites = array(), $filteredTables = array(), $filteredPaths = array())
    {
        if (!is_multisite()) {
            return array(
                self::getSubsiteInfo(1, $filteredTables, $filteredPaths)
            );
        }

        $site_array    = array();
        $filteredSites = is_array($filteredSites) ? $filteredSites : array();

        DUP_Log::trace("NETWORK SITES");

        foreach (SnapWP::getSitesIds() as $siteId) {
            if (in_array($siteId, $filteredSites)) {
                continue;
            }
            if (($siteInfo = self::getSubsiteInfo($siteId, $filteredTables, $filteredPaths)) == false) {
                continue;
            }
            array_push($site_array, $siteInfo);
            DUP_Log::trace("Multisite subsite detected. ID={$siteInfo->id} Domain={$siteInfo->domain} Path={$siteInfo->path} Blogname={$siteInfo->blogname}");
        }

        return $site_array;
    }

    /**
     *
     * @param int $subsiteId
     *
     * @return stdClass|bool false on failure
     */
    public static function getSubsiteInfoById($subsiteId)
    {
        if (!is_multisite()) {
            $subsiteId = 1;
        }
        return self::getSubsiteInfo($subsiteId);
    }

    /**
     * Get subsite info
     *
     * @param int         $siteId
     * @param array       $filteredTables
     * @param array|false $filteredPaths return
     *
     * @return stdClass|bool false on failure
     */
    public static function getSubsiteInfo($siteId = 1, $filteredTables = array(), $filteredPaths = array())
    {
        if (is_multisite()) {
            if (($siteDetails = get_blog_details($siteId)) == false) {
                return false;
            }
        } else {
            $siteId                = 1;
            $siteDetails           = new stdClass();
            $home                  = DUP_Archive::getOriginalUrls('home');
            $parsedHome            = SnapURL::parseUrl($home);
            $siteDetails->domain   = $parsedHome['host'];
            $siteDetails->path     = trailingslashit($parsedHome['path']);
            $siteDetails->blogname = sanitize_text_field(get_option('blogname'));
        }

        $subsiteID             = $siteId;
        $siteInfo              = new stdClass();
        $siteInfo->id          = $subsiteID;
        $siteInfo->domain      = $siteDetails->domain;
        $siteInfo->path        = $siteDetails->path;
        $siteInfo->blogname    = $siteDetails->blogname;
        $siteInfo->blog_prefix = $GLOBALS['wpdb']->get_blog_prefix($subsiteID);
        if (count($filteredTables) > 0) {
            $siteInfo->filteredTables = array_values(array_intersect(self::getSubsiteTables($subsiteID), $filteredTables));
        } else {
            $siteInfo->filteredTables = array();
        }
        $siteInfo->adminUsers  = SnapWP::getAdminUserLists($siteInfo->id);
        $siteInfo->fullHomeUrl = get_home_url($siteId);
        $siteInfo->fullSiteUrl = get_site_url($siteId);

        if ($siteId > 1) {
            switch_to_blog($siteId);
        }

        $uploadData                   = wp_upload_dir();
        $uploadPath                   = $uploadData['basedir'];
        $siteInfo->uploadPath         = SnapIO::getRelativePath($uploadPath, DUP_Archive::getTargetRootPath(), true);
        $siteInfo->fullUploadPath     = untrailingslashit($uploadPath);
        $siteInfo->fullUploadSafePath = SnapIO::safePathUntrailingslashit($uploadPath);
        $siteInfo->fullUploadUrl      = $uploadData['baseurl'];
        if (count($filteredPaths)) {
            $globalDirFilters        = apply_filters('duplicator_global_file_filters', $GLOBALS['DUPLICATOR_GLOBAL_FILE_FILTERS']);
            $siteInfo->filteredPaths = array_values(array_filter($filteredPaths, function ($path) use ($uploadPath, $subsiteID, $globalDirFilters) {
                if (
                    ($relativeUpload = SnapIO::getRelativePath($path, $uploadPath)) === false ||
                    in_array($path, $globalDirFilters)
                ) {
                    return false;
                }

                if ($subsiteID > 1) {
                    return true;
                } else {
                    // no check on blogs.dir because in wp-content/blogs.dir not in upload folder
                    return !(strpos($relativeUpload, 'sites') === 0);
                }
            }));
        } else {
            $siteInfo->filteredPaths = array();
        }

        if ($siteId > 1) {
            restore_current_blog();
        }
        return $siteInfo;
    }

    /**
     * @param int $subsiteID
     *
     * @return array List of tables belonging to subsite
     */
    public static function getSubsiteTables($subsiteID)
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $basePrefix    = $wpdb->base_prefix;
        $subsitePrefix = $wpdb->get_blog_prefix($subsiteID);

        $sharedTables        = array(
            $basePrefix . 'users',
            $basePrefix . 'usermeta'
        );
        $multisiteOnlyTables = array(
            $basePrefix . 'blogmeta',
            $basePrefix . 'blogs',
            $basePrefix . 'blog_versions',
            $basePrefix . 'registration_log',
            $basePrefix . 'signups',
            $basePrefix . 'site',
            $basePrefix . 'sitemeta'
        );

        $subsiteTables = array();
        $sql           = "";
        $dbnameSafe    = esc_sql(DB_NAME);

        if ($subsiteID != 1) {
            $regex      = '^' . SnapDB::quoteRegex($subsitePrefix);
            $regexpSafe = esc_sql($regex);

            $sharedTablesSafe = "'" . implode(
                "', '",
                esc_sql($sharedTables)
            ) . "'";
            $sql              = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbnameSafe' AND ";
            $sql             .= "(TABLE_NAME REGEXP '$regexpSafe' OR TABLE_NAME IN ($sharedTablesSafe))";
        } else {
            $regexMain        = '^' . SnapDB::quoteRegex($basePrefix);
            $regexpMainSafe   = esc_sql($regexMain);
            $regexNotSub      = '^' . SnapDB::quoteRegex($basePrefix) . '[0-9]+_';
            $regexpNotSubSafe = esc_sql($regexNotSub);

            $multisiteOnlyTablesSafe = "'" . implode(
                "', '",
                esc_sql($multisiteOnlyTables)
            ) . "'";
            $sql                     = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$dbnameSafe' AND ";
            $sql                    .= "TABLE_NAME REGEXP '$regexpMainSafe' AND ";
            $sql                    .= "TABLE_NAME NOT REGEXP '$regexpNotSubSafe' AND ";
            $sql                    .= "TABLE_NAME NOT IN ($multisiteOnlyTablesSafe)";
        }
        $subsiteTables = $wpdb->get_col($sql);
        return $subsiteTables;
    }
}
