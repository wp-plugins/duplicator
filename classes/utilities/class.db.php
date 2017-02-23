<?php
if (!defined('DUPLICATOR_VERSION')) exit; // Exit if accessed directly

/**
 * Lightweight abstraction layer for common simple database routines
 *
 * Standard: PSR-2
 *
 * @package SC\Dup\DB
 *
 */
class DUP_DB extends wpdb
{

    /**
     * Get the requested MySQL system variable
     *
     * @param string $name The database variable name to lookup
     *
     * @return string the server variable to query for
     */
    public static function mysqlVariable($name)
    {
        global $wpdb;
        $row = $wpdb->get_row("SHOW VARIABLES LIKE '{$name}'", ARRAY_N);
        return isset($row[1]) ? $row[1] : null;
    }

    /**
     * Gets the MySQL database version number
     *
     * @param bool $full    True:  Gets the full version
     *                      False: Gets only the numeric portion i.e. 5.5.6 or 10.1.2 (for MariaDB)
     *
     * @return false|string 0 on failure, version number on success
     */
    public static function mysqlVersion($full = false)
    {
        if ($full) {
            $version = self::mysqlVariable('version');
        } else {
            $version = preg_replace('/[^0-9.].*/', '', self::mysqlVariable('version'));
        }

        return empty($version) ? 0 : $version;
    }
}