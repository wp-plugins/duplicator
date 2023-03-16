<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/**
 * Lightweight abstraction layer for common simple database routines
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    Duplicator
 * @subpackage classes/utilities
 * @copyright  (c) 2017, Snapcreek LLC
 */
// Exit if accessed directly
if (!defined('DUPLICATOR_VERSION')) {
    exit;
}

class DUP_DB extends wpdb
{
    const MAX_TABLE_COUNT_IN_PACKET                 = 100;
    public static $remove_placeholder_escape_exists = null;

    public static function init()
    {
        global $wpdb;
        self::$remove_placeholder_escape_exists = method_exists($wpdb, 'remove_placeholder_escape');
    }

    /**
     * Get the requested MySQL system variable
     *
     * @param string $name The database variable name to lookup
     *
     * @return string the server variable to query for
     */
    public static function getVariable($name)
    {
        global $wpdb;
        if (strlen($name)) {
            $row = $wpdb->get_row("SHOW VARIABLES LIKE '{$name}'", ARRAY_N);
            return isset($row[1]) ? $row[1] : null;
        } else {
            return null;
        }
    }

    /**
     * Gets the MySQL database version number
     *
     * @param bool $full    True:  Gets the full version
     *                      False: Gets only the numeric portion i.e. 5.5.6 or 10.1.2 (for MariaDB)
     *
     * @return false|string 0 on failure, version number on success
     */
    public static function getVersion($full = false)
    {
        global $wpdb;
        if ($full) {
            $version = self::getVariable('version');
        } else {
            $version = preg_replace('/[^0-9.].*/', '', self::getVariable('version'));
        }

        //Fall-back for servers that have restricted SQL for SHOW statement
        if (empty($version)) {
            $version = $wpdb->db_version();
        }

        return empty($version) ? 0 : $version;
    }

    /**
     * Try to return the mysqldump path on Windows servers
     *
     * @return boolean|string
     */
    public static function getWindowsMySqlDumpRealPath()
    {
        if (function_exists('php_ini_loaded_file')) {
            $get_php_ini_path = php_ini_loaded_file();
            if (file_exists($get_php_ini_path)) {
                $search = array(
                    dirname(dirname($get_php_ini_path)) . '/mysql/bin/mysqldump.exe',
                    dirname(dirname(dirname($get_php_ini_path))) . '/mysql/bin/mysqldump.exe',
                    dirname(dirname($get_php_ini_path)) . '/mysql/bin/mysqldump',
                    dirname(dirname(dirname($get_php_ini_path))) . '/mysql/bin/mysqldump',
                );
                foreach ($search as $mysqldump) {
                    if (file_exists($mysqldump)) {
                        return str_replace("\\", "/", $mysqldump);
                    }
                }
            }
        }

        unset($search);
        unset($get_php_ini_path);
        return false;
    }

    /**
     * Returns the correct database build mode PHP, MYSQLDUMP, PHPCHUNKING
     *
     * @return string   Returns a string with one of theses three values PHP, MYSQLDUMP, PHPCHUNKING
     */
    public static function getBuildMode()
    {
        $package_mysqldump = DUP_Settings::Get('package_mysqldump');
        $mysqlDumpPath     = DUP_DB::getMySqlDumpPath();
        return ($mysqlDumpPath && $package_mysqldump) ? 'MYSQLDUMP' : 'PHP';
    }

    /**
     * Returns the mysqldump path if the server is enabled to execute it otherwise false
     *
     * @return boolean|string
     */
    public static function getMySqlDumpPath()
    {
        //Is shell_exec possible
        if (!DUP_Util::hasShellExec()) {
            return false;
        }

        $custom_mysqldump_path = DUP_Settings::Get('package_mysqldump_path');
        $custom_mysqldump_path = (strlen($custom_mysqldump_path)) ? $custom_mysqldump_path : '';
        $custom_mysqldump_path = escapeshellcmd($custom_mysqldump_path);

        // Common Windows Paths
        if (DUP_Util::isWindows()) {
            $paths = array(
                $custom_mysqldump_path,
                'mysqldump.exe',
                self::getWindowsMySqlDumpRealPath(),
                'C:/xampp/mysql/bin/mysqldump.exe',
                'C:/Program Files/xampp/mysql/bin/mysqldump',
                'C:/Program Files/MySQL/MySQL Server 6.0/bin/mysqldump',
                'C:/Program Files/MySQL/MySQL Server 5.5/bin/mysqldump',
                'C:/Program Files/MySQL/MySQL Server 5.4/bin/mysqldump'
            );
        // Common Linux Paths
        } else {
            $paths = array(
                $custom_mysqldump_path,
                'mysqldump',
                '/usr/local/bin/mysqldump',
                '/usr/local/mysql/bin/mysqldump',
                '/usr/mysql/bin/mysqldump',
                '/usr/bin/mysqldump',
                '/opt/local/lib/mysql6/bin/mysqldump',
                '/opt/local/lib/mysql5/bin/mysqldump',
                '/usr/bin/mysqldump',
            );
        }

        $exec_available = function_exists('exec');
        foreach ($paths as $path) {
            if (@file_exists($path)) {
                if (DUP_Util::isExecutable($path)) {
                    return $path;
                }
            } elseif ($exec_available) {
                $out = array();
                $rc  = -1;
                $cmd = $path . ' --help';
                @exec($cmd, $out, $rc);
                if ($rc === 0) {
                    return $path;
                }
            }
        }

        return false;
    }

