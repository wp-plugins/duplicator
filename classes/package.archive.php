<?php

require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.archive.zip.php');

class DUP_Archive {
	
	//PUBLIC
	public $FilterDirs;
	public $FilterExts;
	public $FilterOn;
	public $File;
	public $Format;
	public $PackDir;
	public $Size;
	
	//PROTECTED
	protected $Package;
	
	//PRIVATE
	private $filerDirsArray = array();
	private $filerExtsArray = array();
	
	public function __construct($package) {
		$this->Package   = $package;
		$this->FilterOn  = false;
	}
	
	public function Build() {
		try {
			
			if (!isset($this->PackDir) && ! is_dir($this->PackDir)) throw new Exception("The 'PackDir' property must be a valid diretory.");
			if (!isset($this->File)) throw new Exception("A 'File' property must be set.");
		
			switch ($this->Format) {
				case 'TAR':			break;
				case 'TAR-GZIP': 	break;
				default:

					if (class_exists(ZipArchive)) {
						$this->Format = 'ZIP';
						DUP_Zip::Create($this);
					} else {
						//TODO:PECL and SHELL FORMATS
					}
					break;
			}
		
		} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), "\n";
		}
	}
	
	public function GetFilterDirAsArray() {
		return array_map('DUP_Util::SafePath', explode(";", $this->FilterDirs, -1));
	}
	
	public function GetFilterExtsAsArray() {
		return explode(";", $this->FilterExts, -1);
	}
	
	/**
	 *  DIRSTATS
	 *  Get the directory size recursively, but don't calc the snapshot directory, exclusion diretories
	 *  @param string $directory		The directory to calculate
	 *  @returns array					An array of values for the directory stats
	 */	
	public function DirStats() {
		$this->filerDirsArray = $this->GetFilterDirAsArray();
		$this->filerExtsArray = $this->GetFilterExtsAsArray();
		$rootPath = rtrim(DUPLICATOR_WPROOTPATH, '//' );
		if ($this->FilterOn) {
			if (! in_array($rootPath, $this->filerDirsArray) ) {
				return $this->runDirStats($this->PackDir);
			}
		} else {
			return $this->runDirStats($this->PackDir);
		}
	}
	

	private function runDirStats($directory) {
		
		$currentPath = DUP_Util::SafePath($directory);
		//EXCLUDE: Snapshot directory
		if (strstr($currentPath, DUPLICATOR_SSDIR_PATH) || empty($currentPath)) {
			return;
		}
		
		$size    = 0;
		$dirCount = 1;
		$fileCount = 0;
		$linkCount = 0;
		$longFiles = array();
		$bigFiles  = array();
		
		$dh = new DirectoryIterator($currentPath);
		foreach ($dh as $file) {
			if (!$file->isDot()) {
				$nextpath	= "{$currentPath}/{$file}";
				if ($file->isDir()) {
					if (! in_array($nextpath, $this->filerDirsArray)) {						
						$result      = $this->runDirStats($nextpath);
						$size		+= $result['Size'];
						$dirCount	+= $result['DirCount'];
						$fileCount	+= $result['FileCount'];
						$linkCount	+= $result['LinkCount'];
						if (count($result['LongFiles']))
							array_push($longFiles, $result['LongFiles']);
						if (count($result['BigFiles']))
							array_push($bigFiles, $result['BigFiles']);
					}

				} else if ($file->isFile() && $file->isReadable()) {
					if (!in_array(@pathinfo($nextpath, PATHINFO_EXTENSION), $this->filerExtsArray)) {
						$size += $file->getSize();
						$fileCount++;
						if (strlen($file) > 200) 
							array_push($longFiles, $nextpath);
						if ($file->getSize() > DUPLICATOR_SCAN_BIGFILE) 
							array_push($bigFiles, $nextpath);
					}
				} else if ($file->isLink()) {
					$linkCount++;
				} 
			}	 
		}
		$total['Size']		= $size;
		$total['DirCount']  = $dirCount;
		$total['FileCount']	= $fileCount;
		$total['LinkCount'] = $linkCount;
		$total['LongFiles'] = $longFiles;
		$total['BigFiles']  = $bigFiles;
		return $total;
	}	
	
	
	

	
	

}
?>
