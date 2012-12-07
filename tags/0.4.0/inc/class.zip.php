<?php 

class Duplicator_Zip 
{
	protected $limit = DUPLICATOR_ZIP_FILE_POOL;
	protected $zipArchive;
	protected $rootFolder;
	protected $skipNames;
	protected $zipFilePath;
	private   $limitItems = 0;
	
	/**
	 *  DUPLICATOR ZIP
	 *  Creates the zip file
	 *
	 *  @param string $zipFilePath	The full path to the zip file that will be made
	 *  @param string $folderPath	The folder that will be zipped
	 *  @param string $sqlfilepath	The path to the database file to include in the package
	 */
	function __construct($zipFilePath, $folderPath, $sqlfilepath) {
		try 
		{
		
			duplicator_log("log:class.zip=>started");
			duplicator_log("archive this folder: {$folderPath}");
			duplicator_log("archive file name:   {$zipFilePath}");
			
			$this->zipArchive  = new ZipArchive();
			$this->zipFilePath = duplicator_safe_path($zipFilePath);
			$this->rootFolder  = rtrim(duplicator_safe_path($folderPath), '/');
			$this->skipNames   = $GLOBALS['duplicator_skip_ext-array'];
			
			$exts_list = implode(";", $this->skipNames);
			$path_list = implode(";", $GLOBALS['duplicator_bypass-array']);
			duplicator_log("filter file extensions: '{$exts_list}'");
			duplicator_log("filter directory paths: '{$path_list}'");
			

			if ($this->zipArchive->open($this->zipFilePath, ZIPARCHIVE::CREATE) === TRUE) {
				duplicator_log("zipArchive opened");
			} 
			else {
				$err = "cannot open <{$this->zipFilePath}>";
				duplicator_log($err);
				throw new Exception($err);
			}
			
			//Recursive Call
			$this->resursiveZip($this->rootFolder);
			
			//ADD SQL File
			$this->zipArchive->addFile($sqlfilepath, "database.sql");

			duplicator_log("archive info: " . print_r($this->zipArchive, true));
			duplicator_log("close returned: " . $this->zipArchive->close() . " (if null check your disk quota)" );
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
						duplicator_log("path filter found: {$val}", 2);
						return;
					}
				}
			}
			
			/* Legacy: Excludes empty diretories and files with no extention
			while (false !== ($file = @readdir($dh))) { 
				if ($file != '.' && $file != '..') { 
					$fullpath = "{$folderPath}/{$file}";
					
					if(is_dir($fullpath)) {
						duplicator_fcgi_flush();
						$this->resursiveZip($fullpath);
					}
					else if(is_file($fullpath) && is_readable($fullpath)) {
						//Check filter extensions
						if(!in_array(@pathinfo($fullpath, PATHINFO_EXTENSION), $this->skipNames)) {
							$localpath = str_replace($this->rootFolder, '', $folderPath);
							$localname = empty($localpath) ? '' : ltrim("{$localpath}/", '/');
							$this->zipArchive->addFile("{$folderPath}/{$file}", "{$localname}{$file}");
						}
					} 
					$this->limitItems++;
				}
			} */
			
			
			while (false !== ($file = @readdir($dh))) {
                 if ($file != '.' && $file != '..') {
                    $fullpath = "{$folderPath}/{$file}";
					$localpath = str_replace($this->rootFolder, '', $folderPath);
					$localname = empty($localpath) ? '' : ltrim("{$localpath}/", '/');
                     if(is_dir($fullpath)) {
                         duplicator_fcgi_flush();
 						 $this->zipArchive->addEmptyDir("{$localname}{$file}");
                         $this->resursiveZip($fullpath);
                     }
                     else if(is_file($fullpath) && is_readable($fullpath)) {
                         //Check filter extensions
                        $ext = @pathinfo($fullpath, PATHINFO_EXTENSION);
                        if($ext == '' || !in_array($ext, $this->skipNames)) {
							$this->zipArchive->addFile("{$folderPath}/{$file}", "{$localname}{$file}");
                        }
                     }
					 $this->limitItems++;
				}
			}
			
			
			
			//Check if were over our count
			if($this->limitItems > $this->limit) {
				duplicator_log("log:class.zip=>new open handle {$this->zipArchive->numFiles}");
				$this->zipArchive->close();
				$this->zipArchive->open($this->zipFilePath, ZIPARCHIVE::CREATE);
				$this->limitItems = 0;
				duplicator_fcgi_flush();
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