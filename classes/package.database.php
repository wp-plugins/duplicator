<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

class DUP_Database {
	
	//PUBLIC
	public $Type = 'MySQL';
	public $Size;
	public $File;
	public $Path;
	public $FilterTables;
	public $FilterOn;
	public $Name;
	
	//PROTECTED
	protected $Package;
	
	//PRIVATE
	private $dbStorePath;

	//CONSTRUCTOR
	function __construct($package) {
		 $this->Package = $package;
	}
	
	public function Build() {
		try {
		
			DUP_Log::Info("********************************************************************************");
			DUP_Log::Info("BUILD SQL SCRIPT:");
			DUP_Log::Info("********************************************************************************");
			
			$time_start = DUP_Util::GetMicrotime();
			$package_mysqldump	= DUP_Settings::Get('package_mysqldump');
			$this->Package->SetStatus(DUP_PackageStatus::DBSTART);
			$this->dbStorePath = "{$this->Package->StorePath}/{$this->File}";

			$mysqlDumpPath = $this->getMySqlDumpPath();
			if ($mysqlDumpPath && $package_mysqldump) {
				DUP_Log::Info("BUILD MODE: MySQLDump");
				$this->mysqlDump($mysqlDumpPath);
			} else {
				DUP_Log::Info("BUILD MODE: PHP");
				$this->phpDump();
			}

			DUP_Log::Info("SQL CREATED: {$this->File}");
			$time_end = DUP_Util::GetMicrotime();
			$time_sum = DUP_Util::ElapsedTime($time_end, $time_start);

			$sql_file_size = filesize($this->dbStorePath);
			if ($sql_file_size <= 0) {
				DUP_Log::Error("SQL file generated zero bytes.", "No data was written to the sql file.  Check permission on file and parent directory at [{$this->dbStorePath}]");
			}
			DUP_Log::Info("SQL FILE SIZE: " . DUP_Util::ByteSize($sql_file_size));
			DUP_Log::Info("SQL RUNTIME: {$time_sum}");

			$this->Size = @filesize($this->dbStorePath);
			$this->Package->SetStatus(DUP_PackageStatus::DBDONE);
			
		} catch (Exception $e) {
			DUP_Log::Error("Runtime error in DUP_Database::Build","Exception: {$e}");
		}
	}
	
	/**
	 *  DATABASESTATS
	 *  Get the database stats
	 */
	public function Stats() {
		
		global $wpdb;
		$filterTables  = isset($this->FilterTables) ? explode(',', $this->FilterTables) : null;
		$tblCount = 0;
	
		$sql = "SHOW TABLE STATUS";
		$tables	 = $wpdb->get_results($sql, ARRAY_A);
		$info = array();
		$info['Status']['Success'] = is_null($tables) ? false : true;
		$info['Status']['Size']    = 'Good';
		$info['Status']['Rows']    = 'Good';
		
		$info['Size']   = 0;
		$info['Rows']   = 0;
		$info['TableCount'] = 0;
		$info['TableList']	= array();
		
		//Only return what we really need
		foreach ($tables as $table) {
			
			$name = $table["Name"];
			if ($this->FilterOn  && is_array($filterTables)) {
				if (in_array($name, $filterTables)) {
					continue;
				}
			}
			$size = ($table["Data_length"] +  $table["Index_length"]);
			
			$info['Size'] += $size;
			$info['Rows'] += ($table["Rows"]);
			$info['TableList'][$name]['Rows']	= empty($table["Rows"]) ? '0' : number_format($table["Rows"]);
			$info['TableList'][$name]['Size']	= DUP_Util::ByteSize($size);
			$tblCount++;
		}
		
		$info['Status']['Size']   = ($info['Size'] > 100000000) ? 'Warn' : 'Good';
		$info['Status']['Rows']   = ($info['Rows'] > 1000000)   ? 'Warn' : 'Good';
		$info['TableCount']		  = $tblCount;
		
		return $info;
	}	
	

	
	private function mysqlDump($exePath) {
		
		$host = explode(':', DB_HOST);
		$host = reset($host);
		$port = strpos(DB_HOST, ':') ? end(explode( ':', DB_HOST ) ) : '';
		//Build command
		$cmd = escapeshellarg($exePath);
		$cmd .= ' --no-create-db';
		$cmd .= ' --single-transaction';
		$cmd .= ' --hex-blob';

		//--ignore-table=database.table1

		$cmd .= ' -u ' . escapeshellarg(DB_USER);
		$cmd .= (DB_PASSWORD) ? 
				' -p'  . escapeshellarg(DB_PASSWORD) : '';
		$cmd .= ' -h ' . escapeshellarg($host);
		$cmd .= ( ! empty($port) && is_numeric($port) ) ?
				' -P ' . $port : '';
		$cmd .= ' -r ' . escapeshellarg($this->dbStorePath);
		$cmd .= ' ' . escapeshellarg(DB_NAME);
		$cmd .= ' 2>&1';		

		$output = shell_exec($cmd);

		// Password bug > 5.6 (@see http://bugs.mysql.com/bug.php?id=66546)
		if ( trim( $output ) === 'Warning: Using a password on the command line interface can be insecure.' ) {
			$output = '';
		}
		$output = (strlen($strerr)) ? $output : 'Empty';
		DUP_Log::Info("RESPONSE: {$output}");
		return ($output) ?  false : true;
	}


