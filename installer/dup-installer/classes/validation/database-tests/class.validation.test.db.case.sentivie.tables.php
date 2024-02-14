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

class DUPX_Validation_test_db_case_sensitive_tables extends DUPX_Validation_abstract_item
{
    /** @var string */
    protected $errorMessage = '';

    /** @var int<-1, max> */
    protected $lowerCaseTableNames = -1;

    /** @var int<0, max> */
    protected $lowerCaseTableNamesSource = 0;

    /** @var array<string[]> */
    protected $duplicateTables = array();

    /** @var string[] */
    protected $redundantTables = array();

    protected function runTest()
    {
        $archiveConfig             = DUPX_ArchiveConfig::getInstance();
        $caseSensitiveTablePresent = $archiveConfig->isTablesCaseSensitive();
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests() || !$caseSensitiveTablePresent) {
            return self::LV_SKIP;
        }

        $this->duplicateTables           = DUPX_ArchiveConfig::getInstance()->getDuplicateTableNames();
        $this->redundantTables           = DUPX_ArchiveConfig::getInstance()->getRedundantDuplicateTableNames();
        $this->lowerCaseTableNames       = DUPX_Validation_database_service::getInstance()->caseSensitiveTablesValue();
        $this->lowerCaseTableNamesSource = $archiveConfig->dbInfo->lowerCaseTableNames;
        $destIsCaseInsensitive           = $this->lowerCaseTableNames !== 0;
        $sourceIsCaseSensitive           = $this->lowerCaseTableNamesSource === 0;

        if ($destIsCaseInsensitive && $sourceIsCaseSensitive && count($this->duplicateTables) > 0) {
            return self::LV_HARD_WARNING;
        }

        if ($destIsCaseInsensitive) {
            return self::LV_SOFT_WARNING;
        }

        return self::LV_PASS;
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

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-case-sensitive-duplicates', array(
            'lowerCaseTableNames' => $this->lowerCaseTableNames,
            'duplicateTableNames' => $this->duplicateTables,
            'reduntantTableNames' => $this->redundantTables
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
