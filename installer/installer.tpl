<?php

if (!defined('KB_IN_BYTES')) { define('KB_IN_BYTES', 1024); }
if (!defined('MB_IN_BYTES')) { define('MB_IN_BYTES', 1024 * KB_IN_BYTES); }
if (!defined('GB_IN_BYTES')) { define('GB_IN_BYTES', 1024 * MB_IN_BYTES); }
if (!defined('DUPLICATOR_PHP_MAX_MEMORY')) { define('DUPLICATOR_PHP_MAX_MEMORY', 4096 * MB_IN_BYTES); }

date_default_timezone_set('UTC'); // Some machines don’t have this set so just do it here.
@ignore_user_abort(true);
@set_time_limit(3600);
@ini_set('memory_limit', DUPLICATOR_PHP_MAX_MEMORY);
@ini_set('max_input_time', '-1');
@ini_set('pcre.backtrack_limit', PHP_INT_MAX);
@ini_set('default_socket_timeout', 3600);

// phpseclib 1.0.14 for the Rijndael Symmetric key encryption
/**
 * Base Class for all Crypt_* cipher classes
 *
 * PHP versions 4 and 5
 *
 * Internally for phpseclib developers:
 *  If you plan to add a new cipher class, please note following rules:
 *
 *  - The new Crypt_* cipher class should extend Crypt_Base
 *
 *  - Following methods are then required to be overridden/overloaded:
 *
 *    - _encryptBlock()
 *
 *    - _decryptBlock()
 *
 *    - _setupKey()
 *
 *  - All other methods are optional to be overridden/overloaded
 *
 *  - Look at the source code of the current ciphers how they extend Crypt_Base
 *    and take one of them as a start up for the new cipher class.
 *
 *  - Please read all the other comments/notes/hints here also for each class var/method
 *
 * LICENSE: Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Crypt
 * @package   Crypt_Base
 * @author    Jim Wigginton <terrafrost@php.net>
 * @author    Hans-Juergen Petrich <petrich@tronic-media.com>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

/**#@+
 * @access public
 * @see self::encrypt()
 * @see self::decrypt()
 */
/**
 * Encrypt / decrypt using the Counter mode.
 *
 * Set to -1 since that's what Crypt/Random.php uses to index the CTR mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Counter_.28CTR.29
 */
define('CRYPT_MODE_CTR', -1);
/**
 * Encrypt / decrypt using the Electronic Code Book mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Electronic_codebook_.28ECB.29
 */
define('CRYPT_MODE_ECB', 1);
/**
 * Encrypt / decrypt using the Code Book Chaining mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29
 */
define('CRYPT_MODE_CBC', 2);
/**
 * Encrypt / decrypt using the Cipher Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher_feedback_.28CFB.29
 */
define('CRYPT_MODE_CFB', 3);
/**
 * Encrypt / decrypt using the Output Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Output_feedback_.28OFB.29
 */
define('CRYPT_MODE_OFB', 4);
/**
 * Encrypt / decrypt using streaming mode.
 */
define('CRYPT_MODE_STREAM', 5);
/**#@-*/

/**#@+
 * @access private
 * @see self::Crypt_Base()
 * @internal These constants are for internal use only
 */
/**
 * Base value for the internal implementation $engine switch
 */
define('CRYPT_ENGINE_INTERNAL', 1);
/**
 * Base value for the mcrypt implementation $engine switch
 */
define('CRYPT_ENGINE_MCRYPT', 2);
/**
 * Base value for the OpenSSL implementation $engine switch
 */
define('CRYPT_ENGINE_OPENSSL', 3);
/**#@-*/

/**
 * Base Class for all Crypt_* cipher classes
 *
 * @package Crypt_Base
 * @author  Jim Wigginton <terrafrost@php.net>
 * @author  Hans-Juergen Petrich <petrich@tronic-media.com>
 * @access  public
 */
class Crypt_Base
{
    /**
     * The Encryption Mode
     *
     * @see self::Crypt_Base()
     * @var int
     * @access private
     */
    var $mode;

    /**
     * The Block Length of the block cipher
     *
     * @var int
     * @access private
     */
    var $block_size = 16;

    /**
     * The Key
     *
     * @see self::setKey()
     * @var string
     * @access private
     */
    var $key = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    /**
     * The Initialization Vector
     *
     * @see self::setIV()
     * @var string
     * @access private
     */
    var $iv;

    /**
     * A "sliding" Initialization Vector
     *
     * @see self::enableContinuousBuffer()
     * @see self::_clearBuffers()
     * @var string
     * @access private
     */
    var $encryptIV;

    /**
     * A "sliding" Initialization Vector
     *
     * @see self::enableContinuousBuffer()
     * @see self::_clearBuffers()
     * @var string
     * @access private
     */
    var $decryptIV;

    /**
     * Continuous Buffer status
     *
     * @see self::enableContinuousBuffer()
     * @var bool
     * @access private
     */
    var $continuousBuffer = false;

    /**
     * Encryption buffer for CTR, OFB and CFB modes
     *
     * @see self::encrypt()
     * @see self::_clearBuffers()
     * @var array
     * @access private
     */
    var $enbuffer;

    /**
     * Decryption buffer for CTR, OFB and CFB modes
     *
     * @see self::decrypt()
     * @see self::_clearBuffers()
     * @var array
     * @access private
     */
    var $debuffer;

    /**
     * mcrypt resource for encryption
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     *
     * @see self::encrypt()
     * @var resource
     * @access private
     */
    var $enmcrypt;

    /**
     * mcrypt resource for decryption
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     *
     * @see self::decrypt()
     * @var resource
     * @access private
     */
    var $demcrypt;

    /**
     * Does the enmcrypt resource need to be (re)initialized?
     *
     * @see Crypt_Twofish::setKey()
     * @see Crypt_Twofish::setIV()
     * @var bool
     * @access private
     */
    var $enchanged = true;

    /**
     * Does the demcrypt resource need to be (re)initialized?
     *
     * @see Crypt_Twofish::setKey()
     * @see Crypt_Twofish::setIV()
     * @var bool
     * @access private
     */
    var $dechanged = true;

    /**
     * mcrypt resource for CFB mode
     *
     * mcrypt's CFB mode, in (and only in) buffered context,
     * is broken, so phpseclib implements the CFB mode by it self,
     * even when the mcrypt php extension is available.
     *
     * In order to do the CFB-mode work (fast) phpseclib
     * use a separate ECB-mode mcrypt resource.
     *
     * @link http://phpseclib.sourceforge.net/cfb-demo.phps
     * @see self::encrypt()
     * @see self::decrypt()
     * @see self::_setupMcrypt()
     * @var resource
     * @access private
     */
    var $ecb;

    /**
     * Optimizing value while CFB-encrypting
     *
     * Only relevant if $continuousBuffer enabled
     * and $engine == CRYPT_ENGINE_MCRYPT
     *
     * It's faster to re-init $enmcrypt if
     * $buffer bytes > $cfb_init_len than
     * using the $ecb resource furthermore.
     *
     * This value depends of the chosen cipher
     * and the time it would be needed for it's
     * initialization [by mcrypt_generic_init()]
     * which, typically, depends on the complexity
     * on its internaly Key-expanding algorithm.
     *
     * @see self::encrypt()
     * @var int
     * @access private
     */
    var $cfb_init_len = 600;

    /**
     * Does internal cipher state need to be (re)initialized?
     *
     * @see self::setKey()
     * @see self::setIV()
     * @see self::disableContinuousBuffer()
     * @var bool
     * @access private
     */
    var $changed = true;

    /**
     * Padding status
     *
     * @see self::enablePadding()
     * @var bool
     * @access private
     */
    var $padding = true;

    /**
     * Is the mode one that is paddable?
     *
     * @see self::Crypt_Base()
     * @var bool
     * @access private
     */
    var $paddable = false;

    /**
     * Holds which crypt engine internaly should be use,
     * which will be determined automatically on __construct()
     *
     * Currently available $engines are:
     * - CRYPT_ENGINE_OPENSSL  (very fast, php-extension: openssl, extension_loaded('openssl') required)
     * - CRYPT_ENGINE_MCRYPT   (fast, php-extension: mcrypt, extension_loaded('mcrypt') required)
     * - CRYPT_ENGINE_INTERNAL (slower, pure php-engine, no php-extension required)
     *
     * @see self::_setEngine()
     * @see self::encrypt()
     * @see self::decrypt()
     * @var int
     * @access private
     */
    var $engine;

    /**
     * Holds the preferred crypt engine
     *
     * @see self::_setEngine()
     * @see self::setPreferredEngine()
     * @var int
     * @access private
     */
    var $preferredEngine;

    /**
     * The mcrypt specific name of the cipher
     *
     * Only used if $engine == CRYPT_ENGINE_MCRYPT
     *
     * @link http://www.php.net/mcrypt_module_open
     * @link http://www.php.net/mcrypt_list_algorithms
     * @see self::_setupMcrypt()
     * @var string
     * @access private
     */
    var $cipher_name_mcrypt;

    /**
     * The openssl specific name of the cipher
     *
     * Only used if $engine == CRYPT_ENGINE_OPENSSL
     *
     * @link http://www.php.net/openssl-get-cipher-methods
     * @var string
     * @access private
     */
    var $cipher_name_openssl;

    /**
     * The openssl specific name of the cipher in ECB mode
     *
     * If OpenSSL does not support the mode we're trying to use (CTR)
     * it can still be emulated with ECB mode.
     *
     * @link http://www.php.net/openssl-get-cipher-methods
     * @var string
     * @access private
     */
    var $cipher_name_openssl_ecb;

    /**
     * The default salt used by setPassword()
     *
     * @see self::setPassword()
     * @var string
     * @access private
     */
    var $password_default_salt = 'phpseclib/salt';

    /**
     * The namespace used by the cipher for its constants.
     *
     * ie: AES.php is using CRYPT_AES_MODE_* for its constants
     *     so $const_namespace is AES
     *
     *     DES.php is using CRYPT_DES_MODE_* for its constants
     *     so $const_namespace is DES... and so on
     *
     * All CRYPT_<$const_namespace>_MODE_* are aliases of
     * the generic CRYPT_MODE_* constants, so both could be used
     * for each cipher.
     *
     * Example:
     * $aes = new Crypt_AES(CRYPT_AES_MODE_CFB); // $aes will operate in cfb mode
     * $aes = new Crypt_AES(CRYPT_MODE_CFB);     // identical
     *
     * @see self::Crypt_Base()
     * @var string
     * @access private
     */
    var $const_namespace;

    /**
     * The name of the performance-optimized callback function
     *
     * Used by encrypt() / decrypt()
     * only if $engine == CRYPT_ENGINE_INTERNAL
     *
     * @see self::encrypt()
     * @see self::decrypt()
     * @see self::_setupInlineCrypt()
     * @see self::$use_inline_crypt
     * @var Callback
     * @access private
     */
    var $inline_crypt;

    /**
     * Holds whether performance-optimized $inline_crypt() can/should be used.
     *
     * @see self::encrypt()
     * @see self::decrypt()
     * @see self::inline_crypt
     * @var mixed
     * @access private
     */
    var $use_inline_crypt;

    /**
     * If OpenSSL can be used in ECB but not in CTR we can emulate CTR
     *
     * @see self::_openssl_ctr_process()
     * @var bool
     * @access private
     */
    var $openssl_emulate_ctr = false;

    /**
     * Determines what options are passed to openssl_encrypt/decrypt
     *
     * @see self::isValidEngine()
     * @var mixed
     * @access private
     */
    var $openssl_options;

    /**
     * Has the key length explicitly been set or should it be derived from the key, itself?
     *
     * @see self::setKeyLength()
     * @var bool
     * @access private
     */
    var $explicit_key_length = false;

    /**
     * Don't truncate / null pad key
     *
     * @see self::_clearBuffers()
     * @var bool
     * @access private
     */
    var $skip_key_adjustment = false;

    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.
     *
     * $mode could be:
     *
     * - CRYPT_MODE_ECB
     *
     * - CRYPT_MODE_CBC
     *
     * - CRYPT_MODE_CTR
     *
     * - CRYPT_MODE_CFB
     *
     * - CRYPT_MODE_OFB
     *
     * (or the alias constants of the chosen cipher, for example for AES: CRYPT_AES_MODE_ECB or CRYPT_AES_MODE_CBC ...)
     *
     * If not explicitly set, CRYPT_MODE_CBC will be used.
     *
     * @param int $mode
     * @access public
     */
    function __construct($mode = CRYPT_MODE_CBC)
    {
        // $mode dependent settings
        switch ($mode) {
            case CRYPT_MODE_ECB:
                $this->paddable = true;
                $this->mode = CRYPT_MODE_ECB;
                break;
            case CRYPT_MODE_CTR:
            case CRYPT_MODE_CFB:
            case CRYPT_MODE_OFB:
            case CRYPT_MODE_STREAM:
                $this->mode = $mode;
                break;
            case CRYPT_MODE_CBC:
            default:
                $this->paddable = true;
                $this->mode = CRYPT_MODE_CBC;
        }

        $this->_setEngine();

        // Determining whether inline crypting can be used by the cipher
        if ($this->use_inline_crypt !== false) {
            $this->use_inline_crypt = version_compare(PHP_VERSION, '5.3.0') >= 0 || function_exists('create_function');
        }
    }

    /**
     * PHP4 compatible Default Constructor.
     *
     * @see self::__construct()
     * @param int $mode
     * @access public
     */
    function Crypt_Base($mode = CRYPT_MODE_CBC)
    {
        $this->__construct($mode);
    }

    /**
     * Sets the initialization vector. (optional)
     *
     * SetIV is not required when CRYPT_MODE_ECB (or ie for AES: CRYPT_AES_MODE_ECB) is being used.  If not explicitly set, it'll be assumed
     * to be all zero's.
     *
     * @access public
     * @param string $iv
     * @internal Can be overwritten by a sub class, but does not have to be
     */
    function setIV($iv)
    {
        if ($this->mode == CRYPT_MODE_ECB) {
            return;
        }

        $this->iv = $iv;
        $this->changed = true;
    }

    /**
     * Sets the key length.
     *
     * Keys with explicitly set lengths need to be treated accordingly
     *
     * @access public
     * @param int $length
     */
    function setKeyLength($length)
    {
        $this->explicit_key_length = true;
        $this->changed = true;
        $this->_setEngine();
    }

    /**
     * Returns the current key length in bits
     *
     * @access public
     * @return int
     */
    function getKeyLength()
    {
        return $this->key_length << 3;
    }

    /**
     * Returns the current block length in bits
     *
     * @access public
     * @return int
     */
    function getBlockLength()
    {
        return $this->block_size << 3;
    }

    /**
     * Sets the key.
     *
     * The min/max length(s) of the key depends on the cipher which is used.
     * If the key not fits the length(s) of the cipher it will paded with null bytes
     * up to the closest valid key length.  If the key is more than max length,
     * we trim the excess bits.
     *
     * If the key is not explicitly set, it'll be assumed to be all null bytes.
     *
     * @access public
     * @param string $key
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function setKey($key)
    {
        if (!$this->explicit_key_length) {
            $this->setKeyLength(strlen($key) << 3);
            $this->explicit_key_length = false;
        }

        $this->key = $key;
        $this->changed = true;
        $this->_setEngine();
    }

    /**
     * Sets the password.
     *
     * Depending on what $method is set to, setPassword()'s (optional) parameters are as follows:
     *     {@link http://en.wikipedia.org/wiki/PBKDF2 pbkdf2} or pbkdf1:
     *         $hash, $salt, $count, $dkLen
     *
     *         Where $hash (default = sha1) currently supports the following hashes: see: Crypt/Hash.php
     *
     * @see Crypt/Hash.php
     * @param string $password
     * @param string $method
     * @return bool
     * @access public
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function setPassword($password, $method = 'pbkdf2')
    {
        $key = '';

        switch ($method) {
            default: // 'pbkdf2' or 'pbkdf1'
                $func_args = func_get_args();

                // Hash function
                $hash = isset($func_args[2]) ? $func_args[2] : 'sha1';

                // WPA and WPA2 use the SSID as the salt
                $salt = isset($func_args[3]) ? $func_args[3] : $this->password_default_salt;

                // RFC2898#section-4.2 uses 1,000 iterations by default
                // WPA and WPA2 use 4,096.
                $count = isset($func_args[4]) ? $func_args[4] : 1000;

                // Keylength
                if (isset($func_args[5]) && $func_args[5] > 0) {
                    $dkLen = $func_args[5];
                } else {
                    $dkLen = $method == 'pbkdf1' ? 2 * $this->key_length : $this->key_length;
                }

                switch (true) {
                    case $method == 'pbkdf1':
						/*
                        if (!class_exists('Crypt_Hash')) {
                            include_once 'Crypt/Hash.php';
                        }
						*/
                        $hashObj = new Crypt_Hash();
                        $hashObj->setHash($hash);
                        if ($dkLen > $hashObj->getLength()) {
                            user_error('Derived key too long');
                            return false;
                        }
                        $t = $password . $salt;
                        for ($i = 0; $i < $count; ++$i) {
                            $t = $hashObj->hash($t);
                        }
                        $key = substr($t, 0, $dkLen);

                        $this->setKey(substr($key, 0, $dkLen >> 1));
                        $this->setIV(substr($key, $dkLen >> 1));

