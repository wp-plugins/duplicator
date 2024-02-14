<?php

/**
 * Validation object
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\LogHandler;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapIO;

class DUPX_Validation_test_wordfence extends DUPX_Validation_abstract_item
{
    private $wordFencePath = "";

    protected function runTest()
    {
        return $this->parentHasWordfence() ? self::LV_HARD_WARNING : self::LV_GOOD;
    }

    public function getTitle()
    {
        return 'Wordfence';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/wordfence/wordfence-detected', array(
            'wordFencePath' => $this->wordFencePath
            ), false);
    }

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/tests/wordfence/wordfence-detected', array(
            'wordFencePath' => $this->wordFencePath
            ), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/wordfence/wordfence-not-detected', array(), false);
    }

    protected function parentHasWordfence()
    {
        $scanPath = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW);
        $rootPath = SnapIO::getMaxAllowedRootOfPath($scanPath);
        $result   = false;

        if ($rootPath === false) {
            //$scanPath is not contained in open_basedir paths skip
            return false;
        }

        LogHandler::setMode(LogHandler::MODE_OFF);
        $continueScan = true;
        while ($continueScan) {
            if ($this->wordFenceFirewallEnabled($scanPath)) {
                $this->wordFencePath = $scanPath;
                $result              = true;
                break;
            }
            $continueScan = $scanPath !== $rootPath && $scanPath != dirname($scanPath);
            $scanPath     = dirname($scanPath);
        }
        LogHandler::setMode();

        return $result;
    }

    protected function wordFenceFirewallEnabled($path)
    {
        $configFiles = array(
            'php.ini',
            '.user.ini',
            '.htaccess'
        );

        foreach ($configFiles as $configFile) {
            $file = $path . '/' . $configFile;

            if (!@is_readable($file)) {
                continue;
            }

            if (($content = @file_get_contents($file)) === false) {
                continue;
            }

            if (strpos($content, 'wordfence-waf.php') !== false) {
                return true;
            }
        }

        return false;
    }
}
