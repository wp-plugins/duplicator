<?php

class Duplicator_Zip {

    protected $limit = DUPLICATOR_ZIP_FILE_POOL;
    protected $zipArchive;
    protected $rootFolder;
    protected $skipNames;
    protected $zipFilePath;
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
            duplicator_log("ARCHIVE FOLDER: {$folderPath}");
            duplicator_log("ARCHIVE FILE:   {$zipFilePath}");

            $this->zipArchive = new ZipArchive();
            $this->zipFilePath = duplicator_safe_path($zipFilePath);
            $this->rootFolder = rtrim(duplicator_safe_path($folderPath), '/');
            $this->skipNames = $GLOBALS['duplicator_skip_ext-array'];
			$this->countDirs  = 0;
			$this->countFiles = 0;
			$this->countLinks = 0;

            $exts_list = implode(";", $this->skipNames);
            $path_list = implode(";", $GLOBALS['duplicator_bypass-array']);
            duplicator_log("FILTER EXTENSIONS:  '{$exts_list}'");
            duplicator_log("FILTER DIRECTORIES: '{$path_list}'");
            duplicator_log($GLOBALS['DUPLICATOR_SEPERATOR2']);


            if ($this->zipArchive->open($this->zipFilePath, ZIPARCHIVE::CREATE) === TRUE) {
                duplicator_log("ZIPARCHIVE OPENED");
            } else {
                $err = "CANNOT OPEN <{$this->zipFilePath}>";
                duplicator_log($err);
                throw new Exception($err);
            }

            //ADD SQL File
            $sql_in_zip = $this->zipArchive->addFile($sqlfilepath, "/database.sql");
            if ($sql_in_zip) {
                duplicator_log("DATABASE.SQL ADDED TO ZIP: {$sqlfilepath}");
            } else {
                duplicator_log("{$GLOBALS['DUPLICATOR_SEPERATOR1']}\nERROR: Unable to add database.sql file to package from {$sqlfilepath} \n{$GLOBALS['DUPLICATOR_SEPERATOR1']}");
            }

            //RECURSIVE CALL TO ALL FILES
            $this->resursiveZip($this->rootFolder);

            //LOG FINAL RESULTS
            duplicator_log("ARCHIVE INFO: " . print_r($this->zipArchive, true));
			duplicator_log("STATS: Directories= {$this->countDirs} | Files = {$this->countFiles} | Links = {$this->countLinks} | hidden files may not be counted on some servers" );
            $zip_close_result = $this->zipArchive->close();
            $status_msg = ($zip_close_result) 
				? "CLOSE RETURNED: '{$zip_close_result}'" 
				: "{$GLOBALS['DUPLICATOR_SEPERATOR1']}\nWARNING: ZipArchive Class did not close successfully.  This server or hosted segement might have a disk quota limit.\nPlease check your disk space usage to make sure you can store this zip file successfully.\n{$GLOBALS['DUPLICATOR_SEPERATOR1']}";

            duplicator_log($status_msg);
            
            $time_end = DuplicatorUtils::GetMicrotime();
            $time_sum = DuplicatorUtils::ElapsedTime($time_end, $time_start);
            duplicator_log("ZIP TOTAL RUNTIME: {$time_sum}");
            
        } 
        catch (Exception $e) {
            duplicator_log("LOG:CLASS.ZIP=>RUNTIME ERROR: " . $e);
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

            $dh = new DirectoryIterator($folderPath);
			
            foreach ($dh as $file) {
                if (!$file->isDot()) {
                    $fullpath  = "{$folderPath}/{$file}";
                    $localpath = str_replace($this->rootFolder, '', $folderPath);
                    $localname = empty($localpath) ? '' : ltrim("{$localpath}/", '/');
                    $filename  = $file->getFilename();

                    if ($file->isDir()) {
                        if (!in_array($fullpath, $GLOBALS['duplicator_bypass-array'])) {
							if ($this->zipArchive->addEmptyDir("{$localname}{$filename}")) {
								$this->countDirs++;
								@set_time_limit(0);
								duplicator_fcgi_flush();
								$this->resursiveZip($fullpath);
							} else {
								duplicator_log("WARN: Unable to add directory: $fullpath");
							}
                        } 
					} else if ($file->isFile()) {
                        //Check filter extensions
						if ($this->skipNames) {
							$ext = @pathinfo($fullpath, PATHINFO_EXTENSION);
							if (!in_array($ext, $this->skipNames)) {
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
                duplicator_log("log:class.zip=>new open handle {$this->zipArchive->numFiles}");
                $this->zipArchive->close();
                $this->zipArchive->open($this->zipFilePath, ZIPARCHIVE::CREATE);
                $this->limitItems = 0;
                duplicator_fcgi_flush();
            }

            @closedir($dh);
        } 
		
		catch (Exception $e) {
            duplicator_log("log:class.zip=>runtime error: " . $e);
        }
    }
}

?>