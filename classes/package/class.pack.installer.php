<?php

use Duplicator\Libs\DupArchive\DupArchiveEngine;
use Duplicator\Libs\Snap\SnapCode;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapOrigFileManager;
use Duplicator\Libs\Snap\SnapWP;
use Duplicator\Libs\WpConfig\WPConfigTransformer;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
// Exit if accessed directly
/* @var $global DUP_Global_Entity */

require_once(DUPLICATOR_PLUGIN_PATH . '/classes/class.archive.config.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/utilities/class.u.zip.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/utilities/class.u.multisite.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/class.password.php');
class DUP_Installer
{
    const INSTALLER_SERVER_EXTENSION               = '.php.bak';
    const DEFAULT_INSTALLER_FILE_NAME_WITHOUT_HASH = 'installer';
    const CONFIG_ORIG_FILE_FOLDER_PREFIX           = 'source_site_';
    const CONFIG_ORIG_FILE_USERINI_ID              = 'userini';
    const CONFIG_ORIG_FILE_HTACCESS_ID             = 'htaccess';
    const CONFIG_ORIG_FILE_WPCONFIG_ID             = 'wpconfig';
    const CONFIG_ORIG_FILE_PHPINI_ID               = 'phpini';
    const CONFIG_ORIG_FILE_WEBCONFIG_ID            = 'webconfig';

    //PUBLIC
    public $File;
    public $Size = 0;
    public $OptsDBHost;
    public $OptsDBPort;
    public $OptsDBName;
    public $OptsDBUser;
    public $OptsDBCharset;
    public $OptsDBCollation;
    public $OptsSecureOn = 0;
    public $OptsSecurePass;
    public $numFilesAdded = 0;
    public $numDirsAdded  = 0;

    /** @var DUP_Package */
    protected $Package = null;
    /** @var SnapOrigFileManager */
    protected $origFileManger = null;

    /** @var WPConfigTransformer */
    private $configTransformer = null;

    /**
     *  Class contructor
     */
    public function __construct($package)
    {
        $this->Package = $package;

        if (($wpConfigPath = SnapWP::getWPConfigPath()) !== false) {
            $this->configTransformer = new WPConfigTransformer($wpConfigPath);
        }
    }

    public function build($package, $error_behavior = Dup_ErrorBehavior::Quit)
    {
        DUP_Log::Info("building installer");
        $this->Package = $package;
        $success       = false;
        if ($this->create_enhanced_installer_files()) {
            $success = $this->add_extra_files($package);
        } else {
            DUP_Log::Info("error creating enhanced installer files");
        }


        if ($success) {
            $package->BuildProgress->installer_built = true;
        } else {
            $error_message = 'Error adding installer';
            $package->BuildProgress->set_failed($error_message);
            $package->Status = DUP_PackageStatus::ERROR;
            $package->Update();
            DUP_Log::error($error_message, "Marking build progress as failed because couldn't add installer files", $error_behavior);
            //$package->BuildProgress->failed = true;
            //$package->setStatus(DUP_PackageStatus::ERROR);
        }

        return $success;
    }

    private function create_enhanced_installer_files()
    {
        $success = false;
        if ($this->create_enhanced_installer()) {
            $success = $this->create_archive_config_file();
        } else {
            DUP_Log::infoTrace("Error in create_enhanced_installer, set build failed");
        }

        return $success;
    }

