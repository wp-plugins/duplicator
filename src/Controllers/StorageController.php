<?php

namespace Duplicator\Controllers;

use DUP_UI_Dialog;
use Duplicator\Core\Views\TplMng;

class StorageController
{
    /**
     * Render storages page
     *
     * @return void
     */
    public static function render()
    {
        TplMng::getInstance()->render('mocks/storage/storage', array(
            'storages' => self::getStoragesData()
        ), true);
    }

    /**
     * Fet storage alert dialog box
     *
     * @param string $utm_medium UTM medium for the upsell link
     *
     * @return DUP_UI_Dialog
     */
    public static function getDialogBox($utm_medium)
    {
        require_once(DUPLICATOR_PLUGIN_PATH . '/classes/ui/class.ui.dialog.php');

        $storageAlert          = new DUP_UI_Dialog();
        $storageAlert->title   = __('Advanced Storage', 'duplicator');
        $storageAlert->height  = 600;
        $storageAlert->width   = 550;
        $storageAlert->okText  = '';
        $storageAlert->message = TplMng::getInstance()->render('mocks/storage/popup', array(
            'storages' => self::getStoragesData(),
            'utm_medium' => $utm_medium,
        ), false);
        $storageAlert->initAlert();

        return $storageAlert;
    }

    /**
     * Returns the storage data for the view
     *
     * @return array[]
     */
    private static function getStoragesData()
    {
        return array(
            array(
                'title'    => __('Amazon S3', 'duplicator'),
                'label'    => __('Amazon S3', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/aws.svg',
            ),
            array(
                'title'    => __('Google Drive', 'duplicator'),
                'label'    => __('Google Drive', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/google-drive.svg',
            ),
            array(
                'title'    => __('OneDrive', 'duplicator'),
                'label'    => __('OneDrive', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/onedrive.svg',
            ),
            array(
                'title'    => __('DropBox', 'duplicator'),
                'label'    => __('DropBox', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/dropbox.svg',
            ),
            array(
                'title'    => __('FTP/SFTP', 'duplicator'),
                'label'    => __('FTP/SFTP', 'duplicator'),
                'fa-class' => 'fas fa-network-wired',
            ),
            array(
                'title'    => __('Google Cloud Storage', 'duplicator'),
                'label'    => __('Google Cloud Storage', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/google-cloud.svg',
            ),
            array(
                'title'    => __('Back Blaze', 'duplicator'),
                'label'    => __('Back Blaze', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/backblaze.svg',
            ),
            array(
                'title'    => __('Cloudflare R2', 'duplicator'),
                'label'    => __('Cloudflare R2', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/cloudflare.svg',
            ),
            array(
                'title'    => __('Digital Ocean Spaces', 'duplicator'),
                'label'    => __('Digital Ocean Spaces', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/digital-ocean.svg',
            ),
            array(
                'title'    => __('Vultr Object Storage', 'duplicator'),
                'label'    => __('Vultr Object Storage', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/vultr.svg',
            ),
            array(
                'title'    => __('Dream Objects', 'duplicator'),
                'label'    => __('Dream Objects', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/dreamhost.svg',
            ),
            array(
                'title'    => __('Wasabi', 'duplicator'),
                'label'    => __('Wasabi', 'duplicator'),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/wasabi.svg',
            ),
            array(
                'title'    => __('S3-Compatible Provider', 'duplicator'),
                'label'    => __(
                    'S3-Compatible (Generic) Cloudian, Cloudn, Connectria, Constant, Exoscal, Eucalyptus, Nifty, Nimbula, Minio, etc...',
                    'duplicator'
                ),
                'iconUrl'  => DUPLICATOR_PLUGIN_URL . 'assets/img/aws.svg',
            ),
        );
    }
}
