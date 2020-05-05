<?php
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
        add_action('wp_ajax_duplicator_download', array(__CLASS__, 'duplicator_download'));
        add_action('wp_ajax_nopriv_duplicator_download', array(__CLASS__, 'duplicator_download'));
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

            /** Execute function * */
            $error  = false;
            $result = array(
                'data'    => array(),
                'html'    => '',
                'message' => ''
            );

            $nonce = sanitize_text_field($_POST['nonce']);
            if (!wp_verify_nonce($nonce, 'duplicator_reset_all_settings')) {
                DUP_Log::trace('Security issue');
                throw new Exception('Security issue');
            }

            DUP_Package::by_status_callback(array(__CLASS__, 'package_delete_callback'), array(
                array('op' => '<', 'status' => DUP_PackageStatus::COMPLETE)
            ));

            /** reset active package id * */
            DUP_Settings::Set('active_package_id', -1);
            DUP_Settings::Save();

            /** Clean tmp folder * */
            DUP_Package::not_active_files_tmp_cleanup();

            //throw new Exception('force error test');
        }
        catch (Exception $e) {
            $error             = true;
            $result['message'] = $e->getMessage();
        }

        /** Intercept output * */
        $result['html'] = ob_get_clean();

        /** check error and return json * */
        if ($error) {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }

    public static function duplicator_download()
    {
        $error = false;

        $packageId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $hash      = filter_input(INPUT_GET, 'hash', FILTER_SANITIZE_STRING);
        $file      = filter_input(INPUT_GET, 'file', FILTER_SANITIZE_STRING);

        if ($packageId === false || $hash === false || $file === false) {
            $error = true;
        }

        if ($error || ($package = DUP_Package::getByID($packageId)) == false) {
            $error = true;
        }

        if ($error || $hash !== $package->Hash) {
            $error = true;
        }

        switch ($file) {
            case 'sql':
                $fileName = "{$package->NameHash}_database.sql";
                break;
            case 'archive':
                $format   = strtolower($package->Archive->Format);
                $fileName = "{$package->NameHash}_archive.{$format}";
                break;
            case 'installer':
                $fileName = $package->NameHash.'_installer.php';
                break;
            default:
                $error    = true;
        }

        if(!$error)
        {
            $filepath = DUPLICATOR_SSDIR_PATH.'/'.$fileName;

            // Process download
            if (file_exists($filepath)) {
                // Clean output buffer
                if (ob_get_level() !== 0 && @ob_end_clean() === FALSE) {
                    @ob_clean();
                }

                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="'.$fileName.'"');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: '.filesize($filepath));
                flush(); // Flush system output buffer

                try {
                    $fp = @fopen($filepath, 'r');
                    if (false === $fp) {
                        throw new Exception('Fail to open the file '.$filepath);
                    }
                    while (!feof($fp) && ($data = fread($fp, DUPLICATOR_BUFFER_READ_WRITE_SIZE)) !== FALSE) {
                        echo $data;
                    }
                    @fclose($fp);
                }
                catch (Exception $e) {
                    readfile($filepath);
                }
                exit;
            } else {
                // if the request is wrong wait to avoid brute force attack
                sleep(2);
                wp_die('Invalid request');
            }
        }
    }

    public static function set_admin_notice_viewed()
    {
        DUP_Util::hasCapability('export', DUP_Util::SECURE_ISSUE_THROW);

        if (!wp_verify_nonce($_REQUEST['nonce'], 'duplicator_set_admin_notice_viewed')) {
            DUP_Log::trace('Security issue');
            throw new Exception('Security issue');
        }

        if (empty($_REQUEST['notice_id'])) {
            wp_die();
        }

        $notices = get_user_meta(get_current_user_id(), DUPLICATOR_ADMIN_NOTICES_USER_META_KEY, true);
        if (empty($notices)) {
            $notices = array();
        }

        $notices[$_REQUEST['notice_id']] = 'true';
        update_user_meta(get_current_user_id(), DUPLICATOR_ADMIN_NOTICES_USER_META_KEY, $notices);

        wp_die();
    }

    public static function admin_notice_to_dismiss()
    {
        try {
            DUP_Util::hasCapability('export', DUP_Util::SECURE_ISSUE_THROW);

            $nonce = filter_input(INPUT_POST, 'nonce', FILTER_SANITIZE_STRING);
            if (!wp_verify_nonce($nonce, 'duplicator_admin_notice_to_dismiss')) {
                DUP_Log::trace('Security issue');
                throw new Exception('Security issue');
            }

            $noticeToDismiss = filter_input(INPUT_POST, 'notice', FILTER_SANITIZE_STRING);
            switch ($noticeToDismiss) {
                case DUP_UI_Notice::OPTION_KEY_INSTALLER_HASH_NOTICE:
                case DUP_UI_Notice::OPTION_KEY_ACTIVATE_PLUGINS_AFTER_INSTALL_DISMISS:
                    delete_option($noticeToDismiss);
                    break;
                default:
                    throw new Exception('Notice invalid');
            }
        }
        catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }

        wp_send_json_success();
    }
}