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

class DUPX_Validation_test_managed_supported extends DUPX_Validation_abstract_item
{
    private $managed     = false;
    private $failMessage = '';

    protected function runTest()
    {
        if (!($this->managed = DUPX_Custom_Host_Manager::getInstance()->isManaged())) {
            return self::LV_SKIP;
        }

        if (DUPX_InstallerState::isImportFromBackendMode()) {
            return self::LV_PASS;
        }

        switch ($this->managed) {
            case DUPX_Custom_Host_Manager::HOST_GODADDY:
            case DUPX_Custom_Host_Manager::HOST_LIQUIDWEB:
            case DUPX_Custom_Host_Manager::HOST_WPENGINE:
                return self::LV_PASS;
            case DUPX_Custom_Host_Manager::HOST_PANTHEON:
            case DUPX_Custom_Host_Manager::HOST_WORDPRESSCOM:
            case DUPX_Custom_Host_Manager::HOST_FLYWHEEL:
                $this->failMessage = 'Standard installations on this managed host are not supported because it uses a non-standard configuration that can ' .
                    'only be read at runtime. Use Drop and Drop install to overwrite the site instead.';
                return self::LV_FAIL;
            default:
                $this->failMessage = "Unknown managed host type.";
                return self::LV_FAIL;
        }
    }

    public function getTitle()
    {
        return 'Managed hosting supported';
    }

    protected function failContent()
    {
        return dupxTplRender(
            'parts/validation/tests/managed-supported',
            array(
            'isOk'           => false,
            'managedHosting' => $this->managed,
            'failMessage'    => $this->failMessage
            ),
            false
        );
    }

    protected function passContent()
    {
        return dupxTplRender(
            'parts/validation/tests/managed-supported',
            array(
            'isOk'           => true,
            'managedHosting' => $this->managed,
            'failMessage'    => $this->failMessage
            ),
            false
        );
    }
}