    /**
     * Get Sql query to create table which is given.
     *
     * @param string $table Table name
     *
     * @return string mysql query create table
     */
    private static function getCreateTableQuery($table)
    {
        $row = $GLOBALS['wpdb']->get_row('SHOW CREATE TABLE `' . esc_sql($table) . '`', ARRAY_N);
        return $row[1];
    }

    /**
     * Returns all collation types that are assigned to the tables in
     * the current database.  Each element in the array is unique
     *
     * @param array $tables A list of tables to include from the search
     *
     * @return array    Returns an array with all the character set being used
     */
    public static function getTableCharSetList($tables)
    {
        $charSets = array();
        try {
            foreach ($tables as $table) {
                $createTableQuery = self::getCreateTableQuery($table);
                if (preg_match('/ CHARSET=([^\s;]+)/i', $createTableQuery, $charsetMatch)) {
                    if (!in_array($charsetMatch[1], $charSets)) {
                        $charSets[] = $charsetMatch[1];
                    }
                }
            }
            return $charSets;
        } catch (Exception $ex) {
            return $charSets;
        }
    }

    /**
     * Returns all collation types that are assigned to the tables and columns table in
     * the current database.  Each element in the array is unique
     *
     * @param string[] $tablesToInclude A list of tables to include in the search.
     *
     * @return string[]    Returns an array with all the collation types being used
     */
    public static function getTableCollationList($tablesToInclude)
    {
        global $wpdb;
        static $collations = null;
        if (is_null($collations)) {
            $collations = array();
        //use half the number of tables since we are using them twice
            foreach (array_chunk($tablesToInclude, self::MAX_TABLE_COUNT_IN_PACKET) as $tablesChunk) {
                $sqlTables = implode(",", array_map(array(__CLASS__, 'escValueToQueryString'), $tablesChunk));
//UNION is by default DISTINCT
                $query = "SELECT `COLLATION_NAME` FROM `information_schema`.`columns` WHERE `COLLATION_NAME` IS NOT NULL AND `table_schema` = '{$wpdb->dbname}' "
                    . "AND `table_name` in (" . $sqlTables . ")"
                    . "UNION SELECT `TABLE_COLLATION` FROM `information_schema`.`tables` WHERE `TABLE_COLLATION` IS NOT NULL AND `table_schema` = '{$wpdb->dbname}' "
                    . "AND `table_name` in (" . $sqlTables . ")";
                if (!$wpdb->query($query)) {
                    DUP_Log::Info("GET TABLE COLLATION ERROR: " . $wpdb->last_error);
                    continue;
                }

                $collations = array_unique(array_merge($collations, $wpdb->get_col()));
            }
            $collations = array_values($collations);
            sort($collations);
        }
        return $collations;
    }

    /**
     * Returns list of MySQL engines used by $tablesToInclude in the current DB
     *
     * @param string[] $tablesToInclude tables to check the engines for
     *
     * @return string[]
     */
    public static function getTableEngineList($tablesToInclude)
    {
        global $wpdb;
        static $engines = null;
        if (is_null($engines)) {
            $engines = array();
            foreach (array_chunk($tablesToInclude, self::MAX_TABLE_COUNT_IN_PACKET) as $tablesChunk) {
                $query = "SELECT DISTINCT `ENGINE` FROM `information_schema`.`tables` WHERE `ENGINE` IS NOT NULL AND `table_schema` = '{$wpdb->dbname}' "
                    . "AND `table_name` in (" . implode(",", array_map(array(__CLASS__, 'escValueToQueryString'), $tablesChunk)) . ")";
                if (!$wpdb->query($query)) {
                    DUP_Log::info("GET TABLE ENGINES ERROR: " . $wpdb->last_error);
                }

                $engines = array_merge($engines, $wpdb->get_col($query));
            }
            $engines = array_values(array_unique($engines));
        }

        return $engines;
    }

    /**
     * Returns an escaped SQL string
     *
     * @param string    $sql                        The SQL to escape
     * @param bool      $removePlaceholderEscape    Patch for how the default WP function works.
     *
     * @return boolean|string
     * @also   see: https://make.wordpress.org/core/2017/10/31/changed-behaviour-of-esc_sql-in-wordpress-4-8-3/
     */
    public static function escSQL($sql, $removePlaceholderEscape = false)
    {
        global $wpdb;
        $removePlaceholderEscape = $removePlaceholderEscape && self::$remove_placeholder_escape_exists;
        if ($removePlaceholderEscape) {
            return $wpdb->remove_placeholder_escape(@esc_sql($sql));
        } else {
            return @esc_sql($sql);
        }
    }

     /**
     * this function escape sql string without add and remove remove_placeholder_escape
     * doesn't work on array
     *
     * @param mixed $sql
     *
     * @return string
     */
    public static function escValueToQueryString($value)
    {
        global $wpdb;
        if (is_null($value)) {
            return 'NULL';
        }

        if ($wpdb->use_mysqli) {
            return '"' . mysqli_real_escape_string($wpdb->dbh, $value) . '"';
        } else {
            return '"' . mysql_real_escape_string($value, $wpdb->dbh) . '"';
        }
    }
}