                        return true;
                    // Determining if php[>=5.5.0]'s hash_pbkdf2() function avail- and useable
                    case !function_exists('hash_pbkdf2'):
                    case !function_exists('hash_algos'):
                    case !in_array($hash, hash_algos()):
						/*
                        if (!class_exists('Crypt_Hash')) {
                            include_once 'Crypt/Hash.php';
                        }
						*/
                        $i = 1;
                        $hmac = new Crypt_Hash();
                        $hmac->setHash($hash);
                        $hmac->setKey($password);
                        while (strlen($key) < $dkLen) {
                            $f = $u = $hmac->hash($salt . pack('N', $i++));
                            for ($j = 2; $j <= $count; ++$j) {
                                $u = $hmac->hash($u);
                                $f^= $u;
                            }
                            $key.= $f;
                        }
                        $key = substr($key, 0, $dkLen);
                        break;
                    default:
                        $key = hash_pbkdf2($hash, $password, $salt, $count, $dkLen, true);
                }
        }

        $this->setKey($key);

        return true;
    }

    /**
     * Encrypts a message.
     *
     * $plaintext will be padded with additional bytes such that it's length is a multiple of the block size. Other cipher
     * implementations may or may not pad in the same manner.  Other common approaches to padding and the reasons why it's
     * necessary are discussed in the following
     * URL:
     *
     * {@link http://www.di-mgt.com.au/cryptopad.html http://www.di-mgt.com.au/cryptopad.html}
     *
     * An alternative to padding is to, separately, send the length of the file.  This is what SSH, in fact, does.
     * strlen($plaintext) will still need to be a multiple of the block size, however, arbitrary values can be added to make it that
     * length.
     *
     * @see self::decrypt()
     * @access public
     * @param string $plaintext
     * @return string $ciphertext
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function encrypt($plaintext)
    {
        if ($this->paddable) {
            $plaintext = $this->_pad($plaintext);
        }

        if ($this->engine === CRYPT_ENGINE_OPENSSL) {
            if ($this->changed) {
                $this->_clearBuffers();
                $this->changed = false;
            }
            switch ($this->mode) {
                case CRYPT_MODE_STREAM:
                    return openssl_encrypt($plaintext, $this->cipher_name_openssl, $this->key, $this->openssl_options);
                case CRYPT_MODE_ECB:
                    $result = openssl_encrypt($plaintext, $this->cipher_name_openssl, $this->key, $this->openssl_options);
                    return !defined('OPENSSL_RAW_DATA') ? substr($result, 0, -$this->block_size) : $result;
                case CRYPT_MODE_CBC:
                    $result = openssl_encrypt($plaintext, $this->cipher_name_openssl, $this->key, $this->openssl_options, $this->encryptIV);
                    if (!defined('OPENSSL_RAW_DATA')) {
                        $result = substr($result, 0, -$this->block_size);
                    }
                    if ($this->continuousBuffer) {
                        $this->encryptIV = substr($result, -$this->block_size);
                    }
                    return $result;
                case CRYPT_MODE_CTR:
                    return $this->_openssl_ctr_process($plaintext, $this->encryptIV, $this->enbuffer);
                case CRYPT_MODE_CFB:
                    // cfb loosely routines inspired by openssl's:
                    // {@link http://cvs.openssl.org/fileview?f=openssl/crypto/modes/cfb128.c&v=1.3.2.2.2.1}
                    $ciphertext = '';
                    if ($this->continuousBuffer) {
                        $iv = &$this->encryptIV;
                        $pos = &$this->enbuffer['pos'];
                    } else {
                        $iv = $this->encryptIV;
                        $pos = 0;
                    }
                    $len = strlen($plaintext);
                    $i = 0;
                    if ($pos) {
                        $orig_pos = $pos;
                        $max = $this->block_size - $pos;
                        if ($len >= $max) {
                            $i = $max;
                            $len-= $max;
                            $pos = 0;
                        } else {
                            $i = $len;
                            $pos+= $len;
                            $len = 0;
                        }
                        // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                        $ciphertext = substr($iv, $orig_pos) ^ $plaintext;
                        $iv = substr_replace($iv, $ciphertext, $orig_pos, $i);
                        $plaintext = substr($plaintext, $i);
                    }

                    $overflow = $len % $this->block_size;

                    if ($overflow) {
                        $ciphertext.= openssl_encrypt(substr($plaintext, 0, -$overflow) . str_repeat("\0", $this->block_size), $this->cipher_name_openssl, $this->key, $this->openssl_options, $iv);
                        $iv = $this->_string_pop($ciphertext, $this->block_size);

                        $size = $len - $overflow;
                        $block = $iv ^ substr($plaintext, -$overflow);
                        $iv = substr_replace($iv, $block, 0, $overflow);
                        $ciphertext.= $block;
                        $pos = $overflow;
                    } elseif ($len) {
                        $ciphertext = openssl_encrypt($plaintext, $this->cipher_name_openssl, $this->key, $this->openssl_options, $iv);
                        $iv = substr($ciphertext, -$this->block_size);
                    }

                    return $ciphertext;
                case CRYPT_MODE_OFB:
                    return $this->_openssl_ofb_process($plaintext, $this->encryptIV, $this->enbuffer);
            }
        }

        if ($this->engine === CRYPT_ENGINE_MCRYPT) {
            if ($this->changed) {
                $this->_setupMcrypt();
                $this->changed = false;
            }
            if ($this->enchanged) {
                @mcrypt_generic_init($this->enmcrypt, $this->key, $this->encryptIV);
                $this->enchanged = false;
            }

            // re: {@link http://phpseclib.sourceforge.net/cfb-demo.phps}
            // using mcrypt's default handing of CFB the above would output two different things.  using phpseclib's
            // rewritten CFB implementation the above outputs the same thing twice.
            if ($this->mode == CRYPT_MODE_CFB && $this->continuousBuffer) {
                $block_size = $this->block_size;
                $iv = &$this->encryptIV;
                $pos = &$this->enbuffer['pos'];
                $len = strlen($plaintext);
                $ciphertext = '';
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len-= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos+= $len;
                        $len = 0;
                    }
                    $ciphertext = substr($iv, $orig_pos) ^ $plaintext;
                    $iv = substr_replace($iv, $ciphertext, $orig_pos, $i);
                    $this->enbuffer['enmcrypt_init'] = true;
                }
                if ($len >= $block_size) {
                    if ($this->enbuffer['enmcrypt_init'] === false || $len > $this->cfb_init_len) {
                        if ($this->enbuffer['enmcrypt_init'] === true) {
                            @mcrypt_generic_init($this->enmcrypt, $this->key, $iv);
                            $this->enbuffer['enmcrypt_init'] = false;
                        }
                        $ciphertext.= @mcrypt_generic($this->enmcrypt, substr($plaintext, $i, $len - $len % $block_size));
                        $iv = substr($ciphertext, -$block_size);
                        $len%= $block_size;
                    } else {
                        while ($len >= $block_size) {
                            $iv = @mcrypt_generic($this->ecb, $iv) ^ substr($plaintext, $i, $block_size);
                            $ciphertext.= $iv;
                            $len-= $block_size;
                            $i+= $block_size;
                        }
                    }
                }

                if ($len) {
                    $iv = @mcrypt_generic($this->ecb, $iv);
                    $block = $iv ^ substr($plaintext, -$len);
                    $iv = substr_replace($iv, $block, 0, $len);
                    $ciphertext.= $block;
                    $pos = $len;
                }

                return $ciphertext;
            }

            $ciphertext = @mcrypt_generic($this->enmcrypt, $plaintext);

            if (!$this->continuousBuffer) {
                @mcrypt_generic_init($this->enmcrypt, $this->key, $this->encryptIV);
            }

            return $ciphertext;
        }

        if ($this->changed) {
            $this->_setup();
            $this->changed = false;
        }
        if ($this->use_inline_crypt) {
            $inline = $this->inline_crypt;
            return $inline('encrypt', $this, $plaintext);
        }

        $buffer = &$this->enbuffer;
        $block_size = $this->block_size;
        $ciphertext = '';
        switch ($this->mode) {
            case CRYPT_MODE_ECB:
                for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                    $ciphertext.= $this->_encryptBlock(substr($plaintext, $i, $block_size));
                }
                break;
            case CRYPT_MODE_CBC:
                $xor = $this->encryptIV;
                for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                    $block = substr($plaintext, $i, $block_size);
                    $block = $this->_encryptBlock($block ^ $xor);
                    $xor = $block;
                    $ciphertext.= $block;
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                }
                break;
            case CRYPT_MODE_CTR:
                $xor = $this->encryptIV;
                if (strlen($buffer['ciphertext'])) {
                    for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                        $block = substr($plaintext, $i, $block_size);
                        if (strlen($block) > strlen($buffer['ciphertext'])) {
                            $buffer['ciphertext'].= $this->_encryptBlock($xor);
                        }
                        $this->_increment_str($xor);
                        $key = $this->_string_shift($buffer['ciphertext'], $block_size);
                        $ciphertext.= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                        $block = substr($plaintext, $i, $block_size);
                        $key = $this->_encryptBlock($xor);
                        $this->_increment_str($xor);
                        $ciphertext.= $block ^ $key;
                    }
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                    if ($start = strlen($plaintext) % $block_size) {
                        $buffer['ciphertext'] = substr($key, $start) . $buffer['ciphertext'];
                    }
                }
                break;
            case CRYPT_MODE_CFB:
                // cfb loosely routines inspired by openssl's:
                // {@link http://cvs.openssl.org/fileview?f=openssl/crypto/modes/cfb128.c&v=1.3.2.2.2.1}
                if ($this->continuousBuffer) {
                    $iv = &$this->encryptIV;
                    $pos = &$buffer['pos'];
                } else {
                    $iv = $this->encryptIV;
                    $pos = 0;
                }
                $len = strlen($plaintext);
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len-= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos+= $len;
                        $len = 0;
                    }
                    // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                    $ciphertext = substr($iv, $orig_pos) ^ $plaintext;
                    $iv = substr_replace($iv, $ciphertext, $orig_pos, $i);
                }
                while ($len >= $block_size) {
                    $iv = $this->_encryptBlock($iv) ^ substr($plaintext, $i, $block_size);
                    $ciphertext.= $iv;
                    $len-= $block_size;
                    $i+= $block_size;
                }
                if ($len) {
                    $iv = $this->_encryptBlock($iv);
                    $block = $iv ^ substr($plaintext, $i);
                    $iv = substr_replace($iv, $block, 0, $len);
                    $ciphertext.= $block;
                    $pos = $len;
                }
                break;
            case CRYPT_MODE_OFB:
                $xor = $this->encryptIV;
                if (strlen($buffer['xor'])) {
                    for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                        $block = substr($plaintext, $i, $block_size);
                        if (strlen($block) > strlen($buffer['xor'])) {
                            $xor = $this->_encryptBlock($xor);
                            $buffer['xor'].= $xor;
                        }
                        $key = $this->_string_shift($buffer['xor'], $block_size);
                        $ciphertext.= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                        $xor = $this->_encryptBlock($xor);
                        $ciphertext.= substr($plaintext, $i, $block_size) ^ $xor;
                    }
                    $key = $xor;
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                    if ($start = strlen($plaintext) % $block_size) {
                        $buffer['xor'] = substr($key, $start) . $buffer['xor'];
                    }
                }
                break;
            case CRYPT_MODE_STREAM:
                $ciphertext = $this->_encryptBlock($plaintext);
                break;
        }

        return $ciphertext;
    }

    /**
     * Decrypts a message.
     *
     * If strlen($ciphertext) is not a multiple of the block size, null bytes will be added to the end of the string until
     * it is.
     *
     * @see self::encrypt()
     * @access public
     * @param string $ciphertext
     * @return string $plaintext
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function decrypt($ciphertext)
    {
        if ($this->paddable) {
            // we pad with chr(0) since that's what mcrypt_generic does.  to quote from {@link http://www.php.net/function.mcrypt-generic}:
            // "The data is padded with "\0" to make sure the length of the data is n * blocksize."
            $ciphertext = str_pad($ciphertext, strlen($ciphertext) + ($this->block_size - strlen($ciphertext) % $this->block_size) % $this->block_size, chr(0));
        }

        if ($this->engine === CRYPT_ENGINE_OPENSSL) {
            if ($this->changed) {
                $this->_clearBuffers();
                $this->changed = false;
            }
            switch ($this->mode) {
                case CRYPT_MODE_STREAM:
                    $plaintext = openssl_decrypt($ciphertext, $this->cipher_name_openssl, $this->key, $this->openssl_options);
                    break;
                case CRYPT_MODE_ECB:
                    if (!defined('OPENSSL_RAW_DATA')) {
                        $ciphertext.= openssl_encrypt('', $this->cipher_name_openssl_ecb, $this->key, true);
                    }
                    $plaintext = openssl_decrypt($ciphertext, $this->cipher_name_openssl, $this->key, $this->openssl_options);
                    break;
                case CRYPT_MODE_CBC:
                    if (!defined('OPENSSL_RAW_DATA')) {
                        $padding = str_repeat(chr($this->block_size), $this->block_size) ^ substr($ciphertext, -$this->block_size);
                        $ciphertext.= substr(openssl_encrypt($padding, $this->cipher_name_openssl_ecb, $this->key, true), 0, $this->block_size);
                        $offset = 2 * $this->block_size;
                    } else {
                        $offset = $this->block_size;
                    }
                    $plaintext = openssl_decrypt($ciphertext, $this->cipher_name_openssl, $this->key, $this->openssl_options, $this->decryptIV);
                    if ($this->continuousBuffer) {
                        $this->decryptIV = substr($ciphertext, -$offset, $this->block_size);
                    }
                    break;
                case CRYPT_MODE_CTR:
                    $plaintext = $this->_openssl_ctr_process($ciphertext, $this->decryptIV, $this->debuffer);
                    break;
                case CRYPT_MODE_CFB:
                    // cfb loosely routines inspired by openssl's:
                    // {@link http://cvs.openssl.org/fileview?f=openssl/crypto/modes/cfb128.c&v=1.3.2.2.2.1}
                    $plaintext = '';
                    if ($this->continuousBuffer) {
                        $iv = &$this->decryptIV;
                        $pos = &$this->buffer['pos'];
                    } else {
                        $iv = $this->decryptIV;
                        $pos = 0;
                    }
                    $len = strlen($ciphertext);
                    $i = 0;
                    if ($pos) {
                        $orig_pos = $pos;
                        $max = $this->block_size - $pos;
                        if ($len >= $max) {
                            $i = $max;
                            $len-= $max;
                            $pos = 0;
                        } else {
                            $i = $len;
                            $pos+= $len;
                            $len = 0;
                        }
                        // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $this->blocksize
                        $plaintext = substr($iv, $orig_pos) ^ $ciphertext;
                        $iv = substr_replace($iv, substr($ciphertext, 0, $i), $orig_pos, $i);
                        $ciphertext = substr($ciphertext, $i);
                    }
                    $overflow = $len % $this->block_size;
                    if ($overflow) {
                        $plaintext.= openssl_decrypt(substr($ciphertext, 0, -$overflow), $this->cipher_name_openssl, $this->key, $this->openssl_options, $iv);
                        if ($len - $overflow) {
                            $iv = substr($ciphertext, -$overflow - $this->block_size, -$overflow);
                        }
                        $iv = openssl_encrypt(str_repeat("\0", $this->block_size), $this->cipher_name_openssl, $this->key, $this->openssl_options, $iv);
                        $plaintext.= $iv ^ substr($ciphertext, -$overflow);
                        $iv = substr_replace($iv, substr($ciphertext, -$overflow), 0, $overflow);
                        $pos = $overflow;
                    } elseif ($len) {
                        $plaintext.= openssl_decrypt($ciphertext, $this->cipher_name_openssl, $this->key, $this->openssl_options, $iv);
                        $iv = substr($ciphertext, -$this->block_size);
                    }
                    break;
                case CRYPT_MODE_OFB:
                    $plaintext = $this->_openssl_ofb_process($ciphertext, $this->decryptIV, $this->debuffer);
            }

            return $this->paddable ? $this->_unpad($plaintext) : $plaintext;
        }

        if ($this->engine === CRYPT_ENGINE_MCRYPT) {
            $block_size = $this->block_size;
            if ($this->changed) {
                $this->_setupMcrypt();
                $this->changed = false;
            }
            if ($this->dechanged) {
                @mcrypt_generic_init($this->demcrypt, $this->key, $this->decryptIV);
                $this->dechanged = false;
            }

            if ($this->mode == CRYPT_MODE_CFB && $this->continuousBuffer) {
                $iv = &$this->decryptIV;
                $pos = &$this->debuffer['pos'];
                $len = strlen($ciphertext);
                $plaintext = '';
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len-= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos+= $len;
                        $len = 0;
                    }
                    // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                    $plaintext = substr($iv, $orig_pos) ^ $ciphertext;
                    $iv = substr_replace($iv, substr($ciphertext, 0, $i), $orig_pos, $i);
                }
                if ($len >= $block_size) {
                    $cb = substr($ciphertext, $i, $len - $len % $block_size);
                    $plaintext.= @mcrypt_generic($this->ecb, $iv . $cb) ^ $cb;
                    $iv = substr($cb, -$block_size);
                    $len%= $block_size;
                }
                if ($len) {
                    $iv = @mcrypt_generic($this->ecb, $iv);
                    $plaintext.= $iv ^ substr($ciphertext, -$len);
                    $iv = substr_replace($iv, substr($ciphertext, -$len), 0, $len);
                    $pos = $len;
                }

                return $plaintext;
            }

            $plaintext = @mdecrypt_generic($this->demcrypt, $ciphertext);

            if (!$this->continuousBuffer) {
                @mcrypt_generic_init($this->demcrypt, $this->key, $this->decryptIV);
            }

            return $this->paddable ? $this->_unpad($plaintext) : $plaintext;
        }

        if ($this->changed) {
            $this->_setup();
            $this->changed = false;
        }
        if ($this->use_inline_crypt) {
            $inline = $this->inline_crypt;
            return $inline('decrypt', $this, $ciphertext);
        }

        $block_size = $this->block_size;

        $buffer = &$this->debuffer;
        $plaintext = '';
        switch ($this->mode) {
            case CRYPT_MODE_ECB:
                for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                    $plaintext.= $this->_decryptBlock(substr($ciphertext, $i, $block_size));
                }
                break;
            case CRYPT_MODE_CBC:
                $xor = $this->decryptIV;
                for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                    $block = substr($ciphertext, $i, $block_size);
                    $plaintext.= $this->_decryptBlock($block) ^ $xor;
                    $xor = $block;
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                }
                break;
            case CRYPT_MODE_CTR:
                $xor = $this->decryptIV;
                if (strlen($buffer['ciphertext'])) {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        if (strlen($block) > strlen($buffer['ciphertext'])) {
                            $buffer['ciphertext'].= $this->_encryptBlock($xor);
                            $this->_increment_str($xor);
                        }
                        $key = $this->_string_shift($buffer['ciphertext'], $block_size);
                        $plaintext.= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        $key = $this->_encryptBlock($xor);
                        $this->_increment_str($xor);
                        $plaintext.= $block ^ $key;
                    }
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                    if ($start = strlen($ciphertext) % $block_size) {
                        $buffer['ciphertext'] = substr($key, $start) . $buffer['ciphertext'];
                    }
                }
                break;
            case CRYPT_MODE_CFB:
                if ($this->continuousBuffer) {
                    $iv = &$this->decryptIV;
                    $pos = &$buffer['pos'];
                } else {
                    $iv = $this->decryptIV;
                    $pos = 0;
                }
                $len = strlen($ciphertext);
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len-= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos+= $len;
                        $len = 0;
                    }
                    // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                    $plaintext = substr($iv, $orig_pos) ^ $ciphertext;
                    $iv = substr_replace($iv, substr($ciphertext, 0, $i), $orig_pos, $i);
                }
                while ($len >= $block_size) {
                    $iv = $this->_encryptBlock($iv);
                    $cb = substr($ciphertext, $i, $block_size);
                    $plaintext.= $iv ^ $cb;
                    $iv = $cb;
                    $len-= $block_size;
                    $i+= $block_size;
                }
                if ($len) {
                    $iv = $this->_encryptBlock($iv);
                    $plaintext.= $iv ^ substr($ciphertext, $i);
                    $iv = substr_replace($iv, substr($ciphertext, $i), 0, $len);
                    $pos = $len;
                }
                break;
            case CRYPT_MODE_OFB:
                $xor = $this->decryptIV;
                if (strlen($buffer['xor'])) {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        if (strlen($block) > strlen($buffer['xor'])) {
                            $xor = $this->_encryptBlock($xor);
                            $buffer['xor'].= $xor;
                        }
                        $key = $this->_string_shift($buffer['xor'], $block_size);
                        $plaintext.= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < strlen($ciphertext); $i+=$block_size) {
                        $xor = $this->_encryptBlock($xor);
                        $plaintext.= substr($ciphertext, $i, $block_size) ^ $xor;
                    }
                    $key = $xor;
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                    if ($start = strlen($ciphertext) % $block_size) {
                        $buffer['xor'] = substr($key, $start) . $buffer['xor'];
                    }
                }
                break;
            case CRYPT_MODE_STREAM:
                $plaintext = $this->_decryptBlock($ciphertext);
                break;
        }
        return $this->paddable ? $this->_unpad($plaintext) : $plaintext;
    }

    /**
     * OpenSSL CTR Processor
     *
     * PHP's OpenSSL bindings do not operate in continuous mode so we'll wrap around it. Since the keystream
     * for CTR is the same for both encrypting and decrypting this function is re-used by both Crypt_Base::encrypt()
     * and Crypt_Base::decrypt(). Also, OpenSSL doesn't implement CTR for all of it's symmetric ciphers so this
     * function will emulate CTR with ECB when necessary.
     *
     * @see self::encrypt()
     * @see self::decrypt()
     * @param string $plaintext
     * @param string $encryptIV
     * @param array $buffer
     * @return string
     * @access private
     */
    function _openssl_ctr_process($plaintext, &$encryptIV, &$buffer)
    {
        $ciphertext = '';

        $block_size = $this->block_size;
        $key = $this->key;

        if ($this->openssl_emulate_ctr) {
            $xor = $encryptIV;
            if (strlen($buffer['ciphertext'])) {
                for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                    $block = substr($plaintext, $i, $block_size);
                    if (strlen($block) > strlen($buffer['ciphertext'])) {
                        $result = openssl_encrypt($xor, $this->cipher_name_openssl_ecb, $key, $this->openssl_options);
                        $result = !defined('OPENSSL_RAW_DATA') ? substr($result, 0, -$this->block_size) : $result;
                        $buffer['ciphertext'].= $result;
                    }
                    $this->_increment_str($xor);
                    $otp = $this->_string_shift($buffer['ciphertext'], $block_size);
                    $ciphertext.= $block ^ $otp;
                }
            } else {
                for ($i = 0; $i < strlen($plaintext); $i+=$block_size) {
                    $block = substr($plaintext, $i, $block_size);
                    $otp = openssl_encrypt($xor, $this->cipher_name_openssl_ecb, $key, $this->openssl_options);
                    $otp = !defined('OPENSSL_RAW_DATA') ? substr($otp, 0, -$this->block_size) : $otp;
                    $this->_increment_str($xor);
                    $ciphertext.= $block ^ $otp;
                }
            }
            if ($this->continuousBuffer) {
                $encryptIV = $xor;
                if ($start = strlen($plaintext) % $block_size) {
                    $buffer['ciphertext'] = substr($key, $start) . $buffer['ciphertext'];
                }
            }

            return $ciphertext;
        }

        if (strlen($buffer['ciphertext'])) {
            $ciphertext = $plaintext ^ $this->_string_shift($buffer['ciphertext'], strlen($plaintext));
            $plaintext = substr($plaintext, strlen($ciphertext));

            if (!strlen($plaintext)) {
                return $ciphertext;
            }
        }

        $overflow = strlen($plaintext) % $block_size;
        if ($overflow) {
            $plaintext2 = $this->_string_pop($plaintext, $overflow); // ie. trim $plaintext to a multiple of $block_size and put rest of $plaintext in $plaintext2
            $encrypted = openssl_encrypt($plaintext . str_repeat("\0", $block_size), $this->cipher_name_openssl, $key, $this->openssl_options, $encryptIV);
            $temp = $this->_string_pop($encrypted, $block_size);
            $ciphertext.= $encrypted . ($plaintext2 ^ $temp);
            if ($this->continuousBuffer) {
                $buffer['ciphertext'] = substr($temp, $overflow);
                $encryptIV = $temp;
            }
        } elseif (!strlen($buffer['ciphertext'])) {
            $ciphertext.= openssl_encrypt($plaintext . str_repeat("\0", $block_size), $this->cipher_name_openssl, $key, $this->openssl_options, $encryptIV);
            $temp = $this->_string_pop($ciphertext, $block_size);
            if ($this->continuousBuffer) {
                $encryptIV = $temp;
            }
        }
        if ($this->continuousBuffer) {
            if (!defined('OPENSSL_RAW_DATA')) {
                $encryptIV.= openssl_encrypt('', $this->cipher_name_openssl_ecb, $key, $this->openssl_options);
            }
            $encryptIV = openssl_decrypt($encryptIV, $this->cipher_name_openssl_ecb, $key, $this->openssl_options);
            if ($overflow) {
                $this->_increment_str($encryptIV);
            }
        }

        return $ciphertext;
    }

    /**
     * OpenSSL OFB Processor
     *
     * PHP's OpenSSL bindings do not operate in continuous mode so we'll wrap around it. Since the keystream
     * for OFB is the same for both encrypting and decrypting this function is re-used by both Crypt_Base::encrypt()
     * and Crypt_Base::decrypt().
     *
     * @see self::encrypt()
     * @see self::decrypt()
     * @param string $plaintext
     * @param string $encryptIV
     * @param array $buffer
     * @return string
     * @access private
     */
    function _openssl_ofb_process($plaintext, &$encryptIV, &$buffer)
    {
        if (strlen($buffer['xor'])) {
            $ciphertext = $plaintext ^ $buffer['xor'];
            $buffer['xor'] = substr($buffer['xor'], strlen($ciphertext));
            $plaintext = substr($plaintext, strlen($ciphertext));
        } else {
            $ciphertext = '';
        }

        $block_size = $this->block_size;

        $len = strlen($plaintext);
        $key = $this->key;
        $overflow = $len % $block_size;

        if (strlen($plaintext)) {
            if ($overflow) {
                $ciphertext.= openssl_encrypt(substr($plaintext, 0, -$overflow) . str_repeat("\0", $block_size), $this->cipher_name_openssl, $key, $this->openssl_options, $encryptIV);
                $xor = $this->_string_pop($ciphertext, $block_size);
                if ($this->continuousBuffer) {
                    $encryptIV = $xor;
                }
                $ciphertext.= $this->_string_shift($xor, $overflow) ^ substr($plaintext, -$overflow);
                if ($this->continuousBuffer) {
                    $buffer['xor'] = $xor;
                }
            } else {
                $ciphertext = openssl_encrypt($plaintext, $this->cipher_name_openssl, $key, $this->openssl_options, $encryptIV);
                if ($this->continuousBuffer) {
                    $encryptIV = substr($ciphertext, -$block_size) ^ substr($plaintext, -$block_size);
                }
            }
        }

        return $ciphertext;
    }

    /**
     * phpseclib <-> OpenSSL Mode Mapper
     *
     * May need to be overwritten by classes extending this one in some cases
     *
     * @return int
     * @access private
     */
    function _openssl_translate_mode()
    {
        switch ($this->mode) {
            case CRYPT_MODE_ECB:
                return 'ecb';
            case CRYPT_MODE_CBC:
                return 'cbc';
            case CRYPT_MODE_CTR:
                return 'ctr';
            case CRYPT_MODE_CFB:
                return 'cfb';
            case CRYPT_MODE_OFB:
                return 'ofb';
        }
    }

    /**
     * Pad "packets".
     *
     * Block ciphers working by encrypting between their specified [$this->]block_size at a time
     * If you ever need to encrypt or decrypt something that isn't of the proper length, it becomes necessary to
     * pad the input so that it is of the proper length.
     *
     * Padding is enabled by default.  Sometimes, however, it is undesirable to pad strings.  Such is the case in SSH,
     * where "packets" are padded with random bytes before being encrypted.  Unpad these packets and you risk stripping
     * away characters that shouldn't be stripped away. (SSH knows how many bytes are added because the length is
     * transmitted separately)
     *
     * @see self::disablePadding()
     * @access public
     */
    function enablePadding()
    {
        $this->padding = true;
    }

    /**
     * Do not pad packets.
     *
     * @see self::enablePadding()
     * @access public
     */
    function disablePadding()
    {
        $this->padding = false;
    }

    /**
     * Treat consecutive "packets" as if they are a continuous buffer.
     *
     * Say you have a 32-byte plaintext $plaintext.  Using the default behavior, the two following code snippets
     * will yield different outputs:
     *
     * <code>
     *    echo $rijndael->encrypt(substr($plaintext,  0, 16));
     *    echo $rijndael->encrypt(substr($plaintext, 16, 16));
     * </code>
     * <code>
     *    echo $rijndael->encrypt($plaintext);
     * </code>
     *
     * The solution is to enable the continuous buffer.  Although this will resolve the above discrepancy, it creates
     * another, as demonstrated with the following:
     *
     * <code>
     *    $rijndael->encrypt(substr($plaintext, 0, 16));
     *    echo $rijndael->decrypt($rijndael->encrypt(substr($plaintext, 16, 16)));
     * </code>
     * <code>
     *    echo $rijndael->decrypt($rijndael->encrypt(substr($plaintext, 16, 16)));
     * </code>
     *
     * With the continuous buffer disabled, these would yield the same output.  With it enabled, they yield different
     * outputs.  The reason is due to the fact that the initialization vector's change after every encryption /
     * decryption round when the continuous buffer is enabled.  When it's disabled, they remain constant.
     *
     * Put another way, when the continuous buffer is enabled, the state of the Crypt_*() object changes after each
     * encryption / decryption round, whereas otherwise, it'd remain constant.  For this reason, it's recommended that
     * continuous buffers not be used.  They do offer better security and are, in fact, sometimes required (SSH uses them),
     * however, they are also less intuitive and more likely to cause you problems.
     *
     * @see self::disableContinuousBuffer()
     * @access public
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function enableContinuousBuffer()
    {
        if ($this->mode == CRYPT_MODE_ECB) {
            return;
        }

        $this->continuousBuffer = true;

        $this->_setEngine();
    }

    /**
     * Treat consecutive packets as if they are a discontinuous buffer.
     *
     * The default behavior.
     *
     * @see self::enableContinuousBuffer()
     * @access public
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function disableContinuousBuffer()
    {
        if ($this->mode == CRYPT_MODE_ECB) {
            return;
        }
        if (!$this->continuousBuffer) {
            return;
        }

        $this->continuousBuffer = false;
        $this->changed = true;

        $this->_setEngine();
    }

    /**
     * Test for engine validity
     *
     * @see self::Crypt_Base()
     * @param int $engine
     * @access public
     * @return bool
     */
    function isValidEngine($engine)
    {
        switch ($engine) {
            case CRYPT_ENGINE_OPENSSL:
                if ($this->mode == CRYPT_MODE_STREAM && $this->continuousBuffer) {
                    return false;
                }
                $this->openssl_emulate_ctr = false;
                $result = $this->cipher_name_openssl &&
                          extension_loaded('openssl') &&
                          // PHP 5.3.0 - 5.3.2 did not let you set IV's
                          version_compare(PHP_VERSION, '5.3.3', '>=');
                if (!$result) {
                    return false;
                }

                // prior to PHP 5.4.0 OPENSSL_RAW_DATA and OPENSSL_ZERO_PADDING were not defined. instead of expecting an integer
                // $options openssl_encrypt expected a boolean $raw_data.
                if (!defined('OPENSSL_RAW_DATA')) {
                    $this->openssl_options = true;
                } else {
                    $this->openssl_options = OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING;
                }

                $methods = openssl_get_cipher_methods();
                if (in_array($this->cipher_name_openssl, $methods)) {
                    return true;
                }
                // not all of openssl's symmetric cipher's support ctr. for those
                // that don't we'll emulate it
                switch ($this->mode) {
                    case CRYPT_MODE_CTR:
                        if (in_array($this->cipher_name_openssl_ecb, $methods)) {
                            $this->openssl_emulate_ctr = true;
                            return true;
                        }
                }
                return false;
            case CRYPT_ENGINE_MCRYPT:
                return $this->cipher_name_mcrypt &&
                       extension_loaded('mcrypt') &&
                       in_array($this->cipher_name_mcrypt, @mcrypt_list_algorithms());
            case CRYPT_ENGINE_INTERNAL:
                return true;
        }

        return false;
    }

    /**
     * Sets the preferred crypt engine
     *
     * Currently, $engine could be:
     *
     * - CRYPT_ENGINE_OPENSSL  [very fast]
     *
     * - CRYPT_ENGINE_MCRYPT   [fast]
     *
     * - CRYPT_ENGINE_INTERNAL [slow]
     *
     * If the preferred crypt engine is not available the fastest available one will be used
     *
     * @see self::Crypt_Base()
     * @param int $engine
     * @access public
     */
    function setPreferredEngine($engine)
    {
        switch ($engine) {
            //case CRYPT_ENGINE_OPENSSL:
            case CRYPT_ENGINE_MCRYPT:
            case CRYPT_ENGINE_INTERNAL:
                $this->preferredEngine = $engine;
                break;
            default:
                $this->preferredEngine = CRYPT_ENGINE_OPENSSL;
        }

        $this->_setEngine();
    }

    /**
     * Returns the engine currently being utilized
     *
     * @see self::_setEngine()
     * @access public
     */
    function getEngine()
    {
        return $this->engine;
    }

    /**
     * Sets the engine as appropriate
     *
     * @see self::Crypt_Base()
     * @access private
     */
    function _setEngine()
    {
        $this->engine = null;

        $candidateEngines = array(
            $this->preferredEngine,
            CRYPT_ENGINE_OPENSSL,
            CRYPT_ENGINE_MCRYPT
        );
        foreach ($candidateEngines as $engine) {
            if ($this->isValidEngine($engine)) {
                $this->engine = $engine;
                break;
            }
        }
        if (!$this->engine) {
            $this->engine = CRYPT_ENGINE_INTERNAL;
        }

        if ($this->engine != CRYPT_ENGINE_MCRYPT && $this->enmcrypt) {
            // Closing the current mcrypt resource(s). _mcryptSetup() will, if needed,
            // (re)open them with the module named in $this->cipher_name_mcrypt
            @mcrypt_module_close($this->enmcrypt);
            @mcrypt_module_close($this->demcrypt);
            $this->enmcrypt = null;
            $this->demcrypt = null;

            if ($this->ecb) {
                @mcrypt_module_close($this->ecb);
                $this->ecb = null;
            }
        }

        $this->changed = true;
    }

    /**
     * Encrypts a block
     *
     * @access private
     * @param string $in
     * @return string
     * @internal Must be extended by the child Crypt_* class
     */
    function _encryptBlock($in)
    {
        user_error((version_compare(PHP_VERSION, '5.0.0', '>=')  ? __METHOD__ : __FUNCTION__)  . '() must extend by class ' . get_class($this), E_USER_ERROR);
    }

    /**
     * Decrypts a block
     *
     * @access private
     * @param string $in
     * @return string
     * @internal Must be extended by the child Crypt_* class
     */
    function _decryptBlock($in)
    {
        user_error((version_compare(PHP_VERSION, '5.0.0', '>=')  ? __METHOD__ : __FUNCTION__)  . '() must extend by class ' . get_class($this), E_USER_ERROR);
    }

    /**
     * Setup the key (expansion)
     *
     * Only used if $engine == CRYPT_ENGINE_INTERNAL
     *
     * @see self::_setup()
     * @access private
     * @internal Must be extended by the child Crypt_* class
     */
    function _setupKey()
    {
        user_error((version_compare(PHP_VERSION, '5.0.0', '>=')  ? __METHOD__ : __FUNCTION__)  . '() must extend by class ' . get_class($this), E_USER_ERROR);
    }

    /**
     * Setup the CRYPT_ENGINE_INTERNAL $engine
     *
     * (re)init, if necessary, the internal cipher $engine and flush all $buffers
     * Used (only) if $engine == CRYPT_ENGINE_INTERNAL
     *
     * _setup() will be called each time if $changed === true
     * typically this happens when using one or more of following public methods:
     *
     * - setKey()
     *
     * - setIV()
     *
     * - disableContinuousBuffer()
     *
     * - First run of encrypt() / decrypt() with no init-settings
     *
     * @see self::setKey()
     * @see self::setIV()
     * @see self::disableContinuousBuffer()
     * @access private
     * @internal _setup() is always called before en/decryption.
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function _setup()
    {
        $this->_clearBuffers();
        $this->_setupKey();

        if ($this->use_inline_crypt) {
            $this->_setupInlineCrypt();
        }
    }

    /**
     * Setup the CRYPT_ENGINE_MCRYPT $engine
     *
     * (re)init, if necessary, the (ext)mcrypt resources and flush all $buffers
     * Used (only) if $engine = CRYPT_ENGINE_MCRYPT
     *
     * _setupMcrypt() will be called each time if $changed === true
     * typically this happens when using one or more of following public methods:
     *
     * - setKey()
     *
     * - setIV()
     *
     * - disableContinuousBuffer()
     *
     * - First run of encrypt() / decrypt()
     *
     * @see self::setKey()
     * @see self::setIV()
     * @see self::disableContinuousBuffer()
     * @access private
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function _setupMcrypt()
    {
        $this->_clearBuffers();
        $this->enchanged = $this->dechanged = true;

        if (!isset($this->enmcrypt)) {
            static $mcrypt_modes = array(
                CRYPT_MODE_CTR    => 'ctr',
                CRYPT_MODE_ECB    => MCRYPT_MODE_ECB,
                CRYPT_MODE_CBC    => MCRYPT_MODE_CBC,
                CRYPT_MODE_CFB    => 'ncfb',
                CRYPT_MODE_OFB    => MCRYPT_MODE_NOFB,
                CRYPT_MODE_STREAM => MCRYPT_MODE_STREAM,
            );

            $this->demcrypt = @mcrypt_module_open($this->cipher_name_mcrypt, '', $mcrypt_modes[$this->mode], '');
            $this->enmcrypt = @mcrypt_module_open($this->cipher_name_mcrypt, '', $mcrypt_modes[$this->mode], '');

            // we need the $ecb mcrypt resource (only) in MODE_CFB with enableContinuousBuffer()
            // to workaround mcrypt's broken ncfb implementation in buffered mode
            // see: {@link http://phpseclib.sourceforge.net/cfb-demo.phps}
            if ($this->mode == CRYPT_MODE_CFB) {
                $this->ecb = @mcrypt_module_open($this->cipher_name_mcrypt, '', MCRYPT_MODE_ECB, '');
            }
        } // else should mcrypt_generic_deinit be called?

        if ($this->mode == CRYPT_MODE_CFB) {
            @mcrypt_generic_init($this->ecb, $this->key, str_repeat("\0", $this->block_size));
        }
    }

    /**
     * Pads a string
     *
     * Pads a string using the RSA PKCS padding standards so that its length is a multiple of the blocksize.
     * $this->block_size - (strlen($text) % $this->block_size) bytes are added, each of which is equal to
     * chr($this->block_size - (strlen($text) % $this->block_size)
     *
     * If padding is disabled and $text is not a multiple of the blocksize, the string will be padded regardless
     * and padding will, hence forth, be enabled.
     *
     * @see self::_unpad()
     * @param string $text
     * @access private
     * @return string
     */
    function _pad($text)
    {
        $length = strlen($text);

        if (!$this->padding) {
            if ($length % $this->block_size == 0) {
                return $text;
            } else {
                user_error("The plaintext's length ($length) is not a multiple of the block size ({$this->block_size})");
                $this->padding = true;
            }
        }

        $pad = $this->block_size - ($length % $this->block_size);

        return str_pad($text, $length + $pad, chr($pad));
    }

    /**
     * Unpads a string.
     *
     * If padding is enabled and the reported padding length is invalid the encryption key will be assumed to be wrong
     * and false will be returned.
     *
     * @see self::_pad()
     * @param string $text
     * @access private
     * @return string
     */
    function _unpad($text)
    {
        if (!$this->padding) {
            return $text;
        }

        $length = ord($text[strlen($text) - 1]);

        if (!$length || $length > $this->block_size) {
            return false;
        }

        return substr($text, 0, -$length);
    }

    /**
     * Clears internal buffers
     *
     * Clearing/resetting the internal buffers is done everytime
     * after disableContinuousBuffer() or on cipher $engine (re)init
     * ie after setKey() or setIV()
     *
     * @access public
     * @internal Could, but not must, extend by the child Crypt_* class
     */
    function _clearBuffers()
    {
        $this->enbuffer = $this->debuffer = array('ciphertext' => '', 'xor' => '', 'pos' => 0, 'enmcrypt_init' => true);

        // mcrypt's handling of invalid's $iv:
        // $this->encryptIV = $this->decryptIV = strlen($this->iv) == $this->block_size ? $this->iv : str_repeat("\0", $this->block_size);
        $this->encryptIV = $this->decryptIV = str_pad(substr($this->iv, 0, $this->block_size), $this->block_size, "\0");

        if (!$this->skip_key_adjustment) {
            $this->key = str_pad(substr($this->key, 0, $this->key_length), $this->key_length, "\0");
        }
    }

    /**
     * String Shift
     *
     * Inspired by array_shift
     *
     * @param string $string
     * @param int $index
     * @access private
     * @return string
     */
    function _string_shift(&$string, $index = 1)
    {
        $substr = substr($string, 0, $index);
        $string = substr($string, $index);
        return $substr;
    }

    /**
     * String Pop
     *
     * Inspired by array_pop
     *
     * @param string $string
     * @param int $index
     * @access private
     * @return string
     */
    function _string_pop(&$string, $index = 1)
    {
        $substr = substr($string, -$index);
        $string = substr($string, 0, -$index);
        return $substr;
    }

    /**
     * Increment the current string
     *
     * @see self::decrypt()
     * @see self::encrypt()
     * @param string $var
     * @access private
     */
    function _increment_str(&$var)
    {
        for ($i = 4; $i <= strlen($var); $i+= 4) {
            $temp = substr($var, -$i, 4);
            switch ($temp) {
                case "\xFF\xFF\xFF\xFF":
                    $var = substr_replace($var, "\x00\x00\x00\x00", -$i, 4);
                    break;
                case "\x7F\xFF\xFF\xFF":
                    $var = substr_replace($var, "\x80\x00\x00\x00", -$i, 4);
                    return;
                default:
                    $temp = unpack('Nnum', $temp);
                    $var = substr_replace($var, pack('N', $temp['num'] + 1), -$i, 4);
                    return;
            }
        }

        $remainder = strlen($var) % 4;

        if ($remainder == 0) {
            return;
        }

        $temp = unpack('Nnum', str_pad(substr($var, 0, $remainder), 4, "\0", STR_PAD_LEFT));
        $temp = substr(pack('N', $temp['num'] + 1), -$remainder);
        $var = substr_replace($var, $temp, 0, $remainder);
    }

    /**
     * Setup the performance-optimized function for de/encrypt()
     *
     * Stores the created (or existing) callback function-name
     * in $this->inline_crypt
     *
     * Internally for phpseclib developers:
     *
     *     _setupInlineCrypt() would be called only if:
     *
     *     - $engine == CRYPT_ENGINE_INTERNAL and
     *
     *     - $use_inline_crypt === true
     *
     *     - each time on _setup(), after(!) _setupKey()
     *
     *
     *     This ensures that _setupInlineCrypt() has always a
     *     full ready2go initializated internal cipher $engine state
     *     where, for example, the keys allready expanded,
     *     keys/block_size calculated and such.
     *
     *     It is, each time if called, the responsibility of _setupInlineCrypt():
     *
     *     - to set $this->inline_crypt to a valid and fully working callback function
     *       as a (faster) replacement for encrypt() / decrypt()
     *
     *     - NOT to create unlimited callback functions (for memory reasons!)
     *       no matter how often _setupInlineCrypt() would be called. At some
     *       point of amount they must be generic re-useable.
     *
     *     - the code of _setupInlineCrypt() it self,
     *       and the generated callback code,
     *       must be, in following order:
     *       - 100% safe
     *       - 100% compatible to encrypt()/decrypt()
     *       - using only php5+ features/lang-constructs/php-extensions if
     *         compatibility (down to php4) or fallback is provided
     *       - readable/maintainable/understandable/commented and... not-cryptic-styled-code :-)
     *       - >= 10% faster than encrypt()/decrypt() [which is, by the way,
     *         the reason for the existence of _setupInlineCrypt() :-)]
     *       - memory-nice
     *       - short (as good as possible)
     *
     * Note: - _setupInlineCrypt() is using _createInlineCryptFunction() to create the full callback function code.
     *       - In case of using inline crypting, _setupInlineCrypt() must extend by the child Crypt_* class.
     *       - The following variable names are reserved:
     *         - $_*  (all variable names prefixed with an underscore)
     *         - $self (object reference to it self. Do not use $this, but $self instead)
     *         - $in (the content of $in has to en/decrypt by the generated code)
     *       - The callback function should not use the 'return' statement, but en/decrypt'ing the content of $in only
     *
     *
     * @see self::_setup()
     * @see self::_createInlineCryptFunction()
     * @see self::encrypt()
     * @see self::decrypt()
     * @access private
     * @internal If a Crypt_* class providing inline crypting it must extend _setupInlineCrypt()
     */
    function _setupInlineCrypt()
    {
        // If, for any reason, an extending Crypt_Base() Crypt_* class
        // not using inline crypting then it must be ensured that: $this->use_inline_crypt = false
        // ie in the class var declaration of $use_inline_crypt in general for the Crypt_* class,
        // in the constructor at object instance-time
        // or, if it's runtime-specific, at runtime

        $this->use_inline_crypt = false;
    }

    /**
     * Creates the performance-optimized function for en/decrypt()
     *
     * Internally for phpseclib developers:
     *
     *    _createInlineCryptFunction():
     *
     *    - merge the $cipher_code [setup'ed by _setupInlineCrypt()]
     *      with the current [$this->]mode of operation code
     *
     *    - create the $inline function, which called by encrypt() / decrypt()
     *      as its replacement to speed up the en/decryption operations.
     *
     *    - return the name of the created $inline callback function
     *
     *    - used to speed up en/decryption
     *
     *
     *
     *    The main reason why can speed up things [up to 50%] this way are:
     *
     *    - using variables more effective then regular.
     *      (ie no use of expensive arrays but integers $k_0, $k_1 ...
     *      or even, for example, the pure $key[] values hardcoded)
     *
     *    - avoiding 1000's of function calls of ie _encryptBlock()
     *      but inlining the crypt operations.
     *      in the mode of operation for() loop.
     *
     *    - full loop unroll the (sometimes key-dependent) rounds
     *      avoiding this way ++$i counters and runtime-if's etc...
     *
     *    The basic code architectur of the generated $inline en/decrypt()
     *    lambda function, in pseudo php, is:
     *
     *    <code>
     *    +----------------------------------------------------------------------------------------------+
     *    | callback $inline = create_function:                                                          |
     *    | lambda_function_0001_crypt_ECB($action, $text)                                               |
     *    | {                                                                                            |
     *    |     INSERT PHP CODE OF:                                                                      |
     *    |     $cipher_code['init_crypt'];                  // general init code.                       |
     *    |                                                  // ie: $sbox'es declarations used for       |
     *    |                                                  //     encrypt and decrypt'ing.             |
     *    |                                                                                              |
     *    |     switch ($action) {                                                                       |
     *    |         case 'encrypt':                                                                      |
     *    |             INSERT PHP CODE OF:                                                              |
     *    |             $cipher_code['init_encrypt'];       // encrypt sepcific init code.               |
     *    |                                                    ie: specified $key or $box                |
     *    |                                                        declarations for encrypt'ing.         |
     *    |                                                                                              |
     *    |             foreach ($ciphertext) {                                                          |
     *    |                 $in = $block_size of $ciphertext;                                            |
     *    |                                                                                              |
     *    |                 INSERT PHP CODE OF:                                                          |
     *    |                 $cipher_code['encrypt_block'];  // encrypt's (string) $in, which is always:  |
     *    |                                                 // strlen($in) == $this->block_size          |
     *    |                                                 // here comes the cipher algorithm in action |
     *    |                                                 // for encryption.                           |
     *    |                                                 // $cipher_code['encrypt_block'] has to      |
     *    |                                                 // encrypt the content of the $in variable   |
     *    |                                                                                              |
     *    |                 $plaintext .= $in;                                                           |
     *    |             }                                                                                |
     *    |             return $plaintext;                                                               |
     *    |                                                                                              |
     *    |         case 'decrypt':                                                                      |
     *    |             INSERT PHP CODE OF:                                                              |
     *    |             $cipher_code['init_decrypt'];       // decrypt sepcific init code                |
     *    |                                                    ie: specified $key or $box                |
     *    |                                                        declarations for decrypt'ing.         |
     *    |             foreach ($plaintext) {                                                           |
     *    |                 $in = $block_size of $plaintext;                                             |
     *    |                                                                                              |
     *    |                 INSERT PHP CODE OF:                                                          |
     *    |                 $cipher_code['decrypt_block'];  // decrypt's (string) $in, which is always   |
     *    |                                                 // strlen($in) == $this->block_size          |
     *    |                                                 // here comes the cipher algorithm in action |
     *    |                                                 // for decryption.                           |
     *    |                                                 // $cipher_code['decrypt_block'] has to      |
     *    |                                                 // decrypt the content of the $in variable   |
     *    |                 $ciphertext .= $in;                                                          |
     *    |             }                                                                                |
     *    |             return $ciphertext;                                                              |
     *    |     }                                                                                        |
     *    | }                                                                                            |
     *    +----------------------------------------------------------------------------------------------+
     *    </code>
     *
     *    See also the Crypt_*::_setupInlineCrypt()'s for
     *    productive inline $cipher_code's how they works.
     *
     *    Structure of:
     *    <code>
     *    $cipher_code = array(
     *        'init_crypt'    => (string) '', // optional
     *        'init_encrypt'  => (string) '', // optional
     *        'init_decrypt'  => (string) '', // optional
     *        'encrypt_block' => (string) '', // required
     *        'decrypt_block' => (string) ''  // required
     *    );
     *    </code>
     *
     * @see self::_setupInlineCrypt()
     * @see self::encrypt()
     * @see self::decrypt()
     * @param array $cipher_code
     * @access private
     * @return string (the name of the created callback function)
     */
    function _createInlineCryptFunction($cipher_code)
    {
        $block_size = $this->block_size;

        // optional
        $init_crypt    = isset($cipher_code['init_crypt'])    ? $cipher_code['init_crypt']    : '';
        $init_encrypt  = isset($cipher_code['init_encrypt'])  ? $cipher_code['init_encrypt']  : '';
        $init_decrypt  = isset($cipher_code['init_decrypt'])  ? $cipher_code['init_decrypt']  : '';
        // required
        $encrypt_block = $cipher_code['encrypt_block'];
        $decrypt_block = $cipher_code['decrypt_block'];

        // Generating mode of operation inline code,
        // merged with the $cipher_code algorithm
        // for encrypt- and decryption.
        switch ($this->mode) {
            case CRYPT_MODE_ECB:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_plaintext_len = strlen($_text);

                    for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.');
                        '.$encrypt_block.'
                        $_ciphertext.= $in;
                    }

                    return $_ciphertext;
                    ';

                $decrypt = $init_decrypt . '
                    $_plaintext = "";
                    $_text = str_pad($_text, strlen($_text) + ('.$block_size.' - strlen($_text) % '.$block_size.') % '.$block_size.', chr(0));
                    $_ciphertext_len = strlen($_text);

                    for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.');
                        '.$decrypt_block.'
                        $_plaintext.= $in;
                    }

                    return $self->_unpad($_plaintext);
                    ';
                break;
            case CRYPT_MODE_CTR:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_plaintext_len = strlen($_text);
                    $_xor = $self->encryptIV;
                    $_buffer = &$self->enbuffer;
                    if (strlen($_buffer["ciphertext"])) {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["ciphertext"])) {
                                $in = $_xor;
                                '.$encrypt_block.'
                                $self->_increment_str($_xor);
                                $_buffer["ciphertext"].= $in;
                            }
                            $_key = $self->_string_shift($_buffer["ciphertext"], '.$block_size.');
                            $_ciphertext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            $in = $_xor;
                            '.$encrypt_block.'
                            $self->_increment_str($_xor);
                            $_key = $in;
                            $_ciphertext.= $_block ^ $_key;
                        }
                    }
                    if ($self->continuousBuffer) {
                        $self->encryptIV = $_xor;
                        if ($_start = $_plaintext_len % '.$block_size.') {
                            $_buffer["ciphertext"] = substr($_key, $_start) . $_buffer["ciphertext"];
                        }
                    }

                    return $_ciphertext;
                ';

                $decrypt = $init_encrypt . '
                    $_plaintext = "";
                    $_ciphertext_len = strlen($_text);
                    $_xor = $self->decryptIV;
                    $_buffer = &$self->debuffer;

                    if (strlen($_buffer["ciphertext"])) {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["ciphertext"])) {
                                $in = $_xor;
                                '.$encrypt_block.'
                                $self->_increment_str($_xor);
                                $_buffer["ciphertext"].= $in;
                            }
                            $_key = $self->_string_shift($_buffer["ciphertext"], '.$block_size.');
                            $_plaintext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            $in = $_xor;
                            '.$encrypt_block.'
                            $self->_increment_str($_xor);
                            $_key = $in;
                            $_plaintext.= $_block ^ $_key;
                        }
                    }
                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_xor;
                        if ($_start = $_ciphertext_len % '.$block_size.') {
                            $_buffer["ciphertext"] = substr($_key, $_start) . $_buffer["ciphertext"];
                        }
                    }

                    return $_plaintext;
                    ';
                break;
            case CRYPT_MODE_CFB:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_buffer = &$self->enbuffer;

                    if ($self->continuousBuffer) {
                        $_iv = &$self->encryptIV;
                        $_pos = &$_buffer["pos"];
                    } else {
                        $_iv = $self->encryptIV;
                        $_pos = 0;
                    }
                    $_len = strlen($_text);
                    $_i = 0;
                    if ($_pos) {
                        $_orig_pos = $_pos;
                        $_max = '.$block_size.' - $_pos;
                        if ($_len >= $_max) {
                            $_i = $_max;
                            $_len-= $_max;
                            $_pos = 0;
                        } else {
                            $_i = $_len;
                            $_pos+= $_len;
                            $_len = 0;
                        }
                        $_ciphertext = substr($_iv, $_orig_pos) ^ $_text;
                        $_iv = substr_replace($_iv, $_ciphertext, $_orig_pos, $_i);
                    }
                    while ($_len >= '.$block_size.') {
                        $in = $_iv;
                        '.$encrypt_block.';
                        $_iv = $in ^ substr($_text, $_i, '.$block_size.');
                        $_ciphertext.= $_iv;
                        $_len-= '.$block_size.';
                        $_i+= '.$block_size.';
                    }
                    if ($_len) {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $_block = $_iv ^ substr($_text, $_i);
                        $_iv = substr_replace($_iv, $_block, 0, $_len);
                        $_ciphertext.= $_block;
                        $_pos = $_len;
                    }
                    return $_ciphertext;
                ';

                $decrypt = $init_encrypt . '
                    $_plaintext = "";
                    $_buffer = &$self->debuffer;

                    if ($self->continuousBuffer) {
                        $_iv = &$self->decryptIV;
                        $_pos = &$_buffer["pos"];
                    } else {
                        $_iv = $self->decryptIV;
                        $_pos = 0;
                    }
                    $_len = strlen($_text);
                    $_i = 0;
                    if ($_pos) {
                        $_orig_pos = $_pos;
                        $_max = '.$block_size.' - $_pos;
                        if ($_len >= $_max) {
                            $_i = $_max;
                            $_len-= $_max;
                            $_pos = 0;
                        } else {
                            $_i = $_len;
                            $_pos+= $_len;
                            $_len = 0;
                        }
                        $_plaintext = substr($_iv, $_orig_pos) ^ $_text;
                        $_iv = substr_replace($_iv, substr($_text, 0, $_i), $_orig_pos, $_i);
                    }
                    while ($_len >= '.$block_size.') {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $cb = substr($_text, $_i, '.$block_size.');
                        $_plaintext.= $_iv ^ $cb;
                        $_iv = $cb;
                        $_len-= '.$block_size.';
                        $_i+= '.$block_size.';
                    }
                    if ($_len) {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $_plaintext.= $_iv ^ substr($_text, $_i);
                        $_iv = substr_replace($_iv, substr($_text, $_i), 0, $_len);
                        $_pos = $_len;
                    }

                    return $_plaintext;
                    ';
                break;
            case CRYPT_MODE_OFB:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_plaintext_len = strlen($_text);
                    $_xor = $self->encryptIV;
                    $_buffer = &$self->enbuffer;

                    if (strlen($_buffer["xor"])) {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["xor"])) {
                                $in = $_xor;
                                '.$encrypt_block.'
                                $_xor = $in;
                                $_buffer["xor"].= $_xor;
                            }
                            $_key = $self->_string_shift($_buffer["xor"], '.$block_size.');
                            $_ciphertext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $in = $_xor;
                            '.$encrypt_block.'
                            $_xor = $in;
                            $_ciphertext.= substr($_text, $_i, '.$block_size.') ^ $_xor;
                        }
                        $_key = $_xor;
                    }
                    if ($self->continuousBuffer) {
                        $self->encryptIV = $_xor;
                        if ($_start = $_plaintext_len % '.$block_size.') {
                             $_buffer["xor"] = substr($_key, $_start) . $_buffer["xor"];
                        }
                    }
                    return $_ciphertext;
                    ';

                $decrypt = $init_encrypt . '
                    $_plaintext = "";
                    $_ciphertext_len = strlen($_text);
                    $_xor = $self->decryptIV;
                    $_buffer = &$self->debuffer;

                    if (strlen($_buffer["xor"])) {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["xor"])) {
                                $in = $_xor;
                                '.$encrypt_block.'
                                $_xor = $in;
                                $_buffer["xor"].= $_xor;
                            }
                            $_key = $self->_string_shift($_buffer["xor"], '.$block_size.');
                            $_plaintext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $in = $_xor;
                            '.$encrypt_block.'
                            $_xor = $in;
                            $_plaintext.= substr($_text, $_i, '.$block_size.') ^ $_xor;
                        }
                        $_key = $_xor;
                    }
                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_xor;
                        if ($_start = $_ciphertext_len % '.$block_size.') {
                             $_buffer["xor"] = substr($_key, $_start) . $_buffer["xor"];
                        }
                    }
                    return $_plaintext;
                    ';
                break;
            case CRYPT_MODE_STREAM:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    '.$encrypt_block.'
                    return $_ciphertext;
                    ';
                $decrypt = $init_decrypt . '
                    $_plaintext = "";
                    '.$decrypt_block.'
                    return $_plaintext;
                    ';
                break;
            // case CRYPT_MODE_CBC:
            default:
                $encrypt = $init_encrypt . '
                    $_ciphertext = "";
                    $_plaintext_len = strlen($_text);

                    $in = $self->encryptIV;

                    for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.') ^ $in;
                        '.$encrypt_block.'
                        $_ciphertext.= $in;
                    }

                    if ($self->continuousBuffer) {
                        $self->encryptIV = $in;
                    }

                    return $_ciphertext;
                    ';

                $decrypt = $init_decrypt . '
                    $_plaintext = "";
                    $_text = str_pad($_text, strlen($_text) + ('.$block_size.' - strlen($_text) % '.$block_size.') % '.$block_size.', chr(0));
                    $_ciphertext_len = strlen($_text);

                    $_iv = $self->decryptIV;

                    for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                        $in = $_block = substr($_text, $_i, '.$block_size.');
                        '.$decrypt_block.'
                        $_plaintext.= $in ^ $_iv;
                        $_iv = $_block;
                    }

                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_iv;
                    }

                    return $self->_unpad($_plaintext);
                    ';
                break;
        }

        // Create the $inline function and return its name as string. Ready to run!
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            eval('$func = function ($_action, &$self, $_text) { ' . $init_crypt . 'if ($_action == "encrypt") { ' . $encrypt . ' } else { ' . $decrypt . ' } };');
            return $func;
        }

        return create_function('$_action, &$self, $_text', $init_crypt . 'if ($_action == "encrypt") { ' . $encrypt . ' } else { ' . $decrypt . ' }');
    }

    /**
     * Holds the lambda_functions table (classwide)
     *
     * Each name of the lambda function, created from
     * _setupInlineCrypt() && _createInlineCryptFunction()
     * is stored, classwide (!), here for reusing.
     *
     * The string-based index of $function is a classwide
     * unique value representing, at least, the $mode of
     * operation (or more... depends of the optimizing level)
     * for which $mode the lambda function was created.
     *
     * @access private
     * @return array &$functions
     */
    function &_getLambdaFunctions()
    {
        static $functions = array();
        return $functions;
    }

    /**
     * Generates a digest from $bytes
     *
     * @see self::_setupInlineCrypt()
     * @access private
     * @param $bytes
     * @return string
     */
    function _hashInlineCryptFunction($bytes)
    {
        if (!defined('CRYPT_BASE_WHIRLPOOL_AVAILABLE')) {
            define('CRYPT_BASE_WHIRLPOOL_AVAILABLE', (bool)(extension_loaded('hash') && in_array('whirlpool', hash_algos())));
        }

        $result = '';
        $hash = $bytes;

        switch (true) {
            case CRYPT_BASE_WHIRLPOOL_AVAILABLE:
                foreach (str_split($bytes, 64) as $t) {
                    $hash = hash('whirlpool', $hash, true);
                    $result .= $t ^ $hash;
                }
                return $result . hash('whirlpool', $hash, true);
            default:
                $len = strlen($bytes);
                for ($i = 0; $i < $len; $i+=20) {
                    $t = substr($bytes, $i, 20);
                    $hash = pack('H*', sha1($hash));
                    $result .= $t ^ $hash;
                }
                return $result . pack('H*', sha1($hash));
        }
    }

    /**
     * Convert float to int
     *
     * On 32-bit Linux installs running PHP < 5.3 converting floats to ints doesn't always work
     *
     * @access private
     * @param string $x
     * @return int
     */
    function safe_intval($x)
    {
        switch (true) {
            case is_int($x):
            // PHP 5.3, per http://php.net/releases/5_3_0.php, introduced "more consistent float rounding"
            case version_compare(PHP_VERSION, '5.3.0') >= 0 && (php_uname('m') & "\xDF\xDF\xDF") != 'ARM':
            // PHP_OS & "\xDF\xDF\xDF" == strtoupper(substr(PHP_OS, 0, 3)), but a lot faster
            case (PHP_OS & "\xDF\xDF\xDF") === 'WIN':
                return $x;
        }
        return (fmod($x, 0x80000000) & 0x7FFFFFFF) |
            ((fmod(floor($x / 0x80000000), 2) & 1) << 31);
    }

    /**
     * eval()'able string for in-line float to int
     *
     * @access private
     * @return string
     */
    function safe_intval_inline()
    {
        // on 32-bit linux systems with PHP < 5.3 float to integer conversion is bad
        switch (true) {
            case defined('PHP_INT_SIZE') && PHP_INT_SIZE == 8:
            case version_compare(PHP_VERSION, '5.3.0') >= 0 && (php_uname('m') & "\xDF\xDF\xDF") != 'ARM':
            case (PHP_OS & "\xDF\xDF\xDF") === 'WIN':
                return '%s';
                break;
            default:
                $safeint = '(is_int($temp = %s) ? $temp : (fmod($temp, 0x80000000) & 0x7FFFFFFF) | ';
                return $safeint . '((fmod(floor($temp / 0x80000000), 2) & 1) << 31))';
        }
    }
}

