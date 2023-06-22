<?php

/**
 * Engines params descriptions
 *
 * @category  Duplicator
 * @package   Installer
 * @author    Snapcreek <admin@snapcreek.com>
 * @copyright 2011-2021  Snapcreek LLC
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 */

namespace Duplicator\Installer\Core\Params\Descriptors;

use DUP_Extraction;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Items\ParamItem;
use Duplicator\Installer\Core\Params\Items\ParamForm;
use Duplicator\Installer\Core\Params\Items\ParamOption;
use DUPX_InstallerState;
use DUPX_ArchiveConfig;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescEngines implements DescriptorInterface
{
    /**
     * Init params
     *
     * @param ParamItem[]|ParamForm[] $params params list
     *
     * @return void
     */
    public static function init(&$params)
    {
        $archiveConfig = \DUPX_ArchiveConfig::getInstance();

        $params[PrmMng::PARAM_ARCHIVE_ACTION] = new ParamForm(
            PrmMng::PARAM_ARCHIVE_ACTION,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
            'default'      => DUP_Extraction::ACTION_DO_NOTHING,
            'acceptValues' => array(
            DUP_Extraction::ACTION_DO_NOTHING,
            DUP_Extraction::ACTION_REMOVE_WP_FILES,
            DUP_Extraction::ACTION_REMOVE_ALL_FILES
            )
            ),
            array(
            'label'  => 'Archive Action:',
            'status' => function ($paramObj) {
                if (DUPX_InstallerState::isRestoreBackup()) {
                    return ParamForm::STATUS_INFO_ONLY;
                } else {
                    return ParamForm::STATUS_ENABLED;
                }
            },
            'options'        => array(
            new ParamOption(DUP_Extraction::ACTION_DO_NOTHING, 'Extract files over current files'),
            new ParamOption(DUP_Extraction::ACTION_REMOVE_WP_FILES, 'Remove WordPress core and content and extract (Pro)', ParamOption::OPT_DISABLED),
            new ParamOption(DUP_Extraction::ACTION_REMOVE_ALL_FILES, 'Remove all files except addon sites and extract (Pro)', ParamOption::OPT_DISABLED),
            new ParamOption(DUP_Extraction::ACTION_REMOVE_UPLOADS, 'Empty only uploads folder (Pro)', ParamOption::OPT_DISABLED)
            ),
            'wrapperClasses' => array('revalidate-on-change'),
            'subNote'        => function ($param) {
                return dupxTplRender('parts/params/archive-action-notes', array(
                'currentAction' => $param->getValue()
                ), false);
            },
            // Temporarly diabled for inital release 1.5
            //            'proFlagTitle'   => 'Upgrade Features',
            //            'proFlag'        => 'Enhance the install experience with custom extraction modes.
            // When performing an overwrite install process users can '
            //                . 'automate and customize that files they need to be installed.'
            )
        );

        $params[PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES] = new ParamForm(
            PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => DUP_Extraction::FILTER_NONE,
                'acceptValues' => array(DUP_Extraction::FILTER_NONE)
            ),
            array(
                'label'  => 'Skip Files:',
                'status' => ParamForm::STATUS_ENABLED,
                'options'        => array(
                    new ParamOption(DUP_Extraction::FILTER_NONE, 'Extract all files'),
                    new ParamOption(DUP_Extraction::FILTER_SKIP_WP_CORE, 'Skip extraction of WordPress core files (Pro)', ParamOption::OPT_DISABLED),
                    new ParamOption(
                        DUP_Extraction::FILTER_SKIP_CORE_PLUG_THEMES,
                        'Skip extraction of WordPress core files and plugins/themes existing on host (Pro)',
                        ParamOption::OPT_DISABLED
                    ),
                    new ParamOption(
                        DUP_Extraction::FILTER_ONLY_MEDIA_PLUG_THEMES,
                        'Extract only media files and new plugins/themes (Pro)',
                        ParamOption::OPT_DISABLED
                    )
                ),
                'wrapperClasses' => array('revalidate-on-change'),
                'subNote'        => dupxTplRender('parts/params/extract-skip-notes', array(
                    'currentSkipMode' => DUP_Extraction::FILTER_NONE
                    ), false),
            // Temporarly diabled for inital release 1.5
            //                'proFlagTitle'   => 'Upgrade Features',
            //                'proFlag'        => 'Exclude plugins and themes from extraction so as not to overwrite existing ones' .
            //                    '<ul>' .
            //                        '<li>Skip extraction of WP core files</li>' .
            //                        '<li>Skip extraction of WP core files &amp plugins/themes existing on the host</li>' .
            //                        '<li>Extract only media files &amp; new plugins/themes</li>' .
            //                    '</ul>'
            )
        );

        $engineOptions = self::getArchiveEngineOptions();

        $params[PrmMng::PARAM_ARCHIVE_ENGINE] = new ParamForm(
            PrmMng::PARAM_ARCHIVE_ENGINE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'          => $engineOptions['default'],
                'acceptValues'     => $engineOptions['acceptValues'],
                'sanitizeCallback' => function ($value) {
                    if (
                        PrmMng::getInstance()->getValue(
                            PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES
                        ) !== DUP_Extraction::FILTER_NONE && $value === DUP_Extraction::ENGINE_ZIP_SHELL
                    ) {
                        return DUP_Extraction::ENGINE_ZIP_CHUNK;
                    }
                    return $value;
                }
            ),
            array(
                'label'   => 'Extraction Mode:',
                'options' => $engineOptions['options'],
                'size'    => 0,
                'subNote' => $engineOptions['subNote'],
                'attr'    => array(
                    'onchange' => 'DUPX.onSafeModeSwitch();'
                )
            )
        );

        $params[PrmMng::PARAM_ZIP_THROTTLING] = new ParamForm(
            PrmMng::PARAM_ZIP_THROTTLING,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default'      => false
            ),
            array(
                'label'   => 'Server Throttling:',
                'checkboxLabel' => 'Enable archive extraction throttling',
                'status'  => function () {
                    if (
                        PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE) === DUP_Extraction::ENGINE_ZIP
                        || PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE) === DUP_Extraction::ENGINE_ZIP_CHUNK
                    ) {
                        return ParamForm::STATUS_ENABLED;
                    } else {
                        return ParamForm::STATUS_DISABLED;
                    }
                },
                'subNote' => '* This option is only available with Zip Formats'
            )
        );

        $params[PrmMng::PARAM_DB_ACTION] = new ParamForm(
            PrmMng::PARAM_DB_ACTION,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => 'empty',
                'acceptValues' => array(
                    \DUPX_DBInstall::DBACTION_CREATE,
                    \DUPX_DBInstall::DBACTION_EMPTY,
                    \DUPX_DBInstall::DBACTION_REMOVE_ONLY_TABLES,
                    \DUPX_DBInstall::DBACTION_RENAME,
                    \DUPX_DBInstall::DBACTION_MANUAL,
                    \DUPX_DBInstall::DBACTION_ONLY_CONNECT
                )
            ),
            array(
                'label'  => 'Action:',
                'status' => function ($paramObj) {
                    if (
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'wrapperClasses' => array('revalidate-on-change'),
                'options'        => array(
                    new ParamOption(\DUPX_DBInstall::DBACTION_EMPTY, 'Empty Database'),
                    new ParamOption(\DUPX_DBInstall::DBACTION_RENAME, 'Backup Existing Tables'),
                    new ParamOption(\DUPX_DBInstall::DBACTION_ONLY_CONNECT, 'Do Nothing (Advanced)', ParamOption::OPT_HIDDEN),
                    new ParamOption(\DUPX_DBInstall::DBACTION_MANUAL, 'Skip Database Extraction'),
                    new ParamOption(\DUPX_DBInstall::DBACTION_CREATE, 'Create New Database'),
                    new ParamOption(\DUPX_DBInstall::DBACTION_REMOVE_ONLY_TABLES, 'Overwrite Existing Tables (Pro)', ParamOption::OPT_DISABLED),
                ),
            // Temporarly diabled for inital release 1.5
            //                'proFlagTitle'   => 'Upgrade Features',
            //                'proFlag'        =>
            //                    'Duplicator Pro offers these additional actions on the database for greater flexibility.'
            //                    . '<ul class="pro-tip-flag">' .
            //                        '<li>Create New Database</li>' .
            //                        '<li>Overwrite Existing Tables</li>' .
            //                    '</ul>' .
            //                    'Users can create a new database on supported servers or act only on installation-specific tables without touching '
            //                    . 'others tables.  This process is useful if you want to install multiple instances of worpdress in the same database.'
            )
        );

        $params[PrmMng::PARAM_DB_ENGINE] = new ParamForm(
            PrmMng::PARAM_DB_ENGINE,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
            'default'      => \DUPX_DBInstall::ENGINE_CHUNK,
            'acceptValues' => array(
                \DUPX_DBInstall::ENGINE_CHUNK,
                \DUPX_DBInstall::ENGINE_NORMAL
            )),
            array(
            'label'   => 'Processing:',
            'size'    => 0,
            'options' => array(
                new ParamOption(\DUPX_DBInstall::ENGINE_CHUNK, 'Chunking mode'),
                new ParamOption(\DUPX_DBInstall::ENGINE_NORMAL, 'Single step')
            ))
        );

        $params[PrmMng::PARAM_DB_CHUNK] = new ParamItem(
            PrmMng::PARAM_DB_CHUNK,
            ParamForm::TYPE_BOOL,
            array(
                'default' => ($params[PrmMng::PARAM_DB_ENGINE]->getValue() === \DUPX_DBInstall::ENGINE_CHUNK)
            )
        );

        $params[PrmMng::PARAM_REPLACE_ENGINE] = new ParamItem(
            PrmMng::PARAM_REPLACE_ENGINE,
            ParamForm::TYPE_INT,
            array(
            'default'      => \DUPX_S3_Funcs::MODE_CHUNK,
            'acceptValues' => array(
                \DUPX_S3_Funcs::MODE_NORMAL,
                \DUPX_S3_Funcs::MODE_CHUNK,
                \DUPX_S3_Funcs::MODE_SKIP,
            ))
        );

        $oldHomePath                             = DUPX_ArchiveConfig::getInstance()->getRealValue('archivePaths')->home;
        $params[PrmMng::PARAM_SKIP_PATH_REPLACE] = new ParamForm(
            PrmMng::PARAM_SKIP_PATH_REPLACE,
            ParamForm::TYPE_BOOL,
            ParamForm::FORM_TYPE_CHECKBOX,
            array(
                'default' => in_array($oldHomePath, array('', '/html'))
            ),
            array(
                'label' => 'Skip Path Replace:',
                'checkboxLabel' => 'Skips the replacement of the source path',
                'status' => function (ParamForm $paramObj) {
                    $sourcePath = PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_OLD);
                    if (strlen($sourcePath) == 0) {
                        return ParamForm::STATUS_DISABLED;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                }
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
        if (
            $params[PrmMng::PARAM_ARCHIVE_ACTION]->getStatus() !== ParamItem::STATUS_OVERWRITE &&
            DUPX_InstallerState::isRestoreBackup($params[PrmMng::PARAM_INST_TYPE]->getValue())
        ) {
            $params[PrmMng::PARAM_ARCHIVE_ACTION]->setValue(DUP_Extraction::ACTION_REMOVE_WP_FILES);
        }

        if (DUPX_InstallerState::isRestoreBackup($params[PrmMng::PARAM_INST_TYPE]->getValue())) {
            $default = \DUPX_S3_Funcs::MODE_SKIP;
        } elseif ($params[PrmMng::PARAM_DB_ENGINE]->getValue() === \DUPX_DBInstall::ENGINE_CHUNK) {
            $default = \DUPX_S3_Funcs::MODE_CHUNK;
        } else {
            $default = \DUPX_S3_Funcs::MODE_NORMAL;
        }
        $params[PrmMng::PARAM_REPLACE_ENGINE]->setValue($default);

        if (
            ($params[PrmMng::PARAM_ARCHIVE_ENGINE]->getValue() === DUP_Extraction::ENGINE_ZIP
                || $params[PrmMng::PARAM_ARCHIVE_ENGINE]->getValue() === DUP_Extraction::ENGINE_ZIP_CHUNK)
            && \DUPX_Custom_Host_Manager::getInstance()->isHosting(\DUPX_Custom_Host_Manager::HOST_SITEGROUND)
        ) {
            $params[PrmMng::PARAM_ZIP_THROTTLING]->setValue(true);
        }
    }

    /**
     * Get db chunk engine value
     *
     * @return boolean
     */
    public static function getDbChunkFromParams()
    {
        return PrmMng::getInstance()->getValue(PrmMng::PARAM_DB_ENGINE) === \DUPX_DBInstall::ENGINE_CHUNK;
    }

    /**
     * Get replace engine mode
     *
     * @return integer
     */
    public static function getReplaceEngineModeFromParams()
    {
        $paramsManager = PrmMng::getInstance();
        if (DUPX_InstallerState::isRestoreBackup()) {
            return \DUPX_S3_Funcs::MODE_SKIP;
        } elseif ($paramsManager->getValue(PrmMng::PARAM_DB_ENGINE) === \DUPX_DBInstall::ENGINE_CHUNK) {
            return \DUPX_S3_Funcs::MODE_CHUNK;
        } else {
            return \DUPX_S3_Funcs::MODE_NORMAL;
        }
    }


    /**
     * Get archive engine options
     *
     * @return array
     */
    private static function getArchiveEngineOptions()
    {
        $archiveConfig = \DUPX_ArchiveConfig::getInstance();

        $acceptValues = array();
        $subNote      = null;
        if (($manualEnable = \DUPX_Conf_Utils::isManualExtractFilePresent()) === true) {
            $acceptValues[] = DUP_Extraction::ENGINE_MANUAL;
        } else {
            $subNote = <<<SUBNOTEHTML
* Option enabled when archive has been pre-extracted
<a href="https://duplicator.com/knowledge-base/how-to-handle-various-install-scenarios" target="_blank">[more info]</a>               
SUBNOTEHTML;
        }
        if (($zipEnable = ($archiveConfig->isZipArchive() && \DUPX_Conf_Utils::archiveExists() && \DUPX_Conf_Utils::classZipArchiveEnable())) === true) {
            $acceptValues[] = DUP_Extraction::ENGINE_ZIP;
            $acceptValues[] = DUP_Extraction::ENGINE_ZIP_CHUNK;
        }
        if (($shellZipEnable = ($archiveConfig->isZipArchive() && \DUPX_Conf_Utils::archiveExists() && \DUPX_Conf_Utils::shellExecUnzipEnable())) === true) {
            $acceptValues[] = DUP_Extraction::ENGINE_ZIP_SHELL;
        }
        if (($dupEnable = (!$archiveConfig->isZipArchive() && \DUPX_Conf_Utils::archiveExists())) === true) {
            $acceptValues[] = DUP_Extraction::ENGINE_DUP;
        }

        $options   = array();
        $options[] = new ParamOption(
            DUP_Extraction::ENGINE_MANUAL,
            'Manual Archive Extraction',
            $manualEnable ? ParamOption::OPT_ENABLED : ParamOption::OPT_DISABLED
        );

        if ($archiveConfig->isZipArchive()) {
            //ZIP-ARCHIVE
            $options[] = new ParamOption(
                DUP_Extraction::ENGINE_ZIP,
                'PHP ZipArchive',
                $zipEnable ? ParamOption::OPT_ENABLED : ParamOption::OPT_DISABLED
            );

            $options[] = new ParamOption(
                DUP_Extraction::ENGINE_ZIP_CHUNK,
                'PHP ZipArchive Chunking',
                $zipEnable ? ParamOption::OPT_ENABLED : ParamOption::OPT_DISABLED
            );

            $options[] = new ParamOption(
                DUP_Extraction::ENGINE_ZIP_SHELL,
                'Shell Exec Unzip',
                function () {
                    $archiveConfig = \DUPX_ArchiveConfig::getInstance();
                    $pathsMapping  = $archiveConfig->getPathsMapping();
                    if (PrmMng::getInstance()->getValue(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES) !== DUP_Extraction::FILTER_NONE) {
                        return ParamOption::OPT_DISABLED;
                    }
                    if (is_array($pathsMapping) && count($pathsMapping) > 1) {
                        return ParamOption::OPT_DISABLED;
                    }
                    if ($archiveConfig->isZipArchive() && \DUPX_Conf_Utils::archiveExists() && \DUPX_Conf_Utils::shellExecUnzipEnable()) {
                        return ParamOption::OPT_ENABLED;
                    }
                    return ParamOption::OPT_DISABLED;
                }
            );
        } else {
            // DUPARCHIVE
            $options[] = new ParamOption(
                DUP_Extraction::ENGINE_DUP,
                'DupArchive',
                $dupEnable ? ParamOption::OPT_ENABLED : ParamOption::OPT_DISABLED
            );
        }

        if ($manualEnable) {
            $default = DUP_Extraction::ENGINE_MANUAL;
        } elseif ($zipEnable) {
            $default = DUP_Extraction::ENGINE_ZIP_CHUNK;
        } elseif ($shellZipEnable) {
            $default = DUP_Extraction::ENGINE_ZIP_SHELL;
        } elseif ($dupEnable) {
            $default = DUP_Extraction::ENGINE_DUP;
        } else {
            $default = null;
        }

        return array(
            'options'      => $options,
            'acceptValues' => $acceptValues,
            'default'      => $default,
            'subNote'      => $subNote
        );
    }
}