	private function phpDump() {

		global $wpdb;

		$wpdb->query("SET session wait_timeout = " . DUPLICATOR_DB_MAX_TIME);
		$handle		= fopen($this->dbStorePath, 'w+');
		$tables		= $wpdb->get_col('SHOW TABLES');

		$filterTables  = isset($this->FilterTables) ? explode(',', $this->FilterTables) : null;
		$tblAllCount	= count($tables);
		$tblFilterOn	= ($this->FilterOn) ? 'ON' : 'OFF';

		if (is_array($filterTables) && $this->FilterOn) {
			foreach ($tables as $key => $val) {
				if (in_array($tables[$key], $filterTables)) {
					unset($tables[$key]);
				}
			}
		}
		$tblCreateCount = count($tables);
		$tblFilterCount = $tblAllCount - $tblCreateCount;

		DUP_Log::Info("----------------------------------------");
		DUP_Log::Info("Table Settings");
		DUP_Log::Info("filters: *{$tblFilterOn}*");
		DUP_Log::Info("total:{$tblAllCount} | filtered:{$tblFilterCount} | created:{$tblCreateCount}");
		DUP_Log::Info("filtered: [{$this->FilterTables}]");
		DUP_Log::Info("----------------------------------------");

		$sql_header = "/* DUPLICATOR MYSQL SCRIPT CREATED ON : " . @date("F j, Y, g:i a") . " */\n\n";
		$sql_header .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
		@fwrite($handle, $sql_header);

		//BUILD CREATES:
		//All creates must be created before inserts do to foreign key constraints
		foreach ($tables as $table) {
			//$sql_del = ($GLOBALS['duplicator_opts']['dbadd_drop']) ? "DROP TABLE IF EXISTS {$table};\n\n" : "";
			//@fwrite($handle, $sql_del);
			$create = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);
			@fwrite($handle, "{$create[1]};\n\n");
		}

		//BUILD INSERTS: 
		//Create Insert in 100 row increments to better handle memory
		foreach ($tables as $table) {

			$row_count = $wpdb->get_var("SELECT Count(*) FROM `{$table}`");
			DUP_Log::Info("{$table} ({$row_count})");

			if ($row_count > 100) {
				$row_count = ceil($row_count / 100);
			} else if ($row_count > 0) {
				$row_count = 1;
			}

			if ($row_count >= 1) {
				@fwrite($handle, "\n/* INSERT TABLE DATA: {$table} */\n");
			}

			for ($i = 0; $i < $row_count; $i++) {
				$sql = "";
				$limit = $i * 100;
				$query = "SELECT * FROM `{$table}` LIMIT {$limit}, 100";
				$rows = $wpdb->get_results($query, ARRAY_A);
				if (is_array($rows)) {
					foreach ($rows as $row) {
						$sql .= "INSERT INTO `{$table}` VALUES(";
						$num_values = count($row);
						$num_counter = 1;
						foreach ($row as $value) {
							if ( is_null( $value ) || ! isset( $value ) ) {
								($num_values == $num_counter) 	? $sql .= 'NULL' 	: $sql .= 'NULL, ';
							} else {
								($num_values == $num_counter) 
									? $sql .= '"' . @mysql_real_escape_string($value) . '"' 
									: $sql .= '"' . @mysql_real_escape_string($value) . '", ';
							}
							$num_counter++;
						}
						$sql .= ");\n";
					}
					@fwrite($handle, $sql);
					DUP_Util::FcgiFlush();
				}
			}
		}
		unset($sql);
		$sql_footer = "\nSET FOREIGN_KEY_CHECKS = 1;";
		@fwrite($handle, $sql_footer);
		$wpdb->flush();
		fclose($handle);
	}
	
	
	private function getMySqlDumpPath() {
		
		if (! DUP_Util::IsShellExecAvailable()) {
			DUP_Log::Info("SHELL EXEC: Not Enabled");
			return false;
		}
			
		//Common Windows Paths
		if (DUP_Util::IsOSWindows()) {
			$paths = array(
				'C:/xampp/mysql/bin/mysqldump.exe',
				'C:/Program Files/xampp/mysql/bin/mysqldump',
				'C:/Program Files/MySQL/MySQL Server 6.0/bin/mysqldump',
				'C:/Program Files/MySQL/MySQL Server 5.5/bin/mysqldump',
				'C:/Program Files/MySQL/MySQL Server 5.4/bin/mysqldump',
				'C:/Program Files/MySQL/MySQL Server 5.1/bin/mysqldump',
				'C:/Program Files/MySQL/MySQL Server 5.0/bin/mysqldump',
			);	
			
		//Common Linux Paths			
		} else {
			$mysqldump = `which mysqldump`;
			if (@is_executable($mysqldump)) 
				return $mysqldump;
			
			$mysqldump = dirname(`which mysql`) . "/mysqldump";
			if (@is_executable($mysqldump)) 
				return $mysqldump;
			
			$paths = array(
				'/usr/local/bin/mysqldump',
				'/usr/local/mysql/bin/mysqldump',
				'/usr/mysql/bin/mysqldump',
				'/usr/bin/mysqldump',
				'/opt/local/lib/mysql6/bin/mysqldump',
				'/opt/local/lib/mysql5/bin/mysqldump',
				'/opt/local/lib/mysql4/bin/mysqldump',
			);
		}

		// Find the one which works
		foreach ( $paths as $path ) {
		    if ( @is_executable($path))
	 	    	return $path;
		}
		
		return false;
	}

}
?>
