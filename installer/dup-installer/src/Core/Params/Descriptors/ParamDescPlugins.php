<?php

/**
 * Plugins params descriptions
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
use Duplicator\Installer\Core\Params\Items\ParamFormPlugins;
use Duplicator\Libs\Snap\SnapUtil;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescPlugins implements DescriptorInterface
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
        $params[PrmMng::PARAM_PLUGINS] = new ParamFormPlugins(
            PrmMng::PARAM_PLUGINS,
            ParamFormPlugins::TYPE_ARRAY_STRING,
            ParamFormPlugins::FORM_TYPE_PLUGINS_SELECT,
            array(
                'default'          => array(),
                'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline')
            ),
            array(
                'label'       => 'Plugins',
                'renderLabel' => false,
                'status' => function ($paramObj) {
                    if (
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        return ParamForm::STATUS_DISABLED;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                }
            )
        );

        $params[PrmMng::PARAM_IGNORE_PLUGINS] = new ParamItem(
            PrmMng::PARAM_IGNORE_PLUGINS,
            ParamItem::TYPE_ARRAY_STRING,
            array(
            'default'          => array(),
            'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline'),
            )
        );

        $params[PrmMng::PARAM_FORCE_DIABLE_PLUGINS] = new ParamItem(
            PrmMng::PARAM_FORCE_DIABLE_PLUGINS,
            ParamItem::TYPE_ARRAY_STRING,
            array(
            'default'          => array(),
            'sanitizeCallback' => array('Duplicator\\Libs\\Snap\\SnapUtil', 'sanitizeNSCharsNewline'),
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