/**
 * Random Number Generator
 *
 * The idea behind this function is that it can be easily replaced with your own crypt_random_string()
 * function. eg. maybe you have a better source of entropy for creating the initial states or whatever.
 *
 * PHP versions 4 and 5
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'Crypt/Random.php';
 *
 *    echo bin2hex(crypt_random_string(8));
 * ?>
 * </code>
 *
 * LICENSE: Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Crypt
 * @package   Crypt_Random
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2007 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */

// laravel is a PHP framework that utilizes phpseclib. laravel workbenches may, independently,
// have phpseclib as a requirement as well. if you're developing such a program you may encounter
// a "Cannot redeclare crypt_random_string()" error.
if (!function_exists('crypt_random_string')) {
    /**
     * "Is Windows" test
     *
     * @access private
     */
    define('CRYPT_RANDOM_IS_WINDOWS', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

    /**
     * Generate a random string.
     *
     * Although microoptimizations are generally discouraged as they impair readability this function is ripe with
     * microoptimizations because this function has the potential of being called a huge number of times.
     * eg. for RSA key generation.
     *
     * @param int $length
     * @return string
     * @access public
     */
    function crypt_random_string($length)
    {
        if (!$length) {
            return '';
        }

        if (CRYPT_RANDOM_IS_WINDOWS) {
            // method 1. prior to PHP 5.3, mcrypt_create_iv() would call rand() on windows
            if (extension_loaded('mcrypt') && version_compare(PHP_VERSION, '5.3.0', '>=')) {
                return @mcrypt_create_iv($length);
            }
            // method 2. openssl_random_pseudo_bytes was introduced in PHP 5.3.0 but prior to PHP 5.3.4 there was,
            // to quote <http://php.net/ChangeLog-5.php#5.3.4>, "possible blocking behavior". as of 5.3.4
            // openssl_random_pseudo_bytes and mcrypt_create_iv do the exact same thing on Windows. ie. they both
            // call php_win32_get_random_bytes():
            //
            // https://github.com/php/php-src/blob/7014a0eb6d1611151a286c0ff4f2238f92c120d6/ext/openssl/openssl.c#L5008
            // https://github.com/php/php-src/blob/7014a0eb6d1611151a286c0ff4f2238f92c120d6/ext/mcrypt/mcrypt.c#L1392
            //
            // php_win32_get_random_bytes() is defined thusly:
            //
            // https://github.com/php/php-src/blob/7014a0eb6d1611151a286c0ff4f2238f92c120d6/win32/winutil.c#L80
            //
            // we're calling it, all the same, in the off chance that the mcrypt extension is not available
            if (extension_loaded('openssl') && version_compare(PHP_VERSION, '5.3.4', '>=')) {
                return openssl_random_pseudo_bytes($length);
            }
        } else {
            // method 1. the fastest
            if (extension_loaded('openssl') && version_compare(PHP_VERSION, '5.3.0', '>=')) {
                return openssl_random_pseudo_bytes($length);
            }
            // method 2
            static $fp = true;
            if ($fp === true) {
                // warning's will be output unles the error suppression operator is used. errors such as
                // "open_basedir restriction in effect", "Permission denied", "No such file or directory", etc.
                $fp = @fopen('/dev/urandom', 'rb');
            }
            if ($fp !== true && $fp !== false) { // surprisingly faster than !is_bool() or is_resource()
                return fread($fp, $length);
            }
            // method 3. pretty much does the same thing as method 2 per the following url:
            // https://github.com/php/php-src/blob/7014a0eb6d1611151a286c0ff4f2238f92c120d6/ext/mcrypt/mcrypt.c#L1391
            // surprisingly slower than method 2. maybe that's because mcrypt_create_iv does a bunch of error checking that we're
            // not doing. regardless, this'll only be called if this PHP script couldn't open /dev/urandom due to open_basedir
            // restrictions or some such
            if (extension_loaded('mcrypt')) {
                return @mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            }
        }
        // at this point we have no choice but to use a pure-PHP CSPRNG

        // cascade entropy across multiple PHP instances by fixing the session and collecting all
        // environmental variables, including the previous session data and the current session
        // data.
        //
        // mt_rand seeds itself by looking at the PID and the time, both of which are (relatively)
        // easy to guess at. linux uses mouse clicks, keyboard timings, etc, as entropy sources, but
        // PHP isn't low level to be able to use those as sources and on a web server there's not likely
        // going to be a ton of keyboard or mouse action. web servers do have one thing that we can use
        // however, a ton of people visiting the website. obviously you don't want to base your seeding
        // soley on parameters a potential attacker sends but (1) not everything in $_SERVER is controlled
        // by the user and (2) this isn't just looking at the data sent by the current user - it's based
        // on the data sent by all users. one user requests the page and a hash of their info is saved.
        // another user visits the page and the serialization of their data is utilized along with the
        // server envirnment stuff and a hash of the previous http request data (which itself utilizes
        // a hash of the session data before that). certainly an attacker should be assumed to have
        // full control over his own http requests. he, however, is not going to have control over
        // everyone's http requests.
        static $crypto = false, $v;
        if ($crypto === false) {
            // save old session data
            $old_session_id = session_id();
            $old_use_cookies = ini_get('session.use_cookies');
            $old_session_cache_limiter = session_cache_limiter();
            $_OLD_SESSION = isset($_SESSION) ? $_SESSION : false;
            if ($old_session_id != '') {
                session_write_close();
            }

            session_id(1);
            ini_set('session.use_cookies', 0);
            session_cache_limiter('');
            session_start();

            $v = $seed = $_SESSION['seed'] = pack('H*', sha1(
                (isset($_SERVER) ? phpseclib_safe_serialize($_SERVER) : '') .
                (isset($_POST) ? phpseclib_safe_serialize($_POST) : '') .
                (isset($_GET) ? phpseclib_safe_serialize($_GET) : '') .
                (isset($_COOKIE) ? phpseclib_safe_serialize($_COOKIE) : '') .
                phpseclib_safe_serialize($GLOBALS) .
                phpseclib_safe_serialize($_SESSION) .
                phpseclib_safe_serialize($_OLD_SESSION)
            ));
            if (!isset($_SESSION['count'])) {
                $_SESSION['count'] = 0;
            }
            $_SESSION['count']++;

            session_write_close();

            // restore old session data
            if ($old_session_id != '') {
                session_id($old_session_id);
                session_start();
                ini_set('session.use_cookies', $old_use_cookies);
                session_cache_limiter($old_session_cache_limiter);
            } else {
                if ($_OLD_SESSION !== false) {
                    $_SESSION = $_OLD_SESSION;
                    unset($_OLD_SESSION);
                } else {
                    unset($_SESSION);
                }
            }

            // in SSH2 a shared secret and an exchange hash are generated through the key exchange process.
            // the IV client to server is the hash of that "nonce" with the letter A and for the encryption key it's the letter C.
            // if the hash doesn't produce enough a key or an IV that's long enough concat successive hashes of the
            // original hash and the current hash. we'll be emulating that. for more info see the following URL:
            //
            // http://tools.ietf.org/html/rfc4253#section-7.2
            //
            // see the is_string($crypto) part for an example of how to expand the keys
            $key = pack('H*', sha1($seed . 'A'));
            $iv = pack('H*', sha1($seed . 'C'));

            // ciphers are used as per the nist.gov link below. also, see this link:
            //
            // http://en.wikipedia.org/wiki/Cryptographically_secure_pseudorandom_number_generator#Designs_based_on_cryptographic_primitives
            switch (true) {
                case phpseclib_resolve_include_path('Crypt/AES.php'):
                    /*
					if (!class_exists('Crypt_AES')) {
                        include_once 'AES.php';
                    }
					*/
                    $crypto = new Crypt_AES(CRYPT_AES_MODE_CTR);
                    break;
                case phpseclib_resolve_include_path('Crypt/Twofish.php'):
                    /*
					if (!class_exists('Crypt_Twofish')) {
                        include_once 'Twofish.php';
                    }
					*/
                    $crypto = new Crypt_Twofish(CRYPT_TWOFISH_MODE_CTR);
                    break;
                case phpseclib_resolve_include_path('Crypt/Blowfish.php'):
                    /*
					if (!class_exists('Crypt_Blowfish')) {
                        include_once 'Blowfish.php';
                    }
					*/
                    $crypto = new Crypt_Blowfish(CRYPT_BLOWFISH_MODE_CTR);
                    break;
                case phpseclib_resolve_include_path('Crypt/TripleDES.php'):
                    /*
					if (!class_exists('Crypt_TripleDES')) {
                        include_once 'TripleDES.php';
                    }
					*/
                    $crypto = new Crypt_TripleDES(CRYPT_DES_MODE_CTR);
                    break;
                case phpseclib_resolve_include_path('Crypt/DES.php'):
                    /*
					if (!class_exists('Crypt_DES')) {
                        include_once 'DES.php';
                    }
					*/
                    $crypto = new Crypt_DES(CRYPT_DES_MODE_CTR);
                    break;
                case phpseclib_resolve_include_path('Crypt/RC4.php'):
                    /*
					if (!class_exists('Crypt_RC4')) {
                        include_once 'RC4.php';
                    }
					*/
                    $crypto = new Crypt_RC4();
                    break;
                default:
                    user_error('crypt_random_string requires at least one symmetric cipher be loaded');
                    return false;
            }

            $crypto->setKey($key);
            $crypto->setIV($iv);
            $crypto->enableContinuousBuffer();
        }

        //return $crypto->encrypt(str_repeat("\0", $length));

        // the following is based off of ANSI X9.31:
        //
        // http://csrc.nist.gov/groups/STM/cavp/documents/rng/931rngext.pdf
        //
        // OpenSSL uses that same standard for it's random numbers:
        //
        // http://www.opensource.apple.com/source/OpenSSL/OpenSSL-38/openssl/fips-1.0/rand/fips_rand.c
        // (do a search for "ANS X9.31 A.2.4")
        $result = '';
        while (strlen($result) < $length) {
            $i = $crypto->encrypt(microtime()); // strlen(microtime()) == 21
            $r = $crypto->encrypt($i ^ $v); // strlen($v) == 20
            $v = $crypto->encrypt($r ^ $i); // strlen($r) == 20
            $result.= $r;
        }
        return substr($result, 0, $length);
    }
}

if (!function_exists('phpseclib_safe_serialize')) {
    /**
     * Safely serialize variables
     *
     * If a class has a private __sleep() method it'll give a fatal error on PHP 5.2 and earlier.
     * PHP 5.3 will emit a warning.
     *
     * @param mixed $arr
     * @access public
     */
    function phpseclib_safe_serialize(&$arr)
    {
        if (is_object($arr)) {
            return '';
        }
        if (!is_array($arr)) {
            return serialize($arr);
        }
        // prevent circular array recursion
        if (isset($arr['__phpseclib_marker'])) {
            return '';
        }
        $safearr = array();
        $arr['__phpseclib_marker'] = true;
        foreach (array_keys($arr) as $key) {
            // do not recurse on the '__phpseclib_marker' key itself, for smaller memory usage
            if ($key !== '__phpseclib_marker') {
                $safearr[$key] = phpseclib_safe_serialize($arr[$key]);
            }
        }
        unset($arr['__phpseclib_marker']);
        return serialize($safearr);
    }
}

if (!function_exists('phpseclib_resolve_include_path')) {
    /**
     * Resolve filename against the include path.
     *
     * Wrapper around stream_resolve_include_path() (which was introduced in
     * PHP 5.3.2) with fallback implementation for earlier PHP versions.
     *
     * @param string $filename
     * @return string|false
     * @access public
     */
    function phpseclib_resolve_include_path($filename)
    {
        if (function_exists('stream_resolve_include_path')) {
            return stream_resolve_include_path($filename);
        }

        // handle non-relative paths
        if (file_exists($filename)) {
            return realpath($filename);
        }

        $paths = PATH_SEPARATOR == ':' ?
            preg_split('#(?<!phar):#', get_include_path()) :
            explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $prefix) {
            // path's specified in include_path don't always end in /
            $ds = substr($prefix, -1) == DIRECTORY_SEPARATOR ? '' : DIRECTORY_SEPARATOR;
            $file = $prefix . $ds . $filename;
            if (file_exists($file)) {
                return realpath($file);
            }
        }

        return false;
    }
}

