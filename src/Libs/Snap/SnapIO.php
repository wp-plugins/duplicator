<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Libs\Snap;

use Error;
use Exception;

class SnapIO
{
    // Real upper bound of a signed int is 214748364.
    // The value chosen below, makes sure we have a buffer of ~4.7 million.
    const FILE_SIZE_LIMIT_32BIT = 1900000000;
    const FWRITE_CHUNK_SIZE     = 4096; // bytes

    /**
     * Return include file in a string
     *
     * @param string               $path     inclue path
     * @param array<string, mixed> $args     array key/val where key is the var name in include
     * @param bool                 $required if true is required
     *
     * @return string
     *
     * @throws Exception // thorw exception if is $required and file can't be read
     */
    public static function getInclude($path, $args = array(), $required = true)
    {
        if (!is_readable($path)) {
            if ($required) {
                throw new Exception('Can\'t read required file ' . $path);
            } else {
                return '';
            }
        }

        foreach ($args as $var => $value) {
            ${$var} = $value;
        }

        ob_start();
        if ($required) {
            require($path);
        } else {
            include($path);
        }
        return ob_get_clean();
    }

    /**
     * Copy file
     *
     * @param string  $source            source path
     * @param string  $dest              detination path
     * @param boolean $overwriteIfExists if true and file exists the file is overwritten
     *
     * @return boolean Returns true on success or false on failure.
     */
    public static function copy($source, $dest, $overwriteIfExists = true)
    {
        if (file_exists($dest)) {
            if ($overwriteIfExists) {
                self::rm($dest);
            } else {
                return false;
            }
        }
        return copy($source, $dest);
    }

    /**
     * Copy part of file, if offset is 0 anf to file exists is truncated
     *
     * @param string|resource $from   file name or resource
     * @param string|resource $to     file name or resource
     * @param int<0, max>     $offset copy offset
     * @param int<-1, max>    $length copy if -1 copy ot the end of file
     *
     * @return bool true on success or false on fail.
     */
    public static function copyFilePart($from, $to, $offset = 0, $length = -1)
    {
        $closeFrom  = false;
        $closeTo    = false;
        $fromStream = null;
        $toStream   = null;
        if (is_resource($from)) {
            $fromStream = $from;
        } else {
            if (!is_file((string) $from)) {
                return false;
            }
            if (($fromStream = self::fopen($from, 'r')) === false) {
                return false;
            }
            $closeFrom = true;
        }
        if (is_resource($to)) {
            $toStream = $to;
        } else {
            $mode = ($offset == 0 ? 'w+' : 'c+');
            if (($toStream = SnapIO::fopen($to, $mode)) === false) {
                return false;
            }
            $closeTo = true;
        }
        if ($offset === 0) {
            if (ftruncate($toStream, 0) === false) {
                return false;
            }
        }
        if (fseek($toStream, $offset) === -1) {
            return false;
        }
        if ($closeFrom && is_resource($fromStream)) {
            fclose($fromStream);
        }
        if ($closeTo && is_resource($toStream)) {
            fclose($toStream);
        }
        return (stream_copy_to_stream($fromStream, $toStream, ($length < 0 ? null : $length), $offset) !== false);
    }

    /**
     * Copy recursive folder content
     *
     * @param string $source source path
     * @param string $dest   detination path
     *
     * @return boolean Returns true on success or false on failure.
     */
    public static function rcopy($source, $dest)
    {
        if (!is_readable($source)) {
            return false;
        }

        if (is_dir($source)) {
            if (!file_exists($dest)) {
                if (!self::mkdir($dest)) {
                    return false;
                }
            }

            if (($handle = opendir($source)) == false) {
                return false;
            }

            while ($file = readdir($handle)) {
                if ($file == "." || $file == "..") {
                    continue;
                }

                if (!self::rcopy($source . '/' . $file, $dest . '/' . $file)) {
                    closedir($handle);
                    return false;
                }
            }
            closedir($handle);
            return true;
        } else {
            return copy($source, $dest);
        }
    }

    /**
     * Untrailingslashit path
     *
     * @param string $path file path
     *
     * @return string
     */
    public static function untrailingslashit($path)
    {
        return rtrim($path, '/\\');
    }

    /**
     * Trailingslashit path
     *
     * @param string $path file path
     *
     * @return string
     */
    public static function trailingslashit($path)
    {
        return self::untrailingslashit($path) . '/';
    }

    /**
     * Normalize path
     *
     * @param string  $path file path
     * @param boolean $real if true apply realpath function
     *
     * @return string
     */
    public static function safePath($path, $real = false)
    {
        if ($real) {
            if (($res = realpath($path)) === false) {
                $res = $path;
            }
        } else {
            $res = $path;
        }
        return self::normalizePath($res);
    }

    /**
     * Untrailingslashit and normalize path
     *
     * @param string  $path file path
     * @param boolean $real if true apply realpath function
     *
     * @return string
     */
    public static function safePathUntrailingslashit($path, $real = false)
    {
        if ($real) {
            if (($res = realpath($path)) === false) {
                $res = $path;
            }
        } else {
            $res = $path;
        }
        return rtrim(self::normalizePath($res), '/');
    }

    /**
     * Trailingslashit and normalize path
     *
     * @param string  $path file path
     * @param boolean $real if true apply realpath function
     *
     * @return string
     */
    public static function safePathTrailingslashit($path, $real = false)
    {
        return self::safePathUntrailingslashit($path, $real) . '/';
    }

    /**
     * Remove file path
     *
     * @param string $file path
     *
     * @return bool Returns TRUE  on success or  FALSE on failure.
     */
    public static function unlink($file)
    {
        try {
            if (!file_exists($file)) {
                return true;
            }
            if (!function_exists('unlink') || is_dir($file)) {
                return false;
            }
            self::chmod($file, 'u+rw');
            return @unlink($file);
        } catch (Exception $e) {
            return false;
        } catch (Error $e) {
            return false;
        }
    }

