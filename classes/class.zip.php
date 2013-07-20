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
            
            $time_start = DuplicatorUtils::GetMicrotime();
            duplicator_log("PACKAGE FOLDER: {$folderPath}");
            duplicator_log("PACKAGE FILE:   {$zipFilePath}");

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
                duplicator_log("BUILDING PACKAGE FILE");
            } else {
				duplicator_error("ERROR: Cannot open zip file with PHP ZipArchive.  \nERROR INFO: Path location [{$this->zipFilePath}]");
            }

            //ADD SQL File
            $sql_in_zip = $this->zipArchive->addFile($sqlfilepath, "/database.sql");
            if ($sql_in_zip) {
                duplicator_log("SQL FILE ADDED TO PACKAGE: {$sqlfilepath}");
            } else {
				duplicator_error("ERROR: Unable to add database.sql file to package from.  \nERROR INFO: SQL File Path [{$sqlfilepath}]");
            }

            //RECURSIVE CALL TO ALL FILES
            $this->resursiveZip($this->rootFolder);

            //LOG FINAL RESULTS
            duplicator_log("PACKAGE INFO: " . print_r($this->zipArchive, true));
			duplicator_log("STATS: Directories={$this->countDirs} | Files={$this->countFiles} | Links={$this->countLinks} | hidden files may not be counted on some servers" );
            $zip_close_result = $this->zipArchive->close();
			if ($zip_close_result) {
				 duplicator_log("CLOSING PACKAGE RESULT: '{$zip_close_result}'");
			}  else {
				$err_info = 'This server or hosted segement might have a disk quota limit.\nPlease check your disk space usage to make sure you can store this zip file successfully.';
				duplicator_error("ERROR: ZipArchive Class did not close successfully.   \nERROR INFO: {$err_info}");
			}

            $time_end = DuplicatorUtils::GetMicrotime();
            $time_sum = DuplicatorUtils::ElapsedTime($time_end, $time_start);
			
			$this->zipFileSize = @filesize($this->zipFilePath);
			duplicator_log("PACKAGE FILE SIZE: " . duplicator_bytesize($this->zipFileSize));
            duplicator_log("PACKAGE RUNTIME: {$time_sum}");
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
								duplicator_fcgi_flush();
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
            if ($this->limitItems > $this->limit) {
                duplicator_log("New open zipArchive handle {$this->zipArchive->numFiles}");
                $this->zipArchive->close();
                $this->zipArchive->open($this->zipFilePath, ZIPARCHIVE::CREATE);
                $this->limitItems = 0;
                duplicator_fcgi_flush();
            }

            @closedir($dh);
        } 
		
		catch (Exception $e) {
			duplicator_error("ERROR: Runtime error in class.zip.php resursiveZip.   \nERROR INFO: {$e}");
        }
    }
}

?>