/**
 * Pure-PHP implementation of Rijndael.
 *
 * Uses mcrypt, if available/possible, and an internal implementation, otherwise.
 *
 * PHP versions 4 and 5
 *
 * If {@link self::setBlockLength() setBlockLength()} isn't called, it'll be assumed to be 128 bits.  If
 * {@link self::setKeyLength() setKeyLength()} isn't called, it'll be calculated from
 * {@link self::setKey() setKey()}.  ie. if the key is 128-bits, the key length will be 128-bits.  If it's
 * 136-bits it'll be null-padded to 192-bits and 192 bits will be the key length until
 * {@link self::setKey() setKey()} is called, again, at which point, it'll be recalculated.
 *
 * Not all Rijndael implementations may support 160-bits or 224-bits as the block length / key length.  mcrypt, for example,
 * does not.  AES, itself, only supports block lengths of 128 and key lengths of 128, 192, and 256.
 * {@link http://csrc.nist.gov/archive/aes/rijndael/Rijndael-ammended.pdf#page=10 Rijndael-ammended.pdf#page=10} defines the
 * algorithm for block lengths of 192 and 256 but not for block lengths / key lengths of 160 and 224.  Indeed, 160 and 224
 * are first defined as valid key / block lengths in
 * {@link http://csrc.nist.gov/archive/aes/rijndael/Rijndael-ammended.pdf#page=44 Rijndael-ammended.pdf#page=44}:
 * Extensions: Other block and Cipher Key lengths.
 * Note: Use of 160/224-bit Keys must be explicitly set by setKeyLength(160) respectively setKeyLength(224).
 *
 * {@internal The variable names are the same as those in
 * {@link http://www.csrc.nist.gov/publications/fips/fips197/fips-197.pdf#page=10 fips-197.pdf#page=10}.}}
 *
 * Here's a short example of how to use this library:
 * <code>
 * <?php
 *    include 'Crypt/Rijndael.php';
 *
 *    $rijndael = new Crypt_Rijndael();
 *
 *    $rijndael->setKey('abcdefghijklmnop');
 *
 *    $size = 10 * 1024;
 *    $plaintext = '';
 *    for ($i = 0; $i < $size; $i++) {
 *        $plaintext.= 'a';
 *    }
 *
 *    echo $rijndael->decrypt($rijndael->encrypt($plaintext));
 * ?>
 * </code>
 *
 * LICENSE: Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Crypt
 * @package   Crypt_Rijndael
 * @author    Jim Wigginton <terrafrost@php.net>
 * @copyright 2008 Jim Wigginton
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link      http://phpseclib.sourceforge.net
 */


