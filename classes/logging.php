<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


class DUP_Log {
	
	private static $logFileHandle;
	
	/**
	 *  Open
	 *  Open a log file connection for writing
	 *  @param string $name		Name of the log file to create
	 */
	static public function Open($name) {
		if (! isset($name)) throw new Exception("A name value is required!");
		self::$logFileHandle = @fopen(DUPLICATOR_SSDIR_PATH . "/{$name}.log", "c+");	
	}
	
	/**
	 *  Close
	 *  Closes the log file connection
	 */
	static public function Close() {
		 @fclose(self::$logFileHandle);
	}
	
	/**
	 *  Info
	 *  Genral informationlogging method
	 *  @param string $msg		The message to log
	 */
	static public function Info($msg) {
		@fwrite(self::$logFileHandle, "{$msg} \n"); 
	}
	
	/**
	*  Error
	*  Centralized logging for errors
	*  @param string $msg		The message to log
	*  @param string $details	More info to help out
	*/
	static public function Error($msg, $detail) {
		
		$source = self::getStack(debug_backtrace());
		
		$err_msg  = "\n==================================================================================\n";
		$err_msg .= "!!!DUPLICATOR ERROR!!!\n";
		$err_msg .= "Please Try Again! If the error persists please see the Duplicator 'Support' link.\n";
		$err_msg .= "---------------------------------------------------------------------------------\n";
		$err_msg .= "MESSAGE:\n{$msg}\n";
		if (strlen($detail)) {
			$err_msg .= "DETAILS:\n{$detail}\n";
		}
		$err_msg .= "---------------------------------------------------------------------------------\n";
		$err_msg .= "TRACE:\n{$source}";
		$err_msg .= "==================================================================================\n\n";
		@fwrite(self::$logFileHandle, "\n{$err_msg}"); 
		die("DUPLICATOR ERROR: Please see the duplicator log file.");
	}
	
	
	/** 
	 * Getting backtrace 
	 * 
	 * @param $stacktrace The current debug stack
	 * 
	 * @return string 
	 */ 
    public static function getStack($stacktrace) {
        $output = "";
        $i = 1;
        foreach($stacktrace as $node) {
            $output .= "$i. ".basename($node['file']) ." : " .$node['function'] ." (" .$node['line'].")\n";
            $i++;
        }
		return $output;
    } 

}
?>
