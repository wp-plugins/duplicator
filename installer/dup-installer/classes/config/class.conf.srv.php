<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Class for server type enum setup
 * .htaccess, web.config and .user.ini
 *
 */
abstract class DUPX_ServerConfigTypes
{
    const Apache		= 0;
    const IIS			= 1;
    const WordFence     = 2;
}


/**
 * Class used to update and edit web server configuration files
 * for .htaccess, web.config and .user.ini
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 */
class DUPX_ServerConfig
{
	protected static $fileHash;
	protected static $timeStamp;
	protected static $confFileApache;
	protected static $confFileApacheOrig;
	protected static $confFileIIS;
	protected static $confFileIISOrig;
	protected static $confFileWordFence;
	protected static $configMode;
	protected static $newSiteURL;
	protected static $rootPath;

	/**
	 *  Setup this classes properties
	 */
	public static function init()
	{
		self::$fileHash				= date("ymdHis") . '-' . uniqid();
		self::$timeStamp			= date("Y-m-d H:i:s");
		self::$rootPath				= "{$GLOBALS['DUPX_ROOT']}";
		self::$confFileApache		= "{$GLOBALS['DUPX_ROOT']}/.htaccess";
		self::$confFileApacheOrig	= "{$GLOBALS['DUPX_ROOT']}/htaccess.orig";
		self::$confFileIIS			= "{$GLOBALS['DUPX_ROOT']}/web.config";
		self::$confFileIISOrig		= "{$GLOBALS['DUPX_ROOT']}/web.config.orig";
		self::$confFileWordFence	= "{$GLOBALS['DUPX_ROOT']}/.user.ini";
		self::$configMode           = isset($_POST['config_mode']) ? DUPX_U::sanitize_text_field($_POST['config_mode'])  : null;
		self::$newSiteURL           = isset($_POST['url_new']) ? DUPX_U::sanitize_text_field($_POST['url_new']) : null;
	}

	/**
	 * After the archive is extracted run setup checks
	 *
	 * @return null
	 */
	public static function afterExtractionSetup()
	{
		if (self::$configMode  != 'IGNORE') {
			//WORDFENCE: Only the WordFence file needs to be removed
			//completly from setup to avoid any issues
			self::removeFile(self::$confFileWordFence, DUPX_ServerConfigTypes::WordFence);
		} else {
			DUPX_Log::info("** CONFIG FILE SET TO IGNORE ALL CHANGES **");
		}
	}

	/**
	 * Before the archive is extracted run a series of back and remove checks
	 * This is for existing config files that may exist before the ones in the
	 * archive are extracted.
	 *
	 * @return void
	 */
	public static function beforeExtractionSetup()
	{
		if (self::$configMode != 'IGNORE') {
			//---------------------
			//APACHE
			if (self::createBackup(self::$confFileApache, DUPX_ServerConfigTypes::Apache))
				self::removeFile(self::$confFileApache, DUPX_ServerConfigTypes::Apache);

			//---------------------
			//MICROSOFT IIS
			if (self::createBackup(self::$confFileIIS, DUPX_ServerConfigTypes::IIS))
				 self::removeFile(self::$confFileIIS, DUPX_ServerConfigTypes::IIS);

			//---------------------
			//WORDFENCE
			if (self::createBackup(self::$confFileWordFence, DUPX_ServerConfigTypes::WordFence))
				 self::removeFile(self::$confFileWordFence, DUPX_ServerConfigTypes::WordFence);
		}
	}

    /**
     * Copies the code in htaccess.orig and web.config.orig
	 * to .htaccess and web.config
     *
	 * @return void
     */
	public static function renameOrigConfigs()
	{
		//APACHE
		if(rename(self::$confFileApacheOrig, self::$confFileApache)){
			DUPX_Log::info("\n- PASS: The orginal htaccess.orig was renamed");
		} else {
			DUPX_Log::info("\n- WARN: The orginal htaccess.orig was NOT renamed");
		}

		//IIS
		if(rename(self::$confFileIISOrig, self::$confFileIIS)){
			DUPX_Log::info("\n- PASS: The orginal htaccess.orig was renamed");
		} else {
			DUPX_Log::info("\n- WARN: The orginal htaccess.orig was NOT renamed");
		}
    }