/**#@+
 * @access public
 * @see self::encrypt()
 * @see self::decrypt()
 */
/**
 * Encrypt / decrypt using the Counter mode.
 *
 * Set to -1 since that's what Crypt/Random.php uses to index the CTR mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Counter_.28CTR.29
 */
define('CRYPT_RIJNDAEL_MODE_CTR', CRYPT_MODE_CTR);
/**
 * Encrypt / decrypt using the Electronic Code Book mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Electronic_codebook_.28ECB.29
 */
define('CRYPT_RIJNDAEL_MODE_ECB', CRYPT_MODE_ECB);
/**
 * Encrypt / decrypt using the Code Book Chaining mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29
 */
define('CRYPT_RIJNDAEL_MODE_CBC', CRYPT_MODE_CBC);
/**
 * Encrypt / decrypt using the Cipher Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher_feedback_.28CFB.29
 */
define('CRYPT_RIJNDAEL_MODE_CFB', CRYPT_MODE_CFB);
/**
 * Encrypt / decrypt using the Cipher Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Output_feedback_.28OFB.29
 */
define('CRYPT_RIJNDAEL_MODE_OFB', CRYPT_MODE_OFB);
/**#@-*/

/**
 * Pure-PHP implementation of Rijndael.
 *
 * @package Crypt_Rijndael
 * @author  Jim Wigginton <terrafrost@php.net>
 * @access  public
 */
class Crypt_Rijndael extends Crypt_Base
{
    /**
     * The namespace used by the cipher for its constants.
     *
     * @see Crypt_Base::const_namespace
     * @var string
     * @access private
     */
    var $const_namespace = 'RIJNDAEL';

    /**
     * The mcrypt specific name of the cipher
     *
     * Mcrypt is useable for 128/192/256-bit $block_size/$key_length. For 160/224 not.
     * Crypt_Rijndael determines automatically whether mcrypt is useable
     * or not for the current $block_size/$key_length.
     * In case of, $cipher_name_mcrypt will be set dynamically at run time accordingly.
     *
     * @see Crypt_Base::cipher_name_mcrypt
     * @see Crypt_Base::engine
     * @see self::isValidEngine()
     * @var string
     * @access private
     */
    var $cipher_name_mcrypt = 'rijndael-128';

    /**
     * The default salt used by setPassword()
     *
     * @see Crypt_Base::password_default_salt
     * @see Crypt_Base::setPassword()
     * @var string
     * @access private
     */
    var $password_default_salt = 'phpseclib';

    /**
     * The Key Schedule
     *
     * @see self::_setup()
     * @var array
     * @access private
     */
    var $w;

    /**
     * The Inverse Key Schedule
     *
     * @see self::_setup()
     * @var array
     * @access private
     */
    var $dw;

    /**
     * The Block Length divided by 32
     *
     * @see self::setBlockLength()
     * @var int
     * @access private
     * @internal The max value is 256 / 32 = 8, the min value is 128 / 32 = 4.  Exists in conjunction with $block_size
     *    because the encryption / decryption / key schedule creation requires this number and not $block_size.  We could
     *    derive this from $block_size or vice versa, but that'd mean we'd have to do multiple shift operations, so in lieu
     *    of that, we'll just precompute it once.
     */
    var $Nb = 4;

    /**
     * The Key Length (in bytes)
     *
     * @see self::setKeyLength()
     * @var int
     * @access private
     * @internal The max value is 256 / 8 = 32, the min value is 128 / 8 = 16.  Exists in conjunction with $Nk
     *    because the encryption / decryption / key schedule creation requires this number and not $key_length.  We could
     *    derive this from $key_length or vice versa, but that'd mean we'd have to do multiple shift operations, so in lieu
     *    of that, we'll just precompute it once.
     */
    var $key_length = 16;

    /**
     * The Key Length divided by 32
     *
     * @see self::setKeyLength()
     * @var int
     * @access private
     * @internal The max value is 256 / 32 = 8, the min value is 128 / 32 = 4
     */
    var $Nk = 4;

    /**
     * The Number of Rounds
     *
     * @var int
     * @access private
     * @internal The max value is 14, the min value is 10.
     */
    var $Nr;

    /**
     * Shift offsets
     *
     * @var array
     * @access private
     */
    var $c;

    /**
     * Holds the last used key- and block_size information
     *
     * @var array
     * @access private
     */
    var $kl;

    /**
     * Sets the key.
     *
     * Keys can be of any length.  Rijndael, itself, requires the use of a key that's between 128-bits and 256-bits long and
     * whose length is a multiple of 32.  If the key is less than 256-bits and the key length isn't set, we round the length
     * up to the closest valid key length, padding $key with null bytes.  If the key is more than 256-bits, we trim the
     * excess bits.
     *
     * If the key is not explicitly set, it'll be assumed to be all null bytes.
     *
     * Note: 160/224-bit keys must explicitly set by setKeyLength(), otherwise they will be round/pad up to 192/256 bits.
     *
     * @see Crypt_Base:setKey()
     * @see self::setKeyLength()
     * @access public
     * @param string $key
     */
    function setKey($key)
    {
        if (!$this->explicit_key_length) {
            $length = strlen($key);
            switch (true) {
                case $length <= 16:
                    $this->key_size = 16;
                    break;
                case $length <= 20:
                    $this->key_size = 20;
                    break;
                case $length <= 24:
                    $this->key_size = 24;
                    break;
                case $length <= 28:
                    $this->key_size = 28;
                    break;
                default:
                    $this->key_size = 32;
            }
        }
        parent::setKey($key);
    }

    /**
     * Sets the key length
     *
     * Valid key lengths are 128, 160, 192, 224, and 256.  If the length is less than 128, it will be rounded up to
     * 128.  If the length is greater than 128 and invalid, it will be rounded down to the closest valid amount.
     *
     * Note: phpseclib extends Rijndael (and AES) for using 160- and 224-bit keys but they are officially not defined
     *       and the most (if not all) implementations are not able using 160/224-bit keys but round/pad them up to
     *       192/256 bits as, for example, mcrypt will do.
     *
     *       That said, if you want be compatible with other Rijndael and AES implementations,
     *       you should not setKeyLength(160) or setKeyLength(224).
     *
     * Additional: In case of 160- and 224-bit keys, phpseclib will/can, for that reason, not use
     *             the mcrypt php extension, even if available.
     *             This results then in slower encryption.
     *
     * @access public
     * @param int $length
     */
    function setKeyLength($length)
    {
        switch (true) {
            case $length <= 128:
                $this->key_length = 16;
                break;
            case $length <= 160:
                $this->key_length = 20;
                break;
            case $length <= 192:
                $this->key_length = 24;
                break;
            case $length <= 224:
                $this->key_length = 28;
                break;
            default:
                $this->key_length = 32;
        }

        parent::setKeyLength($length);
    }

    /**
     * Sets the block length
     *
     * Valid block lengths are 128, 160, 192, 224, and 256.  If the length is less than 128, it will be rounded up to
     * 128.  If the length is greater than 128 and invalid, it will be rounded down to the closest valid amount.
     *
     * @access public
     * @param int $length
     */
    function setBlockLength($length)
    {
        $length >>= 5;
        if ($length > 8) {
            $length = 8;
        } elseif ($length < 4) {
            $length = 4;
        }
        $this->Nb = $length;
        $this->block_size = $length << 2;
        $this->changed = true;
        $this->_setEngine();
    }

    /**
     * Test for engine validity
     *
     * This is mainly just a wrapper to set things up for Crypt_Base::isValidEngine()
     *
     * @see Crypt_Base::Crypt_Base()
     * @param int $engine
     * @access public
     * @return bool
     */
    function isValidEngine($engine)
    {
        switch ($engine) {
            case CRYPT_ENGINE_OPENSSL:
                if ($this->block_size != 16) {
                    return false;
                }
                $this->cipher_name_openssl_ecb = 'aes-' . ($this->key_length << 3) . '-ecb';
                $this->cipher_name_openssl = 'aes-' . ($this->key_length << 3) . '-' . $this->_openssl_translate_mode();
                break;
            case CRYPT_ENGINE_MCRYPT:
                $this->cipher_name_mcrypt = 'rijndael-' . ($this->block_size << 3);
                if ($this->key_length % 8) { // is it a 160/224-bit key?
                    // mcrypt is not usable for them, only for 128/192/256-bit keys
                    return false;
                }
        }

        return parent::isValidEngine($engine);
    }

