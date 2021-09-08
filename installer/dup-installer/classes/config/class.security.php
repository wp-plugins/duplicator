<?php

/**
 * Security class
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\Constants
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * singleton class
 *
 *
 * In this class all installer security checks are performed. If the security checks are not passed, an exception is thrown and the installer is stopped.
 * This happens before anything else so the class must work without the initialization of all global duplicator variables.
 */
class DUPX_Security
{
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
     * @var string
     */
    private $archivePath = null;

    /**
     * installer name read from csrf file
     * @var string
     */
    private $bootloader = null;

    /**
     * installer url path read from csrf file
     * @var string
     */
    private $bootUrl = null;

    /**
     * boot log file full path read from csrf file
     * @var string
     */
    private $bootFilePath = null;

    /**
     * boot log file full path read from csrf file
     * @var string
     */
    private $bootLogFile = null;

    /**
     * package hash read from csrf file
     * @var string
     */
    private $packageHash = null;

    /**
     * public package hash read from csrf file
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
        DUPX_CSRF::init($GLOBALS['DUPX_INIT'], DUPX_Boot::getPackageHash());

        if (!file_exists(DUPX_CSRF::getFilePath())) {
            throw new Exception("CSRF FILE NOT FOUND\n"
                    . "Please, check webroot file permsission and dup-installer folder permission");
        }

        $this->bootloader           = DUPX_CSRF::getVal('bootloader');
        $this->bootUrl              = DUPX_CSRF::getVal('booturl');
        $this->bootLogFile          = DupLiteSnapLibIOU::safePath(DUPX_CSRF::getVal('bootLogFile'));
        $this->bootFilePath         = DupLiteSnapLibIOU::safePath(DUPX_CSRF::getVal('installerOrigPath'));
        $this->archivePath          = DupLiteSnapLibIOU::safePath(DUPX_CSRF::getVal('archive'));
        $this->packageHash          = DUPX_CSRF::getVal('package_hash');
        $this->secondaryPackageHash = DUPX_CSRF::getVal('secondaryHash');
    }

    /**
     * archive path read from installer.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getArchivePath()
    {
        return $this->archivePath;
    }

    /**
     * installer full path read from installer.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootFilePath()
    {
        return $this->bootFilePath;
    }

    /**
     * boot log file full path read from installer.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootLogFile()
    {
        return $this->bootLogFile;
    }

    /**
     * bootloader path read from installer.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootloader()
    {
        return $this->bootloader;
    }

    /**
     * bootloader path read from installer.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getBootUrl()
    {
        return $this->bootUrl;
    }

    /**
     * package hash read from installer.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getPackageHash()
    {
        return $this->packageHash;
    }

    /**
     * package public hash read from installer.php passed by DUPX_CSFR
     *
     * @return string
     */
    public function getSecondaryPackageHash()
    {
        return $this->secondaryPackageHash;
    }

    /**
     * Get security tipe (NONE, PASSWORD, ARCHIVE)
     *
     * @return string enum type
     */
    public function getSecurityType()
    {
        if ($GLOBALS['DUPX_AC']->secure_on == true) {
            return self::SECURITY_PASSWORD;
        }

        if (
            DUPX_InstallerState::getInstance()->mode == DUPX_InstallerMode::OverwriteInstall &&
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
        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $result = false;
        switch ($this->getSecurityType()) {
            case self::SECURITY_NONE:
                $result = true;
                break;
            case self::SECURITY_PASSWORD:
                $securePass = isset($_POST['secure-pass']) ? DupLiteSnapLibUtil::sanitize_non_stamp_chars_and_newline($_POST['secure-pass']) : '';
                $pass_hasher = new DUPX_PasswordHash(8, false);
                $base64Pass  = base64_encode($securePass);
                $result      = $pass_hasher->CheckPassword($base64Pass, $archiveConfig->secure_pass);
                break;
            case self::SECURITY_ARCHIVE:
                $secureArchive = isset($_POST['secure-archive']) ? DupLiteSnapLibUtil::sanitize_non_stamp_chars_newline_and_trim($_POST['secure-archive']) : '';
                $result = (strcmp(basename($this->archivePath), $secureArchive) == 0);
                break;
            default:
                throw new Exception('Security type not valid ' . $this->getSecurityType());
                break;
        }
        return $result;
    }
}
