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

use Duplicator\Libs\Snap\SnapUtil;

class DUPX_Validation_test_db_user_resources extends DUPX_Validation_abstract_item
{
    private $userResources             = array();
    private $userHasRestrictedResource = false;

    protected function runTest()
    {
        if (DUPX_Validation_database_service::getInstance()->skipDatabaseTests()) {
            return self::LV_SKIP;
        }

        if (($this->userResources = DUPX_Validation_database_service::getInstance()->getUserResources()) !== false) {
            $this->userHasRestrictedResource = SnapUtil::inArrayExtended($this->userResources, function ($value) {
                return $value > 0;
            });
        }

        if ($this->userHasRestrictedResource) {
            return self::LV_SOFT_WARNING;
        }

        return self::LV_PASS;
    }

    public function getTitle()
    {
        return 'Privileges: User Resources';
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-user-resources', array(
            'isOk'          => !$this->userHasRestrictedResource,
            'userResources' => $this->userResources,
        ), false);
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/database-tests/db-user-resources', array(
            'isOk'          => !$this->userHasRestrictedResource,
            'userResources' => $this->userResources,
        ), false);
    }
}
