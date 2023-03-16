<?php

use Duplicator\Core\Controllers\ControllersManager;
use Duplicator\Libs\Snap\SnapURL;
use Duplicator\Libs\Snap\SnapUtil;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
// Exit if accessed directly
if (! defined('DUPLICATOR_VERSION')) {
    exit;
}

require_once(DUPLICATOR_PLUGIN_PATH . '/ctrls/ctrl.base.php');
require_once(DUPLICATOR_PLUGIN_PATH . '/classes/utilities/class.u.scancheck.php');

/**
 * Controller for Tools
 *
 * @package Duplicator\ctrls
 */
class DUP_CTRL_Tools extends DUP_CTRL_Base
{
    /**
     *  Init this instance of the object
     */
    public function __construct()
    {
        add_action('wp_ajax_DUP_CTRL_Tools_runScanValidator', array($this, 'runScanValidator'));
        add_action('wp_ajax_DUP_CTRL_Tools_getTraceLog', array($this, 'getTraceLog'));
    }

    /**
     *
     * @return boolean
     */
    public static function isToolPage()
    {
        return ControllersManager::isCurrentPage(ControllersManager::TOOLS_SUBMENU_SLUG);
    }

    /**
     *
     * @return boolean
     */
    public static function isDiagnosticPage()
    {
        return ControllersManager::isCurrentPage(ControllersManager::TOOLS_SUBMENU_SLUG, 'diagnostics');
    }

    /**
     * Return diagnostic URL
     *
     * @param bool $relative if true return relative URL else absolute
     *
     * @return string
     */
    public static function getDiagnosticURL($relative = true)
    {
        return ControllersManager::getMenuLink(
            ControllersManager::TOOLS_SUBMENU_SLUG,
            'diagnostics',
            '',
            array(),
            $relative
        );
    }

    /**
     * Return clean installer files action URL
     *
     * @param bool $relative if true return relative URL else absolute
     *
     * @return string
     */
    public static function getCleanFilesAcrtionUrl($relative = true)
    {
        return ControllersManager::getMenuLink(
            ControllersManager::TOOLS_SUBMENU_SLUG,
            'diagnostics',
            '',
            array(
                'action' => 'installer',
                '_wpnonce' => wp_create_nonce('duplicator_cleanup_page')
            ),
            $relative
        );
    }

    /**
     * Calls the ScanValidator and returns a JSON result
     *
     * @notes: Testing = /wp-admin/admin-ajax.php?action=DUP_CTRL_Tools_runScanValidator
     */
    public function runScanValidator()
    {
        DUP_Handler::init_error_handler();
        check_ajax_referer('DUP_CTRL_Tools_runScanValidator', 'nonce');
        @set_time_limit(0);
        $isValid   = true;
        $inputData = filter_input_array(INPUT_POST, array(
            'recursive_scan' => array(
                'filter'  => FILTER_VALIDATE_BOOLEAN,
                'flags'   => FILTER_NULL_ON_FAILURE
            )
        ));
        if (is_null($inputData['recursive_scan'])) {
            $isValid = false;
        }

        $result = new DUP_CTRL_Result($this);
        try {
            DUP_Util::hasCapability('export', DUP_Util::SECURE_ISSUE_THROW);
            if (!$isValid) {
                throw new Exception(__('Invalid Request.', 'duplicator'));
            }
            //CONTROLLER LOGIC
            $path = duplicator_get_abs_path();
            if (!is_dir($path)) {
                throw new Exception("Invalid directory provided '{$path}'!");
            }

            $scanner            = new DUP_ScanCheck();
            $scanner->recursion = $inputData['recursive_scan'];
            $payload            = $scanner->run($path);
//RETURN RESULT
            $test = ($payload->fileCount > 0)
                ? DUP_CTRL_Status::SUCCESS
                : DUP_CTRL_Status::FAILED;
            $result->process($payload, $test);
        } catch (Exception $exc) {
            $result->processError($exc);
        }
    }

    public function getTraceLog()
    {
        DUP_Log::Trace("enter");
        check_ajax_referer('DUP_CTRL_Tools_getTraceLog', 'nonce');
        Dup_Util::hasCapability('export');
        $file_path   = DUP_Log::GetTraceFilepath();
        $backup_path = DUP_Log::GetBackupTraceFilepath();
        $zip_path    = DUP_Settings::getSsdirPath() . "/" . DUPLICATOR_ZIPPED_LOG_FILENAME;
        $zipped      = DUP_Zip_U::zipFile($file_path, $zip_path, true, null, true);
        if ($zipped && file_exists($backup_path)) {
            $zipped = DUP_Zip_U::zipFile($backup_path, $zip_path, false, null, true);
        }

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Content-Transfer-Encoding: binary");
        $fp = fopen($zip_path, 'rb');
        if (($fp !== false) && $zipped) {
            $zip_filename = basename($zip_path);
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"$zip_filename\";");
        // required or large files wont work
            if (ob_get_length()) {
                ob_end_clean();
            }

            DUP_Log::trace("streaming $zip_path");
            if (fpassthru($fp) === false) {
                DUP_Log::trace("Error with fpassthru for $zip_path");
            }

            fclose($fp);
            @unlink($zip_path);
        } else {
            header("Content-Type: text/plain");
            header("Content-Disposition: attachment; filename=\"error.txt\";");
            if ($zipped === false) {
                $message = "Couldn't create zip file.";
            } else {
                $message = "Couldn't open $file_path.";
            }
            DUP_Log::trace($message);
            echo esc_html($message);
        }

        exit;
    }
}
