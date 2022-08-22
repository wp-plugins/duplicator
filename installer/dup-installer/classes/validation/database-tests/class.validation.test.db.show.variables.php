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

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUPX_Validation_test_db_custom_queries extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }

        if (!DUPX_Validation_database_service::getInstance()->isQueryWorking('SHOW VARIABLES LIKE "version"')) {
            return self::LV_FAIL;
        }

        return self::LV_PASS;
    }

    public function getTitle()
    {
        return "Privileges: 'Show Variables' Query";
    }

    protected function failContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-show-variables', array(
            'pass' => false
        ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-show-variables', array(
            'pass' => true
        ), false);
    }
}
