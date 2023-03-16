<?php

/**
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Crypt
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Deploy\Database\DbCleanup;
use Duplicator\Installer\Core\Deploy\Database\DbReplace;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapDB;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapString;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\WpConfig\WPConfigTransformer;
use Duplicator\Libs\WpConfig\WPConfigTransformerSrc;

//-- START OF ACTION STEP 3: Update the database
require_once(DUPX_INIT . '/classes/utilities/class.u.search.reaplce.manager.php');
require_once(DUPX_INIT . '/classes/chunk/class.chunk.s3.manager.php');

/**
 * Step 3 functions
 * Singlethon
 */
final class DUPX_S3_Funcs
{
    const MODE_NORMAL           = 1;
    const MODE_CHUNK            = 2;
    const MODE_SKIP             = 3;
    const FIRST_LOGIN_OPTION    = 'duplicator_first_login_after_install';
    const MIGRATION_DATA_OPTION = 'duplicator_migration_data';

    /**
     *
     * @var DUPX_S3_Funcs
     */
    protected static $instance = null;

    /**
     *
     * @var array
     */
    public $cTableParams = null;

    /**
     *
     * @var array
     */
    public $report = array();

    /**
     *
     * @var int
     */
    private $timeStart = null;

    /**
     *
     * @var resource|mysqli connection
     */
    private $dbh = null;

    private function __construct()
    {
        $this->initData();
        $this->timeStart = DUPX_U::getMicrotime();
    }

    public function updateWebsite()
    {
        Log::setThrowExceptionOnError(true);
        $nManager = DUPX_NOTICE_MANAGER::getInstance();

        switch ($this->getEngineMode()) {
            case DUPX_S3_Funcs::MODE_CHUNK:
                /* START CHUNK MANAGER */
                $maxIteration = 0;     // max iteration before stop. If 0 have no limit
                // auto set prevent timeout
                $inimaxExecutionTime           = ini_get('max_execution_time');
                $maxExecutionTime              = (int) (empty($inimaxExecutionTime) ? DUPX_Constants::CHUNK_MAX_TIMEOUT_TIME : $inimaxExecutionTime);
                $timeOut                       = max(5, $maxExecutionTime - 2) * 1000;    // timeout in milliseconds before stop exectution
                $throttling                    = 2;  // sleep in milliseconds every iteration
                $GLOBALS['DATABASE_PAGE_SIZE'] = 1000;   // database pagination size for engine update queries

                /* TEST INIT SINGLE FUNC
                  $maxIteration                  = 1;     // max iteration before stop. If 0 have no limit
                  $timeOut                       = 0;    // timeout in milliseconds before stop exectution
                  $throttling                    = 0;  // sleep in milliseconds every iteration
                  $GLOBALS['DATABASE_PAGE_SIZE'] = 1000000;   // database pagination size for engine update queries
                 */

                $chunkmManager = new DUPX_chunkS3Manager($maxIteration, $timeOut, $throttling);
                if ($chunkmManager->start() === false) {
                    /* Stop executions */
                    $this->chunkStop($chunkmManager->getProgressPerc(), $chunkmManager->getLastPosition());
                } else {
                    /* step 3 completed */
                    $this->complete();
                }
                break;
            case DUPX_S3_Funcs::MODE_SKIP:
                $this->initLog();
                DbCleanup::cleanupOptions();
                DbCleanup::cleanupExtra();
                DbCleanup::cleanupPackages();
                $this->removeMaintenanceMode();
                $this->configFilesUpdate();
                $this->forceLogoutOfAllUsers();
                $this->duplicatorMigrationInfoSet();
                $this->checkForIndexHtml();
                $this->noticeTest();
                $this->cleanupTmpFiles();
                $this->setFilePermsission();
                $this->finalReportNotices();
                $this->complete();
                break;
            case DUPX_S3_Funcs::MODE_NORMAL:
            default:
                $chunkmManager = new DUPX_chunkS3Manager();
                if ($chunkmManager->start() === false) {
                    throw new Exception('No chunk in normal mode');
                }
                $this->complete();
        }

        $nManager->saveNotices();
        return $this->getJsonReport();
    }

    /**
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
     * inizialize 3sFunc data
     */
    public function initData()
    {
        // if data file exists load saved data
        if (file_exists(self::getS3dataFilePath())) {
            Log::info('LOAD S3 DATA FROM JSON', Log::LV_DETAILED);
            if ($this->loadData() == false) {
                throw new Exception('Can\'t load s3 data');
            }
        } else {
            Log::info('INIT S3 DATA', Log::LV_DETAILED);
            // else init data from $_POST
            $this->setReplaceList();
            $this->initReport();
        }
    }

