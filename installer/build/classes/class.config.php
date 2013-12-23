<?php
/** * *****************************************************
 * DUPX_Config 
 * Class used to update and edit web server configuration files  */

class DUPX_Config {

    /** METHOD: ResetHTACCESS
     *  Resetst the .htaccess file
     */
    static public function ResetHTACCESS() {
		
		if (self::isPathNew()) {
			DUPX_Log::Info("HTACCESS CHANGES:");
			@copy('.htaccess', '.htaccess.orig');
			@unlink('.htaccess');
			DUPX_Log::Info("created backup of original .htaccess to htaccess.orig");

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
			DUPX_Log::Info("created basic .htaccess file.  If using IIS web.config this process will need to be done manually.");
			DUPX_Log::Info("updated .htaccess file.");
		} else {
			DUPX_Log::Info(".htaccess file was not reset because the old url and new url paths did not change.");
		}
    }
	
	/** METHOD: ResetWebConfig
     *  Resetst the IIS web.config file
     */
	static public function ResetWebConfig() {

		if (self::isPathNew()) {
			
			DUPX_Log::Info("WEB.CONFIG CHANGES:");
			@copy('web.config', 'web.config.orig');
			@unlink('web.config');
			DUPX_Log::Info("created backup of original web.config to web.config.orig");
			DUPX_Log::Info("If using IIS web.config this process will need to be done manually.");
		} else {
			DUPX_Log::Info("web.config file was not reset because the old url and new url paths did not change (IIS Only).");
		}
	}
	
	static private function isPathNew() {
		
		$currdata = parse_url($_POST['url_old']);
		$newdata  = parse_url($_POST['url_new']);
		$currpath = DupUtil::add_slash(isset($currdata['path']) ? $currdata['path'] : "");
		$newpath  = DupUtil::add_slash(isset($newdata['path'])  ? $newdata['path'] : "");
		
		return ($currpath != $newpath);
	}
	
}
?>
