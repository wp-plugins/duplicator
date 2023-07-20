<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Snap;

use Exception;
use mysqli;
use mysqli_result;

class SnapDB
{
    const CONN_MYSQL                      = 'mysql';
    const CONN_MYSQLI                     = 'mysqli';
    const CACHE_PREFIX_PRIMARY_KEY_COLUMN = 'pkcol_';
    const DB_ENGINE_MYSQL                 = 'MySQL';
    const DB_ENGINE_MARIA                 = 'MariaDB';
    const DB_ENGINE_PERCONA               = 'Percona';

    /** @var array<string, mixed> */
    private static $cache = array();

    /**
     * Return array if primary key is composite key
     *
     * @param mysqli|resource $dbh         database connection
     * @param string          $tableName   table name
     * @param null|callable   $logCallback log callback
     *
     * @return false|string|string[] return unique index column ky or false if don't exists
     */
    public static function getUniqueIndexColumn($dbh, $tableName, $logCallback = null)
    {
        $cacheKey = self::CACHE_PREFIX_PRIMARY_KEY_COLUMN . $tableName;

        if (!isset(self::$cache[$cacheKey])) {
            $query = 'SHOW COLUMNS FROM `' . self::realEscapeString($dbh, $tableName) . '` WHERE `Key` IN ("PRI","UNI")';
            if (($result = self::query($dbh, $query)) === false) {
                if (is_callable($logCallback)) {
                    call_user_func($logCallback, $dbh, $result, $query);
                }
                throw new \Exception('SHOW KEYS QUERY ERROR: ' . self::error($dbh));
            }

            if (is_callable($logCallback)) {
                call_user_func($logCallback, $dbh, $result, $query);
            }

            if (self::numRows($result) == 0) {
                self::$cache[$cacheKey] = false;
            } else {
                $primary        = false;
                $excludePrimary = false;
                $unique         = false;

                while ($row = self::fetchAssoc($result)) {
                    switch ($row['Key']) {
                        case 'PRI':
                            if ($primary === false) {
                                $primary = $row['Field'];
                            } else {
                                if (is_scalar($primary)) {
                                    $primary = array($primary);
                                }
                                $primary[] = $row['Field'];
                            }

                            if (preg_match('/^(?:var)?binary/i', $row['Type'])) {
                                // exclude binary or varbynary columns
                                $excludePrimary = true;
                            }
                            break;
                        case 'UNI':
                            if (!preg_match('/^(?:var)?binary/i', $row['Type'])) {
                                // exclude binary or varbynary columns
                                $unique = $row['Field'];
                            }
                            break;
                        default:
                            break;
                    }
                }
                if ($primary !== false && $excludePrimary === false) {
                    self::$cache[$cacheKey] = $primary;
                } elseif ($unique !== false) {
                    self::$cache[$cacheKey] = $unique;
                } else {
                    self::$cache[$cacheKey] = false;
                }
            }
            self::freeResult($result);
        }

        return self::$cache[$cacheKey];
    }

    /**
     * Escape the regex for mysql queries, the mysqli_real_escape must be applied anyway to the generated string
     *
     * @param string $regex Regex
     *
     * @return string Escaped regex
     */
    public static function quoteRegex($regex)
    {
        // preg_quote takes a string and escapes special characters with a backslash.
        // It is meant for PHP regexes, not MySQL regexes, and it does not escape &,
        // which is needed for MySQL. So we only need to modify it like so:
        // https://stackoverflow.com/questions/3782379/whats-the-best-way-to-escape-user-input-for-regular-expressions-in-mysql
        return preg_replace('/&/', '\\&', preg_quote($regex, null /* no delimiter */));
    }

    /**
     * Returns the offset from the current row
     *
     * @param mixed[]             $row          current database row
     * @param int|string|string[] $indexColumns columns of the row that generated the index offset
     * @param mixed               $lastOffset   last offset
     *
     * @return mixed
     */
    public static function getOffsetFromRowAssoc($row, $indexColumns, $lastOffset)
    {
        if (is_array($indexColumns)) {
            $result = array();
            foreach ($indexColumns as $col) {
                $result[$col] = isset($row[$col]) ? $row[$col] : 0;
            }
            return $result;
        } elseif (strlen($indexColumns) > 0) {
            return isset($row[$indexColumns]) ? $row[$indexColumns] : 0;
        } else {
            if (is_scalar($lastOffset)) {
                return $lastOffset + 1;
            } else {
                return $lastOffset;
            }
        }
    }

