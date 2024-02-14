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

class DUPX_Validation_test_managed_tprefix extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (!DUPX_Custom_Host_Manager::getInstance()->isManaged()) {
            return self::LV_SKIP;
        }

        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        if (DUPX_ArchiveConfig::getInstance()->wp_tableprefix != $overwriteData['table_prefix']) {
            return self::LV_SOFT_WARNING;
        } else {
            return self::LV_GOOD;
        }
    }

    public function getTitle()
    {
        return 'Table prefix of managed hosting';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/managed-tprefix', array(
            'isOk' => false
            ), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/managed-tprefix', array(
            'isOk' => true
            ), false);
    }
}
