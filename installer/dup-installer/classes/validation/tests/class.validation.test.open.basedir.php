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

use Duplicator\Libs\Snap\SnapIO;

class DUPX_Validation_test_open_basedir extends DUPX_Validation_abstract_item
{
    private $openBaseDirEnabled      = false;
    private $pathsOutsideOpenBaseDir = array();

    protected function runTest()
    {
        if (($this->openBaseDirEnabled = SnapIO::isOpenBaseDirEnabled()) === false) {
            return self::LV_GOOD;
        }

        $archivePaths = array();
        $pathMapping  = DUPX_ArchiveConfig::getInstance()->getPathsMapping();
        if (is_array($pathMapping)) {
            $archivePaths = $pathMapping;
        } else {
            $archivePaths[] = $pathMapping;
        }

        foreach ($archivePaths as $archivePath) {
            if (SnapIO::getOpenBaseDirRootOfPath($archivePath) === false) {
                $this->pathsOutsideOpenBaseDir[] = $archivePath;
            }
        }

        if (empty($this->pathsOutsideOpenBaseDir)) {
            return self::LV_GOOD;
        } else {
            return self::LV_HARD_WARNING;
        }
    }

    public function getTitle()
    {
        return 'PHP Open Base';
    }

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/tests/open-basedir', array(
            'openBaseDirEnabled'      => $this->openBaseDirEnabled,
            'pathsOutsideOpenBaseDir' => $this->pathsOutsideOpenBaseDir,
            'isOk'                    => false
        ), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/open-basedir', array(
            'openBaseDirEnabled'      => $this->openBaseDirEnabled,
            'pathsOutsideOpenBaseDir' => $this->pathsOutsideOpenBaseDir,
            'isOk'                    => true
        ), false);
    }
}