	 /**
     * Creates the new config file
     *
	 * @return void
     */
	public static function createNewConfigs()
	{
		$config_made = false;

		//APACHE
		if(file_exists(self::$confFileApacheOrig)){
			self::createNewApacheConfig();
			self::removeFile(self::$confFileApacheOrig, DUPX_ServerConfigTypes::Apache);
			$config_made = true;
		}

		if(file_exists(self::$confFileIISOrig)){
			self::createNewIISConfig();
			self::removeFile(self::$confFileIISOrig, DUPX_ServerConfigTypes::IIS);
			$config_made = true;
		}

		//No config was made so try to guess which one
		//95% of the time it will be Apache
		if (! $config_made) {
			if (DUPX_Server::isIISRunning()) {
				self::createNewIISConfig();
			} else {
				self::createNewApacheConfig();
			}
		}
    }

	/**
	 * Sets up the web config file based on the inputs from the installer forms.
	 *
	 * @return void
	 */
	private static function createNewApacheConfig()
	{
		$timestamp   = self::$timeStamp;
		$newdata	 = parse_url(self::$newSiteURL);
		$newpath	 = DUPX_U::addSlash(isset($newdata['path']) ? $newdata['path'] : "");
		$update_msg  = "#This Apache config file was created by Duplicator Installer on {$timestamp}.\n";
		$update_msg .= "#The original can be found in archived file with the name htaccess.orig\n";

        $tmp_htaccess = <<<HTACCESS
{$update_msg}
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$newpath}
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . {$newpath}index.php [L]
</IfModule>
# END WordPress
HTACCESS;

