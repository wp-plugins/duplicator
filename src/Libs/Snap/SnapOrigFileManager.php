<?php

/**
 *
 * @package Duplicator
 * @copyright (c) 2021, Snapcreek LLC
 *
 */

namespace Duplicator\Libs\Snap;

/**
 * Original installer files manager
 *
 * This class saves a file or folder in the original files folder and saves the original location persistent.
 * By entry we mean a file or a folder but not the files contained within it.
 * In this way it is possible, for example, to move an entire plugin to restore it later.
 *
 */
class SnapOrigFileManager
{
    const MODE_MOVE             = 'move';
    const MODE_COPY             = 'copy';
    const ORIG_FOLDER_PREFIX    = 'original_files_';
    const PERSISTANCE_FILE_NAME = 'entries_stored.json';

    /**
     *
     * @var string
     */
    protected $persistanceFile = null;

    /**
     *
     * @var string
     */
    protected $origFilesFolder = null;

    /**
     *
     * @var array
     */
    protected $origFolderEntries = array();

    /**
     *
     * @var string
     */
    protected $rootPath = null;

    /**
     * Class constructor
     *
     * @param string $root                 wordpress root path
     * @param string $origFolderParentPath orig files folder path
     * @param string $hash                 package hash
     */
    public function __construct($root, $origFolderParentPath, $hash)
    {
        $this->rootPath        = SnapIO::safePathUntrailingslashit($root, true);
        $this->origFilesFolder = SnapIO::safePathTrailingslashit($origFolderParentPath, true) . self::ORIG_FOLDER_PREFIX . $hash;
        $this->persistanceFile = $this->origFilesFolder . '/' . self::PERSISTANCE_FILE_NAME;
    }

    /**
     * Create a main folder if don't exist and load the entries
     *
     * @param boolean $reset if strue reset orig file folder
     *
     * @return void
     */
    public function init($reset = false)
    {
        $this->createMainFolder($reset);
        $this->load();
    }

    /**
     * Create orig file folder
     *
     * @param boolean $reset if true delete current folder
     *
     * @return boolean  return true if succeded
     *
     * @throws \Exception
     */
    protected function createMainFolder($reset = false)
    {
        if ($reset) {
            $this->deleteMainFolder();
        }

        if (!file_exists($this->origFilesFolder)) {
            if (!SnapIO::mkdir($this->origFilesFolder, 'u+rwx')) {
                throw new \Exception('Can\'t create the original files folder ' . SnapLog::v2str($this->origFilesFolder));
            }
        }

        $htaccessFile = $this->origFilesFolder . '/.htaccess';
        if (!file_exists($htaccessFile)) {
            $content = <<<HTACCESS
Order Allow,Deny
Deny from All
HTACCESS;
            @file_put_contents($htaccessFile, $content);
        }

        if (!file_exists($this->persistanceFile)) {
            $this->save();
        }

        return true;
    }

    /**
     * @return string Main folder path
     * @throws \Exception
     */
    public function getMainFolder()
    {
        if (!file_exists($this->origFilesFolder)) {
            throw new \Exception('Can\'t get the original files folder ' . SnapLog::v2str($this->origFilesFolder));
        }

        return $this->origFilesFolder;
    }

    /**
     * delete origianl files folder
     *
     * @return boolean
     * @throws \Exception
     */
    public function deleteMainFolder()
    {
        if (file_exists($this->origFilesFolder) && !SnapIO::rrmdir($this->origFilesFolder)) {
            throw new \Exception('Can\'t delete the original files folder ' . SnapLog::v2str($this->origFilesFolder));
        }
        $this->origFolderEntries = array();

        return true;
    }

    /**
     * add a entry on original folder.
     *
     * @param string      $identifier entry identifier
     * @param string      $path       entry path. can be a file or a folder
     * @param string      $mode       MODE_MOVE move the item in original folder
     *                                MODE_COPY copy the item in original folder
     * @param bool|string $rename     if rename is a string the item is renamed in original folder.
     *
     * @return boolean true if succeded
     *
     * @throws Exception
     */
    public function addEntry($identifier, $path, $mode = self::MODE_MOVE, $rename = false)
    {
        if (!file_exists($path)) {
            return false;
        }

        $baseName = empty($rename) ? basename($path) : $rename;

        if (($relativePath = SnapIO::getRelativePath($path, $this->rootPath)) === false) {
            $isRelative = false;
        } else {
            $isRelative = true;
        }
        $parentFolder = $isRelative ? dirname($relativePath) : SnapIO::removeRootPath(dirname($path));
        if (empty($parentFolder) || $parentFolder === '.') {
            $parentFolder = '';
        } else {
            $parentFolder .= '/';
        }
        $targetFolder = $this->origFilesFolder . '/' . $parentFolder;
        if (!file_exists($targetFolder)) {
            SnapIO::mkdirP($targetFolder);
        }
        $dest = $targetFolder . $baseName;

        switch ($mode) {
            case self::MODE_MOVE:
                // Don't use rename beacause new files must have the current script owner
                if (!SnapIO::rcopy($path, $dest)) {
                    throw new \Exception('Can\'t copy the original file  ' . SnapLog::v2str($path));
                }
                if (!SnapIO::rrmdir($path, $dest)) {
                    throw new \Exception('Can\'t remove the original file  ' . SnapLog::v2str($path));
                }
                break;
            case self::MODE_COPY:
                if (!SnapIO::rcopy($path, $dest)) {
                    throw new \Exception('Can\'t copy the original file  ' . SnapLog::v2str($path));
                }
                break;
            default:
                throw new \Exception('invalid mode addEntry');
        }

        $this->origFolderEntries[$identifier] = array(
            'baseName'   => $baseName,
            'source'     => $isRelative ? $relativePath : $path,
            'stored'     => $parentFolder . $baseName,
            'mode'       => $mode,
            'isRelative' => $isRelative
        );

        $this->save();
        return true;
    }

