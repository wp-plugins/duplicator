<?php

/**
 * Database params descriptions
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Core\Params\Descriptors;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Descriptors\ParamsDescriptors;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Installer\Core\Params\Items\ParamOption;
use Duplicator\Installer\Core\Params\Items\ParamFormTables;
use Duplicator\Installer\Core\Params\Items\ParamFormPass;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapJson;
use Duplicator\Libs\Snap\SnapUtil;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescDatabase implements DescriptorInterface
{
    const INVALID_EMPTY                     = 'can\'t be empty';
    const EMPTY_COLLATION_LABEL             = ' --- DEFAULT --- ';
    const DEFAULT_CHARSET_POSTFIX           = ' (Default)';
    const DEFAULT_COLLATE_POSTFIX           = ' (Default)';
    const SPLIT_CREATE_MAX_VALUE_TO_DEFAULT = 200;

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

        $params[PrmMng::PARAM_DB_DISPLAY_OVERWIRE_WARNING] = new ParamItem(
            PrmMng::PARAM_DB_DISPLAY_OVERWIRE_WARNING,
            ParamItem::TYPE_BOOL,
            array(
                'default' => true
            )
        );

        $params[PrmMng::PARAM_DB_VIEW_MODE] = new ParamForm(
            PrmMng::PARAM_DB_VIEW_MODE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_BGROUP,
            array(
                'default'      => 'basic',
                'acceptValues' => array(
                    'basic',
                    'cpnl'
                )
            ),
            array(
                'label'                 => 'Database view mode',
                'renderLabel'           => false,
                'options'               => array(
                    new ParamOption('basic', 'Default'),
                    new ParamOption('cpnl', 'cPanel')
                ),
                'wrapperClasses'        => array('revalidate-on-change', 'align-right'),
                'inputContainerClasses' => array('small')
            )
        );

        $params[PrmMng::PARAM_DB_HOST] = new ParamForm(
            PrmMng::PARAM_DB_HOST,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'persistence'      => true,
                'default'          => 'localhost',
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline'),
                'validateCallback' => array(__CLASS__, 'validateNoEmptyIfBasic'),
                'invalidMessage'   => self::INVALID_EMPTY
            ),
            array(
                'label'          => 'Host:',
                'wrapperClasses' => array('revalidate-on-change'),
                'attr'           => array(
                    'required'    => 'required',
                    'placeholder' => 'localhost'
                )
            )
        );

        $params[PrmMng::PARAM_DB_NAME] = new ParamForm(
            PrmMng::PARAM_DB_NAME,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'persistence'      => true,
                'default'          => '',
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline'),
                'validateCallback' => array(__CLASS__, 'validateNoEmptyIfBasic'),
                'invalidMessage'   => self::INVALID_EMPTY
            ),
            array(
                'label'          => 'Database:',
                'wrapperClasses' => array('revalidate-on-change'),
                'attr'           => array(
                    'required'    => 'required',
                    'placeholder' => 'new or existing database name'
                ),
                'subNote'        => dupxTplRender('parts/params/db-name-notes', array(), false)
            )
        );

        $params[PrmMng::PARAM_DB_USER] = new ParamForm(
            PrmMng::PARAM_DB_USER,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'persistence'      => true,
                'default'          => '',
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline'),
                'validateCallback' => array(__CLASS__, 'validateNoEmptyIfBasic'),
                'invalidMessage'   => self::INVALID_EMPTY
            ),
            array(
                'label'          => 'User:',
                'wrapperClasses' => array('revalidate-on-change'),
                'attr'           => array(
                    'placeholder'  => 'valid database username',
                    // Can be written field wise
                    // Ref. https://developer.mozilla.org/en-US/docs/Web/Security/Securing_your_site/Turning_off_form_autocompletion
                    'autocomplete' => "off"
                )
            )
        );

        $params[PrmMng::PARAM_DB_PASS] = new ParamFormPass(
            PrmMng::PARAM_DB_PASS,
            ParamFormPass::TYPE_STRING,
            ParamFormPass::FORM_TYPE_PWD_TOGGLE,
            array(
                'persistence'      => true,
                'default'          => '',
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline')
            ),
            array(
                'label'          => 'Password:',
                'wrapperClasses' => array('revalidate-on-change'),
                'attr'           => array(
                    'placeholder'  => 'valid database user password',
                    // Can be written field wise
                    // Ref. https://devBasicBasiceloper.mozilla.org/en-US/docs/Web/Security/Securing_your_site/Turning_off_form_autocompletion
                    'autocomplete' => "off"
                )
            )
        );

        $params[PrmMng::PARAM_DB_FLAG] = new ParamItem(
            PrmMng::PARAM_DB_FLAG,
            ParamForm::TYPE_INT,
            array(
                'default'      => \DUPX_DB::DB_CONNECTION_FLAG_NOT_SET,
                'acceptValues' => function (ParamItem $param) {
                    $result = array(
                        \DUPX_DB::MYSQLI_CLIENT_NO_FLAGS,
                        MYSQLI_CLIENT_SSL,
                    );
                    if (defined("MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT")) {
                        $result[] = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
                    }
                    return $result;
                }
            )
        );

        $params[PrmMng::PARAM_DB_CHARSET] = new ParamForm(
            PrmMng::PARAM_DB_CHARSET,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'          => $archiveConfig->getWpConfigDefineValue('DB_CHARSET', ''),
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateRegex'    => ParamForm::VALIDATE_REGEX_AZ_NUMBER_SEP_EMPTY
            ),
            array(
                'label'  => 'Charset:',
                'status' => function (ParamForm $param) {
                    if (DUPX_InstallerState::isRestoreBackup()) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'options' => array(__CLASS__, 'getCharsetSelectOptions')
            )
        );

        $params[PrmMng::PARAM_DB_COLLATE] = new ParamForm(
            PrmMng::PARAM_DB_COLLATE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'          => $archiveConfig->getWpConfigDefineValue('DB_COLLATE', ''),
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateRegex'    => ParamForm::VALIDATE_REGEX_AZ_NUMBER_SEP_EMPTY
            ),
            array(
                'label'  => 'Collation:',
                'status' => function () {
                    if (DUPX_InstallerState::isRestoreBackup()) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'options' => array(__CLASS__, 'getCollationSelectOptions')
            )
        );

        $params[PrmMng::PARAM_DB_TABLE_PREFIX] = new ParamForm(
            PrmMng::PARAM_DB_TABLE_PREFIX,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => \DUPX_ArchiveConfig::getInstance()->wp_tableprefix,
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateRegex'    => ParamForm::VALIDATE_REGEX_AZ_NUMBER_SEP
            ),
            array(
                'status' => function () {
                    return ParamForm::STATUS_DISABLED;
                },
                'label'          => 'Table Prefix:',
                'proFlagTitle'   => 'Upgrade Features',
                'proFlag'        => '<p>Enhance the install experiance by changing the table prefix to a new name during installation.</p>'
            )
        );

        $params[PrmMng::PARAM_DB_VIEW_CREATION] = new ParamForm(
            PrmMng::PARAM_DB_VIEW_CREATION,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => true
            ),
            array(
                'label'         => 'Objects:',
                'checkboxLabel' => 'Enable View Creation'
            )
        );

        $params[PrmMng::PARAM_DB_PROC_CREATION] = new ParamForm(
            PrmMng::PARAM_DB_PROC_CREATION,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => true
            ),
            array(
                'label'         => ' ',
                'checkboxLabel' => 'Enable Stored Procedure Creation'
            )
        );

        $params[PrmMng::PARAM_DB_FUNC_CREATION] = new ParamForm(
            PrmMng::PARAM_DB_FUNC_CREATION,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => true
            ),
            array(
                'label'         => ' ',
                'checkboxLabel' => 'Enable Function Creation'
            )
        );

        $params[PrmMng::PARAM_DB_REMOVE_DEFINER] = new ParamForm(
            PrmMng::PARAM_DB_REMOVE_DEFINER,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => false
            ),
            array(
                'label'         => ' ',
                'checkboxLabel' => 'Remove security DEFINER declarations'
            )
        );

        $numTables                              = count((array) \DUPX_ArchiveConfig::getInstance()->dbInfo->tablesList);
        $params[PrmMng::PARAM_DB_SPLIT_CREATES] = new ParamForm(
            PrmMng::PARAM_DB_SPLIT_CREATES,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => ($numTables > self::SPLIT_CREATE_MAX_VALUE_TO_DEFAULT ? false : true)
            ),
            array(
                'label'         => 'Create:',
                'checkboxLabel' => 'Run all CREATE SQL statements at once'
            )
        );

        $newObj = new ParamForm(
            PrmMng::PARAM_DB_MYSQL_MODE_OPTS,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => '',
                'validateRegex'    => '/^[A-Za-z0-9_\-,]*$/', // db options with , and can be empty
                'sanitizeCallback' => function ($value) {
                    $value = SnapUtil::sanitizeNSCharsNewlineTrim($value);
                    return str_replace(' ', '', $value);
                },
            ),
            array(
                'label'          => ' ', // for aligment at PARAM_DB_MYSQL_MODE
                'wrapperClasses' => 'no-display',
                'subNote'        => 'Separate additional ' . \DUPX_View_Funcs::helpLink('step2', 'sql modes', false) . ' with commas &amp; no spaces.<br>'
                . 'Example: <i>NO_ENGINE_SUBSTITUTION,NO_ZERO_IN_DATE,...</i>.</small>'
            )
        );
        $params[PrmMng::PARAM_DB_MYSQL_MODE_OPTS] = $newObj;
        $modeOptsWrapper                          = $newObj->getFormWrapperId();

        $params[PrmMng::PARAM_DB_MYSQL_MODE] = new ParamForm(
            PrmMng::PARAM_DB_MYSQL_MODE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_RADIO,
            array(
            'default'      => 'DEFAULT',
            'acceptValues' => array(
                'DEFAULT',
                'DISABLE',
                'CUSTOM'
            )
            ),
            array(
            'label'   => 'Mode:',
            'options' => array(
                new ParamOption('DEFAULT', 'Default', ParamOption::OPT_ENABLED, array(
                    'onchange' => "if ($(this).is(':checked')) { "
                    . "jQuery('#" . $modeOptsWrapper . "').addClass('no-display');"
                    . "}"
                    )),
                new ParamOption('DISABLE', 'Disable', ParamOption::OPT_ENABLED, array(
                    'onchange' => "if ($(this).is(':checked')) { "
                    . "jQuery('#" . $modeOptsWrapper . "').addClass('no-display');"
                    . "}"
                    )),
                new ParamOption('CUSTOM', 'Custom', ParamOption::OPT_ENABLED, array(
                    'onchange' => "if ($(this).is(':checked')) { "
                    . "jQuery('#" . $modeOptsWrapper . "').removeClass('no-display');"
                    . "}")),
            ))
        );

        $params[PrmMng::PARAM_DB_TABLES] = new ParamFormTables(
            PrmMng::PARAM_DB_TABLES,
            ParamFormTables::TYPE_ARRAY_TABLES,
            ParamFormTables::FORM_TYPE_TABLES_SELECT,
            array(// ITEM ATTRIBUTES
                'default' => array()
            ),
            array(// FORM ATTRIBUTES
                'label'       => 'Tables',
                'renderLabel' => false,
                'status' => function (ParamForm $paramObj) {
                    if (DUPX_InstallerState::isRestoreBackup()) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                }
            )
        );
    }

    /**
     * Validate function for database params
     *
     * @param mixed     $value    input value
     * @param ParamItem $paramObj current param object
     *
     * @return boolean
     */
    public static function validateNoEmptyIfBasic($value, ParamItem $paramObj)
    {
        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_VIEW_MODE) !== 'basic') {
            return true;
        }

        return ParamsDescriptors::validateNotEmpty($value, $paramObj);
    }

    /**
     * Get charset options list
     *
     * @return ParamOption[]
     */
    public static function getCharsetSelectOptions()
    {
        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_VALIDATION_LEVEL) < \DUPX_Validation_manager::MIN_LEVEL_VALID) {
            return array();
        }

        $data       = \DUPX_DB_Functions::getInstance()->getCharsetAndCollationData();
        $charsetDef = \DUPX_DB_Functions::getInstance()->getDefaultCharset();

        $options = array();

        foreach ($data as $charset => $charsetInfo) {
            $label     = $charset . ($charset == $charsetDef ? self::DEFAULT_CHARSET_POSTFIX : '');
            $options[] = new ParamOption($charset, $label, ParamOption::OPT_ENABLED, array(
                'data-collations'        => json_encode($charsetInfo['collations']),
                'data-collation-default' => $charsetInfo['defCollation']
            ));
        }

        return $options;
    }

    /**
     * Get collation options list
     *
     * @return ParamOption[]
     */
    public static function getCollationSelectOptions()
    {
        $options = array(
            new ParamOption('', self::EMPTY_COLLATION_LABEL)
        );

        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_VALIDATION_LEVEL) < \DUPX_Validation_manager::MIN_LEVEL_VALID) {
            return $options;
        }

        $data           = \DUPX_DB_Functions::getInstance()->getCharsetAndCollationData();
        $currentCharset = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_CHARSET);

        if (!isset($data[$currentCharset])) {
            return $options;
        }

        $defaultCollation = \DUPX_DB_Functions::getInstance()->getDefaultCollateOfCharset($currentCharset);
        // if charset exists update default
        $options = array(
            new ParamOption('', self::EMPTY_COLLATION_LABEL . ' [' . $defaultCollation . ']')
        );

        foreach ($data[$currentCharset]['collations'] as $collation) {
            $label     = $collation . ($collation == $data[$currentCharset]['defCollation'] ? self::DEFAULT_COLLATE_POSTFIX : '');
            $options[] = new ParamOption($collation, $label);
        }

        return $options;
    }

    /**
     * Update Charset and collate param by database settings
     *
     * @return void
     */
    public static function updateCharsetAndCollateByDatabaseSettings()
    {
        $paramsManager = PrmMng::getInstance();
        $data          = \DUPX_DB_Functions::getInstance()->getCharsetAndCollationData();
        $charsetDef    = \DUPX_DB_Functions::getInstance()->getDefaultCharset();

        $currentCharset = $paramsManager->getValue(PrmMng::PARAM_DB_CHARSET);
        $currentCollate = $paramsManager->getValue(PrmMng::PARAM_DB_COLLATE);

        if (!array_key_exists($currentCharset, $data)) {
            $paramsManager->setValue(PrmMng::PARAM_DB_CHARSET, $charsetDef);
            $paramsManager->setValue(PrmMng::PARAM_DB_COLLATE, '');
            Log::info('DEFAULT DB_CHARSET [' . $currentCharset . '] isn\'t valid, update DB_CHARSET to ' . $charsetDef . ' and DB_COLLATE set empty');
        } elseif (strlen($currentCollate) > 0 && !in_array($currentCollate, $data[$currentCharset]['collations'])) {
            $paramsManager->setValue(PrmMng::PARAM_DB_COLLATE, '');
            Log::info('DEFAULT DB_COLLATE [' . $currentCollate . '] isn\'t valid, DB_COLLATE set empty');
        }
        $paramsManager->save();
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
        $params[PrmMng::PARAM_DB_TABLES]->setValue(\DUPX_DB_Tables::getInstance()->getDefaultParamValue());
    }
}
