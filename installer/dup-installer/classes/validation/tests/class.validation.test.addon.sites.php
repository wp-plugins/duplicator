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

class DUPX_Validation_test_addon_sites extends DUPX_Validation_abstract_item
{
    /**
     *
     * @return int
     */
    protected function runTest()
    {
        $list = self::getAddonsListsFolders();

        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ACTION) === DUP_Extraction::ACTION_DO_NOTHING) {
            return self::LV_GOOD;
        }

        if (count($list) > 0) {
            return self::LV_SOFT_WARNING;
        } else {
            return self::LV_GOOD;
        }
    }

    /**
     *
     * @staticvar string[] $addonListFolder
     *
     * @return string[]
     */
    public static function getAddonsListsFolders()
    {
        static $addonListFolder = null;
        if (is_null($addonListFolder)) {
            $addonListFolder = DUPX_Server::getWpAddonsSiteLists();
        }
        return $addonListFolder;
    }

    public function getTitle()
    {
        return 'Addon Sites';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/addon-sites', array(
            'testResult' => $this->testResult,
            'pathsList'  => self::getAddonsListsFolders()
            ), false);
    }

    protected function goodContent()
    {
        return $this->swarnContent();
    }
}