    /**
     * Get entry info from identifier
     *
     * @param string $identifier orig file identifier
     *
     * @return boolean|string false if entry don't exists
     */
    public function getEntry($identifier)
    {
        if (isset($this->origFolderEntries[$identifier])) {
            return $this->origFolderEntries[$identifier];
        } else {
            return false;
        }
    }

    /**
     * Get entry stored path in original folder
     *
     * @param string $identifier orig file identifier
     *
     * @return boolean|string false if entry don't exists
     */
    public function getEntryStoredPath($identifier)
    {
        if (isset($this->origFolderEntries[$identifier])) {
            return $this->origFilesFolder . '/' . $this->origFolderEntries[$identifier]['stored'];
        } else {
            return false;
        }
    }

    /**
     * Return true if identifier org file is relative path
     *
     * @param string $identifier orig file identifier
     *
     * @return boolean
     */
    public function isRelative($identifier)
    {
        if (isset($this->origFolderEntries[$identifier])) {
            $this->origFolderEntries[$identifier]['isRelative'];
        } else {
            return false;
        }
    }

    /**
     * Get entry target restore path
     *
     * @param string      $identifier          orig file identifier
     * @param null|string $defaultIfIsAbsolute if isn't null return the value if path is absolute
     *
     * @return boolean false if entry don't exists
     */
    public function getEntryTargetPath($identifier, $defaultIfIsAbsolute = null)
    {
        if (isset($this->origFolderEntries[$identifier])) {
            if ($this->origFolderEntries[$identifier]['isRelative']) {
                return $this->rootPath . '/' . $this->origFolderEntries[$identifier]['source'];
            } else {
                if (is_null($defaultIfIsAbsolute)) {
                    return $this->origFolderEntries[$identifier]['source'];
                } else {
                    return $defaultIfIsAbsolute;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * this function restore current entry in original position.
     * If mode is copy it simply delete the entry else move the entry in original position
     *
     * @param string      $identifier          identified of current entrye
     * @param boolean     $save                update saved entries
     * @param null|string $defaultIfIsAbsolute if isn't null return the value if path is absolute
     *
     * @return boolean true if succeded
     *
     * @throws Exception
     */
    public function restoreEntry($identifier, $save = true, $defaultIfIsAbsolute = null)
    {
        if (!isset($this->origFolderEntries[$identifier])) {
            return false;
        }

        $stored = $this->getEntryStoredPath($identifier);
        if (($original = $this->getEntryTargetPath($identifier, $defaultIfIsAbsolute)) === false) {
            return false;
        }

        switch ($this->origFolderEntries[$identifier]['mode']) {
            case self::MODE_MOVE:
                if (!SnapIO::rename($stored, $original)) {
                    throw new \Exception('Can\'t move the original file  ' . SnapLog::v2str($stored));
                }
                break;
            case self::MODE_COPY:
                if (!SnapIO::rrmdir($stored)) {
                    throw new \Exception('Can\'t delete entry ' . SnapLog::v2str($stored));
                }
                break;
            default:
                throw new \Exception('invalid mode addEntry');
        }

        unset($this->origFolderEntries[$identifier]);
        if ($save) {
            $this->save();
        }
        return true;
    }

    /**
     * Put all entries on original position and empty original folder
     *
     * @param string[] $exclude identifiers list t exclude
     *
     * @return boolean
     */
    public function restoreAll($exclude = array())
    {
        foreach (array_keys($this->origFolderEntries) as $ident) {
            if (in_array($ident, $exclude)) {
                continue;
            }
            $this->restoreEntry($ident, false);
        }
        $this->save();
        return true;
    }

    /**
     * Save notices from json file
     *
     * @return void
     */
    public function save()
    {
        if (!file_put_contents($this->persistanceFile, SnapJson::jsonEncodePPrint($this->origFolderEntries))) {
            throw new \Exception('Can\'t write persistence file');
        }
        return true;
    }

    /**
     * Load notice from json file
     *
     * @return boolean
     */
    private function load()
    {
        if (file_exists($this->persistanceFile)) {
            $json                    = file_get_contents($this->persistanceFile);
            $this->origFolderEntries = json_decode($json, true);
        } else {
            $this->origFolderEntries = array();
        }
        return true;
    }
}