    /**
     * Rename file from old name to new name
     *
     * @param string $oldname        path
     * @param string $newname        path
     * @param bool   $removeIfExists if true remove exists file
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public static function rename($oldname, $newname, $removeIfExists = false)
    {
        try {
            if (!file_exists($oldname) || !function_exists('rename')) {
                return false;
            }

            if ($removeIfExists && file_exists($newname)) {
                if (!self::rrmdir($newname)) {
                    return false;
                }
            }
            return @rename($oldname, $newname);
        } catch (Exception $e) {
            return false;
        } catch (Error $e) {
            return false;
        }
    }

    /**
     * Open file
     *
     * @param string  $filepath     File path
     * @param string  $mode         The mode parameter specifies the type of access you require to the stream.
     * @param boolean $throwOnError thorw exception on error
     *
     * @return boolean|resource Returns a file pointer resource on success, or false on failure
     */
    public static function fopen($filepath, $mode, $throwOnError = true)
    {
        if (strlen($filepath) > PHP_MAXPATHLEN) {
            throw new Exception('Skipping a file that exceeds allowed max path length [' . PHP_MAXPATHLEN . ']. File: ' . $filepath);
        }

        if (SnapString::startsWith($mode, 'w') || SnapString::startsWith($mode, 'c') || file_exists($filepath)) {
            $file_handle = @fopen($filepath, $mode);
        } else {
            if ($throwOnError) {
                throw new Exception("$filepath doesn't exist");
            } else {
                return false;
            }
        }

        if (!is_resource($file_handle)) {
            if ($throwOnError) {
                throw new Exception("Error opening $filepath");
            } else {
                return false;
            }
        } else {
            return $file_handle;
        }
    }

    /**
     * Touch file
     *
     * @param string $filepath File path
     * @param int    $time     The touch time. If time is not supplied, the current system time is used.
     *
     * @return bool Returns true on success or false on failure.
     */
    public static function touch($filepath, $time = null)
    {
        if (!function_exists('touch')) {
            return false;
        }

        if ($time === null) {
            $time = time();
        }
        return @touch($filepath, $time);
    }

    /**
     * Remove folder
     *
     * @param string  $dirname   dir path
     * @param boolean $mustExist if true and folder don't esist thorw error
     *
     * @return void
     */
    public static function rmdir($dirname, $mustExist = false)
    {
        if (file_exists($dirname)) {
            self::chmod($dirname, 'u+rwx');
            if (self::rrmdir($dirname) === false) {
                throw new Exception("Couldn't remove {$dirname}");
            }
        } elseif ($mustExist) {
            throw new Exception("{$dirname} doesn't exist");
        }
    }

    /**
     * Remove file
     *
     * @param string  $filepath  file path
     * @param boolean $mustExist if true and folder don't esist thorw error
     *
     * @return void
     */
    public static function rm($filepath, $mustExist = false)
    {
        if (file_exists($filepath)) {
            self::chmod($filepath, 'u+rw');
            if (@unlink($filepath) === false) {
                throw new Exception("Couldn't remove {$filepath}");
            }
        } elseif ($mustExist) {
            throw new Exception("{$filepath} doesn't exist");
        }
    }

    /**
     * string string in file
     *
     * @param resource $handle file handle
     * @param string   $string fwrite string
     *
     * @return int bytes written
     */
    public static function fwrite($handle, $string)
    {
        $bytes_written = @fwrite($handle, $string);

        if ($bytes_written != strlen($string)) {
            throw new Exception('Error writing all bytes to file.');
        } else {
            return $bytes_written;
        }
    }

    /**
     * Wrinte file in chunk mode. For big data.
     *
     * @param resource $handle  file handle
     * @param string   $content fwrite string
     *
     * @return int bytes written
     *
     * @throws Exception
     */
    public static function fwriteChunked($handle, $content)
    {
        if (strlen($content) == 0) {
            return 0;
        }

        $pieces  = str_split($content, self::FWRITE_CHUNK_SIZE);
        $written = 0;

        foreach ($pieces as $piece) {
            if (($fwResult = @fwrite($handle, $piece, self::FWRITE_CHUNK_SIZE)) === false) {
                throw new Exception('Error writing to file.');
            }
            $written += $fwResult;
        }

        if ($written != strlen($content)) {
            throw new Exception('Error writing all bytes to file.');
        }

        return $written;
    }

    /**
     * Append file $from to file $to, if $to file don't exits create it.
     * In case of error throw exceptions.
     *
     * @param string $from file path
     * @param string $to   file path
     *
     * @return int writte bytes
     */
    public static function appendFileToFile($from, $to)
    {
        try {
            $written = 0;
            $fromHd  = false;
            $toHd    = false;

            if (!file_exists($from) || !is_readable($from)) {
                throw new Exception('File: ' . $from . ' don\'t exists os isn\'t readable');
            }

            if (file_exists($to) && !is_writable($to)) {
                throw new Exception('File: ' . $to . ' isn\'t writeable');
            }

            if (($fromHd = @fopen($from, "rb")) === false) {
                throw new Exception('Could not open file: ' . $from);
            }

            if (($toHd = @fopen($to, "ab")) === false) {
                throw new Exception('Could not open file: ' . $to);
            }

            if (($fromStat = fstat($fromHd)) == false) {
                throw new Exception('Can\t stat file: ' . $from);
            }

            while ($buffer = fread($fromHd, self::FWRITE_CHUNK_SIZE)) {
                if (($fwResult = @fwrite($toHd, $buffer)) === false) {
                    throw new Exception('Error writing to file ' . $to);
                }
                $written += $fwResult;
            }

            if ($written != $fromStat['size']) {
                throw new Exception('Error on file append, written bytes ' . $written . ' expected ' . $fromStat['size']);
            }
        } catch (Exception $e) {
            if ($fromHd !== false) {
                fclose($fromHd);
            }
            if ($toHd !== false) {
                fclose($toHd);
            }
            throw $e;
        }

        fclose($fromHd);
        fclose($toHd);

        return $written;
    }

