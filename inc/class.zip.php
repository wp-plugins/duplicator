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
		
			duplicator_log("log:class.zip=>started " . @date('h:i:s') );
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
			
			//ADD SQL File
			$sql_in_zip = $this->zipArchive->addFile($sqlfilepath, "/database.sql");
			if ($sql_in_zip) {
				duplicator_log("Added database.sql to zip package from file: {$sqlfilepath}");
			} else {
				duplicator_log("{$GLOBALS['DUPLICATOR_SEPERATOR1']}\nERROR: Unable to add database.sql file to package from {$sqlfilepath} \n{$GLOBALS['DUPLICATOR_SEPERATOR1']}");
			}
			
			//RECURSIVE CALL TO ALL FILES
			$this->resursiveZip($this->rootFolder);
			
			//LOG FINAL RESULTS
			duplicator_log("archive info: " . print_r($this->zipArchive, true));
			$zip_close_result = $this->zipArchive->close();
			$status_msg = ($zip_close_result) 
				? "close returned: '{$zip_close_result}'"
				: "{$GLOBALS['DUPLICATOR_SEPERATOR1']}\nWARNING: ZipArchive Class did not close successfully.  This means you most likely have a disk quota issue on your server.\nPlease check your disk space usage to make sure you can store this zip file successfully.\n{$GLOBALS['DUPLICATOR_SEPERATOR1']}";
			
			duplicator_log($status_msg);
			duplicator_log("log:class.zip=>ended " . @date('h:i:s') );
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
			
			while (false !== ($file = @readdir($dh))) {
                 if ($file != '.' && $file != '..') {
                    $fullpath = "{$folderPath}/{$file}";
					$localpath = str_replace($this->rootFolder, '', $folderPath);
					$localname = empty($localpath) ? '' : ltrim("{$localpath}/", '/');
					if(is_file($fullpath) && is_readable($fullpath)) {
                         //Check filter extensions
                        $ext = @pathinfo($fullpath, PATHINFO_EXTENSION);
                        if($ext == '' || !in_array($ext, $this->skipNames)) {
							$this->zipArchive->addFile("{$folderPath}/{$file}", "{$localname}{$file}");
                        }
                    } else if(is_dir($fullpath)) {
						if(! in_array($fullpath, $GLOBALS['duplicator_bypass-array'])) {
							$this->zipArchive->addEmptyDir("{$localname}{$file}");
							@set_time_limit(0);
							duplicator_fcgi_flush();
						}
						$this->resursiveZip($fullpath);
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