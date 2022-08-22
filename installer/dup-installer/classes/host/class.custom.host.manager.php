<?php

/**
 * custom hosting manager
 * singleton class
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\DB
 * @link http://www.php-fig.org/psr/psr-2/
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Libs\Snap\SnapWP;

require_once(DUPX_INIT . '/classes/host/interface.host.php');
require_once(DUPX_INIT . '/classes/host/class.godaddy.host.php');
require_once(DUPX_INIT . '/classes/host/class.wpengine.host.php');
require_once(DUPX_INIT . '/classes/host/class.wordpresscom.host.php');
require_once(DUPX_INIT . '/classes/host/class.liquidweb.host.php');
require_once(DUPX_INIT . '/classes/host/class.pantheon.host.php');
require_once(DUPX_INIT . '/classes/host/class.flywheel.host.php');
require_once(DUPX_INIT . '/classes/host/class.siteground.host.php');

class DUPX_Custom_Host_Manager
{
    const HOST_GODADDY      = 'godaddy';
    const HOST_WPENGINE     = 'wpengine';
    const HOST_WORDPRESSCOM = 'wordpresscom';
    const HOST_LIQUIDWEB    = 'liquidweb';
    const HOST_PANTHEON     = 'pantheon';
    const HOST_FLYWHEEL     = 'flywheel';
    const HOST_SITEGROUND   = 'siteground';

    /**
     *
     * @var self
     */
    protected static $instance = null;

    /**
     * this var prevent multiple params inizialization.
     * it's useful on development to prevent an infinite loop in class constructor
     *
     * @var bool
     */
    private $initialized = false;

    /**
     * custom hostings list
     *
     * @var DUPX_Host_interface[]
     */
    private $customHostings = array();

    /**
     * active custom hosting in current server
     *
     * @var string[]
     */
    private $activeHostings = array();

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * init custom histings
     */
    private function __construct()
    {
        $this->customHostings[DUPX_WPEngine_Host::getIdentifier()]     = new DUPX_WPEngine_Host();
        $this->customHostings[DUPX_GoDaddy_Host::getIdentifier()]      = new DUPX_GoDaddy_Host();
        $this->customHostings[DUPX_WordpressCom_Host::getIdentifier()] = new DUPX_WordpressCom_Host();
        $this->customHostings[DUPX_Liquidweb_Host::getIdentifier()]    = new DUPX_Liquidweb_Host();
        $this->customHostings[DUPX_Pantheon_Host::getIdentifier()]     = new DUPX_Pantheon_Host();
        $this->customHostings[DUPX_Flywheel_Host::getIdentifier()]     = new DUPX_Flywheel_Host();
        $this->customHostings[DUPX_Siteground_Host::getIdentifier()]   = new DUPX_Siteground_Host();
    }

    /**
     * execute the active custom hostings inizialization only one time.
     *
     * @return boolean
     * @throws Exception
     */
    public function init()
    {
        if ($this->initialized) {
            return true;
        }
        foreach ($this->customHostings as $cHost) {
            if (!($cHost instanceof DUPX_Host_interface)) {
                throw new Exception('Host must implemnete DUPX_Host_interface');
            }
            if ($cHost->isHosting()) {
                $this->activeHostings[] = $cHost->getIdentifier();
                $cHost->init();
            }
        }
        $this->initialized = true;
        return true;
    }

    /**
     * return the lisst of current custom active hostings
     *
     * @return DUPX_Host_interface[]
     */
    public function getActiveHostings()
    {
        $result = array();
        foreach ($this->customHostings as $cHost) {
            if ($cHost->isHosting()) {
                $result[] = $cHost->getIdentifier();
            }
        }
        return $result;
    }

    /**
     * return true if current identifier hostoing is active
     *
     * @param string $identifier
     * @return bool
     */
    public function isHosting($identifier)
    {
        return isset($this->customHostings[$identifier]) && $this->customHostings[$identifier]->isHosting();
    }

    /**
     *
     * @return boolean|string return false if isn't managed manage hosting of manager hosting
     */
    public function isManaged()
    {
        if ($this->isHosting(self::HOST_WPENGINE)) {
            return self::HOST_WPENGINE;
        } elseif ($this->isHosting(self::HOST_LIQUIDWEB)) {
            return self::HOST_LIQUIDWEB;
        } elseif ($this->isHosting(self::HOST_GODADDY)) {
            return self::HOST_GODADDY;
        } elseif ($this->isHosting(self::HOST_WORDPRESSCOM)) {
            return self::HOST_WORDPRESSCOM;
        } elseif ($this->isHosting(self::HOST_PANTHEON)) {
            return self::HOST_PANTHEON;
        } elseif ($this->isHosting(self::HOST_FLYWHEEL)) {
            return self::HOST_FLYWHEEL;
        } else {
            return false;
        }
    }

    /**
     *
     * @param type $identifier
     * @return boolean|DUPX_Host_interface
     */
    public function getHosting($identifier)
    {
        if ($this->isHosting($identifier)) {
            return $this->customHostings[$identifier];
        } else {
            return false;
        }
    }

    /**
     * @todo temp function fot prevent the warnings on managed hosting.
     * This function must be removed in favor of right extraction mode will'be implemented
     *
     * @param string $extract_filename
     * @return boolean
     */
    public function skipWarningExtractionForManaged($extract_filename)
    {
        if (!$this->isManaged()) {
            return false;
        } elseif (SnapWP::isWpCore($extract_filename, SnapWP::PATH_RELATIVE)) {
            return true;
        } elseif (DUPX_ArchiveConfig::getInstance()->isChildOfArchivePath($extract_filename, array('abs', 'plugins', 'muplugins', 'themes'))) {
            return true;
        } elseif (in_array($extract_filename, DUPX_Plugins_Manager::getInstance()->getDropInsPaths())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return bool
     * @throws Exception
     */
    public function setManagedHostParams()
    {
        if (($managedSlug = $this->isManaged()) === false) {
            return;
        }

        $paramsManager = PrmMng::getInstance();

        $paramsManager->setValue(PrmMng::PARAM_WP_CONFIG, 'nothing');
        $paramsManager->setFormStatus(PrmMng::PARAM_WP_CONFIG, ParamForm::STATUS_INFO_ONLY);
        $paramsManager->setValue(PrmMng::PARAM_HTACCESS_CONFIG, 'nothing');
        $paramsManager->setFormStatus(PrmMng::PARAM_HTACCESS_CONFIG, ParamForm::STATUS_INFO_ONLY);
        $paramsManager->setValue(PrmMng::PARAM_OTHER_CONFIG, 'nothing');
        $paramsManager->setFormStatus(PrmMng::PARAM_OTHER_CONFIG, ParamForm::STATUS_INFO_ONLY);

        $paramsManager->setValue(PrmMng::PARAM_DB_ACTION, 'empty');

        if (DUPX_InstallerState::getInstance()->getMode() === DUPX_InstallerState::MODE_OVR_INSTALL) {
            $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
            $paramsManager->setValue(PrmMng::PARAM_VALIDATION_ACTION_ON_START, DUPX_Validation_manager::ACTION_ON_START_AUTO);
            $paramsManager->setValue(PrmMng::PARAM_DB_DISPLAY_OVERWIRE_WARNING, false);
            $paramsManager->setValue(PrmMng::PARAM_DB_HOST, $overwriteData['dbhost']);
            $paramsManager->setValue(PrmMng::PARAM_DB_NAME, $overwriteData['dbname']);
            $paramsManager->setValue(PrmMng::PARAM_DB_USER, $overwriteData['dbuser']);
            $paramsManager->setValue(PrmMng::PARAM_DB_PASS, $overwriteData['dbpass']);
            $paramsManager->setValue(PrmMng::PARAM_DB_TABLE_PREFIX, $overwriteData['table_prefix']);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_ACTION, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_HOST, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_NAME, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_USER, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_PASS, ParamForm::STATUS_INFO_ONLY);
            $paramsManager->setFormStatus(PrmMng::PARAM_DB_TABLE_PREFIX, ParamForm::STATUS_INFO_ONLY);
        }

        $paramsManager->setFormStatus(PrmMng::PARAM_URL_NEW, ParamForm::STATUS_INFO_ONLY);
        $paramsManager->setFormStatus(PrmMng::PARAM_SITE_URL, ParamForm::STATUS_INFO_ONLY);
        $paramsManager->setFormStatus(PrmMng::PARAM_PATH_NEW, ParamForm::STATUS_INFO_ONLY);

        $this->getHosting($managedSlug)->setCustomParams();
    }
}