    private function create_enhanced_installer()
    {
        $success            = true;
        $archive_filepath   = DUP_Settings::getSsdirTmpPath() . "/{$this->Package->Archive->File}";
        $installer_filepath = apply_filters(
            'duplicator_installer_file_path',
            DUP_Settings::getSsdirTmpPath() . "/{$this->Package->NameHash}_installer" . self::INSTALLER_SERVER_EXTENSION
        );
        $template_filepath  = DUPLICATOR_PLUGIN_PATH . '/installer/installer.tpl';
        // Replace the @@ARCHIVE@@ token
        $header             = <<<HEADER
<?php
/* ------------------------------ NOTICE ----------------------------------

If you're seeing this text when browsing to the installer, it means your
web server is not set up properly.

Please contact your host and ask them to enable "PHP" processing on your
account.
----------------------------- NOTICE --------------------------------- */
?>
HEADER;
        $installer_contents = $header . SnapCode::getSrcClassCode($template_filepath, false) . "\n/* DUPLICATOR_INSTALLER_EOF */";
        // $installer_contents     = file_get_contents($template_filepath);
        // $csrf_class_contents = file_get_contents($csrf_class_filepath);

        if (DUP_Settings::Get('archive_build_mode') == DUP_Archive_Build_Mode::DupArchive) {
            $dupLib            = DUPLICATOR_PLUGIN_PATH . '/src/Libs/DupArchive/';
            $dupExpanderCoder  = '';
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'DupArchive.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'DupArchiveExpandBasicEngine.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveReaderDirectoryHeader.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveReaderFileHeader.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveReaderGlobHeader.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveReaderHeader.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Headers/DupArchiveHeaderU.php') . "\n";
            $dupExpanderCoder .= SnapCode::getSrcClassCode($dupLib . 'Info/DupArchiveExpanderInfo.php') . "\n";
            if (strlen($dupExpanderCoder) == 0) {
                DUP_Log::error(__('Error reading DupArchive expander', 'Duplicator'), __('Error reading DupArchive expander', 'duplicator'), false);
                return false;
            }
        } else {
            $dupExpanderCoder = '';
        }

        $search_array           = array('@@ARCHIVE@@', '@@VERSION@@', '@@ARCHIVE_SIZE@@', '@@PACKAGE_HASH@@', '@@SECONDARY_PACKAGE_HASH@@', '@@DUPARCHIVE_MINI_EXPANDER@@');
        $package_hash           = $this->Package->getPackageHash();
        $secondary_package_hash = $this->Package->getSecondaryPackageHash();
        $replace_array          = array($this->Package->Archive->File, DUPLICATOR_VERSION, @filesize($archive_filepath), $package_hash, $secondary_package_hash, $dupExpanderCoder);
        $installer_contents     = str_replace($search_array, $replace_array, $installer_contents);
        if (@file_put_contents($installer_filepath, $installer_contents) === false) {
            DUP_Log::error(__('Error writing installer contents', 'duplicator'), __("Couldn't write to $installer_filepath", 'duplicator'), false);
            $success = false;
        }

        if ($success) {
            $this->Size = @filesize($installer_filepath);
        }

        return $success;
    }

    /**
     * Create archive.txt file */
    private function create_archive_config_file()
    {
        global $wpdb;
        $success                 = true;
        $archive_config_filepath = DUP_Settings::getSsdirTmpPath() . "/{$this->Package->NameHash}_archive.txt";
        $ac                      = new DUP_Archive_Config();
        $extension               = strtolower($this->Package->Archive->Format);
        $hasher                  = new DUP_PasswordHash(8, false);
        $pass_hash               = $hasher->HashPassword($this->Package->Installer->OptsSecurePass);
        $this->Package->Database->getScannerData();
//READ-ONLY: COMPARE VALUES
        $ac->created     = $this->Package->Created;
        $ac->version_dup = DUPLICATOR_VERSION;
        $ac->version_wp  = $this->Package->VersionWP;
        $ac->version_db  = $this->Package->VersionDB;
        $ac->version_php = $this->Package->VersionPHP;
        $ac->version_os  = $this->Package->VersionOS;
        $ac->dbInfo      = $this->Package->Database->info;
        $ac->packInfo    = array(
            'packageId'     => $this->Package->ID,
            'packageName'   => $this->Package->Name,
            'packageHash'   => $this->Package->getPackageHash(),
            'secondaryHash' => $this->Package->getSecondaryPackageHash()
        );
        $ac->fileInfo    = array(
            'dirCount'  => $this->Package->Archive->dirsCount,
            'fileCount' => $this->Package->Archive->filesCount,
            'size'      => $this->Package->Archive->Size
        );
        $ac->wpInfo      = $this->getWpInfo();

        $ac->installer_base_name   = 'installer' . self::INSTALLER_SERVER_EXTENSION;
        $ac->installer_backup_name = $this->Package->NameHash . '_installer-backup.php';
        $ac->package_name          = "{$this->Package->NameHash}_archive.{$extension}";
        $ac->package_hash          = $this->Package->getPackageHash();
        $ac->package_notes         = $this->Package->Notes;
        $ac->opts_delete           = SnapJson::jsonEncode($GLOBALS['DUPLICATOR_OPTS_DELETE']);
        $ac->blogname              = esc_html(get_option('blogname'));

        $ac->exportOnlyDB = $this->Package->Archive->ExportOnlyDB;

        // PRE-FILLED: GENERAL
        $ac->secure_on   = filter_var($this->Package->Installer->OptsSecureOn, FILTER_VALIDATE_BOOLEAN);
        $ac->secure_pass = $pass_hash;
        $ac->dbhost      = (strlen($this->Package->Installer->OptsDBHost) ? $this->Package->Installer->OptsDBHost : null);
        $ac->dbname      = (strlen($this->Package->Installer->OptsDBName) ? $this->Package->Installer->OptsDBName : null);
        $ac->dbuser      = (strlen($this->Package->Installer->OptsDBUser) ? $this->Package->Installer->OptsDBUser : null);
        $ac->dbpass      = null;

        $ac->mu_mode        = DUP_MU::getMode();
        $ac->wp_tableprefix = $wpdb->base_prefix;
        $ac->mu_generation  = DUP_MU::getGeneration();
        $ac->mu_is_filtered = !empty($this->Package->Multisite->FilterSites) ? true : false;

        $ac->mu_siteadmins = array_values(get_super_admins());
        $filteredTables    = ($this->Package->Database->FilterOn && isset($this->Package->Database->FilterTables)) ? explode(',', $this->Package->Database->FilterTables) : array();
        $ac->subsites      = DUP_MU::getSubsites(array(), $filteredTables, $this->Package->Archive->FilterInfo->Dirs->Instance);
        if ($ac->subsites === false) {
            DUP_Log::error("Error get subsites", "Couldn't get subisites", false);
            $success = false;
        }
        $ac->main_site_id   = SnapWP::getMainSiteId();
        $ac->brand          = array(
            'name' => "Duplicator",
            'isDefault' => true,
            'logo' => '<i class="fa fa-bolt fa-sm"></i> Duplicator',
            'enabled' => false,
            'style' => array()
        );
        $ac->wp_tableprefix = $wpdb->base_prefix;
        $ac->mu_mode        = DUP_MU::getMode();

        $ac->overwriteInstallerParams = apply_filters('duplicator_overwrite_params_data', $this->getPrefillParams());
        $json                         = SnapJson::jsonEncodePPrint($ac);
        DUP_Log::TraceObject('json', $json);
        if (file_put_contents($archive_config_filepath, $json) === false) {
            DUP_Log::error("Error writing archive config", "Couldn't write archive config at $archive_config_filepath", Dup_ErrorBehavior::LogOnly);
            $success = false;
        }

        return $success;
    }

