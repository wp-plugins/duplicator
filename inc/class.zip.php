<?php 
class Duplicator_Zip {
	protected $zip;
	protected $root;
	protected $ignored_names;
	 
	function __construct($file, $folder, $ignored=null) {
		duplicator_log("log:class.zip=>started");
		$this->zip = new ZipArchive();
		$this->ignored_names = is_array($ignored) ? $ignored : $ignored ? array($ignored) : array();
		if ($this->zip->open($file, ZIPARCHIVE::CREATE)=== TRUE) {
			duplicator_log("log:class.zip=>opened");
		} else {
			$err = "log:class.zip=>cannot open <$file>";
			duplicator_log($err);
			throw new Exception($err);
		}
		
		$folder = substr($folder, -1) == '/' ? substr($folder, 0, strlen($folder)-1) : $folder;
		if(strstr($folder, '/')) {
			$this->root = substr($folder, 0, strrpos($folder, '/')+1);
			$folder = substr($folder, strrpos($folder, '/')+1);
		}
			
		$this->zip($folder);
		
		$msg = 'log:class.zip=>archive info: ' . print_r($this->zip, true);
		duplicator_log($msg);
		$this->zip->close();
		duplicator_log("log:class.zip=>ended");
	}
	  
	function zip($folder, $parent=null) {
		try {
			$full_path = $this->root.$parent . $folder;
			$zip_path  = $parent . $folder;
			$this->zip->addEmptyDir($zip_path);
			$dir = new DirectoryIterator($full_path);
			foreach($dir as $file) {
				if(!$file->isDot()) {
					$filename = $file->getFilename();
					if(!in_array($filename, $this->ignored_names)) {
						if($file->isDir()) {
							$this->zip($filename, $zip_path.'/');
						}
						else {
							$this->zip->addFile($full_path.'/'.$filename, $zip_path.'/'.$filename);
						}
					}
				}
			}
		} catch(Exception $e) {
			duplicator_log("log:class.zip=>runtime error: " . $e);
		}
	}
}
?>