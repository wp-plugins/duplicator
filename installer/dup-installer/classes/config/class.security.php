<?php

/**
 * Security class
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Constants
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Core\Bootstrap;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapUtil;

/**
 * singleton class
 *
 * In this class all installer security checks are performed. If the security checks are not passed, an exception is thrown and the installer is stopped.
 * This happens before anything else so the class must work without the initialization of all global duplicator variables.
 */
class DUPX_Security
{
    const CTRL_TOKEN   = 'ctrl_csrf_token';
    const ROUTER_TOKEN = 'router_csrf_token';

    const SECURITY_NONE     = 'none';
    const SECURITY_PASSWORD = 'pwd';
    const SECURITY_ARCHIVE  = 'archive';

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     * archive path read from  csrf file
     *
     * @var string
     */
    private $archivePath = null;

    /**
     * installer name read from csrf file
     *
     * @var string
     */
    private $bootloader = null;

    /**
     * installer url path read from csrf file
     *
     * @var string
     */
    private $bootUrl = null;

    /**
     * boot log file full path read from csrf file
     *
     * @var string
     */
    private $bootFilePath = null;

    /**
     * boot log file full path read from csrf file
     *
     * @var string
     */
    private $bootLogFile = null;

    /**
     * package hash read from csrf file
     *
     * @var string
     */
    private $packageHash = null;

    /**
     * public package hash read from csrf file
     *
     * @var string
     */
    private $secondaryPackageHash = null;

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

    private function __construct()
    {
        DUPX_CSRF::init(DUPX_INIT, Bootstrap::getPackageHash());

        if (!file_exists(DUPX_CSRF::getFilePath())) {
            throw new Exception("CSRF FILE NOT FOUND\n"
                    . "Please, check webroot file permsission and dup-installer folder permission");
        }

        $this->bootloader           = DUPX_CSRF::getVal('bootloader');
        $this->bootUrl              = DUPX_CSRF::getVal('booturl');
        $this->bootLogFile          = SnapIO::safePath(DUPX_CSRF::getVal('bootLogFile'));
        $this->bootFilePath         = SnapIO::safePath(DUPX_CSRF::getVal('installerOrigPath'));
        $this->archivePath          = SnapIO::safePath(DUPX_CSRF::getVal('archive'));
        $this->packageHash          = DUPX_CSRF::getVal('package_hash');
        $this->secondaryPackageHash = DUPX_CSRF::getVal('secondaryHash');
    }

