<?php

/**
 * Replace params descriptions
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
use Duplicator\Libs\Snap\SnapUtil;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescReplace implements DescriptorInterface
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
        $params[PrmMng::PARAM_BLOGNAME] = new ParamForm(
            PrmMng::PARAM_BLOGNAME,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(
            'default'          => '',
            'sanitizeCallback' => function ($value) {
                $value = SnapUtil::sanitizeNSCharsNewline($value);
                return htmlspecialchars_decode((empty($value) ? 'No Blog Title Set' : $value), ENT_QUOTES);
            }
            ),
            array(
            'label'  => 'Site Title:',
            'status' => function ($paramObj) {
                if (DUPX_InstallerState::isRestoreBackup()) {
                    return ParamForm::STATUS_DISABLED;
                } else {
                    return ParamForm::STATUS_ENABLED;
                }
            },
            'wrapperClasses' => array('revalidate-on-change'),
            )
        );

        $params[PrmMng::PARAM_EMAIL_REPLACE] = new ParamForm(
            PrmMng::PARAM_EMAIL_REPLACE,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => 'Email Domains:',
            'checkboxLabel' => 'Update'
            )
        );

        $params[PrmMng::PARAM_FULL_SEARCH] = new ParamForm(
            PrmMng::PARAM_FULL_SEARCH,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => 'Database Search:',
            'checkboxLabel' => 'Full Search Mode'
            )
        );

        $params[PrmMng::PARAM_POSTGUID] = new ParamForm(
            PrmMng::PARAM_POSTGUID,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
            'default' => false
            ),
            array(
            'label'         => 'Post GUID:',
            'checkboxLabel' => 'Keep Unchanged'
            )
        );

        $params[PrmMng::PARAM_MAX_SERIALIZE_CHECK] = new ParamForm(
            PrmMng::PARAM_MAX_SERIALIZE_CHECK,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_NUMBER,
            array(
            'default' => \DUPX_Constants::DEFAULT_MAX_STRLEN_SERIALIZED_CHECK_IN_M
            ),
            array(
            'min'              => 0,
            'max'              => 99,
            'step'             => 1,
            'wrapperClasses'   => array('small'),
            'label'            => 'Serialized obj max size:',
            'postfix'         => array('type' => 'label', 'label' => 'MB'),
            'subNote'          => 'If the serialized object stored in the database exceeds this size, it will not be parsed for replacement.'
            . '<br><b>Too large a size in low memory installations can generate a fatal error.</b>'
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
