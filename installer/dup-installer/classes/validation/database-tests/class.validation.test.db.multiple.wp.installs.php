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
use Duplicator\Libs\Snap\SnapWP;

class DUPX_Validation_test_db_multiple_wp_installs extends DUPX_Validation_abstract_item
{
    /**
     * @var string[] unique wp prefixes in the DB
     */
    protected $uniquePrefixes = array();

    /**
     * Check mutiple db install in database
     *
     * @return int
     */
    protected function runTest()
    {
        $dbAction = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_ACTION);
        if (
            DUPX_Validation_database_service::getInstance()->skipDatabaseTests()
            || $dbAction === DUPX_DBInstall::DBACTION_MANUAL
            || $dbAction === DUPX_DBInstall::DBACTION_CREATE
        ) {
            return self::LV_SKIP;
        }

        if (DUPX_Validation_database_service::getInstance()->dbTablesCount() === 0) {
            return self::LV_PASS;
        }

        $affectedTables       = DUPX_Validation_database_service::getInstance()->getDBActionAffectedTables($dbAction);
        $this->uniquePrefixes = SnapWP::getUniqueWPTablePrefixes($affectedTables);

        if (count($this->uniquePrefixes) > 1) {
            return self::LV_SOFT_WARNING;
        }

        return self::LV_PASS;
    }

    /**
     * Get test title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Multiple WordPress Installs';
    }

    /**
     * Return content for test status: soft warning
     *
     * @return string
     */
    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-multiple-wp-installs', array(
            'isOk'          => false,
            'uniquePrefixes' => $this->uniquePrefixes
        ), false);
    }

    /**
     * Return content for test status: pass
     *
     * @return string
     */
    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-multiple-wp-installs', array(
            'isOk'          => true,
            'uniquePrefixes' => $this->uniquePrefixes
        ), false);
    }
}
