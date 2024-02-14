<?php

/**
 * Main params descriptions
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Core\Params\Descriptors;

use Duplicator\Installer\Core\Hooks\HooksMng;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Libs\Snap\SnapIO;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamsDescriptors
{
    /**
     * Params init
     *
     * @return void
     */
    public static function init()
    {
        HooksMng::getInstance()->addAction('after_params_overwrite', array(__CLASS__, 'updateParamsAfterOverwrite'));
    }

    /**
     * Init params
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function initParams(&$params)
    {
        ParamDescUrlsPaths::init($params);
        ParamDescController::init($params);
        ParamDescSecurity::init($params);
        ParamDescGeneric::init($params);
        ParamDescConfigs::init($params);
        ParamDescEngines::init($params);
        ParamDescValidation::init($params);
        ParamDescDatabase::init($params);
        ParamDescReplace::init($params);
        ParamDescPlugins::init($params);
        ParamDescUsers::init($params);
        ParamDescNewAdmin::init($params);
        ParamDescWpConfig::init($params);
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
        Log::info('UPDATE PARAMS AFTER OVERWRITE', Log::LV_DETAILED);
        ParamDescUrlsPaths::updateParamsAfterOverwrite($params);
        ParamDescController::updateParamsAfterOverwrite($params);
        ParamDescSecurity::updateParamsAfterOverwrite($params);
        ParamDescGeneric::updateParamsAfterOverwrite($params);
        ParamDescConfigs::updateParamsAfterOverwrite($params);
        ParamDescEngines::updateParamsAfterOverwrite($params);
        ParamDescValidation::updateParamsAfterOverwrite($params);
        ParamDescDatabase::updateParamsAfterOverwrite($params);
        ParamDescReplace::updateParamsAfterOverwrite($params);
        ParamDescPlugins::updateParamsAfterOverwrite($params);
        ParamDescUsers::updateParamsAfterOverwrite($params);
        ParamDescNewAdmin::updateParamsAfterOverwrite($params);
        ParamDescWpConfig::updateParamsAfterOverwrite($params);
    }

    /**
     * Validate function, return true if value isn't empty
     *
     * @param mixed     $value    input value
     * @param ParamItem $paramObj current param object
     *
     * @return boolean
     */
    public static function validateNotEmpty($value, ParamItem $paramObj)
    {
        if (is_string($value)) {
            $result = strlen($value) > 0;
        } else {
            $result = !empty($value);
        }

        if ($result == false) {
            $paramObj->setInvalidMessage('Can\'t be empty');
        }

        return true;
    }

    /**
     * Sanitize path
     *
     * @param string $value input value
     *
     * @return string
     */
    public static function sanitizePath($value)
    {
        $result = SnapUtil::sanitizeNSCharsNewlineTrim($value);
        return SnapIO::safePathUntrailingslashit($result);
    }

    /**
     * The path can't be empty
     *
     * @param string    $value    input value
     * @param ParamItem $paramObj current param object
     *
     * @return bool
     */
    public static function validatePath($value, ParamItem $paramObj)
    {
        if (strlen($value) > 1) {
            return true;
        } else {
            $paramObj->setInvalidMessage('Path can\'t empty');
            return false;
        }
    }

    /**
     * Sanitize URL
     *
     * @param string $value input value
     *
     * @return string
     */
    public static function sanitizeUrl($value)
    {
        $result = SnapUtil::sanitizeNSCharsNewlineTrim($value);
        if (empty($value)) {
            return '';
        }
        // if scheme not set add http by default
        if (!preg_match('/^[a-zA-Z]+\:\/\//', $result)) {
            $result = 'http://' . ltrim($result, '/');
        }
        return rtrim($result, '/\\');
    }

    /**
     * The URL can't be empty
     *
     * @param string    $value    input value
     * @param ParamItem $paramObj current param object
     *
     * @return bool
     */
    public static function validateUrlWithScheme($value, ParamItem $paramObj)
    {
        if (strlen($value) == 0) {
            $paramObj->setInvalidMessage('URL can\'t be empty');
            return false;
        }
        if (($parsed = parse_url($value)) === false) {
            $paramObj->setInvalidMessage('URL isn\'t valid');
            return false;
        }
        if (!isset($parsed['host']) || empty($parsed['host'])) {
            $paramObj->setInvalidMessage('URL must be a valid host');
            return false;
        }
        return true;
    }
}
