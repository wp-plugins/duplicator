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

class DUPX_Validation_test_db_gtid_mode extends DUPX_Validation_abstract_item
{
    protected $errorMessage = '';

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }

        if (DUPX_Validation_database_service::getInstance()->dbGtidModeEnabled($this->errorMessage)) {
            return self::LV_SOFT_WARNING;
        } else {
            return self::LV_PASS;
        }
    }

    public function getTitle()
    {
        return 'Database GTID Mode';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-gtid-mode', array(
            'isOk'         => false,
            'errorMessage' => $this->errorMessage
            ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-gtid-mode', array(
            'isOk'         => true,
            'errorMessage' => $this->errorMessage
            ), false);
    }
}
