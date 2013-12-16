<?php
/* JSON RESPONSE: Most sites have warnings turned off by default, but if they're turned on the warnings
cause errors in the JSON data Here we hide the status so warning level is reset at it at the end*/
$ajax2_error_level = error_reporting();
error_reporting(E_ERROR);

/** * *****************************************************
 * CLASS::DUPDBTEXTSWAP
 * Walks every table in db that then walks every row and column replacing searches with replaces
 * large tables are split into 50k row blocks to save on memory. */
class DupDBTextSwap {

	/**
	 * LOG ERRORS
	 */
	static public function log_errors($report) {
		if (!empty($report['errsql'])) {
			DupUtil::log("====================================");
			DupUtil::log("DATA-REPLACE ERRORS (MySQL)");
			foreach ($report['errsql'] as $error) {
				DupUtil::log($error);
			}
			DupUtil::log("");
		}
		if (!empty($report['errser'])) {
			DupUtil::log("====================================");
			DupUtil::log("DATA-REPLACE ERRORS (Serialization):");
			foreach ($report['errser'] as $error) {
				DupUtil::log($error);
			}
			DupUtil::log("");
		}
		if (!empty($report['errkey'])) {
			DupUtil::log("====================================");
			DupUtil::log("DATA-REPLACE ERRORS (Key):");
			DupUtil::log('Use SQL: SELECT @row := @row + 1 as row, t.* FROM some_table t, (SELECT @row := 0) r');
			foreach ($report['errkey'] as $error) {
				DupUtil::log($error);
			}
			DupUtil::log("");
		}
	}

	/**
	 * LOG STATS
	 */
	static public function log_stats($report) {
		if (!empty($report) && is_array($report)) {
			$stats = sprintf("SEARCH1:\t'%s' \nREPLACE1:\t'%s' \n", $_POST['url_old'], $_POST['url_new']);
			$stats .= sprintf("SEARCH2:\t'%s' \nREPLACE2:\t'%s' \n", $_POST['path_old'], $_POST['path_new']);
			$stats .= sprintf("SCANNED:\tTables:%d | Rows:%d | Cells:%d \n", $report['scan_tables'], $report['scan_rows'], $report['scan_cells']);
			$stats .= sprintf("UPDATED:\tTables:%d | Rows:%d |Cells:%d \n", $report['updt_tables'], $report['updt_rows'], $report['updt_cells']);
			$stats .= sprintf("ERRORS:\t\t%d \nRUNTIME:\t%f sec", $report['err_all'], $report['time']);
			DupUtil::log($stats);
		}
	}

