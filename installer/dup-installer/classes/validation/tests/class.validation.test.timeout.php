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

class DUPX_Validation_test_timeout extends DUPX_Validation_abstract_item
{
    const MAX_TIME_SIZE = 314572800;  //300MB

    protected $maxTimeZero = false;

    protected function runTest()
    {
        $max_time_ini      = ini_get('max_execution_time');
        $this->maxTimeZero = ($GLOBALS['DUPX_ENFORCE_PHP_INI']) ? false : @set_time_limit(0);

        if ((is_numeric($max_time_ini) && $max_time_ini < 31 && $max_time_ini > 0) && DUPX_Conf_Utils::archiveSize() > self::MAX_TIME_SIZE) {
            return self::LV_SOFT_WARNING;
        } else {
            return self::LV_GOOD;
        }
    }

    public function getTitle()
    {
        return 'PHP Timeout';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/timeout', array(
            'maxTimeZero' => $this->maxTimeZero,
            'maxTimeIni'  => ini_get('max_execution_time'),
            'archiveSize' => DUPX_U::readableByteSize(DUPX_Conf_Utils::archiveSize()),
            'maxSize'     => DUPX_U::readableByteSize(self::MAX_TIME_SIZE),
            'isOk'        => true
            ), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/timeout', array(
            'maxTimeZero' => $this->maxTimeZero,
            'maxTimeIni'  => ini_get('max_execution_time'),
            'archiveSize' => DUPX_U::readableByteSize(DUPX_Conf_Utils::archiveSize()),
            'maxSize'     => DUPX_U::readableByteSize(self::MAX_TIME_SIZE),
            'isOk'        => true
            ), false);
    }
}