    /**
     * File get
     *
     * @param resource $handle file handle
     * @param int      $length max num bytes
     *
     * @return string
     */
    public static function fgets($handle, $length)
    {
        $line = fgets($handle, $length);

        if ($line === false) {
            throw new Exception('Error reading line.');
        }

        return $line;
    }

    /**
     * File close
     *
     * @param resource $handle            file handle
     * @param boolean  $exception_on_fail if true thorw exception on fail
     *
     * @return void
     */
    public static function fclose($handle, $exception_on_fail = true)
    {
        if ((@fclose($handle) === false) && $exception_on_fail) {
            throw new Exception("Error closing file");
        }
    }

    /**
     * Exec a flock, thow exception on failure
     *
     * @param resource $handle    file handle
     * @param int      $operation flock openration
     *
     * @return void
     */
    public static function flock($handle, $operation)
    {
        if (@flock($handle, $operation) === false) {
            throw new Exception("Error locking file");
        }
    }

    /**
     * Returns the current position of the file read/write pointer
     * throw exception on failure
     *
     * @param resource $file_handle file handle
     *
     * @return int
     */
    public static function ftell($file_handle)
    {
        $position = @ftell($file_handle);

        if ($position === false) {
            throw new Exception("Couldn't retrieve file offset.");
        } else {
            return $position;
        }
    }

    /**
     * Safely remove a directory and recursively files and directory upto multiple sublevels
     *
     * @param string $path The full path to the directory to remove
     *
     * @return bool Returns true if all content was removed
     */
    public static function rrmdir($path)
    {
        if (is_dir($path)) {
            if (($dh = opendir($path)) === false) {
                return false;
            }
            while (($object = readdir($dh)) !== false) {
                if ($object == "." || $object == "..") {
                    continue;
                }
                if (!self::rrmdir($path . "/" . $object)) {
                    closedir($dh);
                    return false;
                }
            }
            closedir($dh);
            return @rmdir($path);
        } else {
            if (is_writable($path)) {
                return @unlink($path);
            } else {
                return false;
            }
        }
    }

    /**
     * Return files size, throw eception on failure
     *
     * @param string $filename file path
     *
     * @return int
     */
    public static function filesize($filename)
    {
        $file_size = @filesize($filename);

        if ($file_size === false) {
            throw new Exception("Error retrieving file size of $filename");
        }

        return $file_size;
    }

    /**
     * Fseek on file, throw exception on failure
     *
     * @param resource $handle file handle
     * @param int      $offset The offset.
     * @param int      $whence whence values are: SEEK_SET
     *                         - Set position equal to offset bytes. SEEK_CUR
     *                         - Set position to current location plus offset. SEEK_END
     *                         - Set position to end-of-file plus offset.
     *
     * @return void
     */
    public static function fseek($handle, $offset, $whence = SEEK_SET)
    {
        $ret_val = @fseek($handle, $offset, $whence);

        if ($ret_val !== 0) {
            $filepath = stream_get_meta_data($handle);
            $filepath = $filepath["uri"];
            $filesize = self::filesize($filepath);

            if (
                abs($offset) > self::FILE_SIZE_LIMIT_32BIT ||
                $filesize > self::FILE_SIZE_LIMIT_32BIT ||
                ($offset <= 0 && ($whence == SEEK_SET || $whence == SEEK_END))
            ) {
                //This check is not strict, but in most cases 32 Bit PHP will be the issue
                throw new Snap32BitSizeLimitException("Trying to seek on a file beyond the capability of 32 bit PHP. offset=$offset filesize=$filesize");
            } else {
                throw new Exception("Error seeking to file offset $offset. Retval = $ret_val");
            }
        }
    }

    /**
     * Gets file modification time
     *
     * @param string $filename file path
     *
     * @return int|false the time the file was last modified, or false on failure.
     *                   The time is returned as a Unix timestamp, which is suitable for the date function
     */
    public static function filemtime($filename)
    {
        $mtime = filemtime($filename);

        if ($mtime === false) {
            throw new Exception("Cannot retrieve last modified time of $filename");
        }

        return $mtime;
    }

    /**
     * File put content, thorw exception on failure
     *
     * @param string $filename file path
     * @param mixed  $data     The data to write. Can be either a string, an array or a stream resource.

     * @return bool
     */
    public static function filePutContents($filename, $data)
    {
        if (($dirFile = realpath(dirname($filename))) === false) {
            throw new Exception('FILE ERROR: put_content for file ' . $filename . ' failed [realpath fail]');
        }
        if (!is_dir($dirFile)) {
            throw new Exception('FILE ERROR: put_content for file ' . $filename . ' failed [dir ' . $dirFile . ' doesn\'t exist]');
        }
        if (!is_writable($dirFile)) {
            throw new Exception('FILE ERROR: put_content for file ' . $filename . ' failed [dir ' . $dirFile . ' exists but isn\'t writable]');
        }
        $realFileName = $dirFile . basename($filename);
        if (file_exists($realFileName) && !is_writable($realFileName)) {
            throw new Exception('FILE ERROR: put_content for file ' . $filename . ' failed [file exist ' . $realFileName . ' but isn\'t writable');
        }
        if (file_put_contents($filename, $data) === false) {
            throw new Exception('FILE ERROR: put_content for file ' . $filename . ' failed [Couldn\'t write data to ' . $realFileName . ']');
        }
        return true;
    }

