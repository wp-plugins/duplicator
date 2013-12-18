<?php
/** * *****************************************************
 * DUPEXE_Config 
 * Class used to update and edit web server configuration files  */

class DUPEXE_Config {

    /** METHOD: ResetHTACCESS
     *  Resetst the .htaccess file
     */
    static public function ResetHTACCESS() {
		
		if (self::isPathNew()) {
			DupUtil::log("HTACCESS CHANGES:");
			@copy('.htaccess', '.htaccess.orig');
			@unlink('.htaccess');
			DupUtil::log("created backup of original .htaccess to htaccess.orig");

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
			DupUtil::log("created basic .htaccess file.  If using IIS web.config this process will need to be done manually.");
			DupUtil::log("updated .htaccess file.");
		} else {
			DupUtil::log(".htaccess file was not reset because the old url and new url paths did not change.");
		}
    }
	
	/** METHOD: ResetWebConfig
     *  Resetst the IIS web.config file
     */
	static public function ResetWebConfig() {

		if (self::isPathNew()) {
			
			DupUtil::log("WEB.CONFIG CHANGES:");
			@copy('web.config', 'web.config.orig');
			@unlink('web.config');
			DupUtil::log("created backup of original web.config to web.config.orig");
			DupUtil::log("If using IIS web.config this process will need to be done manually.");
		} else {
			DupUtil::log("web.config file was not reset because the old url and new url paths did not change (IIS Only).");
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