    /**
     * Encrypts a block
     *
     * @access private
     * @param string $in
     * @return string
     */
    function _encryptBlock($in)
    {
        static $tables;
        if (empty($tables)) {
            $tables = &$this->_getTables();
        }
        $t0   = $tables[0];
        $t1   = $tables[1];
        $t2   = $tables[2];
        $t3   = $tables[3];
        $sbox = $tables[4];

        $state = array();
        $words = unpack('N*', $in);

        $c = $this->c;
        $w = $this->w;
        $Nb = $this->Nb;
        $Nr = $this->Nr;

        // addRoundKey
        $wc = $Nb - 1;
        foreach ($words as $word) {
            $state[] = $word ^ $w[++$wc];
        }

        // fips-197.pdf#page=19, "Figure 5. Pseudo Code for the Cipher", states that this loop has four components -
        // subBytes, shiftRows, mixColumns, and addRoundKey. fips-197.pdf#page=30, "Implementation Suggestions Regarding
        // Various Platforms" suggests that performs enhanced implementations are described in Rijndael-ammended.pdf.
        // Rijndael-ammended.pdf#page=20, "Implementation aspects / 32-bit processor", discusses such an optimization.
        // Unfortunately, the description given there is not quite correct.  Per aes.spec.v316.pdf#page=19 [1],
        // equation (7.4.7) is supposed to use addition instead of subtraction, so we'll do that here, as well.

        // [1] http://fp.gladman.plus.com/cryptography_technology/rijndael/aes.spec.v316.pdf
        $temp = array();
        for ($round = 1; $round < $Nr; ++$round) {
            $i = 0; // $c[0] == 0
            $j = $c[1];
            $k = $c[2];
            $l = $c[3];

            while ($i < $Nb) {
                $temp[$i] = $t0[$state[$i] >> 24 & 0x000000FF] ^
                            $t1[$state[$j] >> 16 & 0x000000FF] ^
                            $t2[$state[$k] >>  8 & 0x000000FF] ^
                            $t3[$state[$l]       & 0x000000FF] ^
                            $w[++$wc];
                ++$i;
                $j = ($j + 1) % $Nb;
                $k = ($k + 1) % $Nb;
                $l = ($l + 1) % $Nb;
            }
            $state = $temp;
        }

        // subWord
        for ($i = 0; $i < $Nb; ++$i) {
            $state[$i] =   $sbox[$state[$i]       & 0x000000FF]        |
                          ($sbox[$state[$i] >>  8 & 0x000000FF] <<  8) |
                          ($sbox[$state[$i] >> 16 & 0x000000FF] << 16) |
                          ($sbox[$state[$i] >> 24 & 0x000000FF] << 24);
        }

        // shiftRows + addRoundKey
        $i = 0; // $c[0] == 0
        $j = $c[1];
        $k = $c[2];
        $l = $c[3];
        while ($i < $Nb) {
            $temp[$i] = ($state[$i] & 0xFF000000) ^
                        ($state[$j] & 0x00FF0000) ^
                        ($state[$k] & 0x0000FF00) ^
                        ($state[$l] & 0x000000FF) ^
                         $w[$i];
            ++$i;
            $j = ($j + 1) % $Nb;
            $k = ($k + 1) % $Nb;
            $l = ($l + 1) % $Nb;
        }

        switch ($Nb) {
            case 8:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5], $temp[6], $temp[7]);
            case 7:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5], $temp[6]);
            case 6:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5]);
            case 5:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3], $temp[4]);
            default:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3]);
        }
    }

    /**
     * Decrypts a block
     *
     * @access private
     * @param string $in
     * @return string
     */
    function _decryptBlock($in)
    {
        static $invtables;
        if (empty($invtables)) {
            $invtables = &$this->_getInvTables();
        }
        $dt0   = $invtables[0];
        $dt1   = $invtables[1];
        $dt2   = $invtables[2];
        $dt3   = $invtables[3];
        $isbox = $invtables[4];

        $state = array();
        $words = unpack('N*', $in);

        $c  = $this->c;
        $dw = $this->dw;
        $Nb = $this->Nb;
        $Nr = $this->Nr;

        // addRoundKey
        $wc = $Nb - 1;
        foreach ($words as $word) {
            $state[] = $word ^ $dw[++$wc];
        }

        $temp = array();
        for ($round = $Nr - 1; $round > 0; --$round) {
            $i = 0; // $c[0] == 0
            $j = $Nb - $c[1];
            $k = $Nb - $c[2];
            $l = $Nb - $c[3];

            while ($i < $Nb) {
                $temp[$i] = $dt0[$state[$i] >> 24 & 0x000000FF] ^
                            $dt1[$state[$j] >> 16 & 0x000000FF] ^
                            $dt2[$state[$k] >>  8 & 0x000000FF] ^
                            $dt3[$state[$l]       & 0x000000FF] ^
                            $dw[++$wc];
                ++$i;
                $j = ($j + 1) % $Nb;
                $k = ($k + 1) % $Nb;
                $l = ($l + 1) % $Nb;
            }
            $state = $temp;
        }

        // invShiftRows + invSubWord + addRoundKey
        $i = 0; // $c[0] == 0
        $j = $Nb - $c[1];
        $k = $Nb - $c[2];
        $l = $Nb - $c[3];

        while ($i < $Nb) {
            $word = ($state[$i] & 0xFF000000) |
                    ($state[$j] & 0x00FF0000) |
                    ($state[$k] & 0x0000FF00) |
                    ($state[$l] & 0x000000FF);

            $temp[$i] = $dw[$i] ^ ($isbox[$word       & 0x000000FF]        |
                                  ($isbox[$word >>  8 & 0x000000FF] <<  8) |
                                  ($isbox[$word >> 16 & 0x000000FF] << 16) |
                                  ($isbox[$word >> 24 & 0x000000FF] << 24));
            ++$i;
            $j = ($j + 1) % $Nb;
            $k = ($k + 1) % $Nb;
            $l = ($l + 1) % $Nb;
        }

        switch ($Nb) {
            case 8:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5], $temp[6], $temp[7]);
            case 7:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5], $temp[6]);
            case 6:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3], $temp[4], $temp[5]);
            case 5:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3], $temp[4]);
            default:
                return pack('N*', $temp[0], $temp[1], $temp[2], $temp[3]);
        }
    }

    /**
     * Setup the key (expansion)
     *
     * @see Crypt_Base::_setupKey()
     * @access private
     */
    function _setupKey()
    {
        // Each number in $rcon is equal to the previous number multiplied by two in Rijndael's finite field.
        // See http://en.wikipedia.org/wiki/Finite_field_arithmetic#Multiplicative_inverse
        static $rcon = array(0,
            0x01000000, 0x02000000, 0x04000000, 0x08000000, 0x10000000,
            0x20000000, 0x40000000, 0x80000000, 0x1B000000, 0x36000000,
            0x6C000000, 0xD8000000, 0xAB000000, 0x4D000000, 0x9A000000,
            0x2F000000, 0x5E000000, 0xBC000000, 0x63000000, 0xC6000000,
            0x97000000, 0x35000000, 0x6A000000, 0xD4000000, 0xB3000000,
            0x7D000000, 0xFA000000, 0xEF000000, 0xC5000000, 0x91000000
        );

        if (isset($this->kl['key']) && $this->key === $this->kl['key'] && $this->key_length === $this->kl['key_length'] && $this->block_size === $this->kl['block_size']) {
            // already expanded
            return;
        }
        $this->kl = array('key' => $this->key, 'key_length' => $this->key_length, 'block_size' => $this->block_size);

        $this->Nk = $this->key_length >> 2;
        // see Rijndael-ammended.pdf#page=44
        $this->Nr = max($this->Nk, $this->Nb) + 6;

        // shift offsets for Nb = 5, 7 are defined in Rijndael-ammended.pdf#page=44,
        //     "Table 8: Shift offsets in Shiftrow for the alternative block lengths"
        // shift offsets for Nb = 4, 6, 8 are defined in Rijndael-ammended.pdf#page=14,
        //     "Table 2: Shift offsets for different block lengths"
        switch ($this->Nb) {
            case 4:
            case 5:
            case 6:
                $this->c = array(0, 1, 2, 3);
                break;
            case 7:
                $this->c = array(0, 1, 2, 4);
                break;
            case 8:
                $this->c = array(0, 1, 3, 4);
        }

        $w = array_values(unpack('N*words', $this->key));

        $length = $this->Nb * ($this->Nr + 1);
        for ($i = $this->Nk; $i < $length; $i++) {
            $temp = $w[$i - 1];
            if ($i % $this->Nk == 0) {
                // according to <http://php.net/language.types.integer>, "the size of an integer is platform-dependent".
                // on a 32-bit machine, it's 32-bits, and on a 64-bit machine, it's 64-bits. on a 32-bit machine,
                // 0xFFFFFFFF << 8 == 0xFFFFFF00, but on a 64-bit machine, it equals 0xFFFFFFFF00. as such, doing 'and'
                // with 0xFFFFFFFF (or 0xFFFFFF00) on a 32-bit machine is unnecessary, but on a 64-bit machine, it is.
                $temp = (($temp << 8) & 0xFFFFFF00) | (($temp >> 24) & 0x000000FF); // rotWord
                $temp = $this->_subWord($temp) ^ $rcon[$i / $this->Nk];
            } elseif ($this->Nk > 6 && $i % $this->Nk == 4) {
                $temp = $this->_subWord($temp);
            }
            $w[$i] = $w[$i - $this->Nk] ^ $temp;
        }

        // convert the key schedule from a vector of $Nb * ($Nr + 1) length to a matrix with $Nr + 1 rows and $Nb columns
        // and generate the inverse key schedule.  more specifically,
        // according to <http://csrc.nist.gov/archive/aes/rijndael/Rijndael-ammended.pdf#page=23> (section 5.3.3),
        // "The key expansion for the Inverse Cipher is defined as follows:
        //        1. Apply the Key Expansion.
        //        2. Apply InvMixColumn to all Round Keys except the first and the last one."
        // also, see fips-197.pdf#page=27, "5.3.5 Equivalent Inverse Cipher"
        list($dt0, $dt1, $dt2, $dt3) = $this->_getInvTables();
        $temp = $this->w = $this->dw = array();
        for ($i = $row = $col = 0; $i < $length; $i++, $col++) {
            if ($col == $this->Nb) {
                if ($row == 0) {
                    $this->dw[0] = $this->w[0];
                } else {
                    // subWord + invMixColumn + invSubWord = invMixColumn
                    $j = 0;
                    while ($j < $this->Nb) {
                        $dw = $this->_subWord($this->w[$row][$j]);
                        $temp[$j] = $dt0[$dw >> 24 & 0x000000FF] ^
                                    $dt1[$dw >> 16 & 0x000000FF] ^
                                    $dt2[$dw >>  8 & 0x000000FF] ^
                                    $dt3[$dw       & 0x000000FF];
                        $j++;
                    }
                    $this->dw[$row] = $temp;
                }

                $col = 0;
                $row++;
            }
            $this->w[$row][$col] = $w[$i];
        }

        $this->dw[$row] = $this->w[$row];

        // Converting to 1-dim key arrays (both ascending)
        $this->dw = array_reverse($this->dw);
        $w  = array_pop($this->w);
        $dw = array_pop($this->dw);
        foreach ($this->w as $r => $wr) {
            foreach ($wr as $c => $wc) {
                $w[]  = $wc;
                $dw[] = $this->dw[$r][$c];
            }
        }
        $this->w  = $w;
        $this->dw = $dw;
    }

    /**
     * Performs S-Box substitutions
     *
     * @access private
     * @param int $word
     */
    function _subWord($word)
    {
        static $sbox;
        if (empty($sbox)) {
            list(, , , , $sbox) = $this->_getTables();
        }

        return  $sbox[$word       & 0x000000FF]        |
               ($sbox[$word >>  8 & 0x000000FF] <<  8) |
               ($sbox[$word >> 16 & 0x000000FF] << 16) |
               ($sbox[$word >> 24 & 0x000000FF] << 24);
    }

    /**
     * Provides the mixColumns and sboxes tables
     *
     * @see Crypt_Rijndael:_encryptBlock()
     * @see Crypt_Rijndael:_setupInlineCrypt()
     * @see Crypt_Rijndael:_subWord()
     * @access private
     * @return array &$tables
     */
    function &_getTables()
    {
        static $tables;
        if (empty($tables)) {
            // according to <http://csrc.nist.gov/archive/aes/rijndael/Rijndael-ammended.pdf#page=19> (section 5.2.1),
            // precomputed tables can be used in the mixColumns phase. in that example, they're assigned t0...t3, so
            // those are the names we'll use.
            $t3 = array_map('intval', array(
                // with array_map('intval', ...) we ensure we have only int's and not
                // some slower floats converted by php automatically on high values
                0x6363A5C6, 0x7C7C84F8, 0x777799EE, 0x7B7B8DF6, 0xF2F20DFF, 0x6B6BBDD6, 0x6F6FB1DE, 0xC5C55491,
                0x30305060, 0x01010302, 0x6767A9CE, 0x2B2B7D56, 0xFEFE19E7, 0xD7D762B5, 0xABABE64D, 0x76769AEC,
                0xCACA458F, 0x82829D1F, 0xC9C94089, 0x7D7D87FA, 0xFAFA15EF, 0x5959EBB2, 0x4747C98E, 0xF0F00BFB,
                0xADADEC41, 0xD4D467B3, 0xA2A2FD5F, 0xAFAFEA45, 0x9C9CBF23, 0xA4A4F753, 0x727296E4, 0xC0C05B9B,
                0xB7B7C275, 0xFDFD1CE1, 0x9393AE3D, 0x26266A4C, 0x36365A6C, 0x3F3F417E, 0xF7F702F5, 0xCCCC4F83,
                0x34345C68, 0xA5A5F451, 0xE5E534D1, 0xF1F108F9, 0x717193E2, 0xD8D873AB, 0x31315362, 0x15153F2A,
                0x04040C08, 0xC7C75295, 0x23236546, 0xC3C35E9D, 0x18182830, 0x9696A137, 0x05050F0A, 0x9A9AB52F,
                0x0707090E, 0x12123624, 0x80809B1B, 0xE2E23DDF, 0xEBEB26CD, 0x2727694E, 0xB2B2CD7F, 0x75759FEA,
                0x09091B12, 0x83839E1D, 0x2C2C7458, 0x1A1A2E34, 0x1B1B2D36, 0x6E6EB2DC, 0x5A5AEEB4, 0xA0A0FB5B,
                0x5252F6A4, 0x3B3B4D76, 0xD6D661B7, 0xB3B3CE7D, 0x29297B52, 0xE3E33EDD, 0x2F2F715E, 0x84849713,
                0x5353F5A6, 0xD1D168B9, 0x00000000, 0xEDED2CC1, 0x20206040, 0xFCFC1FE3, 0xB1B1C879, 0x5B5BEDB6,
                0x6A6ABED4, 0xCBCB468D, 0xBEBED967, 0x39394B72, 0x4A4ADE94, 0x4C4CD498, 0x5858E8B0, 0xCFCF4A85,
                0xD0D06BBB, 0xEFEF2AC5, 0xAAAAE54F, 0xFBFB16ED, 0x4343C586, 0x4D4DD79A, 0x33335566, 0x85859411,
                0x4545CF8A, 0xF9F910E9, 0x02020604, 0x7F7F81FE, 0x5050F0A0, 0x3C3C4478, 0x9F9FBA25, 0xA8A8E34B,
                0x5151F3A2, 0xA3A3FE5D, 0x4040C080, 0x8F8F8A05, 0x9292AD3F, 0x9D9DBC21, 0x38384870, 0xF5F504F1,
                0xBCBCDF63, 0xB6B6C177, 0xDADA75AF, 0x21216342, 0x10103020, 0xFFFF1AE5, 0xF3F30EFD, 0xD2D26DBF,
                0xCDCD4C81, 0x0C0C1418, 0x13133526, 0xECEC2FC3, 0x5F5FE1BE, 0x9797A235, 0x4444CC88, 0x1717392E,
                0xC4C45793, 0xA7A7F255, 0x7E7E82FC, 0x3D3D477A, 0x6464ACC8, 0x5D5DE7BA, 0x19192B32, 0x737395E6,
                0x6060A0C0, 0x81819819, 0x4F4FD19E, 0xDCDC7FA3, 0x22226644, 0x2A2A7E54, 0x9090AB3B, 0x8888830B,
                0x4646CA8C, 0xEEEE29C7, 0xB8B8D36B, 0x14143C28, 0xDEDE79A7, 0x5E5EE2BC, 0x0B0B1D16, 0xDBDB76AD,
                0xE0E03BDB, 0x32325664, 0x3A3A4E74, 0x0A0A1E14, 0x4949DB92, 0x06060A0C, 0x24246C48, 0x5C5CE4B8,
                0xC2C25D9F, 0xD3D36EBD, 0xACACEF43, 0x6262A6C4, 0x9191A839, 0x9595A431, 0xE4E437D3, 0x79798BF2,
                0xE7E732D5, 0xC8C8438B, 0x3737596E, 0x6D6DB7DA, 0x8D8D8C01, 0xD5D564B1, 0x4E4ED29C, 0xA9A9E049,
                0x6C6CB4D8, 0x5656FAAC, 0xF4F407F3, 0xEAEA25CF, 0x6565AFCA, 0x7A7A8EF4, 0xAEAEE947, 0x08081810,
                0xBABAD56F, 0x787888F0, 0x25256F4A, 0x2E2E725C, 0x1C1C2438, 0xA6A6F157, 0xB4B4C773, 0xC6C65197,
                0xE8E823CB, 0xDDDD7CA1, 0x74749CE8, 0x1F1F213E, 0x4B4BDD96, 0xBDBDDC61, 0x8B8B860D, 0x8A8A850F,
                0x707090E0, 0x3E3E427C, 0xB5B5C471, 0x6666AACC, 0x4848D890, 0x03030506, 0xF6F601F7, 0x0E0E121C,
                0x6161A3C2, 0x35355F6A, 0x5757F9AE, 0xB9B9D069, 0x86869117, 0xC1C15899, 0x1D1D273A, 0x9E9EB927,
                0xE1E138D9, 0xF8F813EB, 0x9898B32B, 0x11113322, 0x6969BBD2, 0xD9D970A9, 0x8E8E8907, 0x9494A733,
                0x9B9BB62D, 0x1E1E223C, 0x87879215, 0xE9E920C9, 0xCECE4987, 0x5555FFAA, 0x28287850, 0xDFDF7AA5,
                0x8C8C8F03, 0xA1A1F859, 0x89898009, 0x0D0D171A, 0xBFBFDA65, 0xE6E631D7, 0x4242C684, 0x6868B8D0,
                0x4141C382, 0x9999B029, 0x2D2D775A, 0x0F0F111E, 0xB0B0CB7B, 0x5454FCA8, 0xBBBBD66D, 0x16163A2C
            ));

            foreach ($t3 as $t3i) {
                $t0[] = (($t3i << 24) & 0xFF000000) | (($t3i >>  8) & 0x00FFFFFF);
                $t1[] = (($t3i << 16) & 0xFFFF0000) | (($t3i >> 16) & 0x0000FFFF);
                $t2[] = (($t3i <<  8) & 0xFFFFFF00) | (($t3i >> 24) & 0x000000FF);
            }

            $tables = array(
                // The Precomputed mixColumns tables t0 - t3
                $t0,
                $t1,
                $t2,
                $t3,
                // The SubByte S-Box
                array(
                    0x63, 0x7C, 0x77, 0x7B, 0xF2, 0x6B, 0x6F, 0xC5, 0x30, 0x01, 0x67, 0x2B, 0xFE, 0xD7, 0xAB, 0x76,
                    0xCA, 0x82, 0xC9, 0x7D, 0xFA, 0x59, 0x47, 0xF0, 0xAD, 0xD4, 0xA2, 0xAF, 0x9C, 0xA4, 0x72, 0xC0,
                    0xB7, 0xFD, 0x93, 0x26, 0x36, 0x3F, 0xF7, 0xCC, 0x34, 0xA5, 0xE5, 0xF1, 0x71, 0xD8, 0x31, 0x15,
                    0x04, 0xC7, 0x23, 0xC3, 0x18, 0x96, 0x05, 0x9A, 0x07, 0x12, 0x80, 0xE2, 0xEB, 0x27, 0xB2, 0x75,
                    0x09, 0x83, 0x2C, 0x1A, 0x1B, 0x6E, 0x5A, 0xA0, 0x52, 0x3B, 0xD6, 0xB3, 0x29, 0xE3, 0x2F, 0x84,
                    0x53, 0xD1, 0x00, 0xED, 0x20, 0xFC, 0xB1, 0x5B, 0x6A, 0xCB, 0xBE, 0x39, 0x4A, 0x4C, 0x58, 0xCF,
                    0xD0, 0xEF, 0xAA, 0xFB, 0x43, 0x4D, 0x33, 0x85, 0x45, 0xF9, 0x02, 0x7F, 0x50, 0x3C, 0x9F, 0xA8,
                    0x51, 0xA3, 0x40, 0x8F, 0x92, 0x9D, 0x38, 0xF5, 0xBC, 0xB6, 0xDA, 0x21, 0x10, 0xFF, 0xF3, 0xD2,
                    0xCD, 0x0C, 0x13, 0xEC, 0x5F, 0x97, 0x44, 0x17, 0xC4, 0xA7, 0x7E, 0x3D, 0x64, 0x5D, 0x19, 0x73,
                    0x60, 0x81, 0x4F, 0xDC, 0x22, 0x2A, 0x90, 0x88, 0x46, 0xEE, 0xB8, 0x14, 0xDE, 0x5E, 0x0B, 0xDB,
                    0xE0, 0x32, 0x3A, 0x0A, 0x49, 0x06, 0x24, 0x5C, 0xC2, 0xD3, 0xAC, 0x62, 0x91, 0x95, 0xE4, 0x79,
                    0xE7, 0xC8, 0x37, 0x6D, 0x8D, 0xD5, 0x4E, 0xA9, 0x6C, 0x56, 0xF4, 0xEA, 0x65, 0x7A, 0xAE, 0x08,
                    0xBA, 0x78, 0x25, 0x2E, 0x1C, 0xA6, 0xB4, 0xC6, 0xE8, 0xDD, 0x74, 0x1F, 0x4B, 0xBD, 0x8B, 0x8A,
                    0x70, 0x3E, 0xB5, 0x66, 0x48, 0x03, 0xF6, 0x0E, 0x61, 0x35, 0x57, 0xB9, 0x86, 0xC1, 0x1D, 0x9E,
                    0xE1, 0xF8, 0x98, 0x11, 0x69, 0xD9, 0x8E, 0x94, 0x9B, 0x1E, 0x87, 0xE9, 0xCE, 0x55, 0x28, 0xDF,
                    0x8C, 0xA1, 0x89, 0x0D, 0xBF, 0xE6, 0x42, 0x68, 0x41, 0x99, 0x2D, 0x0F, 0xB0, 0x54, 0xBB, 0x16
                )
            );
        }
        return $tables;
    }

    /**
     * Provides the inverse mixColumns and inverse sboxes tables
     *
     * @see Crypt_Rijndael:_decryptBlock()
     * @see Crypt_Rijndael:_setupInlineCrypt()
     * @see Crypt_Rijndael:_setupKey()
     * @access private
     * @return array &$tables
     */
    function &_getInvTables()
    {
        static $tables;
        if (empty($tables)) {
            $dt3 = array_map('intval', array(
                0xF4A75051, 0x4165537E, 0x17A4C31A, 0x275E963A, 0xAB6BCB3B, 0x9D45F11F, 0xFA58ABAC, 0xE303934B,
                0x30FA5520, 0x766DF6AD, 0xCC769188, 0x024C25F5, 0xE5D7FC4F, 0x2ACBD7C5, 0x35448026, 0x62A38FB5,
                0xB15A49DE, 0xBA1B6725, 0xEA0E9845, 0xFEC0E15D, 0x2F7502C3, 0x4CF01281, 0x4697A38D, 0xD3F9C66B,
                0x8F5FE703, 0x929C9515, 0x6D7AEBBF, 0x5259DA95, 0xBE832DD4, 0x7421D358, 0xE0692949, 0xC9C8448E,
                0xC2896A75, 0x8E7978F4, 0x583E6B99, 0xB971DD27, 0xE14FB6BE, 0x88AD17F0, 0x20AC66C9, 0xCE3AB47D,
                0xDF4A1863, 0x1A3182E5, 0x51336097, 0x537F4562, 0x6477E0B1, 0x6BAE84BB, 0x81A01CFE, 0x082B94F9,
                0x48685870, 0x45FD198F, 0xDE6C8794, 0x7BF8B752, 0x73D323AB, 0x4B02E272, 0x1F8F57E3, 0x55AB2A66,
                0xEB2807B2, 0xB5C2032F, 0xC57B9A86, 0x3708A5D3, 0x2887F230, 0xBFA5B223, 0x036ABA02, 0x16825CED,
                0xCF1C2B8A, 0x79B492A7, 0x07F2F0F3, 0x69E2A14E, 0xDAF4CD65, 0x05BED506, 0x34621FD1, 0xA6FE8AC4,
                0x2E539D34, 0xF355A0A2, 0x8AE13205, 0xF6EB75A4, 0x83EC390B, 0x60EFAA40, 0x719F065E, 0x6E1051BD,
                0x218AF93E, 0xDD063D96, 0x3E05AEDD, 0xE6BD464D, 0x548DB591, 0xC45D0571, 0x06D46F04, 0x5015FF60,
                0x98FB2419, 0xBDE997D6, 0x4043CC89, 0xD99E7767, 0xE842BDB0, 0x898B8807, 0x195B38E7, 0xC8EEDB79,
                0x7C0A47A1, 0x420FE97C, 0x841EC9F8, 0x00000000, 0x80868309, 0x2BED4832, 0x1170AC1E, 0x5A724E6C,
                0x0EFFFBFD, 0x8538560F, 0xAED51E3D, 0x2D392736, 0x0FD9640A, 0x5CA62168, 0x5B54D19B, 0x362E3A24,
                0x0A67B10C, 0x57E70F93, 0xEE96D2B4, 0x9B919E1B, 0xC0C54F80, 0xDC20A261, 0x774B695A, 0x121A161C,
                0x93BA0AE2, 0xA02AE5C0, 0x22E0433C, 0x1B171D12, 0x090D0B0E, 0x8BC7ADF2, 0xB6A8B92D, 0x1EA9C814,
                0xF1198557, 0x75074CAF, 0x99DDBBEE, 0x7F60FDA3, 0x01269FF7, 0x72F5BC5C, 0x663BC544, 0xFB7E345B,
                0x4329768B, 0x23C6DCCB, 0xEDFC68B6, 0xE4F163B8, 0x31DCCAD7, 0x63851042, 0x97224013, 0xC6112084,
                0x4A247D85, 0xBB3DF8D2, 0xF93211AE, 0x29A16DC7, 0x9E2F4B1D, 0xB230F3DC, 0x8652EC0D, 0xC1E3D077,
                0xB3166C2B, 0x70B999A9, 0x9448FA11, 0xE9642247, 0xFC8CC4A8, 0xF03F1AA0, 0x7D2CD856, 0x3390EF22,
                0x494EC787, 0x38D1C1D9, 0xCAA2FE8C, 0xD40B3698, 0xF581CFA6, 0x7ADE28A5, 0xB78E26DA, 0xADBFA43F,
                0x3A9DE42C, 0x78920D50, 0x5FCC9B6A, 0x7E466254, 0x8D13C2F6, 0xD8B8E890, 0x39F75E2E, 0xC3AFF582,
                0x5D80BE9F, 0xD0937C69, 0xD52DA96F, 0x2512B3CF, 0xAC993BC8, 0x187DA710, 0x9C636EE8, 0x3BBB7BDB,
                0x267809CD, 0x5918F46E, 0x9AB701EC, 0x4F9AA883, 0x956E65E6, 0xFFE67EAA, 0xBCCF0821, 0x15E8E6EF,
                0xE79BD9BA, 0x6F36CE4A, 0x9F09D4EA, 0xB07CD629, 0xA4B2AF31, 0x3F23312A, 0xA59430C6, 0xA266C035,
                0x4EBC3774, 0x82CAA6FC, 0x90D0B0E0, 0xA7D81533, 0x04984AF1, 0xECDAF741, 0xCD500E7F, 0x91F62F17,
                0x4DD68D76, 0xEFB04D43, 0xAA4D54CC, 0x9604DFE4, 0xD1B5E39E, 0x6A881B4C, 0x2C1FB8C1, 0x65517F46,
                0x5EEA049D, 0x8C355D01, 0x877473FA, 0x0B412EFB, 0x671D5AB3, 0xDBD25292, 0x105633E9, 0xD647136D,
                0xD7618C9A, 0xA10C7A37, 0xF8148E59, 0x133C89EB, 0xA927EECE, 0x61C935B7, 0x1CE5EDE1, 0x47B13C7A,
                0xD2DF599C, 0xF2733F55, 0x14CE7918, 0xC737BF73, 0xF7CDEA53, 0xFDAA5B5F, 0x3D6F14DF, 0x44DB8678,
                0xAFF381CA, 0x68C43EB9, 0x24342C38, 0xA3405FC2, 0x1DC37216, 0xE2250CBC, 0x3C498B28, 0x0D9541FF,
                0xA8017139, 0x0CB3DE08, 0xB4E49CD8, 0x56C19064, 0xCB84617B, 0x32B670D5, 0x6C5C7448, 0xB85742D0
            ));

            foreach ($dt3 as $dt3i) {
                $dt0[] = (($dt3i << 24) & 0xFF000000) | (($dt3i >>  8) & 0x00FFFFFF);
                $dt1[] = (($dt3i << 16) & 0xFFFF0000) | (($dt3i >> 16) & 0x0000FFFF);
                $dt2[] = (($dt3i <<  8) & 0xFFFFFF00) | (($dt3i >> 24) & 0x000000FF);
            };

            $tables = array(
                // The Precomputed inverse mixColumns tables dt0 - dt3
                $dt0,
                $dt1,
                $dt2,
                $dt3,
                // The inverse SubByte S-Box
                array(
                    0x52, 0x09, 0x6A, 0xD5, 0x30, 0x36, 0xA5, 0x38, 0xBF, 0x40, 0xA3, 0x9E, 0x81, 0xF3, 0xD7, 0xFB,
                    0x7C, 0xE3, 0x39, 0x82, 0x9B, 0x2F, 0xFF, 0x87, 0x34, 0x8E, 0x43, 0x44, 0xC4, 0xDE, 0xE9, 0xCB,
                    0x54, 0x7B, 0x94, 0x32, 0xA6, 0xC2, 0x23, 0x3D, 0xEE, 0x4C, 0x95, 0x0B, 0x42, 0xFA, 0xC3, 0x4E,
                    0x08, 0x2E, 0xA1, 0x66, 0x28, 0xD9, 0x24, 0xB2, 0x76, 0x5B, 0xA2, 0x49, 0x6D, 0x8B, 0xD1, 0x25,
                    0x72, 0xF8, 0xF6, 0x64, 0x86, 0x68, 0x98, 0x16, 0xD4, 0xA4, 0x5C, 0xCC, 0x5D, 0x65, 0xB6, 0x92,
                    0x6C, 0x70, 0x48, 0x50, 0xFD, 0xED, 0xB9, 0xDA, 0x5E, 0x15, 0x46, 0x57, 0xA7, 0x8D, 0x9D, 0x84,
                    0x90, 0xD8, 0xAB, 0x00, 0x8C, 0xBC, 0xD3, 0x0A, 0xF7, 0xE4, 0x58, 0x05, 0xB8, 0xB3, 0x45, 0x06,
                    0xD0, 0x2C, 0x1E, 0x8F, 0xCA, 0x3F, 0x0F, 0x02, 0xC1, 0xAF, 0xBD, 0x03, 0x01, 0x13, 0x8A, 0x6B,
                    0x3A, 0x91, 0x11, 0x41, 0x4F, 0x67, 0xDC, 0xEA, 0x97, 0xF2, 0xCF, 0xCE, 0xF0, 0xB4, 0xE6, 0x73,
                    0x96, 0xAC, 0x74, 0x22, 0xE7, 0xAD, 0x35, 0x85, 0xE2, 0xF9, 0x37, 0xE8, 0x1C, 0x75, 0xDF, 0x6E,
                    0x47, 0xF1, 0x1A, 0x71, 0x1D, 0x29, 0xC5, 0x89, 0x6F, 0xB7, 0x62, 0x0E, 0xAA, 0x18, 0xBE, 0x1B,
                    0xFC, 0x56, 0x3E, 0x4B, 0xC6, 0xD2, 0x79, 0x20, 0x9A, 0xDB, 0xC0, 0xFE, 0x78, 0xCD, 0x5A, 0xF4,
                    0x1F, 0xDD, 0xA8, 0x33, 0x88, 0x07, 0xC7, 0x31, 0xB1, 0x12, 0x10, 0x59, 0x27, 0x80, 0xEC, 0x5F,
                    0x60, 0x51, 0x7F, 0xA9, 0x19, 0xB5, 0x4A, 0x0D, 0x2D, 0xE5, 0x7A, 0x9F, 0x93, 0xC9, 0x9C, 0xEF,
                    0xA0, 0xE0, 0x3B, 0x4D, 0xAE, 0x2A, 0xF5, 0xB0, 0xC8, 0xEB, 0xBB, 0x3C, 0x83, 0x53, 0x99, 0x61,
                    0x17, 0x2B, 0x04, 0x7E, 0xBA, 0x77, 0xD6, 0x26, 0xE1, 0x69, 0x14, 0x63, 0x55, 0x21, 0x0C, 0x7D
                )
            );
        }
        return $tables;
    }

    /**
     * Setup the performance-optimized function for de/encrypt()
     *
     * @see Crypt_Base::_setupInlineCrypt()
     * @access private
     */
    function _setupInlineCrypt()
    {
        // Note: _setupInlineCrypt() will be called only if $this->changed === true
        // So here we are'nt under the same heavy timing-stress as we are in _de/encryptBlock() or de/encrypt().
        // However...the here generated function- $code, stored as php callback in $this->inline_crypt, must work as fast as even possible.

        $lambda_functions =& Crypt_Rijndael::_getLambdaFunctions();

        // We create max. 10 hi-optimized code for memory reason. Means: For each $key one ultra fast inline-crypt function.
        // (Currently, for Crypt_Rijndael/AES, one generated $lambda_function cost on php5.5@32bit ~80kb unfreeable mem and ~130kb on php5.5@64bit)
        // After that, we'll still create very fast optimized code but not the hi-ultimative code, for each $mode one.
        $gen_hi_opt_code = (bool)(count($lambda_functions) < 10);

        // Generation of a uniqe hash for our generated code
        $code_hash = "Crypt_Rijndael, {$this->mode}, {$this->Nr}, {$this->Nb}";
        if ($gen_hi_opt_code) {
            $code_hash = str_pad($code_hash, 32) . $this->_hashInlineCryptFunction($this->key);
        }

        if (!isset($lambda_functions[$code_hash])) {
            switch (true) {
                case $gen_hi_opt_code:
                    // The hi-optimized $lambda_functions will use the key-words hardcoded for better performance.
                    $w  = $this->w;
                    $dw = $this->dw;
                    $init_encrypt = '';
                    $init_decrypt = '';
                    break;
                default:
                    for ($i = 0, $cw = count($this->w); $i < $cw; ++$i) {
                        $w[]  = '$w['  . $i . ']';
                        $dw[] = '$dw[' . $i . ']';
                    }
                    $init_encrypt = '$w  = $self->w;';
                    $init_decrypt = '$dw = $self->dw;';
            }

            $Nr = $this->Nr;
            $Nb = $this->Nb;
            $c  = $this->c;

            // Generating encrypt code:
            $init_encrypt.= '
                static $tables;
                if (empty($tables)) {
                    $tables = &$self->_getTables();
                }
                $t0   = $tables[0];
                $t1   = $tables[1];
                $t2   = $tables[2];
                $t3   = $tables[3];
                $sbox = $tables[4];
            ';

            $s  = 'e';
            $e  = 's';
            $wc = $Nb - 1;

            // Preround: addRoundKey
            $encrypt_block = '$in = unpack("N*", $in);'."\n";
            for ($i = 0; $i < $Nb; ++$i) {
                $encrypt_block .= '$s'.$i.' = $in['.($i + 1).'] ^ '.$w[++$wc].";\n";
            }

            // Mainrounds: shiftRows + subWord + mixColumns + addRoundKey
            for ($round = 1; $round < $Nr; ++$round) {
                list($s, $e) = array($e, $s);
                for ($i = 0; $i < $Nb; ++$i) {
                    $encrypt_block.=
                        '$'.$e.$i.' =
                        $t0[($'.$s.$i                  .' >> 24) & 0xff] ^
                        $t1[($'.$s.(($i + $c[1]) % $Nb).' >> 16) & 0xff] ^
                        $t2[($'.$s.(($i + $c[2]) % $Nb).' >>  8) & 0xff] ^
                        $t3[ $'.$s.(($i + $c[3]) % $Nb).'        & 0xff] ^
                        '.$w[++$wc].";\n";
                }
            }

            // Finalround: subWord + shiftRows + addRoundKey
            for ($i = 0; $i < $Nb; ++$i) {
                $encrypt_block.=
                    '$'.$e.$i.' =
                     $sbox[ $'.$e.$i.'        & 0xff]        |
                    ($sbox[($'.$e.$i.' >>  8) & 0xff] <<  8) |
                    ($sbox[($'.$e.$i.' >> 16) & 0xff] << 16) |
                    ($sbox[($'.$e.$i.' >> 24) & 0xff] << 24);'."\n";
            }
            $encrypt_block .= '$in = pack("N*"'."\n";
            for ($i = 0; $i < $Nb; ++$i) {
                $encrypt_block.= ',
                    ($'.$e.$i                  .' & '.((int)0xFF000000).') ^
                    ($'.$e.(($i + $c[1]) % $Nb).' &         0x00FF0000   ) ^
                    ($'.$e.(($i + $c[2]) % $Nb).' &         0x0000FF00   ) ^
                    ($'.$e.(($i + $c[3]) % $Nb).' &         0x000000FF   ) ^
                    '.$w[$i]."\n";
            }
            $encrypt_block .= ');';

            // Generating decrypt code:
            $init_decrypt.= '
                static $invtables;
                if (empty($invtables)) {
                    $invtables = &$self->_getInvTables();
                }
                $dt0   = $invtables[0];
                $dt1   = $invtables[1];
                $dt2   = $invtables[2];
                $dt3   = $invtables[3];
                $isbox = $invtables[4];
            ';

            $s  = 'e';
            $e  = 's';
            $wc = $Nb - 1;

            // Preround: addRoundKey
            $decrypt_block = '$in = unpack("N*", $in);'."\n";
            for ($i = 0; $i < $Nb; ++$i) {
                $decrypt_block .= '$s'.$i.' = $in['.($i + 1).'] ^ '.$dw[++$wc].';'."\n";
            }

            // Mainrounds: shiftRows + subWord + mixColumns + addRoundKey
            for ($round = 1; $round < $Nr; ++$round) {
                list($s, $e) = array($e, $s);
                for ($i = 0; $i < $Nb; ++$i) {
                    $decrypt_block.=
                        '$'.$e.$i.' =
                        $dt0[($'.$s.$i                        .' >> 24) & 0xff] ^
                        $dt1[($'.$s.(($Nb + $i - $c[1]) % $Nb).' >> 16) & 0xff] ^
                        $dt2[($'.$s.(($Nb + $i - $c[2]) % $Nb).' >>  8) & 0xff] ^
                        $dt3[ $'.$s.(($Nb + $i - $c[3]) % $Nb).'        & 0xff] ^
                        '.$dw[++$wc].";\n";
                }
            }

            // Finalround: subWord + shiftRows + addRoundKey
            for ($i = 0; $i < $Nb; ++$i) {
                $decrypt_block.=
                    '$'.$e.$i.' =
                     $isbox[ $'.$e.$i.'        & 0xff]        |
                    ($isbox[($'.$e.$i.' >>  8) & 0xff] <<  8) |
                    ($isbox[($'.$e.$i.' >> 16) & 0xff] << 16) |
                    ($isbox[($'.$e.$i.' >> 24) & 0xff] << 24);'."\n";
            }
            $decrypt_block .= '$in = pack("N*"'."\n";
            for ($i = 0; $i < $Nb; ++$i) {
                $decrypt_block.= ',
                    ($'.$e.$i.                        ' & '.((int)0xFF000000).') ^
                    ($'.$e.(($Nb + $i - $c[1]) % $Nb).' &         0x00FF0000   ) ^
                    ($'.$e.(($Nb + $i - $c[2]) % $Nb).' &         0x0000FF00   ) ^
                    ($'.$e.(($Nb + $i - $c[3]) % $Nb).' &         0x000000FF   ) ^
                    '.$dw[$i]."\n";
            }
            $decrypt_block .= ');';

            $lambda_functions[$code_hash] = $this->_createInlineCryptFunction(
                array(
                   'init_crypt'    => '',
                   'init_encrypt'  => $init_encrypt,
                   'init_decrypt'  => $init_decrypt,
                   'encrypt_block' => $encrypt_block,
                   'decrypt_block' => $decrypt_block
                )
            );
        }
        $this->inline_crypt = $lambda_functions[$code_hash];
    }
}


