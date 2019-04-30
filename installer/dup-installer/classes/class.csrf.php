<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUPX_CSRF {
	
	/** Session var name
	 * @var string
	 */
	public static $prefix = '_DUPX_CSRF';
	private static $cipher;
	private static $CSRFVars;

	public static function setKeyVal($key, $val) {
		$CSRFVars = self::getCSRFVars();
		$CSRFVars[$key] = $val;
		self::saveCSRFVars($CSRFVars);
		self::$CSRFVars = false;
	}

	public static function getVal($key) {
		$CSRFVars = self::getCSRFVars();
		if (isset($CSRFVars[$key])) {
			return $CSRFVars[$key];
		} else {
			return false;
		}

	}
	
	/** Generate DUPX_CSRF value for form
	 * @param	string	$form	- Form name as session key
	 * @return	string	- token
	 */
	public static function generate($form = NULL) {
		$keyName = self::getKeyName($form);

		$existingToken = self::getVal($keyName);
		if (false !== $existingToken) {
			$token = $existingToken;
		} else {
			$token = DUPX_CSRF::token() . DUPX_CSRF::fingerprint();
			if (self::isCrypt()) {
				// $keyName = self::encrypt($keyName);
				$token = self::encrypt($token);
			}
		}
		
		self::setKeyVal($keyName, $token);
		return $token;
	}
	
	/** Check DUPX_CSRF value of form
	 * @param	string	$token	- Token
	 * @param	string	$form	- Form name as session key
	 * @return	boolean
	 */
	public static function check($token, $form = NULL) {
		$keyName = self::getKeyName($form);
		// if (self::isCrypt()) {
			// $keyName = self::decrypt($keyName);
			// $token = self::decrypt($token);
		// }
		$CSRFVars = self::getCSRFVars();
		if (isset($CSRFVars[$keyName]) && $CSRFVars[$keyName] == $token) { // token OK
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

	private static function getKeyName($form) {
		return DUPX_CSRF::$prefix . '_' . $form;
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

	private static function getFilePath() {
		if (class_exists('DUPX_Bootstrap')) {
			$dupInstallerfolderPath = dirname(__FILE__).'/dup-installer/';
		} else {
			$dupInstallerfolderPath = $GLOBALS['DUPX_INIT'].'/';
		}
		$packageHash = self::getPackageHash();
		$fileName = 'dup-installer-csrf__'.$packageHash.'.txt';
		$filePath = $dupInstallerfolderPath.$fileName;
		return $filePath;
	}

	private static function getCSRFVars() {
		if (!isset(self::$CSRFVars) || false === self::$CSRFVars) {
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

	private static function saveCSRFVars($CSRFVars) {
		$contents = json_encode($CSRFVars);
		$filePath = self::getFilePath();
		file_put_contents($filePath, $contents);
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

?>