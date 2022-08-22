<?php

/**
 * plugin custom actions
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Params\PrmMng;

require_once(DUPX_INIT . '/classes/tests/interface.test.php');
require_once(DUPX_INIT . '/classes/tests/class.error.handler.script.exec.php');

class DUPX_test_wordpress_exec implements DUPX_interface_test
{
    const SCRIPT_NAME_HTTP_PARAM = 'dpro_test_script_name';

    /**
     *
     * @return boolean
     */
    public static function preTestPrepare()
    {
        $nManager       = DUPX_NOTICE_MANAGER::getInstance();
        $scriptFilePath = self::getScriptTestPath();
        Log::info('PREPARE FILE BEFORE TEST: ' . $scriptFilePath, Log::LV_DETAILED);
        if (file_put_contents($scriptFilePath, self::getExecFileContent()) === false) {
            $nManager->addFinalReportNotice(array(
                'shortMsg'    => 'Can\'t create final text script file',
                'longMsg'     => 'Can\'t create file ' . $scriptFilePath,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                'sections'    => array('general'),
            ));

            return false;
        }

        return true;
    }

    public static function afterTestClean()
    {
        $nManager       = DUPX_NOTICE_MANAGER::getInstance();
        $scriptFilePath = self::getScriptTestPath();
        Log::info('DELETE FILE AFTER TEST: ' . $scriptFilePath, Log::LV_DETAILED);
        if (file_exists($scriptFilePath)) {
            if (unlink($scriptFilePath) == false) {
                $nManager->addFinalReportNotice(array(
                    'shortMsg'    => 'Can\'t deleta final text script file',
                    'longMsg'     => 'Can\'t delete file ' . $scriptFilePath . '. Remove it manually',
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
                    'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
                    'sections'    => array('general'),
                ));
            }
        }

        return true;
    }

    public static function getFrontendUrl()
    {
        $indexPath = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW) . '/index.php';
        $data      = array(
            DUPX_test_wordpress_exec::SCRIPT_NAME_HTTP_PARAM => $indexPath
        );

        return self::getScriptTestUrl() . '?' . http_build_query($data);
    }

    public static function getBackendUrl()
    {
        $indexPath = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW) . '/wp-login.php';
        $data      = array(
            DUPX_test_wordpress_exec::SCRIPT_NAME_HTTP_PARAM => $indexPath
        );

        return self::getScriptTestUrl() . '?' . http_build_query($data);
    }

    protected static function getScriptTestName()
    {
        return 'wp_test_script_' . DUPX_Security::getInstance()->getSecondaryPackageHash() . '.php';
    }

    public static function getScriptTestPath()
    {
        // use wp-content path and not root path
        return PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_CONTENT_NEW) . '/' . self::getScriptTestName();
    }

    public static function getScriptTestUrl()
    {
        // use wp-content path and not root path
        return PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_CONTENT_NEW) . '/' . self::getScriptTestName();
    }

    public static function getExecFileContent()
    {
        $result = file_get_contents(dirname(__FILE__) . '/file.test.template.php');
        $reuslt = preg_replace('/^.*\[REMOVE LINE BY SCRIPT].*\n/m', '', $result);  // remove first line with die
        $reuslt = str_replace(
            array(
                '$_$_NOTICES_FILE_PATH_$_$',
                '$_$_DUPX_INIT_$_$'
            ),
            array(
                $GLOBALS["NOTICES_FILE_PATH"],
                DUPX_INIT
            ),
            $reuslt
        );
        return $reuslt;
    }
}
