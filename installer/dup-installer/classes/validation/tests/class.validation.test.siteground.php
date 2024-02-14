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

class DUPX_Validation_test_siteground extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (!DUPX_Custom_Host_Manager::getInstance()->isHosting(DUPX_Custom_Host_Manager::HOST_SITEGROUND)) {
            return self::LV_SKIP;
        }

        return self::LV_SOFT_WARNING;
    }

    public function getTitle()
    {
        return 'Siteground';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/siteground', array(), false);
    }
}