    /**
     * This function performs a select by structuring the primary key as offset if the table has a primary key.
     * For optimization issues, no checks are performed on the input query and it is assumed that the select has at least a where value.
     * If there are no conditions, you still have to perform an always true condition, for example
     * SELECT * FROM `copy1_postmeta` WHERE 1
     *
     * @param mysqli|resource $dbh           database connection
     * @param string          $query         query string
     * @param string          $table         table name
     * @param int             $offset        row offset
     * @param int             $limit         limit of query, 0 no limit
     * @param mixed           $lastRowOffset last offset to use on next function call
     * @param null|callable   $logCallback   log callback
     *
     * @return mysqli_result
     */
    public static function selectUsingPrimaryKeyAsOffset($dbh, $query, $table, $offset, $limit, &$lastRowOffset = null, $logCallback = null)
    {
        $where     = '';
        $orderby   = '';
        $offsetStr = '';
        $limitStr  = $limit > 0 ? ' LIMIT ' . $limit : '';

        if (($primaryColumn = self::getUniqueIndexColumn($dbh, $table, $logCallback)) == false) {
            $offsetStr = ' OFFSET ' . (is_scalar($offset) ? $offset : 0);
        } else {
            if (is_array($primaryColumn)) {
                // COMPOSITE KEY
                $orderByCols = array();
                foreach ($primaryColumn as $colIndex => $col) {
                    $orderByCols[] = '`' . $col . '` ASC';
                }
                $orderby = ' ORDER BY ' . implode(',', $orderByCols);
            } else {
                $orderby = ' ORDER BY `' . $primaryColumn . '` ASC';
            }
            $where = self::getOffsetKeyCondition($dbh, $primaryColumn, $offset);
        }
        $query .= $where . $orderby . $limitStr . $offsetStr;

        if (($result = self::query($dbh, $query)) === false) {
            if (is_callable($logCallback)) {
                call_user_func($logCallback, $dbh, $result, $query);
            }
            throw new \Exception('SELECT ERROR: ' . self::error($dbh) . ' QUERY: ' . $query);
        }

        if (is_callable($logCallback)) {
            call_user_func($logCallback, $dbh, $result, $query);
        }

        if (self::dbConnTypeByResult($result) === self::CONN_MYSQLI) {
            if ($primaryColumn == false) {
                $lastRowOffset = $offset + $result->num_rows;
            } else {
                if ($result->num_rows == 0) {
                    $lastRowOffset = $offset;
                } else {
                    $result->data_seek(($result->num_rows - 1));
                    $row = $result->fetch_assoc();
                    if (is_array($primaryColumn)) {
                        $lastRowOffset = array();
                        foreach ($primaryColumn as $col) {
                            $lastRowOffset[$col] = $row[$col];
                        }
                    } else {
                        $lastRowOffset = $row[$primaryColumn];
                    }
                    $result->data_seek(0);
                }
            }
        } else {
            if ($primaryColumn == false) {
                $lastRowOffset = $offset + mysql_num_rows($result); // @phpstan-ignore-line
            } else {
                if (mysql_num_rows($result) == 0) {  // @phpstan-ignore-line
                    $lastRowOffset = $offset;
                } else {
                    mysql_data_seek($result, (mysql_num_rows($result) - 1));  // @phpstan-ignore-line
                    $row = mysql_fetch_assoc($result);  // @phpstan-ignore-line
                    if (is_array($primaryColumn)) {
                        $lastRowOffset = array();
                        foreach ($primaryColumn as $col) {
                            $lastRowOffset[$col] = $row[$col];
                        }
                    } else {
                        $lastRowOffset = $row[$primaryColumn];
                    }
                    mysql_data_seek($result, 0); // @phpstan-ignore-line
                }
            }
        }

        return $result;
    }

