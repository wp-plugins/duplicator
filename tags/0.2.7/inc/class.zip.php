<?php 

class Duplicator_Zip 
{
	protected $limit = DUPLICATOR_ZIP_FILE_POOL;
	protected $zipArchive;
	protected $rootFolder;
	protected $skipNames;
	protected $zipFileName;
	private   $limitItems = 0;
	 
	function __construct($file, $folder, $ignored=null) 
	{
		try 
		{
			duplicator_log("log:class.zip=>started");
			duplicator_log("log:class.zip=>archive file:   {$file}");
			duplicator_log("log:class.zip=>archive folder: {$folder}");
			
			$this->zipFileName = $file;
			$this->zipArchive  = new ZipArchive();
			$this->skipNames   = is_array($ignored) ? $ignored : $ignored ? array($ignored) : array();
			
			if ($this->zipArchive->open($this->zipFileName, ZIPARCHIVE::CREATE) === TRUE) 
			{
				duplicator_log("log:class.zip=>opened");
			} 
			else 
			{
				$err = "log:class.zip=>cannot open <{$this->zipFileName}>";
				duplicator_log($err);
				throw new Exception($err);
			}
			
			$folder = substr($folder, -1) == '/' ? substr($folder, 0, strlen($folder)-1) : $folder;
			if(strstr($folder, '/')) 
			{
				$this->rootFolder = substr($folder, 0, strrpos($folder, '/')+1);
				$folder = substr($folder, strrpos($folder, '/')+1);
			}
			
			//Recursive Call
			$this->zipDir($folder);
			
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
	
	function zipDir($folder, $parent=null) {
		try 
		{
			$full_path = $this->rootFolder.$parent . $folder;
			$zip_path  = $parent . $folder;
			$this->zipArchive->addEmptyDir($zip_path);
			$dir = new DirectoryIterator($full_path);
			foreach($dir as $file) 
			{
				if(!$file->isDot()) 
				{
					$filename = $file->getFilename();
					if(!in_array($filename, $this->skipNames)) 
					{
						if($file->isDir()) 
						{
							$this->zipDir($filename, $zip_path.'/');
						}
						else 
						{
							$this->zipArchive->addFile($full_path.'/'.$filename, $zip_path.'/'.$filename);
						}
						$this->limitItems++;
					}
				}
			}
			
			//Check if were over our count
			if($this->limitItems > $this->limit) 
			{
				duplicator_log("log:class.zip=>new open handle {$this->zipArchive->numFiles}");
				$this->zipArchive->close();
				$this->zipArchive->open($this->zipFileName, ZIPARCHIVE::CREATE);
				$this->limitItems = 0;
			}
		} 
		catch(Exception $e) 
		{
			duplicator_log("log:class.zip=>runtime error: " . $e);
		}
	}
}
?>