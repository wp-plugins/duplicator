<?php

namespace Duplicator\Installer\Core\Deploy\Database;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapIO;
use DUPX_ArchiveConfig;
use DUPX_InstallerState;
use DUPX_S_R_ITEM;
use DUPX_S_R_MANAGER;
use DUPX_U;
use DUPX_UpdateEngine;
use Exception;

class DbReplace
{
    /** @var string */
    protected $mainUrlOld = '';
    /** @var string */
    protected $mainUrlNew = '';
    /** @var bool */
    protected $forceReplaceSiteSubfolders = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $prmMng           = PrmMng::getInstance();
        $this->mainUrlOld = $prmMng->getValue(PrmMng::PARAM_URL_OLD);
        $this->mainUrlNew = $prmMng->getValue(PrmMng::PARAM_URL_NEW);
    }

    /**
     * Set search and replace strings
     *
     * @return bool
     */
    public function setSearchReplace()
    {
        switch (DUPX_InstallerState::getInstType()) {
            case DUPX_InstallerState::INSTALL_SINGLE_SITE:
                $this->setGlobalSearchAndReplaceList();
                break;
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBDOMAIN:
            case DUPX_InstallerState::INSTALL_SINGLE_SITE_ON_SUBFOLDER:
                throw new Exception('mode not avaiable');
            case DUPX_InstallerState::INSTALL_RBACKUP_SINGLE_SITE:
                throw new Exception('Replace engine isn\'t avaiable for restore backup mode');
            case DUPX_InstallerState::INSTALL_NOT_SET:
            default:
                throw new Exception('Invalid installer mode');
        }

        return true;
    }

    /**
     * Set global search replace
     *
     * @return void
     */
    private function setGlobalSearchAndReplaceList()
    {
        $srManager     = DUPX_S_R_MANAGER::getInstance();
        $paramsManager = PrmMng::getInstance();

        // DIRS PATHS
        $this->addReplaceEnginePaths($srManager);

        Log::info('GLOBAL SEARCH REPLACE ', Log::LV_DETAILED);

        if (
            !DUPX_InstallerState::isInstallerCreatedInThisLocation()
        ) {
            $uploadUrlOld = $paramsManager->getValue(PrmMng::PARAM_URL_UPLOADS_OLD);
            $uploadUrlNew = $paramsManager->getValue(PrmMng::PARAM_URL_UPLOADS_NEW);

            if (self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $uploadUrlOld, $uploadUrlNew)) {
                $srManager->addItem($uploadUrlOld, $uploadUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
            }

            $siteUrlOld = $paramsManager->getValue(PrmMng::PARAM_SITE_URL_OLD);
            $siteUrlNew = $paramsManager->getValue(PrmMng::PARAM_SITE_URL);
            if (self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $siteUrlOld, $siteUrlNew)) {
                $srManager->addItem($siteUrlOld, $siteUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            }

            $srManager->addItem($this->mainUrlOld, $this->mainUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
        }

        $pluginsUrlOld = $paramsManager->getValue(PrmMng::PARAM_URL_PLUGINS_OLD);
        $pluginsUrlNew = $paramsManager->getValue(PrmMng::PARAM_URL_PLUGINS_NEW);
        if (
            $this->forceReplaceSiteSubfolders ||
            self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $pluginsUrlOld, $pluginsUrlNew)
        ) {
            $srManager->addItem($pluginsUrlOld, $pluginsUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }

        $mupluginsUrlOld = $paramsManager->getValue(PrmMng::PARAM_URL_MUPLUGINS_OLD);
        $mupluginsUrlNew = $paramsManager->getValue(PrmMng::PARAM_URL_MUPLUGINS_NEW);
        if (
            $this->forceReplaceSiteSubfolders ||
            self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $mupluginsUrlOld, $mupluginsUrlNew)
        ) {
            $srManager->addItem($mupluginsUrlOld, $mupluginsUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }

        $contentUrlOld = $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_OLD);
        $contentUrlNew = $paramsManager->getValue(PrmMng::PARAM_URL_CONTENT_NEW);
        if (
            $this->forceReplaceSiteSubfolders ||
            self::checkRelativeAndAbsoluteDiff($this->mainUrlOld, $this->mainUrlNew, $contentUrlOld, $contentUrlNew)
        ) {
            $srManager->addItem($contentUrlOld, $contentUrlNew, DUPX_S_R_ITEM::TYPE_URL_NORMALIZE_DOMAIN, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P2);
        }

        // Replace email address (xyz@oldomain.com to xyz@newdomain.com).
        if ($paramsManager->getValue(PrmMng::PARAM_EMAIL_REPLACE)) {
            $at_old_domain = '@' . DUPX_U::getDomain($this->mainUrlOld);
            $at_new_domain = '@' . DUPX_U::getDomain($this->mainUrlNew);
            $srManager->addItem($at_old_domain, $at_new_domain, DUPX_S_R_ITEM::TYPE_STRING, DUPX_UpdateEngine::SR_PRORITY_LOW);
        }
    }

    /**
     * add paths to replace on sear/replace engine
     *
     * @return void
     */
    private function addReplaceEnginePaths()
    {
        $srManager     = DUPX_S_R_MANAGER::getInstance();
        $paramsManager = PrmMng::getInstance();
        if ($paramsManager->getValue(PrmMng::PARAM_SKIP_PATH_REPLACE)) {
            return;
        }

        $archiveConfig = DUPX_ArchiveConfig::getInstance();
        $originalPaths = $archiveConfig->getRealValue('originalPaths');
        $mainPathOld   = $paramsManager->getValue(PrmMng::PARAM_PATH_OLD);
        $mainPathNew   = $paramsManager->getValue(PrmMng::PARAM_PATH_NEW);

        if (
            !DUPX_InstallerState::isInstallerCreatedInThisLocation()
        ) {
            $uploadPathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_OLD);
            $uploadPathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_UPLOADS_NEW);
            if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $uploadPathOld, $uploadPathNew)) {
                $srManager->addItem($uploadPathOld, $uploadPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
            }
            if (
                $originalPaths->uploads != $uploadPathOld &&
                self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->uploads, $uploadPathNew)
            ) {
                $srManager->addItem($originalPaths->uploads, $uploadPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
            }

            $corePathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_OLD);
            $corePathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_WP_CORE_NEW);
            if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $corePathOld, $corePathNew)) {
                $srManager->addItem($corePathOld, $corePathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            }
            if (
                $originalPaths->abs != $corePathOld &&
                self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->abs, $corePathNew)
            ) {
                $srManager->addItem($originalPaths->abs, $corePathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            }

            $srManager->addItem($mainPathOld, $mainPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            if ($originalPaths->home != $mainPathOld) {
                $srManager->addItem($originalPaths->home, $mainPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P3);
            }
        }

        $pluginsPathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_OLD);
        $pluginsPathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_PLUGINS_NEW);
        if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $pluginsPathOld, $pluginsPathNew)) {
            $srManager->addItem($pluginsPathOld, $pluginsPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }
        if (
            $originalPaths->plugins != $pluginsPathOld &&
            self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->plugins, $pluginsPathNew)
        ) {
            $srManager->addItem($originalPaths->plugins, $pluginsPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }

        $mupluginsPathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_OLD);
        $mupluginsPathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_MUPLUGINS_NEW);
        if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $mupluginsPathOld, $mupluginsPathNew)) {
            $srManager->addItem($mupluginsPathOld, $mupluginsPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }
        if (
            $originalPaths->muplugins != $mupluginsPathOld &&
            self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->muplugins, $mupluginsPathNew)
        ) {
            $srManager->addItem($originalPaths->muplugins, $mupluginsPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P1);
        }

        $contentPathOld = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_OLD);
        $contentPathNew = $paramsManager->getValue(PrmMng::PARAM_PATH_CONTENT_NEW);
        if (self::checkRelativeAndAbsoluteDiff($mainPathOld, $mainPathNew, $contentPathOld, $contentPathNew)) {
            $srManager->addItem($contentPathOld, $contentPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P2);
        }
        if (
            $originalPaths->wpcontent != $contentPathOld &&
            self::checkRelativeAndAbsoluteDiff($originalPaths->home, $mainPathNew, $originalPaths->wpcontent, $contentPathNew)
        ) {
            $srManager->addItem($originalPaths->wpcontent, $contentPathNew, DUPX_S_R_ITEM::TYPE_PATH, DUPX_UpdateEngine::SR_PRORITY_GENERIC_SUBST_P2);
        }
    }

    /**
     * Check if sub path if different
     *
     * @param string $mainOld main old path
     * @param string $mainNew main new path
     * @param string $old     old sub path
     * @param string $new     new sub path
     *
     * @return bool
     */
    private static function checkRelativeAndAbsoluteDiff($mainOld, $mainNew, $old, $new)
    {
        $mainOld = SnapIO::safePath($mainOld);
        $mainNew = SnapIO::safePath($mainNew);
        $old     = SnapIO::safePath($old);
        $new     = SnapIO::safePath($new);

        $log = "CHECK REL AND ABS DIF\n" .
            "\tMAIN OLD: " . Log::v2str($mainOld) . "\n" .
            "\tMAIN NEW: " . Log::v2str($mainNew) . "\n" .
            "\tOLD: " . Log::v2str($old) . "\n" .
            "\tNEW: " . Log::v2str($new);
        Log::info($log, Log::LV_DEBUG);

        $isRelativePathDifferent = substr($old, strlen($mainOld)) !== substr($new, strlen($mainNew));

        if (strpos($old, $mainOld) !== 0 || strpos($new, $mainNew) !== 0 || $isRelativePathDifferent) {
            Log::info("\t*** RESULT: TRUE", Log::LV_DEBUG);
            return true;
        } else {
            Log::info("\t*** RESULT: FALSE", Log::LV_DEBUG);
            return false;
        }
    }
}
