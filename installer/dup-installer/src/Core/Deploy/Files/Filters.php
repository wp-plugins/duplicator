<?php

namespace  Duplicator\Installer\Core\Deploy\Files;

use Duplicator\Libs\Snap\JsonSerialize\AbstractJsonSerializable;
use Duplicator\Libs\Snap\SnapIO;

/**
 * Manage filters for extraction
 */
class Filters extends AbstractJsonSerializable
{
    /** @var string[] */
    protected $files = array();
    /** @var string[] */
    protected $dirsWithoutChilds = array();
    /** @var string[] */
    protected $dirs = array();

    /**
     * Class contructor
     *
     * @param string[] $dirs              dirs filters
     * @param string[] $dirsWithoutChilds dirs without child filters
     * @param string[] $files             files filters
     */
    public function __construct($dirs = array(), $dirsWithoutChilds = array(), $files = array())
    {
        $this->files             = (array) $files;
        $this->dirs              = (array) $dirs;
        $this->dirsWithoutChilds = (array) $dirsWithoutChilds;
    }

    /**
     * Check if passe path is filterd
     *
     * @param string $path path to check
     *
     * @return bool
     */
    public function isFiltered($path)
    {
        if (in_array($path, $this->dirsWithoutChilds)) {
            return true;
        }

        foreach ($this->dirs as $dirFilter) {
            if (SnapIO::isChildPath($path, $dirFilter)) {
                return true;
            }
        }

        return in_array($path, $this->files);
    }

    /**
     * Add dir filter
     *
     * @param string $dir          dir path
     * @param bool   $withoutChild if true add dir filter without childs
     *
     * @return void
     */
    public function addDir($dir, $withoutChild = false)
    {
        if ($withoutChild) {
            $this->dirsWithoutChilds[] = (string) $dir;
        } else {
            $this->dirs[] = (string) $dir;
        }
    }

    /**
     * Add file fo filters
     *
     * @param string $file file path
     *
     * @return void
     */
    public function addFile($file)
    {
        $this->files[] = (string) $file;
    }

    /**
     * Optimize and sort filters
     *
     * @return bool
     */
    public function optmizeFilters()
    {
        $this->files             = array_values(array_unique($this->files));
        $this->dirsWithoutChilds = array_values(array_unique($this->dirsWithoutChilds));
        $this->dirs              = array_values(array_unique($this->dirs));

        $optimizedDirs  = array();
        $optimizedFiles = array();

        for ($i = 0; $i < count($this->dirs); $i++) {
            $exclude = false;
            for ($j = 0; $j < count($this->dirs); $j++) {
                if ($i === $j) {
                    continue;
                }
                if (SnapIO::isChildPath($this->dirs[$i], $this->dirs[$j])) {
                    $exclude = true;
                    break;
                }
            }
            if (!$exclude) {
                $optimizedDirs[] = $this->dirs[$i];
            }
        }

        $optimizedDirs = SnapIO::sortBySubfoldersCount($optimizedDirs);

        foreach ($this->files as $file) {
            $exclude = false;
            foreach ($optimizedDirs as $cDir) {
                if (SnapIO::isChildPath($file, $cDir)) {
                    $exclude = true;
                    break;
                }
            }

            if (!$exclude) {
                $optimizedFiles[] = $file;
            }
        }

        $this->files = $optimizedFiles;
        $this->dirs  = $optimizedDirs;

        return true;
    }

    /**
     * Get the value of files
     *
     * @return string[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Get the value of dirs
     *
     * @return string[]
     */
    public function getDirs()
    {
        return $this->dirs;
    }

    /**
     * Get the value of dirsWithoutChilds
     *
     * @return string[]
     */
    public function getDirsWithoutChilds()
    {
        return $this->dirsWithoutChilds;
    }
}
