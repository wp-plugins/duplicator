<?php

namespace Duplicator\Installer\Core\Deploy\Database;

use Duplicator\Installer\Core\Params\Descriptors\ParamDescUsers;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Models\ImportUser;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\JsonSerialize\AbstractJsonSerializable;
use Duplicator\Libs\Snap\SnapDB;
use Duplicator\Libs\Snap\SnapUtil;
use DUPX_ArchiveConfig;
use DUPX_DB;
use DUPX_DB_Functions;
use DUPX_DB_Tables;
use DUPX_InstallerState;
use DUPX_NOTICE_ITEM;
use DUPX_NOTICE_MANAGER;
use DUPX_Security;
use DUPX_UpdateEngine;
use Error;
use Exception;

class DbUserMode extends AbstractJsonSerializable
{
    /** @var ImportUser[] */
    protected $targetUsersById = array();
    /** @var ImportUser[] */
    protected $targetUsersByMail = array();
    /** @var ImportUser[] */
    protected $targetUsersByLogin = array();
    /** @var int */
    protected $usersAutoIncrement = -1;
    /** @var int */
    protected $usersMetaAutoIncrement = -1;
    /** @var bool[] */
    protected $addedUsers = array();
    /** @var int[] */
    protected $mappingIds = array();
    /** @var string[] */
    protected $existingsMetaIsd = array();
    /** @var int */
    protected $userTableNumCols = 0;
    /** @var string */
    protected $userMode = ParamDescUsers::USER_MODE_OVERWRITE;
    /** @var string */
    protected $prefixMetaRegexCheck = '';
    /** @var array */
    protected $prefixMetaMapping = array();