		if (@file_put_contents(self::$confFileApache, $tmp_htaccess) === FALSE) {
			DUPX_Log::info("- WARN: Unable to create the .htaccess file! Please check the permission on the root directory and make sure the .htaccess exists.");
		} else {
			DUPX_Log::info("- PASS: Successfully created a new .htaccess file.");
			@chmod(self::$confFileApache, 0644);
		}
    }

	/**
	 * Sets up the web config file based on the inputs from the installer forms.
	 *
	 * @return void
	 */
	private static function createNewIISConfig()
	{
		$timestamp = self::$timeStamp;
		$xml_contents  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml_contents .= "<!-- This new IIS config file was created by Duplicator Installer on {$timestamp}.\n"
					  .  "The original can be found in archived file with the name web.config.orig -->\n";
		$xml_contents .=  "<configuration></configuration>\n";
		@file_put_contents(self::$confFileIIS, $xml_contents);
    }
	
	/**
	 * Creates a copy of any existing file and hashes it with a .bak extension
	 *
	 * @param string $file_path					The full path of the config file
	 * @param DUPX_ServerConfigTypes $type		A valid DUPX_ServerConfigTypes
	 *
	 * @return bool		Returns true if the file was backed-up.
	 */
	private static function createBackup($file_path, $type)
	{
		$status		= false;
		$file_name  = DupLiteSnapLibIOU::getFileName($file_path);
		$hash		= self::$fileHash;
		$source = self::getTypeName($type);
		if (is_file($file_path)) {
			if (! self::backupExists($type)) {
				$status = copy($file_path, "{$file_path}-{$hash}-duplicator.bak");
				$status ? DUPX_Log::info("- PASS: {$source} '{$file_name}' backed-up to {$file_name}-{$hash}-duplicator.bak")
						: DUPX_Log::info("- WARN: {$source} '{$file_name}' unable to create backup copy, a possible permission error?");
			}
		} else {
			DUPX_Log::info("- PASS: {$source} '{$file_name}' not found - no backup needed.");
		}

		return $status;
	}

	/**
	 * Removes the specified file
	 *
	 * @param string $file_path					The full path of the config file
	 * @param DUPX_ServerConfigTypes $type		A valid DUPX_ServerConfigTypes
	 *
	 * @return bool		Returns true if the file was removed
	 */
	private static function removeFile($file_path, $type)
	{
		$status = false;
		if (is_file($file_path)) {
			$source		= self::getTypeName($type);
			$file_name  = DupLiteSnapLibIOU::getFileName($file_path);
			$status = @unlink($file_path);
			if ($status === FALSE) {
				@chmod($file_path, 0777);
				$status = @unlink($file_path);
			}
			$status ? DUPX_Log::info("- PASS: Existing {$source} '{$file_name}' was removed")
					: DUPX_Log::info("- WARN: Existing {$source} '{$file_path}' not removed, a possible permission error?");
		}
		return $status;
	}

	/**
	 * Check if a backup file already exists
	 *
	 * @param DUPX_ServerConfigTypes $type		A valid DUPX_ServerConfigTypes
	 *
	 * @return bool		Returns true if the file was removed
	 */
	private static function backupExists($type)
	{
		$pattern = 'unknown-duplicator-type-set';

		switch ($type) {
			case DUPX_ServerConfigTypes::Apache:
				$pattern = '/.htaccess-.*-duplicator.bak/';
				break;
			case DUPX_ServerConfigTypes::IIS:
				$pattern = '/web.config-.*-duplicator.bak/';
				break;
			case DUPX_ServerConfigTypes::WordFence:
				$pattern = '/.user.ini-.*-duplicator.bak/';
				break;
		}

		if (is_dir(self::$rootPath)){
			$dir = new DirectoryIterator(self::$rootPath);
			foreach ($dir as $file) {
				if ($file->isFile()) {
					$name = $file->getFilename();
					if (strpos($name, '-duplicator.bak')) {
						if (preg_match($pattern, $name))
							return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Gets the friendly type name
	 *
	 * @param DUPX_ServerConfigTypes $type		A valid DUPX_ServerConfigTypes
	 *
	 * @return string		The friendly enum name
	 */
	private static function getTypeName($type)
	{
		switch ($type) {
			case DUPX_ServerConfigTypes::Apache:
				return 'Apache';
				break;
			case DUPX_ServerConfigTypes::IIS:
				return 'Microsoft IIS';
				break;
			case DUPX_ServerConfigTypes::WordFence:
				return 'WordFence';
				break;			

			default:
				throw new Exception('The param $type must be of type DUPX_ServerConfigTypes');
				break;
		}
	}
    
    /**
     *
     * @staticvar string $path
     * @return string
     */
    public static function getWpconfigArkPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = $GLOBALS['DUPX_AC']->installSiteOverwriteOn ? $GLOBALS['DUPX_ROOT'].'/dup-wp-config-arc__'.$GLOBALS['DUPX_AC']->package_hash.'.txt' : $GLOBALS['DUPX_ROOT'].'/wp-config.php';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $path
     * @return string
     */
    public static function getHtaccessArkPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = $GLOBALS['DUPX_ROOT'].'/htaccess.orig';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $path
     * @return string
     */
    public static function getOrigWpConfigPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = $GLOBALS['DUPX_INIT'].'/dup-orig-wp-config__'.$GLOBALS['DUPX_AC']->package_hash.'.txt';
        }
        return $path;
    }

    /**
     *
     * @staticvar string $path
     * @return string
     */
    public static function getOrigHtaccessPath()
    {
        static $path = null;
        if (is_null($path)) {
            $path = $GLOBALS['DUPX_INIT'].'/dup-orig-wp-config__'.$GLOBALS['DUPX_AC']->package_hash.'.txt';
        }
        return $GLOBALS['DUPX_INIT'].'/dup-orig-htaccess__'.$GLOBALS['DUPX_AC']->package_hash.'.txt';
    }

    /**
     *
     * @return string
     */
    public static function copyOriginalConfigFiles()
    {
        $wpOrigPath = self::getOrigWpConfigPath();
        $wpArkPath  = self::getWpconfigArkPath();

        if (file_exists($wpOrigPath)) {
            if (!@unlink($wpOrigPath)) {
                DUPX_Log::info('Can\'t delete copy of WP Config orig file');
            }
        }

        if (!file_exists($wpArkPath)) {
            DUPX_Log::info('WP Config ark file don\' exists');
        }

        if (!@copy($wpArkPath, $wpOrigPath)) {
            $errors = error_get_last();
            DUPX_Log::info("COPY ERROR: ".$errors['type']."\n".$errors['message']);
        } else {
            echo DUPX_Log::info("Original WP Config file copied", 2);
        }

        $htOrigPath = self::getOrigHtaccessPath();
        $htArkPath  = self::getHtaccessArkPath();

        if (file_exists($htOrigPath)) {
            if (!@unlink($htOrigPath)) {
                DUPX_Log::info('Can\'t delete copy of htaccess orig file');
            }
        }

        if (!file_exists($htArkPath)) {
            DUPX_Log::info('htaccess ark file don\' exists');
        }

        if (!@copy($htArkPath, $htOrigPath)) {
            $errors = error_get_last();
            DUPX_Log::info("COPY ERROR: ".$errors['type']."\n".$errors['message']);
        } else {
            echo DUPX_Log::info("htaccess file copied", 2);
        }
    }

    public static function finalReportNotices()
    {
        DUPX_Log::info('FINAL REPORT NOTICES');

        self::wpConfigFinalReport();
        self::htaccessFinalReport();
    }

    private static function htaccessFinalReport()
    {
        $nManager = DUPX_NOTICE_MANAGER::getInstance();

        $orig = file_get_contents(self::getOrigHtaccessPath());
        $new  = file_get_contents($GLOBALS['DUPX_ROOT'].'/.htaccess');

        $lightBoxContent = '<div class="row-cols-2">'.
            '<div class="col col-1" style="background-color:#fff7f7"><b style="color:maroon"><i class="fas fa-sticky-note"></i> Original .htaccess</b><pre>'.htmlspecialchars($orig).'</pre></div>'.
            '<div class="col col-2" style="background-color:#f7fdf1"><b style="color:green"><i class="far fa-sticky-note"></i> New .htaccess</b><pre>'.htmlspecialchars($new).'</pre></div>'.
            '</div>';
        $longMsg         = DUPX_U_Html::getLigthBox('.htaccess changes', 'HTACCESS COMPARE', $lightBoxContent, false);

        $nManager->addFinalReportNotice(array(
            'shortMsg' => 'htaccess changes',
            'level' => DUPX_NOTICE_ITEM::INFO,
            'longMsg' => $longMsg,
            'sections' => 'changes',
            'open' => true,
            'longMsgMode'=> DUPX_NOTICE_ITEM::MSG_MODE_HTML
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'htaccess-changes');
    }

    private static function wpConfigFinalReport()
    {
        $nManager = DUPX_NOTICE_MANAGER::getInstance();

        if (($orig = file_get_contents(self::getOrigWpConfigPath())) === false) {
            $orig = 'Can read origin wp-config.php file';
        } else {
            $orig = self::obscureWpConfig($orig);
        }

        if (($new = file_get_contents($GLOBALS['DUPX_ROOT'].'/wp-config.php')) === false) {
            $new = 'Can read wp-config.php file';
        } else {
            $new = self::obscureWpConfig($new);
        }

        $lightBoxContent = '<div class="row-cols-2">'.
            '<div class="col col-1" style="background-color:#fff7f7"><b style="color:maroon"><i class="fas fa-sticky-note"></i> Original wp-config.php</b><pre class="s4-diff-viewer">'.htmlspecialchars($orig).'</pre></div>'.
            '<div class="col col-2" style="background-color:#f7fdf1"><b style="color:green"><i class="far fa-sticky-note"></i> New wp-config.php</b><pre class="s4-diff-viewer">'.htmlspecialchars($new).'</pre></div>'.
            '</div>';
        $longMsg         = DUPX_U_Html::getLigthBox('wp-config.php changes', 'WP-CONFIG.PHP COMPARE', $lightBoxContent, false);

        $nManager->addFinalReportNotice(array(
            'shortMsg' => 'wp-config.php changes',
            'level' => DUPX_NOTICE_ITEM::INFO,
            'longMsg' => $longMsg,
            'sections' => 'changes',
            'open' => true,
            'longMsgMode'=> DUPX_NOTICE_ITEM::MSG_MODE_HTML
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'wp-config-changes');
    }

    private static function obscureWpConfig($src)
    {
        $transformer = new WPConfigTransformerSrc($src);
        $obsKeys     = array(
            'DB_NAME',
            'DB_USER',
            'DB_HOST',
            'DB_PASSWORD',
            'AUTH_KEY',
            'SECURE_AUTH_KEY',
            'LOGGED_IN_KEY',
            'NONCE_KEY',
            'AUTH_SALT',
            'SECURE_AUTH_SALT',
            'LOGGED_IN_SALT',
            'NONCE_SALT');
        
        foreach ($obsKeys as $key) {
            if ($transformer->exists('constant', $key)) {
                $transformer->update('constant', $key, '**OBSCURED**');
            }
        }

        return $transformer->getSrc();
    }
}

DUPX_ServerConfig::init();
