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
use Duplicator\Installer\Core\Params\Items\ParamFormPass;
use DUPX_ArchiveConfig;
use DUPX_InstallerState;
use DUPX_Security;
use DUPX_View_Funcs;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescSecurity implements DescriptorInterface
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
        $params[PrmMng::PARAM_SECURE_PASS] = new ParamFormPass(
            PrmMng::PARAM_SECURE_PASS,
            ParamFormPass::TYPE_STRING,
            ParamFormPass::FORM_TYPE_PWD_TOGGLE,
            array(
                'persistence'      => false,
                'default'          => null,
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline')
            ),
            array(
                'label'    => 'Password:',
                'status' => function (ParamForm $param) {
                    if (DUPX_Security::getInstance()->getSecurityType() == DUPX_Security::SECURITY_PASSWORD) {
                        return ParamForm::STATUS_ENABLED;
                    } else {
                        return ParamForm::STATUS_DISABLED;
                    }
                },
                'wrapperClasses' => 'margin-bottom-1',
                'attr' => array(
                    'placeholder' => (DUPX_ArchiveConfig::getInstance()->secure_on ? '' : 'Password not enabled')
                )
            )
        );

        $params[PrmMng::PARAM_SECURE_ARCHIVE_HASH] = new ParamForm(
            PrmMng::PARAM_SECURE_ARCHIVE_HASH,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
                'persistence'      => false,
                'default'          => null,
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewlineTrim')
            ),
            array(
                'label' => 'Archive File Name:',
                'status' => function (ParamForm $param) {
                    if (!DUPX_InstallerState::isOverwrite()) {
                        return ParamForm::STATUS_SKIP;
                    } elseif (DUPX_Security::getInstance()->getSecurityType() == DUPX_Security::SECURITY_ARCHIVE) {
                        return ParamForm::STATUS_ENABLED;
                    } else {
                        return ParamForm::STATUS_DISABLED;
                    }
                },
                'wrapperClasses' => 'margin-bottom-4',
                'attr' => array(
                    'placeholder' => 'example: [full-unique-name]_archive.zip'
                ),
                'subNote'  => DUPX_View_Funcs::helpLink('secure', 'How to get archive file name?', false)
            )
        );

        $params[PrmMng::PARAM_SECURE_OK] = new ParamItem(
            PrmMng::PARAM_SECURE_OK,
            ParamForm::TYPE_BOOL,
            array(
                'default' => false
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