	/**
	 * LOAD
	 * Begins the processing for replace logic
	 * @param mysql  $conn 		 The db connection object
	 * @param array  $list       Key value pair of 'search' and 'replace' arrays
	 * @param array  $tables     The tables we want to look at.
	 * @return array Collection of information gathered during the run.
	 */
	static public function load($conn, $list = array(), $tables = array(), $cols = array()) {
		$exclude_cols = $cols;

		$report = array('scan_tables' => 0, 'scan_rows' => 0, 'scan_cells' => 0,
			'updt_tables' => 0, 'updt_rows' => 0, 'updt_cells' => 0,
			'errsql' => array(), 'errser' => array(), 'errkey' => array(),
			'errsql_sum' => 0, 'errser_sum' => 0, 'errkey_sum' => 0,
			'time' => '', 'err_all' => 0);

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

				// Count the number of rows we have in the table if large we'll split into blocks, This is a mod from Simon Wheatley
				$row_count = mysqli_query($conn, 'SELECT COUNT(*) FROM ' . $table);
				$rows_result = mysqli_fetch_array($row_count);
				@mysqli_free_result($row_count);
				$row_count = $rows_result[0];
				if ($row_count == 0)
					continue;

				$page_size = 25000;
				$pages = ceil($row_count / $page_size);

				for ($page = 0; $page < $pages; $page++) {

					$current_row = 0;
					$start = $page * $page_size;
					$end = $start + $page_size;
					// Grab the content of the table
					$data = mysqli_query($conn, sprintf('SELECT * FROM %s LIMIT %d, %d', $table, $start, $end));

					if (!$data)
						$report['errsql'][] = mysqli_error($conn);

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

							//Only replacing string values
							if (!is_numeric($row[$column])) {

								//Base 64 detection
								if (base64_decode($row[$column], true)) {
									$decoded = base64_decode($row[$column], true);
									if (self::is_serialized($decoded)) {
										$edited_data = $decoded;
										$base64coverted = true;
									}
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
							if ($result) {
								if ($serial_err > 0) {
									$report['errser'][] = "SELECT " . implode(', ', $upd_col) . " FROM `{$table}`  WHERE " . implode(' AND ', array_filter($where_sql)) . ';';
								}
								$report['updt_rows']++;
							}
						} elseif ($upd) {
							$report['errkey'][] = sprintf("Row [%s] on Table [%s] requires a manual update.", $current_row, $table);
						}
						DupUtil::fcgi_flush();
					}
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
			DupUtil::log("\nRECURSIVE UNSERIALIZE ERROR: With string\n" . $error, 2);
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
					$inner = preg_replace_callback($regex, 'DupDBTextSwap::fix_string_callback', rtrim($matches[1], '";'));
					$serialized_fixed = 's:' . strlen($inner) . ':"' . $inner . '";';
				} else {
					$serialized_fixed = preg_replace_callback($regex, 'DupDBTextSwap::fix_string_callback', $data);
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

//====================================================================================================
//DATABASE UPDATES
//====================================================================================================

$ajax2_start = DupUtil::get_microtime();

//MYSQL CONNECTION
$db_port = parse_url($_POST['dbhost'], PHP_URL_PORT);
$dbh = @mysqli_connect($_POST['dbhost'], $_POST['dbuser'], $_POST['dbpass'], $_POST['dbname'], $db_port);
$charset_server = @mysqli_character_set_name($dbh);
@mysqli_query($dbh, "SET wait_timeout = {$GLOBALS['DB_MAX_TIME']}");
DupUtil::mysql_set_charset($dbh, $_POST['dbcharset'], $_POST['dbcollate']);

//POST PARAMS
$_POST['blogname'] = mysqli_real_escape_string($dbh, $_POST['blogname']);
$_POST['postguid'] = isset($_POST['postguid']) && $_POST['postguid'] == 1 ? 1 : 0;
$_POST['path_old'] = isset($_POST['path_old']) ? trim($_POST['path_old']) : null;
$_POST['path_new'] = isset($_POST['path_new']) ? trim($_POST['path_new']) : null;
$_POST['siteurl'] = isset($_POST['siteurl']) ? rtrim(trim($_POST['siteurl']), '/') : null;
$_POST['tables'] = isset($_POST['tables']) && is_array($_POST['tables']) ? array_map('stripcslashes', $_POST['tables']) : array();
$_POST['url_old'] = isset($_POST['url_old']) ? trim($_POST['url_old']) : null;
$_POST['url_new'] = isset($_POST['url_new']) ? rtrim(trim($_POST['url_new']), '/') : null;

//LOGGING
$POST_LOG = $_POST;
unset($POST_LOG['tables']);
unset($POST_LOG['plugins']);
unset($POST_LOG['dbpass']);
ksort($POST_LOG);

//GLOBAL DB-REPLACE
DupUtil::log("\n\n\n{$GLOBALS['SEPERATOR1']}");
DupUtil::log('DUPLICATOR INSTALL-LOG');
DupUtil::log('STEP2 START @ ' . @date('h:i:s'));
DupUtil::log('NOTICE: NOTICE: Do not post to public sites or forums');
DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log("CHARSET SERVER:\t{$charset_server}");
DupUtil::log("CHARSET CLIENT:\t" . @mysqli_character_set_name($dbh));
DupUtil::log("--------------------------------------");
DupUtil::log("POST DATA");
DupUtil::log("--------------------------------------");
DupUtil::log(print_r($POST_LOG, true));

DupUtil::log("--------------------------------------");
DupUtil::log("SCANNED TABLES");
DupUtil::log("--------------------------------------");
$msg = (isset($_POST['tables']) && count($_POST['tables'] > 0)) ? print_r($_POST['tables'], true) : 'No tables selected to update';
DupUtil::log($msg);

DupUtil::log("--------------------------------------");
DupUtil::log("KEEP PLUGINS ACTIVE");
DupUtil::log("--------------------------------------");
$msg = (isset($_POST['plugins']) && count($_POST['plugins'] > 0)) ? print_r($_POST['plugins'], true) : 'No plugins selected for activation';
DupUtil::log($msg);

//UPDATE SETTINGS
$serial_plugin_list = (isset($_POST['plugins']) && count($_POST['plugins'] > 0)) ? @serialize($_POST['plugins']) : '';
mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}options` SET option_value = '{$_POST['blogname']}' WHERE option_name = 'blogname' ");
mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}options` SET option_value = '{$serial_plugin_list}'  WHERE option_name = 'active_plugins' ");

DupUtil::log("--------------------------------------");
DupUtil::log("GLOBAL DB-REPLACE");
DupUtil::log("--------------------------------------");

array_push($GLOBALS['REPLACE_LIST'], 
		array('search' => $_POST['url_old'], 'replace' => $_POST['url_new']), 
		array('search' => $_POST['path_old'], 'replace' => $_POST['path_new']), 
		array('search' => rtrim(DupUtil::unset_safe_path($_POST['path_old']), '\\'), 'replace' => rtrim($_POST['path_new'], '/'))
);

@mysqli_autocommit($dbh, false);
$report = DupDBTextSwap::load($dbh, $GLOBALS['REPLACE_LIST'], $_POST['tables'], $GLOBALS['TABLES_SKIP_COLS']);
@mysqli_commit($dbh);
@mysqli_autocommit($dbh, true);


//BUILD JSON RESPONSE
$JSON = array();
$JSON['step1'] = json_decode(urldecode($_POST['json']));
$JSON['step2'] = $report;
$JSON['step2']['warn_all'] = 0;
$JSON['step2']['warnlist'] = array();

DupDBTextSwap::log_stats($report);
DupDBTextSwap::log_errors($report);

//Reset the postguid data
if ($_POST['postguid']) {
	mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}posts` SET guid = REPLACE(guid, '{$_POST['url_new']}', '{$_POST['url_old']}')");
	$update_guid = @mysqli_affected_rows($dbh) or 0;
	DupUtil::log("Reverted '{$update_guid}' post guid columns back to '{$_POST['url_old']}'");
}

/* FINAL UPDATES: Must happen after the global replace to prevent double pathing
  http://xyz.com/abc01 will become http://xyz.com/abc0101  with trailing data */
mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}options` SET option_value = '{$_POST['url_new']}'  WHERE option_name = 'home' ");
mysqli_query($dbh, "UPDATE `{$GLOBALS['FW_TABLEPREFIX']}options` SET option_value = '{$_POST['siteurl']}'  WHERE option_name = 'siteurl' ");


//====================================================================================================
//FINAL CLEANUP
//====================================================================================================
DupUtil::log("\n{$GLOBALS['SEPERATOR1']}");
DupUtil::log('START FINAL CLEANUP: ' . @date('h:i:s'));
DupUtil::log("{$GLOBALS['SEPERATOR1']}");

/*CREATE NEW USER LOGIC */
if (strlen($_POST['wp_username']) >= 4 && strlen($_POST['wp_password']) >= 6) {
	
	$newuser_check = mysqli_query($dbh, "SELECT COUNT(*) AS count FROM `{$GLOBALS['FW_TABLEPREFIX']}users` WHERE user_login = '{$_POST['wp_username']}' ");
	$newuser_row   = mysqli_fetch_row($newuser_check);
    $newuser_count = is_null($newuser_row) ? 0 : $newuser_row[0];
	
	if ($newuser_count == 0) {
	
		$newuser_datetime =	@date("Y-m-d H:i:s");
		$newuser_security = mysqli_real_escape_string($dbh, 'a:1:{s:13:"administrator";s:1:"1";}');

		$newuser_test1 = @mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}users` 
			(`user_login`, `user_pass`, `user_nicename`, `user_email`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) 
			VALUES ('{$_POST['wp_username']}', MD5('{$_POST['wp_password']}'), '{$_POST['wp_username']}', '', '{$newuser_datetime}', '', '0', '{$_POST['wp_username']}')");

		$newuser_insert_id = mysqli_insert_id($dbh);

		$newuser_test2 = @mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` 
				(`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', '{$GLOBALS['FW_TABLEPREFIX']}capabilities', '{$newuser_security}')");

		$newuser_test3 = @mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` 
				(`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', '{$GLOBALS['FW_TABLEPREFIX']}user_level', '10')");
				
		//Misc Meta-Data Settings:
		@mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', 'rich_editing', 'true')");
		@mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', 'admin_color',  'fresh')");
		@mysqli_query($dbh, "INSERT INTO `{$GLOBALS['FW_TABLEPREFIX']}usermeta` (`user_id`, `meta_key`, `meta_value`) VALUES ('{$newuser_insert_id}', 'nickname', '{$_POST['wp_username']}')");

		if ($newuser_test1 && $newuser_test2 && $newuser_test3) {
			DupUtil::log("NEW WP-ADMIN USER: New username '{$_POST['wp_username']}' was created successfully \n ");
		} else {
			$newuser_warnmsg = "NEW WP-ADMIN USER: Failed to create the user '{$_POST['wp_username']}' \n ";
			$JSON['step2']['warnlist'][] = $newuser_warnmsg;
			DupUtil::log($newuser_warnmsg);
		}			
	} 
	else {
		$newuser_warnmsg = "NEW WP-ADMIN USER: Username '{$_POST['wp_username']}' already exists in the database.  Unable to create new account \n";
		$JSON['step2']['warnlist'][] = $newuser_warnmsg;
		DupUtil::log($newuser_warnmsg);
	}
}

/*UPDATE WP-CONFIG FILE */
$patterns = array("/'WP_HOME',\s*'.*?'/", "/'WP_SITEURL',\s*'.*?'/");

$replace = array("'WP_HOME', " . '\'' . $_POST['url_new'] . '\'',
	"'WP_SITEURL', " . '\'' . $_POST['url_new'] . '\'');

$config_file = @file_get_contents('wp-config.php', true);
$config_file = preg_replace($patterns, $replace, $config_file);
file_put_contents('wp-config.php', $config_file);


//Create Snapshots directory
if (!file_exists(DUPLICATOR_SSDIR_NAME)) {
	mkdir(DUPLICATOR_SSDIR_NAME, 0755);
}
$fp = fopen(DUPLICATOR_SSDIR_NAME . '/index.php', 'w');
fclose($fp);


//WEB CONFIG FILE(S)
$currdata = parse_url($_POST['url_old']);
$newdata = parse_url($_POST['url_new']);
$currpath = DupUtil::add_slash(isset($currdata['path']) ? $currdata['path'] : "");
$newpath = DupUtil::add_slash(isset($newdata['path']) ? $newdata['path'] : "");

if ($currpath != $newpath) {
	DupUtil::log("HTACCESS CHANGES:");
	@copy('.htaccess', '.htaccess.orig');
	@copy('web.config', 'web.config.orig');
	@unlink('.htaccess');
	@unlink('web.config');
	DupUtil::log("created backup of original .htaccess to htaccess.orig and web.config to web.config.orig");

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
	DupUtil::log("web configuration file was not renamed because the paths did not change.");
}


//===============================
//WARNING TESTS
//===============================
DupUtil::log("\n--------------------------------------");
DupUtil::log("WARNINGS");
DupUtil::log("--------------------------------------");
$config_vars = array('WP_CONTENT_DIR', 'WP_CONTENT_URL', 'WPCACHEHOME', 'COOKIE_DOMAIN', 'WP_SITEURL', 'WP_HOME', 'WP_TEMP_DIR');
$config_found = DupUtil::string_has_value($config_vars, $config_file);

//Files
if ($config_found) {
	$msg = 'WP-CONFIG WARNING: The wp-config.php has one or more of these values "' . implode(", ", $config_vars) . '" which may cause issues please validate these values by opening the file.';
	$JSON['step2']['warnlist'][] = $msg;
	DupUtil::log($msg);
}

//Database
$result = @mysqli_query($dbh, "SELECT option_value FROM `{$GLOBALS['FW_TABLEPREFIX']}options` WHERE option_name IN ('upload_url_path','upload_path')");
if ($result) {
	while ($row = mysqli_fetch_row($result)) {
		if (strlen($row[0])) {
			$msg = "MEDIA SETTINGS WARNING: The table '{$GLOBALS['FW_TABLEPREFIX']}options' has at least one the following values ['upload_url_path','upload_path'] set please validate settings. These settings can be changed in the wp-admin by going to Settings->Media area see 'Uploading Files'";
			$JSON['step2']['warnlist'][] = $msg;
			DupUtil::log($msg);
			break;
		}
	}
}

if (empty($JSON['step2']['warnlist'])) {
	DupUtil::log("No Warnings Found\n");
}

$JSON['step2']['warn_all'] = empty($JSON['step2']['warnlist']) ? 0 : count($JSON['step2']['warnlist']);

mysqli_close($dbh);
@unlink('database.sql');

$ajax2_end = DupUtil::get_microtime();
$ajax2_sum = DupUtil::elapsed_time($ajax2_end, $ajax2_start);
DupUtil::log("{$GLOBALS['SEPERATOR1']}");
DupUtil::log('STEP 2 COMPLETE @ ' . @date('h:i:s') . " - TOTAL RUNTIME: {$ajax2_sum}");
DupUtil::log("{$GLOBALS['SEPERATOR1']}");

$JSON['step2']['pass'] = 1;
error_reporting($ajax2_error_level);
die(json_encode($JSON));
?>