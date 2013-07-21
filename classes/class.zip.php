<?php

class Duplicator_Zip {

	public $limit = DUPLICATOR_ZIP_FILE_POOL;
    public $zipArchive;
    public $zipFilePath;
	public $zipFileSize = 0;	
    public $rootFolder;
    public $skipNames;
    private $limitItems = 0;

    /**
     *  DUPLICATOR ZIP
     *  Creates the zip file
     *
     *  @param string $zipFilePath	The full path to the zip file that will be made
     *  @param string $folderPath	The folder that will be zipped
     *  @param string $sqlfilepath	The path to the database file to include in the package
     */
    function __construct($zipFilePath, $folderPath, $sqlfilepath) {
        try {
            
            $total_time_start = DuplicatorUtils::GetMicrotime();
            duplicator_log("PACKAGE DIR:  {$folderPath}");
            duplicator_log("PACKAGE FILE: {$zipFilePath}");

            $this->zipArchive	= new ZipArchive();
            $this->zipFilePath	= duplicator_safe_path($zipFilePath);
            $this->rootFolder	= rtrim(duplicator_safe_path($folderPath), '/');
            $this->skipNames	= $GLOBALS['duplicator_skip_ext-array'];
			$this->countDirs  = 0;
			$this->countFiles = 0;
			$this->countLinks = 0;

            $exts_list = implode(";", $this->skipNames);
            $path_list = implode(";", $GLOBALS['duplicator_bypass-array']);
			$this->fileExtActive = strlen($exts_list);
			

            duplicator_log("FILTER EXTENSIONS:  '{$exts_list}'");
            duplicator_log("FILTER DIRECTORIES: '{$path_list}'");
            duplicator_log($GLOBALS['DUPLICATOR_SEPERATOR2']);

			//CREATE ZIP FILE
            if ($this->zipArchive->open($this->zipFilePath, ZIPARCHIVE::CREATE) === TRUE) {
                duplicator_log("STARTING PACKAGE BUILD");
            } else {
				duplicator_error("ERROR: Cannot open zip file with PHP ZipArchive.  \nERROR INFO: Path location [{$this->zipFilePath}]");
            }

            //ADD SQL File
            $sql_in_zip = $this->zipArchive->addFile($sqlfilepath, "/database.sql");
            if ($sql_in_zip) {
                duplicator_log("ADDED=>SQL: {$sqlfilepath}");
            } else {
				duplicator_error("ERROR: Unable to add database.sql file to package from.  \nERROR INFO: SQL File Path [{$sqlfilepath}]");
            }

            //RECURSIVE CALL TO ALL FILES
			$list_time_start = DuplicatorUtils::GetMicrotime();
			duplicator_log("BUILDING FILE LIST");
            $this->resursiveZip($this->rootFolder);
			
            $list_time_end = DuplicatorUtils::GetMicrotime();
            $list_time_sum = DuplicatorUtils::ElapsedTime($list_time_end, $list_time_start);
			duplicator_log("FILE LIST COMPLETE: {$list_time_sum}");
			duplicator_log("FILE LIST STATS: Dirs {$this->countDirs} | Files {$this->countFiles} | Links {$this->countLinks} | " );
            duplicator_log("\nPACKAGE INFO: " . print_r($this->zipArchive, true));
			
			//LOG FINAL RESULTS
			duplicator_log("CREATING PACKAGE");
			duplicator_fcgi_flush();
			@set_time_limit(0);
            $zip_close_result = $this->zipArchive->close();
			if ($zip_close_result) {
				 duplicator_log("CLOSING PACKAGE RESULT: '{$zip_close_result}'");
			}  else {
				$err_info = 'This server or hosted segement might have a disk quota limit.\nPlease check your disk space usage to make sure you can store this zip file successfully.';
				duplicator_error("ERROR: ZipArchive Class did not close successfully.   \nERROR INFO: {$err_info}");
			}

            $total_time_end = DuplicatorUtils::GetMicrotime();
            $total_time_sum = DuplicatorUtils::ElapsedTime($total_time_end, $total_time_start);
			
			$this->zipFileSize = @filesize($this->zipFilePath);
			duplicator_log("PACKAGE FILE SIZE: " . duplicator_bytesize($this->zipFileSize));
            duplicator_log("PACKAGE RUNTIME: {$total_time_sum}");
        } 
        catch (Exception $e) {
			duplicator_error("ERROR: Runtime error in class.zip.php constructor.   \nERROR INFO: {$e}");
        }
    }
	
    function resursiveZip($directory) {
        try {
            $folderPath = duplicator_safe_path($directory);
			
            //EXCLUDE: Snapshot directory
            if (strstr($folderPath, DUPLICATOR_SSDIR_PATH) || empty($folderPath)) {
                return;
            }

            //EXCLUDE: Directory Exclusions List
            if (is_array($GLOBALS['duplicator_bypass-array'])) {
                foreach ($GLOBALS['duplicator_bypass-array'] as $val) {
                    if (duplicator_safe_path($val) == $folderPath) {
                        duplicator_log("path filter found: {$val}", 2);
                        return;
                    }
                }
            }

			//Notes: $file->getExtension() is not reliable as it silently fails at least in php 5.2.17 
			//when a file has a permission such as 705 falling back to pathinfo is more stable
            $dh = new DirectoryIterator($folderPath);
			
            foreach ($dh as $file) {
                if (!$file->isDot()) {
                    $fullpath  = "{$folderPath}/{$file}";
                    $localpath = str_replace($this->rootFolder, '', $folderPath);
                    $localname = empty($localpath) ? '' : ltrim("{$localpath}/", '/');
                    $filename  = $file->getFilename();
                    if ($file->isDir()) {
                        if (!in_array($fullpath, $GLOBALS['duplicator_bypass-array'])) {
							if ($file->isReadable() && $this->zipArchive->addEmptyDir("{$localname}{$filename}")) {
								$this->countDirs++;
								$this->resursiveZip($fullpath);
							} else {
								duplicator_log("WARNING: Unable to add directory: $fullpath");
							}
                        } 
					} else if ($file->isFile() && $file->isReadable()) {
						if ($this->fileExtActive) {
							$ext = @pathinfo($fullpath, PATHINFO_EXTENSION);
							if (!in_array($ext, $this->skipNames) || empty($ext)) {
								$this->zipArchive->addFile("{$folderPath}/{$filename}", "{$localname}{$filename}");
								$this->countFiles++;
							}
						} else {
							$this->zipArchive->addFile("{$folderPath}/{$filename}", "{$localname}{$filename}");
							$this->countFiles++;
						}
                    } else if ($file->isLink()) {
						$this->countLinks++;
                    } 
                    $this->limitItems++;
                }
            }

            //Check if were over our count
			//This process seems to slow things down.
           /* if ($this->limitItems > $this->limit) {
				$currentfilecount = $this->countDirs + $this->countFiles;
                duplicator_log("ADDED=>ZIP HANDLE: ({$currentfilecount})");
                $this->zipArchive->close();
                $this->zipArchive->open($this->zipFilePath, ZIPARCHIVE::CREATE);
                $this->limitItems = 0;
                duplicator_fcgi_flush();
            }*/
			
            @closedir($dh);
        } 
		
		catch (Exception $e) {
			duplicator_error("ERROR: Runtime error in class.zip.php resursiveZip.   \nERROR INFO: {$e}");
        }
    }
}

?>