    /**
     * archive path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getArchivePath()
    {
        return $this->archivePath;
    }

    /**
     * installer full path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootFilePath()
    {
        return $this->bootFilePath;
    }

    /**
     * boot log file full path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootLogFile()
    {
        return $this->bootLogFile;
    }

    /**
     * bootloader path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootloader()
    {
        return $this->bootloader;
    }

    /**
     * bootloader path read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootUrl()
    {
        return $this->bootUrl;
    }

    /**
     * package hash read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getPackageHash()
    {
        return $this->packageHash;
    }

    /**
     * package public hash read from intaller.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getSecondaryPackageHash()
    {
        return $this->secondaryPackageHash;
    }

    /**
     *
     * @return boolean
     * @throws Exception    // if fail throw exception of return true
     */
    public function check()
    {
        try {
            // check if current package hash is equal at bootloader package hash
            if ($this->packageHash !== Bootstrap::getPackageHash()) {
                throw new Exception('Incorrect hash package');
            }

            // checks if the version of the package descriptor is consistent with the version of the files.
            if (DUPX_ArchiveConfig::getInstance()->version_dup !== DUPX_VERSION) {
                throw new Exception('The version of the archive is different from the version of the PHP scripts');
            }

            $token_tested = false;
            // @todo connect with global debug
            $debug = false;

            $action = null;
            if (DUPX_Ctrl_ajax::isAjax($action) == true) {
                if (($token = self::getTokenFromInput(DUPX_Ctrl_ajax::TOKEN_NAME)) === false) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . DUPX_Ctrl_ajax::TOKEN_NAME : '');
                    throw new Exception($msg);
                }
                if (!DUPX_CSRF::check(self::getTokenFromInput(DUPX_Ctrl_ajax::TOKEN_NAME), DUPX_Ctrl_ajax::getTokenKeyByAction($action))) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . DUPX_Ctrl_ajax::getTokenKeyByAction($action) . ' KEY VALUE ' . DUPX_Ctrl_ajax::getTokenKeyByAction($action) : '');
                    throw new Exception($msg);
                }
                $token_tested = true;
            } elseif (($token = self::getTokenFromInput(self::CTRL_TOKEN)) !== false) {
                if (!isset($_REQUEST[PrmMng::PARAM_CTRL_ACTION])) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . PrmMng::PARAM_CTRL_ACTION : '');
                    throw new Exception($msg);
                }
                if (!DUPX_CSRF::check($token, $_REQUEST[PrmMng::PARAM_CTRL_ACTION])) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . PrmMng::PARAM_CTRL_ACTION . ' KEY VALUE ' . $_REQUEST[PrmMng::PARAM_CTRL_ACTION] : '');
                    throw new Exception($msg);
                }
                $token_tested = true;
            }

            if (($token = self::getTokenFromInput(self::ROUTER_TOKEN)) !== false) {
                if (!isset($_REQUEST[PrmMng::PARAM_ROUTER_ACTION])) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . PrmMng::PARAM_ROUTER_ACTION : '');
                    throw new Exception($msg);
                }
                if (!DUPX_CSRF::check($token, $_REQUEST[PrmMng::PARAM_ROUTER_ACTION])) {
                    $msg = 'Security issue' . ($debug ? ' LINE: ' . __LINE__ . ' TOKEN: ' . $token . ' KEY NAME: ' . PrmMng::PARAM_ROUTER_ACTION . ' KEY VALUE ' . $_REQUEST[PrmMng::PARAM_ROUTER_ACTION] : '');
                    throw new Exception($msg);
                }
                $token_tested = true;
            }

            // At least one token must always and in any case be tested
            if (!$token_tested) {
                throw new Exception('Security Check Validation - No Token Found');
            }
        } catch (Exception $e) {
            if (function_exists('error_clear_last')) {
                /**
                 * comment error_clear_last if you want see te exception html on shutdown
                 */
                error_clear_last();
            }

            Log::logException($e, Log::LV_DEFAULT, 'SECURITY CHECK: ');
            dupxTplRender('page-security-error', array(
                'message' => $e->getMessage()
            ));
            die();
        }

        return true;
    }

    /**
     * get sanitized token frominput
     *
     * @param string $tokenName
     *
     * @return string
     */
    protected static function getTokenFromInput($tokenName)
    {
        return SnapUtil::filterInputDefaultSanitizeString(SnapUtil::INPUT_REQUEST, $tokenName, false);
    }

    /**
     * Get security tipe (NONE, PASSWORD, ARCHIVE)
     *
     * @return string enum type
     */
    public function getSecurityType()
    {
        if (PrmMng::getInstance()->getValue(PrmMng::PARAM_SECURE_OK) == true) {
            return self::SECURITY_NONE;
        }

        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        if ($archiveConfig->secure_on === true) {
            return self::SECURITY_PASSWORD;
        }

        if (
            DUPX_InstallerState::isOverwrite() &&
            basename($this->bootFilePath) == 'installer.php' &&
            !in_array($_SERVER['REMOTE_ADDR'], self::getSecurityAddrWhitelist())
        ) {
            return self::SECURITY_ARCHIVE;
        }

        return self::SECURITY_NONE;
    }

    /**
     * Get IPs white list for remote requests
     *
     * @return string[]
     */
    private static function getSecurityAddrWhitelist()
    {
        // uncomment this to test security archive on localhost
        // return array();
        // -------
        return array(
            '127.0.0.1',
            '::1'
        );
    }

    /**
     * return true if security check is passed
     *
     * @return bool
     */
    public function securityCheck()
    {
        $paramsManager = PrmMng::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $result        = false;
        switch ($this->getSecurityType()) {
            case self::SECURITY_NONE:
                $result = true;
                break;
            case self::SECURITY_PASSWORD:
                $paramsManager->setValueFromInput(PrmMng::PARAM_SECURE_PASS);
                $pass_hasher = new DUPX_PasswordHash(8, false);
                $base64Pass  = base64_encode($paramsManager->getValue(PrmMng::PARAM_SECURE_PASS));
                $result      = $pass_hasher->CheckPassword($base64Pass, $archiveConfig->secure_pass);
                break;
            case self::SECURITY_ARCHIVE:
                $paramsManager->setValueFromInput(PrmMng::PARAM_SECURE_ARCHIVE_HASH);
                $result = (strcmp(basename($this->archivePath), $paramsManager->getValue(PrmMng::PARAM_SECURE_ARCHIVE_HASH)) == 0);
                break;
            default:
                throw new Exception('Security type not valid ' . $this->getSecurityType());
                break;
        }

        $paramsManager->setValue(PrmMng::PARAM_SECURE_OK, $result);
        $paramsManager->save();
        return $result;
    }
}
