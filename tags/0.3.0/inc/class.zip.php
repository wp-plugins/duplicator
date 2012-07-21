<?php 

class Duplicator_Zip 
{
	protected $limit = DUPLICATOR_ZIP_FILE_POOL;
	protected $zipArchive;
	protected $rootFolder;
	protected $skipNames;
	protected $zipFileName;
	private   $limitItems = 0;
	
	/**
	 *  DUPLICATOR ZIP
	 *  Creates the zip file
	 *
	 *  @param string $zipFilePath	The full path to the zip file that will be made
	 *  @param string $folderPath	The folder that will be zipped
	 *  @param string $ignored		The file extentions to ignore
	 *  @param string $sqlfilepath	The path to the database file to include in the package
	 */
	function __construct($zipFilePath, $folderPath, $ignored=null, $sqlfilepath) {
		try 
		{
		
			duplicator_log("log:class.zip=>started");
			duplicator_log("log:class.zip=>archive file:   {$zipFilePath}");
			duplicator_log("log:class.zip=>archive folder: {$folderPath}");
			
			$this->zipArchive  = new ZipArchive();
			$this->zipFileName = duplicator_safe_path($zipFilePath);
		
			$this->rootFolder  = rtrim(duplicator_safe_path($folderPath), '/');
			$this->skipNames   = is_array($ignored) ? $ignored : $ignored ? array($ignored) : array();
			
			if ($this->zipArchive->open($this->zipFileName, ZIPARCHIVE::CREATE) === TRUE) {
				duplicator_log("log:class.zip=>opened");
			} 
			else {
				$err = "log:class.zip=>cannot open <{$this->zipFileName}>";
				duplicator_log($err);
				throw new Exception($err);
			}
			
			//Recursive Call
			$this->resursiveZip($this->rootFolder);
			
			//ADD SQL File
			$this->zipArchive->addFile($sqlfilepath, "database.sql");


			$msg = 'log:class.zip=>archive info: ' . print_r($this->zipArchive, true);
			duplicator_log($msg);
			duplicator_log("log:class.zip=>close returned: " . $this->zipArchive->close() . " (if null check your disk quota)" );
			duplicator_log("log:class.zip=>ended");
		} 
		catch(Exception $e) 
		{
			duplicator_log("log:class.zip=>runtime error: " . $e);
		}
	}
	
	function resursiveZip($directory)
	{
		try 
		{
			$folderPath = duplicator_safe_path($directory);
			if(!$dh = opendir($folderPath)) {
				return false;
			}
			
			//EXCLUDE: Snapshot directory
			if( strstr($folderPath, DUPLICATOR_SSDIR_PATH)) {
				return;
			}
			
			//EXCLUDE: Directory Exclusions List
			if ($GLOBALS['duplicator_bypass-array'] != null) {
				foreach ($GLOBALS['duplicator_bypass-array'] as $val) {
					if (duplicator_safe_path($val) == $folderPath) {
						duplicator_log("directory exclusion found: {$val}", 2);
						return;
					}
				}
			}
			
			while($file = readdir($dh)) {
				if($file == "." || $file == "..") {
					continue;
				}
				
				if(!in_array($file, $this->skipNames)) {
					$fullpath = "{$folderPath}/{$file}";
					if(is_dir($fullpath)) {
						$this->resursiveZip($fullpath);
						//KEEP Buffer active to prevent Idle timeout
						echo(str_repeat(' ',256));
						@flush();
					}
					else if(is_file($fullpath)) {
						$localpath = str_replace($this->rootFolder, '', $folderPath);
						$localname = empty($localpath) ? '' : ltrim("{$localpath}/", '/');
						$this->zipArchive->addFile("{$folderPath}/{$file}", "{$localname}{$file}");
					} 

					$this->limitItems++;
				}
			}
			
			//Check if were over our count
			if($this->limitItems > $this->limit) {
				duplicator_log("log:class.zip=>new open handle {$this->zipArchive->numFiles}");
				$this->zipArchive->close();
				$this->zipArchive->open($this->zipFileName, ZIPARCHIVE::CREATE);
				$this->limitItems = 0;
			}
			
			closedir($dh);
		} 
		catch(Exception $e) 
		{
			duplicator_log("log:class.zip=>runtime error: " . $e);
		}		
	}
}
?>