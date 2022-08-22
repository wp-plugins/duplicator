<?php

/**
 * New admin params descriptions
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
use Duplicator\Installer\Core\Params\Items\ParamFormPass;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescNewAdmin implements DescriptorInterface
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
        $params[PrmMng::PARAM_WP_ADMIN_CREATE_NEW] = new ParamForm(
            PrmMng::PARAM_WP_ADMIN_CREATE_NEW,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_SWITCH,
            array(
                'default' => false
            ),
            array(
                'label'  => 'Create New User:',
                'status' => function ($paramObj) {
                    if (ParamDescUsers::getUsersMode() != ParamDescUsers::USER_MODE_OVERWRITE) {
                        return ParamForm::STATUS_DISABLED;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'checkboxLabel' => ''
            )
        );

        $params[PrmMng::PARAM_WP_ADMIN_NAME] = new ParamForm(
            PrmMng::PARAM_WP_ADMIN_NAME,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
            'default'          => '',
            'sanitizeCallback' => array('\\Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
            'validateCallback' => function ($value, ParamItem $paramObj) {
                if (!PrmMng::getInstance()->getValue(PrmMng::PARAM_WP_ADMIN_CREATE_NEW)) {
                    return true;
                }

                if (strlen($value) < 4) {
                    $paramObj->setInvalidMessage('Must have 4 or more characters');
                    return false;
                }

                return true;
            }
            ),
            array(
                'status'  => array(__CLASS__, 'getStatuOfNewAdminParams'),
                'label'   => 'Username:',
                'classes' => 'new-admin-field',
                'attr'    => array(
                    'title'       => '4 characters minimum',
                    'placeholder' => "(4 or more characters)"
                )
            )
        );

        $params[PrmMng::PARAM_WP_ADMIN_PASSWORD] = new ParamFormPass(
            PrmMng::PARAM_WP_ADMIN_PASSWORD,
            ParamFormPass::TYPE_STRING,
            ParamFormPass::FORM_TYPE_PWD_TOGGLE,
            array(
                'default'          => '',
                'sanitizeCallback' => array('\\Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    if (!PrmMng::getInstance()->getValue(PrmMng::PARAM_WP_ADMIN_CREATE_NEW)) {
                        return true;
                    }

                    if (strlen($value) <  \DUPX_Constants::MIN_NEW_PASSWORD_LEN) {
                        $paramObj->setInvalidMessage('Must have ' . \DUPX_Constants::MIN_NEW_PASSWORD_LEN . ' or more characters');
                        return false;
                    }

                    return true;
                }
            ),
            array(
                'status'  => array(__CLASS__, 'getStatuOfNewAdminParams'),
                'label'   => 'Password:',
                'classes' => array('strength-pwd-check', 'new-admin-field'),
                'attr'    => array(
                    'placeholder' => '(' . \DUPX_Constants::MIN_NEW_PASSWORD_LEN . ' or more characters)',
                    'title'       => \DUPX_Constants::MIN_NEW_PASSWORD_LEN . ' characters minimum'
                )
            )
        );

        $params[PrmMng::PARAM_WP_ADMIN_MAIL] = new ParamForm(
            PrmMng::PARAM_WP_ADMIN_MAIL,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => '',
                'sanitizeCallback' => array('\\Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim'),
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    if (!PrmMng::getInstance()->getValue(PrmMng::PARAM_WP_ADMIN_CREATE_NEW)) {
                        return true;
                    }

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
            array(
                'status'  => array(__CLASS__, 'getStatuOfNewAdminParams'),
                'label'   => 'Email:',
                'classes' => 'new-admin-field',
                'attr'    => array(
                    'title'       => '4 characters minimum',
                    'placeholder' => "(4 or more characters)"
                )
            )
        );

        $params[PrmMng::PARAM_WP_ADMIN_NICKNAME] = new ParamForm(
            PrmMng::PARAM_WP_ADMIN_NICKNAME,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => '',
                'sanitizeCallback' => array('\\Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim')
            ),
            array(
                'status'  => array(__CLASS__, 'getStatuOfNewAdminParams'),
                'label'   => 'Nickname:',
                'classes' => 'new-admin-field',
                'attr'    => array(
                    'title'       => 'if username is empty',
                    'placeholder' => "(if username is empty)"
                )
            )
        );

        $params[PrmMng::PARAM_WP_ADMIN_FIRST_NAME] = new ParamForm(
            PrmMng::PARAM_WP_ADMIN_FIRST_NAME,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => '',
                'sanitizeCallback' => array('\\Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim')
            ),
            array(
                'status'  => array(__CLASS__, 'getStatuOfNewAdminParams'),
                'label'   => 'First Name:',
                'classes' => 'new-admin-field',
                'attr'    => array(
                    'title'       => 'optional',
                    'placeholder' => "(optional)"
                )
            )
        );

        $params[PrmMng::PARAM_WP_ADMIN_LAST_NAME] = new ParamForm(
            PrmMng::PARAM_WP_ADMIN_LAST_NAME,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'default'          => '',
                'sanitizeCallback' => array('\\Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim')
            ),
            array(
                'status'  => array(__CLASS__, 'getStatuOfNewAdminParams'),
                'label'   => 'Last Name:',
                'classes' => 'new-admin-field',
                'attr'    => array(
                    'title'       => 'optional',
                    'placeholder' => "(optional)"
                )
            )
        );
    }

    /**
     *
     * @return string
     */
    public static function getStatuOfNewAdminParams()
    {
        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_WP_ADMIN_CREATE_NEW)) {
            return ParamForm::STATUS_ENABLED;
        } else {
            return ParamForm::STATUS_DISABLED;
        }
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
