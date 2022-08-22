<?php

/**
 * Installer Params Manager
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Installer\Core\Params;

use Duplicator\Installer\Core\Bootstrap;
use Duplicator\Installer\Core\Hooks\HooksMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Installer\Core\Params\Descriptors\ParamsDescriptors;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapString;

class PrmMng
{
    const ENV_PARAMS_KEY = 'DUPLICATOR_PARAMS';

    /**
     * overwrite file content example
     * <?php
     * $json = <<<JSON
     * {
     * "debug": {
     * "value": false
     * },
     * "debug_params": {
     * "value": true
     * },
     * "logging": {
     * "value": 2
     * }
     * }
     * JSON;
     * // OVERWRITE FILE END
     */
    const LOCAL_OVERWRITE_PARAMS           = 'dup_params_owr';
    const LOCAL_OVERWRITE_PARAMS_EXTENSION = '.php';
    // actionsLOCAL_OVERWRITE_PARAMS
    const PARAM_INSTALLER_MODE             = 'inst_mode';
    const PARAM_OVERWRITE_SITE_DATA        = 'ovr_site_data';
    const PARAM_CTRL_ACTION                = 'ctrl_action';
    const PARAM_ROUTER_ACTION              = 'router-action';
    const PARAM_SECURE_PASS                = 'secure-pass';
    const PARAM_SECURE_ARCHIVE_HASH        = 'secure-archive';
    const PARAM_SECURE_OK                  = 'secure-ok';
    const PARAM_STEP_ACTION                = 'step-action';
    const PARAM_TEMPLATE                   = 'template';
    const PARAM_VALIDATION_LEVEL           = 'valid-level';
    const PARAM_VALIDATION_ACTION_ON_START = 'valid-act';
    const PARAM_VALIDATION_SHOW_ALL        = 'valid-show-all';
    const PARAM_ACCEPT_TERM_COND           = 'accept-warnings';
    const PARAM_RECOVERY_LINK              = 'recovery-link';
    const PARAM_FROM_SITE_IMPORT_INFO      = 'import-info';
    // input params
    const PARAM_DEBUG                            = 'debug';
    const PARAM_DEBUG_PARAMS                     = 'debug_params';
    const PARAM_ARCHIVE_ENGINE                   = 'archive_engine';
    const PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES     = 'archive_engine_wpskip';
    const PARAM_ARCHIVE_ACTION                   = 'archive_action';
    const PARAM_LOGGING                          = 'logging';
    const PARAM_ZIP_THROTTLING                   = 'zip_throttling';
    const PARAM_REMOVE_RENDUNDANT                = 'remove-redundant';
    const PARAM_REMOVE_USERS_WITHOUT_PERMISSIONS = 'remove-users-without-permissions';
    const PARAM_FILE_TIME                        = 'zip_filetime';
    const PARAM_HTACCESS_CONFIG                  = 'ht_config';
    const PARAM_OTHER_CONFIG                     = 'other_config';
    const PARAM_WP_CONFIG                        = 'wp_config';
    const PARAM_SAFE_MODE                        = 'exe_safe_mode';
    const PARAM_SET_FILE_PERMS                   = 'set_file_perms';
    const PARAM_FILE_PERMS_VALUE                 = 'file_perms_value';
    const PARAM_SET_DIR_PERMS                    = 'set_dir_perms';
    const PARAM_DIR_PERMS_VALUE                  = 'dir_perms_value';
    const PARAM_INST_TYPE                        = 'install-type';
    const PARAM_DB_DISPLAY_OVERWIRE_WARNING      = 'db-display-overwarn';
    const PARAM_DB_ENGINE                        = 'db-engine';
    const PARAM_DB_VIEW_MODE                     = 'view_mode';
    const PARAM_DB_ACTION                        = 'dbaction';
    const PARAM_DB_HOST                          = 'dbhost';
    const PARAM_DB_NAME                          = 'dbname';
    const PARAM_DB_USER                          = 'dbuser';
    const PARAM_DB_FLAG                          = 'dbflag';
    const PARAM_DB_TABLE_PREFIX                  = 't_prefix';
    const PARAM_DB_PASS                          = 'dbpass';
    const PARAM_DB_CHARSET                       = 'dbcharset';
    const PARAM_DB_COLLATE                       = 'dbcollate';
    const PARAM_DB_CHUNK                         = 'dbchunk';
    const PARAM_DB_VIEW_CREATION                 = 'dbobj_views';
    const PARAM_DB_PROC_CREATION                 = 'dbobj_procs';
    const PARAM_DB_FUNC_CREATION                 = 'dbobj_funcs';
    const PARAM_DB_REMOVE_DEFINER                = 'db_remove_definer';
    const PARAM_DB_SPLIT_CREATES                 = 'dbsplit_creates';
    const PARAM_DB_MYSQL_MODE                    = 'dbmysqlmode';
    const PARAM_DB_MYSQL_MODE_OPTS               = 'dbmysqlmode_opts';
    const PARAM_DB_DONE_CREATES                  = 'db_done_creates';
    const PARAM_URL_OLD                          = 'url_old';
    const PARAM_URL_NEW                          = 'url_new';
    const PARAM_SITE_URL_OLD                     = 'siteurl_old';
    const PARAM_SITE_URL                         = 'siteurl';
    const PARAM_PATH_WP_CORE_OLD                 = 'path_core_old';
    const PARAM_PATH_WP_CORE_NEW                 = 'path_core_new';
    const PARAM_PATH_OLD                         = 'path_old';
    const PARAM_PATH_NEW                         = 'path_new';
    const PARAM_PATH_CONTENT_OLD                 = 'path_cont_old';
    const PARAM_PATH_CONTENT_NEW                 = 'path_cont_new';
    const PARAM_URL_CONTENT_OLD                  = 'url_cont_old';
    const PARAM_URL_CONTENT_NEW                  = 'url_cont_new';
    const PARAM_PATH_UPLOADS_OLD                 = 'path_upl_old';
    const PARAM_PATH_UPLOADS_NEW                 = 'path_upl_new';
    const PARAM_URL_UPLOADS_OLD                  = 'url_upl_old';
    const PARAM_URL_UPLOADS_NEW                  = 'url_upl_new';
    const PARAM_PATH_PLUGINS_OLD                 = 'path_plug_old';
    const PARAM_PATH_PLUGINS_NEW                 = 'path_plug_new';
    const PARAM_URL_PLUGINS_OLD                  = 'url_plug_old';
    const PARAM_URL_PLUGINS_NEW                  = 'url_plug_new';
    const PARAM_PATH_MUPLUGINS_OLD               = 'path_muplug_old';
    const PARAM_PATH_MUPLUGINS_NEW               = 'path_muplug_new';
    const PARAM_URL_MUPLUGINS_OLD                = 'url_muplug_old';
    const PARAM_URL_MUPLUGINS_NEW                = 'url_muplug_new';
    const PARAM_WP_ADDON_SITES_PATHS             = 'wpaddon_sites';
    const PARAM_BLOGNAME                         = 'blogname';
    const PARAM_REPLACE_ENGINE                   = 'mode_chunking';
    const PARAM_SKIP_PATH_REPLACE                = 'skip_path_replace';
    const PARAM_DB_TABLES                        = 'tables';
    const PARAM_EMAIL_REPLACE                    = 'search_replace_email_domain';
    const PARAM_FULL_SEARCH                      = 'fullsearch';
    const PARAM_POSTGUID                         = 'postguid';
    const PARAM_MAX_SERIALIZE_CHECK              = 'mstrlim';
    const PARAM_PLUGINS                          = 'plugins';
    const PARAM_IGNORE_PLUGINS                   = 'ignore_plugins';
    const PARAM_FORCE_DIABLE_PLUGINS             = 'fd_plugins';
    const PARAM_USERS_MODE                       = 'users_mode';
    const PARAM_USERS_PWD_RESET                  = 'users_pwd_reset';
    const PARAM_WP_ADMIN_CREATE_NEW              = 'wp_new_admin';
    const PARAM_WP_ADMIN_NAME                    = 'wp_username';
    const PARAM_WP_ADMIN_PASSWORD                = 'wp_password';
    const PARAM_WP_ADMIN_MAIL                    = 'wp_mail';
    const PARAM_WP_ADMIN_NICKNAME                = 'wp_nickname';
    const PARAM_WP_ADMIN_FIRST_NAME              = 'wp_first_name';
    const PARAM_WP_ADMIN_LAST_NAME               = 'wp_last_name';
    // WP CONFIG
    const PARAM_GEN_WP_AUTH_KEY                        = 'auth_keys_and_salts';
    const PARAM_WP_CONF_WP_SITEURL                     = 'wpc_WP_SITEURL';
    const PARAM_WP_CONF_MYSQL_CLIENT_FLAGS             = 'wpc_MYSQL_CLIENT_FLAGS';
    const PARAM_WP_CONF_WP_HOME                        = 'wpc_WP_HOME';
    const PARAM_WP_CONF_WP_CONTENT_DIR                 = 'wpc_WP_CONTENT_DIR';
    const PARAM_WP_CONF_WP_CONTENT_URL                 = 'wpc_WP_CONTENT_URL';
    const PARAM_WP_CONF_WP_PLUGIN_DIR                  = 'wpc_WP_PLUGIN_DIR';
    const PARAM_WP_CONF_WP_PLUGIN_URL                  = 'wpc_WP_PLUGIN_URL';
    const PARAM_WP_CONF_PLUGINDIR                      = 'wpc_PLUGINDIR';
    const PARAM_WP_CONF_UPLOADS                        = 'wpc_UPLOADS';
    const PARAM_WP_CONF_AUTOSAVE_INTERVAL              = 'wpc_AUTOSAVE_INTERVAL';
    const PARAM_WP_CONF_WP_POST_REVISIONS              = 'wpc_WP_POST_REVISIONS';
    const PARAM_WP_CONF_COOKIE_DOMAIN                  = 'wpc_COOKIE_DOMAIN';
    const PARAM_WP_CONF_WP_ALLOW_MULTISITE             = 'wpc_WP_ALLOW_MULTISITE';
    const PARAM_WP_CONF_NOBLOGREDIRECT                 = 'wpc_NOBLOGREDIRECT';
    const PARAM_WP_CONF_WP_DEBUG                       = 'wpc_WP_DEBUG';
    const PARAM_WP_CONF_SCRIPT_DEBUG                   = 'wpc_SCRIPT_DEBUG';
    const PARAM_WP_CONF_CONCATENATE_SCRIPTS            = 'wpc_CONCATENATE_SCRIPTS';
    const PARAM_WP_CONF_WP_DEBUG_LOG                   = 'wpc_WP_DEBUG_LOG';
    const PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER = 'wpc_WP_DISABLE_FATAL_ERROR_HANDLER';
    const PARAM_WP_CONF_WP_DEBUG_DISPLAY               = 'wpc_WP_DEBUG_DISPLAY';
    const PARAM_WP_CONF_WP_MEMORY_LIMIT                = 'wpc_WP_MEMORY_LIMIT';
    const PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT            = 'wpc_WP_MAX_MEMORY_LIMIT';
    const PARAM_WP_CONF_WP_TEMP_DIR                    = 'wpc_WP_TEMP_DIR';
    const PARAM_WP_CONF_WP_CACHE                       = 'wpc_WP_CACHE';
    const PARAM_WP_CONF_CUSTOM_USER_TABLE              = 'wpc_CUSTOM_USER_TABLE';
    const PARAM_WP_CONF_CUSTOM_USER_META_TABLE         = 'wpc_CUSTOM_USER_META_TABLE';
    const PARAM_WP_CONF_WPLANG                         = 'wpc_WPLANG';
    const PARAM_WP_CONF_WP_LANG_DIR                    = 'wpc_WP_LANG_DIR';
    const PARAM_WP_CONF_SAVEQUERIES                    = 'wpc_SAVEQUERIES';
    const PARAM_WP_CONF_FS_CHMOD_DIR                   = 'wpc_FS_CHMOD_DIR';
    const PARAM_WP_CONF_FS_CHMOD_FILE                  = 'wpc_FS_CHMOD_FILE';
    const PARAM_WP_CONF_FS_METHOD                      = 'wpc_FS_METHOD';
    const PARAM_WP_CONF_ALTERNATE_WP_CRON              = 'wpc_ALTERNATE_WP_CRON';
    const PARAM_WP_CONF_DISABLE_WP_CRON                = 'wpc_DISABLE_WP_CRON';
    const PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT           = 'wpc_WP_CRON_LOCK_TIMEOUT';
    const PARAM_WP_CONF_COOKIEPATH                     = 'wpc_COOKIEPATH';
    const PARAM_WP_CONF_SITECOOKIEPATH                 = 'wpc_SITECOOKIEPATH';
    const PARAM_WP_CONF_ADMIN_COOKIE_PATH              = 'wpc_ADMIN_COOKIE_PATH';
    const PARAM_WP_CONF_PLUGINS_COOKIE_PATH            = 'wpc_PLUGINS_COOKIE_PATH';
    const PARAM_WP_CONF_TEMPLATEPATH                   = 'wpc_TEMPLATEPATH';
    const PARAM_WP_CONF_STYLESHEETPATH                 = 'wpc_STYLESHEETPATH';
    const PARAM_WP_CONF_EMPTY_TRASH_DAYS               = 'wpc_EMPTY_TRASH_DAYS';
    const PARAM_WP_CONF_WP_ALLOW_REPAIR                = 'wpc_WP_ALLOW_REPAIR';
    const PARAM_WP_CONF_DO_NOT_UPGRADE_GLOBAL_TABLES   = 'wpc_DO_NOT_UPGRADE_GLOBAL_TABLES';
    const PARAM_WP_CONF_DISALLOW_FILE_EDIT             = 'wpc_DISALLOW_FILE_EDIT';
    const PARAM_WP_CONF_DISALLOW_FILE_MODS             = 'wpc_DISALLOW_FILE_MODS';
    const PARAM_WP_CONF_FORCE_SSL_ADMIN                = 'wpc_FORCE_SSL_ADMIN';
    const PARAM_WP_CONF_WP_HTTP_BLOCK_EXTERNAL         = 'wpc_WP_HTTP_BLOCK_EXTERNAL';
    const PARAM_WP_CONF_WP_ACCESSIBLE_HOSTS            = 'wpc_WP_ACCESSIBLE_HOSTS';
    const PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED     = 'wpc_AUTOMATIC_UPDATER_DISABLED';
    const PARAM_WP_CONF_WP_AUTO_UPDATE_CORE            = 'wpc_WP_AUTO_UPDATE_CORE';
    const PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE           = 'wpc_IMAGE_EDIT_OVERWRITE';
    const PARAM_WP_CONF_WPMU_PLUGIN_DIR                = 'wpc_WPMU_PLUGIN_DIR';
    const PARAM_WP_CONF_WPMU_PLUGIN_URL                = 'wpc_WPMU_PLUGIN_URL';
    const PARAM_WP_CONF_MUPLUGINDIR                    = 'wpc_MUPLUGINDIR';
    // OTHER WP CONFIG SETTINGS NOT IN WP CORE
    const PARAM_WP_CONF_WPCACHEHOME        = 'wpc_WPCACHEHOME';
    const PARAM_FINAL_REPORT_DATA          = 'final_report';
    const PARAM_AUTO_CLEAN_INSTALLER_FILES = 'auto-delete';

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @var bool
     */
    private static $initialized = false;

    /**
     *
     * @var ParamForm[]
     */
    private $params = array();

    /**
     *
     * @var array
     */
    private $paramsHtmlInfo = array();

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
     * __construct
     *
     * @return void
     */
    private function __construct()
    {
        ParamsDescriptors::init();
    }

    /**
     *
     * @return boolean
     * @throws \Exception
     */
    public function initParams()
    {
        if (self::$initialized) {
            // prevent multiple inizialization
            return true;
        }
        self::$initialized = true;

        ParamsDescriptors::initParams($this->params);
        $this->params = HooksMng::getInstance()->applyFilters('installer_get_init_params', $this->params);

        $this->paramsHtmlInfo[] = '***** INIT PARAMS WITH STD VALUlES';

        return true;
    }

    /**
     * get value of param key.
     * thorw execption if key don't exists
     *
     * @param string $key param key
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function getValue($key)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }
        return $this->params[$key]->getValue();
    }

    /**
     * Get param status
     *
     * @param string $key param key
     *
     * @return string // STATUS_INIT | STATUS_OVERWRITE | STATUS_UPD_FROM_INPUT
     *
     * @throws \Exception
     */
    public function getInitStatus($key)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'does not exists');
        }
        return $this->params[$key]->getStatus();
    }

    /**
     * get the label of param key.
     * thorw execption if key don't exists
     *
     * @param string $key param key
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getLabel($key)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }
        return rtrim($this->params[$key]->getLabel(), ": \n\t");
    }

    /**
     * Set param value
     *
     * @param string $key   param key
     * @param mixed  $value value
     *
     * @return boolean // return false if params isn't valid
     *
     * @throws \Exception // if key don't exists
     */
    public function setValue($key, $value)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }
        return $this->params[$key]->setValue($value);
    }

    /**
     * this cungion set value get from input method.
     *
     * @param string  $key                  param key
     * @param string  $method               input method (GET, POST ... )
     * @param boolean $thowException        if true throw exception if  value isn't valid.
     * @param boolean $nextStepErrorMessage if true and param isn't valid add next step message
     *
     * @return boolean
     *
     */
    public function setValueFromInput($key, $method = ParamForm::INPUT_POST, $thowException = true, $nextStepErrorMessage = false)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (($result = $this->params[$key]->setValueFromInput($method)) === false) {
            $this->paramsHtmlInfo[] = 'INVALID VALUE INPUT <b>' . $key . '</b>';

            if ($nextStepErrorMessage) {
                $this->addParamValidationFaliedNotice($key);
            }
            if ($thowException) {
                $errorMessage = 'Parameter "' . $this->getLabel($key) . '" have invalid value';
                throw new \Exception('PARAM ERROR: ' . $errorMessage);
            }
        } else {
            $this->paramsHtmlInfo[] = 'SET FROM INPUT <b>' . $key . '</b> VALUE: ' . Log::v2str($this->params[$key]->getValue());
        }
        return $result;
    }

    /**
     * Add next step validation failed notice
     *
     * @param string $key param key
     *
     * @return void
     */
    public function addParamValidationFaliedNotice($key)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        $longMessage = '<b>' . $this->getLabel($key) . '</b> ' . $this->params[$key]->getInvalidMessage() . "<br>\n";
        \DUPX_NOTICE_MANAGER::getInstance()->addNextStepNotice(array(
            'shortMsg'    => 'Parameter validation failed',
            'level'       => \DUPX_NOTICE_ITEM::CRITICAL,
            'longMsg'     => $longMessage,
            'longMsgMode' => \DUPX_NOTICE_ITEM::MSG_MODE_HTML
        ), \DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, 'params_validation_fail');
    }

    /**
     * Return the form param wrapper id
     *
     * @param string $key param key
     *
     * @return boolean|string return false if the item key isn't a instance of ParamForm
     */
    public function getFormWrapperId($key)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (method_exists($this->params[$key], 'getFormWrapperId')) {
            return $this->params[$key]->getFormWrapperId();
        } else {
            return false;
        }
    }

    /**
     * Return form item id
     *
     * @param string $key param key
     *
     * @return boolean|string return false if the item key isn't a instance of ParamForm
     */
    public function getFormItemId($key)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (method_exists($this->params[$key], 'getFormItemId')) {
            return $this->params[$key]->getFormItemId();
        } else {
            return false;
        }
    }

    /**
     * Get param form status
     *
     * @param string $key param key
     *
     * @return boolean|string return false if the item key isn't a instance of ParamForm
     */
    public function getFormStatus($key)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (method_exists($this->params[$key], 'getFormStatus')) {
            return $this->params[$key]->getFormStatus();
        } else {
            return false;
        }
    }

    /**
     * Set form param status
     *
     * @param string          $key    param key
     * @param string|callable $status STATUS_ENABLED , STATUS_READONLY or callable function
     *
     * @return boolean|string return false if the item key isn't a instance of ParamForm
     */
    public function setFormStatus($key, $status)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (method_exists($this->params[$key], 'setFormAttr')) {
            return $this->params[$key]->setFormAttr('status', $status);
        } else {
            return false;
        }
    }

    /**
     * Set form wrapper class
     *
     * @param string $key   param key
     * @param string $class wrapper class
     *
     * @return boolean|string return false if the item key isn't a instance of ParamForm
     */
    public function addFormWrapperClass($key, $class)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (method_exists($this->params[$key], 'addWrapperClass')) {
            return $this->params[$key]->addWrapperClass($class);
        } else {
            return false;
        }
    }

    /**
     * Remove form wrapper class
     *
     * @param string $key   param key
     * @param string $class wrapper class
     *
     * @return boolean|string return false if the item key isn't a instance of ParamForm
     */
    public function removeFormWrapperClass($key, $class)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (method_exists($this->params[$key], 'removeWrapperClass')) {
            return $this->params[$key]->removeWrapperClass($class);
        } else {
            return false;
        }
    }

    /**
     * This tunction add o remove note on the param form
     *
     * @param string $key        param key
     * @param string $htmlString true if is html
     *
     * @return boolean
     */
    public function setFormNote($key, $htmlString)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (method_exists($this->params[$key], 'setFormAttr')) {
            $this->params[$key]->setFormAttr('subNote', $htmlString);
            return true;
        } else {
            return false;
        }
    }

    /**
     * return true if the input exists in html form
     * false if isn't ParamForm object or status is STATUS_INFO_ONLY or STATUS_SKIP
     *
     * @param string $key param key
     *
     * @return boolean
     */
    public function isHtmlInput($key)
    {
        $status = $this->getFormStatus($key);
        switch ($status) {
            case ParamForm::STATUS_ENABLED:
            case ParamForm::STATUS_READONLY:
            case ParamForm::STATUS_DISABLED:
                return true;
            case ParamForm::STATUS_INFO_ONLY:
            case ParamForm::STATUS_SKIP:
            default:
                return false;
        }
    }

    /**
     * get the input form html
     *
     * @param string $key            the param identifier
     * @param mixed  $overwriteValue if not null set overwriteValue begore get html
     *                               (IMPORTANT: the stored param value don't change. To change it use setValue.)
     * @param bool   $echo           true echo else return string
     *
     * @return bool|string return false if the item kay isn't a instance of ParamForm
     */
    public function getHtmlFormParam($key, $overwriteValue = null, $echo = true)
    {
        if (!isset($this->params[$key])) {
            throw new \Exception('Param key ' . Log::v2str($key) . 'don\' exists');
        }

        if (!($this->params[$key] instanceof ParamForm)) {
            return false;
        }

        if (is_null($overwriteValue)) {
            return $this->params[$key]->getHtml($echo);
        } else {
            $tmpParam = clone $this->params[$key];
            if ($tmpParam->setValue($overwriteValue) === false) {
                throw new \Exception('Can\'t set overwriteValue ' . Log::v2str($overwriteValue) . ' in param:' . $tmpParam->getName());
            }

            return $tmpParam->getHtml($echo);
        }
    }

    /**
     * Load params from persistance files
     *
     * @param boolean $reset if true reset params
     *
     * @return boolean
     */
    public function load($reset = false)
    {
        if ($reset) {
            $this->resetParams();
            $this->initParamsOverwrite();
            return true;
        } else {
            if (!file_exists(self::getPersistanceFilePath())) {
                return false;
            }
            $this->paramsHtmlInfo[] = '***** LOAD PARAMS FROM PERSISTENCE FILE';

            if (($json = file_get_contents(self::getPersistanceFilePath())) === false) {
                throw new \Exception('Can\'t read param persistence file ' . Log::v2str(self::getPersistanceFilePath()));
            }

            $arrayData = json_decode($json, true);
            if ($this->setParamsValues($arrayData) === false) {
                throw new \Exception('Can\'t set params from persistence file ' . Log::v2str(self::getPersistanceFilePath()));
            }
            return true;
        }
    }

    /**
     * Remove persistance file and all params and reinit all
     *
     * @return boolean
     */
    protected function resetParams()
    {
        $this->paramsHtmlInfo[] = '***** RESET PARAMS';
        SnapIO::rm(self::getPersistanceFilePath());
        $this->params      = array();
        self::$initialized = false;
        return $this->initParams();
    }

    /**
     * Ovrewrite params from sources
     *
     * @return boolean
     */
    public function initParamsOverwrite()
    {
        Log::info('OVERWRITE PARAMS');
        $this->paramsHtmlInfo[] = '***** LOAD OVERWRITE INFO';
        /**
         * @todo temp disabled require major study
         * if (isset($_ENV[self::ENV_PARAMS_KEY])) {
         *  $this->paramsHtmlInfo[] = 'LOAD FROM ENV VARS';
          $arrayData = json_decode($_ENV[self::ENV_PARAMS_KEY]);
          $this->setParamsValues($arrayData, true);
          } */
        // LOAD PARAMS FROM PACKAGE OVERWRITE
        $arrayData = (array) \DUPX_ArchiveConfig::getInstance()->overwriteInstallerParams;
        if (!empty($arrayData)) {
            $this->paramsHtmlInfo[] = '***** LOAD FROM PACKAGE OVERWRITE';
            Log::info(' *** FROM PACKAGE');
            if ($this->setParamsValues($arrayData, true, Log::LV_DEFAULT) === false) {
                throw new \Exception('Can\'t set params from package overwrite ');
            }
        }

        // LOAD PARAMS FROM LOCAL OVERWRITE
        $localOverwritePath = DUPX_ROOT . '/' . self::LOCAL_OVERWRITE_PARAMS . self::LOCAL_OVERWRITE_PARAMS_EXTENSION;
        if (is_readable($localOverwritePath)) {
            // json file is set in $localOverwritePath php file
            $json = null;
            include($localOverwritePath);
            if (empty($json)) {
                Log::info('LOCAL OVERWRITE PARAMS FILE ISN\'T WELL FORMED');
            } else {
                $arrayData = json_decode($json, true);
                if (!empty($arrayData)) {
                    $this->paramsHtmlInfo[] = '***** LOAD FROM LOCAL OVERWRITE';
                    Log::info(' *** FROM LOCAL FILE');
                    if ($this->setParamsValues($arrayData, true, Log::LV_DEFAULT) === false) {
                        throw new \Exception('Can\'t set params from local overwrite ');
                    }
                }
            }
        }

        // LOAD PARAMS FROM LOCAL OVERWRITE PACKAGE_HASH
        $localOverwritePath = DUPX_ROOT . '/' . self::LOCAL_OVERWRITE_PARAMS . '_' . Bootstrap::getPackageHash() . '.json';
        if (is_readable($localOverwritePath)) {
            if (($json = file_get_contents($localOverwritePath)) === false) {
                Log::info('CAN\'T READ LOCAL OVERWRITE PARAM HASH FILE');
            } else {
                $arrayData = json_decode($json, true);
                if (!empty($arrayData)) {
                    $this->paramsHtmlInfo[] = '***** LOAD FROM LOCAL OVERWRITE HASH';
                    Log::info(' *** FROM LOCAL FILE');
                    if ($this->setParamsValues($arrayData, true, Log::LV_DEFAULT) === false) {
                        throw new \Exception('Can\'t set params from local overwrite ');
                    }
                }
            }
        }

        HooksMng::getInstance()->doAction('after_params_overwrite', $this->params);

        Log::info("********************************************************************************");
        return true;
    }

    /**
     * Update params values from arrayData
     *
     * @param array   $arrayData      params data
     * @param boolean $overwrite      if true overwrite status
     * @param integer $logginLevelSet log level
     *
     * @return bool returns false if a parameter has not been set
     */
    protected function setParamsValues($arrayData, $overwrite = false, $logginLevelSet = Log::LV_DEBUG)
    {

        if (!is_array($arrayData)) {
            throw new \Exception('Invalid data params ');
        }
        $result = true;

        foreach ($arrayData as $key => $arrayValues) {
            if (isset($this->params[$key])) {
                $arrayValues      = (array) $arrayValues;
                $arrayValValToStr = array_map(array('\\Duplicator\\Installer\\Utils\\Log\\Log', 'v2str'), $arrayValues);

                $this->paramsHtmlInfo[] = 'SET PARAM <b>' . $key . '</b> ARRAY DATA: ' .
                    SnapString::implodeKeyVals(', ', $arrayValValToStr, '[<b>%s</b> = %s]');
                if ($this->params[$key]->fromArrayData($arrayValues) === false) {
                    Log::info('PARAM ISSUE SET KEY[' . $key . '] VALUE: ' . SnapString::implodeKeyVals(', ', $arrayValValToStr, '[%s = %s]'));
                    Log::info(Log::traceToString(debug_backtrace()));
                    // $result = false;
                } else {
                    $log  = 'PARAM SET KEY[' . $key . ']';
                    $log .= (Log::isLevel(Log::LV_DEBUG) ? (' VALUE: ' . SnapString::implodeKeyVals(', ', $arrayValValToStr, '[%s = %s]')) : '');
                    Log::info($log, $logginLevelSet);
                    if ($overwrite) {
                        $this->params[$key]->setOveriteStatus();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * update persistance file
     *
     * @return bool\int // This function returns the number of bytes that were written to the file, or FALSE on failure.
     */
    public function save()
    {
        Log::info("SAVE PARAMS\n" . Log::traceToString(debug_backtrace()), Log::LV_DEBUG);

        $arrayData = array();
        foreach ($this->params as $param) {
            if ($param->isPersistent()) {
                $arrayData[$param->getName()] = $param->toArrayData();
            }
        }
        $json = SnapJson::jsonEncodePPrint($arrayData);
        if (($result = file_put_contents(self::getPersistanceFilePath(), $json, LOCK_EX)) === false) {
            Log::info('PRAMS: can\'t save persistence file');
        }
        return $result;
    }

    /**
     *
     * @staticvar string $path
     * @return string
     */
    protected static function getPersistanceFilePath()
    {
        static $path = null;

        if (is_null($path)) {
            $path = DUPX_INIT . '/' . 'dup-params__' . \DUPX_Package::getPackageHash() . '.json';
        }

        return $path;
    }

    /**
     * html params info for debug params
     *
     * @return void
     */
    public function getParamsHtmlInfo()
    {
        if (!$this->getValue(self::PARAM_DEBUG_PARAMS)) {
            return;
        }
        ?>
        <div id="params-html-info">
            <h3>CURRENT VALUES</h3>
            <ul class="values">
                <?php foreach ($this->params as $param) { ?>
                    <li>
                        PARAM <b><?php echo $param->getName(); ?></b> VALUE: <b><?php echo htmlentities(Log::v2str($param->getValue())); ?></b>
                    </li>
                <?php } ?>
            </ul>
            <h3>LOAD SEQUENCE</h3>
            <ul class="load-sequence">
                <?php foreach ($this->paramsHtmlInfo as $info) { ?>
                    <li>
                        <?php echo $info; ?>
                    </li>
                <?php } ?>
            </ul>
            <h3>ARCHIVE PARAM DATA</h3>
            <pre><?php
                    $data = \DUPX_ArchiveConfig::getInstance();
                    var_dump($data);
            ?></pre>
        </div>
        <?php
    }

    /**
     * Get params value list for log
     *
     * @return string
     */
    public function getParamsToText()
    {
        $result = array();

        foreach ($this->params as $param) {
            if (method_exists($param, 'getFormStatus')) {
                $line = 'PARAM FORM ' . $param->getName() . ' VALUE: ' . Log::v2str($param->getValue()) . ' STATUS: ' . $param->getFormStatus();
            } else {
                $line = 'PARAM ITEM ' . $param->getName() . ' VALUE: ' . Log::v2str($param->getValue());
            }

            $result[] = $line;
        }

        return implode("\n", $result);
    }

    /**
     * Prevent clone object
     *
     * @return void
     */
    private function __clone()
    {
    }
}
