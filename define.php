<?php

	define('DUPLICATOR_VERSION',   		'0.3.0');
	define("DUPLICATOR_GIVELINK",		"http://lifeinthegrid.com/partner/");
	define("DUPLICATOR_HELPLINK",  		"http://lifeinthegrid.com/duplicator-docs/");
	define("DUPLICATOR_CERTIFIED",		"http://lifeinthegrid.com/duplicator-certified/");
	
	define("DUPLICATOR_DB_ICONV_IN",	"UTF-8"); 
	define("DUPLICATOR_DB_ICONV_OUT",	"ISO-8859-1//TRANSLIT"); 
	define('DUPLICATOR_PLUGIN_URL',  plugin_dir_url( __FILE__ ));
	define('DUPLICATOR_PLUGIN_PATH', str_replace("\\", "/", plugin_dir_path( __FILE__ )));
	
	/* Paths should ALWAYS read "/"
		uni: /home/path/file.txt
		win:  D:/home/path/file.txt 
		SSDIR = SnapShot Directory */
	if ( !defined('ABSPATH') ) {
		define('ABSPATH', dirname('__FILE__'));
	}
	define("DUPLICATOR_SSDIR_NAME",		'wp-snapshots'); 
	define('DUPLICATOR_WPROOTPATH',		str_replace("\\", "/", ABSPATH));
	define("DUPLICATOR_SSDIR_PATH",		str_replace("\\", "/", DUPLICATOR_WPROOTPATH . DUPLICATOR_SSDIR_NAME));
	define("DUPLICATOR_INSTALL_PHP",	'installer.php');
	define("DUPLICATOR_INSTALL_SQL",	'installer-data.sql');
	define("DUPLICATOR_INSTALL_LOG",	'installer-log.txt');
	define("DUPLICATOR_ZIP_FILE_POOL",	5000);
	
?>