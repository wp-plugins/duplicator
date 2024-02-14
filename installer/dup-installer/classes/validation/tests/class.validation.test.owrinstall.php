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

class DUPX_Validation_test_owrinstall extends DUPX_Validation_abstract_item
{
    protected function runTest()
    {
        if (
            DUPX_InstallerState::getInstance()->getMode() !== DUPX_InstallerState::MODE_OVR_INSTALL ||
            DUPX_InstallerState::isImportFromBackendMode()
        ) {
            return self::LV_SKIP;
        }

        if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) {
            return self::LV_SOFT_WARNING;
        } else {
            return self::LV_GOOD;
        }
    }

    public function getTitle()
    {
        return 'Overwrite Install';
    }

    protected function swarnContent()
    {
        return dupxTplRender('parts/validation/tests/overwrite-install', array(), false);
    }

    protected function goodContent()
    {
        return dupxTplRender('parts/validation/tests/overwrite-install', array(), false);
    }
}
