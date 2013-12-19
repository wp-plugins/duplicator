<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

class DUP_Database{
	
	//PUBLIC
	public $Type = 'MySQL';
	public $Size;
	public $File;
	public $Path;
	public $FilterTables;
	public $FilterOn;
	public $Name;
	
	function __construct() {
		
	}

	public function Build($destination) {
		try {

			global $wpdb;
			$time_start = DUP_Util::GetMicrotime();
			$handle		= fopen($destination, 'w+');
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
			
			DUP_Log::Info("********************************************************************************");
			DUP_Log::Info("BUILD SQL SCRIPT:");
			DUP_Log::Info("********************************************************************************");
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

			DUP_Log::Info("SQL CREATED: {$destination}");
			fclose($handle);
			$wpdb->flush();

			$time_end = DUP_Util::GetMicrotime();
			$time_sum = DUP_Util::ElapsedTime($time_end, $time_start);

			$sql_file_size = filesize($destination);
			if ($sql_file_size <= 0) {
				DUP_Log::Error("SQL file generated zero bytes.", "No data was written to the sql file.  Check permission on file and parent directory at [$destination]");
			}
			DUP_Log::Info("SQL FILE SIZE: " . DUP_Util::ByteSize($sql_file_size));
			DUP_Log::Info("SQL RUNTIME: {$time_sum}");

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
	
		/* Full row query results from below
			Name: "test_dates",
			Engine: "MyISAM",
			Version: "10",
			Row_format: "Fixed",
			Rows: "1",
			Avg_row_length: "21",
			Data_length: "21",
			Max_data_length: "5910974510923775",
			Index_length: "2048",
			Data_free: "0",
			Auto_increment: "2",
			Create_time: "2013-10-02 08:47:14",
			Update_time: "2013-10-02 09:02:47",
			Check_time: null,
			Collation: "utf8_unicode_ci",
			Checksum: null,
			Create_options: "",
			Comment: "" 
		 */
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

}
?>
