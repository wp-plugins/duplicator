<?php

/**
 * Customizes final report error messages
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

namespace Duplicator\Installer\Utils\Tests;

class MessageCustomizer
{
    const CONTEXT_SHORT_MESSAGE = 'short';
    const CONTEXT_LONG_MESSAGE  = 'long';
    const CONTEXT_NOTICE_ID     = 'notice-id';

    /**
     * Tries to apply each customization until one of them works
     *
     * @param string $shortMessage short message of notice to be customized
     * @param string $longMessage  long message of notice to be customized
     * @param string $noticeId     notice IDfinal-tests.php
     *
     * @return bool true if any of the customizations were applied, false otherwise
     */
    public static function applyAllNoticeCustomizations(&$shortMessage, &$longMessage, &$noticeId)
    {
        foreach (self::getCustomizationItems() as $item) {
            if ($item->conditionSatisfied($longMessage)) {
                $shortMessage = $item->apply($shortMessage, self::CONTEXT_SHORT_MESSAGE);
                $longMessage  = $item->apply($longMessage, self::CONTEXT_LONG_MESSAGE);
                $noticeId     = $item->apply($noticeId, self::CONTEXT_NOTICE_ID);
                return true;
            }
        }

        return false;
    }

    /**
     * Get customization to apply at error messages
     *
     * @return MessageCustomizerItem[] customizations list
     * @throws \Exception
     */
    protected static function getCustomizationItems()
    {
        $items   = array();
        $items[] = new MessageCustomizerItem(
            function ($string) {
                if (MessageCustomizer::getArchiveConfigData() == false) {
                    return false;
                }
                return preg_match("/undefined.*create_function/", $string) &&
                    version_compare(phpversion(), "8") >= 0 &&
                    version_compare(MessageCustomizer::getArchiveConfigData()->version_php, "8") < 0;
            },
            function ($string, $context) {
                if (MessageCustomizer::getArchiveConfigData() == false) {
                    return $string;
                }
                $phpVersionNew = MessageCustomizer::getTwoLevelVersion(phpversion());
                $phpVersionOld = MessageCustomizer::getTwoLevelVersion(MessageCustomizer::getArchiveConfigData()->version_php);
                $longMsgPrefix = "There is code in this site that is not compatible with PHP " . $phpVersionNew . ". " .
                    "To make the install work you will either have to\ninstall on PHP " .
                    $phpVersionOld . " or ";

                switch ($context) {
                    case MessageCustomizer::CONTEXT_SHORT_MESSAGE:
                        return "Source site or plugins are incompatible with PHP " . $phpVersionNew;
                    case MessageCustomizer::CONTEXT_LONG_MESSAGE:
                        if (($plugin = MessageCustomizer::getProblematicPluginFromError($string)) !== false) {
                            return $longMsgPrefix . "disable the plugin '{$plugin->name}' (slug: $plugin->slug) using a " .
                                "file manager of your choice.\nSee full error message below: \n\n" . $string;
                        } elseif (($theme = MessageCustomizer::getProblematicThemeFromError($string)) !== false) {
                            return $longMsgPrefix . "disable the theme '{$theme->themeName}' (slug: $theme->slug) using a " .
                                "file manager of your choice.\nSee full error message below: \n\n" . $string;
                        } else {
                            return $longMsgPrefix . "manually modify the affected files mentioned in the error trace below: \n\n" .
                                $string;
                        }
                    case MessageCustomizer::CONTEXT_NOTICE_ID:
                        return $string . '_php8';
                }
            }
        );

        return $items;
    }

    /**
     * Return the plugin that is causing the error message if present
     *
     * @param string $longMessage the long error message containing the error trace
     *
     * @return false|object object containing plugin info or false on failure
     */
    protected static function getProblematicPluginFromError($longMessage)
    {
        if (($archiveConfig     = self::getArchiveConfigData()) === false) {
            return false;
        }
        $oldMain           = $archiveConfig->wpInfo->targetRoot;
        $oldMuPlugins      = $archiveConfig->wpInfo->configs->realValues->originalPaths->muplugins;
        $oldPlugins        = $archiveConfig->wpInfo->configs->realValues->originalPaths->plugins;
        $relativeMuPlugins = str_replace($oldMain, "", $oldMuPlugins);
        $relativePlugins   = str_replace($oldMain, "", $oldPlugins);
        $regex             = "/(?:" . preg_quote($relativePlugins, "/") . "\/|" . preg_quote($relativeMuPlugins, "/") . "\/)(.*?)(\/|\.php).*$/m";
        if (!preg_match($regex, $longMessage, $matches)) {
            return false;
        }

        //matches the first part of the slug related to the plugin directory
        $slug = $matches[1];
        foreach ($archiveConfig->wpInfo->plugins as $plugin) {
            if (strpos($plugin->slug, $slug) === 0) {
                return $plugin;
            }
        }

        return false;
    }

    /**
     * Returns the theme that is causing the error message if present
     *
     * @param string $longMessage the long error message containing the error trace
     *
     * @return false|object object containing theme info or false
     */
    protected static function getProblematicThemeFromError($longMessage)
    {
        $archiveConfig  = self::getArchiveConfigData();
        $oldMain        = $archiveConfig->wpInfo->targetRoot;
        $oldThemes      = $archiveConfig->wpInfo->configs->realValues->originalPaths->themes;
        $relativeThemes = str_replace($oldMain, "", $oldThemes);

        file_put_contents(
            DUPX_INIT . "/my_log.txt",
            "OLD THEMES: {$oldThemes} \n" .
            "Relative themes: {$relativeThemes} \n" .
            "regex: " . "/(" . preg_quote($relativeThemes, "/") . "\/)(.*?)(\/|\.php).*$/m"
        );

        if (!preg_match("/(?:" . preg_quote($relativeThemes, "/") . "\/)(.*?)(?:\/|\.php).*$/m", $longMessage, $matches)) {
            return false;
        }

        $slug = $matches[1];
        foreach ($archiveConfig->wpInfo->themes as $theme) {
            if ($theme->slug == $slug) {
                return $theme;
            }
        }

        return false;
    }

    /**
     * Get package config data
     *
     * @return false|object package config data or false on failure
     */
    protected static function getArchiveConfigData()
    {
        static $archiveConfig = null;
        if (is_null($archiveConfig)) {
            if (
                ($path = glob(DUPX_INIT . "/dup-archive__*.txt")) === false ||
                count($path) !== 1
            ) {
                return $archiveConfig = false;
            }

            if (($json = file_get_contents($path[0])) === false) {
                return $archiveConfig = false;
            }

            $archiveConfig = json_decode($json);
            if (!is_object($archiveConfig)) {
                $archiveConfig = false;
            }
        }
        return $archiveConfig;
    }

    /**
     * @param string $version a version number
     *
     * @return string returns only the first 2 levels of the version numbers
     */
    private static function getTwoLevelVersion($version)
    {
        $arr = explode(".", $version);
        return $arr[0] . "." . $arr[1];
    }
}