    /**
     * Class contructor
     */
    public function __construct()
    {
        $prmMng                     = PrmMng::getInstance();
        $this->userMode             = ParamDescUsers::getUsersMode();
        $this->prefixMetaRegexCheck = '/^' . preg_quote(DUPX_ArchiveConfig::getInstance()->wp_tableprefix, '/') . '(?:(\d+)_)?(.*)$/';

        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                $this->addPrefixMetaMapping(
                    0,
                    $prmMng->getValue(PrmMng::PARAM_DB_TABLE_PREFIX)
                );
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                throw new Exception('Invalid mode');
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
                break;
            case DUPX_InstallerState::INSTALL_NOT_SET:
                throw new Exception('Cannot change setup with current installation type [' . DUPX_InstallerState::getInstType() . ']');
            default:
                throw new Exception('Unknown mode');
        }
    }

    /**
     * Add meta prefix meta mapping
     *
     * @param int    $subsiteId subsite id
     * @param string $prefix    replace value
     *
     * @return void
     */
    protected function addPrefixMetaMapping($subsiteId, $prefix)
    {
        Log::info('ADD PREFIX META MAP ID ' . $subsiteId . ' ' . $prefix);
        $key                           = ($subsiteId == 1 ? 0 : $subsiteId);
        $this->prefixMetaMapping[$key] = $prefix;
    }

    /**
     * This function renames the user tables of the target site, also updates the user meta keys
     *
     * @return void
     */
    public static function moveTargetUserTablesOnCurrentPrefix()
    {
        $paramsManager = PrmMng::getInstance();
        if (ParamDescUsers::getUsersMode() === ParamDescUsers::USER_MODE_OVERWRITE) {
            return;
        }

        Log::info("\nKEEP TARGET SITE USERS TABLES - USER MODE " . ParamDescUsers::getUsersMode());

        $dbFunc        = DUPX_DB_Functions::getInstance();
        $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        if ($overwriteData['table_prefix'] == $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX)) {
            Log::info('TABLE NAMES ARE THE SAME, SO SKIP USERS TABLES RENAME');
            return;
        }

        $targetUserTable          = DUPX_DB_Functions::getUserTableName($overwriteData['table_prefix']);
        $targetUserMetaTable      = DUPX_DB_Functions::getUserMetaTableName($overwriteData['table_prefix']);
        $currentUserTableName     = DUPX_DB_Functions::getUserTableName();
        $currentUserMetaTableName = DUPX_DB_Functions::getUserMetaTableName();

        $dbFunc->renameTable($targetUserTable, $currentUserTableName, true);
        $dbFunc->renameTable($targetUserMetaTable, $currentUserMetaTableName, true);

        // Update table prefix on meta key
        DUPX_UpdateEngine::updateTablePrefix(
            $dbFunc->dbConnection(),
            $currentUserMetaTableName,
            'meta_key',
            $overwriteData['table_prefix'],
            $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX)
        );
        Log::info("USER TABLES RENAMED");
    }

    /**
     * This function removes all meta keys of the current prefix in the usermeta table.
     * This is needed to replace them with the meta keys that will be imported
     *
     * @return void
     */
    public function removeAllUserMetaKeysOfCurrentPrefix()
    {
        $paramsManager = PrmMng::getInstance();
        if (
            ParamDescUsers::getUsersMode() !== ParamDescUsers::USER_MODE_IMPORT_USERS
        ) {
            return;
        }
        $dbh           = DUPX_DB_Functions::getInstance()->dbConnection();
        $overwriteData = $paramsManager->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);

        $loggedInUserId = (int) $overwriteData['loggedUser']['id'];

        foreach ($this->prefixMetaMapping as $overwriteId => $prefix) {
            $where         = 'user_id != ' . $loggedInUserId;
            $escPergPrefix = mysqli_real_escape_string($dbh, SnapDB::quoteRegex($prefix));

            if ($prefix == $overwriteData['table_prefix']) {
                Log::info("\nREMOVE EXISTING USER META KEY WITH PREFIX  " . $prefix . ' EXCEPT ' . $prefix . '[0-9]+_');
                // SELECT * FROM `prefix_usermeta` WHERE user_id != 2 AND meta_key REGEXP "^prefix_" AND meta_key NOT REGEXP "^prefix_[0-9]+_"
                $where .= ' AND meta_key REGEXP "^' . $escPergPrefix . '" AND meta_key NOT REGEXP "^' . $escPergPrefix . '[0-9]+_"';
            } else {
                Log::info("\nREMOVE EXISTING USER META KEY WITH PREFIX  " . $prefix);
                // SELECT * FROM `prefix_usermeta` WHERE user_id != 2 AND meta_key REGEXP "^prefix_2_"
                $where .= ' AND meta_key REGEXP "^' . $escPergPrefix . '"';
            }

            DUPX_DB::chunksDelete($dbh, DUPX_DB_Functions::getUserMetaTableName(), $where);
        }
    }

    /**
     * Filter props on json encode
     *
     * @return strng[]
     */
    public function __sleep()
    {
        $props = array_keys(get_object_vars($this));
        return array_diff($props, array('targetUsersByMail', 'targetUsersByLogin'));
    }

    /**
     * Called after json decode
     *
     * @return void
     */
    public function __wakeup()
    {
        foreach ($this->targetUsersById as $user) {
            $this->targetUsersByMail[$user->getMail()]   = $user;
            $this->targetUsersByLogin[$user->getLogin()] = $user;
        }
    }

    /**
     * Return the list of columns that contain user id to remap in an array( table => numberColumn)
     *
     * @return int[]
     */
    protected static function getTableColIdsToRemap()
    {
        static $remapTables = null;
        if (is_null($remapTables)) {
            $remapTables = array();

            foreach (DUPX_DB_Tables::getInstance()->getTablesByNameWithoutPrefix('posts') as $table) {
                $remapTables[$table] = 1;
            }

            Log::info('REMAP USERS TABLES/COLUMN ' . Log::v2str($remapTables));
        }
        return $remapTables;
    }

    /**
     * Load from users table the user list
     *
     * @return void
     */
    public function initTargetSiteUsersData()
    {
        if ($this->userMode !== ParamDescUsers::USER_MODE_IMPORT_USERS) {
            return;
        }
        $dbh = DUPX_DB_Functions::getInstance()->dbConnection();
        Log::info('INIT IMPORT TARGET USER TABLE DATA');

        $dbName    = mysqli_real_escape_string($dbh, PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_NAME));
        $userTable = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getUserTableName());

        // count num cols of user table, can be different from source to target
        $query = 'SELECT count(*) AS num_cols FROM information_schema.columns WHERE table_schema = "' . $dbName . '" AND table_name = "' . $userTable . '"';
        if (($queryRes = DUPX_DB::mysqli_query($dbh, $query)) === false) {
            $err = mysqli_error($dbh);
            throw new Exception('Query error: ' . $err);
        }
        $row                    = $queryRes->fetch_array();
        $this->userTableNumCols = (int) $row[0];
        Log::info('USER TABLE COLUMNS COUNT ' . $this->userTableNumCols, Log::LV_DETAILED);

        $query = 'SELECT `ID`,`user_login`,`user_email` FROM `' . $userTable . '`';
        if (($queryRes = DUPX_DB::mysqli_query($dbh, $query)) === false) {
            $err = mysqli_error($dbh);
            throw new Exception('Query error: ' . $err);
        }

        $this->usersAutoIncrement = -1;
        while ($row = $queryRes->fetch_assoc()) {
            $rowId = (int) $row['ID'];
            $user  = new ImportUser($rowId, $row['user_login'], $row['user_email']);

            $this->targetUsersById[$user->getId()]       = $user;
            $this->targetUsersByMail[$user->getMail()]   = $user;
            $this->targetUsersByLogin[$user->getLogin()] = $user;

            if ($rowId > $this->usersAutoIncrement) {
                $this->usersAutoIncrement = $rowId;
            }
        }
        $this->usersAutoIncrement ++;
        $queryRes->free_result();
        Log::info('EXISTING USERS COUNT ' . count($this->targetUsersById), Log::LV_DETAILED);
        Log::info('USERS TABLE AUTOINCREMENT VALUE ' . $this->usersAutoIncrement, Log::LV_DETAILED);

        $this->initTargetSiteUserMetaData();
    }

    /**
     * For each existing meta key, a list of IDs is associated with each user who has that key or true if all users have the key
     *
     * @return void
     */
    protected function initTargetSiteUserMetaData()
    {
        $dbh = DUPX_DB_Functions::getInstance()->dbConnection();
        Log::info('INIT IMPORT TARGET USERMETA TABLE DATA');

        $userTable     = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getUserTableName());
        $userMetaTable = mysqli_real_escape_string($dbh, DUPX_DB_Functions::getUserMetaTableName());

        $query = 'SELECT max(umeta_id) AS maxId FROM `' . $userMetaTable . '`';
        if (($queryRes = DUPX_DB::mysqli_query($dbh, $query)) === false) {
            $err = mysqli_error($dbh);
            throw new Exception('Query error: ' . $err);
        }
        $row                          = $queryRes->fetch_assoc();
        $this->usersMetaAutoIncrement = ((int) $row['maxId']) + 1;
        $queryRes->free_result();
        Log::info('USERMETA TABLE AUTOINCREMENT VALUE ' . $this->usersAutoIncrement, Log::LV_DETAILED);

        $query = 'SELECT COUNT(*) FROM `' . $userTable . '`';
        if (($queryRes = DUPX_DB::mysqli_query($dbh, $query)) === false) {
            $err = mysqli_error($dbh);
            throw new Exception('Query error: ' . $err);
        }
        $row       = $queryRes->fetch_array();
        $maxNumIds = $row[0];

        $query = 'SELECT `meta_key`, IF(COUNT(`user_id`) >= ' . $maxNumIds . ', "ALL", GROUP_CONCAT(`user_id` ORDER BY `user_id` ASC)) AS IDS ' .
                'FROM `' . $userMetaTable . '`' .
                'WHERE `user_id` IN (SELECT `ID` FROM `' . $userTable . '`) GROUP BY meta_key';
        if (($queryRes = DUPX_DB::mysqli_query($dbh, $query)) === false) {
            $err = mysqli_error($dbh);
            throw new Exception('Query error: ' . $err);
        }

        while ($row = $queryRes->fetch_assoc()) {
            $this->existingsMetaIsd[$row['meta_key']] = (
            ($row['IDS'] === 'ALL') ?
                    true :
                    array_map('intval', explode(',', $row['IDS']))
            );
        }
        Log::info('NUM META KEYS READ ' . count($this->existingsMetaIsd), Log::LV_DETAILED);
        $queryRes->free_result();
    }

    /**
     * Apply inser query user fixes
     *
     * @param string $query query string
     *
     * @return string if the string is empty no query must be executed
     */
    public function applyUsersFixes(&$query)
    {
        if ($this->userMode == ParamDescUsers::USER_MODE_OVERWRITE) {
            return $query;
        }

        $matches = array();
        if (preg_match('/^\s*(?:\/\*.*\*\/|#.*\n|--.*\n)?\s*INSERT\s+INTO\s+`?([^\s`]*?)`?\s+VALUES/s', $query, $matches) !== 1) {
            return $query;
        }

        $tableName = SnapDB::parsedQueryValueToString($matches[1]);

        if ($this->userMode == ParamDescUsers::USER_MODE_IMPORT_USERS) {
            if ($tableName == DUPX_DB_Functions::getUserTableName()) {
                return $this->getUserTableQueryFix(SnapDB::getValuesFromQueryInsert($query));
            } elseif ($tableName == DUPX_DB_Functions::getUserMetaTableName()) {
                return $this->getUserMetaTableQueryFix(SnapDB::getValuesFromQueryInsert($query));
            }
        }

        $tablesColRemap = self::getTableColIdsToRemap();
        if (in_array($tableName, array_keys($tablesColRemap))) {
            return $this->getTableUserRemapQueryFix(
                $tableName,
                $tablesColRemap[$tableName],
                SnapDB::getValuesFromQueryInsert($query)
            );
        }
        return $query;
    }

    /**
     * Generate import final report
     *
     * @return void
     */
    public function generateImportReport()
    {
        if ($this->userMode !== ParamDescUsers::USER_MODE_IMPORT_USERS) {
            return;
        }

        $numAdded   = 0;
        $numChanged = 0;

        if (($fp = fopen(DUPX_INIT . '/' . self::getCsvReportName(), 'w')) === false) {
            Log::info('Can\'t open report file ' . DUPX_INIT . '/' . self::getCsvReportName());
        } else {
            fputcsv($fp, ImportUser::getArrayReportTitles());
        }

        foreach ($this->targetUsersById as $user) {
            if ($user->isAdded()) {
                $numAdded++;
            } elseif ($user->isChanged()) {
                $numChanged++;
            } else {
                continue;
            }
            if ($fp != false) {
                fputcsv($fp, $user->getArrayReport());
            }
        }

        if ($fp != false) {
            fclose($fp);
            $csvUrl = DUPX_INIT_URL . '/' . self::getCsvReportName();
        } else {
            $csvUrl = false;
        }

        $longMsg = dupxTplRender(
            'parts/reports/import_report',
            array(
                'numAdded' => $numAdded,
                'numChanged' => $numChanged,
                'csvUrl' => $csvUrl
            ),
            false
        );

        $nManager = DUPX_NOTICE_MANAGER::getInstance();
        $nManager->addFinalReportNotice(
            array(
                'shortMsg' => 'User import report',
                'level'    => DUPX_NOTICE_ITEM::NOTICE,
                'longMsg'  => $longMsg,
                'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
                'sections' => 'general'
            )
        );
        $nManager->saveNotices();
    }

    /**
     * Return csv report file name
     *
     * @return string
     */
    protected static function getCsvReportName()
    {
        return 'dup-installer-import-report__' . DUPX_Security::getInstance()->getSecondaryPackageHash() . '.csv';
    }

    /**
     * Apply query fix for user table
     *
     * @param array $queryValues two dimensional array where each item is a row containing the list of values
     *
     * @return string
     */
    protected function getUserTableQueryFix($queryValues)
    {
        $dbh              = DUPX_DB_Functions::getInstance()->dbConnection();
        $resultValues     = array();
        $numColsQueryVals = isset($queryValues[0]) ? count($queryValues[0]) : 0;
        $colsDeltaDiff    = $this->userTableNumCols - $numColsQueryVals;

        foreach ($queryValues as $rowVals) {
            $rowId    = SnapDB::parsedQueryValueToInt($rowVals[0]);
            $rowLogin = SnapDB::parsedQueryValueToString($rowVals[1]);
            $rowMail  = SnapDB::parsedQueryValueToString($rowVals[4]);

            if (isset($this->targetUsersByMail[$rowMail])) {
                $targetUser  = $this->targetUsersByMail[$rowMail];
                $targetId    = $targetUser->getId();
                $targetLogin = $targetUser->getLogin();

                if ($rowId != $targetId) {
                    $targetUser->setOldId($rowId);
                    $this->mappingIds[$rowId] = $targetId;
                }

                if ($rowLogin != $targetLogin) {
                    $targetUser->setOldLogin($rowLogin);
                }
            } else {
                $rowVals[0] = $targetId = $this->usersAutoIncrement;
                $this->usersAutoIncrement ++;

                if ($rowId != $targetId) {
                    $this->mappingIds[$rowId] = $targetId;
                }

                $newLogin     = $rowLogin;
                $postfixIndex = 0;
                while (isset($this->targetUsersByLogin[$newLogin])) {
                    $postfixIndex++;
                    $newLogin = $rowLogin . $postfixIndex;
                }
                if ($rowLogin != $newLogin) {
                    $rowVals[1] = '"' . mysqli_real_escape_string($dbh, $newLogin) . '"';

                    $niceName   = SnapDB::parsedQueryValueToString($rowVals[3]);
                    $rowVals[3] = '"' . mysqli_real_escape_string($dbh, $niceName) . $postfixIndex . '"';

                    $displayName = SnapDB::parsedQueryValueToString($rowVals[9]);
                    if ($rowLogin == $displayName) {
                        $rowVals[9] = '"' . mysqli_real_escape_string($dbh, $newLogin) . '"';
                    }
                }

                $user = new ImportUser($targetId, $newLogin, $rowMail, $rowId, $rowLogin, true);
                $this->targetUsersById[$user->getId()]       = $user;
                $this->targetUsersByMail[$user->getMail()]   = $user;
                $this->targetUsersByLogin[$user->getLogin()] = $user;
                $this->addedUsers[$user->getOldId()]         = true;

                if ($colsDeltaDiff == 0) {
                    $resultValues[] = $rowVals;
                } elseif ($colsDeltaDiff < 0) {
                    $resultValues[] = array_slice($rowVals, 0, $this->userTableNumCols);
                } else {
                    for ($i = 0; $i < $colsDeltaDiff; $i++) {
                        $rowVals[] = '"0"';
                    }
                    $resultValues[] = $rowVals;
                }
            }
        }

        if (empty($resultValues)) {
            return '';
        }

        return 'INSERT INTO `' . mysqli_real_escape_string($dbh, DUPX_DB_Functions::getUserTableName()) . '` ' .
            'VALUES ' . SnapDB::getQueryInsertValuesFromArray($resultValues) . ';';
    }

    /**
     * Apply query fix for usermeta table
     *
     * @param array $queryValues two dimensional array where each item is a row containing the list of values
     *
     * @return string
     */
    protected function getUserMetaTableQueryFix($queryValues)
    {
        $dbh          = DUPX_DB_Functions::getInstance()->dbConnection();
        $resultValues = array();

        // reset value
        $user = new ImportUser(-1, '', '');

        $prefixMatches = null;
        foreach ($queryValues as $rowVals) {
            try {
                $rowUserId  = SnapDB::parsedQueryValueToInt($rowVals[1]);
                $rowMetakey = SnapDB::parsedQueryValueToString($rowVals[2]);

                if ($user->getId() != $rowUserId) {
                    $userId = isset($this->mappingIds[$rowUserId]) ? $this->mappingIds[$rowUserId] : $rowUserId;
                    if (isset($this->targetUsersById[$userId])) {
                        $user = $this->targetUsersById[$userId];
                    } else {
                        // This happens if there is a meta key that has a user id that does not belong to any user, it is an anomalous thing so it is skipped.
                        continue;
                    }
                }

                if (preg_match($this->prefixMetaRegexCheck, $rowMetakey, $prefixMatches) === 1) {
                    $currentId = (int)$prefixMatches[1];
                    if (!isset($this->prefixMetaMapping[$currentId])) {
                        // if the attribute is not of the selected sub-site then it is not inserted in the import
                        continue;
                    }

                    $rowMetakey = $this->prefixMetaMapping[$currentId] . $prefixMatches[2];
                    $rowVals[2] = '"' . mysqli_real_escape_string($dbh, $rowMetakey) . '"';
                }

                if (
                    $user->isAdded() ||
                    !isset($this->existingsMetaIsd[$rowMetakey]) ||
                    (
                        $this->existingsMetaIsd[$rowMetakey] !== true &&
                        SnapUtil::binarySearch($this->existingsMetaIsd[$rowMetakey], $user->getId()) == false
                    )
                ) {
                    $rowVals[0] = $this->usersMetaAutoIncrement;
                    $this->usersMetaAutoIncrement ++;
                    $rowVals[1] = $user->getId();

                    if ($rowMetakey == 'nickname') {
                        // update nickname
                        $rowMetaValue = SnapDB::parsedQueryValueToString($rowVals[3]);
                        if ($rowMetaValue ==  $user->getOldLogin()) {
                            $rowVals[3] = '"' . mysqli_real_escape_string($dbh, $user->getLogin()) . '"';
                        }
                    }

                    $resultValues[] = $rowVals;
                }
            } catch (Exception $e) {
                Log::logException($e, 'Error on parse user meta row');
            } catch (Error $e) {
                Log::logException($e, 'Error on parse user meta row');
            }
        }

        if (empty($resultValues)) {
            return '';
        }

        return 'INSERT INTO `' . mysqli_real_escape_string($dbh, DUPX_DB_Functions::getUserMetaTableName()) .
            '` VALUES ' . SnapDB::getQueryInsertValuesFromArray($resultValues) . ';';
    }

    /**
     * Apply query fix for table/colum user id
     *
     * @param string $tableName   table name
     * @param string $colNum      column index, 0 is first
     * @param array  $queryValues two dimensional array where each item is a row containing the list of values
     *
     * @return void
     */
    protected function getTableUserRemapQueryFix($tableName, $colNum, $queryValues)
    {
        $dbh = DUPX_DB_Functions::getInstance()->dbConnection();

        for ($i = 0; $i < count($queryValues); $i++) {
            $rowUserId = SnapDB::parsedQueryValueToInt($queryValues[$i][$colNum]);
            if (isset($this->mappingIds[$rowUserId])) {
                $queryValues[$i][$colNum] = $this->mappingIds[$rowUserId];
            }
        }

        return 'INSERT INTO `' . mysqli_real_escape_string($dbh, $tableName) .
            '` VALUES ' . SnapDB::getQueryInsertValuesFromArray($queryValues) . ';';
    }
}
