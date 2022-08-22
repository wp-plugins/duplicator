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

class DUPX_Validation_test_db_manual_tabels_count extends DUPX_Validation_abstract_item
{
    const MIN_TABLES_NUM = 10;

    protected $errorMessage = '';
    protected $numTables    = 0;

    protected function runTest()
    {
        if (
            DUPX_Validation_database_service::getInstance()->skipDatabaseTests() ||
            PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_ACTION) !== DUPX_DBInstall::DBACTION_MANUAL
        ) {
            return self::LV_SKIP;
        }

        $this->numTables = DUPX_Validation_database_service::getInstance()->dbTablesCount($this->errorMessage);

        if ($this->numTables >= self::MIN_TABLES_NUM) {
            return self::LV_PASS;
        } else {
            return self::LV_HARD_WARNING;
        }
    }

    public function getTitle()
    {
        return 'Manual Table Check';
    }

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-manual-tables-count', array(
            'isOk'         => false,
            'dbname'       => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME),
            'numTables'    => $this->numTables,
            'minNumTables' > self::MIN_TABLES_NUM,
            'errorMessage' => $this->errorMessage
            ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-manual-tables-count', array(
            'isOk'         => true,
            'dbname'       => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME),
            'numTables'    => $this->numTables,
            'minNumTables' > self::MIN_TABLES_NUM,
            'errorMessage' => $this->errorMessage
            ), false);
    }
}
