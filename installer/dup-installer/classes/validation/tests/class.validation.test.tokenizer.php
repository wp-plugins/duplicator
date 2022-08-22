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

use Duplicator\Installer\Core\Params\PrmMng;

class DUPX_Validation_test_tokenizer extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (function_exists('token_get_all')) {
            return self::LV_PASS;
        } else {
            PrmMng::getInstance()->setValue(PrmMng::PARAM_WP_CONFIG, 'nothing');
            PrmMng::getInstance()->save();
            return self::LV_HARD_WARNING;
        }
    }

    public function getTitle()
    {
        return 'PHP Tokenizer';
    }

    protected function hwarnContent()
    {
        return dupxTplRender('parts/validation/tests/tokenizer', array(
            'isOk' => false
        ), false);
    }

    protected function passContent()
    {
        return dupxTplRender('parts/validation/tests/tokenizer', array(
            'isOk' => true
        ), false);
    }
}
