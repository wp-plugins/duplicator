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

class DUPX_Validation_test_db_triggers extends DUPX_Validation_abstract_item
{
    protected $triggers = array();

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }

        $this->triggers = (array)DUPX_ArchiveConfig::getInstance()->dbInfo->triggerList;
        if (count($this->triggers) > 0) {
            return self::LV_SOFT_WARNING;
        }

        return self::LV_PASS;
    }

    public function getTitle()
    {
        return 'Source Database Triggers';
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-triggers', array(
            'isOk'     => true,
            'triggers' => $this->triggers,
        ), false);
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-triggers', array(
            'isOk'     => false,
            'triggers' => $this->triggers,
        ), false);
    }
}
