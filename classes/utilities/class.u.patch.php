<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/**
 * Class used to apply various patches to installer file
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package Duplicator
 * @subpackage classes
 * @copyright (c) 2022, Snapcreek LLC
 *
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
    public function __construct() {
        $this->DupLiteBackupDir = DUP_Settings::getSsdirPath();
    }

    /**
    * Apply patch code to all installer files
    */
    public function ApplyInstallerPatch_0001()
    {
        $backupDir = $this->DupLiteBackupDir;

        foreach (glob("{$backupDir}/*_installer" . DUP_Installer::INSTALLER_SERVER_EXTENSION) as $file) {
            if (strstr($file, '_installer' . DUP_Installer::INSTALLER_SERVER_EXTENSION)) {
                $content  = "<?php \n";
                $content .= "    /** PATCH_MARKER_START:V.0001 **/ \n";
                $content .= "    //TODO ADD PHP CODE HERE";
                $content .= "    /** PATCH_MARKER_END **/ \n";
                $content .= "?>\n";
                DUP_IO::fwritePrepend($file, $content);
            }
        }
    }
}