    /**
     * Depending on the structure type of the primary key returns the condition to position at the right offset
     *
     * @param mysqli|resource $dbh           database connection
     * @param string|string[] $primaryColumn primaricolumng index
     * @param mixed           $offset        offset
     *
     * @return string
     */
    protected static function getOffsetKeyCondition($dbh, $primaryColumn, $offset)
    {
        $condition = '';

        if ($offset === 0) {
            return '';
        }

        // COUPOUND KEY
        if (is_array($primaryColumn)) {
            $isFirstCond = true;

            foreach ($primaryColumn as $colIndex => $col) {
                if (is_array($offset) && isset($offset[$col])) {
                    if ($isFirstCond) {
                        $isFirstCond = false;
                    } else {
                        $condition .= ' OR ';
                    }
                    $condition .= ' (';
                    for ($prevColIndex = 0; $prevColIndex < $colIndex; $prevColIndex++) {
                        $condition .=
                            ' `' . $primaryColumn[$prevColIndex] . '` = "' .
                            self::realEscapeString($dbh, $offset[$primaryColumn[$prevColIndex]]) . '" AND ';
                    }
                    $condition .= ' `' . $col . '` > "' . self::realEscapeString($dbh, $offset[$col]) . '")';
                }
            }
        } else {
            $condition = '`' . $primaryColumn . '` > "' . self::realEscapeString($dbh, (is_scalar($offset) ? $offset : 0)) . '"';
        }

        return (strlen($condition) ? ' AND (' . $condition . ')' : '');
    }

    /**
     * get current database engine (mysql, maria, percona)
     *
     * @param mysqli|resource $dbh database connection
     *
     * @return string
     */
    public static function getDBEngine($dbh)
    {
        if (($result = self::query($dbh, "SHOW VARIABLES LIKE 'version%'")) === false) {
            // on query error assume is mysql.
            return self::DB_ENGINE_MYSQL;
        }

        $rows = array();
        while ($row  = self::fetchRow($result)) {
            $rows[] = $row;
        }
        self::freeResult($result);

        $version        = isset($rows[0][1]) ? $rows[0][1] : false;
        $versionComment = isset($rows[1][1]) ? $rows[1][1] : false;

        //Default is mysql
        if ($version === false && $versionComment === false) {
            return self::DB_ENGINE_MYSQL;
        }

        if (stripos($version, 'maria') !== false || stripos($versionComment, 'maria') !== false) {
            return self::DB_ENGINE_MARIA;
        }

        if (stripos($version, 'percona') !== false || stripos($versionComment, 'percona') !== false) {
            return self::DB_ENGINE_PERCONA;
        }

        return self::DB_ENGINE_MYSQL;
    }

    /**
     * Escape string
     *
     * @param resource|mysqli $dbh    database connection
     * @param string          $string string to escape
     *
     * @return string Returns an escaped string.
     */
    public static function realEscapeString($dbh, $string)
    {
        if (self::dbConnType($dbh) === self::CONN_MYSQLI) {
            return mysqli_real_escape_string($dbh, $string);
        } else {
            return mysql_real_escape_string($string, $dbh);  // @phpstan-ignore-line
        }
    }

