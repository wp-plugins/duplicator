<?php
if ( ! defined( 'DUPLICATOR_VERSION' ) ) exit; // Exit if accessed directly

require_once (DUPLICATOR_PLUGIN_PATH . 'classes/package.archive.zip.php');

class DUP_Archive {
	
	//PUBLIC
	public $FilterDirs;
	public $FilterExts;
	public $FilterOn;
	public $File;
	public $Format;
	public $PackDir;		
	public $Size = 0;
	public $WarnFileSize = array();
	public $WarnFileName = array();
	public $Dirs  = array();
	public $Files = array();
	public $Links = array();
	public $OmitDirs  = array();
	public $OmitFiles = array();
	
	//PROTECTED
	protected $Package;
	
	//PRIVATE
	private $filterDirsArray = array();
	private $filterExtsArray = array();


	public function __construct($package) {
		$this->Package   = $package;
		$this->FilterOn  = false;
	}
	
	public function Build() {
		try {
			
			if (!isset($this->PackDir) && ! is_dir($this->PackDir)) throw new Exception("The 'PackDir' property must be a valid diretory.");
			if (!isset($this->File)) throw new Exception("A 'File' property must be set.");
		
			$this->Package->SetStatus(DUP_PackageStatus::ARCSTART);
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
			
			$storePath = "{$this->Package->StorePath}/{$this->File}";
			$this->Size   = @filesize($storePath);
			$this->Package->SetStatus(DUP_PackageStatus::ARCDONE);
		
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
	 *  @link http://msdn.microsoft.com/en-us/library/aa365247%28VS.85%29.aspx Windows filename restrictions
	 */	
	public function Stats() {
		
		$this->filterDirsArray = array();
		$this->filterExtsArray = array();
		if ($this->FilterOn) {
			$this->filterDirsArray = $this->GetFilterDirAsArray();
			$this->filterExtsArray = $this->GetFilterExtsAsArray();
		}
		
		$this->runStats();
		return $this;
	}
	

	//Notes: $file->getExtension() is not reliable as it silently fails at least in php 5.2.17 
	//when a file has a permission such as 705 falling back to pathinfo is more stable
	private function runStats() {
		
		array_push($this->filterDirsArray, DUPLICATOR_SSDIR_PATH);
		$rootPath = DUP_Util::SafePath(rtrim(DUPLICATOR_WPROOTPATH, '//' ));
		
		//If the root directory is a filter then we only need a simple DirectoryIterator
		//Must use iterator_to_array in order to avoid the error 'too many files open' for recursion
		if (in_array($this->PackDir, $this->filterDirsArray)) {
			$files = new DirectoryIterator($rootPath);
			$key = array_search($this->PackDir, $this->filterDirsArray);
			unset($this->filterDirsArray[$key]);
		} else {
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath));
			$files = iterator_to_array($iterator);
			unset($iterator);
		}
		
		foreach ($files as $file) {
			
			$filePath =  DUP_Util::SafePath($file->getRealPath());
			$fileName = $file->getFilename();
			$valid = true;
			
			//PATH FILTERS
			foreach($this->filterDirsArray as $item) { 
				if (strstr($filePath, $item)) {
					if ($file->isDir() && $file->getFilename() !== '..')
						$this->OmitDirs[] = $filePath;
					continue 2;
				}
			}
			
			//FILES
			if ($file->isFile()) {

				if (!in_array(@pathinfo($filePath, PATHINFO_EXTENSION), $this->filterExtsArray)  && $file->isReadable()) {
					$fileSize = @filesize($filePath);
					$fileSize = empty($fileSize) ? 0 : $fileSize; 
					if (strlen($filePath) > 250 || preg_match('/(\/|\*|\?|\>|\<|\:|\\|\|)/', $fileName)|| trim($fileName) == "") {
						array_push($this->WarnFileName, $filePath);
						$valid = false;
					} 
					if ($fileSize > DUPLICATOR_SCAN_WARNFILESIZE) {
						array_push($this->WarnFileSize, $filePath . ' [' . DUP_Util::ByteSize($fileSize) . ']');
					}
					if ($valid) {
						$this->Size += $fileSize;
						$this->Files[] = $filePath;
					} 
					else {
						$this->OmitFiles[] = $filePath;
					}
				} else {
					$this->OmitFiles[] = $filePath;	
				} 
			}
			//DIRECTORIES
			elseif ($file->isDir() && $file->getFilename() !== '..') {
				if (preg_match('/(\/|\*|\?|\>|\<|\:|\\|\|)/', $fileName) || trim($fileName) == "") {
					array_push($this->WarnFileName, $filePath);
					$valid = false;
				}
				if ($valid) {
					$this->Dirs[] = $filePath;
				} else {
					$this->OmitDirs[] = $filePath;
				}
			} 
			//LINKS
			else if ($file->isLink()) {
				$this->Links[] = $filePath;
			} 
		}
	}	
	
}
?>