<?php
class DuplicatorUtils {

    /** METHOD: GET_MICROTIME
     * Get current microtime as a float. Can be used for simple profiling.
     */
    static public function GetMicrotime() {
        return microtime(true);
    }

    /** METHOD: ELAPSED_TIME
     * Return a string with the elapsed time.
     * Order of $end and $start can be switched. 
     */
    static public function ElapsedTime($end, $start) {
        return sprintf("%.4f sec.", abs($end - $start));
    }
	
	 /**
     * MySQL server variable
     * @param conn $dbh Database connection handle
     * @return string the server variable to query for
     */
    static public function MysqlVariableValue($variable) {
		global $wpdb;
        $row = $wpdb->get_row("SHOW VARIABLES LIKE '{$variable}'", ARRAY_N);
        return isset($row[1]) ? $row[1] : null;
    }
	
	 /**
     * ListDirs
     * @path path to a system directory
     * @return array of all directories in that path
     */
	static public function ListDirs($path = '.') {
		$dirs = array();

		foreach (new DirectoryIterator($path) as $file) {
			if ($file->isDir() && !$file->isDot()) {
				$dirs[] = duplicator_safe_path($file->getPathname());
			}
		}

		return $dirs;
	}
    
}
?>
