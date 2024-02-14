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
use Duplicator\Libs\Snap\SnapIO;

class DUPX_Validation_test_db_host_name extends DUPX_Validation_abstract_item
{
    protected $fixedHost = '';

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }
        DUPX_Validation_database_service::getInstance()->setSkipOtherTests(true);

        $host = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_HOST);
        //Host check
        $parsed_host_info = DUPX_DB::parseDBHost($host);
        $parsed_host      = $parsed_host_info[0];
        $isInvalidHost    = $parsed_host == 'http' || $parsed_host == "https";

        if ($isInvalidHost) {
            $this->fixedHost = SnapIO::untrailingslashit(str_replace($parsed_host . "://", "", $host));
            return self::LV_FAIL;
        } else {
            DUPX_Validation_database_service::getInstance()->setSkipOtherTests(false);
            return self::LV_PASS;
        }
    }

    public function getTitle()
    {
        return 'Host Name';
    }

    protected function failContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-host-name', array(
            'isOk'      => false,
            'host'      => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_HOST),
            'fixedHost' => $this->fixedHost
            ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-host-name', array(
            'isOk'      => true,
            'host'      => PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_HOST),
            'fixedHost' => ''
            ), false);
    }
}
