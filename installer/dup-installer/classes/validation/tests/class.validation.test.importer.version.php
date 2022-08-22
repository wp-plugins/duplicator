<?php

/**
 * Validation object
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapUtil;

class DUPX_Validation_test_importer_version extends DUPX_Validation_abstract_item
{
    /**
     *
     * @return int
     */
    protected function runTest()
    {

        if (!DUPX_InstallerState::isImportFromBackendMode()) {
            return self::LV_SKIP;
        }

        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        if (SnapUtil::versionCompare($overwriteData['dupVersion'], DUPX_VERSION, '<', 3)) {
            return self::LV_FAIL;
        }

        return self::LV_PASS;
    }

    /**
     * Return test ticekt
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Duplicator importer version';
    }

    protected function failContent()
    {
        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        return dupxTplRender('parts/validation/tests/importer-version', array(
            'testResult'  => $this->testResult,
            'importerVer' => ($overwriteData['dupVersion'] == '0' ? 'Unknown' : $overwriteData['dupVersion'])
        ), false);
    }

    protected function passContent()
    {
        return $this->failContent();
    }
}
