<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;


class DUPX_CSRF
{

    private static $packagHash = null;
    private static $mainFolder = null;

	/**
     * Session var name prefix
     * @var string
     */
    public static $prefix = '_DUPX_CSRF';
	
	/**
     * Stores all CSRF values: Key as CSRF name and Val as CRF value
     * @var array
     */
    private static $CSRFVars = null;
	
    public static function init($mainFolderm, $packageHash)
    {
        self::$mainFolder = $mainFolderm;
        self::$packagHash = $packageHash;
        self::$CSRFVars   = null;
    }

    /**
     * Set new CSRF
     *
     * @param string $key CSRF key
     * @param string $val CSRF val
     *
     * @return Void
     */
    public static function setKeyVal($key, $val)
    {
        $CSRFVars       = self::getCSRFVars();
        $CSRFVars[$key] = $val;
        self::saveCSRFVars($CSRFVars);
        self::$CSRFVars = null;
    }

    /**
     * Get CSRF value by passing CSRF key
     *
     * @param string $key CSRF key
     *
     * @return string|boolean If CSRF value set for give n Key, It returns CRF value otherise returns false
     */
    public static function getVal($key)
    {
        $CSRFVars = self::getCSRFVars();
        if (isset($CSRFVars[$key])) {
            return $CSRFVars[$key];
        } else {
            return false;
        }
    }

    /**
     * Generate DUPX_CSRF value for form
     *
     * @param   string  $form    // Form name as session key
     *
     * @return  string      // token
     */
    public static function generate($form = null)
    {
        $keyName = self::getKeyName($form);
        $existingToken = self::getVal($keyName);
        if (false !== $existingToken) {
            $token = $existingToken;
        } else {
            $token = DUPX_CSRF::token() . DUPX_CSRF::fingerprint();
        }

        self::setKeyVal($keyName, $token);
        return $token;
    }

    /**
     * Check DUPX_CSRF value of form
     *
     * @param   string  $token  - Token
     * @param   string  $form   - Form name as session key
     * @return  boolean
     */
    public static function check($token, $form = null)
    {
        if (empty($form)) {
            return false;
        }

        $keyName  = self::getKeyName($form);
        $CSRFVars = self::getCSRFVars();
        if (isset($CSRFVars[$keyName]) && $CSRFVars[$keyName] == $token) {
        // token OK
            return true;
        }
        return false;
    }

    /** Generate token
     *
     * @return  string
     */
    protected static function token()
    {
        mt_srand((int)((double) microtime() * 10000));
        $charid = strtoupper(md5(uniqid(rand(), true)));
        return substr($charid, 0, 8) . substr($charid, 8, 4) . substr($charid, 12, 4) . substr($charid, 16, 4) . substr($charid, 20, 12);
    }

    /** Returns "digital fingerprint" of user
     *
     * @return  string  - MD5 hashed data
     */
    protected static function fingerprint()
    {
        return strtoupper(md5(implode('|', array($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']))));
    }

    /**
     * Generate CSRF Key name
     *
     * @param string $form the form name for which CSRF key need to generate
     * @return string CSRF key
     */
    private static function getKeyName($form)
    {
        return DUPX_CSRF::$prefix . '_' . $form;
    }

    /**
     * Get Package hash
     *
     * @return string Package hash
     */
    private static function getPackageHash()
    {
        if (is_null(self::$packagHash)) {
            throw new Exception('Not init CSFR CLASS');
        }
        return self::$packagHash;
    }

    /**
     * Get file path where CSRF tokens are stored in JSON encoded format
     *
     * @return string file path where CSRF token stored
     */
    public static function getFilePath()
    {
        if (is_null(self::$mainFolder)) {
            throw new Exception('Not init CSFR CLASS');
        }
        $dupInstallerfolderPath = self::$mainFolder;
        $packageHash            = self::getPackageHash();
        $fileName               = 'dup-installer-csrf__' . $packageHash . '.txt';
        $filePath               = $dupInstallerfolderPath . '/' . $fileName;
        return $filePath;
    }

    /**
     * Get all CSRF vars in array format
     *
     * @return array Key as CSRF name and value as CSRF value
     */
    private static function getCSRFVars()
    {
        if (is_null(self::$CSRFVars)) {
            $filePath = self::getFilePath();
            if (file_exists($filePath)) {
                $contents = file_get_contents($filePath);
                if (empty($contents)) {
                    self::$CSRFVars = array();
                } else {
                    $CSRFobjs = json_decode($contents);
                    foreach ($CSRFobjs as $key => $value) {
                        self::$CSRFVars[$key] = $value;
                    }
                }
            } else {
                self::$CSRFVars = array();
            }
        }
        return self::$CSRFVars;
    }

    /**
     * Stores all CSRF vars
     *
     * @param array $CSRFVars holds all CSRF key val
     * @return void
     */
    private static function saveCSRFVars($CSRFVars)
    {
        $contents = DupLiteSnapJsonU::wp_json_encode($CSRFVars);
        $filePath = self::getFilePath();
        file_put_contents($filePath, $contents);
    }
}
