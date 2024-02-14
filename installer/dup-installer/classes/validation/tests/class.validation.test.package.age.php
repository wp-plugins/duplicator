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

class DUPX_Validation_test_package_age extends DUPX_Validation_abstract_item
{
    const PACKAGE_DAYS_BEFORE_WARNING = 180;

    protected function runTest()
    {
        if ($this->getPackageDays() <= self::PACKAGE_DAYS_BEFORE_WARNING) {
            return self::LV_GOOD;
        } else {
            return self::LV_SOFT_WARNING;
        }
    }

    protected function getPackageDays()
    {
        return round((time() - strtotime(DUPX_ArchiveConfig::getInstance()->created)) / 86400);
    }

    public function getTitle()
    {
        return 'Package Age';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/package-age', array(
            'packageDays'    => $this->getPackageDays(),
            'maxPackageDays' => self::PACKAGE_DAYS_BEFORE_WARNING
            ), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/package-age', array(
            'packageDays'    => $this->getPackageDays(),
            'maxPackageDays' => self::PACKAGE_DAYS_BEFORE_WARNING
            ), false);
    }
}
