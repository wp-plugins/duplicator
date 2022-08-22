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

class DUPX_Validation_test_db_case_sensitive_tables extends DUPX_Validation_abstract_item
{
    protected $errorMessage        = '';
    protected $lowerCaseTableNames = -1;

    protected function runTest()
    {
        if (
            DUPX_Validation_database_service::getInstance()->skipDatabaseTests() ||
            DUPX_ArchiveConfig::getInstance()->isTablesCaseSensitive() === false
        ) {
            return self::LV_SKIP;
        }

        if (
            ($this->lowerCaseTableNames = DUPX_Validation_database_service::getInstance()->caseSensitiveTablesValue()) !== 0
            && DUPX_ArchiveConfig::getInstance()->dbInfo->isTablesUpperCase
        ) {
            return self::LV_SOFT_WARNING;
        } else {
            return self::LV_PASS;
        }
    }

    public function getTitle()
    {
        return 'Tables Case Sensitivity';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-case-sensitive-tables', array(
            'isOk'                => false,
            'errorMessage'        => $this->errorMessage,
            'lowerCaseTableNames' => $this->lowerCaseTableNames
            ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-case-sensitive-tables', array(
            'isOk'                => true,
            'errorMessage'        => $this->errorMessage,
            'lowerCaseTableNames' => $this->lowerCaseTableNames
            ), false);
    }
}
