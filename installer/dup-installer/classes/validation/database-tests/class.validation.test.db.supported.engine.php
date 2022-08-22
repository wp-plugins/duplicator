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

class DUPX_Validation_test_db_supported_engine extends DUPX_Validation_abstract_item
{
    protected $errorMessage   = '';
    protected $invalidEngines = array();
    protected $defaultEngine  = "";

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }

        try {
            $this->invalidEngines = DUPX_ArchiveConfig::getInstance()->invalidEngines();
            $this->defaultEngine  = DUPX_DB_Functions::getInstance()->getDefaultEngine();

            if (empty($this->invalidEngines)) {
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
        return 'Database Engine Support';
    }

    protected function failContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-supported-engine', array(
            'testResult'     => $this->testResult,
            'invalidEngines' => $this->invalidEngines,
            'defaultEngine'  => $this->defaultEngine,
            'errorMessage'   => $this->errorMessage
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
