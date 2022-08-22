<?php

/**
 * Validation params descriptions
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

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescValidation implements DescriptorInterface
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
        $params[PrmMng::PARAM_VALIDATION_LEVEL] = new ParamItem(
            PrmMng::PARAM_VALIDATION_LEVEL,
            ParamItem::TYPE_INT,
            array(
                'default'      => \DUPX_Validation_abstract_item::LV_FAIL,
                'acceptValues' => array(
                    \DUPX_Validation_abstract_item::LV_FAIL,
                    \DUPX_Validation_abstract_item::LV_HARD_WARNING,
                    \DUPX_Validation_abstract_item::LV_SOFT_WARNING,
                    \DUPX_Validation_abstract_item::LV_GOOD,
                    \DUPX_Validation_abstract_item::LV_PASS
                )
            )
        );

        $params[PrmMng::PARAM_VALIDATION_ACTION_ON_START] = new ParamItem(
            PrmMng::PARAM_VALIDATION_ACTION_ON_START,
            ParamForm::TYPE_STRING,
            array(
                'default'      => \DUPX_Validation_manager::ACTION_ON_START_NORMAL,
                'acceptValues' => array(
                    \DUPX_Validation_manager::ACTION_ON_START_NORMAL,
                    \DUPX_Validation_manager::ACTION_ON_START_AUTO
                )
            )
        );

        $params[PrmMng::PARAM_VALIDATION_SHOW_ALL] = new ParamForm(
            PrmMng::PARAM_VALIDATION_SHOW_ALL,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_SWITCH,
            array(
                'default' => false
            ),
            array(
                'label'          => 'Show all',
                'wrapperClasses' => 'align-right'
            )
        );

        $params[PrmMng::PARAM_ACCEPT_TERM_COND] = new ParamForm(
            PrmMng::PARAM_ACCEPT_TERM_COND,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => false
            ),
            array(
                'label'         => 'Accept term and conditions',
                'renderLabel'   => false,
                'checkboxLabel' => 'I have read and accept all <a href="#" onclick="DUPX.viewTerms()" >terms &amp; notices</a>*',
                'subNote'       => '<div class="required-txt">* required to continue</div>',
                'attr'          => array(
                    'onclick' => 'DUPX.acceptWarning();'
                )
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
