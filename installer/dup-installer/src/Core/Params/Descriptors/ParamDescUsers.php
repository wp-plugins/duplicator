<?php

/**
 * Users params descriptions
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
use Duplicator\Installer\Core\Params\Items\ParamFormUsersReset;
use DUPX_DBInstall;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescUsers implements DescriptorInterface
{
    const USER_MODE_OVERWRITE    = 'overwrite';
    const USER_MODE_IMPORT_USERS = 'import_users';
    /**
     * Init params
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function init(&$params)
    {
        $params[PrmMng::PARAM_USERS_MODE] = new ParamForm(
            PrmMng::PARAM_USERS_MODE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_RADIO,
            array(
                'default'          => self::USER_MODE_OVERWRITE,
                'sanitizeCallback' => function ($value) {
                    if (
                        DUPX_InstallerState::getInstance()->getMode() !== DUPX_InstallerState::MODE_OVR_INSTALL ||
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        // if is restore backup user mode must be overwrite
                        return ParamDescUsers::USER_MODE_OVERWRITE;
                    }

                    $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
                    if ($overwriteData['isMultisite']) {
                        return ParamDescUsers::USER_MODE_OVERWRITE;
                    }

                    // disable keep users for some db actions
                    switch (PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_ACTION)) {
                        case DUPX_DBInstall::DBACTION_CREATE:
                        case DUPX_DBInstall::DBACTION_MANUAL:
                        case DUPX_DBInstall::DBACTION_ONLY_CONNECT:
                            return ParamDescUsers::USER_MODE_OVERWRITE;
                        case DUPX_DBInstall::DBACTION_EMPTY:
                        case DUPX_DBInstall::DBACTION_REMOVE_ONLY_TABLES:
                        case DUPX_DBInstall::DBACTION_RENAME:
                            return $value;
                    }
                },
                'acceptValues' => array(
                    self::USER_MODE_OVERWRITE,
                    self::USER_MODE_IMPORT_USERS
                )
            ),
            array(
                'status' => function () {
                    /** Hide user mode instandalone migration for now */
                    return ParamForm::STATUS_SKIP;
                    if (DUPX_InstallerState::getInstance()->getMode() !== DUPX_InstallerState::MODE_OVR_INSTALL) {
                        return ParamForm::STATUS_DISABLED;
                    }

                    $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

                    if (
                        $overwriteData['isMultisite'] ||
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        return ParamForm::STATUS_DISABLED;
                    }
                    return ParamForm::STATUS_ENABLED;
                },
                'label'   => 'Users:',
                'options' => function ($item) {
                    $result   = array();
                    $result[] = new ParamOption(ParamDescUsers::USER_MODE_OVERWRITE, 'Overwrite');
                    $result[] = new ParamOption(ParamDescUsers::USER_MODE_IMPORT_USERS, 'Merge');
                    return $result;
                },
                'inlineHelp' => dupxTplRender('parts/params/inline_helps/user_mode', array(), false),
                'wrapperClasses' => array('revalidate-on-change')
            )
        );

        $params[PrmMng::PARAM_USERS_PWD_RESET] = new ParamFormUsersReset(
            PrmMng::PARAM_USERS_PWD_RESET,
            ParamFormUsersReset::TYPE_ARRAY_STRING,
            ParamFormUsersReset::FORM_TYPE_USERS_PWD_RESET,
            array( // ITEM ATTRIBUTES
                'default' => array_map(function ($value) {
                    return '';
                }, \DUPX_ArchiveConfig::getInstance()->getUsersLists()),
                'sanitizeCallback' => array('\\Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateCallback' => function ($value) {
                    return strlen($value) == 0 || strlen($value) >= \DUPX_Constants::MIN_NEW_PASSWORD_LEN;
                },
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    if (strlen($value) > 0 && strlen($value) <  \DUPX_Constants::MIN_NEW_PASSWORD_LEN) {
                        $paramObj->setInvalidMessage('New password must have ' . \DUPX_Constants::MIN_NEW_PASSWORD_LEN . ' or more characters');
                        return false;
                    }

                    return true;
                }
            ),
            array( // FORM ATTRIBUTES
                'status' => function ($paramObj) {
                    if (ParamDescUsers::getUsersMode() != ParamDescUsers::USER_MODE_OVERWRITE) {
                        return ParamForm::STATUS_DISABLED;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'label'       => 'Existing user reset password:',
                'classes'     => 'strength-pwd-check',
                'attr'        => array(
                    'title'       => \DUPX_Constants::MIN_NEW_PASSWORD_LEN . ' characters minimum',
                    'placeholder' => "Reset user password"
                )
            )
        );
    }

    /**
     * Return import users mode
     *
     * @return string
     */
    public static function getUsersMode()
    {
        $paramsManager = PrmMng::getInstance();
        return $paramsManager->getValue(PrmMng::PARAM_USERS_MODE);
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
