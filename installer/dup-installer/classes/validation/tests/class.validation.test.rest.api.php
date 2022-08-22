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

use Duplicator\Installer\Core\Params\PrmMng;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;


class DUPX_Validation_test_rest_api extends DUPX_Validation_abstract_item
{
    protected $errorMessage = '';
    protected $restUrl      = '';

    protected function runTest()
    {
        if (true) {
            // REST API for future feathures
            return self::LV_SKIP;
        }

        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        if (is_array($overwriteData) && isset($overwriteData['restUrl']) && strlen($overwriteData['restUrl']) > 0) {
            $this->restUrl = $overwriteData['restUrl'];
        } else {
            $this->restUrl = PrmMng::getInstance()->getValue(PrmMng::PARAM_URL_NEW) . '/wp-json';
        }


        $this->errorMessage = "REST API call to WordPress backend failed";
        if (DUPX_REST::getInstance()->checkRest(true, $this->errorMessage)) {
            return self::LV_PASS;
        }

        return self::LV_FAIL;
    }

    public function getTitle()
    {
        return 'REST API test';
    }

    protected function passContent()
    {
        return dupxTplRender(
            'parts/validation/tests/rest-api',
            array(
                "isOk"         => true,
                "restUrl"      => $this->restUrl
            ),
            false
        );
    }

    protected function failContent()
    {
        return dupxTplRender(
            'parts/validation/tests/rest-api',
            array(
                "isOk"         => false,
                "errorMessage" => $this->errorMessage,
                "restUrl"      => $this->restUrl
            ),
            false
        );
    }
}
