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

class DUPX_Validation_test_package_size extends DUPX_Validation_abstract_item
{
    const PACKAGE_SIZE_BEFORE_WARNING_MB = 500;

    private $packageSize = 0;

    protected function runTest()
    {
        $this->packageSize = DUPX_Conf_Utils::archiveSize();
        if ($this->packageSize <= self::PACKAGE_SIZE_BEFORE_WARNING_MB * 1024 * 1024) {
            return self::LV_GOOD;
        } else {
            return self::LV_SOFT_WARNING;
        }
    }

    public function getTitle()
    {
        return 'Package Size';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/package-size', array(
            'packageSize'    => DUPX_U::readableByteSize($this->packageSize),
            'maxPackageSize' => self::PACKAGE_SIZE_BEFORE_WARNING_MB
        ), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/package-size', array(
            'packageSize'    => DUPX_U::readableByteSize($this->packageSize),
            'maxPackageSize' => self::PACKAGE_SIZE_BEFORE_WARNING_MB
        ), false);
    }
}
