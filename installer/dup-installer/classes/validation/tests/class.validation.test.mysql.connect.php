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

class DUPX_Validation_test_mysql_connect extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (function_exists('mysqli_connect')) {
            return self::LV_PASS;
        } else {
            return self::LV_FAIL;
        }
    }

    public function getTitle()
    {
        return 'PHP Mysqli';
    }

    protected function failContent()
    {
        return dupxTplRender('parts/validation/tests/mysql-connect', array(), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/tests/mysql-connect', array(), false);
    }
}
