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

use Duplicator\Installer\Core\Params\PrmMng;

class DUPX_Validation_test_db_cleanup extends DUPX_Validation_abstract_item
{
    protected $errorMessage = '';

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->isDatabaseCreated() === false) {
            return self::LV_SKIP;
        }

        if (DUPX_Validation_database_service::getInstance()->cleanUpDatabase($this->errorMessage)) {
            return self::LV_PASS;
        } else {
            return self::LV_HARD_WARNING;
        }
    }

    public function getTitle()
    {
        return 'Database cleanup';
    }

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-cleanup', array(
            'isOk'         => false,
            'dbname'       => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME),
            'isCpanel'     => false,
            'errorMessage' => $this->errorMessage
            ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-cleanup', array(
            'isOk'         => true,
            'dbname'       => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME),
            'isCpanel'     => false,
            'errorMessage' => $this->errorMessage
            ), false);
    }
}
