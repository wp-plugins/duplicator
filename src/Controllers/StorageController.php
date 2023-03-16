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
        $storageAlert->height  = 520;
        $storageAlert->width   = 400;
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
                'fa-class' => 'fab fa-amazon',
            ),
            array(
                'title'    => __('S3-Compatible Provider', 'duplicator'),
                'label'    => __('S3-Compatible (Generic) Google Cloud Drive, BackBlaze, Wasabi, etc…', 'duplicator'),
                'fa-class' => 'fab fa-aws',
            ),
            array(
                'title'    => __('Google Drive', 'duplicator'),
                'label'    => __('Google Drive', 'duplicator'),
                'fa-class' => 'fab fa-google-drive',
            ),
            array(
                'title'    => __('OneDrive', 'duplicator'),
                'label'    => __('OneDrive', 'duplicator'),
                'fa-class' => 'fas fa-cloud',
            ),
            array(
                'title'    => __('DropBox', 'duplicator'),
                'label'    => __('DropBox', 'duplicator'),
                'fa-class' => 'fab fa-dropbox',
            ),
            array(
                'title'    => __('FTP/SFTP', 'duplicator'),
                'label'    => __('FTP/SFTP', 'duplicator'),
                'fa-class' => 'fas fa-network-wired',
            )
        );
    }
}
