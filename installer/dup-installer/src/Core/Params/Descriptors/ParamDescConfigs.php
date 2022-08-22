<?php

/**
 * Configs(htaccess, wp-config ...) params descriptions
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
use Duplicator\Installer\Core\Params\Items\ParamOption;
use Duplicator\Installer\Utils\Log\Log;
use DUPX_InstallerState;
use DUPX_Template;
use DUPX_TemplateItem;
use Exception;

/**
 * class where all parameters are initialized. Used by the param manager
 */
final class ParamDescConfigs implements DescriptorInterface
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
        $params[PrmMng::PARAM_INST_TYPE] = new ParamForm(
            PrmMng::PARAM_INST_TYPE,
            ParamForm::TYPE_INT,
            ParamForm::FORM_TYPE_RADIO,
            array(
                'default'        => DUPX_InstallerState::INSTALL_NOT_SET,
                'acceptValues'   => array(__CLASS__, 'getInstallTypesAcceptValues')
            ),
            array(
                'status' => ParamForm::STATUS_ENABLED,
                'label'          => 'Install Type:',
                'wrapperClasses' => array('group-block', 'revalidate-on-change'),
                'options'        => self::getInstallTypeOptions(),
            // Temporarly diabled for inital release 1.5
            //                'proFlagTitle'   => 'Upgrade Features',
            //                'proFlag'        => 'Improve the install experiance with support for these popular install modes:'
            //                . '<ul class="pro-tip-flag">' .
            //                        '<li>Full Multisite Support</li>' .
            //                        '<li>Install from Remote Server</li>' .
            //                        '<li>Restore from Recovery Point</li>' .
            //                    '</ul>'
            )
        );

        $params[PrmMng::PARAM_WP_CONFIG] = new ParamForm(
            PrmMng::PARAM_WP_CONFIG,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => 'modify',
                'acceptValues' => array(
                    'modify',
                    'nothing',
                    'new'
                )
            ),
            array(
                'label'          => 'WordPress:',
                'wrapperClasses' => 'medium',
                'status'         => function (ParamItem $paramObj) {
                    if (
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'options' => array(
                    new ParamOption('nothing', 'Do nothing'),
                    new ParamOption('modify', 'Modify original'),
                    new ParamOption('new', 'Create new from wp-config sample')
                ),
                'subNote' => 'wp-config.php'
            )
        );

        $params[PrmMng::PARAM_HTACCESS_CONFIG] = new ParamForm(
            PrmMng::PARAM_HTACCESS_CONFIG,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => 'new',
                'acceptValues' => array(
                    'new',
                    'original',
                    'nothing'
                )
            ),
            array(
                'label'          => 'Apache:',
                'wrapperClasses' => 'medium',
                'status'         => function (ParamItem $paramObj) {
                    if (
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'options' => array(
                    new ParamOption('nothing', 'Do nothing'),
                    new ParamOption('original', 'Retain original from Archive.zip/daf'),
                    new ParamOption('new', 'Create new (recommended)')
                ),
                'subNote' => '.htaccess'
            )
        );

        $params[PrmMng::PARAM_OTHER_CONFIG] = new ParamForm(
            PrmMng::PARAM_OTHER_CONFIG,
            ParamForm::TYPE_STRING,
            ParamForm::FORM_TYPE_SELECT,
            array(
                'default'      => 'new',
                'acceptValues' => array(
                    'new',
                    'original',
                    'nothing'
                )
            ),
            array(
                'label'          => 'General:',
                'wrapperClasses' => 'medium',
                'status'         => function (ParamItem $paramObj) {
                    if (
                        DUPX_InstallerState::isRestoreBackup()
                    ) {
                        return ParamForm::STATUS_INFO_ONLY;
                    } else {
                        return ParamForm::STATUS_ENABLED;
                    }
                },
                'options' => array(
                    new ParamOption('nothing', 'Do nothing'),
                    new ParamOption('original', 'Retain original from Archive.zip/daf'),
                    new ParamOption('new', 'Reset')
                ),
                'subNote' => 'includes: php.ini, .user.ini, web.config'
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
        $installType = $params[PrmMng::PARAM_INST_TYPE]->getValue();
        if ($installType == DUPX_InstallerState::INSTALL_NOT_SET) {
            $acceptValues = $params[PrmMng::PARAM_INST_TYPE]->getAcceptValues();
            $params[PrmMng::PARAM_INST_TYPE]->setValue(self::getInstTypeByPriority($acceptValues));
        }

        $installType = $params[PrmMng::PARAM_INST_TYPE]->getValue();
        if (DUPX_InstallerState::isRestoreBackup($installType)) {
            if (\DUPX_Custom_Host_Manager::getInstance()->isManaged()) {
                $params[PrmMng::PARAM_WP_CONFIG]->setValue('nothing');
                $params[PrmMng::PARAM_HTACCESS_CONFIG]->setValue('nothing');
                $params[PrmMng::PARAM_OTHER_CONFIG]->setValue('nothing');
            } else {
                $params[PrmMng::PARAM_WP_CONFIG]->setValue('modify');
                $params[PrmMng::PARAM_HTACCESS_CONFIG]->setValue('original');
                $params[PrmMng::PARAM_OTHER_CONFIG]->setValue('original');
            }
        }
    }

    /**
     * Return default install type from install types enabled
     *
     * @param  int[] $acceptValues install types enabled
     *
     * @return int
     */
    protected static function getInstTypeByPriority($acceptValues)
    {
        $defaultPriority = array(
            DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE,
            DUPX_InstallerState::INSTALL_SINGLE_SITE
        );

        foreach ($defaultPriority as $current) {
            if (in_array($current, $acceptValues)) {
                return $current;
            }
        }

        throw new Exception('No default value found on proprity list');
    }

    /**
     *
     * @return ParamOption[]
     */
    protected static function getInstallTypeOptions()
    {
        $result = array();

        $option   = new ParamOption(
            DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE,
            'Restore single site',
            array(__CLASS__, 'typeOptionsVisibility')
        );
        $result[] = $option;

        $option   = new ParamOption(
            DUPX_InstallerState::INSTALL_SINGLE_SITE,
            '<b>Full</b> install single site',
            array(__CLASS__, 'typeOptionsVisibility')
        );
        $result[] = $option;

        $option   = new ParamOption(
            DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN,
            '<b>Import</b> single site into multisite network',
            array(__CLASS__, 'typeOptionsVisibility')
        );
        $result[] = $option;

        $option   = new ParamOption(
            DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER,
            '<b>Import</b> single site into multisite network',
            array(__CLASS__, 'typeOptionsVisibility')
        );
        $result[] = $option;

        return $result;
    }

    /**
     * Return option type status
     *
     * @param ParamOption $option install type option
     *
     * @return string option status
     */
    public static function typeOptionsVisibility(ParamOption $option)
    {
        $archiveConfig = \DUPX_ArchiveConfig::getInstance();
        $overwriteData = PrmMng::getInstance()->getValue(PrmMng::PARAM_OVERWRITE_SITE_DATA);
        $isOwrMode     = PrmMng::getInstance()->getValue(PrmMng::PARAM_INSTALLER_MODE) === DUPX_InstallerState::MODE_OVR_INSTALL;

        switch ($option->value) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                if ($archiveConfig->mu_mode != 0) {
                    return ParamOption::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
                if (!$isOwrMode || !$overwriteData['isMultisite'] || !$overwriteData['subdomain']) {
                    return ParamOption::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                if (!$isOwrMode || !$overwriteData['isMultisite'] || $overwriteData['subdomain']) {
                    return ParamOption::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
                if ($archiveConfig->mu_mode != 0 || !DUPX_InstallerState::isInstallerCreatedInThisLocation()) {
                    return ParamOption::OPT_HIDDEN;
                }
                break;
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Install type not valid ' . $option->value);
        }

        $acceptValues = self::getInstallTypesAcceptValues();
        return in_array($option->value, $acceptValues) ? ParamOption::OPT_ENABLED : ParamOption::OPT_DISABLED;
    }

    /**
     *
     * @return int[]
     */
    public static function getInstallTypesAcceptValues()
    {
        $acceptValues   = array();
        $isSameLocation = DUPX_InstallerState::isInstallerCreatedInThisLocation();

        $acceptValues[] = DUPX_InstallerState::INSTALL_SINGLE_SITE;
        if ($isSameLocation) {
            $acceptValues[] = DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE;
        }

        return $acceptValues;
    }

    /**
     * Return install type option note
     *
     * @param ParamOption $option install type option
     *
     * @return string
     */
    public static function getInstallTypesNotes(ParamOption $option)
    {
        switch ($option->value) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                return '';
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                return '';
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Install type not valid ' . $option->value);
        }
    }
}
