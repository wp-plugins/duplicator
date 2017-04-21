<?php

/**
 * Class used to update and edit web server configuration files
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\ServerConfig
 *
 */
class DUPX_ServerConfig
{

	/**
	 *  Clear .htaccess and web.config files and backup
	 *
	 *  @return null
	 */
	public static function reset()
	{


		DUPX_Log::info("\nWEB SERVER CONFIGURATION FILE RESET:");
		$timeStamp = date("ymdHis");

		//Apache
		@copy('.htaccess', ".htaccess.{$timeStamp}.orig");
		@unlink('.htaccess');
		@file_put_contents('.htaccess', "#Reset by Duplicator Installer.  Original can be found in .htaccess.{$timeStamp}.orig");

		//IIS
		@copy('web.config', "web.config.{$timeStamp}.orig");
		@unlink('web.config');
		@file_put_contents('web.config', "<!-- Reset by Duplicator Installer.  Original can be found in web.config.{$timeStamp}.orig -->");

		//.user.ini - For WordFence
		@copy('.user.ini', ".user.ini.{$timeStamp}.orig");
		@unlink('.user.ini');

		DUPX_Log::info("- Backup of .htaccess/web.config made to *.{$timeStamp}.orig");
		DUPX_Log::info("- Reset of .htaccess/web.config files");
		
		
		@chmod('.htaccess', 0644);
	}

	/**
	 *  Resets the .htaccess file to a very slimed down version with new paths
	 *
	 *  @return null
	 */
	public static function setup()
	{

		if (!isset($_POST['url_new'])) {
			return;
		}

		DUPX_Log::info("\nWEB SERVER CONFIGURATION FILE BASIC SETUP:");
		$currdata	 = parse_url($_POST['url_old']);
		$newdata	 = parse_url($_POST['url_new']);
		$currpath	 = DUPX_U::addSlash(isset($currdata['path']) ? $currdata['path'] : "");
		$newpath	 = DUPX_U::addSlash(isset($newdata['path']) ? $newdata['path'] : "");

		$tmp_htaccess = <<<HTACCESS
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

		file_put_contents('.htaccess', $tmp_htaccess);
		@chmod('.htaccess', 0644);
		DUPX_Log::info("created basic .htaccess file.  If using IIS web.config this process will need to be done manually.");
	}
}
?>