    /**
     * this function make a chmod only if the are different from perms input and if chmod function is enabled
     *
     * this function handles the variable MODE in a way similar to the chmod of lunux
     * So the MODE variable can be
     * 1) an octal number (0755)
     * 2) a string that defines an octal number ("644")
     * 3) a string with the following format [ugoa]*([-+=]([rwx]*)+
     *
     * examples
     * u+rw         add read and write at the user
     * u+rw,uo-wx   add read and write ad the user and remove wx at groupd and other
     * a=rw         is equal at 666
     * u=rwx,go-rwx is equal at 700
     *
     * @param string     $file file path
     * @param int|string $mode permission mode
     *
     * @return boolean
     */
    public static function chmod($file, $mode)
    {
        if (!file_exists($file)) {
            return false;
        }

        $octalMode = 0;

        if (is_int($mode)) {
            $octalMode = $mode;
        } elseif (is_numeric($mode)) {
            $octalMode = intval((($mode[0] === '0' ? '' : '0') . $mode), 8);
        } elseif (is_string($mode) && preg_match_all('/(a|[ugo]{1,3})([-=+])([rwx]{1,3})/', $mode, $gMatch, PREG_SET_ORDER)) {
            if (!function_exists('fileperms')) {
                return false;
            }

            // start by file permission
            $octalMode = (fileperms($file) & 0777);

            foreach ($gMatch as $matches) {
                // [ugo] or a = ugo
                $group = $matches[1];
                if ($group === 'a') {
                    $group = 'ugo';
                }
                // can be + - =
                $action = $matches[2];
                // [rwx]
                $gPerms = $matches[3];

                // reset octal group perms
                $octalGroupMode = 0;

                // Init sub perms
                $subPerm  = 0;
                $subPerm += strpos($gPerms, 'x') !== false ? 1 : 0; // mask 001
                $subPerm += strpos($gPerms, 'w') !== false ? 2 : 0; // mask 010
                $subPerm += strpos($gPerms, 'r') !== false ? 4 : 0; // mask 100

                $ugoLen = strlen($group);

                if ($action === '=') {
                    // generate octal group permsissions and ugo mask invert
                    $ugoMaskInvert = 0777;
                    for ($i = 0; $i < $ugoLen; $i++) {
                        switch ($group[$i]) {
                            case 'u':
                                $octalGroupMode = $octalGroupMode | $subPerm << 6; // mask xxx000000
                                $ugoMaskInvert  = $ugoMaskInvert & 077;
                                break;
                            case 'g':
                                $octalGroupMode = $octalGroupMode | $subPerm << 3; // mask 000xxx000
                                $ugoMaskInvert  = $ugoMaskInvert & 0707;
                                break;
                            case 'o':
                                $octalGroupMode = $octalGroupMode | $subPerm; // mask 000000xxx
                                $ugoMaskInvert  = $ugoMaskInvert & 0770;
                                break;
                        }
                    }
                    // apply = action
                    $octalMode = $octalMode & ($ugoMaskInvert | $octalGroupMode);
                } else {
                    // generate octal group permsissions
                    for ($i = 0; $i < $ugoLen; $i++) {
                        switch ($group[$i]) {
                            case 'u':
                                $octalGroupMode = $octalGroupMode | $subPerm << 6; // mask xxx000000
                                break;
                            case 'g':
                                $octalGroupMode = $octalGroupMode | $subPerm << 3; // mask 000xxx000
                                break;
                            case 'o':
                                $octalGroupMode = $octalGroupMode | $subPerm; // mask 000000xxx
                                break;
                        }
                    }
                    // apply + or - action
                    switch ($action) {
                        case '+':
                            $octalMode = $octalMode | $octalGroupMode;
                            break;
                        case '-':
                            $octalMode = $octalMode & ~$octalGroupMode;
                            break;
                    }
                }
            }
        } else {
            return true;
        }

        // if input permissions are equal at file permissions return true without performing chmod
        if (function_exists('fileperms') && $octalMode === (fileperms($file) & 0777)) {
            return true;
        }

        if (!function_exists('chmod')) {
            return false;
        }

        return @chmod($file, $octalMode);
    }

    /**
     * Return file perms in string
     *
     * @param int|string $perms permssions
     *
     * @return string|false false if fail
     */
    public static function permsToString($perms)
    {
        if (is_int($perms)) {
            return decoct($perms);
        } elseif (is_numeric($perms)) {
            return ($perms[0] === '0' ? '' : '0') . $perms;
        } elseif (is_string($perms)) {
            return $perms;
        } else {
            return false;
        }
    }

    /**
     * this function creates a folder if it does not exist and performs a chmod.
     * it is different from the normal mkdir function to which an umask is applied to the input permissions.
     *
     * this function handles the variable MODE in a way similar to the chmod of lunux
     * So the MODE variable can be
     * 1) an octal number (0755)
     * 2) a string that defines an octal number ("644")
     * 3) a string with the following format [ugoa]*([-+=]([rwx]*)+
     *
     * @param string     $path      folder path
     * @param int|string $mode      mode permissions
     * @param bool       $recursive Allows the creation of nested directories specified in the pathname. Default to false.
     * @param resource   $context   not used for windows bug
     *
     * @return boolean bool TRUE on success or FALSE on failure.
     *
     * @todo check recursive true and multiple chmod
     */
    public static function mkdir($path, $mode = 0777, $recursive = false, $context = null)
    {
        if (strlen($path) > PHP_MAXPATHLEN) {
            throw new Exception('Skipping a file that exceeds allowed max path length [' . PHP_MAXPATHLEN . ']. File: ' . $path);
        }

        if (!file_exists($path)) {
            if (!function_exists('mkdir')) {
                return false;
            }
            if (!@mkdir($path, 0777, $recursive)) {
                return false;
            }
        }

        return self::chmod($path, $mode);
    }