class DUPX_CSRF {
	
	/** Session var name
	 * @var string
	 */
	public static $prefix = '_DUPX_CSRF';
	
	/** Generate DUPX_CSRF value for form
	 * @param	string	$form	- Form name as session key
	 * @return	string	- token
	 */
	public static function generate($form = NULL) {
		if (!empty($_COOKIE[DUPX_CSRF::$prefix . '_' . $form])) {
			$token = $_COOKIE[DUPX_CSRF::$prefix . '_' . $form];
		} else {
            $token = DUPX_CSRF::token() . DUPX_CSRF::fingerprint();
		}
		$cookieName = DUPX_CSRF::$prefix . '_' . $form;
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
		if (isset($_COOKIE[DUPX_CSRF::$prefix . '_' . $form]) && $_COOKIE[DUPX_CSRF::$prefix . '_' . $form] == $token) { // token OK
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
		$domainPath = self::getDomainPath();
		return setcookie($cookieName, $cookieVal, time() + 10800, '/');
	}

	public static function getDomainPath() {
		return '/';
		// return str_replace('main.installer.php', '', $_SERVER['SCRIPT_NAME']);
	}
	
	/**
	* @return bool
	*/
	protected static function isCookieEnabled() {
		return (count($_COOKIE) > 0);
	}

	public static function resetAllTokens() {
		foreach ($_COOKIE as $cookieName => $cookieVal) {
			$step1Key = DUPX_CSRF::$prefix . '_step1';
			if ($step1Key != $cookieName && (0 === strpos($cookieName, DUPX_CSRF::$prefix) || 'archive' == $cookieName || 'bootloader' == $cookieName)) {
				// $domainPath = self::getDomainPath();
				setcookie($cookieName, '', time() - 86400, '/');
				unset($_COOKIE[$cookieName]);
			}
		}
	}
}

/**
 * Bootstrap utility to exatract the core installer
 *
 * Standard: PSR-2
 *
 * @package SC\DUPX\Bootstrap
 * @link http://www.php-fig.org/psr/psr-2/
 *
 *  To force extraction mode:
 *		installer.php?unzipmode=auto
 *		installer.php?unzipmode=ziparchive
 *		installer.php?unzipmode=shellexec
 */

abstract class DUPX_Bootstrap_Zip_Mode
{
	const AutoUnzip		= 0;
	const ZipArchive	= 1;
	const ShellExec		= 2;
}

abstract class DUPX_Connectivity
{
	const OK		= 0;
	const Error		= 1;
	const Unknown	= 2;
}

class DUPX_Bootstrap
{
	//@@ Params get dynamically swapped when package is built
	const ARCHIVE_FILENAME	 = '@@ARCHIVE@@';
	const ARCHIVE_SIZE		 = '@@ARCHIVE_SIZE@@';
	const INSTALLER_DIR_NAME = 'dup-installer';
	const PACKAGE_HASH		 = '@@PACKAGE_HASH@@';
	const CSRF_CRYPT		 =  @@CSRF_CRYPT@@;
	const VERSION			 = '@@VERSION@@';

	public $hasZipArchive     = false;
	public $hasShellExecUnzip = false;
	public $mainInstallerURL;
	public $installerContentsPath;
	public $installerExtractPath;
	public $archiveExpectedSize = 0;
	public $archiveActualSize = 0;
	public $activeRatio = 0;

	/**
	 * Instantiate the Bootstrap Object
	 *
	 * @return null
	 */
	public function __construct()
	{
		//ARCHIVE_SIZE will be blank with a root filter so we can estimate
		//the default size of the package around 17.5MB (18088000)
		$archiveActualSize		        = @filesize(self::ARCHIVE_FILENAME);
		$archiveActualSize				= ($archiveActualSize !== false) ? $archiveActualSize : 0;
		$this->hasZipArchive			= class_exists('ZipArchive');
		$this->hasShellExecUnzip		= $this->getUnzipFilePath() != null ? true : false;
		$this->installerContentsPath	= str_replace("\\", '/', (dirname(__FILE__). '/' .self::INSTALLER_DIR_NAME));
		$this->installerExtractPath		= str_replace("\\", '/', (dirname(__FILE__)));
		$this->archiveExpectedSize      = strlen(self::ARCHIVE_SIZE) ?  self::ARCHIVE_SIZE : 0 ;
		$this->archiveActualSize        = $archiveActualSize;

        if($this->archiveExpectedSize > 0) {
            $this->archiveRatio			= (((1.0) * $this->archiveActualSize)  / $this->archiveExpectedSize) * 100;
        } else {
            $this->archiveRatio			= 100;
        }

        $this->overwriteMode = (isset($_GET['mode']) && ($_GET['mode'] == 'overwrite'));
	}

	/**
	 * Run the bootstrap process which includes checking for requirements and running
	 * the extraction process
	 *
	 * @return null | string	Returns null if the run was successful otherwise an error message
	 */
	public function run()
	{
		date_default_timezone_set('UTC'); // Some machines don't have this set so just do it here
		@unlink('./dup-installer-bootlog__'.self::PACKAGE_HASH.'.txt');
		self::log('==DUPLICATOR INSTALLER BOOTSTRAP v@@VERSION@@==');
		self::log('----------------------------------------------------');
		self::log('Installer bootstrap start');

		$archive_filepath	 = $this->getArchiveFilePath();
		$archive_filename	 = self::ARCHIVE_FILENAME;

		$error					= null;
		$extract_installer		= true;
		$installer_directory	= dirname(__FILE__).'/'.self::INSTALLER_DIR_NAME;
		$extract_success		= false;
		$archiveExpectedEasy	= $this->readableByteSize($this->archiveExpectedSize);
		$archiveActualEasy		= $this->readableByteSize($this->archiveActualSize);

        //$archive_extension = strtolower(pathinfo($archive_filepath)['extension']);
        $archive_extension		= strtolower(pathinfo($archive_filepath, PATHINFO_EXTENSION));
		$manual_extract_found   = (
									file_exists($installer_directory."/main.installer.php")
									&&
									file_exists($installer_directory."/dup-archive__".self::PACKAGE_HASH.".txt")
									&&
									file_exists($installer_directory."/dup-database__".self::PACKAGE_HASH.".sql")
									);
                                    
        $isZip = ($archive_extension == 'zip');

		//MANUAL EXTRACTION NOT FOUND
		if (! $manual_extract_found) {

			//MISSING ARCHIVE FILE
			if (! file_exists($archive_filepath)) {
				self::log("ERROR: Archive file not found!");
				$archive_candidates = ($isZip) ? $this->getFilesWithExtension('zip') : $this->getFilesWithExtension('daf');
				$candidate_count = count($archive_candidates);
				$candidate_html  = "- No {$archive_extension} files found -";

				if ($candidate_count >= 1) {
					$candidate_html = "<ol>";
					foreach($archive_candidates as $archive_candidate) {
						$candidate_html .=  "<li> {$archive_candidate}</li>";
					}
				   $candidate_html .=  "</ol>";
				}

				$error  = "<b>Archive not found!</b> The <i>'Required File'</i> below should be present in the <i>'Extraction Path'</i>.  "
					. "The archive file name must be the <u>exact</u> name of the archive file placed in the extraction path character for character.<br/><br/>  "
					. "If the file does not have the correct name then rename it to the <i>'Required File'</i> below.   When downloading the package files make "
					. "sure both files are from the same package line in the packages view.  If the archive is not finished downloading please wait for it to complete.<br/><br/>"
					. "<b>Required File:</b>  <span class='file-info'>{$archive_filename}</span> <br/>"
					. "<b>Extraction Path:</b> <span class='file-info'>{$this->installerExtractPath}/</span><br/><br/>"
					. "Potential archives found at extraction path: <br/>{$candidate_html}<br/><br/>";

				return $error;
			}

			if (!filter_var(self::ARCHIVE_SIZE, FILTER_VALIDATE_INT) || self::ARCHIVE_SIZE > 2147483647) {
			
				$os_first_three_chars = substr(PHP_OS, 0, 3);
				$os_first_three_chars = strtoupper($os_first_three_chars);
				$no_of_bits = PHP_INT_SIZE * 8;

				if ($no_of_bits == 32) {
					if ($isZip) { // ZIP
						if ('WIN' === $os_first_three_chars) {
							$error = "This package is currently {$archiveExpectedEasy} and it's on a Windows OS. PHP on Windows does not support files larger than 2GB. Please use the file filters to get your package lower to support this server or try the package on a Linux server.";
							return $error;
						}
					} else { // DAF
						if ('WIN' === $os_first_three_chars) {
							$error  = 'Windows PHP limitations prevents extraction of archives larger than 2GB. Please do the following: <ol><li>Download and use the <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-052-q">Windows DupArchive extractor</a> to extract all files from the archive.</li><li>Perform a <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q">Manual Extract Install</a> starting at step 4.</li></ol>';
						} else 	{					
							$error  = 'This archive is too large for 32-bit PHP. Ask your host to upgrade the server to 64-bit PHP or install on another system has 64-bit PHP.';
						}
						return $error;
					}
				}
			}

			//SIZE CHECK ERROR
			if (($this->archiveRatio < 90) && ($this->archiveActualSize > 0) && ($this->archiveExpectedSize > 0)) {
				$this->log("ERROR: The expected archive size should be around [{$archiveExpectedEasy}].  The actual size is currently [{$archiveActualEasy}].");
				$this->log("The archive file may not have fully been downloaded to the server");
				$percent = round($this->archiveRatio);

				$autochecked = isset($_POST['auto-fresh']) ? "checked='true'" : '';
				$error  = "<b>Archive file size warning.</b><br/> The expected archive size should be around <b class='pass'>[{$archiveExpectedEasy}]</b>.  "
					. "The actual size is currently <b class='fail'>[{$archiveActualEasy}]</b>.  The archive file may not have fully been downloaded to the server.  "
					. "Please validate that the file sizes are close to the same size and that the file has been completely downloaded to the destination server.  If the archive is still "
					. "downloading then refresh this page to get an update on the download size.<br/><br/>";

				return $error;
			}

		}


        // OLD COMPATIBILITY MODE
        if (isset($_GET['extract-installer']) && !isset($_GET['force-extract-installer'])) {
            $_GET['force-extract-installer'] = $_GET['extract-installer'];
        }
        
        if ($manual_extract_found) {
			// INSTALL DIRECTORY: Check if its setup correctly AND we are not in overwrite mode
			if (isset($_GET['force-extract-installer']) && ('1' == $_GET['force-extract-installer'] || 'enable' == $_GET['force-extract-installer'] || 'false' == $_GET['force-extract-installer'])) {

				self::log("Manual extract found with force extract installer get parametr");
				$extract_installer = true;

			} else {
				$extract_installer = false;
				self::log("Manual extract found so not going to extract dup-installer dir");
			}
		} else {
			$extract_installer = true;
			self::log("Manual extract didn't found so going to extract dup-installer dir");
		}

		if ($extract_installer && file_exists($installer_directory)) {
			$scanned_directory = array_diff(scandir($installer_directory), array('..', '.'));
			foreach ($scanned_directory as $object) {
				$object_file_path = $installer_directory.'/'.$object;
				if (is_file($object_file_path)) {
					if (unlink($object_file_path)) {
						self::log('Successfully deleted the file '.$object_file_path);
					} else {
						$error .= 'Error deleting the file '.$object_file_path.' Please manually delete it and try again.';
						self::log($error);
					}
				}
			}
		}

		//ATTEMPT EXTRACTION:
		//ZipArchive and Shell Exec
		if ($extract_installer) {
			self::log("Ready to extract the installer");

			self::log("Checking permission of destination folder");
			$destination = dirname(__FILE__);
			if (!is_writable($destination)) {
				self::log("destination folder for extraction is not writable");
				if (@chmod($destination, 0755)) {
					self::log("Permission of destination folder changed to 0755");
				} else {
					self::log("Permission of destination folder failed to change to 0755");
				}
			}

			if (!is_writable($destination)) {
				$error	= "NOTICE: The {$destination} directory is not writable on this server please talk to your host or server admin about making ";
				$error	.= "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-055-q'>writable {$destination} directory</a> on this server. <br/>";
				return $error; 
			}


			if ($isZip) {
				$zip_mode = $this->getZipMode();

				if (($zip_mode == DUPX_Bootstrap_Zip_Mode::AutoUnzip) || ($zip_mode == DUPX_Bootstrap_Zip_Mode::ZipArchive) && class_exists('ZipArchive')) {
					if ($this->hasZipArchive) {
						self::log("ZipArchive exists so using that");
						$extract_success = $this->extractInstallerZipArchive($archive_filepath);

						if ($extract_success) {
							self::log('Successfully extracted with ZipArchive');
						} else {
							if (0 == $this->installer_files_found) {
								$error = "This archive is not properly formatted and does not contain a dup-installer directory. Please make sure you are attempting to install the original archive and not one that has been reconstructed.";
								self::log($error);
								return $error;
							} else {
								$error = 'Error extracting with ZipArchive. ';
								self::log($error);
							}
						}
					} else {
						self::log("WARNING: ZipArchive is not enabled.");
						$error	 = "NOTICE: ZipArchive is not enabled on this server please talk to your host or server admin about enabling ";
						$error	 .= "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-060-q'>ZipArchive</a> on this server. <br/>";
					}
				}

				if (!$extract_success) {
					if (($zip_mode == DUPX_Bootstrap_Zip_Mode::AutoUnzip) || ($zip_mode == DUPX_Bootstrap_Zip_Mode::ShellExec)) {
						$unzip_filepath = $this->getUnzipFilePath();
						if ($unzip_filepath != null) {
							$extract_success = $this->extractInstallerShellexec($archive_filepath);
							if ($extract_success) {
								self::log('Successfully extracted with Shell Exec');
								$error = null;
							} else {
								$error .= 'Error extracting with Shell Exec. Please manually extract archive then choose Advanced > Manual Extract in installer.';
								self::log($error);
							}
						} else {
							self::log('WARNING: Shell Exec Zip is not available');
							$error	 .= "NOTICE: Shell Exec is not enabled on this server please talk to your host or server admin about enabling ";
							$error	 .= "<a target='_blank' href='http://php.net/manual/en/function.shell-exec.php'>Shell Exec</a> on this server or manually extract archive then choose Advanced > Manual Extract in installer.";
						}
					}
				}
				
				// If both ZipArchive and ShellZip are not available, Error message should be combined for both
				if (!$extract_success && $zip_mode == DUPX_Bootstrap_Zip_Mode::AutoUnzip) {
					$unzip_filepath = $this->getUnzipFilePath();
					if (!class_exists('ZipArchive') && empty($unzip_filepath)) {
						$error	 = "NOTICE: ZipArchive and Shell Exec are not enabled on this server please talk to your host or server admin about enabling ";
						$error	 .= "<a target='_blank' href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-trouble-060-q'>ZipArchive</a> or <a target='_blank' href='http://php.net/manual/en/function.shell-exec.php'>Shell Exec</a> on this server or manually extract archive then choose Advanced > Manual Extract in installer.";	
					}
				}
			} else {
				DupArchiveMiniExpander::init("DUPX_Bootstrap::log");
				try {
					DupArchiveMiniExpander::expandDirectory($archive_filepath, self::INSTALLER_DIR_NAME, dirname(__FILE__));
				} catch (Exception $ex) {
					self::log("Error expanding installer subdirectory:".$ex->getMessage());
					throw $ex;
				}
			}

			$is_apache = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);
			$is_nginx = (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);

			$sapi_type = php_sapi_name();
			$php_ini_data = array(						
						'max_execution_time' => 3600,
						'max_input_time' => -1,
						'ignore_user_abort' => 'On',
						'post_max_size' => '4096M',
						'upload_max_filesize' => '4096M',
						'memory_limit' => DUPLICATOR_PHP_MAX_MEMORY,
						'default_socket_timeout' => 3600,
						'pcre.backtrack_limit' => 99999999999,
					);
			$sapi_type_first_three_chars = substr($sapi_type, 0, 3);
			if ('fpm' === $sapi_type_first_three_chars) {
				self::log("SAPI: FPM");
				if ($is_apache) {
					self::log('Server: Apache');
				} elseif ($is_nginx) {
					self::log('Server: Nginx');
				}

				if (($is_apache && function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) || $is_nginx) {
					$htaccess_data = array();
					foreach ($php_ini_data as $php_ini_key=>$php_ini_val) {
						if ($is_apache) {
							$htaccess_data[] = 'SetEnv PHP_VALUE "'.$php_ini_key.' = '.$php_ini_val.'"';
						} elseif ($is_nginx) {
							if ('On' == $php_ini_val || 'Off' == $php_ini_val) {
								$htaccess_data[] = 'php_flag '.$php_ini_key.' '.$php_ini_val;
							} else {
								$htaccess_data[] = 'php_value '.$php_ini_key.' '.$php_ini_val;
							}							
						}
					}				
				
					$htaccess_text = implode("\n", $htaccess_data);
					$htaccess_file_path = dirname(__FILE__).'/dup-installer/.htaccess';
					self::log("creating {$htaccess_file_path} with the content:");
					self::log($htaccess_text);
					@file_put_contents($htaccess_file_path, $htaccess_text);
				}
			} elseif ('cgi' === $sapi_type_first_three_chars || 'litespeed' === $sapi_type) {
				if ('cgi' === $sapi_type_first_three_chars) {
					self::log("SAPI: CGI");
				} else {
					self::log("SAPI: litespeed");
				}
				if (version_compare(phpversion(), 5.5) >= 0 && (!$is_apache || 'litespeed' === $sapi_type)) {
					$ini_data = array();
					foreach ($php_ini_data as $php_ini_key=>$php_ini_val) {
						$ini_data[] = $php_ini_key.' = '.$php_ini_val;
					}
					$ini_text = implode("\n", $ini_data);
					$ini_file_path = dirname(__FILE__).'/dup-installer/.user.ini';
					self::log("creating {$ini_file_path} with the content:");
					self::log($ini_text);
					@file_put_contents($ini_file_path, $ini_text);
				} else{
					self::log("No need to create dup-installer/.htaccess or dup-installer/.user.ini");
				}
			} else {
				self::log("No need to create dup-installer/.htaccess or dup-installer/.user.ini");
				self::log("SAPI: Unrecognized");
			}
		} else {
			self::log("Didn't need to extract the installer.");
		}

		if (empty($error)) {
			$config_files = glob('./dup-installer/dup-archive__*.txt');
			$config_file_absolute_path = array_pop($config_files);
			if (!file_exists($config_file_absolute_path)) {
				$error = '<b>Archive config file not found in dup-installer folder.</b> <br><br>';
				return $error;
			}
		}
		
		$is_https = $this->isHttps();

		if($is_https) {
			$current_url = 'https://';
		} else {
			$current_url = 'http://';
		}

		if(($_SERVER['SERVER_PORT'] == 80) && ($is_https)) {
			// Fixing what appears to be a bad server setting
			$server_port = 443;
		} else {
			$server_port = $_SERVER['SERVER_PORT'];
		}


		//$current_url .= $_SERVER['HTTP_HOST'];//WAS SERVER_NAME and caused problems on some boxes
		$current_url .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];//WAS SERVER_NAME and caused problems on some boxes
		if(strpos($current_url,':') === false) {
                   $current_url = $current_url.':'.$server_port;
                }
                
		$current_url .= $_SERVER['REQUEST_URI'];
		$uri_start    = dirname($current_url);

        $encoded_archive_path = urlencode($archive_filepath);

