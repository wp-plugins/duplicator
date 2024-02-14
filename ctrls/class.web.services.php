<?php

use Duplicator\Libs\Snap\SnapUtil;
use Duplicator\Views\AdminNotices;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

class DUP_Web_Services
{
    /**
     * init ajax actions
     */
    public static function init()
    {
        add_action('wp_ajax_duplicator_reset_all_settings', array(__CLASS__, 'ajax_reset_all'));
        add_action('wp_ajax_duplicator_set_admin_notice_viewed', array(__CLASS__, 'set_admin_notice_viewed'));
        add_action('wp_ajax_duplicator_admin_notice_to_dismiss', array(__CLASS__, 'admin_notice_to_dismiss'));
        add_action('wp_ajax_duplicator_download_installer', array(__CLASS__, 'duplicator_download_installer'));
    }

    /**
     *
     * @param DUP_Package $package
     */
    public static function package_delete_callback($package)
    {
        $package->delete();
    }

    /**
     * reset all ajax action
     *
     * the output must be json
     */
    public static function ajax_reset_all()
    {
        ob_start();
        try {
            DUP_Handler::init_error_handler();

            if (!check_ajax_referer('duplicator_reset_all_settings', 'nonce', false)) {
                DUP_LOG::Trace('Security issue');
                throw new Exception('Security issue');
            }
            DUP_Util::hasCapability('export', DUP_Util::SECURE_ISSUE_THROW);

            /* Execute function * */
            $error  = false;
            $result = array(
                'data'    => array(),
                'html'    => '',
                'message' => ''
            );

            DUP_Package::by_status_callback(array(__CLASS__, 'package_delete_callback'), array(
                array('op' => '<', 'status' => DUP_PackageStatus::COMPLETE)
            ));

            /* reset active package id * */
            DUP_Settings::Set('active_package_id', -1);
            DUP_Settings::Save();

            /* Clean tmp folder * */
            DUP_Package::not_active_files_tmp_cleanup();

            //throw new Exception('force error test');
        } catch (Exception $e) {
            $error             = true;
            $result['message'] = $e->getMessage();
        }

        /* Intercept output * */
        $result['html'] = ob_get_clean();

        /* check error and return json * */
        if ($error) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }

    public static function duplicator_download_installer()
    {
        check_ajax_referer('duplicator_download_installer', 'nonce');

        $isValid   = true;
        $inputData = filter_input_array(INPUT_GET, array(
            'id'   => array(
                'filter'  => FILTER_VALIDATE_INT,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            ),
            'hash' => array(
                'filter'  => FILTER_UNSAFE_RAW,
                'flags'   => FILTER_REQUIRE_SCALAR,
                'options' => array(
                    'default' => false
                )
            )
        ));

        $packageId = $inputData['id'];
        $hash      = $inputData['hash'];

        if (!$packageId || !$hash) {
            $isValid = false;
        }

        try {
            DUP_Util::hasCapability('export', DUP_Util::SECURE_ISSUE_THROW);

            if (!$isValid) {
                throw new Exception(__('Invalid request.', 'duplicator'));
            }

            if (($package = DUP_Package::getByID($packageId)) == null) {
                throw new Exception(__('Invalid request.', 'duplicator'));
            }

            if ($hash !== $package->Hash) {
                throw new Exception(__('Invalid request.', 'duplicator'));
            }

            $fileName     = $package->getInstDownloadName();
            $realFileName = $package->Installer->File;
            $backupDir    = DUP_Settings::getSsdirPath();

            if (DUP_STR::endsWith($realFileName, '.php')) {
                $realFileName = basename($realFileName, '.php') . DUP_Installer::INSTALLER_SERVER_EXTENSION;
            }
            $filepath = "{$backupDir}/{$realFileName}";

            // Process download
            if (!file_exists($filepath)) {
                throw new Exception(__('INVALID REQUEST: File not found, please check the backup folder for file.', 'duplicator'));
            }

            // Clean output buffer
            if (ob_get_level() !== 0 && @ob_end_clean() === false) {
                @ob_clean();
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            flush(); // Flush system output buffer

            try {
                $fp = @fopen($filepath, 'r');
                if (false === $fp) {
                    throw new Exception('Fail to open the file ' . $filepath);
                }
                while (!feof($fp) && ($data = fread($fp, DUPLICATOR_BUFFER_READ_WRITE_SIZE)) !== false) {
                    echo $data;
                }
                @fclose($fp);
            } catch (Exception $e) {
                readfile($filepath);
            }
            exit;
        } catch (Exception $ex) {
            //Prevent brute force
            sleep(2);
            wp_die($ex->getMessage());
        }
    }

    public static function set_admin_notice_viewed()
    {
        DUP_Handler::init_error_handler();

        try {
            DUP_Util::hasCapability('export', DUP_Util::SECURE_ISSUE_THROW);

            if (!wp_verify_nonce($_REQUEST['nonce'], 'duplicator_set_admin_notice_viewed')) {
                DUP_Log::trace(__('Security issue', 'duplicator'));
                throw new Exception('Security issue');
            }

            $notice_id = SnapUtil::filterInputRequest('notice_id', FILTER_UNSAFE_RAW);

            if (empty($notice_id)) {
                throw new Exception(__('Invalid Request', 'duplicator'));
            }

            $notices = get_user_meta(get_current_user_id(), DUPLICATOR_ADMIN_NOTICES_USER_META_KEY, true);
            if (empty($notices)) {
                $notices = array();
            }

            if (!isset($notices[$notice_id])) {
                throw new Exception(__("Notice with that ID doesn't exist.", 'duplicator'));
            }

            $notices[$notice_id] = 'true';
            update_user_meta(get_current_user_id(), DUPLICATOR_ADMIN_NOTICES_USER_META_KEY, $notices);
        } catch (Exception $ex) {
            wp_die($ex->getMessage());
        }
    }

    public static function admin_notice_to_dismiss()
    {
        try {
            DUP_Util::hasCapability('export', DUP_Util::SECURE_ISSUE_THROW);

            $nonce = filter_input(INPUT_POST, 'nonce', FILTER_UNSAFE_RAW);
            if (!wp_verify_nonce($nonce, 'duplicator_admin_notice_to_dismiss')) {
                DUP_Log::trace('Security issue');
                throw new Exception('Security issue');
            }

            $noticeToDismiss = filter_input(INPUT_POST, 'notice', FILTER_UNSAFE_RAW);
            switch ($noticeToDismiss) {
                case AdminNotices::OPTION_KEY_ACTIVATE_PLUGINS_AFTER_INSTALL:
                case AdminNotices::OPTION_KEY_NEW_NOTICE_TEMPLATE:
                    delete_option($noticeToDismiss);
                    break;
                case AdminNotices::OPTION_KEY_IS_ENABLE_NOTICE_DISMISSED:
                case AdminNotices::OPTION_KEY_IS_MU_NOTICE_DISMISSED:
                    update_option($noticeToDismiss, true);
                    break;
                default:
                    throw new Exception('Notice invalid');
            }
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }

        wp_send_json_success();
    }
}