    /**
     * this function call snap mkdir if te folder don't exists od don't have write or exec permissions
     *
     * this function handles the variable MODE in a way similar to the chmod of lunux
     * The mode variable can be set to have more flexibility but not giving the user write and read and exec permissions doesn't make much sense
     *
     * @param string     $path      folder path
     * @param int|string $mode      mode permissions
     * @param bool       $recursive Allows the creation of nested directories specified in the pathname. Default to false.
     * @param resource   $context   not used for windows bug
     *
     * @return boolean bool TRUE on success or FALSE on failure.
     */
    public static function dirWriteCheckOrMkdir($path, $mode = 'u+rwx', $recursive = false, $context = null)
    {
        if (!file_exists($path)) {
            return self::mkdir($path, $mode, $recursive, $context);
        } elseif (!is_writable($path) || (function_exists('is_executable') && !is_executable($path))) {
            return self::chmod($path, $mode);
        } else {
            return true;
        }
    }

    /**
     * from wordpress function wp_is_stream
     *
     * @param string $path The resource path or URL.
     *
     * @return bool True if the path is a stream URL.
     */
    public static function isStream($path)
    {
        $scheme_separator = strpos($path, '://');

        if (false === $scheme_separator) {
            // $path isn't a stream
            return false;
        }

        $stream = substr($path, 0, $scheme_separator);

        return in_array($stream, stream_get_wrappers(), true);
    }

