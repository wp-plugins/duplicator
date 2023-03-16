<?php

/**
 * Generic params descriptions
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
use Duplicator\Installer\Core\Params\Items\ParamFormPass;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapOS;
use DUPX_ArchiveConfig;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescGeneric implements DescriptorInterface
{
    /**
     * Init params
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function init(&$params)
    {
        $newObj = new ParamForm(
            PrmMng::PARAM_FILE_PERMS_VALUE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => '644',
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateRegex'    => '/^[ugorwx,\s\+\-0-7]+$/' // octal + ugo rwx,
            ),
            array(
                'label'          => 'File permissions',
                'renderLabel'    => false,
                'status'         => SnapOS::isWindows() ? ParamForm::STATUS_SKIP : ParamForm::STATUS_ENABLED,
                'wrapperClasses' => array('display-inline-block')
            )
        );

        $params[PrmMng::PARAM_FILE_PERMS_VALUE] = $newObj;
        $permItemId                             = $newObj->getFormItemId();
        $params[PrmMng::PARAM_SET_FILE_PERMS]   = new ParamForm(
            PrmMng::PARAM_SET_FILE_PERMS,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_SWITCH,
            array(
                'default' => !SnapOS::isWindows()
            ),
            array(
                'status'         => SnapOS::isWindows() ? ParamForm::STATUS_SKIP : ParamForm::STATUS_ENABLED,
                'label'          => 'File permissions:',
                'checkboxLabel'  => 'All files',
                'wrapperClasses' => array('display-inline-block'),
                'attr'           => array(
                    'onclick' => "jQuery('#" . $permItemId . "').prop('disabled', !jQuery(this).is(':checked'));"
                )
            )
        );

        $newObj = new ParamForm(
            PrmMng::PARAM_DIR_PERMS_VALUE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => '755',
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateRegex'    => '/^[ugorwx,\s\+\-0-7]+$/' // octal + ugo rwx
            ),
            array(
                'label'          => 'Folder permissions',
                'renderLabel'    => false,
                'status'         => SnapOS::isWindows() ? ParamForm::STATUS_SKIP : ParamForm::STATUS_ENABLED,
                'wrapperClasses' => array('display-inline-block')
            )
        );

        $params[PrmMng::PARAM_DIR_PERMS_VALUE] = $newObj;
        $permItemId                            = $newObj->getFormItemId();
        $params[PrmMng::PARAM_SET_DIR_PERMS]   = new ParamForm(
            PrmMng::PARAM_SET_DIR_PERMS,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_SWITCH,
            array(
                'default' => !SnapOS::isWindows()
            ),
            array(
                'status'         => SnapOS::isWindows() ? ParamForm::STATUS_SKIP : ParamForm::STATUS_ENABLED,
                'label'          => 'Dir permissions:',
                'checkboxLabel'  => 'All Directories',
                'wrapperClasses' => array('display-inline-block'),
                'attr'           => array(
                    'onclick' => "jQuery('#" . $permItemId . "').prop('disabled', !jQuery(this).is(':checked'));"
                )
            )
        );

        $params[PrmMng::PARAM_SAFE_MODE] = new ParamForm(
            PrmMng::PARAM_SAFE_MODE,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => 0,
                'acceptValues' => array(0, 1, 2)
            ),
            array(
                'label'  => 'Safe Mode:',
                'status' => function (ParamItem $paramObj) {
                    if (DUPX_InstallerState::isRestoreBackup()) {
                        return ParamForm::STATUS_DISABLED;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'options' => array(
                    new ParamOption(0, 'Disabled'),
                    new ParamOption(1, 'Enabled')
                ),
                'attr'    => array(
                    'onchange' => 'DUPX.onSafeModeSwitch();'
                )
            )
        );

        $params[PrmMng::PARAM_FILE_TIME] = new ParamForm(
            PrmMng::PARAM_FILE_TIME,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_RADIO,
            array(
                'default'      => 'current',
                'acceptValues' => array(
                    'current',
                    'original'
                )
            ),
            array(
                'label'   => 'File Times:',
                'status'        => function (ParamItem $paramObj) {
                    if (DUPX_ArchiveConfig::getInstance()->isZipArchive()) {
                        return ParamForm::STATUS_ENABLED;
                    } else {
                        return ParamForm::STATUS_DISABLED;
                    }
                },
                'options' => array(
                    new ParamOption('current', 'Current', ParamOption::OPT_ENABLED, array('title' => 'Set the files current date time to now')),
                    new ParamOption('original', 'Original', ParamOption::OPT_ENABLED, array('title' => 'Keep the files date time the same'))
                ),
                'subNote' => function (ParamItem $paramObj) {
                    if (DUPX_ArchiveConfig::getInstance()->isZipArchive()) {
                        return '';
                    } else {
                        return 'This option is not supported for Dup Archive (.daf)';
                    }
                }
            )
        );

        $params[PrmMng::PARAM_LOGGING] = new ParamForm(
            PrmMng::PARAM_LOGGING,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_RADIO,
            array(
                'default'      => Log::LV_DEFAULT,
                'acceptValues' => array(
                    Log::LV_DEFAULT,
                    Log::LV_DETAILED,
                    Log::LV_DEBUG,
                    Log::LV_HARD_DEBUG,
                )
            ),
            array(
                'label'   => 'Logging:',
                'options' => array(
                    new ParamOption(Log::LV_DEFAULT, 'Light'),
                    new ParamOption(Log::LV_DETAILED, 'Detailed'),
                    new ParamOption(Log::LV_DEBUG, 'Debug'),
                    // enabled only with overwrite params
                    new ParamOption(Log::LV_HARD_DEBUG, 'Hard debug', ParamOption::OPT_HIDDEN)
                )
            )
        );

        $params[PrmMng::PARAM_REMOVE_RENDUNDANT] = new ParamForm(
            PrmMng::PARAM_REMOVE_RENDUNDANT,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default'          => false
            ),
            array(
                'label'         => 'Cleanup:',
                'checkboxLabel' => 'Remove disabled plugins/themes (Pro)',
                'status'        => ParamForm::STATUS_DISABLED,
                'proFlagTitle'  => 'Upgrade Features',
                'proFlag'       => '<p>Improve the install cleanup and automation of additional tasks with these cleanup options that are available '
                . 'in Duplicator Pro.</p>'
            )
        );

        $params[PrmMng::PARAM_REMOVE_USERS_WITHOUT_PERMISSIONS] = new ParamForm(
            PrmMng::PARAM_REMOVE_USERS_WITHOUT_PERMISSIONS,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default'          => false
            ),
            array(
                'label'         => ' ',
                'checkboxLabel' => 'Remove users without permissions (Pro)',
                'status'        => ParamForm::STATUS_DISABLED
            )
        );

        $params[PrmMng::PARAM_RECOVERY_LINK] = new ParamItem(
            PrmMng::PARAM_RECOVERY_LINK,
            ParamFormPass::TYPE_STRING,
            array(
                'default' => ''
            )
        );

        $params[PrmMng::PARAM_FROM_SITE_IMPORT_INFO] = new ParamItem(
            PrmMng::PARAM_FROM_SITE_IMPORT_INFO,
            ParamFormPass::TYPE_ARRAY_MIXED,
            array(
                'default' => array()
            )
        );

        $params[PrmMng::PARAM_AUTO_CLEAN_INSTALLER_FILES] = new ParamForm(
            PrmMng::PARAM_AUTO_CLEAN_INSTALLER_FILES,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => true
            ),
            array(
                'label'         => 'CLean installation files',
                'renderLabel'   => false,
                'checkboxLabel' => 'Auto delete installer files after login to secure site (recommended!)'
            )
        );

        $params[PrmMng::PARAM_SUBSCRIBE_EMAIL] = new ParamForm(
            PrmMng::PARAM_SUBSCRIBE_EMAIL,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => '',
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    if (strlen($value) < 4) {
                        $paramObj->setInvalidMessage('Email name must have 4 or more characters');
                        return false;
                    }

                    if (filter_var($value, FILTER_VALIDATE_EMAIL) == false) {
                        $paramObj->setInvalidMessage('Email "' . $value . '"  isn\'t valid');
                        return false;
                    }

                    return true;
                }
            ),
            array(// FORM ATTRIBUTES
                'label'  => 'Subscribe to our newsletter:',
                'renderLabel' => false,
                'wrapperClasses' => array('subscribe-form'),
                'attr'           => array(
                    'placeholder' => 'Email Address'
                ),
                'subNote'        => 'Get tips and product updates straight to your inbox.',
                'status' => function ($paramObj) {
                    if ($paramObj->getValue() !== '') {
                        return ParamForm::STATUS_SKIP;
                    }

                    return ParamForm::STATUS_ENABLED;
                },
                'postfix' => array('type' => 'button', 'label' => 'Subscribe', 'btnAction' => 'DUPX.submitEmail(this);')

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
    }
}
