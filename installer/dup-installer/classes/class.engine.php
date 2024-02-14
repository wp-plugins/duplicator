<?php

/**
 * Walks every table in db that then walks every row and column replacing searches with replaces
 * large tables are split into 50k row blocks to save on memory.
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\UpdateEngine
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Installer\Utils\Log\LogHandler;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapDB;

class DUPX_UpdateEngine
{
    const SR_PRORITY_HIGH                 = 5;
    const SR_PRORITY_DEFAULT              = 10;
    const SR_PRORITY_LOW                  = 20;
    const SR_PRORITY_NETWORK_SUBSITE_HIGH = 4;
    const SR_PRORITY_NETWORK_SUBSITE      = 5;
    const SR_PRORITY_GENERIC_SUBST        = 10;
    const SR_PRORITY_GENERIC_SUBST_P1     = 10;
    const SR_PRORITY_GENERIC_SUBST_P2     = 11;
    const SR_PRORITY_GENERIC_SUBST_P3     = 12;
    const SR_PRORITY_GENERIC_SUBST_P4     = 13;
    const SR_PRORITY_CUSTOM               = 20;
    const SERIALIZE_OPEN_STR_REGEX        = '/^(s:\d+:")/';
    const SERIALIZE_OPEN_SUBSTR_LEN       = 25;
    const SERIALIZE_CLOSE_STR_REGEX       = '/^";}*(?:"|a:|s:|S:|b:|d:|i:|o:|O:|C:|r:|R:|N;|$)/';
    const SERIALIZE_CLOSE_SUBSTR_LEN      = 50;
    const SERIALIZE_CLOSE_STR             = '";';
    const SERIALIZE_CLOSE_STR_LEN         = 2;

    private static $report = null;

    /**
     *  Used to report on all log errors into the installer-txt.log
     *
     * @return string Writes the results of the update engine tables to the log
     */
    public static function logErrors()
    {
        $s3Funcs = DUPX_S3_Funcs::getInstance();

        if (!empty($s3Funcs->report['errsql'])) {
            Log::info("--------------------------------------");
            Log::info("DATA-REPLACE ERRORS (MySQL)");
            foreach ($s3Funcs->report['errsql'] as $error) {
                Log::info($error);
            }
            Log::info("");
        }
        if (!empty($s3Funcs->report['errser'])) {
            Log::info("--------------------------------------");
            Log::info("DATA-REPLACE ERRORS (Serialization):");
            foreach ($s3Funcs->report['errser'] as $error) {
                Log::info($error);
            }
            Log::info("");
        }
        if (!empty($s3Funcs->report['errkey'])) {
            Log::info("--------------------------------------");
            Log::info("DATA-REPLACE ERRORS (Key):");
            Log::info('Use SQL: SELECT @row := @row + 1 as row, t.* FROM some_table t, (SELECT @row := 0) r');
            foreach ($s3Funcs->report['errkey'] as $error) {
                Log::info($error);
            }
        }
    }

    /**
     *  Used to report on all log stats into the installer-txt.log
     *
     * @return string Writes the results of the update engine tables to the log
     */
    public static function logStats()
    {
        $s3Funcs = DUPX_S3_Funcs::getInstance();
        Log::resetIndent();

        if (!empty($s3Funcs->report) && is_array($s3Funcs->report)) {
            $stats  = "--------------------------------------\n";
            $stats .= sprintf("SCANNED:\tTables:%d \t|\t Rows:%d \t|\t Cells:%d \n", $s3Funcs->report['scan_tables'], $s3Funcs->report['scan_rows'], $s3Funcs->report['scan_cells']);
            $stats .= sprintf("UPDATED:\tTables:%d \t|\t Rows:%d \t|\t Cells:%d \n", $s3Funcs->report['updt_tables'], $s3Funcs->report['updt_rows'], $s3Funcs->report['updt_cells']);
            $stats .= sprintf("ERRORS:\t\t%d \nRUNTIME:\t%f sec", $s3Funcs->report['err_all'], $s3Funcs->report['time']);
            Log::info($stats);
        }
    }

    /**
     * Returns only the text type columns of a table ignoring all numeric types
     *
     * @param obj $conn A valid database link handle
     * @param string $table A valid table name
     *
     * @return array All the column names of a table
     */
    private static function getTextColumns($table)
    {
        $dbh = DUPX_S3_Funcs::getInstance()->getDbConnection();

        $type_where  = '';
        $type_where .= "type LIKE '%char%' OR ";
        $type_where .= "type LIKE '%text' OR ";
        $type_where .= "type LIKE '%blob' ";
        $sql         = "SHOW COLUMNS FROM `" . mysqli_real_escape_string($dbh, $table) . "` WHERE {$type_where}";

        $result = DUPX_DB::mysqli_query($dbh, $sql);
        if (!$result || mysqli_num_rows($result) === 0) {
            return null;
        }

        $fields = array();
        while ($row    = mysqli_fetch_assoc($result)) {
            $fields[] = $row['Field'];
        }

        //Return Primary which is needed for index lookup.  LIKE '%PRIMARY%' is less accurate with lookup
        //$result = mysqli_query($dbh, "SHOW INDEX FROM `{$table}` WHERE KEY_NAME LIKE '%PRIMARY%'");
        $result = DUPX_DB::mysqli_query($dbh, "SHOW INDEX FROM `" . mysqli_real_escape_string($dbh, $table) . "`");
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $fields[] = $row['Column_name'];
            }
        }

        return (count($fields) > 0) ? array_unique($fields) : null;
    }

    public static function set_sql_column_safe(&$str)
    {
        $str = "`$str`";
    }

    public static function loadInit()
    {
        Log::info('ENGINE LOAD INIT', Log::LV_DEBUG);
        $s3Funcs                          = DUPX_S3_Funcs::getInstance();
        $s3Funcs->report['profile_start'] = DUPX_U::getMicrotime();

        $dbh = $s3Funcs->getDbConnection();
        @mysqli_autocommit($dbh, false);
    }

    /**
     * Begins the processing for replace logic
     *
     * @param array $tables The tables we want to look at
     *
     * @return array Collection of information gathered during the run.
     */
    public static function load($tables = array())
    {
        self::loadInit();

        if (is_array($tables)) {
            foreach ($tables as $table) {
                self::evaluateTalbe($table);
            }
        }

        self::commitAndSave();
        return self::loadEnd();
    }

    public static function commitAndSave()
    {
        Log::info('ENGINE COMMIT AND SAVE', Log::LV_DEBUG);

        $dbh = DUPX_S3_Funcs::getInstance()->getDbConnection();

        @mysqli_commit($dbh);
        @mysqli_autocommit($dbh, true);

        DUPX_NOTICE_MANAGER::getInstance()->saveNotices();
    }

    public static function loadEnd()
    {
        $s3Funcs = DUPX_S3_Funcs::getInstance();
        Log::info('ENGINE LOAD END', Log::LV_DEBUG);

        $s3Funcs->report['profile_end'] = DUPX_U::getMicrotime();
        $s3Funcs->report['time']        = DUPX_U::elapsedTime($s3Funcs->report['profile_end'], $s3Funcs->report['profile_start']);
        $s3Funcs->report['errsql_sum']  = empty($s3Funcs->report['errsql']) ? 0 : count($s3Funcs->report['errsql']);
        $s3Funcs->report['errser_sum']  = empty($s3Funcs->report['errser']) ? 0 : count($s3Funcs->report['errser']);
        $s3Funcs->report['errkey_sum']  = empty($s3Funcs->report['errkey']) ? 0 : count($s3Funcs->report['errkey']);
        $s3Funcs->report['err_all']     = $s3Funcs->report['errsql_sum'] + $s3Funcs->report['errser_sum'] + $s3Funcs->report['errkey_sum'];

        return $s3Funcs->report;
    }

    public static function getTableRowParamsDefault($table = '')
    {
        return array(
            'table'         => $table,
            'updated'       => false,
            'row_count'     => 0,
            'columns'       => array(),
            'colList'       => '*',
            'colMsg'        => 'every column',
            'columnsSRList' => array(),
            'pages'         => 0,
            'page_size'     => 0,
            'page'          => 0,
            'current_row'   => 0
        );
    }

    private static function getTableRowsParams($table)
    {
        $s3Funcs = DUPX_S3_Funcs::getInstance();
        $dbh     = $s3Funcs->getDbConnection();

        $rowsParams = self::getTableRowParamsDefault($table);

        // Count the number of rows we have in the table if large we'll split into blocks
        $rowsParams['row_count'] = DUPX_DB::mysqli_query($dbh, "SELECT COUNT(*) FROM `" . mysqli_real_escape_string($dbh, $rowsParams['table']) . "`");
        if (!$rowsParams['row_count']) {
            return null;
        }
        $rows_result = mysqli_fetch_array($rowsParams['row_count']);
        @mysqli_free_result($rowsParams['row_count']);
        $rowsParams['row_count'] = $rows_result[0];
        if ($rowsParams['row_count'] == 0) {
            $rowsParams['colMsg'] = 'no columns  ';
            self::logEvaluateTable($rowsParams);
            return null;
        }

        // Get a list of columns in this table
        $sql    = 'DESCRIBE ' . mysqli_real_escape_string($dbh, $rowsParams['table']);
        $fields = DUPX_DB::mysqli_query($dbh, $sql);
        if (!$fields) {
            return null;
        }
        while ($column = mysqli_fetch_array($fields)) {
            $rowsParams['columns'][$column['Field']] = $column['Key'] == 'PRI' ? true : false;
        }

        $rowsParams['page_size']  = $GLOBALS['DATABASE_PAGE_SIZE'];
        $rowsParams['pages']      = ceil($rowsParams['row_count'] / $rowsParams['page_size']);
        $rowsParams['lastOffset'] = 0;

        // Grab the columns of the table.  Only grab text based columns because
        // they are the only data types that should allow any type of search/replace logic
        if (!PrmMng::getInstance()->getValue(PrmMng::PARAM_FULL_SEARCH)) {
            $rowsParams['colList'] = self::getTextColumns($rowsParams['table']);
            if ($rowsParams['colList'] != null && is_array($rowsParams['colList'])) {
                array_walk($rowsParams['colList'], array(__CLASS__, 'set_sql_column_safe'));
                $rowsParams['colList'] = implode(',', $rowsParams['colList']);
            }
            $rowsParams['colMsg'] = (empty($rowsParams['colList'])) ? 'every column' : 'text columns';
        }

        if (empty($rowsParams['colList'])) {
            $rowsParams['colMsg'] = 'no columns  ';
        }

        self::logEvaluateTable($rowsParams);

        if (empty($rowsParams['colList'])) {
            return null;
        } else {
            // PREPARE SEARCH AN REPLACE LISF FOR TABLES
            $rowsParams['columnsSRList'] = self::getColumnsSearchReplaceList($rowsParams['table'], $rowsParams['columns']);
            return $rowsParams;
        }
    }

    public static function logEvaluateTable($rowsParams)
    {
        Log::resetIndent();
        $log  = "\n" . 'EVALUATE TABLE: ' . str_pad(Log::v2str($rowsParams['table']), 50, '_', STR_PAD_RIGHT);
        $log .= '[ROWS:' . str_pad($rowsParams['row_count'], 6, " ", STR_PAD_LEFT) . ']';
        $log .= '[PG:' . str_pad($rowsParams['pages'], 4, " ", STR_PAD_LEFT) . ']';
        $log .= '[SCAN:' . $rowsParams['colMsg'] . ']';
        if (Log::isLevel(Log::LV_DETAILED)) {
            $log .= '[COLS: ' . $rowsParams['colList'] . ']';
        }
        Log::info($log);
        Log::incIndent();
    }

    public static function evaluateTalbe($table)
    {
        $s3Funcs = DUPX_S3_Funcs::getInstance();

        // init table params if isn't initialized
        if (!self::initTableParams($table)) {
            return false;
        }

        //Paged Records
        $pages = $s3Funcs->cTableParams['pages'];
        for ($page = 0; $page < $pages; $page++) {
            self::evaluateTableRows($table, $page);
        }

        if ($s3Funcs->cTableParams['updated']) {
            $s3Funcs->report['updt_tables']++;
        }
    }

    public static function evaluateTableRows($table, $page)
    {
        $s3Funcs = DUPX_S3_Funcs::getInstance();

        // init table params if isn't initialized
        if (!self::initTableParams($table)) {
            return false;
        }

        $s3Funcs->cTableParams['page'] = $page;
        if ($s3Funcs->cTableParams['page'] >= $s3Funcs->cTableParams['pages']) {
            Log::info('ENGINE EXIT PAGE:' . Log::v2str($table) . ' PAGES:' . $s3Funcs->cTableParams['pages'], Log::LV_DEBUG);
            return false;
        }

        return self::evaluatePagedRows($s3Funcs->cTableParams);
    }

    public static function initTableParams($table)
    {
        $s3Funcs = DUPX_S3_Funcs::getInstance();

        if (is_null($s3Funcs->cTableParams) || $s3Funcs->cTableParams['table'] !== $table) {
            Log::resetIndent();
            Log::info("\n" . 'ENGINE INIT TABLE PARAMS ' . Log::v2str($table), Log::LV_DETAILED);
            if (!DUPX_DB_Functions::getInstance()->tablesExist($table)) {
                Log::info('ENGINE TABLE DOESN\'T EXIST IN THE DATABASE', Log::LV_DEBUG);
                $longMsg = <<<MSG
The table could not be found in the database.
If the "Skip Database Extraction" option was chosen in step 2, make sure you insert the database manually before continuing to step 3.
                   
Also, verify the database that was inserted contains the urls and paths of the original site before step 3 for proper migration.
MSG;
                DUPX_NOTICE_MANAGER::getInstance()->addFinalReportNotice(array(
                    'shortMsg' => 'Table ' . $table . ' doesn\'t exist in the database',
                    'level'    => DUPX_NOTICE_ITEM::HARD_WARNING,
                    'longMsg'  => $longMsg,
                    'sections' => 'search_replace'
                ));
                return false;
            }

            $s3Funcs->report['scan_tables']++;

            if (($s3Funcs->cTableParams = self::getTableRowsParams($table)) === null) {
                Log::info('ENGINE TABLE PARAMS EMPTY', Log::LV_DEBUG);
                return false;
            }
        }

        return true;
    }

    /**
     * evaluate rows with pagination
     *
     * @param array $rowsParams
     *
     * @return boolean // if true table is modified and updated
     */
    private static function evaluatePagedRows(&$rowsParams)
    {
        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        $s3Funcs  = DUPX_S3_Funcs::getInstance();
        $dbh      = $s3Funcs->getDbConnection();
        $start    = $rowsParams['page'] * $rowsParams['page_size'];
        $end      = $start + $rowsParams['page_size'] - 1;

        if (Log::isLevel(Log::LV_DETAILED)) {
            $scan_count = min($rowsParams['row_count'], $end);
            Log::info('ENGINE EV TABLE ' . str_pad(Log::v2str($rowsParams['table']), 50, '_', STR_PAD_RIGHT) .
                '[PAGE:' . str_pad($rowsParams['page'], 4, " ", STR_PAD_LEFT) . ']' .
                '[START:' . str_pad($start, 6, " ", STR_PAD_LEFT) .
                '[OFFSET:' . str_pad(Log::v2str($rowsParams['lastOffset']), 8, " ", STR_PAD_LEFT) . ']' .
                '[OF:' . str_pad($scan_count, 6, " ", STR_PAD_LEFT) . ']', Log::LV_DETAILED);
        }

        $data = SnapDB::selectUsingPrimaryKeyAsOffset(
            $dbh,
            "SELECT " . $rowsParams['colList'] . " FROM `" . $rowsParams['table'] . "` WHERE 1",
            $rowsParams['table'],
            $rowsParams['lastOffset'],
            $rowsParams['page_size'],
            $rowsParams['lastOffset'],
            array('DUPX_DB', 'query_log_callback')
        );

        if ($data === false) {
            $errMsg                      = mysqli_error($dbh);
            $s3Funcs->report['errsql'][] = $errMsg;
            $nManager->addFinalReportNotice(array(
                'shortMsg' => 'DATA-REPLACE ERRORS: MySQL',
                'level'    => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'  => $errMsg,
                'sections' => 'search_replace'
            ));
            return false;
        } else {
            //Loops every row
            while ($row = mysqli_fetch_assoc($data)) {
                self::evaluateRow($rowsParams, $row);
            }
            @mysqli_free_result($data);

            return $rowsParams['updated'];
        }
    }

    /**
     * Return max serialize len
     *
     * @return int
     */
    private static function getMaxSerializeLen()
    {
        static $maxLen = null;
        if (is_null($maxLen)) {
            $maxLen = PrmMng::getInstance()->getValue(PrmMng::PARAM_MAX_SERIALIZE_CHECK) * 1000000;
        }
        return $maxLen;
    }

    /**
     * evaluate single row columns
     *
     * @param array $rowsParams
     * @param array $row
     *
     * @return boolean true if row is modified and updated
     */
    private static function evaluateRow(&$rowsParams, $row)
    {
        $nManager             = DUPX_NOTICE_MANAGER::getInstance();
        $s3Funcs              = DUPX_S3_Funcs::getInstance();
        $dbh                  = $s3Funcs->getDbConnection();
        $maxSerializeLenCheck = self::getMaxSerializeLen();

        $s3Funcs->report['scan_rows']++;
        $rowsParams['current_row']++;

        $upd_col    = array();
        $upd_sql    = array();
        $where_sql  = array();
        $upd        = false;
        $serial_err = false;
        $is_unkeyed = !in_array(true, $rowsParams['columns']);

        $rowErrors = array();

        //Loops every cell
        foreach ($rowsParams['columns'] as $column => $primary_key) {
            $s3Funcs->report['scan_cells']++;
            if (!isset($row[$column])) {
                continue;
            }

            $safe_column     = '`' . mysqli_real_escape_string($dbh, $column) . '`';
            $edited_data     = $originalData    = $row[$column];
            $base64converted = false;
            $txt_found       = false;

            //Unkeyed table code
            //Added this here to add all columns to $where_sql
            //The if statement with $txt_found would skip additional columns -TG
            if ($is_unkeyed && !empty($originalData)) {
                $where_sql[] = $safe_column . ' = "' . mysqli_real_escape_string($dbh, $originalData) . '"';
            }

            //Only replacing string values
            if (!empty($row[$column]) && !is_numeric($row[$column]) && $primary_key != 1) {
                // get search and reaplace list for column
                $tColList        = &$rowsParams['columnsSRList'][$column]['list'];
                $tColSearchList  = &$rowsParams['columnsSRList'][$column]['sList'];
                $tColreplaceList = &$rowsParams['columnsSRList'][$column]['rList'];
                $tColExactMatch  = $rowsParams['columnsSRList'][$column]['exactMatch'];

                // skip empty search col
                if (empty($tColSearchList)) {
                    continue;
                }

                // Search strings in data
                foreach ($tColList as $item) {
                    if (preg_match($item['search'], $edited_data)) {
                        $txt_found = true;
                        break;
                    }
                }

                if (!$txt_found) {
                    //if not found decetc Base 64
                    if (($decoded = DUPX_U::is_base64($row[$column])) !== false) {
                        $edited_data     = $decoded;
                        $base64converted = true;

                        // Search strings in data decoded
                        foreach ($tColList as $item) {
                            if (preg_match($item['search'], $edited_data)) {
                                $txt_found = true;
                                break;
                            }
                        }
                    }

                    //Skip table cell if match not found
                    if (!$txt_found) {
                        continue;
                    }
                }

                // 0 no limit
                if ($maxSerializeLenCheck > 0 && self::is_serialized_string($edited_data) && strlen($edited_data) > $maxSerializeLenCheck) {
                    $serial_err          = true;
                    $substrData          = substr($edited_data, 0, Log::isLevel(Log::LV_DEBUG) ? 20000 : 1000);
                    $rowErrors[$column]  = 'ENGINE: serialize data too big to convert; data len:' . strlen($edited_data) . ' Max size:' . $maxSerializeLenCheck;
                    $rowErrors[$column] .= "\n\tDATA: " . $substrData;
                    continue;
                }

                //Replace logic - level 1: simple check on any string or serlized strings
                if ($tColExactMatch) {
                    // if is exact match search and replace the itentical string
                    if (($rIndex = array_search($edited_data, $tColSearchList)) !== false) {
                        Log::info("ColExactMatch " . $column . ' search:' . $edited_data . ' replace:' . $tColreplaceList[$rIndex] . ' index:' . $rIndex, Log::LV_DEBUG);
                        $edited_data = $tColreplaceList[$rIndex];
                    }
                    continue;
                }

                // SEARCH AND REPLACE
                $edited_data = self::searchAndReplaceItems($tColSearchList, $tColreplaceList, $edited_data);

                // Replace logic - level 2: repair serialized strings that have become broken
                // check value without unserialize it
                if (self::is_serialized_string($edited_data)) {
                    $serial_check = self::fixSerializedAndCheck($edited_data);
                    if ($serial_check['fixed']) {
                        $edited_data = $serial_check['data'];
                    } else {
                        $substrData = substr($edited_data, 0, Log::isLevel(Log::LV_DEBUG) ? 20000 : 1000);
                        $message    = 'ENGINE: serialize data serial check error' .
                            "\n\tDATA: " . $substrData;

                        Log::info($message);
                        $serial_err         = true;
                        $rowErrors[$column] = $message;
                    }
                }
            }

            //Base 64 encode
            if ($base64converted) {
                $edited_data = base64_encode($edited_data);
            }

            //Change was made
            if ($serial_err == false && $edited_data != $originalData) {
                $s3Funcs->report['updt_cells']++;
                $upd_col[] = $safe_column;
                $upd_sql[] = $safe_column . ' = "' . mysqli_real_escape_string($dbh, $edited_data) . '"';
                $upd       = true;
            }

            if ($primary_key) {
                $where_sql[] = $safe_column . ' = "' . mysqli_real_escape_string($dbh, $originalData) . '"';
            }
        }

        foreach ($rowErrors as $errCol => $msgCol) {
            $longMsg                     = $msgCol . "\n\tTABLE:" . $rowsParams['table'] . ' COLUMN: ' . $errCol . ' WHERE: ' . implode(' AND ', array_filter($where_sql));
            $s3Funcs->report['errser'][] = $longMsg;
            $nManager->addFinalReportNotice(array(
                'shortMsg'    => 'DATA-REPLACE ERROR: Serialization',
                'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'     => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                'sections'    => 'search_replace'
            ));
        }

        //PERFORM ROW UPDATE
        if ($upd && !empty($where_sql)) {
            $sql    = "UPDATE `{$rowsParams['table']}` SET " . implode(', ', $upd_sql) . ' WHERE ' . implode(' AND ', array_filter($where_sql));
            $result = DUPX_DB::mysqli_query($dbh, $sql);

            if ($result) {
                $s3Funcs->report['updt_rows']++;
                $rowsParams['updated'] = true;
            } else {
                $errMsg                      = mysqli_error($dbh) . "\n\tTABLE:" . $rowsParams['table'] . ' WHERE: ' . implode(' AND ', array_filter($where_sql));
                $s3Funcs->report['errsql'][] = 'DB ERROR: ' . $errMsg . (Log::isLevel(Log::LV_DETAILED) ? "\nSQL: [{$sql}]\n" : '');
                $nManager->addFinalReportNotice(array(
                    'shortMsg'    => 'DATA-REPLACE ERRORS: MySQL',
                    'level'       => DUPX_NOTICE_ITEM::SOFT_WARNING,
                    'longMsg'     => $errMsg,
                    'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                    'sections'    => 'search_replace'
                ));
            }
        } elseif ($upd) {
            $errMsg                      = sprintf("Row [%s] on Table [%s] requires a manual update.", $rowsParams['current_row'], $rowsParams['table']);
            $s3Funcs->report['errkey'][] = $errMsg;

            $nManager->addFinalReportNotice(array(
                'shortMsg' => 'DATA-REPLACE ERROR: Key',
                'level'    => DUPX_NOTICE_ITEM::SOFT_WARNING,
                'longMsg'  => $errMsg,
                'sections' => 'search_replace'
            ));
        }

        return $rowsParams['updated'];
    }

    private static function getColumnsSearchReplaceList($table, $columns)
    {
        // PREPARE SEARCH AN REPLACE LISF FOR TABLES
        $srManager   = DUPX_S_R_MANAGER::getInstance();
        $searchList  = array();
        $replaceList = array();
        $list        = $srManager->getSearchReplaceList($table);
        foreach ($list as $item) {
            $searchList[]  = $item['search'];
            $replaceList[] = $item['replace'];
        }

        $columnsSRList = array();
        foreach ($columns as $column => $primary_key) {
            if (($cScope = self::getSearchReplaceCustomScope($table, $column)) === false) {
                // if don't have custom scope get normal search and reaplce table list
                $columnsSRList[$column] = array(
                    'list'       => &$list,
                    'sList'      => &$searchList,
                    'rList'      => &$replaceList,
                    'exactMatch' => false
                );
            } else {
                // if column have custom scope overvrite default table search/replace list
                $columnsSRList[$column] = array(
                    'list'       => $srManager->getSearchReplaceList($cScope, true, false),
                    'sList'      => array(),
                    'rList'      => array(),
                    'exactMatch' => self::isExactMatch($table, $column)
                );
                foreach ($columnsSRList[$column]['list'] as $item) {
                    $columnsSRList[$column]['sList'][] = $item['search'];
                    $columnsSRList[$column]['rList'][] = $item['replace'];
                }
            }
        }

        return $columnsSRList;
    }

    /**
     * searches and replaces strings without deserializing
     * recursion for arrays
     *
     * @param array $search
     * @param array $replace
     * @param mixed $data
     *
     * @return mixed
     */
    public static function searchAndReplaceItems($search, $replace, $data)
    {
        if (empty($data) || is_numeric($data) || is_bool($data) || is_callable($data)) {
            /* do nothing */
        } elseif (is_string($data)) {
            foreach ($search as $index => $cs) {
                //  Multiple replace string. If the string is serialized will fixed with fixSerialString
                $data = preg_replace($cs, $replace[$index], $data);
            }
        } elseif (is_array($data)) {
            $_tmp = array();
            foreach ($data as $key => $value) {
                // prevent recursion overhead
                if (empty($value) || is_numeric($value) || is_bool($value) || is_callable($value) || is_object($data)) {
                    $_tmp[$key] = $value;
                } else {
                    $_tmp[$key] = self::searchAndReplaceItems($search, $replace, $value, false);
                }
            }

            $data = $_tmp;
            unset($_tmp);
        } elseif (is_object($data)) {
            // it can never be an object type
            Log::info("OBJECT DATA IMPOSSIBLE\n");
        }
        return $data;
    }

    /**
     * FROM WORDPRESS
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @since 2.0.5
     *
     * @param string $data   Value to check to see if was serialized.
     * @param bool   $strict Optional. Whether to be strict about the end of the string. Default true.
     *
     * @return bool False if not serialized and true if it was.
     */
    public static function is_serialized_string($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace     = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }

    /**
     * Test if a string in properly serialized
     *
     * @param string $data Any string type
     *
     * @todo The serialized string fix handles layers recursively in case there is a serialization of a serialized string.
     *       As an example if an array is serialized that contains to its turn the list of serialized strings.
     *       For this reason in order to make a working test in all the cases we should recursively test all the values of the serialized object.
     *       Note: since the fix works well I'm fine with running a superficial test like this given the implementation effort to improve it.
     *
     * @return bool Is the string a serialized string
     */
    public static function unserializeTest($data)
    {
        if (!is_string($data)) {
            return false;
        } elseif ($data === 'b:0;') {
            return true;
        } else {
            try {
                LogHandler::setMode(LogHandler::MODE_OFF);
                $unserialize_ret = @unserialize($data);
                LogHandler::setMode();
                return ($unserialize_ret !== false);
            } catch (Exception $e) {
                Log::info("Unserialize exception: " . $e->getMessage());
                //DEBUG ONLY:
                Log::info("Serialized data\n" . $data, Log::LV_DEBUG);
                return false;
            }
        }
    }
    /**
     * custom columns list
     * if the table / column pair exists in this array then the search scope will be overwritten with that contained in the array
     *
     * @var array
     */
    private static $customScopes = array(
        'signups' => array(
            'domain' => array(
                'scope' => 'domain_host',
                'exact' => true
            ),
            'path'   => array(
                'scope' => 'domain_path',
                'exact' => true
            )
        ),
        'site'    => array(
            'domain' => array(
                'scope' => 'domain_host',
                'exact' => true
            ),
            'path'   => array(
                'scope' => 'domain_path',
                'exact' => true
            )
        )
    );

    /**
     *
     * @param string $table
     * @param string $column
     *
     * @return boolean|string  false if custom scope not found or return custom scoper for table/column
     */
    private static function getSearchReplaceCustomScope($table, $column)
    {
        $tablePrefix = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);
        if (strpos($table, $tablePrefix) !== 0) {
            return false;
        }

        $table_key = substr($table, strlen($tablePrefix));

        if (!array_key_exists($table_key, self::$customScopes)) {
            return false;
        }

        if (!array_key_exists($column, self::$customScopes[$table_key])) {
            return false;
        }

        return self::$customScopes[$table_key][$column]['scope'];
    }

    /**
     *
     * @param string $table
     * @param string $column
     *
     * @return boolean if true search a exact match in column if false search as LIKE
     */
    private static function isExactMatch($table, $column)
    {
        $tablePrefix = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);

        if (strpos($table, $tablePrefix) !== 0) {
            return false;
        }

        $table_key = substr($table, strlen($tablePrefix));

        if (!array_key_exists($table_key, self::$customScopes)) {
            return false;
        }

        if (!array_key_exists($column, self::$customScopes[$table_key])) {
            return false;
        }

        return self::$customScopes[$table_key][$column]['exact'];
    }

    /**
     *  Fixes the string length of a string object that has been serialized but the length is broken
     *
     * @param string $data The string object to recalculate the size on.
     *
     * @return string  A serialized string that fixes and string length types
     */
    private static function fixSerializedAndCheck($data)
    {
        $result = array(
            'data'  => null,
            'fixed' => false,
        );

        $serialized_fixed = self::recursiveFixSerialString($data);
        if (self::unserializeTest($serialized_fixed)) {
            $result['data']  = $serialized_fixed;
            $result['fixed'] = true;
        } else {
            $result['fixed'] = false;
        }

        return $result;
    }

    /**
     *  Fixes the string length of a string object that has been serialized but the length is broken
     *  Work on nested serialized string recursively.
     *
     *  @param string $data The string ojbect to recalculate the size on.
     *
     *  @return string  A serialized string that fixes and string length types
     */
    private static function recursiveFixSerialString($data)
    {

        if (!self::is_serialized_string($data)) {
            return $data;
        }

        $result  = '';
        $matches = null;

        $openLevel     = 0;
        $openContent   = '';
        $openContentL2 = '';

        // parse every char
        for ($i = 0; $i < strlen($data); $i++) {
            $cChar = $data[$i];

            $addChar = true;

            if ($cChar == 's') {
                // test if is a open string
                if (preg_match(self::SERIALIZE_OPEN_STR_REGEX, substr($data, $i, self::SERIALIZE_OPEN_SUBSTR_LEN), $matches)) {
                    if ($openLevel > 1) {
                        $openContentL2 .= $matches[0];
                    }

                    $addChar = false;

                    $openLevel++;

                    $i += strlen($matches[0]) - 1;
                }
            } elseif ($openLevel > 0 && $cChar == '"') {
                // test if is a close string
                if (preg_match(self::SERIALIZE_CLOSE_STR_REGEX, substr($data, $i, self::SERIALIZE_CLOSE_SUBSTR_LEN))) {
                    $addChar = false;

                    switch ($openLevel) {
                        case 1:
                            // level 1
                            // flush string content
                            $result     .= 's:' . strlen($openContent) . ':"' . $openContent . '";';
                            $openContent = '';
                            break;
                        case 2:
                            // level 2
                            // fix serial string level2
                            $sublevelstr = self::recursiveFixSerialString($openContentL2);
                            // flush content on level 1
                            $openContent  .= 's:' . strlen($sublevelstr) . ':"' . $sublevelstr . '";';
                            $openContentL2 = '';
                            break;
                        default:
                            // level > 2
                            // keep writing at level 2; it will be corrected with recursion
                            $openContentL2 .= self::SERIALIZE_CLOSE_STR;
                            break;
                    }

                    $openLevel--;
                    $i += self::SERIALIZE_CLOSE_STR_LEN - 1;
                }
            }

            if ($addChar) {
                switch ($openLevel) {
                    case 0:
                        // level 0
                        // add char on result
                        $result .= $cChar;

                        break;
                    case 1:
                        // level 1
                        // add char on content level1
                        $openContent .= $cChar;

                        break;
                    default:
                        // level > 1
                        // add char on content level2
                        $openContentL2 .= $cChar;

                        break;
                }
            }
        }

        return $result;
    }

    /**
     *
     * @param object $dbh
     * @param string $table
     * @param string $column
     * @param string $oldPrefix
     * @param string $newPrefix
     *
     * @return boolean
     */
    public static function updateTablePrefix($dbh, $table, $column, $oldPrefix, $newPrefix)
    {
        if ($oldPrefix === $newPrefix) {
            return true;
        }

        Log::info('UPDATE KEYS PREFIX ON ' . $table . ' COLUMN ' . $column . ' FROM ' . $oldPrefix . ' TO ' . $newPrefix);

        $lenOldPrefix          = strlen($oldPrefix) + 1;
        $regexOldTablePrefix   = mysqli_real_escape_string($dbh, SnapDB::quoteRegex($oldPrefix));
        $escapedNewTablePrefix = mysqli_real_escape_string($dbh, $newPrefix);

        // remove old prefix values if exists
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $column . ' IN ('
            . 'SELECT CONCAT(\'' . $escapedNewTablePrefix . '\',SUBSTRING(' . $column . ' , ' . $lenOldPrefix . ')) '
            . 'FROM (SELECT * FROM ' . $table . ' WHERE `' . $column . '` REGEXP \'^' . $regexOldTablePrefix . '\') selectTableAlias'
            . ')';

        if (DUPX_DB::mysqli_query($dbh, $sql) === false) {
            $nManager = DUPX_NOTICE_MANAGER::getInstance();
            $s3Funcs  = DUPX_S3_Funcs::getInstance();
            $errMsg   = 'Query error on table prefix user meta ' . "\n\t" . mysqli_error($dbh);

            $s3Funcs->report['errsql'][] = 'DB ERROR: ' . $errMsg . (Log::isLevel(Log::LV_DETAILED) ? "\nSQL: [{$sql}]\n" : '');
            $nManager->addFinalReportNotice(array(
                'shortMsg'    => 'UPDATE PREFIX TABLE ERROR',
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsg'     => $errMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                'sections'    => 'search_replace'
            ));
            return false;
        }

        /// rename table prefix value
        $sql = 'UPDATE ' . $table . ' SET ' . $column . ' = CONCAT(\'' . $escapedNewTablePrefix . '\',SUBSTRING(' . $column . ' , ' . $lenOldPrefix . ')) WHERE `' . $column . '` REGEXP \'^' . $regexOldTablePrefix . '\'';
        if (DUPX_DB::mysqli_query($dbh, $sql) === false) {
            $nManager = DUPX_NOTICE_MANAGER::getInstance();
            $s3Funcs  = DUPX_S3_Funcs::getInstance();
            $errMsg   = 'Query error on table prefix user meta ' . "\n\t" . mysqli_error($dbh);

            $s3Funcs->report['errsql'][] = 'DB ERROR: ' . $errMsg . (Log::isLevel(Log::LV_DETAILED) ? "\nSQL: [{$sql}]\n" : '');
            $nManager->addFinalReportNotice(array(
                'shortMsg'    => 'UPDATE PREFIX TABLE ERROR',
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsg'     => $errMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                'sections'    => 'search_replace'
            ));
            return false;
        }
        return true;
    }

    public static function updateTablePrefixKeys()
    {
        if (!DUPX_ArchiveConfig::getInstance()->isTablePrefixChanged()) {
            return true;
        }

        Log::info("\nUPDATE PREFIX KEY TABLES");

        $nManager      = DUPX_NOTICE_MANAGER::getInstance();
        $s3Funcs       = DUPX_S3_Funcs::getInstance();
        $paramsManager = PrmMng::getInstance();
        $archiveConfig = DUPX_ArchiveConfig::getInstance();

        $dbh = $s3Funcs->getDbConnection();

        $oldPrefix    = DUPX_ArchiveConfig::getInstance()->wp_tableprefix;
        $newPrefix    = $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX);
        $optionsTable = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getOptionsTableName());

        $tables = DUPX_DB_Tables::getInstance()->getNewTablesNames();
        if (!is_array($tables)) {
            $nManager->addFinalReportNotice(array(
                'shortMsg'    => 'CAN\'T FIND ANY TABLE IN DATABASE',
                'level'       => DUPX_NOTICE_ITEM::CRITICAL,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
                'sections'    => 'search_replace'
            ));
            return false;
        }

        if (in_array($optionsTable, $tables)) {
            Log::info('UPDATE PREFIX IN TABLE ' . Log::v2str($optionsTable) . ' FROM ' . $oldPrefix . ' TO ' . $newPrefix);
            self::updateTablePrefix($dbh, $optionsTable, 'option_name', $oldPrefix, $newPrefix);
        } else {
            Log::info('CAN\'T UPDATE PREFIX IN TABLE ' . Log::v2str($optionsTable) . ' BACAUSE TABLE NOT IN LIST', Log::LV_DETAILED);
        }

        $usermetaTable = DUPX_DB_Functions::getUserMetaTableName();
        if (in_array($usermetaTable, $tables)) {
            Log::info('UPDATE PREFIX IN TABLE ' . Log::v2str($usermetaTable) . ' FROM ' . $oldPrefix . ' TO ' . $newPrefix);
            self::updateTablePrefix($dbh, $usermetaTable, 'meta_key', $oldPrefix, $newPrefix);
        } else {
            Log::info('CAN\'T UPDATE PREFIX IN TABLE ' . Log::v2str($usermetaTable) . ' BACAUSE TABLE NOT IN LIST', Log::LV_DETAILED);
        }

        return true;
    }
}