    /**
     *
     * @return string
     */
    private static function getS3dataFilePath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = DUPX_INIT . '/dup-installer-s3data__' . DUPX_Package::getPackageHash() . '.json';
        }
        return $path;
    }

    /**
     *
     * @return boolean
     */
    public function saveData()
    {
        $data = array(
            'report'       => $this->report,
            'cTableParams' => $this->cTableParams,
            'replaceData'  => DUPX_S_R_MANAGER::getInstance()->getArrayData()
        );

        if (($json = SnapJson::jsonEncodePPrint($data)) === false) {
            Log::info('Can\'t encode json data');
            return false;
        }

        if (@file_put_contents(self::getS3dataFilePath(), $json) === false) {
            Log::info('Can\'t save s3 data file');
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    private function loadData()
    {
        if (!file_exists(self::getS3dataFilePath())) {
            return false;
        }

        if (($json = @file_get_contents(self::getS3dataFilePath())) === false) {
            Log::info('Can\'t load s3 data file');
            return false;
        }

        $data = json_decode($json, true);

        if (!is_array($data)) {
            Log::info('Can\'t decode json data');
            return false;
        }

        if (array_key_exists('cTableParams', $data)) {
            $this->cTableParams = $data['cTableParams'];
        } else {
            Log::info('S3 data not well formed: cTableParams not found.');
            return false;
        }

        if (array_key_exists('replaceData', $data)) {
            DUPX_S_R_MANAGER::getInstance()->setFromArrayData($data['replaceData']);
        } else {
            Log::info('S3 data not well formed: replace not found.');
            return false;
        }

        if (array_key_exists('report', $data)) {
            $this->report = $data['report'];
        } else {
            Log::info('S3 data not well formed: report not found.');
            return false;
        }

        return true;
    }

    /**
     *
     * @return boolean
     */
    public static function resetData()
    {
        $result = true;
        if (file_exists(self::getS3dataFilePath())) {
            if (@unlink(self::getS3dataFilePath()) === false) {
                Log::info('Can\'t delete s3 data file');
                $result = false;
            }
        }

        if (file_exists($GLOBALS["CHUNK_DATA_FILE_PATH"])) {
            if (@unlink($GLOBALS["CHUNK_DATA_FILE_PATH"]) === false) {
                Log::info('Can\'t delete s3 chunk file');
                $result = false;
            }
        }
        return $result;
    }

    private function initReport()
    {
        $this->report = array(
            'pass'          => 0,
            'chunk'         => 0,
            'chunkPos'      => array(),
            'progress_perc' => 0,
            'scan_tables'   => 0,
            'scan_rows'     => 0,
            'scan_cells'    => 0,
            'updt_tables'   => 0,
            'updt_rows'     => 0,
            'updt_cells'    => 0,
            'errsql'        => array(),
            'errser'        => array(),
            'errkey'        => array(),
            'errsql_sum'    => 0,
            'errser_sum'    => 0,
            'errkey_sum'    => 0,
            'profile_start' => '',
            'profile_end'   => '',
            'time'          => '',
            'err_all'       => 0,
            'warn_all'      => 0,
            'warnlist'      => array()
        );
    }

    public function getJsonReport()
    {
        $this->report['warn_all'] = empty($this->report['warnlist']) ? 0 : count($this->report['warnlist']);

        return array(
            'step3' => $this->report
        );
    }

    private static function logSectionHeader($title, $func, $line)
    {
        $log = "\n" . '====================================' . "\n" .
            $title;

        if (Log::isLevel(Log::LV_DETAILED)) {
            $log .= ' [FUNC: ' . $func . ' L:' . $line . ']';
        }
        $log .= "\n" .
            '====================================';
        Log::info($log);
    }

    /**
     * open db connection if is closed
     *
     * @return database connection handle
     */
    private function dbConnection()
    {
        if (is_null($this->dbh)) {
            $this->dbh = DUPX_DB_Functions::getInstance()->dbConnection();
        }
        return $this->dbh;
    }

    /**
     *  @return database|mysqli connection handle
     */
    public function getDbConnection()
    {
        return $this->dbConnection();
    }

    /**
     * close db connection if is open
     */
    public function closeDbConnection()
    {
        DUPX_DB_Functions::getInstance()->closeDbConnection();
        $this->dbh = null;
    }

    public function initLog()
    {
        $paramsManager = PrmMng::getInstance();
        $labelPadSize  = 22;

        // make sure dbConnection is initialized
        $this->dbConnection();

        $charsetServer = @mysqli_character_set_name($this->dbh);
        $charsetClient = @mysqli_character_set_name($this->dbh);

        //LOGGING
        $date = @date('h:i:s');
        $log  = "\n\n" .
            "********************************************************************************\n" .
            "DUPLICATOR LITE: INSTALL-LOG\n" .
            "STEP-3 START @ " . $date . "\n" .
            "NOTICE: Do NOT post to public sites or forums\n" .
            "********************************************************************************\n" .
            "CHARSET SERVER:\t" . Log::v2str($charsetServer) . "\n" .
            "CHARSET CLIENT:\t" . Log::v2str($charsetClient) . "\n" .
            "********************************************************************************\n" .
            "OPTIONS:\n";

        $log .= str_pad('SKIP PATH REPLACE', $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($paramsManager->getValue(PrmMng::PARAM_SKIP_PATH_REPLACE)) . "\n";

        $wpConfigsKeys = array(
            PrmMng::PARAM_WP_CONF_DISALLOW_FILE_EDIT,
            PrmMng::PARAM_WP_CONF_DISALLOW_FILE_MODS,
            PrmMng::PARAM_WP_CONF_AUTOSAVE_INTERVAL,
            PrmMng::PARAM_WP_CONF_WP_POST_REVISIONS,
            PrmMng::PARAM_WP_CONF_FORCE_SSL_ADMIN,
            PrmMng::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE,
            PrmMng::PARAM_WP_CONF_WP_CACHE,
            PrmMng::PARAM_WP_CONF_WPCACHEHOME,
            PrmMng::PARAM_WP_CONF_WP_DEBUG,
            PrmMng::PARAM_WP_CONF_WP_DEBUG_LOG,
            PrmMng::PARAM_WP_CONF_WP_DEBUG_DISPLAY,
            PrmMng::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER,
            PrmMng::PARAM_WP_CONF_SCRIPT_DEBUG,
            PrmMng::PARAM_WP_CONF_CONCATENATE_SCRIPTS,
            PrmMng::PARAM_WP_CONF_SAVEQUERIES,
            PrmMng::PARAM_WP_CONF_ALTERNATE_WP_CRON,
            PrmMng::PARAM_WP_CONF_DISABLE_WP_CRON,
            PrmMng::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT,
            PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN,
            PrmMng::PARAM_WP_CONF_WP_MEMORY_LIMIT,
            PrmMng::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT,
            PrmMng::PARAM_WP_CONF_WP_TEMP_DIR
        );
        foreach ($wpConfigsKeys as $key) {
            $label = $paramsManager->getLabel($key);
            $value = SnapString::implodeKeyVals(', ', $paramsManager->getValue($key), '[%s = %s]');
            $log  .= str_pad($label, $labelPadSize, '_', STR_PAD_RIGHT) . ': ' . $value . "\n";
        }
        $log .= "********************************************************************************\n";

        Log::info($log);

        $log    .= "--------------------------------------\n";
        $log    .= "KEEP PLUGINS ACTIVE\n";
        $log    .= "--------------------------------------\n";
        $plugins = $paramsManager->getValue(PrmMng::PARAM_PLUGINS);
        $log    .= (count($plugins) > 0 ? Log::v2str($plugins) : 'No plugins selected for activation');
        Log::info($log, Log::LV_DETAILED);
        Log::flush();
    }

    public function initChunkLog($maxIteration, $timeOut, $throttling, $rowsPerPage)
    {
        $log  = "********************************************************************************\n" .
            "CHUNK PARAMS:\n";
        $log .= str_pad('maxIteration', 22, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($maxIteration) . "\n";
        $log .= str_pad('timeOut', 22, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($timeOut) . "\n";
        $log .= str_pad('throttling', 22, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($throttling) . "\n";
        $log .= str_pad('rowsPerPage', 22, '_', STR_PAD_RIGHT) . ': ' . Log::v2str($rowsPerPage) . "\n";
        $log .= "********************************************************************************\n";
        Log::info($log);
    }

    /**
     * set replace list
     *
     * Auto inizialize function
     */
    public function setReplaceList()
    {
        if ($this->getEngineMode() === self::MODE_SKIP) {
            return;
        }

        self::logSectionHeader('SET SEARCH AND REPLACE LIST INSTALL TYPE ' . DUPX_InstallerState::installTypeToString(), __FUNCTION__, __LINE__);

        $dbReplace = new DbReplace();
        $dbReplace->setSearchReplace();
    }

    /**
     *
     * @return int MODE_NORAML|MODE_CHUNK|MODE_SKIP
     */
    public function getEngineMode()
    {
        return PrmMng::getInstance()->getValue(PrmMng::PARAM_REPLACE_ENGINE);
    }

    /**
     *
     * @return bool
     */
    public function isChunk()
    {
        return PrmMng::getInstance()->getValue(PrmMng::PARAM_REPLACE_ENGINE) === self::MODE_CHUNK;
    }

    public function runSearchAndReplace()
    {
        self::logSectionHeader('RUN SEARCH AND REPLACE', __FUNCTION__, __LINE__);

        $tables = DUPX_DB_Tables::getInstance()->getReplaceTablesNames();

        DUPX_UpdateEngine::load($tables);
        DUPX_UpdateEngine::logStats();
        DUPX_UpdateEngine::logErrors();
    }

    public function removeMaintenanceMode()
    {
        self::logSectionHeader('REMOVE MAINTENANCE MODE', __FUNCTION__, __LINE__);
        DUPX_U::maintenanceMode(false);
    }

    protected function resetUsersPasswords()
    {
        self::logSectionHeader('RESET USERS PASSWORD', __FUNCTION__, __LINE__);

        $usersLoginsName = DUPX_ArchiveConfig::getInstance()->getUsersLists();
        foreach (PrmMng::getInstance()->getValue(PrmMng::PARAM_USERS_PWD_RESET) as $userId => $newPassword) {
            if (strlen($newPassword) > 0) {
                Log::info('RESET USER ID ' . $userId . ' NAME ' . $usersLoginsName[$userId] . ' PASSWORD');
                DUPX_DB_Functions::getInstance()->userPwdReset($userId, $newPassword);
            }
        }
    }

    public function forceLogoutOfAllUsers()
    {
        Log::info('RESET ALL USERS SESSION TOKENS');
        $escapedTablePrefix = mysqli_real_escape_string($this->dbh, PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX));

        try {
            DUPX_DB::chunksDelete($this->dbh, $escapedTablePrefix . 'usermeta', "meta_key='session_tokens'");
        } catch (Exception $e) {
            Log::info('RESET USER SESSION TOKENS EXCEPTION: ' . $e->getMessage());
        }
    }

    public function createNewAdminUser()
    {
        $this->resetUsersPasswords();

        if (!PrmMng::getInstance()->getValue(PrmMng::PARAM_WP_ADMIN_CREATE_NEW)) {
            return;
        }

        self::logSectionHeader('CREATE NEW ADMIN USER', __FUNCTION__, __LINE__);
        // make sure dbConnection is initialized
        $this->dbConnection();

        $nManager           = DUPX_NOTICE_MANAGER::getInstance();
        $escapedTablePrefix = mysqli_real_escape_string($this->dbh, PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX));
        $archiveConfig      = DUPX_ArchiveConfig::getInstance();
        $paramsManager      = PrmMng::getInstance();

        $wpUserName        = $paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_NAME);
        $wpUserNameEscaped = mysqli_real_escape_string($this->dbh, $wpUserName);

        $newuser_check = DUPX_DB::mysqli_query(
            $this->dbh,
            "SELECT COUNT(*) AS count FROM `" . $escapedTablePrefix . "users` WHERE user_login = '{$wpUserNameEscaped}' "
        );
        $newuser_row   = mysqli_fetch_row($newuser_check);
        $newuser_count = is_null($newuser_row) ? 0 : $newuser_row[0];

        if ($newuser_count == 0) {
            $newuser_datetime = @date("Y-m-d H:i:s");
            $newuser_datetime = mysqli_real_escape_string($this->dbh, $newuser_datetime);
            $newuser_security = mysqli_real_escape_string($this->dbh, DUPX_WPConfig::ADMIN_SERIALIZED_SECURITY_STRING);

            $post_wp_password = $paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_PASSWORD);
            $post_wp_mail     = $paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_MAIL);
            $post_wp_nickname = $paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_NICKNAME);
            if (empty($post_wp_nickname)) {
                $post_wp_nickname = $wpUserName;
            }
            $post_wp_first_name = $paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_FIRST_NAME);
            $post_wp_last_name  = $paramsManager->getValue(PrmMng::PARAM_WP_ADMIN_LAST_NAME);

            $wp_password   = mysqli_real_escape_string($this->dbh, $post_wp_password);
            $wp_mail       = mysqli_real_escape_string($this->dbh, $post_wp_mail);
            $wp_nickname   = mysqli_real_escape_string($this->dbh, $post_wp_nickname);
            $wp_first_name = mysqli_real_escape_string($this->dbh, $post_wp_first_name);
            $wp_last_name  = mysqli_real_escape_string($this->dbh, $post_wp_last_name);

            $usermeta_table = $escapedTablePrefix . 'usermeta';

            $newuser1 = DUPX_DB::mysqli_query(
                $this->dbh,
                "INSERT INTO `" . $escapedTablePrefix . "users`
                        (`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `user_activation_key`, `user_status`, `display_name`)
                        VALUES ('{$wpUserNameEscaped}', MD5('{$wp_password}'), '{$wpUserNameEscaped}', '{$wp_mail}', '{$newuser_datetime}', '', '0', '{$wpUserNameEscaped}')"
            );

            $newuser1_insert_id = intval(mysqli_insert_id($this->dbh));

            $newuser2 = DUPX_DB::mysqli_query(
                $this->dbh,
                "INSERT INTO `" . $usermeta_table . "`
                        (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', '" . $escapedTablePrefix . "capabilities', '{$newuser_security}')"
            );

            $newuser3 = DUPX_DB::mysqli_query(
                $this->dbh,
                "INSERT INTO `" . $usermeta_table . "`
                        (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', '" . $escapedTablePrefix . "user_level', '10')"
            );

            //Misc Meta-Data Settings:
            DUPX_DB::mysqli_query($this->dbh, "INSERT INTO `" . $usermeta_table . "` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'rich_editing', 'true')");
            DUPX_DB::mysqli_query($this->dbh, "INSERT INTO `" . $usermeta_table . "` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'admin_color',  'fresh')");
            DUPX_DB::mysqli_query($this->dbh, "INSERT INTO `" . $usermeta_table . "` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'nickname', '{$wp_nickname}')");
            DUPX_DB::mysqli_query($this->dbh, "INSERT INTO `" . $usermeta_table . "` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'first_name', '{$wp_first_name}')");
            DUPX_DB::mysqli_query($this->dbh, "INSERT INTO `" . $usermeta_table . "` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser1_insert_id}', 'last_name', '{$wp_last_name}')");

            Log::info("\nNEW WP-ADMIN USER:");
            if ($newuser1 && $newuser2 && $newuser3) {
                Log::info("- New username '{$wpUserName}' was created successfully allong with MU usermeta.");
            } elseif ($newuser1) {
                Log::info("- New username '{$wpUserName}' was created successfully.");
            } else {
                $newuser_warnmsg            = "- Failed to create the user '{$wpUserName}' \n ";
                $this->report['warnlist'][] = $newuser_warnmsg;

                $nManager->addFinalReportNotice(array(
                    'shortMsg' => 'New admin user create error',
                    'level'    => DUPX_NOTICE_ITEM::HARD_WARNING,
                    'longMsg'  => $newuser_warnmsg,
                    'sections' => 'general'
                ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_UPDATE, 'new-user-create-error');

                Log::info($newuser_warnmsg);
            }
        } else {
            $newuser_warnmsg            = "\nNEW WP-ADMIN USER:\n - Username '{$wpUserName}' already exists in the database.  Unable to create new account.\n";
            $this->report['warnlist'][] = $newuser_warnmsg;

            $nManager->addFinalReportNotice(array(
                'shortMsg'    => 'New admin user create error',
                'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'     => $newuser_warnmsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                'sections'    => 'general'
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_UPDATE, 'new-user-create-error');

            Log::info($newuser_warnmsg);
        }
    }

    /**
     * update all config files
     */
    public function configFilesUpdate()
    {
        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        // SET FILES
        DUPX_ServerConfig::setFiles(PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW));
        $wpConfigFile = DUPX_WPConfig::getWpConfigPath();

        // UPDATE FILES
        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_WP_CONFIG) == 'nothing') {
            Log::info('SKIP WP CONFIG UPDATE');
        } elseif (file_exists(($wpConfigFile))) {
            if (SnapIO::chmod($wpConfigFile, 'u+rw') === false) {
                $err_log  = "\nWARNING: Unable to update file permissions and write to wp-config.php.  ";
                $err_log .= "Check that the wp-config.php is in the archive.zip and check with your host or administrator to enable PHP to write to the wp-config.php file.  ";
                $err_log .= "If performing a 'Manual Extraction' please be sure to select the 'Manual Archive Extraction' option on step 1 under options.";
                Log::error("{$err_log}");
            }
            $configTransformer = new WPConfigTransformer($wpConfigFile);
            $this->wpConfigUpdate($configTransformer);
            DUP_Extraction::setPermsFromParams($wpConfigFile);
        } else {
            $msg  = "WP-CONFIG NOTICE: <b>wp-config.php not found.</b><br><br>";
            $msg .= "No action on the wp-config was possible.<br>";
            $msg .= "Be sure to insert a properly modified wp-config for correct wordpress operation.";

            $nManager->addBothNextAndFinalReportNotice(array(
                'shortMsg'    => 'wp-config.php file not found',
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                'longMsg'     => $msg,
                'sections'    => 'general'
            ));
        }

        $this->htaccessUpdate();
        $this->indexPhpUpdate();
        DUPX_NOTICE_MANAGER::getInstance()->saveNotices();
    }

    /**
     * update index.php file with right wp-blog-header include related to installation ABSPATH
     *
     * @return boolean
     */
    protected function indexPhpUpdate()
    {
        $paramsManager = PrmMng::getInstance();

        if (
            DUPX_InstallerState::isRestoreBackup()
        ) {
            return;
        }

        self::logSectionHeader('INDEX.PHP UPDATE', __FUNCTION__, __LINE__);

        $pathNew   = $paramsManager->getValue(PrmMng::PARAM_PATH_NEW);
        $indexPath = $pathNew . '/index.php';

        if (!is_writable($indexPath)) {
            Log::info('index.php isn\'t writable');
            return false;
        }

        if (($relativeAbsPath = SnapIO::getRelativePath($paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW), $pathNew)) === false) {
            $blogHeaderValue = "'" . $paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW) . "/wp-blog-header.php'";
        } else {
            $relativeAbsPath = strlen($relativeAbsPath) ? '/' . $relativeAbsPath : '';
            $blogHeaderValue = "dirname(__FILE__) . '" . $relativeAbsPath . "/wp-blog-header.php'";
        }

        if (($indexContent = file_get_contents($indexPath)) === false) {
            Log::info('Can\'t read index.php content');
            return false;
        }
        $indexContent = preg_replace('/(require\s*\(.*wp-blog-header.php[\'"]\s*\))/m', 'require(' . $blogHeaderValue . ')', $indexContent);

        if (file_put_contents($indexPath, $indexContent) === false) {
            Log::info('Can\'t update index.php content');
            return false;
        }

        Log::info('INDEX.PHP updated with new blog header ' . Log::v2str($blogHeaderValue) . "\n");
        return true;
    }

    /**
     *
     * @param WPConfigTransformer $confTransformer
     *
     * @return void
     */
    protected function wpConfigUpdate(WPConfigTransformer $confTransformer)
    {
        self::logSectionHeader('CONFIGURATION FILE UPDATES', __FUNCTION__, __LINE__);
        Log::incIndent();

        $nManager      = DUPX_NOTICE_MANAGER::getInstance();
        $paramsManager = PrmMng::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        try {
            $this->configurationMultisiteUpdate($confTransformer);
            $this->configurationUrlsAndPaths($confTransformer);

            $dbhost    = DUPX_U::getEscapedGenericString($paramsManager->getValue(PrmMng::PARAM_DB_HOST));
            $dbname    = DUPX_U::getEscapedGenericString($paramsManager->getValue(PrmMng::PARAM_DB_NAME));
            $dbuser    = DUPX_U::getEscapedGenericString($paramsManager->getValue(PrmMng::PARAM_DB_USER));
            $dbpass    = DUPX_U::getEscapedGenericString($paramsManager->getValue(PrmMng::PARAM_DB_PASS));
            $dbcharset = $paramsManager->getValue(PrmMng::PARAM_DB_CHARSET);
            $dbcollate = $paramsManager->getValue(PrmMng::PARAM_DB_COLLATE);

            $confTransformer->update('constant', 'DB_NAME', $dbname, array('raw' => true));
            Log::info('UPDATE DB_NAME ' . Log::v2str($dbname));

            $confTransformer->update('constant', 'DB_USER', $dbuser, array('raw' => true));
            Log::info('UPDATE DB_USER ' . Log::v2str('** OBSCURED **'));

            $confTransformer->update('constant', 'DB_PASSWORD', $dbpass, array('raw' => true));
            Log::info('UPDATE DB_PASSWORD ' . Log::v2str('** OBSCURED **'));

            $confTransformer->update('constant', 'DB_HOST', $dbhost, array('raw' => true));
            Log::info('UPDATE DB_HOST ' . Log::v2str($dbhost));

            $confTransformer->update('constant', 'DB_CHARSET', $dbcharset);
            Log::info('UPDATE DB_CHARSET ' . Log::v2str($dbcharset));

            $confTransformer->update('constant', 'DB_COLLATE', $dbcollate);
            Log::info('UPDATE DB_COLLATE ' . Log::v2str($dbcollate));

            if (DUPX_InstallerState::isRestoreBackup()) {
                Log::info("\nRESTORE BACKUP MODE: SKIP OTHER WP-CONFIGS UPDATE ***");
                Log::resetIndent();
                return;
            }

            $auth_keys = array(
                'AUTH_KEY',
                'SECURE_AUTH_KEY',
                'LOGGED_IN_KEY',
                'NONCE_KEY',
                'AUTH_SALT',
                'SECURE_AUTH_SALT',
                'LOGGED_IN_SALT',
                'NONCE_SALT',
            );

            foreach ($auth_keys as $const_key) {
                $confTransformer->update('constant', $const_key, $archiveConfig->getDefineValue($const_key));
            }

            $confTransformer->update('variable', 'table_prefix', $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX));

            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'DISALLOW_FILE_EDIT', PrmMng::PARAM_WP_CONF_DISALLOW_FILE_EDIT);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'DISALLOW_FILE_MODS', PrmMng::PARAM_WP_CONF_DISALLOW_FILE_MODS);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'FORCE_SSL_ADMIN', PrmMng::PARAM_WP_CONF_FORCE_SSL_ADMIN);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'IMAGE_EDIT_OVERWRITE', PrmMng::PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_CACHE', PrmMng::PARAM_WP_CONF_WP_CACHE);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WPCACHEHOME', PrmMng::PARAM_WP_CONF_WPCACHEHOME);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'COOKIE_DOMAIN', PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'AUTOSAVE_INTERVAL', PrmMng::PARAM_WP_CONF_AUTOSAVE_INTERVAL);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_POST_REVISIONS', PrmMng::PARAM_WP_CONF_WP_POST_REVISIONS);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_DEBUG', PrmMng::PARAM_WP_CONF_WP_DEBUG);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_DEBUG_LOG', PrmMng::PARAM_WP_CONF_WP_DEBUG_LOG);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_DISABLE_FATAL_ERROR_HANDLER', PrmMng::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_DEBUG_DISPLAY', PrmMng::PARAM_WP_CONF_WP_DEBUG_DISPLAY);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'SCRIPT_DEBUG', PrmMng::PARAM_WP_CONF_SCRIPT_DEBUG);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'CONCATENATE_SCRIPTS', PrmMng::PARAM_WP_CONF_CONCATENATE_SCRIPTS);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'SAVEQUERIES', PrmMng::PARAM_WP_CONF_SAVEQUERIES);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'ALTERNATE_WP_CRON', PrmMng::PARAM_WP_CONF_ALTERNATE_WP_CRON);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'DISABLE_WP_CRON', PrmMng::PARAM_WP_CONF_DISABLE_WP_CRON);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_CRON_LOCK_TIMEOUT', PrmMng::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'EMPTY_TRASH_DAYS', PrmMng::PARAM_WP_CONF_EMPTY_TRASH_DAYS);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_MEMORY_LIMIT', PrmMng::PARAM_WP_CONF_WP_MEMORY_LIMIT);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_MAX_MEMORY_LIMIT', PrmMng::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'WP_TEMP_DIR', PrmMng::PARAM_WP_CONF_WP_TEMP_DIR);
            DUPX_ArchiveConfig::updateWpConfigByParam($confTransformer, 'AUTOMATIC_UPDATER_DISABLED', PrmMng::PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED);

            $wpConfigValue = $paramsManager->getValue(PrmMng::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE);
            switch ($wpConfigValue['value']) {
                case 'false':
                    $wpConfigValue['value'] = false;
                    break;
                case 'true':
                    $wpConfigValue['value'] = true;
                    break;
                case 'minor':
                default:
                    $wpConfigValue['value'] = 'minor';
                    break;
            }
            DUPX_ArchiveConfig::updateWpConfigByValue($confTransformer, 'WP_AUTO_UPDATE_CORE', $wpConfigValue);

            $wpConfigValue = $paramsManager->getValue(PrmMng::PARAM_WP_CONF_MYSQL_CLIENT_FLAGS);
            $constantValue = implode(' | ', SnapDB::getMysqlConnectFlagsList(true, $wpConfigValue['value']));
            DUPX_ArchiveConfig::updateWpConfigByValue($confTransformer, 'MYSQL_CLIENT_FLAGS', $wpConfigValue, $constantValue);

            Log::info("\n*** UPDATED WP CONFIG FILE ***");
        } catch (Exception $e) {
            $shortMsg = 'wp-config.php transformer:' . $e->getMessage();
            $longMsg  = <<<LONGMSG
Error updating wp-config file.<br>
The installation is finished but check the wp-config.php file and manually update the incorrect values.
LONGMSG;
            /*    $nManager->addNextStepNotice(array(
              'shortMsg' => $shortMsg,
              'level' => DUPX_NOTICE_ITEM::CRITICAL,

              ), DUPX_NOTICE_MANAGER::ADD_UNIQUE , 'wp-config-transformer-exception'); */
            $nManager->addFinalReportNotice(array(
                'shortMsg'    => $shortMsg,
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsg'     => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                'sections'    => 'general'
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'wp-config-transformer-exception');

            Log::info("WP-CONFIG TRANSFORMER EXCEPTION\n" . $e->getTraceAsString());
        }
        Log::resetIndent();
    }

    /**
     *
     * @param WPConfigTransformer $confTransformer
     */
    protected function configurationMultisiteUpdate(WPConfigTransformer $confTransformer)
    {
        $muDefines = array(
            'WP_ALLOW_MULTISITE',
            'ALLOW_MULTISITE',
            'MULTISITE',
            'DOMAIN_CURRENT_SITE',
            'PATH_CURRENT_SITE',
            'SITE_ID_CURRENT_SITE',
            'BLOG_ID_CURRENT_SITE',
            'NOBLOGREDIRECT',
            'SUBDOMAIN_INSTALL',
            'VHOST',
            'SUNRISE',
            'COOKIEPATH',
            'SITECOOKIEPATH',
            'ADMIN_COOKIE_PATH',
            'PLUGINS_COOKIE_PATH'
        );

        // Clean all mu site define
        foreach ($muDefines as $key) {
            if ($confTransformer->exists('constant', $key)) {
                $confTransformer->remove('constant', $key);
                Log::info('TRANSFORMER[no wpmu]: ' . $key . ' constant removed from WP config file');
            }
        }
    }

    /**
     *
     * @param WPConfigTransformer $confTransformer
     */
    protected function configurationUrlsAndPaths(WPConfigTransformer $confTransformer)
    {
        $paramsManager = PrmMng::getInstance();

        $urlNew  = $paramsManager->getValue(PrmMng::PARAM_URL_NEW);
        $pathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_NEW);

        $mu_newDomain     = parse_url($urlNew);
        $mu_newDomainHost = $mu_newDomain['host'];
        $mu_newUrlPath    = parse_url($urlNew, PHP_URL_PATH);

        if (empty($mu_newUrlPath) || ($mu_newUrlPath == '/')) {
            $mu_newUrlPath = '/';
        } else {
            $mu_newUrlPath = rtrim($mu_newUrlPath, '/') . '/';
        }

        if ($confTransformer->exists('constant', 'ABSPATH')) {
            if (($relativeAbsPath = SnapIO::getRelativePath($paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW), $pathNew)) === false) {
                $absPathValue = "'" . $paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW) . "'";
            } else {
                $absPathValue = "dirname(__FILE__) . '/" . $relativeAbsPath . "'";
            }
            $confTransformer->update('constant', 'ABSPATH', $absPathValue, array('raw' => true));
            Log::info('UPDATE ABSPATH ' . Log::v2str($absPathValue));
        }

        if ($confTransformer->exists('constant', 'WP_HOME')) {
            $confTransformer->update('constant', 'WP_HOME', $urlNew, array('normalize' => true, 'add' => true));
            Log::info('UPDATE WP_HOME ' . Log::v2str($urlNew));
        }

        $newSiteUrl = $paramsManager->getValue(PrmMng::PARAM_SITE_URL);
        if ($confTransformer->exists('constant', 'WP_SITEURL') || $urlNew != $newSiteUrl) {
            $confTransformer->update('constant', 'WP_SITEURL', $newSiteUrl, array('normalize' => true, 'add' => true));
            Log::info('UPDATE WP_SITEURL ' . Log::v2str($newSiteUrl));
        }

        if ($confTransformer->exists('constant', 'DOMAIN_CURRENT_SITE')) {
            $confTransformer->update('constant', 'DOMAIN_CURRENT_SITE', $mu_newDomainHost, array('normalize' => true, 'add' => true));
            Log::info('UPDATE DOMAIN_CURRENT_SITE ' . Log::v2str($mu_newDomainHost));
        }
        if ($confTransformer->exists('constant', 'PATH_CURRENT_SITE')) {
            $confTransformer->update('constant', 'PATH_CURRENT_SITE', $mu_newUrlPath, array('normalize' => true, 'add' => true));
            Log::info('UPDATE PATH_CURRENT_SITE ' . Log::v2str($mu_newUrlPath));
        }

        $pathContent = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW);
        if ($confTransformer->exists('constant', 'WP_CONTENT_DIR') || $pathNew . '/wp-content' != $pathContent) {
            $confTransformer->update('constant', 'WP_CONTENT_DIR', $pathContent, array('normalize' => true, 'add' => true));
            Log::info('UPDATE WP_CONTENT_DIR ' . Log::v2str($pathContent));
        }

        $urlContent = $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW);
        if ($confTransformer->exists('constant', 'WP_CONTENT_URL') || $urlNew . '/wp-content' != $urlContent) {
            $confTransformer->update('constant', 'WP_CONTENT_URL', $urlContent, array('normalize' => true, 'add' => true));
            Log::info('UPDATE WP_CONTENT_URL ' . Log::v2str($urlContent));
        }

        $pathPlugins = $paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW);
        if ($confTransformer->exists('constant', 'WP_PLUGIN_DIR') || $pathNew . '/wp-content/plugins' != $pathPlugins) {
            $confTransformer->update('constant', 'WP_PLUGIN_DIR', $pathPlugins, array('normalize' => true, 'add' => true));
            Log::info('UPDATE WP_PLUGIN_DIR ' . Log::v2str($pathPlugins));
        }

        $urlPlugins = $paramsManager->getValue(PrmMng::PARAM_URL_PLUGINS_NEW);
        if ($confTransformer->exists('constant', 'WP_PLUGIN_URL') || $urlNew . '/wp-content/plugins' != $urlPlugins) {
            $confTransformer->update('constant', 'WP_PLUGIN_URL', $urlPlugins, array('normalize' => true, 'add' => true));
            Log::info('UPDATE WP_PLUGIN_URL ' . Log::v2str($urlPlugins));
        }

        $pathMuPlugins = $paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW);
        if ($confTransformer->exists('constant', 'WPMU_PLUGIN_DIR') || $pathNew . '/wp-content/mu-plugins' != $pathMuPlugins) {
            $confTransformer->update('constant', 'WPMU_PLUGIN_DIR', $pathMuPlugins, array('normalize' => true, 'add' => true));
            Log::info('UPDATE WPMU_PLUGIN_DIR ' . Log::v2str($pathMuPlugins));
        }

        $urlMuPlugins = $paramsManager->getValue(PrmMng::PARAM_URL_MUPLUGINS_NEW);
        if ($confTransformer->exists('constant', 'WPMU_PLUGIN_URL') || $urlNew . '/wp-content/mu-plugins' != $urlMuPlugins) {
            $confTransformer->update('constant', 'WPMU_PLUGIN_URL', $urlMuPlugins, array('normalize' => true, 'add' => true));
            Log::info('UPDATE WPMU_PLUGIN_URL ' . Log::v2str($urlMuPlugins));
        }
    }

    protected function htaccessUpdate()
    {
        self::logSectionHeader('HTACCESS UPDATE', __FUNCTION__, __LINE__);
        // make sure dbConnection is initialized
        $this->dbConnection();

        DUPX_ServerConfig::setup($this->dbh, PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW));
    }

    protected function updateBlogName()
    {
        $paramsManager = PrmMng::getInstance();

        $escapedOptionTable = mysqli_real_escape_string($this->dbh, DUPX_DB_Functions::getOptionsTableName());
        $escapedBlogName    = htmlspecialchars($paramsManager->getValue(PrmMng::PARAM_BLOGNAME), ENT_QUOTES);
        $escapedBlogName    = mysqli_real_escape_string($this->dbh, $escapedBlogName);

        Log::info('UPATE BLOG NAME ' . Log::v2str($escapedBlogName), Log::LV_DETAILED);
        DUPX_DB::mysqli_query(
            $this->dbh,
            "UPDATE `" . $escapedOptionTable .
            "` SET option_value = '" . mysqli_real_escape_string($this->dbh, $escapedBlogName) .
            "' WHERE option_name = 'blogname' "
        );
    }

    /**
     * Update options URLs
     *
     * @param string $table
     * @param string $urlNew
     * @param string $siteUrl
     *
     * @return void
     */
    protected function updateOptionsUrls($table, $urlNew, $siteUrl)
    {
        $paramsManager      = PrmMng::getInstance();
        $escapedOptionTable = mysqli_real_escape_string($this->dbh, $table);

        Log::info('UPATE URL NEW ' . Log::v2str($urlNew), Log::LV_DETAILED);
        DUPX_DB::mysqli_query(
            $this->dbh,
            "UPDATE `" . $escapedOptionTable . "` " .
            "SET option_value = '" . mysqli_real_escape_string($this->dbh, $urlNew) . "'  WHERE option_name = 'home' "
        );
        Log::info('UPATE SITE URL ' . Log::v2str($siteUrl), Log::LV_DETAILED);
        DUPX_DB::mysqli_query(
            $this->dbh,
            "UPDATE `" . $escapedOptionTable . "` " .
            "SET option_value = '" . mysqli_real_escape_string($this->dbh, $siteUrl) . "'  WHERE option_name = 'siteurl' "
        );

        $safeModeVal = mysqli_real_escape_string($this->dbh, $paramsManager->getValue(PrmMng::PARAM_SAFE_MODE));
        DUPX_DB::mysqli_query($this->dbh, "INSERT INTO `" . $escapedOptionTable . "` (option_value, option_name) "
            . "VALUES('" . $safeModeVal . "','duplicator_pro_exe_safe_mode')"
            . "ON DUPLICATE KEY UPDATE option_value = '" . $safeModeVal . "'");
    }

    /**
     * Update post GUID
     *
     * @param string $table
     * @param string $urlNew
     *
     * @return void
     */
    protected function updatePostsGuid($table, $urlNew)
    {
        $paramsManager = PrmMng::getInstance();

        //Reset the postguid data
        if (!$paramsManager->getValue(PrmMng::PARAM_POSTGUID)) {
            return;
        }
        $escapedPostsTable = mysqli_real_escape_string($this->dbh, $table);

        Log::info('UPATE postguid');
        DUPX_DB::mysqli_query(
            $this->dbh,
            "UPDATE `" . $escapedPostsTable . "` SET guid = REPLACE(guid, '" . mysqli_real_escape_string($this->dbh, $urlNew) . "', '" . mysqli_real_escape_string(
                $this->dbh,
                $paramsManager->getValue(PrmMng::PARAM_URL_OLD)
            ) . "')"
        );
        $update_guid = @mysqli_affected_rows($this->dbh) or 0;
        Log::info("Reverted '{$update_guid}' post guid columns back to '" . $paramsManager->getValue(PrmMng::PARAM_URL_OLD) . "'");
    }

    public function generalUpdate()
    {
        self::logSectionHeader('GENERAL UPDATES', __FUNCTION__, __LINE__);
        // make sure dbConnection is initialized
        $this->dbConnection();

        $this->updateBlogName();

        $paramsManager = PrmMng::getInstance();
        $urlNew        = $paramsManager->getValue(PrmMng::PARAM_URL_NEW);
        $siteUrl       = $paramsManager->getValue(PrmMng::PARAM_SITE_URL);

        $this->updateOptionsUrls(
            DUPX_DB_Functions::getOptionsTableName(),
            $urlNew,
            $siteUrl
        );
        $this->updatePostsGuid(
            DUPX_DB_Functions::getPostsTableName(),
            $urlNew
        );

        $this->managePlugins();
    }

    /**
     *
     * @return boolean
     */
    public function duplicatorMigrationInfoSet()
    {
        Log::info('MIGRATION INFO SET');
        // make sure dbConnection is initialized
        $this->dbConnection();

        // on main options tables in all installation
        $optionTable   = mysqli_real_escape_string($this->dbh, DUPX_DB_Functions::getOptionsTableName());
        $migrationData = DUPX_InstallerState::getMigrationData();

        $query = "REPLACE INTO `" . $optionTable . "` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES " .
            "(NULL, '" . self::FIRST_LOGIN_OPTION . "', '1', 'no'), " .
            "(NULL, '" . self::MIGRATION_DATA_OPTION . "', '" . mysqli_real_escape_string($this->dbh, SnapJson::jsonEncodePPrint($migrationData)) . "', 'no');";

        if (DUPX_DB::mysqli_query($this->dbh, $query) === false) {
            $errMsg = "DATABASE ERROR \"" . mysqli_error($this->dbh) . "\"<br>[sql=" . substr($query, 0, DUPX_DBInstall::QUERY_ERROR_LOG_LEN) . "...]";
            DUPX_NOTICE_MANAGER::getInstance()->addBothNextAndFinalReportNotice(array(
                'shortMsg'    => 'UPDATE MIRATION INFO ISSUE',
                'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'     => $errMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                'sections'    => 'database'
            ));

            return false;
        } else {
            return true;
        }
    }

    public function generalCleanup()
    {
        self::logSectionHeader('GENERAL CLEANUP', __FUNCTION__, __LINE__);
        // make sure dbConnection is initialized
        $this->dbConnection();
        $paramsManager = PrmMng::getInstance();

        if (!DUPX_UpdateEngine::updateTablePrefixKeys()) {
            // @todo display erorr on notice manager
        }
    }

    /**
     * activate and deactivate plugins
     *
     * @return void
     */
    protected function managePlugins()
    {
        self::logSectionHeader("MANAGE PLUGINS", __FUNCTION__, __LINE__);
        $paramsManager = PrmMng::getInstance();

        try {
            $pluginsManager = DUPX_Plugins_Manager::getInstance();
            $pluginsManager->setActions($paramsManager->getValue(PrmMng::PARAM_PLUGINS));
            $pluginsManager->executeActions($this->dbConnection());
        } catch (Exception $e) {
            $nManager = DUPX_NOTICE_MANAGER::getInstance();
            $nManager->addFinalReportNotice(array(
                'shortMsg'    => 'Plugins settings error ' . $e->getMessage(),
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsg'     => $e->getTraceAsString(),
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                'sections'    => 'general'
            ));

            Log::info("PLUGIN MANAGER EXCEPTIOMN\n" . $e->getTraceAsString());
        }
    }

    /**
     * checks for index.html in root, and if found, issues a soft warning
     *
     * @return void
     */
    public function checkForIndexHtml()
    {
        self::logSectionHeader('CHECK FOR INDEX.HTML', __FUNCTION__, __LINE__);

        //scan for index.html
        if (file_exists(PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . '/index.html')) {
            $nManager = DUPX_NOTICE_MANAGER::getInstance();
            $nManager->addFinalReportNotice(
                array(
                    'shortMsg'    => 'An index.html was found.',
                    'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                    'longMsg'     => 'An index.html was found in the existing site. You may need to manually remove it for the new site to work.',
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                    'sections'    => 'general'
                )
            );

            Log::info("AN INDEX.HTML WAS FOUND IN THE ROOT OF THE INSTALLATION.");
        } else {
            Log::info("NO INDEX.HTML WAS FOUND");
        }
    }

    public function noticeTest()
    {
        self::logSectionHeader('NOTICES TEST', __FUNCTION__, __LINE__);
        // make sure dbConnection is initialized
        $this->dbConnection();
        $optonsTable = mysqli_real_escape_string($this->dbh, DUPX_DB_Functions::getOptionsTableName());

        $nManager = DUPX_NOTICE_MANAGER::getInstance();

        //Database
        $result = DUPX_DB::mysqli_query(
            $this->dbh,
            "SELECT option_value FROM `" . $optonsTable . "` WHERE option_name IN ('upload_url_path','uploadPath')"
        );
        if ($result) {
            while ($row = mysqli_fetch_row($result)) {
                if (strlen($row[0])) {
                    $msg  = "MEDIA SETTINGS NOTICE: The table '" . $optonsTable . "' has at least one the following values ['upload_url_path','uploadPath'] \n";
                    $msg .= "set please validate settings. These settings can be changed in the wp-admin by going to /wp-admin/options.php'";

                    $this->report['warnlist'][] = $msg;
                    Log::info($msg);

                    $nManager->addFinalReportNotice(array(
                        'shortMsg'    => 'Media settings notice',
                        'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                        'longMsg'     => $msg,
                        'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                        'sections'    => 'general'
                    ), DUPX_NOTICE_MANAGER::ADD_UNIQUE_UPDATE, 'media-settings-notice');

                    break;
                }
            }
        }

        if (empty($this->report['warnlist'])) {
            Log::info("No General Notices Found\n");
        }
    }

    public function cleanupTmpFiles()
    {
        self::logSectionHeader('CLEANUP TMP FILES', __FUNCTION__, __LINE__);

        //Cleanup any tmp files a developer may have forgotten about
        //Lets be proactive for the developer just in case
        $pathNew             = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW);
        $wpconfig_path_bak   = $pathNew . "/wp-config.bak";
        $wpconfig_path_old   = $pathNew . "/wp-config.old";
        $wpconfig_path_org   = $pathNew . "/wp-config.org";
        $wpconfig_path_orig  = $pathNew . "/wp-config.orig";
        $wpconfig_safe_check = array($wpconfig_path_bak, $wpconfig_path_old, $wpconfig_path_org, $wpconfig_path_orig);
        foreach ($wpconfig_safe_check as $file) {
            if (file_exists($file)) {
                $tmp_newfile = $file . uniqid('_');
                if (rename($file, $tmp_newfile) === false) {
                    Log::info("WARNING: Unable to rename '{$file}' to '{$tmp_newfile}'");
                }
            }
        }
    }

    public function setFilePermsission()
    {
        self::logSectionHeader('SET PARAMS PERMISSION', __FUNCTION__, __LINE__);
        DUP_Extraction::setFolderPermissionAfterExtraction();
    }

    public function finalReportNotices()
    {
        self::logSectionHeader('FINAL REPORT NOTICES', __FUNCTION__, __LINE__);

        $this->wpConfigFinalReport();
        $this->htaccessFinalReport();
    }

    private function htaccessFinalReport()
    {
        $nManager = DUPX_NOTICE_MANAGER::getInstance();

        $origHtaccessPath = DUPX_Orig_File_Manager::getInstance()->getEntryStoredPath(DUPX_ServerConfig::CONFIG_ORIG_FILE_HTACCESS_ID);
        if ($origHtaccessPath === false || ($orig             = file_get_contents($origHtaccessPath)) === false) {
            $orig = 'Original .htaccess file doesn\'t exist';
        }

        $targetHtaccessPath = DUPX_ServerConfig::getHtaccessTargetPath();
        if (!file_exists($targetHtaccessPath) || ($new                = file_get_contents($targetHtaccessPath)) === false) {
            $new = 'New .htaccess file doesn\'t exist';
        }

        $lightBoxContent = '<div class="row-cols-2">' .
            '<div class="col col-1"><b>Original .htaccess</b><pre>' . htmlspecialchars($orig) . '</pre></div>' .
            '<div class="col col-2"><b>New .htaccess</b><pre>' . htmlspecialchars($new) . '</pre></div>' .
            '</div>';
        $longMsg         = DUPX_U_Html::getLigthBox('.htaccess changes', 'HTACCESS COMPARE', $lightBoxContent, false);

        $nManager->addFinalReportNotice(array(
            'shortMsg'    => 'htaccess changes',
            'level'       => DUPX_NOTICE_ITEM::INFO,
            'longMsg'     => $longMsg,
            'sections'    => 'changes',
            'open'        => true,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
        ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'htaccess-changes');
    }

    private function wpConfigFinalReport()
    {
        $nManager     = DUPX_NOTICE_MANAGER::getInstance();
        $wpConfigPath = DUPX_Orig_File_Manager::getInstance()->getEntryStoredPath(DUPX_ServerConfig::CONFIG_ORIG_FILE_WPCONFIG_ID);

        if ($wpConfigPath === false || ($orig = file_get_contents($wpConfigPath)) === false) {
            $orig = 'Can\'t read origin wp-config.php file';
        } else {
            $orig = $this->obscureWpConfig($orig);
        }

        $wpConfigFile = DUPX_WPConfig::getWpConfigPath();
        if (!is_readable($wpConfigFile)) {
            $new = 'Can read wp-config.php file';
        } elseif (($new = file_get_contents($wpConfigFile)) === false) {
            $new = 'Can read wp-config.php file';
        } else {
            $new = $this->obscureWpConfig($new);
        }

        $lightBoxContent = '<div class="row-cols-2">' .
            '<div class="col col-1"><b>Original wp-config.php</b><pre>' . htmlspecialchars($orig) . '</pre></div>' .
            '<div class="col col-2"><b>New wp-config.php</b><pre>' . htmlspecialchars($new) . '</pre></div>' .
            '</div>';
        $longMsg         = DUPX_U_Html::getLigthBox('wp-config.php changes', 'WP-CONFIG.PHP COMPARE', $lightBoxContent, false);

        $nManager->addFinalReportNotice(array(
            'shortMsg'    => 'wp-config.php changes',
            'level'       => DUPX_NOTICE_ITEM::INFO,
            'longMsg'     => $longMsg,
            'sections'    => 'changes',
            'open'        => true,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML
        ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'wp-config-changes');
    }

    private function obscureWpConfig($src)
    {
        $transformer = new WPConfigTransformerSrc($src);
        $obsKeys     = array(
            'DB_NAME',
            'DB_USER',
            'DB_HOST',
            'DB_PASSWORD',
            'AUTH_KEY',
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'AUTH_SALT',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT'
        );

        foreach ($obsKeys as $key) {
            if ($transformer->exists('constant', $key)) {
                $transformer->update('constant', $key, '**OBSCURED**');
            }
        }

        return $transformer->getSrc();
    }

    public function chunkStop($progressPerc, $position)
    {
        $this->closeDbConnection();

        $ajax3_sum = DUPX_U::elapsedTime(DUPX_U::getMicrotime(), $this->timeStart);
        Log::info("\nSTEP-3 CHUNK STOP @ " . @date('h:i:s') . " - RUNTIME: {$ajax3_sum} \n\n");

        $this->report['chunk']         = 1;
        $this->report['chunkPos']      = $position;
        $this->report['pass']          = 0;
        $this->report['progress_perc'] = $progressPerc;
    }

    public function complete()
    {
        $this->closeDbConnection();

        $paramsManager = PrmMng::getInstance();

        $ajax3_sum = DUPX_U::elapsedTime(DUPX_U::getMicrotime(), $this->timeStart);
        Log::info("\nSTEP-3 COMPLETE @ " . @date('h:i:s') . " - RUNTIME: {$ajax3_sum} \n\n");

        $finalReport = $paramsManager->getValue(PrmMng::PARAM_FINAL_REPORT_DATA);

        $finalReport['replace']['scan_tables'] = $this->report['scan_tables'];
        $finalReport['replace']['scan_rows']   = $this->report['scan_rows'];
        $finalReport['replace']['scan_cells']  = $this->report['scan_cells'];
        $finalReport['replace']['updt_tables'] = $this->report['updt_tables'];
        $finalReport['replace']['updt_rows']   = $this->report['updt_rows'];
        $finalReport['replace']['updt_cells']  = $this->report['updt_cells'];
        $finalReport['replace']['errsql']      = $this->report['errsql'];
        $finalReport['replace']['errser']      = $this->report['errser'];
        $finalReport['replace']['errkey']      = $this->report['errkey'];
        $finalReport['replace']['errsql_sum']  = $this->report['errsql_sum'];
        $finalReport['replace']['errser_sum']  = $this->report['errser_sum'];
        $finalReport['replace']['errkey_sum']  = $this->report['errkey_sum'];
        $finalReport['replace']['err_all']     = $this->report['err_all'];
        $finalReport['replace']['warn_all']    = $this->report['warn_all'];
        $finalReport['replace']['warnlist']    = $this->report['warnlist'];

        $paramsManager->setValue(PrmMng::PARAM_FINAL_REPORT_DATA, $finalReport);
        $paramsManager->save();

        $this->report['pass']          = 1;
        $this->report['chunk']         = 0;
        $this->report['chunkPos']      = null;
        $this->report['progress_perc'] = 100;
        // error_reporting($ajax3_error_level);
    }
}