		if ($error === null) {
                    $error = $this->postExtractProcessing();

                    if($error == null) {

                        $bootloader_name	 = basename(__FILE__);
                        $this->mainInstallerURL = $uri_start.'/'.self::INSTALLER_DIR_NAME.'/main.installer.php';

                        $this->fixInstallerPerms($this->mainInstallerURL);

						$this->archive = $archive_filepath;
						$this->bootloader = $bootloader_name;

                        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
                                $this->mainInstallerURL .= '?'.$_SERVER['QUERY_STRING'];
                        }

                        self::log("No detected errors so redirecting to the main installer. Main Installer URI = {$this->mainInstallerURL}");
                    }
                }

		return $error;
	}

	public function postExtractProcessing()
	{
		$dproInstallerDir = dirname(__FILE__) . '/dup-installer';                
		$libDir = $dproInstallerDir . '/lib';
		$fileopsDir = $libDir . '/fileops';
        
        if(!file_exists($dproInstallerDir)) {
        
            return 'Can\'t extract installer directory. See <a target="_blank" href="https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-022-q">this FAQ item</a> for details on how to resolve.</a>';
        }

		$sourceFilepath = "{$fileopsDir}/fileops.ppp";
		$destFilepath = "{$fileopsDir}/fileops.php";

		if(file_exists($sourceFilepath) && (!file_exists($destFilepath))) {
			if(@rename($sourceFilepath, $destFilepath) === false) {
				return "Error renaming {$sourceFilepath}";
			}
		}                
	}

	/**
     * Indicates if site is running https or not
     *
     * @return bool  Returns true if https, false if not
     */
	public function isHttps()
	{
		$retVal = true;

		if (isset($_SERVER['HTTPS'])) {
			$retVal = ($_SERVER['HTTPS'] !== 'off');
		} else {
			$retVal = ($_SERVER['SERVER_PORT'] == 443);
            }

		return $retVal;
	}

	/**
     *  Attempts to set the 'dup-installer' directory permissions
     *
     * @return null
     */
	private function fixInstallerPerms()
	{
		$file_perms = substr(sprintf('%o', fileperms(__FILE__)), -4);
		$file_perms = octdec($file_perms);
		//$dir_perms = substr(sprintf('%o', fileperms(dirname(__FILE__))), -4);

		// No longer using existing directory permissions since that can cause problems.  Just set it to 755
		$dir_perms = '755';
		$dir_perms = octdec($dir_perms);
		$installer_dir_path = $this->installerContentsPath;

		$this->setPerms($installer_dir_path, $dir_perms, false);
		$this->setPerms($installer_dir_path, $file_perms, true);
	}

	/**
     * Set the permissions of a given directory and optionally all files
     *
     * @param string $directory		The full path to the directory where perms will be set
     * @param string $perms			The given permission sets to use such as '0755'
	 * @param string $do_files		Also set the permissions of all the files in the directory
     *
     * @return null
     */
	private function setPerms($directory, $perms, $do_files)
	{
		if (!$do_files) {
			// If setting a directory hiearchy be sure to include the base directory
			$this->setPermsOnItem($directory, $perms);
		}

		$item_names = array_diff(scandir($directory), array('.', '..'));

		foreach ($item_names as $item_name) {
			$path = "$directory/$item_name";
			if (($do_files && is_file($path)) || (!$do_files && !is_file($path))) {
				$this->setPermsOnItem($path, $perms);
			}
		}
	}

	/**
     * Set the permissions of a single directory or file
     *
     * @param string $path			The full path to the directory or file where perms will be set
     * @param string $perms			The given permission sets to use such as '0755'
     *
     * @return bool		Returns true if the permission was properly set
     */
	private function setPermsOnItem($path, $perms)
	{
		$result = @chmod($path, $perms);
		$perms_display = decoct($perms);
		if ($result === false) {
			self::log("Couldn't set permissions of $path to {$perms_display}<br/>");
		} else {
			self::log("Set permissions of $path to {$perms_display}<br/>");
		}
		return $result;
	}


	/**
     * Logs a string to the dup-installer-bootlog__[HASH].txt file
     *
     * @param string $s			The string to log to the log file
     *
     * @return null
     */
	public static function log($s)
	{
		$timestamp = date('M j H:i:s');
		file_put_contents('./dup-installer-bootlog__'.self::PACKAGE_HASH.'.txt', "$timestamp $s\n", FILE_APPEND);
	}

	/**
     * Extracts only the 'dup-installer' files using ZipArchive
     *
     * @param string $archive_filepath	The path to the archive file.
     *
     * @return bool		Returns true if the data was properly extracted
     */
	private function extractInstallerZipArchive($archive_filepath, $checkSubFolder = false)
	{
		$success	 = true;
		$zipArchive	 = new ZipArchive();
		$subFolderArchiveList   = array();

		if ($zipArchive->open($archive_filepath) === true) {
			self::log("Successfully opened $archive_filepath");
			$destination = dirname(__FILE__);
			$folder_prefix = self::INSTALLER_DIR_NAME.'/';
			self::log("Extracting all files from archive within ".self::INSTALLER_DIR_NAME);

			$this->installer_files_found = 0;

			for ($i = 0; $i < $zipArchive->numFiles; $i++) {
				$stat		 = $zipArchive->statIndex($i);
				if ($checkSubFolder == false) {
					$filenameCheck = $stat['name'];
					$filename = $stat['name'];
                    $tmpSubFolder = null;
				} else {
                    $safePath = rtrim(self::setSafePath($stat['name']) , '/');
					$tmpArray = explode('/' , $safePath);
					
					if (count($tmpArray) < 2)  {
						continue;
					}

					$tmpSubFolder = $tmpArray[0];
					array_shift($tmpArray);
					$filenameCheck = implode('/' , $tmpArray);
					$filename = $stat['name'];
				}

				
				if ($this->startsWith($filenameCheck , $folder_prefix)) {
					$this->installer_files_found++;

					if (!empty($tmpSubFolder) && !in_array($tmpSubFolder , $subFolderArchiveList)) {
						$subFolderArchiveList[] = $tmpSubFolder;
					}

					if ($zipArchive->extractTo($destination, $filename) === true) {
						self::log("Success: {$filename} >>> {$destination}");
					} else {
						self::log("Error extracting {$filename} from archive archive file");
						$success = false;
						break;
					}
				}
			}

			if ($checkSubFolder && count($subFolderArchiveList) !== 1) {
				self::log("Error: Multiple dup subfolder archive");
				$success = false;			
			} else {
				if ($checkSubFolder) {
					$this->moveUpfromSubFolder(dirname(__FILE__).'/'.$subFolderArchiveList[0] , true);
				}

			    $lib_directory = dirname(__FILE__).'/'.self::INSTALLER_DIR_NAME.'/lib';
			    $snaplib_directory = $lib_directory.'/snaplib';

			    // If snaplib files aren't present attempt to extract and copy those
			    if(!file_exists($snaplib_directory))
			    {
				$folder_prefix = 'snaplib/';
				$destination = $lib_directory;

				for ($i = 0; $i < $zipArchive->numFiles; $i++) {
				    $stat		 = $zipArchive->statIndex($i);
				    $filename	 = $stat['name'];

				    if ($this->startsWith($filename, $folder_prefix)) {
				        $this->installer_files_found++;

				        if ($zipArchive->extractTo($destination, $filename) === true) {
				            self::log("Success: {$filename} >>> {$destination}");
				        } else {
				            self::log("Error extracting {$filename} from archive archive file");
				            $success = false;
				            break;
				        }
				    }
				}
			    }
			}

			if ($zipArchive->close() === true) {
				self::log("Successfully closed archive file");
			} else {
				self::log("Problem closing archive file");
				$success = false;
			}
			
			if ($success != false && $this->installer_files_found < 10) {
				if ($checkSubFolder) {
					self::log("Couldn't find the installer directory in the archive!");
					$success = false;
				} else {
					self::log("Couldn't find the installer directory in archive root! Check subfolder");
					$this->extractInstallerZipArchive($archive_filepath, true);
				}
			}
		} else {
			self::log("Couldn't open archive archive file with ZipArchive");
			$success = false;
		}

		return $success;
	}
    
    /**
     * move all folder content up to parent
     *
     * @param string $subFolderName full path
     * @param boolean $deleteSubFolder if true delete subFolder after moved all
     * @return boolean
     * 
     */
    private function moveUpfromSubFolder($subFolderName, $deleteSubFolder = false)
    {
        if (!is_dir($subFolderName)) {
            return false;
        }

        $parentFolder = dirname($subFolderName);
        if (!is_writable($parentFolder)) {
            return false;
        }

        $success = true;
        if (($subList = glob(rtrim($subFolderName, '/').'/*', GLOB_NOSORT)) === false) {
            self::log("Problem glob folder ".$subFolderName);
            return false;
        } else {
            foreach ($subList as $cName) {
                $destination = $parentFolder.'/'.basename($cName);
                if (file_exists($destination)) {
                    $success = self::deletePath($destination);
                }

                if ($success) {
                    $success = rename($cName, $destination);
                } else {
                    break;
                }
            }

            if ($success && $deleteSubFolder) {
                $success = self::deleteDirectory($subFolderName, true);
            }
        }

        if (!$success) {
            self::log("Problem om moveUpfromSubFolder subFolder:".$subFolderName);
        }

        return $success;
    }

	/**
     * Extracts only the 'dup-installer' files using Shell-Exec Unzip
     *
     * @param string $archive_filepath	The path to the archive file.
     *
     * @return bool		Returns true if the data was properly extracted
     */
	private function extractInstallerShellexec($archive_filepath)
	{
		$success = false;
		self::log("Attempting to use Shell Exec");
		$unzip_filepath	 = $this->getUnzipFilePath();

		if ($unzip_filepath != null) {
			$unzip_command	 = "$unzip_filepath -q $archive_filepath ".self::INSTALLER_DIR_NAME.'/* 2>&1';
			self::log("Executing $unzip_command");
			$stderr	 = shell_exec($unzip_command);

            $lib_directory = dirname(__FILE__).'/'.self::INSTALLER_DIR_NAME.'/lib';
            $snaplib_directory = $lib_directory.'/snaplib';

            // If snaplib files aren't present attempt to extract and copy those
            if(!file_exists($snaplib_directory))
            {
                $local_lib_directory = dirname(__FILE__).'/snaplib';
                $unzip_command	 = "$unzip_filepath -q $archive_filepath snaplib/* 2>&1";
                self::log("Executing $unzip_command");
                $stderr	 .= shell_exec($unzip_command);
				mkdir($lib_directory);
                rename($local_lib_directory, $snaplib_directory);
            }

			if ($stderr == '') {
				self::log("Shell exec unzip succeeded");
				$success = true;
			} else {
				self::log("Shell exec unzip failed. Output={$stderr}");
			}
		}

		return $success;
	}

	/**
     * Attempts to get the archive file path
     *
     * @return string	The full path to the archive file
     */
	private function getArchiveFilePath()
	{
		if (isset($_GET['archive'])) {
			$archive_filepath = $_GET['archive'];
		} else {
		$archive_filename = self::ARCHIVE_FILENAME;
			$archive_filepath = str_replace("\\", '/', dirname(__FILE__) . '/' . $archive_filename);
		}

		self::log("Using archive $archive_filepath");
		return $archive_filepath;
	}

	/**
     * Gets the DUPX_Bootstrap_Zip_Mode enum type that should be used
     *
     * @return DUPX_Bootstrap_Zip_Mode	Returns the current mode of the bootstrapper
     */
	private function getZipMode()
	{
		$zip_mode = DUPX_Bootstrap_Zip_Mode::AutoUnzip;

		if (isset($_GET['zipmode'])) {
			$zipmode_string = $_GET['zipmode'];
			self::log("Unzip mode specified in querystring: $zipmode_string");

			switch ($zipmode_string) {
				case 'autounzip':
					$zip_mode = DUPX_Bootstrap_Zip_Mode::AutoUnzip;
					break;

				case 'ziparchive':
					$zip_mode = DUPX_Bootstrap_Zip_Mode::ZipArchive;
					break;

				case 'shellexec':
					$zip_mode = DUPX_Bootstrap_Zip_Mode::ShellExec;
					break;
			}
		}

		return $zip_mode;
	}

	/**
     * Checks to see if a string starts with specific characters
     *
     * @return bool		Returns true if the string starts with a specific format
     */
	private function startsWith($haystack, $needle)
	{
		return $needle === "" || strrpos($haystack, $needle, - strlen($haystack)) !== false;
	}

	/**
     * Checks to see if the server supports issuing commands to shell_exex
     *
     * @return bool		Returns true shell_exec can be ran on this server
     */
	public function hasShellExec()
	{
		$cmds = array('shell_exec', 'escapeshellarg', 'escapeshellcmd', 'extension_loaded');

		//Function disabled at server level
		if (array_intersect($cmds, array_map('trim', explode(',', @ini_get('disable_functions'))))) return false;

		//Suhosin: http://www.hardened-php.net/suhosin/
		//Will cause PHP to silently fail
		if (extension_loaded('suhosin')) {
			$suhosin_ini = @ini_get("suhosin.executor.func.blacklist");
			if (array_intersect($cmds, array_map('trim', explode(',', $suhosin_ini)))) return false;
		}
		// Can we issue a simple echo command?
		if (!@shell_exec('echo duplicator')) return false;

		return true;
	}

	/**
     * Gets the possible system commands for unzip on Linux
     *
     * @return string		Returns unzip file path that can execute the unzip command
     */
	public function getUnzipFilePath()
	{
		$filepath = null;

		if ($this->hasShellExec()) {
			if (shell_exec('hash unzip 2>&1') == NULL) {
				$filepath = 'unzip';
			} else {
				$possible_paths = array(
					'/usr/bin/unzip',
					'/opt/local/bin/unzip',
					'/bin/unzip',
					'/usr/local/bin/unzip',
					'/usr/sfw/bin/unzip',
					'/usr/xdg4/bin/unzip',
					'/opt/bin/unzip',					
					// RSR TODO put back in when we support shellexec on windows,
				);

				foreach ($possible_paths as $path) {
					if (file_exists($path)) {
						$filepath = $path;
						break;
					}
				}
			}
		}

		return $filepath;
	}

	/**
	 * Display human readable byte sizes such as 150MB
	 *
	 * @param int $size		The size in bytes
	 *
	 * @return string A readable byte size format such as 100MB
	 */
	public function readableByteSize($size)
	{
		try {
			$units = array('B', 'KB', 'MB', 'GB', 'TB');
			for ($i = 0; $size >= 1024 && $i < 4; $i++)
				$size /= 1024;
			return round($size, 2).$units[$i];
		} catch (Exception $e) {
			return "n/a";
		}
	}

	/**
     *  Returns an array of zip files found in the current executing directory
     *
     *  @return array of zip files
     */
    public static function getFilesWithExtension($extension)
    {
        $files = array();
        foreach (glob("*.{$extension}") as $name) {
            if (file_exists($name)) {
                $files[] = $name;
            }
        }

        if (count($files) > 0) {
            return $files;
        }

        //FALL BACK: Windows XP has bug with glob,
        //add secondary check for PHP lameness
        if ($dh = opendir('.')) {
            while (false !== ($name = readdir($dh))) {
                $ext = substr($name, strrpos($name, '.') + 1);
                if (in_array($ext, array($extension))) {
                    $files[] = $name;
                }
            }
            closedir($dh);
        }

        return $files;
    }
    
	/**
     * Safely remove a directory and recursively if needed
     *
     * @param string $directory The full path to the directory to remove
     * @param string $recursive recursively remove all items
     *
     * @return bool Returns true if all content was removed
     */
    public static function deleteDirectory($directory, $recursive)
    {
        $success = true;

        $filenames = array_diff(scandir($directory), array('.', '..'));

        foreach ($filenames as $filename) {
            $fullPath = $directory.'/'.$filename;

            if (is_dir($fullPath)) {
                if ($recursive) {
                    $success = self::deleteDirectory($fullPath, true);
                }
            } else {
                $success = @unlink($fullPath);
                if ($success === false) {
                    self::log( __FUNCTION__.": Problem deleting file:".$fullPath);
                }
            }

            if ($success === false) {
                self::log("Problem deleting dir:".$directory);
                break;
            }
        }

        return $success && rmdir($directory);
    }

    /**
     * Safely remove a file or directory and recursively if needed
     *
     * @param string $directory The full path to the directory to remove
     *
     * @return bool Returns true if all content was removed
     */
    public static function deletePath($path)
    {
        $success = true;

        if (is_dir($path)) {
            $success = self::deleteDirectory($path, true);
        } else {
            $success = @unlink($path);

            if ($success === false) {
                self::log( __FUNCTION__.": Problem deleting file:".$path);
            }
        }

        return $success;
    }
    
    /**
	 *  Makes path safe for any OS for PHP
	 *
	 *  Paths should ALWAYS READ be "/"
	 * 		uni:  /home/path/file.txt
	 * 		win:  D:/home/path/file.txt
	 *
	 *  @param string $path		The path to make safe
	 *
	 *  @return string The original $path with a with all slashes facing '/'.
	 */
	public static function setSafePath($path)
	{
		return str_replace("\\", "/", $path);
	}
}

try {
    $boot  = new DUPX_Bootstrap();
    $boot_error = $boot->run();
    $auto_refresh = isset($_POST['auto-fresh']) ? true : false;
    DUPX_CSRF::resetAllTokens();
} catch (Exception $e) {
   $boot_error = $e->getMessage();
}

if ($boot_error == null) {
	$step1_csrf_token = DUPX_CSRF::generate('step1');
	DUPX_CSRF::setCookie('archive', $boot->archive);
	DUPX_CSRF::setCookie('bootloader', $boot->bootloader);
}
?>

<html>
<?php if ($boot_error == null) :?>
	<head>
		<meta name="robots" content="noindex,nofollow">
		<title>Duplicator Installer</title>
	</head>
	<body>
		<?php
		$id = uniqid();
		$html = "<form id='{$id}' method='post' action='{$boot->mainInstallerURL}' />\n";
		$data = array(
			'archive' => $boot->archive,
			'bootloader' => $boot->bootloader,
			'csrf_token' => $step1_csrf_token,
		);
		foreach ($data as $name => $value) {			
			$html .= "<input type='hidden' name='{$name}' value='{$value}' />\n";
		}
		$html .= "</form>\n";
		$html .= "<script>window.onload = function() { document.getElementById('{$id}').submit(); }</script>";
		echo $html;
		?>
	</body>
<?php else :?>
	<head>
		<style>
			body {font-family:Verdana,Arial,sans-serif; line-height:18px; font-size: 12px}
			h2 {font-size:20px; margin:5px 0 5px 0; border-bottom:1px solid #dfdfdf; padding:3px}
			div#content {border:1px solid #CDCDCD; width:750px; min-height:550px; margin:auto; margin-top:18px; border-radius:5px; box-shadow:0 8px 6px -6px #333; font-size:13px}
			div#content-inner {padding:10px 30px; min-height:550px}

			/* Header */
			table.header-wizard {border-top-left-radius:5px; border-top-right-radius:5px; width:100%; box-shadow:0 5px 3px -3px #999; background-color:#F1F1F1; font-weight:bold}
			table.header-wizard td.header {font-size:24px; padding:7px 0 7px 0; width:100%;}
			div.dupx-logfile-link {float:right; font-weight:normal; font-size:12px}
			.dupx-version {white-space:nowrap; color:#999; font-size:11px; font-style:italic; text-align:right;  padding:0 15px 5px 0; line-height:14px; font-weight:normal}
			.dupx-version a { color:#999; }

			div.errror-notice {text-align:center; font-style:italic; font-size:11px}
			div.errror-msg { color:maroon; padding: 10px 0 5px 0}
			.pass {color:green}
			.fail {color:red}
			span.file-info {font-size: 11px; font-style: italic}
			div.skip-not-found {padding:10px 0 5px 0;}
			div.skip-not-found label {cursor: pointer}
			table.settings {width:100%; font-size:12px}
			table.settings td {padding: 4px}
			table.settings td:first-child {font-weight: bold}
			.w3-light-grey,.w3-hover-light-grey:hover,.w3-light-gray,.w3-hover-light-gray:hover{color:#000!important;background-color:#f1f1f1!important}
			.w3-container:after,.w3-container:before,.w3-panel:after,.w3-panel:before,.w3-row:after,.w3-row:before,.w3-row-padding:after,.w3-row-padding:before,
			.w3-cell-row:before,.w3-cell-row:after,.w3-clear:after,.w3-clear:before,.w3-bar:before,.w3-bar:after
			{content:"";display:table;clear:both}
			.w3-green,.w3-hover-green:hover{color:#fff!important;background-color:#4CAF50!important}
			.w3-container{padding:0.01em 16px}
			.w3-center{display:inline-block;width:auto; text-align: center !important}
		</style>
	</head>
	<body>
	<div id="content">

		<table cellspacing="0" class="header-wizard">
			<tr>
				<td class="header"> &nbsp; Duplicator - Bootloader</td>
				<td class="dupx-version">
					version: <?php echo DUPX_Bootstrap::VERSION ?> <br/>
				</td>
			</tr>
		</table>

		<form id="error-form" method="post">
		<div id="content-inner">
			<h2 style="color:maroon">Setup Notice:</h2>
			<div class="errror-notice">An error has occurred. In order to load the full installer please resolve the issue below.</div>
			<div class="errror-msg">
				<?php echo $boot_error ?>
			</div>
			<br/><br/>

			<h2>Server Settings:</h2>
			<table class='settings'>
				<tr>
					<td>ZipArchive:</td>
					<td><?php echo $boot->hasZipArchive  ? '<i class="pass">Enabled</i>' : '<i class="fail">Disabled</i>'; ?> </td>
				</tr>
				<tr>
					<td>ShellExec&nbsp;Unzip:</td>
					<td><?php echo $boot->hasShellExecUnzip	? '<i class="pass">Enabled</i>' : '<i class="fail">Disabled</i>'; ?> </td>
				</tr>
				<tr>
					<td>Extraction&nbsp;Path:</td>
					<td><?php echo $boot->installerExtractPath; ?></td>
				</tr>
				<tr>
					<td>Installer Path:</td>
					<td><?php echo $boot->installerContentsPath; ?></td>
				</tr>
				<tr>
					<td>Archive Name:</td>
					<td>
						[HASH]_archive.zip or [HASH]_archive.daf<br/>
						<small>This is based on the format used to build the archive</small>
					</td>
				</tr>
				<tr>
					<td>Archive Size:</td>
					<td>
						<b>Expected Size:</b> <?php echo $boot->readableByteSize($boot->archiveExpectedSize); ?>  &nbsp;
						<b>Actual Size:</b>   <?php echo $boot->readableByteSize($boot->archiveActualSize); ?>
					</td>
				</tr>
				<tr>
					<td>Boot Log</td>
					<td>dup-installer-bootlog__[HASH].txt</td>
				</tr>
			</table>
			<br/><br/>

			<div style="font-size:11px">
				Please Note: Either ZipArchive or Shell Exec will need to be enabled for the installer to run automatically otherwise a manual extraction
				will need to be performed.  In order to run the installer manually follow the instructions to
				<a href='https://snapcreek.com/duplicator/docs/faqs-tech/#faq-installer-015-q' target='_blank'>manually extract</a> before running the installer.
			</div>
			<br/><br/>

		</div>
		</form>

	</div>
	</body>

	<script>
		function AutoFresh() {
			document.getElementById('error-form').submit();
		}
		<?php if ($auto_refresh) :?>
			var duration = 10000; //10 seconds
			var counter  = 10;
			var countElement = document.getElementById('count-down');

			setTimeout(function(){window.location.reload(1);}, duration);
			setInterval(function() {
				counter--;
				countElement.innerHTML = (counter > 0) ? counter.toString() : "0";
			}, 1000);

		<?php endif; ?>
	</script>


<?php endif; ?>


@@DUPARCHIVE_MINI_EXPANDER@@
<!--
Used for integrity check do not remove:
DUPLICATOR_INSTALLER_EOF  -->
</html>
