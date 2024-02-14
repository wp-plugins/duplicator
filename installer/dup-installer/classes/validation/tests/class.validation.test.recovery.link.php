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

use Duplicator\Installer\Core\Params\PrmMng;

class DUPX_Validation_test_recovery extends DUPX_Validation_abstract_item
{
    protected $importSiteInfo      = array();
    protected $recoveryPage        = false;
    protected $importPage          = false;
    protected $recoveryIsOutToDate = false;
    protected $recoveryPackageLife = -1;

    protected function runTest()
    {
        $paramsManager = PrmMng::getInstance();
        if (!DUPX_InstallerState::isImportFromBackendMode()) {
            return self::LV_SKIP;
        }
        $this->importSiteInfo      = PrmMng::getInstance()->getValue(PrmMng::PARAM_FROM_SITE_IMPORT_INFO);
        $this->importPage          = $this->importSiteInfo['import_page'];
        $this->recoveryPage        = $this->importSiteInfo['recovery_page'];
        $this->recoveryIsOutToDate = $this->importSiteInfo['recovery_is_out_to_date'];
        $this->recoveryPackageLife = $this->importSiteInfo['recovery_package_life'];

        $recoveryLink = $paramsManager->getValue(PrmMng::PARAM_RECOVERY_LINK);
        if (empty($recoveryLink)) {
            return self::LV_HARD_WARNING;
        } else {
            if ($this->importSiteInfo['recovery_is_out_to_date']) {
                return self::LV_SOFT_WARNING;
            } else {
                return self::LV_GOOD;
            }
        }
    }

    public function getTitle()
    {
        return 'Recovery Point';
    }

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/tests/recovery', array(
            'testResult'          => $this->testResult,
            'importPage'          => $this->importPage,
            'recoveryPage'        => $this->recoveryPage,
            'recoveryIsOutToDate' => $this->recoveryIsOutToDate,
            'recoveryPackageLife' => $this->recoveryPackageLife
            ), false);
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/recovery', array(
            'testResult'          => $this->testResult,
            'importPage'          => $this->importPage,
            'recoveryPage'        => $this->recoveryPage,
            'recoveryIsOutToDate' => $this->recoveryIsOutToDate,
            'recoveryPackageLife' => $this->recoveryPackageLife
            ), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/recovery', array(
            'testResult'          => $this->testResult,
            'importPage'          => $this->importPage,
            'recoveryPage'        => $this->recoveryPage,
            'recoveryIsOutToDate' => $this->recoveryIsOutToDate,
            'recoveryPackageLife' => $this->recoveryPackageLife
            ), false);
    }
}
