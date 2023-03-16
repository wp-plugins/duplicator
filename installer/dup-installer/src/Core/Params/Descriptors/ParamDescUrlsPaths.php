<?php

/**
 * Urls and paths params descriptions
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Core\Params\Descriptors;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Libs\Snap\SnapJson;
use DUPX_InstallerState;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescUrlsPaths implements DescriptorInterface
{
    const INVALID_PATH_EMPTY = 'can\'t be empty';
    const INVALID_URL_EMPTY  = 'can\'t be empty';

    /**
     * Init params
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function init(&$params)
    {
        $archive_config = \DUPX_ArchiveConfig::getInstance();
        $paths          = $archive_config->getRealValue('archivePaths');

        $oldMainPath = $paths->home;
        $newMainPath = DUPX_ROOT;

        $oldHomeUrl = rtrim($archive_config->getRealValue('homeUrl'), '/');
        $newHomeUrl = rtrim(DUPX_ROOT_URL, '/');

        $oldSiteUrl      = rtrim($archive_config->getRealValue('siteUrl'), '/');
        $oldContentUrl   = rtrim($archive_config->getRealValue('contentUrl'), '/');
        $oldUploadUrl    = rtrim($archive_config->getRealValue('uploadBaseUrl'), '/');
        $oldPluginsUrl   = rtrim($archive_config->getRealValue('pluginsUrl'), '/');
        $oldMuPluginsUrl = rtrim($archive_config->getRealValue('mupluginsUrl'), '/');

        $oldWpAbsPath       = $paths->abs;
        $oldContentPath     = $paths->wpcontent;
        $oldUploadsBasePath = $paths->uploads;
        $oldPluginsPath     = $paths->plugins;
        $oldMuPluginsPath   = $paths->muplugins;

        $defValEdit = "This default value is automatically generated.\n"
            . "Change it only if you're sure you know what you're doing!";

        $params[PrmMng::PARAM_URL_OLD] = new ParamItem(
            PrmMng::PARAM_URL_OLD,
            ParamForm::TYPE_STRING,
            array(
            'default' => $oldHomeUrl
            )
        );

        $params[PrmMng::PARAM_WP_ADDON_SITES_PATHS] = new ParamItem(
            PrmMng::PARAM_WP_ADDON_SITES_PATHS,
            ParamForm::TYPE_ARRAY_STRING,
            array(
            'default' => array()
            )
        );

        $newObj                        = new ParamForm(
            PrmMng::PARAM_URL_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => $newHomeUrl,
                'sanitizeCallback' => array('Duplicator\\Installer\\Core\\Params\\Descriptors\\ParamsDescriptors', 'sanitizeUrl'),
                'validateCallback' => array('Duplicator\\Installer\\Core\\Params\\Descriptors\\ParamsDescriptors', 'validateUrlWithScheme')
            ),
            array(// FORM ATTRIBUTES
                'label'  => 'New Site URL:',
                'status' => function (ParamForm $param) {
                    if (
                        PrmMng::getInstance()->getValue(PrmMng::PARAM_TEMPLATE) !== \DUPX_Template::TEMPLATE_ADVANCED ||
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'wrapperClasses' => array('revalidate-on-change', 'cant-be-empty'),
                'subNote'        => function (ParamForm $param) {
                    $archive_config = \DUPX_ArchiveConfig::getInstance();
                    $oldHomeUrl     = rtrim($archive_config->getRealValue('homeUrl'), '/');
                    return 'Old value: <b>' . \DUPX_U::esc_html($oldHomeUrl) . '</b>';
                },
                'postfix' => array('type' => 'button', 'label' => 'get', 'btnAction' => 'DUPX.getNewUrlByDomObj(this);')
            )
        );
        $params[PrmMng::PARAM_URL_NEW] = $newObj;
        $urlNewInputId                 =  $newObj->getFormItemId();

        $params[PrmMng::PARAM_PATH_OLD] = new ParamItem(
            PrmMng::PARAM_PATH_OLD,
            ParamForm::TYPE_STRING,
            array(
            'default' => $oldMainPath
            )
        );

        $newObj = new ParamForm(
            PrmMng::PARAM_PATH_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => $newMainPath,
                'sanitizeCallback' => array('Duplicator\\Installer\\Core\\Params\\Descriptors\\ParamsDescriptors', 'sanitizePath'),
                'validateCallback' => function ($value, ParamItem $paramObj) {
                    if (strlen($value) == 0) {
                        $paramObj->setInvalidMessage('The new path can\'t be empty.');
                        return false;
                    }

                    // if home path is root path is necessary do a trailingslashit
                    $realPath = SnapIO::safePathTrailingslashit($value);
                    if (!is_dir($realPath)) {
                        $paramObj->setInvalidMessage(
                            'The new path must be an existing folder on the server.<br>' .
                            'It is not possible to continue the installation without first creating the folder <br>' .
                            '<b>' . $value . '</b>'
                        );
                        return false;
                    }

                    // don't check the return of chmod, if fail the installer must continue
                    SnapIO::chmod($realPath, 'u+rwx');
                    return true;
                }
            ),
            array(// FORM ATTRIBUTES
                'label'  => 'New Path:',
                'status' => function (ParamForm $param) {
                    if (
                        PrmMng::getInstance()->getValue(PrmMng::PARAM_TEMPLATE) !== \DUPX_Template::TEMPLATE_ADVANCED ||
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldMainPath) . '</b>',
                'wrapperClasses' => array('revalidate-on-change', 'cant-be-empty')
            )
        );

        $params[PrmMng::PARAM_PATH_NEW] = $newObj;
        $pathNewInputId                 =  $newObj->getFormItemId();

        $params[PrmMng::PARAM_SITE_URL_OLD] = new ParamItem(
            PrmMng::PARAM_SITE_URL_OLD,
            ParamForm::TYPE_STRING,
            array(
            'default' => $oldSiteUrl
            )
        );

        $wrapClasses    = array('revalidate-on-change', 'cant-be-empty', 'auto-updatable', 'autoupdate-enabled');
        $postfixElement = array(
            'type'      => 'button',
            'label'     => 'Auto',
            'btnAction' => 'DUPX.autoUpdateToggle(this, ' . SnapJson::jsonEncode($defValEdit) . ');'
        );

        $params[PrmMng::PARAM_SITE_URL] = new ParamForm(
            PrmMng::PARAM_SITE_URL,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'WP core URL:',
                'status'         => ParamForm::STATUS_INFO_ONLY,
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldSiteUrl) . '</b>',
            )
        );

        $params[PrmMng::PARAM_PATH_CONTENT_OLD] = new ParamItem(
            PrmMng::PARAM_PATH_CONTENT_OLD,
            ParamForm::TYPE_STRING,
            array(
            'default' => $oldContentPath
            )
        );

        $params[PrmMng::PARAM_PATH_CONTENT_NEW] = new ParamForm(
            PrmMng::PARAM_PATH_CONTENT_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'WP-content path:',
                'status'         => ParamForm::STATUS_INFO_ONLY,
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldContentPath) . '</b>'
            )
        );

        $params[PrmMng::PARAM_PATH_WP_CORE_OLD] = new ParamItem(
            PrmMng::PARAM_PATH_WP_CORE_OLD,
            ParamForm::TYPE_STRING,
            array(
                'default' => $oldWpAbsPath
            )
        );

        $params[PrmMng::PARAM_PATH_WP_CORE_NEW] = new ParamForm(
            PrmMng::PARAM_PATH_WP_CORE_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
            'label'          => 'WP core path:',
            'status'         => ParamForm::STATUS_INFO_ONLY,
            'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldWpAbsPath) . '</b>'
            )
        );

        $params[PrmMng::PARAM_PATH_UPLOADS_OLD] = new ParamItem(
            PrmMng::PARAM_PATH_UPLOADS_OLD,
            ParamForm::TYPE_STRING,
            array(
            'default' => $oldUploadsBasePath
            )
        );

        $params[PrmMng::PARAM_PATH_UPLOADS_NEW] = new ParamForm(
            PrmMng::PARAM_PATH_UPLOADS_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'Uploads path:',
                'status'         => ParamForm::STATUS_INFO_ONLY,
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldUploadsBasePath) . '</b>'
            )
        );

        $params[PrmMng::PARAM_URL_CONTENT_OLD] = new ParamItem(
            PrmMng::PARAM_URL_CONTENT_OLD,
            ParamForm::TYPE_STRING,
            array(
                'default' => $oldContentUrl
            )
        );

        $params[PrmMng::PARAM_URL_CONTENT_NEW] = new ParamForm(
            PrmMng::PARAM_URL_CONTENT_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'WP-content URL:',
                'status'         => ParamForm::STATUS_INFO_ONLY,
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldContentUrl) . '</b>'
            )
        );

        $params[PrmMng::PARAM_URL_UPLOADS_OLD] = new ParamItem(
            PrmMng::PARAM_URL_UPLOADS_OLD,
            ParamForm::TYPE_STRING,
            array(// ITEM ATTRIBUTES
                'default' => $oldUploadUrl
            )
        );

        $params[PrmMng::PARAM_URL_UPLOADS_NEW] = new ParamForm(
            PrmMng::PARAM_URL_UPLOADS_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
            'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'Uploads URL:',
                'status'         => ParamForm::STATUS_INFO_ONLY,
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldUploadUrl) . '</b>'
            )
        );

        $params[PrmMng::PARAM_URL_PLUGINS_OLD] = new ParamItem(
            PrmMng::PARAM_URL_PLUGINS_OLD,
            ParamForm::TYPE_STRING,
            array(// ITEM ATTRIBUTES
                'default' => $oldPluginsUrl
            )
        );

        $params[PrmMng::PARAM_URL_PLUGINS_NEW] = new ParamForm(
            PrmMng::PARAM_URL_PLUGINS_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'Plugins URL:',
                'status'         =>  ParamForm::STATUS_INFO_ONLY,
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldPluginsUrl) . '</b>'
            )
        );

        $params[PrmMng::PARAM_PATH_PLUGINS_OLD] = new ParamItem(
            PrmMng::PARAM_PATH_PLUGINS_OLD,
            ParamForm::TYPE_STRING,
            array(
                'default'          => $oldPluginsPath
            )
        );

        $params[PrmMng::PARAM_PATH_PLUGINS_NEW] = new ParamForm(
            PrmMng::PARAM_PATH_PLUGINS_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'Plugins path:',
                'status'         => ParamForm::STATUS_INFO_ONLY,
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldPluginsPath) . '</b>'
            )
        );

        $params[PrmMng::PARAM_URL_MUPLUGINS_OLD] = new ParamItem(
            PrmMng::PARAM_URL_MUPLUGINS_OLD,
            ParamForm::TYPE_STRING,
            array(
                'default' => $oldMuPluginsUrl
            )
        );

        $params[PrmMng::PARAM_URL_MUPLUGINS_NEW] = new ParamForm(
            PrmMng::PARAM_URL_MUPLUGINS_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'MU-plugins URL:',
                'status'         => ParamForm::STATUS_INFO_ONLY,
                'subNote'        => 'Old value: <b>' . \DUPX_U::esc_html($oldMuPluginsUrl) . '</b>'
            )
        );

        $params[PrmMng::PARAM_PATH_MUPLUGINS_OLD] = new ParamItem(
            PrmMng::PARAM_PATH_MUPLUGINS_OLD,
            ParamForm::TYPE_STRING,
            array(
                'default' => $oldMuPluginsPath
            )
        );

        $params[PrmMng::PARAM_PATH_MUPLUGINS_NEW] = new ParamForm(
            PrmMng::PARAM_PATH_MUPLUGINS_NEW,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_TEXT,
            array(// ITEM ATTRIBUTES
                'default'          => ''
            ),
            array(// FORM ATTRIBUTES
                'label'          => 'MU-plugins path:',
                'status'         => ParamForm::STATUS_INFO_ONLY,
                'postfix'        => $postfixElement
            )
        );
    }

    /**
     * Update params after overwrite logic
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function updateParamsAfterOverwrite($params)
    {
        PrmMng::getInstance();

        $archive_config = \DUPX_ArchiveConfig::getInstance();
        $paths          = $archive_config->getRealValue('archivePaths');

        $oldMainPath = $paths->home;
        $newMainPath = $params[PrmMng::PARAM_PATH_NEW]->getValue();

        $oldHomeUrl = rtrim($archive_config->getRealValue('homeUrl'), '/');
        $newHomeUrl = $params[PrmMng::PARAM_URL_NEW]->getValue();

        $oldSiteUrl      = rtrim($archive_config->getRealValue('siteUrl'), '/');
        $oldContentUrl   = rtrim($archive_config->getRealValue('contentUrl'), '/');
        $oldUploadUrl    = rtrim($archive_config->getRealValue('uploadBaseUrl'), '/');
        $oldPluginsUrl   = rtrim($archive_config->getRealValue('pluginsUrl'), '/');
        $oldMuPluginsUrl = rtrim($archive_config->getRealValue('mupluginsUrl'), '/');

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_PATH_WP_CORE_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->abs);
            $params[PrmMng::PARAM_PATH_WP_CORE_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_PATH_CONTENT_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->wpcontent);
            $params[PrmMng::PARAM_PATH_CONTENT_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_PATH_UPLOADS_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->uploads);
            $params[PrmMng::PARAM_PATH_UPLOADS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_PATH_PLUGINS_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->plugins);
            $params[PrmMng::PARAM_PATH_PLUGINS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_PATH_MUPLUGINS_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubString($oldMainPath, $newMainPath, $paths->muplugins);
            $params[PrmMng::PARAM_PATH_MUPLUGINS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_SITE_URL]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldSiteUrl);
            $params[PrmMng::PARAM_SITE_URL]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_URL_CONTENT_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldContentUrl);
            $params[PrmMng::PARAM_URL_CONTENT_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_URL_UPLOADS_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldUploadUrl);
            $params[PrmMng::PARAM_URL_UPLOADS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_URL_PLUGINS_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldPluginsUrl);
            $params[PrmMng::PARAM_URL_PLUGINS_NEW]->setValue($newVal);
        }

        // if empty value isn't overwritten
        if (strlen($params[PrmMng::PARAM_URL_MUPLUGINS_NEW]->getValue()) == 0) {
            $newVal = \DUPX_ArchiveConfig::getNewSubUrl($oldHomeUrl, $newHomeUrl, $oldMuPluginsUrl);
            $params[PrmMng::PARAM_URL_MUPLUGINS_NEW]->setValue($newVal);
        }
    }
}
