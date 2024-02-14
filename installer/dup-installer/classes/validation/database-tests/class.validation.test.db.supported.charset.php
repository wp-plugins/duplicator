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

class DUPX_Validation_test_db_supported_charset extends DUPX_Validation_abstract_item
{
    protected $errorMessage      = '';
    protected $charsetsList      = array();
    protected $collationsList    = array();
    protected $invalidCharsets   = array();
    protected $invalidCollations = array();
    protected $extraData         = array();

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }

        try {
            $archiveConfig = DUPX_ArchiveConfig::getInstance();

            $this->charsetsList      = $archiveConfig->dbInfo->charSetList;
            $this->collationsList    = $archiveConfig->dbInfo->collationList;
            $this->invalidCharsets   = $archiveConfig->invalidCharsets();
            $this->invalidCollations = $archiveConfig->invalidCollations();

            if (empty($this->invalidCharsets) && empty($this->invalidCollations)) {
                return self::LV_PASS;
            } else {
                return self::LV_HARD_WARNING;
            }
        } catch (Exception $e) {
            $this->errorMessage = $e->getMessage();
            return self::LV_FAIL;
        }
    }

    public function getTitle()
    {
        return 'Character Set and  Collation Capability';
    }

    protected function failContent()
    {
        $dbFuncs = DUPX_DB_Functions::getInstance();

        return dupxTplRender('parts/validation/database-tests/db-supported-charset', array(
            'testResult'        => $this->testResult,
            'extraData'         => $this->extraData,
            'charsetsList'      => $this->charsetsList,
            'collationsList'    => $this->collationsList,
            'invalidCharsets'   => $this->invalidCharsets,
            'invalidCollations' => $this->invalidCollations,
            'usedCharset'       => $dbFuncs->getRealCharsetByParam(),
            'usedCollate'       => $dbFuncs->getRealCollateByParam(),
            'errorMessage'      => $this->errorMessage
            ), false);
    }

    protected function hwarnContent()
    {
        return $this->failContent();
    }

    protected function passContent()
    {
        return $this->failContent();
    }
}
