<?php

/**
 * database table item descriptor
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\Descriptors\ParamDescUsers;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapWP;

/**
 * This class manages the installer table, all table management refers to the table name in the original site.
 */
class DUPX_DB_Table_item
{
    protected $originalName       = '';
    protected $tableWithoutPrefix = '';
    protected $rows               = 0;
    protected $size               = 0;
    protected $havePrefix         = false;
    protected $subsiteId          = -1;
    protected $subsitePrefix      = '';

    /**
     *
     * @param string $name
     * @param int $rows
     * @param int $size
     */
    public function __construct($name, $rows = 0, $size = 0)
    {
        if (strlen($this->originalName = $name) == 0) {
            throw new Exception('The table name can\'t be empty.');
        }

        $this->rows = max(0, (int) $rows);
        $this->size = max(0, (int) $size);

        $oldPrefix = DUPX_ArchiveConfig::getInstance()->wp_tableprefix;
        if (strlen($oldPrefix) === 0) {
            $this->havePrefix         = true;
            $this->tableWithoutPrefix = $this->originalName;
        } if (strpos($this->originalName, $oldPrefix) === 0) {
            $this->havePrefix         = true;
            $this->tableWithoutPrefix = substr($this->originalName, strlen($oldPrefix));
        } else {
            $this->havePrefix         = false;
            $this->tableWithoutPrefix = $this->originalName;
        }

        $this->subsiteId     = 1;
        $this->subsitePrefix = $oldPrefix;
    }

    /**
     * return the original talbe name in source site
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * return table name without prefix, if the table has no prefix then the original name returns.
     *
     * @return string
     */
    public function getNameWithoutPrefix($includeSubsiteId = false)
    {
        return (($includeSubsiteId && $this->subsiteId > 1) ? $this->subsiteId . '_' : '') . $this->tableWithoutPrefix;
    }

    /**
     *
     * @param array $diffData
     *
     * @return boolean
     */
    public function isDiffPrefix(&$diffData)
    {
        $oldPos = strlen(($oldName = $this->getOriginalName()));
        $newPos = strlen(($newName = $this->getNewName()));

        if ($oldName == $newName) {
            $diffData = array(
                'oldPrefix'  => '',
                'newPrefix'  => '',
                'commonPart' => $oldName
            );
            return false;
        }

        while ($oldPos > 0 && $newPos > 0) {
            if ($oldName[$oldPos - 1] != $newName[$newPos - 1]) {
                break;
            }

            $oldPos--;
            $newPos--;
        }

        $diffData = array(
            'oldPrefix'  => substr($oldName, 0, $oldPos),
            'newPrefix'  => substr($newName, 0, $newPos),
            'commonPart' => substr($oldName, $oldPos)
        );
        return true;
    }

    /**
     *
     * @return bool
     */
    public function havePrefix()
    {
        return $this->havePrefix;
    }

    /**
     * return new name extracted on target site
     *
     * @return string
     */
    public function getNewName()
    {
        if (!$this->canBeExctracted()) {
            return '';
        }

        if (!$this->havePrefix) {
            return $this->originalName;
        }

        $paramsManager = PrmMng::getInstance();

        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
                return $paramsManager->getValue(PrmMng::PARAM_DB_TABLE_PREFIX) . $this->getNameWithoutPrefix(true);
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                throw new Exception('Mode not avaiable');
            case DUPX_InstallerState::INSTALL_NOT_SET:
                throw new Exception('Cannot change setup with current installation type [' . DUPX_InstallerState::getInstType() . ']');
            default:
                throw new Exception('Unknown mode');
        }
    }

    /**
     *
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     *
     * @param type $formatted
     *
     * @return int|string
     */
    public function getSize($formatted = false)
    {
        return $formatted ? DUPX_U::readableByteSize($this->size) : $this->size;
    }

    /**
     *
     * @return int // if -1 isn't a subsite sable
     */
    public function getSubsisteId()
    {
        return $this->subsiteId;
    }

    /**
     *
     * @return boolean
     */
    public function canBeExctracted()
    {
        return true;
    }

    /**
     * If false the current table create query is skipped
     *
     * @return boolran
     */
    public function createTable()
    {
        if ($this->usersTablesCreateCheck() === false) {
            return false;
        }

        return true;
    }

    /**
     * Check if create users table
     *
     * @return boolran
     */
    protected function usersTablesCreateCheck()
    {
        if (!$this->isUserTable()) {
            return true;
        }

        return (ParamDescUsers::getUsersMode() !== ParamDescUsers::USER_MODE_IMPORT_USERS);
    }

    /**
     * Return true if current table is user or usermeta table
     *
     * @return boolean
     */
    public function isUserTable()
    {
        return ($this->havePrefix && in_array($this->tableWithoutPrefix, array('users', 'usermeta')));
    }

    /**
     * returns true if the table is to be extracted
     *
     * @return boolean
     */
    public function extract()
    {
        if (!$this->canBeExctracted()) {
            return false;
        }

        $tablesVals = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLES);
        if (!isset($tablesVals[$this->originalName])) {
            throw new Exception('Table ' . $this->originalName . ' not in table vals');
        }

        return $tablesVals[$this->originalName]['extract'];
    }

    /**
     * returns true if a search and replace is to be performed
     *
     * @return boolean
     */
    public function replaceEngine()
    {
        if (!$this->extract()) {
            return false;
        }

        $tablesVals = PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_TABLES);
        if (!isset($tablesVals[$this->originalName])) {
            throw new Exception('Table ' . $this->originalName . ' not in table vals');
        }

        return $tablesVals[$this->originalName]['replace'];
    }
}
