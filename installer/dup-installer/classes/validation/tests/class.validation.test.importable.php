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

use Duplicator\Installer\Models\ScanInfo;

class DUPX_Validation_test_importable extends DUPX_Validation_abstract_item
{
    /** @var string */
    protected $failMessage = '';

    /**
     * Run test
     *
     * @return int  test status enum
     */
    protected function runTest()
    {
        if (DUPX_InstallerState::isClassicInstall()) {
            return self::LV_SKIP;
        }

        $archiveConf = DUPX_ArchiveConfig::getInstance();

        $coreFoldersCheck  = false;
        $subsitesCheck     = false;
        $globalTablesCheck = false;

        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
                $coreFoldersCheck  = true;
                $globalTablesCheck = true;
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                $globalTablesCheck = true;
                break;
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Unknown mode');
        }

        if ($subsitesCheck) {
            for ($i = 0; $i < count($archiveConf->subsites); $i++) {
                if (
                    empty($archiveConf->subsites[$i]->filteredTables) &&
                    empty($archiveConf->subsites[$i]->filteredPaths)
                ) {
                    break;
                }
            }

            if ($i >= count($archiveConf->subsites)) {
                $this->failMessage = 'The package does not have any importable subsite.';
                return self::LV_FAIL;
            }
        }

        if ($coreFoldersCheck) {
            if (ScanInfo::getInstance()->hasFilteredCoreFolders()) {
                $this->failMessage = 'The package is missing WordPress core folder(s)! ' .
                    'It must include wp-admin, wp-content, wp-includes, uploads, plugins, and themes folders.';
                return self::LV_FAIL;
            }
        }

        if ($globalTablesCheck) {
            if ($archiveConf->dbInfo->tablesBaseCount != $archiveConf->dbInfo->tablesFinalCount) {
                $this->failMessage = 'The package is missing some of the site tables.';
                return self::LV_FAIL;
            }
        }

        return self::LV_PASS;
    }

    /**
     * Get test title
     *
     * @return string
     */
    public function getTitle()
    {
        return 'Package is Importable';
    }

    /**
     * Render fail content
     *
     * @return void
     */
    protected function failContent()
    {
        return dupxTplRender(
            'parts/validation/tests/importable-package',
            array(
                'testResult'  => $this->testResult,
                'failMessage' => $this->failMessage
            ),
            false
        );
    }


    protected function passContent()
    {
        return $this->failContent();
    }
}
