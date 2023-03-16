<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Class used to apply various patches to installer file
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    Duplicator
 * @subpackage classes
 * @copyright  (c) 2022, Snapcreek LLC
 */
class DUP_Patch
{
    /**
    * The current backup directory path for Duplicator Lite
    * Possible options are 'wp-snapshots' and 'backups-dup-lite'
    */
    public $DupLiteBackupDir;

   /**
    * Class construct for init
    */
    public function __construct()
    {
        $this->DupLiteBackupDir = DUP_Settings::getSsdirPath();
    }

    /**
    * Apply patch code to all installer files
    */
    public function ApplyInstallerPatch_0001()
    {
        $backupDir = $this->DupLiteBackupDir;

        foreach (glob("{$backupDir}/*_installer.php") as $file) {
            if (strstr($file, '_installer.php')) {
                $content  = "<?php \n";
                $content .= "    /** PATCH_MARKER_START:V.0001 **/ \n";
                $content .= "    //TODO ADD PHP CODE HERE";
                $content .= "    /** PATCH_MARKER_END **/ \n";
                $content .= "?>\n";
                $this->fwritePrepend($file, $content);
            }
        }
    }


    /**
    * Prepends data to an existing file
    *
    * @param string $file      The full file path to the file
    * @param string $content    The content to prepend to the file
    *
    * @return TRUE on success or if file does not exist. FALSE on failure
    */
    private function fwritePrepend($file, $prepend)
    {
        if (!file_exists($file) || !is_writable($file)) {
            return false;
        }

        $handle    = fopen($file, "r+");
        $len       = strlen($prepend);
        $final_len = filesize($file) + $len;
        $cache_old = fread($handle, $len);
        rewind($handle);
        $i = 1;
        while (ftell($handle) < $final_len) {
            fwrite($handle, $prepend);
            $prepend   = $cache_old;
            $cache_old = fread($handle, $len);
            fseek($handle, $i * $len);
            $i++;
        }
    }
}
