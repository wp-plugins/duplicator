<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/**
 * @copyright 2018 Snap Creek LLC
 * Class for all IO operations
 */

// Exit if accessed directly
if (! defined('DUPLICATOR_VERSION')) exit;

class DUP_IO
{
    /**
     * Safely deletes a file
     *
     * @param string $file	The full filepath to the file
     *
     * @return TRUE on success or if file does not exist. FALSE on failure
     */
    public static function deleteFile($file)
	{
		if (file_exists($file)) {
			if (@unlink($file) === false) {
				DUP_Log::Info("Duplicator could not delete file: '{$file}'");
				return false;
			}
		}
		return true;
	}

	/**
     * Removes a directory recursively except for the root of a WP Site
     *
     * @param string $directory	The full filepath to the directory to remove
     *
     * @return TRUE on success FALSE on failure
     */
	public static function deleteTree($directory)
	{
		$success = true;

        if(!file_exists("{$directory}/wp-config.php")) {
            $filenames = array_diff(scandir($directory), array('.', '..'));

            foreach ($filenames as $filename) {
                if (is_dir("$directory/$filename")) {
                    $success = self::deleteTree("$directory/$filename");
                } else {
                    $success = @unlink("$directory/$filename");
                }

                if ($success === false) {
					break;
                }
            }
        } else {
            return false;
        }

		return $success && @rmdir($directory);
	}

    /**
     * Safely copies a file to a directory
     *
     * @param string $source_file       The full filepath to the file to copy
     * @param string $dest_dir			The full path to the destination directory were the file will be copied
     * @param string $delete_first		Delete file before copying the new one
     *
     *  @return TRUE on success or if file does not exist. FALSE on failure
     */
    public static function copyFile($source_file, $dest_dir, $delete_first = false)
    {
        //Create directory
        if (file_exists($dest_dir) == false)
        {
            if (wp_mkdir_p($dest_dir) === false) {
                return false;
            }
        }

        //Remove file with same name before copy
        $filename = basename($source_file);
        $dest_filepath = $dest_dir . "/$filename";
        if($delete_first)
        {
            self::deleteFile($dest_filepath);
        }

        return copy($source_file, $dest_filepath);
    }

    /**
     * Prepends data to an existing file
     *
     * @param string $file      The full file path to the file
     * @param string $content	The content to prepend to the file
     *
     * @return TRUE on success or if file does not exist. FALSE on failure
     */
    public static function fwritePrepend($file, $prepend)
	{
		if (!file_exists($file) || !is_writable($file)) {
            return false;
        }

        $handle     = fopen($file, "r+");
        $len        = strlen($prepend);
        $final_len  = filesize($file) + $len;
        $cache_old  = fread($handle, $len);
        rewind($handle);
        $i = 1;
        while (ftell($handle) < $final_len) {
          fwrite($handle, $prepend);
          $prepend = $cache_old;
          $cache_old = fread($handle, $len);
          fseek($handle, $i * $len);
          $i++;
        }
	}
}
