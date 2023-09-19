<?php

/**
 * Controller params descriptions
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
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescController implements DescriptorInterface
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
        $params[PrmMng::PARAM_FINAL_REPORT_DATA] = new ParamItem(
            PrmMng::PARAM_FINAL_REPORT_DATA,
            ParamItem::TYPE_ARRAY_MIXED,
            array(
            'default' => array(
                'extraction' => array(
                    'table_count' => 0,
                    'table_rows'  => 0,
                    'query_errs'  => 0,
                ),
                'replace'    => array(
                    'scan_tables' => 0,
                    'scan_rows'   => 0,
                    'scan_cells'  => 0,
                    'updt_tables' => 0,
                    'updt_rows'   => 0,
                    'updt_cells'  => 0,
                    'errsql'      => 0,
                    'errser'      => 0,
                    'errkey'      => 0,
                    'errsql_sum'  => 0,
                    'errser_sum'  => 0,
                    'errkey_sum'  => 0,
                    'err_all'     => 0,
                    'warn_all'    => 0,
                    'warnlist'    => array()
                )
            )
            )
        );

        $params[PrmMng::PARAM_INSTALLER_MODE] = new ParamItem(
            PrmMng::PARAM_INSTALLER_MODE,
            ParamItem::TYPE_INT,
            array(
            'default'      => \DUPX_InstallerState::MODE_UNKNOWN,
            'acceptValues' => array(
                \DUPX_InstallerState::MODE_UNKNOWN,
                \DUPX_InstallerState::MODE_STD_INSTALL,
                \DUPX_InstallerState::MODE_OVR_INSTALL
            )
            )
        );

        $params[PrmMng::PARAM_OVERWRITE_SITE_DATA] = new ParamItem(
            PrmMng::PARAM_OVERWRITE_SITE_DATA,
            ParamItem::TYPE_ARRAY_MIXED,
            array(
                'default' => DUPX_InstallerState::overwriteDataDefault()
            )
        );


        $params[PrmMng::PARAM_DEBUG] = new ParamItem(
            PrmMng::PARAM_DEBUG,
            ParamItem::TYPE_BOOL,
            array(
            'persistence' => true,
            'default'     => false
            )
        );

        $params[PrmMng::PARAM_DEBUG_PARAMS] = new ParamItem(
            PrmMng::PARAM_DEBUG_PARAMS,
            ParamItem::TYPE_BOOL,
            array(
            'persistence' => true,
            'default'     => false
            )
        );

        $params[PrmMng::PARAM_CTRL_ACTION] = new ParamItem(
            PrmMng::PARAM_CTRL_ACTION,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_HIDDEN,
            array(
            'persistence'  => false,
            'default'      => '',
            'acceptValues' => array(
                '',
                'ajax',
                'secure',
                'ctrl-step1',
                'ctrl-step2',
                'ctrl-step3',
                'ctrl-step4',
                'help'
            ))
        );

        $params[PrmMng::PARAM_STEP_ACTION] = new ParamItem(
            PrmMng::PARAM_STEP_ACTION,
            ParamForm::TYPE_STRING,
            array(
            'persistence'  => false,
            'default'      => '',
            'acceptValues' => array(
                '',
                \DUPX_CTRL::ACTION_STEP_INIZIALIZED,
                \DUPX_CTRL::ACTION_STEP_ON_VALIDATE,
                \DUPX_CTRL::ACTION_STEP_SET_TEMPLATE
            ))
        );

        $params[\DUPX_Security::CTRL_TOKEN] = new ParamItem(
            \DUPX_Security::CTRL_TOKEN,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_HIDDEN,
            array(
            'persistence'      => false,
            'default'          => null,
            'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline')
            )
        );

        $params[PrmMng::PARAM_ROUTER_ACTION] = new ParamItem(
            PrmMng::PARAM_ROUTER_ACTION,
            ParamItem::TYPE_STRING,
            array(
            'persistence'  => false,
            'default'      => 'router',
            'acceptValues' => array(
                'router'
            ))
        );

        $params[PrmMng::PARAM_TEMPLATE] = new ParamItem(
            PrmMng::PARAM_TEMPLATE,
            ParamForm::TYPE_STRING,
            array(
            'default'      => \DUPX_Template::TEMPLATE_BASE,
            'acceptValues' => array(
                \DUPX_Template::TEMPLATE_BASE,
                \DUPX_Template::TEMPLATE_ADVANCED,
                \DUPX_Template::TEMPLATE_IMPORT_BASE,
                \DUPX_Template::TEMPLATE_IMPORT_ADVANCED
            ))
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