    private function getPrefillParams()
    {
        $result = array();
        if (strlen($this->OptsDBHost) > 0) {
            $result['dbhost'] = array('value' => $this->OptsDBHost);
        }

        if (strlen($this->OptsDBName) > 0) {
            $result['dbname'] = array('value' => $this->OptsDBName);
        }

        if (strlen($this->OptsDBUser) > 0) {
            $result['dbuser'] = array('value' => $this->OptsDBUser);
        }

        $result['blogname'] = array('value' => esc_html(get_option('blogname')));

        return $result;
    }

    /**
     * get wpInfo object
     *
     * @return \stdClass
     */
    private function getWpInfo()
    {
        $wpInfo               = new stdClass();
        $wpInfo->version      = $this->Package->VersionWP;
        $wpInfo->is_multisite = is_multisite();
        if (function_exists('get_current_network_id')) {
            $wpInfo->network_id = get_current_network_id();
        } else {
            $wpInfo->network_id = 1;
        }

        $wpInfo->targetRoot          = DUP_Archive::getTargetRootPath();
        $wpInfo->targetPaths         = DUP_Archive::getScanPaths();
        $wpInfo->adminUsers          = SnapWP::getAdminUserLists();
        $wpInfo->configs             = new stdClass();
        $wpInfo->configs->defines    = new stdClass();
        $wpInfo->configs->realValues = new stdClass();
        $wpInfo->plugins             = $this->getPluginsInfo();
        $wpInfo->themes              = $this->getThemesInfo();

        $this->addDefineIfExists($wpInfo->configs->defines, 'ABSPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DB_CHARSET');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DB_COLLATE');
        $this->addDefineIfExists(
            $wpInfo->configs->defines,
            'MYSQL_CLIENT_FLAGS',
            array('Duplicator\\Libs\\Snap\\SnapDB', 'getMysqlConnectFlagsFromMaskVal')
        );
        $this->addDefineIfExists($wpInfo->configs->defines, 'AUTH_KEY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SECURE_AUTH_KEY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'LOGGED_IN_KEY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'NONCE_KEY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'AUTH_SALT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SECURE_AUTH_SALT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'LOGGED_IN_SALT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'NONCE_SALT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_SITEURL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_HOME');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_CONTENT_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_CONTENT_URL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_PLUGIN_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_PLUGIN_URL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'PLUGINDIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'UPLOADS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'AUTOSAVE_INTERVAL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_POST_REVISIONS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'COOKIE_DOMAIN');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_ALLOW_MULTISITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'ALLOW_MULTISITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'MULTISITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DOMAIN_CURRENT_SITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'PATH_CURRENT_SITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SITE_ID_CURRENT_SITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'BLOG_ID_CURRENT_SITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SUBDOMAIN_INSTALL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'VHOST');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SUNRISE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'NOBLOGREDIRECT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_DEBUG');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SCRIPT_DEBUG');
        $this->addDefineIfExists($wpInfo->configs->defines, 'CONCATENATE_SCRIPTS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_DEBUG_LOG');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_DEBUG_DISPLAY');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_MEMORY_LIMIT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_MAX_MEMORY_LIMIT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_CACHE');

        // wp super cache define
        $this->addDefineIfExists($wpInfo->configs->defines, 'WPCACHEHOME');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_TEMP_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'CUSTOM_USER_TABLE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'CUSTOM_USER_META_TABLE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WPLANG');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_LANG_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SAVEQUERIES');
        $this->addDefineIfExists($wpInfo->configs->defines, 'FS_CHMOD_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'FS_CHMOD_FILE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'FS_METHOD');
        /**
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_BASE');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_CONTENT_DIR');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_PLUGIN_DIR');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_PUBKEY');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_PRIKEY');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_USER');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_PASS');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_HOST');
          $this->addDefineIfExists($wpInfo->configs->defines, 'FTP_SSL');
         * */
        $this->addDefineIfExists($wpInfo->configs->defines, 'ALTERNATE_WP_CRON');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DISABLE_WP_CRON');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_CRON_LOCK_TIMEOUT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'COOKIEPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'SITECOOKIEPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'ADMIN_COOKIE_PATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'PLUGINS_COOKIE_PATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'TEMPLATEPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'STYLESHEETPATH');
        $this->addDefineIfExists($wpInfo->configs->defines, 'EMPTY_TRASH_DAYS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_ALLOW_REPAIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DO_NOT_UPGRADE_GLOBAL_TABLES');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DISALLOW_FILE_EDIT');
        $this->addDefineIfExists($wpInfo->configs->defines, 'DISALLOW_FILE_MODS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'FORCE_SSL_ADMIN');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_HTTP_BLOCK_EXTERNAL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_ACCESSIBLE_HOSTS');
        $this->addDefineIfExists($wpInfo->configs->defines, 'AUTOMATIC_UPDATER_DISABLED');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WP_AUTO_UPDATE_CORE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'IMAGE_EDIT_OVERWRITE');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WPMU_PLUGIN_DIR');
        $this->addDefineIfExists($wpInfo->configs->defines, 'WPMU_PLUGIN_URL');
        $this->addDefineIfExists($wpInfo->configs->defines, 'MUPLUGINDIR');

        $originalUrls                               = DUP_Archive::getOriginalUrls();
        $wpInfo->configs->realValues->siteUrl       = $originalUrls['abs'];
        $wpInfo->configs->realValues->homeUrl       = $originalUrls['home'];
        $wpInfo->configs->realValues->contentUrl    = $originalUrls['wpcontent'];
        $wpInfo->configs->realValues->uploadBaseUrl = $originalUrls['uploads'];
        $wpInfo->configs->realValues->pluginsUrl    = $originalUrls['plugins'];
        $wpInfo->configs->realValues->mupluginsUrl  = $originalUrls['muplugins'];
        $wpInfo->configs->realValues->themesUrl     = $originalUrls['themes'];
        $wpInfo->configs->realValues->originalPaths = array();
        $originalpaths                              = DUP_Archive::getOriginalPaths();
        foreach ($originalpaths as $key => $val) {
            $wpInfo->configs->realValues->originalPaths[$key] = rtrim($val, '\\/');
        }
        $wpInfo->configs->realValues->archivePaths = array_merge($wpInfo->configs->realValues->originalPaths, DUP_Archive::getArchiveListPaths());
        return $wpInfo;
    }


    /**
     * Check if $define is defined and add a prop to $obj
     *
     * @param object        $obj
     * @param string        $define
     * @param null|callable $transformCallback if it is different from null the function is applied to the value
     *
     * @return boolean return true if define is added of false
     */
    private function addDefineIfExists($obj, $define, $transformCallback = null)
    {
        if (!defined($define)) {
            return false;
        }

        $obj->{$define} = new StdClass();

        if (is_callable($transformCallback)) {
            $obj->{$define}->value = call_user_func($transformCallback, constant($define));
        } else {
            if ($transformCallback !== null) {
                throw new Exception('transformCallback isn\'t callable');
            }
            $obj->{$define}->value = constant($define);
        }

        if (!is_null($this->configTransformer)) {
            $obj->{$define}->inWpConfig = $this->configTransformer->exists('constant', $define);
        } else {
            $obj->{$define}->inWpConfig = false;
        }

        return true;
    }


    /**
     * get plugins array info with multisite, must-use and drop-ins
     *
     * @return array
     */
    public function getPluginsInfo()
    {
        if (!function_exists('get_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // parse all plugins
        $result = array();
        foreach (get_plugins() as $path => $plugin) {
            $result[$path]                  = self::getPluginArrayData($path, $plugin);
            $result[$path]['networkActive'] = is_plugin_active_for_network($path);
            if (!is_multisite()) {
                $result[$path]['active'] = is_plugin_active($path);
            } else {
                // if is _multisite the active value is an array with the blog ids list where the plugin is active
                $result[$path]['active'] = array();
            }
        }

        // if is _multisite the active value is an array with the blog ids list where the plugin is active
        if (is_multisite()) {
            foreach (SnapWP::getSitesIds() as $siteId) {
                switch_to_blog($siteId);
                foreach ($result as $path => $plugin) {
                    if (!$result[$path]['networkActive'] && is_plugin_active($path)) {
                        $result[$path]['active'][] = $siteId;
                    }
                }
                restore_current_blog();
            }
        }

        // parse all must use plugins
        foreach (get_mu_plugins() as $path => $plugin) {
            $result[$path]            = self::getPluginArrayData($path, $plugin);
            $result[$path]['mustUse'] = true;
        }

        // parse all dropins plugins
        foreach (get_dropins() as $path => $plugin) {
            $result[$path]            = self::getPluginArrayData($path, $plugin);
            $result[$path]['dropIns'] = true;
        }

        return $result;
    }

    /**
     * return plugin formatted data from plugin info
     * plugin info =  Array (
     *      [Name] => Hello Dolly
     *      [PluginURI] => http://wordpress.org/extend/plugins/hello-dolly/
     *      [Version] => 1.6
     *      [Description] => This is not just ...
     *      [Author] => Matt Mullenweg
     *      [AuthorURI] => http://ma.tt/
     *      [TextDomain] =>
     *      [DomainPath] =>
     *      [Network] =>
     *      [Title] => Hello Dolly
     *      [AuthorName] => Matt Mullenweg
     * )
     *
     * @param string $slug      // plugin slug
     * @param array $plugin     // pluhin info from get_plugins function
     * @return array
     */
    protected static function getPluginArrayData($slug, $plugin)
    {
        return array(
            'slug'          => $slug,
            'name'          => $plugin['Name'],
            'version'       => $plugin['Version'],
            'pluginURI'     => $plugin['PluginURI'],
            'author'        => $plugin['Author'],
            'authorURI'     => $plugin['AuthorURI'],
            'description'   => $plugin['Description'],
            'title'         => $plugin['Title'],
            'networkActive' => false,
            'active'        => false,
            'mustUse'       => false,
            'dropIns'       => false
        );
    }

    /**
     * get themes array info with active template, stylesheet
     *
     * @return array
     */
    public function getThemesInfo()
    {
        if (!function_exists('wp_get_themes')) {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
        }

        foreach (wp_get_themes() as $slug => $theme) {
            $result[$slug] = self::getThemeArrayData($theme);
        }

        if (is_multisite()) {
            foreach (SnapWP::getSitesIds() as $siteId) {
                switch_to_blog($siteId);
                $stylesheet = get_stylesheet();
                if (isset($result[$stylesheet])) {
                    $result[$stylesheet]['isActive'][] = $siteId;
                }
                restore_current_blog();
            }
        } else {
            $stylesheet = get_stylesheet();
            if (isset($result[$stylesheet])) {
                $result[$stylesheet]['isActive'] = true;
            }
        }

        return $result;
    }

    /**
     * return plugin formatted data from plugin info
     *
     * @param WP_Theme $theme instance of WP Core class WP_Theme. theme info from get_themes function
     * @return array
     */
    protected static function getThemeArrayData(WP_Theme $theme)
    {
        $slug   = $theme->get_stylesheet();
        $parent = $theme->parent();
        return array(
            'slug'         => $slug,
            'themeName'    => $theme->get('Name'),
            'version'      => $theme->get('Version'),
            'themeURI'     => $theme->get('ThemeURI'),
            'parentTheme'  => (false === $parent) ? false : $parent->get_stylesheet(),
            'template'     => $theme->get_template(),
            'stylesheet'   => $theme->get_stylesheet(),
            'description'  => $theme->get('Description'),
            'author'       => $theme->get('Author'),
            "authorURI"    => $theme->get('AuthorURI'),
            'tags'         => $theme->get('Tags'),
            'isAllowed'    => $theme->is_allowed(),
            'isActive'     => (is_multisite() ? array() : false),
            'defaultTheme' => (defined('WP_DEFAULT_THEME') && WP_DEFAULT_THEME == $slug),
        );
    }

    /**
     * return list of extra files to att to archive
     *
     * @param bool $checkExists
     * @return array
     * @throws Exception
     */
    private function getExtraFilesLists($checkExists = true)
    {
        $result = array();

        $installerFilepath = apply_filters(
            'duplicator_installer_file_path',
            DUP_Settings::getSsdirTmpPath() . "/{$this->Package->NameHash}_installer" . self::INSTALLER_SERVER_EXTENSION
        );

        $result[] = array(
            'sourcePath'  => $installerFilepath,
            'archivePath' => $this->getInstallerBackupName(),
            'label'       => 'installer backup file'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/installer/dup-installer',
            'archivePath' => '/',
            'label'       => 'dup installer folder'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/src/Libs/Snap',
            'archivePath' => 'dup-installer/libs/',
            'label'       => 'dup snaplib folder'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/src/Libs/DupArchive',
            'archivePath' => 'dup-installer/libs/',
            'label'       => 'dup snaplib folder'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/src/Libs/WpConfig',
            'archivePath' => 'dup-installer/libs/',
            'label'       => 'lib config folder'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/src/Libs/Certificates',
            'archivePath' => 'dup-installer/libs/',
            'label'       => 'SSL certificates'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/vendor/requests',
            'archivePath' => 'dup-installer/vendor/',
            'label'       => 'Requests library'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/assets/js/duplicator-tooltip.js',
            'archivePath' => 'dup-installer/assets/js/duplicator-tooltip.js',
            'label'       => 'Duplicator tooltip script'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/assets/js/popper',
            'archivePath' => 'dup-installer/assets/js/',
            'label'       => 'popper js'
        );

        $result[] = array(
            'sourcePath'  => DUPLICATOR_LITE_PATH . '/assets/js/tippy',
            'archivePath' => 'dup-installer/assets/js/',
            'label'       => 'tippy js'
        );

        $result[] = array(
            'sourcePath'  => $this->origFileManger->getMainFolder(),
            'archivePath' => 'dup-installer/',
            'label'       => 'original files folder'
        );

        $result[] = array(
            'sourcePath'  => DUP_Settings::getSsdirTmpPath() . "/{$this->Package->NameHash}_archive.txt",
            'archivePath' => $this->getArchiveTxtFilePath(),
            'label'       => 'archive descriptor file'
        );

        $result[] = array(
            'sourcePath'  => DUP_Settings::getSsdirTmpPath() . "/{$this->Package->NameHash}_scan.json",
            'archivePath' => $this->getEmbeddedScanFilePath(),
            'label'       => 'scan file'
        );

        $result[] = array(
            'sourcePath'  => DUP_Settings::getSsdirTmpPath() . '/' . $this->Package->NameHash . DUP_Archive::FILES_LIST_FILE_NAME_SUFFIX,
            'archivePath' => $this->getEmbeddedScanFileList(),
            'label'       => 'files list file'
        );

        $result[] = array(
            'sourcePath'  => DUP_Settings::getSsdirTmpPath() . '/' . $this->Package->NameHash . DUP_Archive::DIRS_LIST_FILE_NAME_SUFFIX,
            'archivePath' => $this->getEmbeddedScanDirList(),
            'label'       => 'folders list file'
        );

        $result[] = array(
            'sourcePath'  => $this->getManualExtractFilePath(),
            'archivePath' => $this->getEmbeddedManualExtractFilePath(),
            'label'       => 'manual extract file'
        );


        if ($checkExists) {
            foreach ($result as $item) {
                if (!is_readable($item['sourcePath'])) {
                    throw new Exception('INSTALLER FILES: "' . $item['label'] . '" doesn\'t exist ' . $item['sourcePath']);
                }
            }
        }

        return $result;
    }

    public function getInstallerBackupName()
    {
        return $this->Package->NameHash . '_installer-backup.php';
    }

    private function getEmbeddedScanFileList()
    {
        return 'dup-installer/dup-scanned-files__' . $this->Package->getPackageHash() . '.txt';
    }

    private function getEmbeddedScanDirList()
    {
        return 'dup-installer/dup-scanned-dirs__' . $this->Package->getPackageHash() . '.txt';
    }

    /**
     *  createZipBackup
     *  Puts an installer zip file in the archive for backup purposes.
     */
    private function add_extra_files()
    {
        $success          = false;
        $archive_filepath = DUP_Settings::getSsdirTmpPath() . "/{$this->Package->Archive->File}";

        $this->initConfigFiles();
        $this->createManualExtractCheckFile();

        if ($this->Package->Archive->file_count != 2) {
            DUP_Log::trace("Doing archive file check");
            // Only way it's 2 is if the root was part of the filter in which case the archive won't be there
            if (file_exists($archive_filepath) == false) {
                $error_text = sprintf(__("Zip archive %1s not present.", 'dup;icator'), $archive_filepath);
                DUP_Log::error($error_text, '', Dup_ErrorBehavior::LogOnly);
                return false;
            }
        }

        if ($this->Package->Archive->Format == 'DAF') {
            $success = $this->dupArchiveAddExtra();
        } else {
            $success = $this->zipArchiveAddExtra();
        }

        try {
            $archive_config_filepath = DUP_Settings::getSsdirTmpPath() . "/{$this->Package->NameHash}_archive.txt";
            // No sense keeping these files
            @unlink($archive_config_filepath);
            $this->origFileManger->deleteMainFolder();
            $this->deleteManualExtractCheckFile();
        } catch (Exception $e) {
            DUP_Log::infoTrace("Error clean temp installer file, but continue. Message: " . $e->getMessage());
        }

        $this->Package->Archive->Size = @filesize($archive_filepath);
        return $success;
    }

    /**
     *
     * @return boolean
     * @throws \Exception
     */
    private function zipArchiveAddExtra()
    {
        $zipArchive   = new ZipArchive();
        $isCompressed = true;

        if ($zipArchive->open($this->getTmpArchiveFullPath(), ZipArchive::CREATE) !== true) {
            throw new \Exception("Couldn't open zip archive ");
        }

        DUP_Log::trace("Successfully opened zip");

        foreach ($this->getExtraFilesLists() as $extraItem) {
            if (is_dir($extraItem['sourcePath'])) {
                if (!DUP_Zip_U::addDirWithZipArchive($zipArchive, $extraItem['sourcePath'], true, $extraItem['archivePath'], $isCompressed)) {
                    throw new \Exception('INSTALLER FILES: zip add ' . $extraItem['label'] . ' folder error on folder ' . $extraItem['sourcePath']);
                }
            } else {
                if (!DUP_Zip_U::addFileToZipArchive($zipArchive, $extraItem['sourcePath'], $extraItem['archivePath'], $isCompressed)) {
                    throw new \Exception('INSTALLER FILES: zip add ' . $extraItem['label'] . ' file error on file ' . $extraItem['sourcePath']);
                }
            }
        }

        if ($zipArchive->close() === false) {
            throw new \Exception("Couldn't close zip archive ");
        }

        DUP_Log::trace('After ziparchive close when adding installer');

        $this->zipArchiveCheck();
        return true;
    }

    private function zipArchiveCheck()
    {
        /* ------ ZIP CONSISTENCY CHECK ------ */
        DUP_Log::trace("Running ZipArchive consistency check");
        $zip = new ZipArchive();

        // ZipArchive::CHECKCONS will enforce additional consistency checks
        $res = $zip->open($this->getTmpArchiveFullPath(), ZipArchive::CHECKCONS);
        if ($res !== true) {
            $consistency_error = sprintf(__('ERROR: Cannot open created archive. Error code = %1$s', 'duplicator'), $res);
            DUP_Log::trace($consistency_error);
            switch ($res) {
                case ZipArchive::ER_NOZIP:
                    $consistency_error = __('ERROR: Archive is not valid zip archive.', 'duplicator');
                    break;
                case ZipArchive::ER_INCONS:
                    $consistency_error = __("ERROR: Archive doesn't pass consistency check.", 'duplicator');
                    break;
                case ZipArchive::ER_CRC:
                    $consistency_error = __("ERROR: Archive checksum is bad.", 'duplicator');
                    break;
            }

            throw new \Exception($consistency_error);
        }

        $failed = false;
        foreach ($this->getInstallerPathsForIntegrityCheck() as $path) {
            if ($zip->locateName($path) === false) {
                $failed = true;
                DUP_Log::infoTrace(__("Couldn't find $path in archive", 'duplicator'));
            }
        }

        if ($failed) {
            DUP_Log::info(__('ARCHIVE CONSISTENCY TEST: FAIL', 'duplicator'));
            throw new \Exception("Zip for package " . $this->Package->ID . " didn't passed consistency test");
        } else {
            DUP_Log::info(__('ARCHIVE CONSISTENCY TEST: PASS', 'duplicator'));
            DUP_Log::trace("Zip for package " . $this->Package->ID . " passed consistency test");
        }

        $zip->close();
    }

    public function getInstallerPathsForIntegrityCheck()
    {
        $filesToValidate = array(
            'dup-installer/assets/index.php',
            'dup-installer/classes/index.php',
            'dup-installer/ctrls/index.php',
            'dup-installer/src/Utils/Autoloader.php',
            'dup-installer/templates/default/page-help.php',
            'dup-installer/main.installer.php',
        );

        foreach ($this->getExtraFilesLists() as $extraItem) {
            if (is_file($extraItem['sourcePath'])) {
                $filesToValidate[] = $extraItem['archivePath'];
            } else {
                if (file_exists(trailingslashit($extraItem['sourcePath']) . '/index.php')) {
                    $filesToValidate[] = ltrim(trailingslashit($extraItem['archivePath']), '\\/') . basename($extraItem['sourcePath']) . '/index.php';
                } else {
                    // SKIP CHECK
                }
            }
        }

        return array_unique($filesToValidate);
    }

    private function dupArchiveAddExtra()
    {

        $logger = new DUP_DupArchive_Logger();
        DupArchiveEngine::init($logger, null);

        $archivePath   = $this->getTmpArchiveFullPath();
        $extraPoistion = filesize($archivePath);

        foreach ($this->getExtraFilesLists() as $extraItem) {
            if (is_dir($extraItem['sourcePath'])) {
                $basePath = dirname($extraItem['sourcePath']);
                $destPath = ltrim(trailingslashit($extraItem['archivePath']), '\\/');
                $result   = DupArchiveEngine::addDirectoryToArchiveST($archivePath, $extraItem['sourcePath'], $basePath, true, $destPath);

                $this->numFilesAdded += $result->numFilesAdded;
                $this->numDirsAdded  += $result->numDirsAdded;
            } else {
                DupArchiveEngine::addRelativeFileToArchiveST($archivePath, $extraItem['sourcePath'], $extraItem['archivePath']);
                $this->numFilesAdded++;
            }
        }

        // store extra files position
        $src  = json_encode(array(DupArchiveEngine::EXTRA_FILES_POS_KEY => $extraPoistion));
        $src .= str_repeat("\0", DupArchiveEngine::INDEX_FILE_SIZE - strlen($src));
        DupArchiveEngine::replaceFileContent($archivePath, $src, DupArchiveEngine::INDEX_FILE_NAME, 0, 3000);

        return true;
    }

    /**
     *
     * @return string
     */
    public function getTmpArchiveFullPath()
    {
        return DUP_Settings::getSsdirTmpPath()  . '/' . $this->Package->Archive->File;
    }

    /**
     * Clear out sensitive database connection information
     *
     * @param $temp_conf_ark_file_path Temp config file path
     * @throws Exception
     */
    private static function cleanTempWPConfArkFilePath($temp_conf_ark_file_path)
    {
        try {
            if (function_exists('token_get_all')) {
                $transformer = new WPConfigTransformer($temp_conf_ark_file_path);
                $constants   = array('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST');
                foreach ($constants as $constant) {
                    if ($transformer->exists('constant', $constant)) {
                        $transformer->update('constant', $constant, '');
                    }
                }
            }
        } catch (Exception $e) {
            DUP_Log::infoTrace("Can\'t inizialize wp-config transformer Message: " . $e->getMessage());
        } catch (Error $e) {
            DUP_Log::infoTrace("Can\'t inizialize wp-config transformer Message: " . $e->getMessage());
        }
    }

    /**
     * Get scan.json file path along with name in archive file
     */
    private function getEmbeddedScanFilePath()
    {
        $package_hash                = $this->Package->getPackageHash();
        $embedded_scan_ark_file_path = 'dup-installer/dup-scan__' . $package_hash . '.json';
        return $embedded_scan_ark_file_path;
    }

    /**
     * Get archive.txt file path along with name in archive file
     */
    private function getArchiveTxtFilePath()
    {
        $package_hash          = $this->Package->getPackageHash();
        $archive_txt_file_path = 'dup-installer/dup-archive__' . $package_hash . '.txt';
        return $archive_txt_file_path;
    }

    /**
     * Creates the original_files_ folder in the tmp directory where all config files are saved
     * to be later added to the archives
     *
     * @throws Exception
     */
    public function initConfigFiles()
    {
        $this->origFileManger = new SnapOrigFileManager(
            DUP_Archive::getArchiveListPaths('home'),
            DUP_Settings::getSsdirTmpPath(),
            $this->Package->getPackageHash()
        );
        $this->origFileManger->init();
        $configFilePaths = $this->getConfigFilePaths();
        foreach ($configFilePaths as $identifier => $path) {
            if ($path !== false) {
                try {
                    $this->origFileManger->addEntry($identifier, $path, SnapOrigFileManager::MODE_COPY, self::CONFIG_ORIG_FILE_FOLDER_PREFIX . $identifier);
                } catch (Exception $ex) {
                    DUP_Log::Info("Error while handling config files: " . $ex->getMessage());
                }
            }
        }

        //Clean sensitive information from wp-config.php file.
        self::cleanTempWPConfArkFilePath($this->origFileManger->getEntryStoredPath(self::CONFIG_ORIG_FILE_WPCONFIG_ID));
    }

    /**
     * Gets config files path
     *
     * @return string[] array of config files in identifier => path format
     */
    public function getConfigFilePaths()
    {
        $home        = DUP_Archive::getArchiveListPaths('home');
        $configFiles = array(
            self::CONFIG_ORIG_FILE_USERINI_ID   => $home . '/.user.ini',
            self::CONFIG_ORIG_FILE_PHPINI_ID    => $home . '/php.ini',
            self::CONFIG_ORIG_FILE_WEBCONFIG_ID => $home . '/web.config',
            self::CONFIG_ORIG_FILE_HTACCESS_ID  => $home . '/.htaccess',
            self::CONFIG_ORIG_FILE_WPCONFIG_ID  => SnapWP::getWPConfigPath()
        );
        foreach ($configFiles as $identifier => $path) {
            if (!file_exists($path)) {
                unset($configFiles[$identifier]);
            }
        }

        return $configFiles;
    }

    private function createManualExtractCheckFile()
    {
        $file_path = $this->getManualExtractFilePath();
        return SnapIO::filePutContents($file_path, '');
    }

    private function getManualExtractFilePath()
    {
        return DUP_Settings::getSsdirTmpPath() . '/dup-manual-extract__' . $this->Package->getPackageHash();
    }

    private function getEmbeddedManualExtractFilePath()
    {
        $embedded_filepath = 'dup-installer/dup-manual-extract__' . $this->Package->getPackageHash();
        return $embedded_filepath;
    }

    private function deleteManualExtractCheckFile()
    {
        SnapIO::rm($this->getManualExtractFilePath());
    }
}
