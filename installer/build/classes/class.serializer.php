<?php
// Exit if accessed directly
if (! defined('DUPLICATOR_INIT')) {
	$_baseURL =  strlen($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'];
	$_baseURL =  "http://" . $_baseURL;
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: $_baseURL");
	exit; 
}

/** * *****************************************************
 * CLASS::DUPX_Serializer
 * Walks every table in db that then walks every row and column replacing searches with replaces
 * large tables are split into 50k row blocks to save on memory. */
class DUPX_Serializer {

	/**
	 * LOG ERRORS
	 */
	static public function log_errors($report) {
		if (!empty($report['errsql'])) {
			DUPX_Log::Info("====================================");
			DUPX_Log::Info("DATA-REPLACE ERRORS (MySQL)");
			foreach ($report['errsql'] as $error) {
				DUPX_Log::Info($error);
			}
			DUPX_Log::Info("");
		}
		if (!empty($report['errser'])) {
			DUPX_Log::Info("====================================");
			DUPX_Log::Info("DATA-REPLACE ERRORS (Serialization):");
			foreach ($report['errser'] as $error) {
				DUPX_Log::Info($error);
			}
			DUPX_Log::Info("");
		}
		if (!empty($report['errkey'])) {
			DUPX_Log::Info("====================================");
			DUPX_Log::Info("DATA-REPLACE ERRORS (Key):");
			DUPX_Log::Info('Use SQL: SELECT @row := @row + 1 as row, t.* FROM some_table t, (SELECT @row := 0) r');
			foreach ($report['errkey'] as $error) {
				DUPX_Log::Info($error);
			}
		}
	}


	public static function log_stats($report) 
	{
		if (!empty($report) && is_array($report)) 
		{
			$stats  = "--------------------------------------\n";
			$srchnum = 0;
			foreach ($GLOBALS['REPLACE_LIST'] as $item) 
			{
				$srchnum++;
				$stats .= sprintf("Search{$srchnum}:\t'%s' \nChange{$srchnum}:\t'%s' \n", $item['search'], $item['replace']);
			}
			$stats .= sprintf("SCANNED:\tTables:%d \t|\t Rows:%d \t|\t Cells:%d \n", $report['scan_tables'], $report['scan_rows'], $report['scan_cells']);
			$stats .= sprintf("UPDATED:\tTables:%d \t|\t Rows:%d \t|\t Cells:%d \n", $report['updt_tables'], $report['updt_rows'], $report['updt_cells']);
			$stats .= sprintf("ERRORS:\t\t%d \nRUNTIME:\t%f sec", $report['err_all'], $report['time']);
			DUPX_Log::Info($stats);
		}
	}

	/**
	 * Returns only the text type columns of a table ignoring all numeric types
	 */
	static public function getTextColumns($conn, $table) {
	
		$type_where  = "type NOT LIKE 'tinyint%' AND ";
		$type_where .= "type NOT LIKE 'smallint%' AND ";
		$type_where .= "type NOT LIKE 'mediumint%' AND ";
		$type_where .= "type NOT LIKE 'int%' AND ";
		$type_where .= "type NOT LIKE 'bigint%' AND ";
		$type_where .= "type NOT LIKE 'float%' AND ";
		$type_where .= "type NOT LIKE 'double%' AND ";
		$type_where .= "type NOT LIKE 'decimal%' AND ";
		$type_where .= "type NOT LIKE 'numeric%' AND ";
		$type_where .= "type NOT LIKE 'date%' AND ";
		$type_where .= "type NOT LIKE 'time%' AND ";
		$type_where .= "type NOT LIKE 'year%' ";

		$result = mysqli_query($conn, "SHOW COLUMNS FROM `{$table}` WHERE {$type_where}");
		if (!$result) { 
			return null;
		} 
		$fields = array(); 
		if (mysqli_num_rows($result) > 0) { 
			while ($row = mysqli_fetch_assoc($result)) { 
				$fields[] = $row['Field']; 
			} 
		} 
		
		//Return Primary which is needed for index lookup
		$result = mysqli_query($conn, "SHOW INDEX FROM `{$table}` WHERE KEY_NAME LIKE '%PRIMARY%'");
		if (mysqli_num_rows($result) > 0) { 
			while ($row = mysqli_fetch_assoc($result)) { 
				$fields[] = $row['Column_name']; 
			} 
		} 
	
		return (count($fields) > 0) ? $fields : null;
	}

	/**
	 * LOAD
	 * Begins the processing for replace logic
	 * @param mysql  $conn 		 The db connection object
	 * @param array  $list       Key value pair of 'search' and 'replace' arrays
	 * @param array  $tables     The tables we want to look at.
	 * @return array Collection of information gathered during the run.
	 */
	static public function load($conn, $list = array(), $tables = array(), $cols = array(), $fullsearch = false) {
		$exclude_cols = $cols;

		$report = array('scan_tables' => 0, 'scan_rows' => 0, 'scan_cells' => 0,
			'updt_tables' => 0, 'updt_rows' => 0, 'updt_cells' => 0,
			'errsql' => array(), 'errser' => array(), 'errkey' => array(),
			'errsql_sum' => 0, 'errser_sum' => 0, 'errkey_sum' => 0,
			'time' => '', 'err_all' => 0);
		
		$walk_function = create_function('&$str', '$str = "`$str`";');

		$profile_start = DupUtil::get_microtime();
		if (is_array($tables) && !empty($tables)) {
			
			foreach ($tables as $table) {
				$report['scan_tables']++;
				$columns = array();

				// Get a list of columns in this table
				$fields = mysqli_query($conn, 'DESCRIBE ' . $table);
				while ($column = mysqli_fetch_array($fields)) {
					$columns[$column['Field']] = $column['Key'] == 'PRI' ? true : false;
				}

				// Count the number of rows we have in the table if large we'll split into blocks
				$row_count = mysqli_query($conn, "SELECT COUNT(*) FROM `{$table}`");
				$rows_result = mysqli_fetch_array($row_count);
				@mysqli_free_result($row_count);
				$row_count = $rows_result[0];
				if ($row_count == 0) {
					DUPX_Log::Info("{$table}^ ({$row_count})");
					continue;
				}

				$page_size = 25000;
				$offset = ($page_size + 1);
				$pages = ceil($row_count / $page_size);
				
				// Grab the columns of the table.  Only grab text based columns because 
				// they are the only data types that should allow any type of search/replace logic
				$colList = '*';
				$colMsg  = '*';
				if (! $fullsearch) {
					$colList = self::getTextColumns($conn, $table);
					if ($colList != null && is_array($colList)) {
						array_walk($colList, $walk_function);
						$colList = implode(',', $colList);
					} 
					$colMsg = (empty($colList)) ? '*' : '~';
				}
				
				if (empty($colList)) {
					DUPX_Log::Info("{$table}^ ({$row_count})");
					continue;
				} else {
					DUPX_Log::Info("{$table}{$colMsg} ({$row_count})");
				}

				//Paged Records
				for ($page = 0; $page < $pages; $page++) {

					$current_row = 0;
					$start = $page * $page_size;
					$end   = $start + $page_size;
					$sql = sprintf("SELECT {$colList} FROM `%s` LIMIT %d, %d", $table, $start, $offset);
					$data  = mysqli_query($conn, $sql);

					if (!$data)
						$report['errsql'][] = mysqli_error($conn);
					
					$scan_count = ($row_count < $end) ? $row_count : $end;
					
					//CUSTOM DEBUG ONLY:
					//DUPX_Log::Info("\tScan => {$start} of {$scan_count}", 3);
					//DUPX_Log::Info("\t{$sql}", 3);

					//Loops every row
					while ($row = mysqli_fetch_array($data)) {
						$report['scan_rows']++;
						$current_row++;
						$upd_col = array();
						$upd_sql = array();
						$where_sql = array();
						$upd = false;
						$serial_err = 0;

						//Loops every cell
						foreach ($columns as $column => $primary_key) {
							if (in_array($column, $exclude_cols)) {
								continue;
							}

							$report['scan_cells']++;
							$edited_data = $data_to_fix = $row[$column];
							$base64coverted = false;
							$txt_found = false;

							//Only replacing string values
							if (!empty($row[$column]) && !is_numeric($row[$column])) {

								//Base 64 detection
								if (base64_decode($row[$column], true)) {
									$decoded = base64_decode($row[$column], true);
									if (self::is_serialized($decoded)) {
										$edited_data = $decoded;
										$base64coverted = true;
									}
								}
								
								//Skip table cell if match not found
								foreach ($list as $item) 
								{
									if (strpos($edited_data, $item['search']) !== false) {
										$txt_found = true;
										break;
									}
								}
								if (! $txt_found) {
									continue;
								}

								//Replace logic - level 1: simple check on basic serilized strings
								foreach ($list as $item) {
									$edited_data = self::recursive_unserialize_replace($item['search'], $item['replace'], $edited_data);
								}

								//Replace logic - level 2: repair larger/complex serilized strings
								$serial_check = self::fix_serial_string($edited_data);
								if ($serial_check['fixed']) {
									$edited_data = $serial_check['data'];
								} elseif ($serial_check['tried'] && !$serial_check['fixed']) {
									$serial_err++;
								}
							}

							//Change was made
							if ($edited_data != $data_to_fix || $serial_err > 0) {
								$report['updt_cells']++;
								//Base 64 encode
								if ($base64coverted) {
									$edited_data = base64_encode($edited_data);
								}
								$upd_col[] = $column;
								$upd_sql[] = $column . ' = "' . mysqli_real_escape_string($conn, $edited_data) . '"';
								$upd = true;
							}

							if ($primary_key) {
								$where_sql[] = $column . ' = "' . mysqli_real_escape_string($conn, $data_to_fix) . '"';
							}
						}

						//PERFORM ROW UPDATE
						if ($upd && !empty($where_sql)) {
							$sql = "UPDATE `{$table}` SET " . implode(', ', $upd_sql) . ' WHERE ' . implode(' AND ', array_filter($where_sql));
							$result = mysqli_query($conn, $sql) or $report['errsql'][] = mysqli_error($conn);
							//DEBUG ONLY:
							DUPX_Log::Info("\t{$sql}\n\n", 3);
							if ($result) {
								if ($serial_err > 0) {
									$report['errser'][] = "SELECT " . implode(', ', $upd_col) . " FROM `{$table}`  WHERE " . implode(' AND ', array_filter($where_sql)) . ';';
								}
								$report['updt_rows']++;
							}
						} elseif ($upd) {
							$report['errkey'][] = sprintf("Row [%s] on Table [%s] requires a manual update.", $current_row, $table);
						}
					}
					DupUtil::fcgi_flush();
					@mysqli_free_result($data);
				}

				if ($upd) {
					$report['updt_tables']++;
				}
			}
		}
		$profile_end = DupUtil::get_microtime();
		$report['time'] = DupUtil::elapsed_time($profile_end, $profile_start);
		$report['errsql_sum'] = empty($report['errsql']) ? 0 : count($report['errsql']);
		$report['errser_sum'] = empty($report['errser']) ? 0 : count($report['errser']);
		$report['errkey_sum'] = empty($report['errkey']) ? 0 : count($report['errkey']);
		$report['err_all'] = $report['errsql_sum'] + $report['errser_sum'] + $report['errkey_sum'];
		return $report;
	}

	/**
	 * Take a serialised array and unserialise it replacing elements and
	 * unserialising any subordinate arrays and performing the replace.
	 * @param string $from       String we're looking to replace.
	 * @param string $to         What we want it to be replaced with
	 * @param array  $data       Used to pass any subordinate arrays back to in.
	 * @param bool   $serialised Does the array passed via $data need serialising.
	 * @return array	The original array with all elements replaced as needed. 
	 */
	static private function recursive_unserialize_replace($from = '', $to = '', $data = '', $serialised = false) {

		// some unseriliased data cannot be re-serialised eg. SimpleXMLElements
		try {

			if (is_string($data) && ($unserialized = @unserialize($data)) !== false) {
				$data = self::recursive_unserialize_replace($from, $to, $unserialized, true);
			} elseif (is_array($data)) {
				$_tmp = array();
				foreach ($data as $key => $value) {
					$_tmp[$key] = self::recursive_unserialize_replace($from, $to, $value, false);
				}
				$data = $_tmp;
				unset($_tmp);
			} elseif (is_object($data)) {
				$dataClass = get_class($data);
				$_tmp = new $dataClass();
				foreach ($data as $key => $value) {
					$_tmp->$key = self::recursive_unserialize_replace($from, $to, $value, false);
				}
				$data = $_tmp;
				unset($_tmp);
			} else {
				if (is_string($data)) {
					$data = str_replace($from, $to, $data);
				}
			}

			if ($serialised)
				return serialize($data);
		} catch (Exception $error) {
			DUPX_Log::Info("\nRECURSIVE UNSERIALIZE ERROR: With string\n" . $error, 2);
		}
		return $data;
	}

	/**
	 *  IS_SERIALIZED
	 *  Test if a string in properly serialized */
	static public function is_serialized($data) {
		$test = @unserialize(($data));
		return ($test !== false || $test === 'b:0;') ? true : false;
	}

	/**
	 *  FIX_STRING
	 *  Fixes the string length of a string object that has been serialized but the length is broken
	 *  @param string $data	The string ojbect to recalculate the size on.
	 *  @return 
	 */
	static private function fix_serial_string($data) {

		$result = array('data' => $data, 'fixed' => false, 'tried' => false);

		if (preg_match("/s:[0-9]+:/", $data)) {
			if (!self::is_serialized($data)) {
				$regex = '!(?<=^|;)s:(\d+)(?=:"(.*?)";(?:}|a:|s:|b:|d:|i:|o:|N;))!s';
				$serial_string = preg_match('/^s:[0-9]+:"(.*$)/s', trim($data), $matches);
				//Nested serial string
				if ($serial_string) {
					$inner = preg_replace_callback($regex, 'DUPX_Serializer::fix_string_callback', rtrim($matches[1], '";'));
					$serialized_fixed = 's:' . strlen($inner) . ':"' . $inner . '";';
				} else {
					$serialized_fixed = preg_replace_callback($regex, 'DUPX_Serializer::fix_string_callback', $data);
				}

				if (self::is_serialized($serialized_fixed)) {
					$result['data'] = $serialized_fixed;
					$result['fixed'] = true;
				}
				$result['tried'] = true;
			}
		}
		return $result;
	}

	static private function fix_string_callback($matches) {
		return 's:' . strlen(($matches[2]));
	}

}
?>