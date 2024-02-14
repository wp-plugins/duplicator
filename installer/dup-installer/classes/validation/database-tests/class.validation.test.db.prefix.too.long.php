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

class DUPX_Validation_test_db_prefix_too_long extends DUPX_Validation_abstract_item
{
    protected $errorMessage = '';

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }

        DUPX_Validation_database_service::getInstance()->setSkipOtherTests(true);
        if (DUPX_Validation_database_service::getInstance()->checkDbPrefixTooLong($this->errorMessage)) {
            DUPX_Validation_database_service::getInstance()->setSkipOtherTests(false);
            return self::LV_PASS;
        } else {
            return self::LV_FAIL;
        }
    }

    public function getTitle()
    {
        return 'Prefix too long';
    }

    protected function failContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-prefix-too-long', array(
            'isOk'         => false,
            'errorMessage' => $this->errorMessage,
            'tooLongNewTableNames' => DUPX_Validation_database_service::getInstance()->getTooLongNewTableNames()
            ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-prefix-too-long', array(
            'isOk'         => true,
            'errorMessage' => $this->errorMessage,
            'tooLongNewTableNames' => DUPX_Validation_database_service::getInstance()->getTooLongNewTableNames()
            ), false);
    }
}
