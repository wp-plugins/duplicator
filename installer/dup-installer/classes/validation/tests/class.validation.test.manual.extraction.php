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

class DUPX_Validation_test_manual_extraction extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (DUPX_Conf_Utils::isManualExtractFilePresent()) {
            return self::LV_SOFT_WARNING;
        } else {
            return self::LV_GOOD;
        }
    }

    public function getTitle()
    {
        return 'Manual extraction detected';
    }

    public function display()
    {
        if ($this->testResult === self::LV_SKIP) {
            return false;
        } else {
            return DUPX_Conf_Utils::isManualExtractFilePresent();
        }
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/manual-extraction', array(), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/manual-extraction', array(), false);
    }
}
