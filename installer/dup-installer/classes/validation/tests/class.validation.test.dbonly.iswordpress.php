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

class DUPX_Validation_test_dbonly_iswordpress extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (!DUPX_ArchiveConfig::getInstance()->exportOnlyDB) {
            return self::LV_SKIP;
        }
        if (DUPX_Server::isWordPress()) {
            return self::LV_GOOD;
        } else {
            return self::LV_SOFT_WARNING;
        }
    }

    public function getTitle()
    {
        return 'Database Only';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/dbonly-iswordpress', array(), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/dbonly-iswordpress', array(), false);
    }
}
