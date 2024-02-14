<?php

/**
 * WP-config params descriptions
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Core\Params\Descriptors;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Installer\Core\Params\Items\ParamOption;
use Duplicator\Installer\Core\Params\Items\ParamFormWpConfig;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapDB;
use DUPX_ArchiveConfig;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescWpConfig implements DescriptorInterface
{
    const NOTICE_ID_WP_CONF_PARAM_PATHS_EMPTY      = 'wp_conf_param_paths_empty_to_validate';
    const NOTICE_ID_WP_CONF_FORCE_SSL_ADMIN        = 'wp_conf_disabled_force_ssl_admin';
    const NOTICE_ID_WP_CONF_PARAM_DOMAINS_MODIFIED = 'wp_conf_param_domains_empty_to_validate';

    /**
     * Init params
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function init(&$params)
    {
        $archiveConfig = \DUPX_ArchiveConfig::getInstance();

        $params[PrmMng::PARAM_GEN_WP_AUTH_KEY] = new ParamForm(
            PrmMng::PARAM_GEN_WP_AUTH_KEY,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => 'Auth Keys:',
            'checkboxLabel' => 'Generate New Unique Authentication Keys and Salts',
            'status'        => ParamForm::STATUS_INFO_ONLY
            )
        );

        $params[PrmMng::PARAM_WP_CONF_DISALLOW_FILE_EDIT] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_DISALLOW_FILE_EDIT,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue(
                    'DISALLOW_FILE_EDIT'
                )
            ),
            array(
                'label'         => 'DISALLOW_FILE_EDIT:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Disable the Plugin/Theme Editor'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_DISALLOW_FILE_MODS] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_DISALLOW_FILE_MODS,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue(
                    'DISALLOW_FILE_MODS',
                    array(
                        'value'      => false,
                        'inWpConfig' => false
                    )
                )
            ),
            array(
                'label'         => 'DISALLOW_FILE_MODS:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'This will block users being able to use the plugin and theme installation/update ' .
                    'functionality from the WordPress admin area'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_AUTOSAVE_INTERVAL] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_AUTOSAVE_INTERVAL,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_NUMBER,
            array( // ITEM ATTRIBUTES
                'default' => $archiveConfig->getDefineArrayValue(
                    'AUTOSAVE_INTERVAL',
                    array(
                        'value'      => 60,
                        'inWpConfig' => false
                    )
                )
            ),
            array( // FORM ATTRIBUTES
                'label'          => 'AUTOSAVE_INTERVAL:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_POST_REVISIONS] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_POST_REVISIONS,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_NUMBER,
            array( // ITEM ATTRIBUTES
                'default'          => $archiveConfig->getDefineArrayValue(
                    'WP_POST_REVISIONS',
                    array(
                        'value'      => true,
                        'inWpConfig' => false
                    )
                ),
            ),
            array( // FORM ATTRIBUTES
                'label'          => 'WP_POST_REVISIONS:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
            )
        );

        $params[PrmMng::PARAM_WP_CONF_FORCE_SSL_ADMIN] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_FORCE_SSL_ADMIN,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => self::getDefaultForceSSLAdminConfig(),
            ),
            array(
                'label'         => 'FORCE_SSL_ADMIN:',
                'checkboxLabel' => 'Enforce Admin SSL'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_AUTOMATIC_UPDATER_DISABLED,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue(
                    'AUTOMATIC_UPDATER_DISABLED',
                    array(
                        'value'      => false,
                        'inWpConfig' => false
                    )
                )
            ),
            array(
                'label'         => 'AUTOMATIC_UPDATER_DISABLED:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Disable automatic updater'
            )
        );

        $autoUpdateValue = $archiveConfig->getWpConfigDefineValue('WP_AUTO_UPDATE_CORE');
        if (is_bool($autoUpdateValue)) {
            $autoUpdateValue = ($autoUpdateValue ? 'true' : 'false');
        }
        $params[PrmMng::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_AUTO_UPDATE_CORE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => array(
                    'value'      => $autoUpdateValue,
                    'inWpConfig' => $archiveConfig->inWpConfigDefine('WP_AUTO_UPDATE_CORE')
                ),
                'acceptValues' => array('', 'false', 'true', 'minor')
            ),
            array(
                'label'   => 'WP_AUTO_UPDATE_CORE:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'options' => array(
                    new ParamOption('minor', 'Enable only core minor updates - Default'),
                    new ParamOption('false', 'Disable all core updates'),
                    new ParamOption('true', 'Enable all core updates')
                )
            )
        );

        $params[PrmMng::PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_IMAGE_EDIT_OVERWRITE,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue(
                    'IMAGE_EDIT_OVERWRITE',
                    array(
                        'value'      => true,
                        'inWpConfig' => false
                    )
                )
            ),
            array(
                'label'         => 'IMAGE_EDIT_OVERWRITE:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Create only one set of image edits'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_CACHE] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_CACHE,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('WP_CACHE')
            ),
            array(
                'label'         => 'WP_CACHE:',
                'checkboxLabel' => 'Keep Enabled'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WPCACHEHOME] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WPCACHEHOME,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array( // ITEM ATTRIBUTES
                'default'          => $archiveConfig->getDefineArrayValue("WPCACHEHOME"),
                'sanitizeCallback' => function ($value) {
                    $result = SnapUtil::sanitizeNSCharsNewlineTrim($value);
                    // WPCACHEHOME want final slash
                    return SnapIO::safePathTrailingslashit($result);
                }
            ),
            array( // FORM ATTRIBUTES
                'label'   => 'WPCACHEHOME:',
                'subNote' => 'This define is not part of the WordPress core but is a define used by WP Super Cache.'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_TEMP_DIR] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_TEMP_DIR,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array( // ITEM ATTRIBUTES
                'default'          => $archiveConfig->getDefineArrayValue("WP_TEMP_DIR"),
                'sanitizeCallback' => array('Duplicator\\Installer\\Core\\Params\\Descriptors\\ParamsDescriptors', 'sanitizePath')
            ),
            array( // FORM ATTRIBUTES
                'label' => 'WP_TEMP_DIR:',
                //'wrapperClasses' => array('small'),
                //'subNote'        => 'Wordpress admin maximum memory limit (default:256M)'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_DEBUG] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_DEBUG,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('WP_DEBUG')
            ),
            array(
                'label'         => 'WP_DEBUG:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Display errors and warnings'
            )
        );

        $debugLogValue = $archiveConfig->getWpConfigDefineValue('WP_DEBUG_LOG');
        if (is_string($debugLogValue)) {
            $debugLogValue = empty($debugLogValue) ? false : true;
        }
        $params[PrmMng::PARAM_WP_CONF_WP_DEBUG_LOG] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_DEBUG_LOG,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => array(
                    'value'      => $debugLogValue,
                    'inWpConfig' => $archiveConfig->inWpConfigDefine('WP_DEBUG_LOG')
                )
            ),
            array(
                'label'         => 'WP_DEBUG_LOG:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Log errors and warnings',
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_DISABLE_FATAL_ERROR_HANDLER,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('WP_DISABLE_FATAL_ERROR_HANDLER')
            ),
            array(
                'label'         => 'WP_DISABLE_FATAL_ERROR_HANDLER:',
                'checkboxLabel' => 'Disable fatal error handler',
                'status'        => ParamForm::STATUS_INFO_ONLY
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_DEBUG_DISPLAY] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_DEBUG_DISPLAY,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('WP_DEBUG_DISPLAY')
            ),
            array(
                'label'         => 'WP_DEBUG_DISPLAY:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Display errors and warnings'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_SCRIPT_DEBUG] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_SCRIPT_DEBUG,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('SCRIPT_DEBUG')
            ),
            array(
                'label'         => 'SCRIPT_DEBUG:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'JavaScript or CSS errors'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_CONCATENATE_SCRIPTS] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_CONCATENATE_SCRIPTS,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('CONCATENATE_SCRIPTS', array(
                    'value'      => false,
                    'inWpConfig' => false
                ))
            ),
            array(
                'label'         => 'CONCATENATE_SCRIPTS:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Concatenate all JavaScript files into one URL'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_SAVEQUERIES] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_SAVEQUERIES,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('SAVEQUERIES')
            ),
            array(
                'label'         => 'SAVEQUERIES:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Save database queries in an array ($wpdb->queries)'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_ALTERNATE_WP_CRON] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_ALTERNATE_WP_CRON,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('ALTERNATE_WP_CRON', array(
                    'value'      => false,
                    'inWpConfig' => false
                ))
            ),
            array(
                'label'         => 'ALTERNATE_WP_CRON:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Use an alternative Cron with WP'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_DISABLE_WP_CRON] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_DISABLE_WP_CRON,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => $archiveConfig->getDefineArrayValue('DISABLE_WP_CRON', array(
                    'value'      => false,
                    'inWpConfig' => false
                ))
            ),
            array(
                'label'         => 'DISABLE_WP_CRON:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'checkboxLabel' => 'Disable cron entirely'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_CRON_LOCK_TIMEOUT,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_NUMBER,
            array(
                'default'   => $archiveConfig->getDefineArrayValue('WP_CRON_LOCK_TIMEOUT', array(
                    'value'      => 60,
                    'inWpConfig' => false
                )),
                'min_range' => 1
            ),
            array(
                'label'          => 'WP_CRON_LOCK_TIMEOUT:',
                'status'        => ParamForm::STATUS_INFO_ONLY
            )
        );

        $params[PrmMng::PARAM_WP_CONF_EMPTY_TRASH_DAYS] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_EMPTY_TRASH_DAYS,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_NUMBER,
            array(
                'default'   => $archiveConfig->getDefineArrayValue('EMPTY_TRASH_DAYS', array(
                    'value'      => 30,
                    'inWpConfig' => false
                )),
                'min_range' => 0
            ),
            array(
                'label'          => 'EMPTY_TRASH_DAYS:',
                'status'        => ParamForm::STATUS_INFO_ONLY
            )
        );

        $params[PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array( // ITEM ATTRIBUTES
                'default'          => $archiveConfig->getDefineArrayValue("COOKIE_DOMAIN"),
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim')
            ),
            array( // FORM ATTRIBUTES
                'label'   => 'COOKIE_DOMAIN:',
                'subNote' => 'Set <a href="http://www.askapache.com/htaccess/apache-speed-subdomains.html" target="_blank">' .
                    'different domain</a> for cookies.subdomain.example.com'
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_MEMORY_LIMIT] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_MEMORY_LIMIT,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array( // ITEM ATTRIBUTES
                'default'          => $archiveConfig->getDefineArrayValue('WP_MEMORY_LIMIT'),
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateRegex'    => ParamItem::VALIDATE_REGEX_AZ_NUMBER
            ),
            array( // FORM ATTRIBUTES
                'label'          => 'WP_MEMORY_LIMIT:',
                'status'        => ParamForm::STATUS_INFO_ONLY
            )
        );

        $params[PrmMng::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_WP_MAX_MEMORY_LIMIT,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array( // ITEM ATTRIBUTES
                'default'          => $archiveConfig->getDefineArrayValue('WP_MAX_MEMORY_LIMIT')
            ),
            array( // FORM ATTRIBUTES
                'label'          => 'WP_MAX_MEMORY_LIMIT:',
                'status'        => ParamForm::STATUS_INFO_ONLY
            )
        );

        $params[PrmMng::PARAM_WP_CONF_MYSQL_CLIENT_FLAGS] = new ParamFormWpConfig(
            PrmMng::PARAM_WP_CONF_MYSQL_CLIENT_FLAGS,
            ParamForm::TYPE_ARRAY_INT,
            ParamForm::FORM_TYPE_SELECT,
            array( // ITEM ATTRIBUTES
                'default'          => self::getMysqlClientFlagsDefaultVals(),
            ),
            array( // FORM ATTRIBUTES
                'label'          => 'MYSQL_CLIENT_FLAGS:',
                'status'        => ParamForm::STATUS_INFO_ONLY,
                'options' => self::getMysqlClientFlagsOptions(),
                'multiple' => true
            )
        );
    }

    /**
     * Update params after overwrite logic
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function updateParamsAfterOverwrite($params)
    {
        //UPDATE PATHS AUTOMATICALLY
        self::setDefaultWpConfigPathValue($params, PrmMng::PARAM_WP_CONF_WP_TEMP_DIR, 'WP_TEMP_DIR');
        self::setDefaultWpConfigPathValue($params, PrmMng::PARAM_WP_CONF_WPCACHEHOME, 'WPCACHEHOME');
        self::wpConfigPathsNotices();

        //UPDATE DOMAINS AUTOMATICALLY
        self::setDefaultWpConfigDomainValue($params, PrmMng::PARAM_WP_CONF_COOKIE_DOMAIN, "COOKIE_DOMAIN");
        self::wpConfigDomainNotices();
    }

    /**
     * Returns wp counfi default value
     *
     * @return array
     */
    protected static function getMysqlClientFlagsDefaultVals()
    {
        $result = DUPX_ArchiveConfig::getInstance()->getDefineArrayValue(
            'MYSQL_CLIENT_FLAGS',
            array(
                'value'      => array(),
                'inWpConfig' => false
            )
        );

        $result['value'] = array_intersect($result['value'], SnapDB::getMysqlConnectFlagsList(false));
        return $result;
    }

    /**
     * Returns the list of options of the mysql real connect flags
     *
     * @return int[]
     */
    protected static function getMysqlClientFlagsOptions()
    {
        $result = array();
        foreach (SnapDB::getMysqlConnectFlagsList() as $flag) {
            $result[] = new ParamOption(constant($flag), $flag);
        }
        return $result;
    }

    /**
     * Tries to replace the old path with the new path for the given wp config define.
     * If that's not possible returns a notice to the user.
     *
     * @param ParamItem[] $params      params list
     * @param string      $paramKey    param key
     * @param string      $wpConfigKey wp config key
     *
     * @return void
     */
    protected static function setDefaultWpConfigPathValue(&$params, $paramKey, $wpConfigKey)
    {
        if (!self::wpConfigNeedsUpdate($params, $paramKey, $wpConfigKey)) {
            return;
        }

        $oldMainPath = $params[PrmMng::PARAM_PATH_OLD]->getValue();
        $newMainPath = $params[PrmMng::PARAM_PATH_NEW]->getValue();
        $wpConfigVal = \DUPX_ArchiveConfig::getInstance()->getDefineArrayValue($wpConfigKey);

        // TRY TO CHANGE THE VALUE OR RESET
        if (($wpConfigVal['value'] = \DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $wpConfigVal['value'])) === false) {
            $wpConfigVal['inWpConfig'] = false;
            $wpConfigVal['value']      = '';

            \DUPX_NOTICE_MANAGER::getInstance()->addNextStepNotice(array(
                'shortMsg'    => 'WP CONFIG custom paths disabled.',
                'level'       => \DUPX_NOTICE_ITEM::NOTICE,
                'longMsg'     => "The " . $params[$paramKey]->getLabel() . " path could not be set programmatically and has been disabled<br>\n",
                'longMsgMode' => \DUPX_NOTICE_ITEM::MSG_MODE_HTML
            ), \DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, self::NOTICE_ID_WP_CONF_PARAM_PATHS_EMPTY);
        }

        $params[$paramKey]->setValue($wpConfigVal);
    }

    /**
     * Tries to replace the old domain with the new domain for the given wp config define.
     * If that's not possible returns a notice to the user.
     *
     * @param ParamItem[] $params      params list
     * @param string      $paramKey    param key
     * @param string      $wpConfigKey wp config key
     *
     * @return void
     */
    protected static function setDefaultWpConfigDomainValue(&$params, $paramKey, $wpConfigKey)
    {
        if (!self::wpConfigNeedsUpdate($params, $paramKey, $wpConfigKey)) {
            return;
        }

        $wpConfigVal  = \DUPX_ArchiveConfig::getInstance()->getDefineArrayValue($wpConfigKey);
        $parsedUrlNew = parse_url(PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_NEW));
        $parsedUrlOld = parse_url(PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_OLD));

        if ($wpConfigVal['value'] == $parsedUrlOld['host']) {
            $wpConfigVal['value'] = $parsedUrlNew['host'];
        } else {
            $wpConfigVal['inWpConfig'] = false;
            $wpConfigVal['value']      = '';

            \DUPX_NOTICE_MANAGER::getInstance()->addNextStepNotice(array(
                'shortMsg'    => 'WP CONFIG domains disabled.',
                'level'       => \DUPX_NOTICE_ITEM::NOTICE,
                'longMsg'     => "The " . $params[$paramKey]->getLabel() . " domain could not be set programmatically and has been disabled<br>\n",
                'longMsgMode' => \DUPX_NOTICE_ITEM::MSG_MODE_HTML
            ), \DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND, self::NOTICE_ID_WP_CONF_PARAM_DOMAINS_MODIFIED);
        }

        $params[$paramKey]->setValue($wpConfigVal);
    }

    /**
     * Return true if wp config key need update
     *
     * @param ParamItem[] $params      params list
     * @param string      $paramKey    param key
     * @param string      $wpConfigKey wp config key
     *
     * @return bool
     */
    protected static function wpConfigNeedsUpdate(&$params, $paramKey, $wpConfigKey)
    {
        if (
            DUPX_InstallerState::isRestoreBackup($params[PrmMng::PARAM_INST_TYPE]->getValue())
        ) {
            return false;
        }

        // SKIP IF PARAM IS OVERWRITTEN
        if ($params[$paramKey]->getStatus() === ParamItem::STATUS_OVERWRITE) {
            return false;
        }

        // SKIP IF EMPTY
        $wpConfigVal = \DUPX_ArchiveConfig::getInstance()->getDefineArrayValue($wpConfigKey);
        if (strlen($wpConfigVal['value']) === 0) {
            return false;
        }

        // EMPTY IF DISABLED
        if ($wpConfigVal['inWpConfig'] == false) {
            $wpConfigVal['value'] = '';
            $params[$paramKey]->setValue($wpConfigVal);
            return false;
        }

        return true;
    }

    /**
     * Set wp config paths notices
     *
     * @return void
     */
    protected static function wpConfigPathsNotices()
    {
        $noticeManager = \DUPX_NOTICE_MANAGER::getInstance();

        /* PREPEND IF EXISTS */
        $noticeManager->addNextStepNotice(array(
            'shortMsg'    => '',
            'level'       => \DUPX_NOTICE_ITEM::NOTICE,
            'longMsg'     => "It was found that the following config paths were outside of the source site's home path (" .
                \DUPX_ArchiveConfig::getInstance()->getRealValue("originalPaths")->home . "):<br><br>\n",
            'longMsgMode' => \DUPX_NOTICE_ITEM::MSG_MODE_HTML
        ), \DUPX_NOTICE_MANAGER::ADD_UNIQUE_PREPEND_IF_EXISTS, self::NOTICE_ID_WP_CONF_PARAM_PATHS_EMPTY);

        /* APPEND IF EXISTS */
        $msg  = '<br>Keeping config paths that are outside of the home path may cause malfunctions, so these settings have been disabled by default,';
        $msg .= ' but you can set them manually if necessary by switching the install mode ';
        $msg .= 'to "Advanced" and at Step 3 navigating to "Options" &gt; "WP-Config File"';

        $noticeManager->addNextStepNotice(array(
            'shortMsg'    => '',
            'level'       => \DUPX_NOTICE_ITEM::NOTICE,
            'longMsg'     => $msg,
            'longMsgMode' => \DUPX_NOTICE_ITEM::MSG_MODE_HTML
        ), \DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND_IF_EXISTS, self::NOTICE_ID_WP_CONF_PARAM_PATHS_EMPTY);

        $noticeManager->saveNotices();
    }

    /**
     * Set wp config domain notices
     *
     * @return void
     */
    protected static function wpConfigDomainNotices()
    {
        $noticeManager = \DUPX_NOTICE_MANAGER::getInstance();

        /* PREPEND IF EXISTS */
        $noticeManager->addNextStepNotice(array(
            'shortMsg'    => '',
            'level'       => \DUPX_NOTICE_ITEM::NOTICE,
            'longMsg'     => "The following config domains were disabled:<br><br>\n",
            'longMsgMode' => \DUPX_NOTICE_ITEM::MSG_MODE_HTML
        ), \DUPX_NOTICE_MANAGER::ADD_UNIQUE_PREPEND_IF_EXISTS, self::NOTICE_ID_WP_CONF_PARAM_DOMAINS_MODIFIED);

        /* APPEND IF EXISTS */
        $msg  = '<br>The plugin was unable to automatically replace the domain, so the setting has been disabled by default.';
        $msg .= ' Please review them by switching the install mode to "Advanced" and at Step 3 navigating to "Options" &gt; "WP-Config File"';

        $noticeManager->addNextStepNotice(array(
            'shortMsg'    => '',
            'level'       => \DUPX_NOTICE_ITEM::NOTICE,
            'longMsg'     => $msg,
            'longMsgMode' => \DUPX_NOTICE_ITEM::MSG_MODE_HTML
        ), \DUPX_NOTICE_MANAGER::ADD_UNIQUE_APPEND_IF_EXISTS, self::NOTICE_ID_WP_CONF_PARAM_DOMAINS_MODIFIED);

        $noticeManager->saveNotices();
    }

    /**
     * Returns default config value for FORCE_SSL_ADMIN depending on current site's settings
     *
     * @return array
     * @throws \Exception
     */
    protected static function getDefaultForceSSLAdminConfig()
    {
        $forceAdminSSLConfig = \DUPX_ArchiveConfig::getInstance()->getDefineArrayValue('FORCE_SSL_ADMIN');
        if (!\DUPX_U::is_ssl() && $forceAdminSSLConfig['inWpConfig'] === true) {
            $noticeMng = \DUPX_NOTICE_MANAGER::getInstance();
            $noticeMng->addFinalReportNotice(
                array(
                    'shortMsg' => "FORCE_SSL_ADMIN was enabled on none SSL",
                    'level'    => \DUPX_NOTICE_ITEM::SOFT_WARNING,
                    'longMsg'  => 'It was found that FORCE_SSL_ADMIN is enabled and you are installing on a site without SSL, ' .
                        'so that config has been disabled.',
                    'sections' => 'general'
                ),
                \DUPX_NOTICE_MANAGER::ADD_UNIQUE,
                self::NOTICE_ID_WP_CONF_FORCE_SSL_ADMIN
            );
            $noticeMng->saveNotices();
            $forceAdminSSLConfig['value'] = false;
        }
        return $forceAdminSSLConfig;
    }
}
