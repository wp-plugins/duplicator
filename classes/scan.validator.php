<?php

/*Recursivly scans a directory and finds all sym-links and unreadable files */
class DUP_ScanValidator 
{
	public $FileCount = 0;
	public $DirCount = 0;
	
	/*If the MaxFiles or MaxDirs limit is reached then true */
	public $LimitReached = false;
	
	/*The maximum count of files before the recursive function stops */
	public $MaxFiles = 1000000;
	
	/*The maximum count of directories before the recursive function stops */
	public $MaxDirs = 75000;
	
	public $Recursion = true;
	
	/*Stores a list of symbolic link files */
	public $SymLinks = array();
	
	/*Stores a list of files unreadable by PHP */
	public $Unreadable = array();
	
	public function Run($dir, &$results = array())
	{
		//Stop Recursion if Max search is reached
		if ($this->FileCount > $this->MaxFiles || $this->DirCount > $this->MaxDirs) 
		{	
			$this->LimitReached = true;
			return $results;
		}

		$files = @scandir($dir);
		if (is_array($files)) 
		{
			foreach($files as $key => $value)
			{
				$path = realpath($dir.DIRECTORY_SEPARATOR.$value);
				if ($path) {
					//Files
					if(!is_dir($path)) {
						if (!is_readable($path))
						{
							$results[] = $path;
							$this->Unreadable[] = $path;
						} 
						else if ($this->_is_link($path)) 
						{
							$results[] = $path;
							$this->SymLinks[] = $path;
						}
						$this->FileCount++;
					} 
					//Dirs
					else if($value != "." && $value != "..") 
					{
						if (! $this->_is_link($path)  && $this->Recursion) 
						{
							$this->Run($path, $results);
						}

						if (!is_readable($path))
						{
							 $results[] = $path;
							 $this->Unreadable[] = $path;
						}
						else if ($this->_is_link($path)) {
							$results[] = $path;
							$this->SymLinks[] = $path;
						}
						$this->DirCount++;
					}
				}
			}
		}
		return $this;
	}

	//Supports windows and linux
	private function _is_link($target) 
	{ 
		if (defined('PHP_WINDOWS_VERSION_BUILD')) {
			if(file_exists($target) && @readlink($target) != $target) {
				return true;
			}
		} elseif (is_link($target)) {
			return true;
		}
		return false;
	}
}