    /**
     *
     * @param resource|mysqli $dbh   database connection
     * @param string          $query query string
     *
     * @return mixed <p>Returns <b><code>FALSE</code></b> on failure. For successful <i>SELECT, SHOW, DESCRIBE</i> or
     *               <i>EXPLAIN</i> queries <b>mysqli_query()</b> will return a mysqli_result object.
     *               For other successful queries <b>mysqli_query()</b> will return <b><code>TRUE</code></b>.</p>
     */
    public static function query($dbh, $query)
    {
        try {
            if (self::dbConnType($dbh) === self::CONN_MYSQLI) {
                return mysqli_query($dbh, $query);
            } else {
                return mysql_query($query, $dbh); // @phpstan-ignore-line
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     *
     * @param resource|mysqli_result $result query result
     *
     * @return int
     */
    public static function numRows($result)
    {
        if (self::dbConnTypeByResult($result) === self::CONN_MYSQLI) {
            return $result->num_rows;
        } else {
            return mysql_num_rows($result); // @phpstan-ignore-line
        }
    }

    /**
     *
     * @param resource|mysqli_result $result query result
     *
     * @return string[]|null|false Returns an array of strings that corresponds to the fetched row. NULL if there are no more rows in result set
     */
    public static function fetchRow($result)
    {
        if (self::dbConnTypeByResult($result) === self::CONN_MYSQLI) {
            return mysqli_fetch_row($result);
        } elseif (is_resource($result)) {
            return mysql_fetch_row($result); // @phpstan-ignore-line
        } else {
            return false;
        }
    }

    /**
     *
     * @param resource|mysqli_result $result query result
     *
     * @return string[]|null|false Returns an associative array of values representing the fetched row in the result set,
     *               where each key in the array represents the name of one of the result set's
     *               columns or null if there are no more rows in result set.
     */
    public static function fetchAssoc($result)
    {
        if (self::dbConnTypeByResult($result) === self::CONN_MYSQLI) {
            return mysqli_fetch_assoc($result);
        } elseif (is_resource($result)) {
            return mysql_fetch_assoc($result); // @phpstan-ignore-line
        } else {
            return false;
        }
    }

    /**
     *
     * @param resource|mysqli_result $result query result
     *
     * @return boolean
     */
    public static function freeResult($result)
    {
        if (self::dbConnTypeByResult($result) === self::CONN_MYSQLI) {
            $result->free();
            return true;
        } elseif (is_resource($result)) {
            return mysql_free_result($result); // @phpstan-ignore-line
        } else {
            $result = null;
            return true;
        }
    }

    /**
     *
     * @param resource|mysqli $dbh database connection
     *
     * @return string
     */
    public static function error($dbh)
    {
        if (self::dbConnType($dbh) === self::CONN_MYSQLI) {
            if ($dbh instanceof mysqli) {
                return mysqli_error($dbh);
            } else {
                return 'Unable to retrieve the error message from MySQL';
            }
        } else {
            if (is_resource($dbh)) {
                return mysql_error($dbh); // @phpstan-ignore-line
            } else {
                return 'Unable to retrieve the error message from MySQL';
            }
        }
    }

    /**
     *
     * @param resource|mysqli $dbh database connection
     *
     * @return string // self::CONN_MYSQLI|self::CONN_MYSQL
     */
    public static function dbConnType($dbh)
    {
        return (is_object($dbh) && get_class($dbh) == 'mysqli') ? self::CONN_MYSQLI : self::CONN_MYSQL;
    }

    /**
     *
     * @param resource|mysqli_result $result query resyult
     *
     * @return string Enum self::CONN_MYSQLI|self::CONN_MYSQL
     */
    public static function dbConnTypeByResult($result)
    {
        return (is_object($result) && get_class($result) == 'mysqli_result') ? self::CONN_MYSQLI : self::CONN_MYSQL;
    }

    /**
     * This function takes in input the values of a multiple inster with this format
     * (v1, v2, v3 ...),(v1, v2, v3, ...),...
     * and returns a two dimensional array where each item is a row containing the list of values
     * [
     *   [v1, v2, v3 ...],
     *   [v1, v2, v3 ...],
     *   ...
     * ]
     * The return values are not processed but are taken exactly as they are in the dump file.
     * So if they are escaped it remains unchanged
     *
     * @param string $query query values
     *
     * @return array<array<scalar>>
     */
    public static function getValuesFromQueryInsert($query)
    {
        $result       = array();
        $isItemOpen   = false;
        $isStringOpen = false;
        $char         = '';
        $pChar        = '';

        $currentItem  = array();
        $currentValue = '';

        for ($i = 0; $i < strlen($query); $i++) {
            $pChar = $char;
            $char  = $query[$i];

            switch ($char) {
                case '(':
                    if ($isItemOpen == false && !$isStringOpen) {
                        $isItemOpen = true;
                        continue 2;
                    }
                    break;
                case ')':
                    if ($isItemOpen && !$isStringOpen) {
                        $isItemOpen    = false;
                        $currentItem[] = trim($currentValue);
                        $currentValue  = '';
                        $result[]      = $currentItem;
                        $currentItem   = array();
                        continue 2;
                    }
                    break;
                case '\'':
                case '"':
                    if ($isStringOpen === false && $pChar !== '\\') {
                        $isStringOpen = $char;
                    } elseif ($isStringOpen === $char && $pChar !== '\\') {
                        $isStringOpen = false;
                    }
                    break;
                case ',':
                    if ($isItemOpen == false) {
                        continue 2;
                    } elseif ($isStringOpen === false) {
                        $currentItem[] = trim($currentValue);
                        $currentValue  = '';
                        continue 2;
                    }
                    break;
                default:
                    break;
            }

            if ($isItemOpen == false) {
                continue;
            }

            $currentValue .= $char;
        }
        return $result;
    }

    /**
     * This is the inverse of getValuesFromQueryInsert, from an array of values it returns the valody of an insert query
     *
     * @param mixed[] $values rows values
     *
     * @return string
     */
    public static function getQueryInsertValuesFromArray(array $values)
    {

        return implode(
            ',',
            array_map(
                function ($rowVals) {
                    return '(' . implode(',', $rowVals) . ')';
                },
                $values
            )
        );
    }

    /**
     * Returns the content of a value resulting from getValuesFromQueryInsert in string
     * Then remove the outer quotes and escape
     * "value\"test" become value"test
     *
     * @param string $value value
     *
     * @return string
     */
    public static function parsedQueryValueToString($value)
    {
        $result = preg_replace('/^[\'"]?(.*?)[\'"]?$/s', '$1', $value);
        return stripslashes($result);
    }

    /**
     * Returns the content of a value resulting from getValuesFromQueryInsert in int
     * Then remove the outer quotes and escape
     * "100" become (int)100
     *
     * @param string $value value
     *
     * @return int
     */
    public static function parsedQueryValueToInt($value)
    {
        return (int) preg_replace('/^[\'"]?(.*?)[\'"]?$/s', '$1', $value);
    }

    /**
     * Return the list of mysqlrealconnect existing flags values from mask
     *
     * @see https://www.php.net/manual/en/mysqli.real-connect.php
     *
     * @param bool       $returnStr if true return define string else values
     * @param null|int[] $filter    if not null only the values that exist and are contained in the array are returned
     *
     * @return int[]|string[]
     */
    public static function getMysqlConnectFlagsList($returnStr = true, $filter = null)
    {
        static $flagsList = null;

        if (is_null($flagsList)) {
            $flagsList = array();

            if (defined('MYSQLI_CLIENT_COMPRESS')) {
                $flagsList[MYSQLI_CLIENT_COMPRESS] = 'MYSQLI_CLIENT_COMPRESS';
            }
            if (defined('MYSQLI_CLIENT_FOUND_ROWS')) {
                $flagsList[MYSQLI_CLIENT_FOUND_ROWS] = 'MYSQLI_CLIENT_FOUND_ROWS';
            }
            if (defined('MYSQLI_CLIENT_IGNORE_SPACE')) {
                $flagsList[MYSQLI_CLIENT_IGNORE_SPACE] = 'MYSQLI_CLIENT_IGNORE_SPACE';
            }
            if (defined('MYSQLI_CLIENT_INTERACTIVE')) {
                $flagsList[MYSQLI_CLIENT_INTERACTIVE] = 'MYSQLI_CLIENT_INTERACTIVE';
            }
            if (defined('MYSQLI_CLIENT_SSL')) {
                $flagsList[MYSQLI_CLIENT_SSL] = 'MYSQLI_CLIENT_SSL';
            }
            if (defined('MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT')) {
                $flagsList[MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT] = 'MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT';
            }
        }

        if (is_null($filter)) {
            $result = $flagsList;
        } else {
            $result = array();
            foreach ($flagsList as $flagVal => $flag) {
                if (!in_array($flagVal, $filter)) {
                    continue;
                }
                $result[$flagVal] = $flag;
            }
        }

        if ($returnStr) {
            return array_values($result);
        } else {
            return array_keys($result);
        }
    }

    /**
     * Return the list of mysqlrealconnect flags values from mask
     *
     * @see https://www.php.net/manual/en/mysqli.real-connect.php
     *
     * @param int $value mask value
     *
     * @return int[]
     */
    public static function getMysqlConnectFlagsFromMaskVal($value)
    {
        /*
        MYSQLI_CLIENT_COMPRESS 32
        MYSQLI_CLIENT_FOUND_ROWS 2
        MYSQLI_CLIENT_IGNORE_SPACE 256
        MYSQLI_CLIENT_INTERACTIVE 1024
        MYSQLI_CLIENT_SSL 2048
        MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT 64
        */

        $result = array();

        foreach (self::getMysqlConnectFlagsList(false) as $flagVal) {
            if (($value & $flagVal) > 0) {
                $result[] = $flagVal;
            }
        }

        return $result;
    }

    /**
     * Returns a list of redundant case insensitive duplicate tables
     *
     * @param string   $prefix     The WP table prefix
     * @param string[] $duplicates List of case insensitive duplicate table names
     *
     * @return string[]
     */
    public static function getRedundantDuplicateTables($prefix, $duplicates)
    {
        foreach ($duplicates as $i => $tableName) {
            if (stripos($tableName, $prefix) === 0) {
                //table has prefix, the case sensitive match is not redundant
                if (strpos($tableName, $prefix) === 0) {
                    unset($duplicates[$i]);
                    break;
                }

                //no case sensitive match is present, first table is not redundant
                if ($i === (count($duplicates) - 1)) {
                    unset($duplicates[0]);
                    break;
                }
            } else {
                //no prefix present, first table not redundant
                unset($duplicates[$i]);
                break;
            }
        }

        return array_values($duplicates);
    }
}