    /**
     * From Wordpress function: wp_mkdir_p
     *
     * Recursive directory creation based on full path.
     *
     * Will attempt to set permissions on folders.
     *
     * @param string $target Full path to attempt to create.
     *
     * @return bool Whether the path was created. True if path already exists.
     */
    public static function mkdirP($target)
    {
        $wrapper = null;

        // Strip the protocol.
        if (self::isStream($target)) {
            list( $wrapper, $target ) = explode('://', $target, 2);
        }

        // From php.net/mkdir user contributed notes.
        $target = str_replace('//', '/', $target);

        // Put the wrapper back on the target.
        if ($wrapper !== null) {
            $target = $wrapper . '://' . $target;
        }

        /*
         * Safe mode fails with a trailing slash under certain PHP versions.
         * Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
         */
        $target = rtrim($target, '/');
        if (empty($target)) {
            $target = '/';
        }

        if (file_exists($target)) {
            return @is_dir($target);
        }

        // We need to find the permissions of the parent folder that exists and inherit that.
        $target_parent = dirname($target);
        while ('.' != $target_parent && !is_dir($target_parent) && dirname($target_parent) !== $target_parent) {
            $target_parent = dirname($target_parent);
        }

        // Get the permission bits.
        if ($stat = @stat($target_parent)) {
            $dir_perms = $stat['mode'] & 0007777;
        } else {
            $dir_perms = 0777;
        }

        if (@mkdir($target, $dir_perms, true)) {
            /*
             * If a umask is set that modifies $dir_perms, we'll have to re-set
             * the $dir_perms correctly with chmod()
             */
            if ($dir_perms != ( $dir_perms & ~umask() )) {
                $folder_parts = explode('/', substr($target, strlen($target_parent) + 1));
                for ($i = 1, $c = count($folder_parts); $i <= $c; $i++) {
                    @chmod($target_parent . '/' . implode('/', array_slice($folder_parts, 0, $i)), $dir_perms);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * This function returns the relative path to mainPath
     *
     * @param string $path     file path
     * @param string $mainPath main path
     * @param bool   $real     if true check real path
     *
     * @return bool|string  false if path isn't a sub path of main path or return the relative path
     */
    public static function getRelativePath($path, $mainPath, $real = false)
    {
        if (strlen($mainPath) == 0) {
            return ltrim(self::safePathUntrailingslashit($path, $real), '/');
        }

        $safePath     = self::safePathUntrailingslashit($path, $real);
        $safeMainPath = self::safePathUntrailingslashit($mainPath, $real);

        if ($safePath === $safeMainPath) {
            return '';
        } elseif (strpos($safePath, self::trailingslashit($safeMainPath)) === 0) {
            return ltrim(substr($safePath, strlen($safeMainPath)), '/');
        } else {
            return false;
        }
    }

    /**
     * Check if path is child of mainPath
     *
     * @param string  $path         file path
     * @param string  $mainPath     main path
     * @param boolean $reverseCheck if true check if path is child of mainpath and  if mainPash is child of path
     * @param boolean $trueIfEquals if paths are equals and is true return true else false
     *
     * @return boolean
     */
    public static function isChildPath($path, $mainPath, $reverseCheck = false, $trueIfEquals = true)
    {
        if (strlen($mainPath) == 0) {
            return true;
        }

        if ($reverseCheck && strlen($path) == 0) {
            return true;
        }

        $safePath     = self::safePathUntrailingslashit($path);
        $safeMainPath = self::safePathUntrailingslashit($mainPath);

        if ($safePath === $safeMainPath) {
            return $trueIfEquals;
        } elseif (strpos($safePath, self::trailingslashit($safeMainPath)) === 0) {
            return true;
        } elseif ($reverseCheck && strpos($safeMainPath, self::trailingslashit($safePath)) === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Return sorted array by subfolders count
     *
     * @param string[] $paths         paths lists
     * @param boolean  $childsFirst   if true put childs before parents
     * @param boolean  $maintainIndex if true maintain array indexes
     * @param boolean  $sortKeys      if true sort by keys
     *
     * @return string[]
     */
    public static function sortBySubfoldersCount($paths, $childsFirst = false, $maintainIndex = false, $sortKeys = false)
    {
        if ($sortKeys) {
            $function = 'uksort';
        } elseif ($maintainIndex) {
            $function = 'uasort';
        } else {
            $function = 'usort';
        }

        $function($paths, function ($a, $b) use ($childsFirst) {
            $lenA = count(preg_split('/[\\\\\/]+/', $a));
            $lenB = count(preg_split('/[\\\\\/]+/', $b));
            if ($lenA === $lenB) {
                return strcmp($a, $b) * ($childsFirst ? -1 : 1);
            } elseif ($lenA > $lenB) {
                return ($childsFirst ? -1 : 1);
            } else {
                return ($childsFirst ? 1 : -1);
            }
        });
        return $paths;
    }

    /**
     * from wp_normalize_path
     *
     * @param string $path Path to normalize.
     *
     * @return string Normalized path.
     */
    public static function normalizePath($path)
    {
        $wrapper = '';
        if (self::isStream($path)) {
            list( $wrapper, $path ) = explode('://', $path, 2);
            $wrapper               .= '://';
        }

        // Standardise all paths to use /
        $path = str_replace('\\', '/', $path);

        // Replace multiple slashes down to a singular, allowing for network shares having two slashes.
        $path = preg_replace('|(?<=.)/+|', '/', $path);
        if (strpos($path, '//') === 0) {
            $path = substr($path, 1);
        }

        // Windows paths should uppercase the drive letter
        if (':' === substr($path, 1, 1)) {
            $path = ucfirst($path);
        }

        return $wrapper . $path;
    }

    /**
     * Get common parent path from given paths
     *
     * @param string[] $paths list of paths
     *
     * @return string common parent path
     */
    public static function getCommonPath($paths = array())
    {
        if (empty($paths)) {
            return '';
        } if (!is_array($paths)) {
            $paths = array($paths);
        } else {
            $paths = array_values($paths);
        }

        $pathAssoc    = array();
        $numPaths     = count($paths);
        $minPathCouts = PHP_INT_MAX;

        for ($i = 0; $i < $numPaths; $i++) {
            $pathAssoc[$i] = explode('/', self::safePathUntrailingslashit($paths[$i]));
            $pathCount     = count($pathAssoc[$i]);
            if ($minPathCouts > $pathCount) {
                $minPathCouts = $pathCount;
            }
        }

        for ($partIndex = 0; $partIndex < $minPathCouts; $partIndex++) {
            $currentPart = $pathAssoc[0][$partIndex];
            for ($currentPath = 1; $currentPath < $numPaths; $currentPath++) {
                if ($pathAssoc[$currentPath][$partIndex] != $currentPart) {
                    break 2;
                }
            }
        }

        $resultParts = array_slice($pathAssoc[0], 0, $partIndex);

        return implode('/', $resultParts);
    }

    /**
     * remove root path transforming the current path into a relative path
     *
     * ex. /aaa/bbb  become aaa/bbb
     * ex. C:\aaa\bbb become aaa\bbb
     *
     * @param string $path file path
     *
     * @return string
     */
    public static function removeRootPath($path)
    {
        return preg_replace('/^(?:[A-Za-z]:)?[\/](.*)/', '$1', $path);
    }

    /**
     * Returns the last N lines of a file. Simular to tail command
     *
     * @param string $filepath The full path to the file to be tailed
     * @param int    $lines    The number of lines to return with each tail call
     *
     * @return false|string The last N parts of the file, flse on failure
     */
    public static function tailFile($filepath, $lines = 2)
    {
        // Open file
        $f = @fopen($filepath, "rb");
        if ($f === false) {
            return false;
        }

        // Sets buffer size
        $buffer = 256;

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }

        // Start reading
        $output = '';
        $chunk  = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk  = fread($f, $seek)) . $output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }
        fclose($f);
        return trim($output);
    }

    /**
     * @param string $filepath     path to file to be downloaded
     * @param string $downloadName name to be downloaded as
     * @param int    $bufferSize   file chunks to be served
     * @param bool   $limitRate    if set to true the download rate will be limited to $bufferSize/seconds
     *
     * @return void
     */
    public static function serveFileForDownload($filepath, $downloadName, $bufferSize = 0, $limitRate = false)
    {
        // Process download
        if (!file_exists($filepath)) {
            throw new Exception("File does not exist!");
        }

        if (!is_file($filepath)) {
            throw new Exception("'$filepath' is not a file!");
        }

        // Clean output buffers
        SnapUtil::obCleanAll(false);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        flush(); // Flush system output buffer

        if ($bufferSize <= 0) {
            readfile($filepath);
            exit;
        }

        $fp = @fopen($filepath, 'r');
        if (!is_resource($fp)) {
            throw new Exception('Fail to open the file ' . $filepath);
        }

        while (!feof($fp) && ($data = fread($fp, $bufferSize)) !== false) {
            echo $data;

            if ($limitRate) {
                sleep(1);
            }
        }
        @fclose($fp);
        exit;
    }

    /**
     * Return lasts fine of file
     *
     * @param string $path      Path to the file
     * @param int    $n         Number of lines to get
     * @param int    $charLimit Number of chars to include in each line
     *
     * @return bool|string[] Last $n lines of file
     */
    public static function getLastLinesOfFile($path, $n, $charLimit = null)
    {
        if (!is_readable($path)) {
            return false;
        }

        if (($handle = self::fopen($path, 'r', false)) === false) {
            return false;
        }

        $result      = array();
        $pos         = -1;
        $currentLine = '';
        $counter     = 0;

        while ($counter < $n && -1 !== fseek($handle, $pos, SEEK_END)) {
            $char = fgetc($handle);
            if (PHP_EOL == $char) {
                $trimmedValue = trim($currentLine);
                if (is_null($charLimit)) {
                    $currentLine = substr($currentLine, 0);
                } else {
                    $currentLine = substr($currentLine, 0, (int) $charLimit);
                    if (strlen($currentLine) == $charLimit) {
                        $currentLine .= '...';
                    }
                }

                if (!empty($trimmedValue)) {
                    $result[] = $currentLine;
                    $counter++;
                }
                $currentLine = '';
            } else {
                $currentLine = $char . $currentLine;
            }
            $pos--;
        }
        self::fclose($handle, false);

        return array_reverse($result);
    }

    /**
     * Thif function scan a folder filter by regex
     *
     * Options
     * regexFile: [bool|string|array]   if is bool alrays or never match, if is string o array of string check if rexeses match file name
     * regexFolder: [bool|string|array] if is bool alrays or never match, if is string o array of string check if rexeses match file name
     * checkFullPath: bool              if false only current file/folder name is passed at regex if true is passed the full path
     * recursive: bool                  if false check only passed folder or all sub folder recursively
     * invert: bool                     if false pass invert the result
     * childFirst: bool                 if false is parsed parent folters first or child folders first
     *
     * @param string                              $dir     dir to scan
     * @param array<string, bool|string|string[]> $options array{
     *                                                     regexFile?: bool|string,
     *                                                     regexFolder?: bool|string,
     *                                                     checkFullPath?: bool,
     *                                                     recursive?: bool,
     *                                                     invert?: bool,
     *                                                     childFirst?: bool
     *                                                     }
     *
     * @return string[] paths lists
     */
    public static function regexGlob($dir, $options)
    {
        $result = array();

        self::regexGlobCallback($dir, function ($path) use (&$result) {
            $result[] = $path;
        }, $options);

        return $result;
    }

    /**
     * Execute the callback function foreach right element, private function for optimization
     *
     * Options
     * regexFile: [bool|string|array]   if is bool alrays or never match, if is string o array of string check if rexeses match file name
     * regexFolder: [bool|string|array] if is bool alrays or never match, if is string o array of string check if rexeses match file name
     * checkFullPath: bool              if false only current file/folder name is passed at regex if true is passed the full path
     * recursive: bool                  if false check only passed folder or all sub folder recursively
     * invert: bool                     if false pass invert the result
     * childFirst: bool                 if false is parsed parent folters first or child folders first
     * symlinks: string[]               list a symblink parsed
     *
     * @param string                              $dir      dir to scan
     * @param callable                            $callback callback function
     * @param array<string, bool|string|string[]> $options  array{
     *                                                      regexFile?: bool|string,
     *                                                      regexFolder?: bool|string,
     *                                                      checkFullPath?: bool,
     *                                                      recursive?: bool,
     *                                                      invert?: bool,
     *                                                      childFirst?: bool,
     *                                                      symlinks?: string[]
     *                                                      }
     *
     * @return boolean Returns true on success or false on failure.
     */
    protected static function regexGlobCallbackPrivate($dir, $callback, &$options)
    {
        if (($dh = opendir($dir)) == false) {
            return false;
        }

        while (($elem = readdir($dh)) !== false) {
            if ($elem === '.' || $elem === '..') {
                continue;
            }

            $fullPath = $dir . $elem;
            $isDir    = is_dir($fullPath);
            if (($regex = $isDir ? $options['regexFolder'] : $options['regexFile']) === false) {
                continue;
            }

            if ($isDir && is_link($fullPath)) {
                $realPath = self::safePathUntrailingslashit($fullPath, true);
                if (in_array($realPath, $options['symlinks'])) {
                    continue;
                }
                $options['symlinks'][] = $realPath;
            }

            if (is_bool($regex)) {
                $match = $regex;
            } else {
                $match     = false;
                $pathCheck = $options['checkFullPath'] ? $fullPath : $elem;

                foreach ($regex as $currentRegex) {
                    if (preg_match($currentRegex, $pathCheck) === 1) {
                        $match = true;
                        break;
                    }
                }

                if ($options['invert']) {
                    $match = !$match;
                }
            }

            if ($match) {
                if ($isDir && $options['execChildFirst']) {
                    self::regexGlobCallbackPrivate($fullPath . '/', $callback, $options);
                }

                call_user_func($callback, $fullPath);

                if ($isDir && $options['execChildAfter']) {
                    self::regexGlobCallbackPrivate($fullPath . '/', $callback, $options);
                }
            }
        }
        closedir($dh);

        return true;
    }

    /**
     * Execute the callback function foreach right element (folder or files)
     *
     * Options
     * regexFile: [bool|string|array]   if is bool alrays or never match, if is string o array of string check if rexeses match file name
     * regexFolder: [bool|string|array] if is bool alrays or never match, if is string o array of string check if rexeses match file name
     * checkFullPath: bool              if false only current file/folder name is passed at regex if true is passed the full path
     * recursive: bool                  if false check only passed folder or all sub folder recursively
     * invert: bool                     if false pass invert the result
     * childFirst: bool                 if false is parsed parent folters first or child folders first
     *
     * @param string                              $dir      dir to scan
     * @param callable                            $callback callback function
     * @param array<string, bool|string|string[]> $options  array{
     *                                                      regexFile?: bool|string,
     *                                                      regexFolder?: bool|string,
     *                                                      checkFullPath?: bool,
     *                                                      recursive?: bool,
     *                                                      invert?: bool,
     *                                                      childFirst?: bool
     *                                                      }
     *
     * @return boolean Returns true on success or false on failure.
     */
    public static function regexGlobCallback($dir, $callback, $options = array())
    {
        $dir = self::safePathTrailingslashit($dir);

        if (!is_dir($dir) || !is_readable($dir)) {
            return false;
        }

        if (!is_callable($callback)) {
            return false;
        }

        $options = array_merge(array(
            'regexFile'     => true,
            'regexFolder'   => true,
            'checkFullPath' => false,
            'recursive'     => false,
            'invert'        => false,
            'childFirst'    => false,
            'symlinks'      => array()
            ), (array) $options);

        if (is_bool($options['regexFile'])) {
            $options['regexFile'] = ($options['regexFile'] xor $options['invert']);
        } elseif (is_scalar($options['regexFile'])) {
            $options['regexFile'] = array($options['regexFile']);
        }

        if (is_bool($options['regexFolder'])) {
            $options['regexFolder'] = ($options['regexFolder'] xor $options['invert']);
        } elseif (is_scalar($options['regexFolder'])) {
            $options['regexFolder'] = array($options['regexFolder']);
        }

        // optimizization
        $options['execChildFirst'] = ($options['recursive'] && $options['childFirst'] === true);
        $options['execChildAfter'] = ($options['recursive'] && $options['childFirst'] === false);

        return self::regexGlobCallbackPrivate($dir, $callback, $options);
    }

    /**
     * Empty passed dir
     *
     * @param string   $dir    folder to empty
     * @param string[] $filter childs name to skip
     *
     * @return boolean Returns true on success or false on failure.
     */
    public static function emptyDir($dir, $filter = array())
    {
        $dir = self::safePathTrailingslashit($dir);
        if (!is_dir($dir) || !is_readable($dir)) {
            return false;
        }

        if (($dh = opendir($dir)) == false) {
            return false;
        }

        $listToDelete = array();

        while (($elem = readdir($dh)) !== false) {
            if ($elem === '.' || $elem === '..') {
                continue;
            }

            if (in_array($elem, $filter)) {
                continue;
            }

            $fullPath = $dir . $elem;
            if (self::chmod($fullPath, 'ugo+rwx')) {
                $listToDelete[] = $fullPath;
            }
        }
        closedir($dh);

        foreach ($listToDelete as $path) {
            self::rrmdir($path);
        }
        return true;
    }

    /**
     * Returns a path to the base root folder of path taking into account the
     * open_basedir setting.
     *
     * @param string $path file path
     *
     * @return bool|string Base root path of $path if it's accessible, otherwise false;
     */
    public static function getMaxAllowedRootOfPath($path)
    {
        $path = self::safePathUntrailingslashit($path, true);

        if (!self::isOpenBaseDirEnabled()) {
            $parts = explode("/", $path);
            return $parts[0] . "/";
        } else {
            return self::getOpenBaseDirRootOfPath($path);
        }
    }

    /**
     * Check if php.ini open_basedir is enabled
     *
     * @return bool true if open_basedir is set
     */
    public static function isOpenBaseDirEnabled()
    {
        $iniVar = ini_get("open_basedir");
        return (strlen($iniVar) > 0);
    }

    /**
     * Get open_basedir list paths
     *
     * @return string[] Paths contained in the open_basedir setting. Empty array if the setting is not enabled.
     */
    public static function getOpenBaseDirPaths()
    {
        if (!($openBase = ini_get("open_basedir"))) {
            return array();
        }
        return explode(PATH_SEPARATOR, $openBase);
    }

    /**
     * Get open base dir root path of path
     *
     * @param string $path file path
     *
     * @return bool|string Path to the base dir of $path if it exists, otherwise false
     */
    public static function getOpenBaseDirRootOfPath($path)
    {
        foreach (self::getOpenBaseDirPaths() as $allowedPath) {
            $allowedPath = $allowedPath !== "/" ? self::safePathUntrailingslashit($allowedPath) : "/";
            if (strpos($path, $allowedPath) === 0) {
                return $allowedPath;
            }
        }

        return false;
    }

    /**
     * this function is similar at dirname but if empty path return empty value not .
     * and is SO indipendent so work on not normalized path
     *
     * @param string $path file path
     *
     * @return string
     */
    public static function getRelativeDirname($path)
    {
        if (preg_match('/^(.*)[\/]+/', $path, $matches) !== 1) {
            return '';
        }

        return $matches[1];
    }

    /**
     * Set full user permissions on folder (rwx)
     *
     * @param string $path dir path
     *
     * @return boolean // return false if folder don't have read write permission on folder
     */
    public static function dirAddFullPermsAndCheckResult($path)
    {
        if (!SnapIO::chmod($path, 'u+rwx')) {
            return false;
        }

        if (!is_readable($path) || !is_writable($path)) {
            return false;
        }

        if (function_exists('is_executable') && !is_executable($path) && !SnapOS::isWindows()) {
            return false;
        }

        return true;
    }

    /**
     * set full user permissions on file (rwx)
     *
     * @param string $path file path
     *
     * @return boolean // return false if folder don't have read write permission on folder
     */
    public static function fileAddFullPermsAndCheckResult($path)
    {
        if (!SnapIO::chmod($path, 'u+rw')) {
            return false;
        }

        if (!is_readable($path) || !is_writable($path)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the total size of a filesystem or disk partition in bytes
     *
     * @param string $directory path
     *
     * @return int rturn number of bytes or -1 on failure
     */
    public static function diskTotalSpace($directory)
    {
        if (!function_exists('disk_total_space')) {
            return -1;
        }

        if (($space = disk_total_space($directory)) === false) {
            return -1;
        }

        return (int) round($space);
    }

    /**
     * Returns available space in directory in bytes
     *
     * @param string $directory path
     *
     * @return int rturn number of bytes or -1 on failure
     */
    public static function diskFreeSpace($directory)
    {
        if (!function_exists('disk_free_space')) {
            return -1;
        }

        if (($space = disk_free_space($directory)) === false) {
            return -1;
        }

        return (int) round($space);
    }
}
