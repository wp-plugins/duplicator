<?php
class DUPX_CSRF {
	
	/** Session var name
	 * @var string
	 */
	public static $prefix = '_DUPX_CSRF';
	private static $cipher;
	
	/** Generate DUPX_CSRF value for form
	 * @param	string	$form	- Form name as session key
	 * @return	string	- token
	 */
	public static function generate($form = NULL) {
		$cookieName = self::getCookieName($form);
		if (!empty($_COOKIE[$cookieName])) {
			$token = $_COOKIE[$cookieName];
		} else {
            $token = DUPX_CSRF::token() . DUPX_CSRF::fingerprint();
		}
		if (self::isCrypt()) {
			// $cookieName = self::encrypt($cookieName);
			$token = self::encrypt($token);
		}
        $ret = DUPX_CSRF::setCookie($cookieName, $token);
		return $token;
	}
	
	/** Check DUPX_CSRF value of form
	 * @param	string	$token	- Token
	 * @param	string	$form	- Form name as session key
	 * @return	boolean
	 */
	public static function check($token, $form = NULL) {
		if (!self::isCookieEnabled()) {
			return true;
		}
		$cookieName = self::getCookieName($form);
		// if (self::isCrypt()) {
			// $cookieName = self::decrypt($cookieName);
			// $token = self::decrypt($token);
		// }
		if (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] == $token) { // token OK
			return true;
			// return (substr($token, -32) == DUPX_CSRF::fingerprint()); // fingerprint OK?
		}
		return FALSE;
	}
	
	/** Generate token
	 * @param	void
	 * @return  string
	 */
	protected static function token() {
		mt_srand((double) microtime() * 10000);
		$charid = strtoupper(md5(uniqid(rand(), TRUE)));
		return substr($charid, 0, 8) . substr($charid, 8, 4) . substr($charid, 12, 4) . substr($charid, 16, 4) . substr($charid, 20, 12);
	}
	
	/** Returns "digital fingerprint" of user
	 * @param 	void
	 * @return 	string 	- MD5 hashed data
	 */
	protected static function fingerprint() {
		return strtoupper(md5(implode('|', array($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']))));
	}

	public static function setCookie($cookieName, $cookieVal) {
		$_COOKIE[$cookieName] = $cookieVal;
		return setcookie($cookieName, $cookieVal, time() + 10800, '/');
	}
	
	/**
	* @return bool
	*/
	protected static function isCookieEnabled() {
		return (count($_COOKIE) > 0);
	}

	public static function resetAllTokens() {
		// $cookiePrefix = DUPX_CSRF::$prefix.'_'.self::getPackageHash().'_';
		$cookiePrefix = DUPX_CSRF::$prefix.'_';
		foreach ($_COOKIE as $cookieName => $cookieVal) {
			if (0 === strpos($cookieName, $cookiePrefix) || 'archive' == $cookieName || 'bootloader' == $cookieName) {
				$baseUrl = self::getBaseUrl();
				setcookie($cookieName, '', time() - 86400, $baseUrl);	
			}
		}
		$_COOKIE = array();
	}

	private static function getBaseUrl() {
		// output: /myproject/index.php
		$currentPath = $_SERVER['PHP_SELF']; 
		
		// output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index ) 
		$pathInfo = pathinfo($currentPath); 
		
		// output: localhost
		$hostName = $_SERVER['HTTP_HOST']; 
		
		// output: http://
		$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
		
		// return: http://localhost/myproject/
		return $protocol.$hostName.$pathInfo['dirname']."/";
	}

	private static function getCookieName($form) {
		return DUPX_CSRF::$prefix . '_' . self::getPackageHash() . '_' . $form;
	}

	private static function isCrypt() {
		if (class_exists('DUPX_Bootstrap')) {
			return DUPX_Bootstrap::CSRF_CRYPT;
		} else {
			return $GLOBALS['DUPX_AC']->csrf_crypt;
		}
	}

	private static function getCryptKey() {
		return 'snapcreek-'.self::getPackageHash();
	}

	private static function getPackageHash() {
		if (class_exists('DUPX_Bootstrap')) {
			return DUPX_Bootstrap::PACKAGE_HASH;
		} else {
			return $GLOBALS['DUPX_AC']->package_hash;
		}
	}

	private static function getCipher() {
		if (!isset(self::$cipher)) {
			self::$cipher = new Crypt_Rijndael();
			$cryptKey = self::getCryptKey();
			self::$cipher->setKey($cryptKey);
		}
		return self::$cipher;
	}

	private static function encrypt($val) {
		$cipher = self::getCipher();
		$val = $cipher->encrypt($val);
		return base64_encode($val);
	}

	private static function decrypt($val) {
		$cipher = self::getCipher();
		$val = base64_decode($val);
		return $cipher->decrypt($val);
	}
}