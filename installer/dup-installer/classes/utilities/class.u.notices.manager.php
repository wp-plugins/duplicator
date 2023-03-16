<?php

/**
 * Notice manager
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Log\Log;
use Duplicator\Libs\Snap\SnapJson;

/**
 * Notice manager
 * singleton class
 */
final class DUPX_NOTICE_MANAGER
{
    const ADD_NORMAL                   = 0; // add notice in list
    const ADD_UNIQUE                   = 1; // add if unique id don't exists
    const ADD_UNIQUE_UPDATE            = 2; // add or update notice unique id
    const ADD_UNIQUE_APPEND            = 3; // append long msg
    const ADD_UNIQUE_APPEND_IF_EXISTS  = 4; // append long msg if already exists item
    const ADD_UNIQUE_PREPEND           = 5; // append long msg
    const ADD_UNIQUE_PREPEND_IF_EXISTS = 6; // prepend long msg if already exists item
    const DEFAULT_UNIQUE_ID_PREFIX     = '__auto_unique_id__';

    private static $uniqueCountId = 0;

    /**
     *
     * @var DUPX_NOTICE_ITEM[]
     */
    private $nextStepNotices = array();

    /**
     *
     * @var DUPX_NOTICE_ITEM[]
     */
    private $finalReporNotices = array();

    /**
     *
     * @var self
     */
    private static $instance = null;

    /**
     *
     * @var string
     */
    private $persistanceFile = null;

    /**
     *
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->persistanceFile = $GLOBALS["NOTICES_FILE_PATH"];
        $this->loadNotices();
    }

    /**
     * save notices from json file
     *
     * @return bool
     */
    public function saveNotices()
    {
        if (class_exists('Duplicator\\Installer\\Utils\\Log\\Log', false)) {
            Log::info('SAVE NOTICES', Log::LV_DEBUG);
        }
        $notices = array(
            'globalData'  => array(
                'uniqueCountId' => self::$uniqueCountId
            ),
            'nextStep'    => array(),
            'finalReport' => array()
        );

        foreach ($this->nextStepNotices as $uniqueId => $notice) {
            $notices['nextStep'][$uniqueId] = $notice->toArray();
        }

        foreach ($this->finalReporNotices as $uniqueId => $notice) {
            $notices['finalReport'][$uniqueId] = $notice->toArray();
        }

        $json = SnapJson::jsonEncodePPrint($notices);
        if (file_put_contents($this->persistanceFile, $json) === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * load notice from json file
     */
    private function loadNotices()
    {
        if (file_exists($this->persistanceFile)) {
            if (class_exists('Duplicator\\Installer\\Utils\\Log\\Log', false)) {
                Log::info('LOAD NOTICES', Log::LV_DEBUG);
            }
            $json    = file_get_contents($this->persistanceFile);
            $notices = json_decode($json, true);

            $this->nextStepNotices   = array();
            $this->finalReporNotices = array();

            if (!empty($notices['nextStep'])) {
                foreach ($notices['nextStep'] as $uniqueId => $notice) {
                    $this->nextStepNotices[$uniqueId] = DUPX_NOTICE_ITEM::getItemFromArray($notice);
                }
            }

            if (!empty($notices['finalReport'])) {
                foreach ($notices['finalReport'] as $uniqueId => $notice) {
                    $this->finalReporNotices[$uniqueId] = DUPX_NOTICE_ITEM::getItemFromArray($notice);
                }
            }

            self::$uniqueCountId = $notices['globalData']['uniqueCountId'];
        } else {
            $this->resetNotices();
        }
    }

    /**
     * remove all notices and save reset file
     */
    public function resetNotices()
    {
        if (class_exists('Duplicator\\Installer\\Utils\\Log\\Log', false)) {
            Log::info('RESET NOTICES', Log::LV_DEBUG);
        }
        $this->nextStepNotices   = array();
        $this->finalReporNotices = array();
        self::$uniqueCountId     = 0;
        $this->saveNotices();
    }

    /**
     * return next step notice by id
     *
     * @param string $id
     *
     * @return DUPX_NOTICE_ITEM
     */
    public function getNextStepNoticeById($id)
    {
        if (isset($this->nextStepNotices[$id])) {
            return $this->nextStepNotices[$id];
        } else {
            return null;
        }
    }

    /**
     * return last report notice by id
     *
     * @param string $id
     *
     * @return DUPX_NOTICE_ITEM
     */
    public function getFinalReporNoticeById($id)
    {
        if (isset($this->finalReporNotices[$id])) {
            return $this->finalReporNotices[$id];
        } else {
            return null;
        }
    }

    /**
     *
     * @param array|DUPX_NOTICE_ITEM $item // if string add new notice obj with item message and level param
     *                                            // if array must be [
     *                                                                   'shortMsg' => text,
     *                                                                   'level' => level,
     *                                                                   'longMsg' => html text,
     *                                                                   'sections' => sections list,
     *                                                                   'faqLink' => [
     *                                                                                     'url' => external link
     *                                                                                     'label' => link text if empty get external url link
     *                                                                               ]
     *                                                                 ]
     * @param int $mode         // ADD_NORMAL | ADD_UNIQUE | ADD_UNIQUE_UPDATE
     * @param string $uniqueId  // used for ADD_UNIQUE or ADD_UNIQUE_UPDATE
     *
     * @return string   // notice insert id
     */
    public function addBothNextAndFinalReportNotice($item, $mode = self::ADD_NORMAL, $uniqueId = null)
    {
        $this->addNextStepNotice($item, $mode, $uniqueId);
        $this->addFinalReportNotice($item, $mode, $uniqueId);
    }

    /**
     *
     * @param array|DUPX_NOTICE_ITEM $item // if string add new notice obj with item message and level param
     *                                            // if array must be [
     *                                                                   'shortMsg' => text,
     *                                                                   'level' => level,
     *                                                                   'longMsg' => html text,
     *                                                                   'sections' => sections list,
     *                                                                   'faqLink' => [
     *                                                                                     'url' => external link
     *                                                                                     'label' => link text if empty get external url link
     *                                                                               ]
     *                                                                 ]
     * @param int $mode         // ADD_NORMAL | ADD_UNIQUE | ADD_UNIQUE_UPDATE
     * @param string $uniqueId  // used for ADD_UNIQUE or ADD_UNIQUE_UPDATE
     *
     * @return string   // notice insert id
     */
    public function addNextStepNotice($item, $mode = self::ADD_NORMAL, $uniqueId = null)
    {
        if (!is_array($item) && !($item instanceof DUPX_NOTICE_ITEM)) {
            throw new Exception('Invalid item param');
        }
        return self::addReportNoticeToList($this->nextStepNotices, $item, $mode, $uniqueId);
    }

    /**
     * addNextStepNotice wrapper to add simple message with error level
     *
     * @param string $message
     * @param int $level        // warning level
     * @param int $mode         // ADD_NORMAL | ADD_UNIQUE | ADD_UNIQUE_UPDATE
     * @param string $uniqueId  // used for ADD_UNIQUE or ADD_UNIQUE_UPDATE
     *
     * @return string   // notice insert id
     */
    public function addNextStepNoticeMessage($message, $level = DUPX_NOTICE_ITEM::INFO, $mode = self::ADD_NORMAL, $uniqueId = null)
    {
        return $this->addNextStepNotice(array(
                'shortMsg' => $message,
                'level'    => $level,
                ), $mode, $uniqueId);
    }

    /**
     *
     * @param array|DUPX_NOTICE_ITEM $item // if string add new notice obj with item message and level param
     *                                            // if array must be [
     *                                                                   'shortMsg' => text,
     *                                                                   'level' => level,
     *                                                                   'longMsg' => html text,
     *                                                                   'sections' => sections list,
     *                                                                   'faqLink' => [
     *                                                                                     'url' => external link
     *                                                                                     'label' => link text if empty get external url link
     *                                                                               ]
     *                                                                 ]
     * @param int $mode         // ADD_NORMAL | ADD_UNIQUE | ADD_UNIQUE_UPDATE
     * @param string $uniqueId  // used for ADD_UNIQUE or ADD_UNIQUE_UPDATE
     *
     * @return string   // notice insert id
     */
    public function addFinalReportNotice($item, $mode = self::ADD_NORMAL, $uniqueId = null)
    {
        if (!is_array($item) && !($item instanceof DUPX_NOTICE_ITEM)) {
            throw new Exception('Invalid item param');
        }
        return self::addReportNoticeToList($this->finalReporNotices, $item, $mode, $uniqueId);
    }

    /**
     * addFinalReportNotice wrapper to add simple message with error level
     *
     * @param string $message
     * @param string|string[] $sections   // message sections on final report
     * @param int $level        // warning level
     * @param int $mode         // ADD_NORMAL | ADD_UNIQUE | ADD_UNIQUE_UPDATE
     * @param string $uniqueId  // used for ADD_UNIQUE or ADD_UNIQUE_UPDATE
     *
     * @return string   // notice insert id
     */
    public function addFinalReportNoticeMessage($message, $sections, $level = DUPX_NOTICE_ITEM::INFO, $mode = self::ADD_NORMAL, $uniqueId = null)
    {
        return $this->addFinalReportNotice(array(
                'shortMsg' => $message,
                'level'    => $level,
                'sections' => $sections,
                ), $mode, $uniqueId);
    }

    /**
     *
     * @param array $list
     * @param array|DUPX_NOTICE_ITEM $item // if string add new notice obj with item message and level param
     *                                            // if array must be [
     *                                                                   'shortMsg' => text,
     *                                                                   'level' => level,
     *                                                                   'longMsg' => html text,
     *                                                                   'sections' => sections list,
     *                                                                   'faqLink' => [
     *                                                                                     'url' => external link
     *                                                                                     'label' => link text if empty get external url link
     *                                                                               ]
     *                                                                 ]
     * @param int $mode         // ADD_NORMAL | ADD_UNIQUE | ADD_UNIQUE_UPDATE
     * @param string $uniqueId  // used for ADD_UNIQUE or ADD_UNIQUE_UPDATE
     *
     * @return string   // notice insert id
     */
    private static function addReportNoticeToList(&$list, $item, $mode = self::ADD_NORMAL, $uniqueId = null)
    {
        switch ($mode) {
            case self::ADD_UNIQUE:
                if (empty($uniqueId)) {
                    throw new Exception('uniqueId can\'t be empty');
                }
                if (isset($list[$uniqueId])) {
                    return $uniqueId;
                }
            // no break -> continue on unique update
            case self::ADD_UNIQUE_UPDATE:
                if (empty($uniqueId)) {
                    throw new Exception('uniqueId can\'t be empty');
                }
                $insertId = $uniqueId;
                break;
            case self::ADD_UNIQUE_APPEND_IF_EXISTS:
                if (empty($uniqueId)) {
                    throw new Exception('uniqueId can\'t be empty');
                }
                if (!isset($list[$uniqueId])) {
                    return false;
                }
            // no break
            case self::ADD_UNIQUE_APPEND:
                if (empty($uniqueId)) {
                    throw new Exception('uniqueId can\'t be empty');
                }
                $insertId = $uniqueId;
                // if item id exist append long msg
                if (isset($list[$uniqueId])) {
                    $tempObj                   = self::getObjFromParams($item);
                    $list[$uniqueId]->longMsg .= $tempObj->longMsg;
                    $item                      = $list[$uniqueId];
                }
                break;
            case self::ADD_UNIQUE_PREPEND_IF_EXISTS:
                if (empty($uniqueId)) {
                    throw new Exception('uniqueId can\'t be empty');
                }
                if (!isset($list[$uniqueId])) {
                    return false;
                }
            // no break
            case self::ADD_UNIQUE_PREPEND:
                if (empty($uniqueId)) {
                    throw new Exception('uniqueId can\'t be empty');
                }
                $insertId = $uniqueId;
                // if item id exist append long msg
                if (isset($list[$uniqueId])) {
                    $tempObj                  = self::getObjFromParams($item);
                    $list[$uniqueId]->longMsg = $tempObj->longMsg . $list[$uniqueId]->longMsg;
                    $item                     = $list[$uniqueId];
                }
                break;
            case self::ADD_NORMAL:
            default:
                if (empty($uniqueId)) {
                    $insertId = self::getNewAutoUniqueId();
                } else {
                    $insertId = $uniqueId;
                }
        }

        $list[$insertId] = self::getObjFromParams($item);
        return $insertId;
    }

    /**
     *
     * @param string|array|DUPX_NOTICE_ITEM $item // if string add new notice obj with item message and level param
     *                                            // if array must be [
     *                                                                   'shortMsg' => text,
     *                                                                   'level' => level,
     *                                                                   'longMsg' => html text,
     *                                                                   'sections' => sections list,
     *                                                                   'faqLink' => [
     *                                                                                     'url' => external link
     *                                                                                     'label' => link text if empty get external url link
     *                                                                               ]
     *                                                                 ]
     * @param int $level message level considered only in the case where $item is a string.
     *
     * @return \DUPX_NOTICE_ITEM
     */
    private static function getObjFromParams($item, $level = DUPX_NOTICE_ITEM::INFO)
    {
        if ($item instanceof DUPX_NOTICE_ITEM) {
            $newObj = $item;
        } elseif (is_array($item)) {
            $newObj = DUPX_NOTICE_ITEM::getItemFromArray($item);
        } elseif (is_string($item)) {
            $newObj = new DUPX_NOTICE_ITEM($item, $level);
        } else {
            throw new Exception('Notice input not valid');
        }

        return $newObj;
    }

    /**
     *
     * @param null|string $section if null is count global
     * @param int $level error level
     * @param string $operator > < >= <= = !=
     *
     * @return int
     */
    public function countFinalReportNotices($section = null, $level = DUPX_NOTICE_ITEM::INFO, $operator = '>=')
    {
        $result = 0;
        foreach ($this->finalReporNotices as $notice) {
            if (is_null($section) || in_array($section, $notice->sections)) {
                switch ($operator) {
                    case '>=':
                        $result += (int) ($notice->level >= $level);
                        break;
                    case '>':
                        $result += (int) ($notice->level > $level);
                        break;
                    case '=':
                        $result += (int) ($notice->level = $level);
                        break;
                    case '<=':
                        $result += (int) ($notice->level <= $level);
                        break;
                    case '<':
                        $result += (int) ($notice->level < $level);
                        break;
                    case '!=':
                        $result += (int) ($notice->level != $level);
                        break;
                }
            }
        }
        return $result;
    }

    /**
     * sort final report notice from priority and notice level
     */
    public function sortFinalReport()
    {
        uasort($this->finalReporNotices, 'DUPX_NOTICE_ITEM::sortNoticeForPriorityAndLevel');
    }

    /**
     * display final final report notice section
     *
     * @param string $section
     */
    public function displayFinalReport($section)
    {
        foreach ($this->finalReporNotices as $id => $notice) {
            if (in_array($section, $notice->sections)) {
                self::finalReportNotice($id, $notice);
            }
        }
    }

    /**
     *
     * @param string $section
     * @param string $title
     */
    public function displayFinalRepostSectionHtml($section, $title)
    {
        if ($this->haveSection($section)) {
            ?>
            <div id="report-section-<?php echo $section; ?>" class="section" >
                <div class="section-title" ><?php echo $title; ?></div>
                <div class="section-content">
                    <?php
                    $this->displayFinalReport($section);
                    ?>
                </div>
            </div>
            <?php
        }
    }

    /**
     *
     * @param string $section
     *
     * @return boolean
     */
    public function haveSection($section)
    {
        foreach ($this->finalReporNotices as $notice) {
            if (in_array($section, $notice->sections)) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param null|string $section  if null is a global result
     *
     * @return int // returns the worst level found
     */
    public function getSectionErrLevel($section = null)
    {
        $result = DUPX_NOTICE_ITEM::INFO;

        foreach ($this->finalReporNotices as $notice) {
            if (is_null($section) || in_array($section, $notice->sections)) {
                $result = max($result, $notice->level);
            }
        }
        return $result;
    }

    /**
     *
     * @param string $section
     * @param bool $echo
     *
     * @return void|string
     */
    public function getSectionErrLevelHtml($section = null, $echo = true)
    {
        return self::getErrorLevelHtml($this->getSectionErrLevel($section), $echo);
    }

    /**
     * Displa next step notice message
     *
     * @param bool $deleteListAfterDisaply
     *
     * @return void
     */
    public function displayStepMessages($deleteAfterDisaply = true)
    {
        if (empty($this->nextStepNotices)) {
            return;
        }
        $this->nextStepMessages($deleteAfterDisaply);
    }

    public function nextStepMessages($deleteAfterDisaply, $echo = true)
    {
        ob_start();
        foreach ($this->nextStepNotices as $notice) {
            self::stepMsg($notice);
        }

        if ($deleteAfterDisaply) {
            $this->nextStepNotices = array();
            $this->saveNotices();
        }
        if ($echo) {
            ob_end_flush();
        } else {
            return ob_get_clean();
        }
    }

    /**
     *
     * @param DUPX_NOTICE_ITEM $notice
     */
    private static function stepMsg($notice)
    {
        $classes     = array(
            'notice',
            'next-step',
            self::getClassFromLevel($notice->level)
        );
        $haveContent = !empty($notice->faqLink) || !empty($notice->longMsg);
        ?>
        <div class="<?php echo implode(' ', $classes); ?>">
            <?php echo self::getNextStepLevelIcon($notice->level); ?>
            <div class="title">
                <?php echo '<b>' . htmlentities($notice->shortMsg) . '</b>'; ?>
            </div>
            <?php if ($haveContent) { ?>
                <div class="title-separator" ></div>
                <?php
                ob_start();
                if (!empty($notice->faqLink)) {
                    ?>
                    See FAQ: <a href="<?php echo $notice->faqLink['url']; ?>" target="_blank" >
                        <b><?php echo htmlentities(empty($notice->faqLink['label']) ? $notice->faqLink['url'] : $notice->faqLink['label']); ?></b>
                    </a>
                    <?php
                }
                if (!empty($notice->faqLink) && !empty($notice->longMsg)) {
                    echo '<br><br>';
                }
                if (!empty($notice->longMsg)) {
                    switch ($notice->longMsgMode) {
                        case DUPX_NOTICE_ITEM::MSG_MODE_PRE:
                            echo '<pre>' . htmlentities($notice->longMsg) . '</pre>';
                            break;
                        case DUPX_NOTICE_ITEM::MSG_MODE_HTML:
                            echo $notice->longMsg;
                            break;
                        case DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT:
                        default:
                            echo htmlentities($notice->longMsg);
                    }
                }
                $longContent = ob_get_clean();
                DUPX_U_Html::getMoreContent($longContent, 'info', 200);
            }
            ?>
        </div>
        <?php
    }

    /**
     *
     * @param string $id
     * @param DUPX_NOTICE_ITEM $notice
     */
    private static function finalReportNotice($id, $notice)
    {
        $classes        = array(
            'notice-report',
            'notice',
            self::getClassFromLevel($notice->level)
        );
        $haveContent    = !empty($notice->faqLink) || !empty($notice->longMsg);
        $contentId      = 'notice-content-' . $id;
        $iconClasses    = $haveContent ? 'fa fa-caret-right' : 'fa fa-toggle-empty';
        $toggleLinkData = $haveContent ? 'data-type="toggle" data-target="#' . $contentId . '"' : '';
        ?>
        <div class="<?php echo implode(' ', $classes); ?>">
            <div class="title" <?php echo $toggleLinkData; ?>>
                <i class="<?php echo $iconClasses; ?>"></i>  <?php echo htmlentities($notice->shortMsg); ?>
            </div>
            <?php
            if ($haveContent) {
                $infoClasses = array('info');
                if (!$notice->open) {
                    $infoClasses[] = 'no-display';
                }
                ?>
                <div id="<?php echo $contentId; ?>" class="<?php echo implode(' ', $infoClasses); ?>" >
                    <?php
                    if (!empty($notice->faqLink)) {
                        ?>
                        <b>See FAQ</b>: <a href="<?php echo $notice->faqLink['url']; ?>" target="_blank" >
                            <?php echo htmlentities(empty($notice->faqLink['label']) ? $notice->faqLink['url'] : $notice->faqLink['label']); ?>
                        </a>
                        <?php
                    }
                    if (!empty($notice->faqLink) && !empty($notice->longMsg)) {
                        echo '<br><br>';
                    }
                    if (!empty($notice->longMsg)) {
                        switch ($notice->longMsgMode) {
                            case DUPX_NOTICE_ITEM::MSG_MODE_PRE:
                                echo '<pre>' . htmlentities($notice->longMsg) . '</pre>';
                                break;
                            case DUPX_NOTICE_ITEM::MSG_MODE_HTML:
                                echo $notice->longMsg;
                                break;
                            case DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT:
                            default:
                                echo htmlentities($notice->longMsg);
                        }
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

    /**
     *
     * @param DUPX_NOTICE_ITEM $notice
     */
    private static function noticeToText($notice)
    {
        $result = '-----------------------' . "\n" .
            '[' . self::getNextStepLevelPrefixMessage($notice->level, false) . '] ' . $notice->shortMsg;

        if (!empty($notice->sections)) {
            $result .= "\n\t" . 'SECTIONS: ' . implode(',', $notice->sections);
        }
        if (!empty($notice->longMsg)) {
            $result .= "\n\t" . 'LONG MSG: ' . preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", strip_tags($notice->longMsg));
        }
        return $result . "\n";
    }

    /**
     * Write next step notices in log
     *
     * @param boolean $title
     *
     * @return void
     */
    public function nextStepLog($title = true)
    {
        if (empty($this->nextStepNotices)) {
            return;
        }

        if ($title) {
            Log::info(
                "\n====================================\n" .
                'NEXT STEP NOTICES' . "\n" .
                '===================================='
            );
        }
        foreach ($this->nextStepNotices as $notice) {
            Log::info(self::noticeToText($notice));
        }
        if ($title) {
            Log::info(
                '===================================='
            );
        }
    }

    /**
     * Write final report notices in log
     *
     * @param array $sections sections to display
     *
     * @return void
     */
    public function finalReportLog($sections = array())
    {
        if (empty($this->finalReporNotices)) {
            return;
        }

        Log::info(
            "\n====================================\n" .
            'FINAL REPORT NOTICES LIST' . "\n" .
            '===================================='
        );
        foreach ($this->finalReporNotices as $notice) {
            if (count(array_intersect($notice->sections, $sections)) > 0) {
                Log::info(self::noticeToText($notice));
            }
        }
        Log::info(
            '===================================='
        );
    }

    /**
     * get html class from level
     *
     * @param int $level
     *
     * @return string
     */
    private static function getClassFromLevel($level)
    {
        switch ($level) {
            case DUPX_NOTICE_ITEM::INFO:
                return 'l-info';
            case DUPX_NOTICE_ITEM::NOTICE:
                return 'l-notice';
            case DUPX_NOTICE_ITEM::SOFT_WARNING:
                return 'l-swarning';
            case DUPX_NOTICE_ITEM::HARD_WARNING:
                return 'l-hwarning';
            case DUPX_NOTICE_ITEM::CRITICAL:
                return 'l-critical';
            case DUPX_NOTICE_ITEM::FATAL:
                return 'l-fatal';
        }
    }

    /**
     * Get level label from level
     *
     * @param int $level
     * @param bool $echo
     *
     * @return string
     */
    public static function getErrorLevelHtml($level, $echo = true)
    {
        switch ($level) {
            case DUPX_NOTICE_ITEM::INFO:
                $label = 'good';
                break;
            case DUPX_NOTICE_ITEM::NOTICE:
                $label = 'good';
                break;
            case DUPX_NOTICE_ITEM::SOFT_WARNING:
                $label = 'warning';
                break;
            case DUPX_NOTICE_ITEM::HARD_WARNING:
                $label = 'warning';
                break;
            case DUPX_NOTICE_ITEM::CRITICAL:
                $label = 'critical error';
                break;
            case DUPX_NOTICE_ITEM::FATAL:
                $label = 'fatal error';
                break;
            default:
                return;
        }
        $classes = self::getClassFromLevel($level);
        ob_start();
        ?>
        <span class="notice-level-status <?php echo $classes; ?>"><?php echo $label; ?></span>
        <?php
        if ($echo) {
            ob_end_flush();
            return '';
        } else {
            return ob_get_clean();
        }
    }

    /**
     * Get next step message prefix
     *
     * @param int $level
     * @param bool $echo
     *
     * @return string
     */
    public static function getNextStepLevelPrefixMessage($level, $echo = true)
    {
        switch ($level) {
            case DUPX_NOTICE_ITEM::INFO:
                $label = 'INFO';
                break;
            case DUPX_NOTICE_ITEM::NOTICE:
                $label = 'NOTICE';
                break;
            case DUPX_NOTICE_ITEM::SOFT_WARNING:
                $label = 'WARNING';
                break;
            case DUPX_NOTICE_ITEM::HARD_WARNING:
                $label = 'WARNING';
                break;
            case DUPX_NOTICE_ITEM::CRITICAL:
                $label = 'ERROR';
                break;
            case DUPX_NOTICE_ITEM::FATAL:
                $label = 'FATAL ERROR';
                break;
            default:
                return;
        }

        if ($echo) {
            echo $label;
        } else {
            return $label;
        }
    }

    public static function getNextStepLevelIcon($level, $echo = true)
    {
        switch ($level) {
            case DUPX_NOTICE_ITEM::INFO:
                $iconClass = 'fa-info-circle fa-lg';
                break;
            case DUPX_NOTICE_ITEM::NOTICE:
                $iconClass = 'fa-info-circle fa-lg';
                break;
            case DUPX_NOTICE_ITEM::SOFT_WARNING:
                $iconClass = 'fa-exclamation-triangle fa-lg';
                break;
            case DUPX_NOTICE_ITEM::HARD_WARNING:
                $iconClass = 'fa-exclamation-circle fa-lg';
                break;
            case DUPX_NOTICE_ITEM::CRITICAL:
                $iconClass = 'fa-exclamation-circle fa-lg';
                break;
            case DUPX_NOTICE_ITEM::FATAL:
                $iconClass = 'fa-exclamation-circle fa-lg';
                break;
            default:
                return;
        }

        $result = '<i class="fas ' . $iconClass . '" title="' . self::getNextStepLevelPrefixMessage($level, false) . '" ></i>';

        if ($echo) {
            echo $result;
        } else {
            return $result;
        }
    }

    /**
     * get unique id
     *
     * @return string
     */
    private static function getNewAutoUniqueId()
    {
        self::$uniqueCountId++;
        return self::DEFAULT_UNIQUE_ID_PREFIX . self::$uniqueCountId;
    }

    /**
     * function for internal test
     *
     * display all messages levels
     */
    public static function testNextStepMessaesLevels()
    {
        $manager = self::getInstance();
        $manager->addNextStepNoticeMessage('Level info (' . DUPX_NOTICE_ITEM::INFO . ')', DUPX_NOTICE_ITEM::INFO);
        $manager->addNextStepNoticeMessage('Level notice (' . DUPX_NOTICE_ITEM::NOTICE . ')', DUPX_NOTICE_ITEM::NOTICE);
        $manager->addNextStepNoticeMessage('Level soft warning (' . DUPX_NOTICE_ITEM::SOFT_WARNING . ')', DUPX_NOTICE_ITEM::SOFT_WARNING);
        $manager->addNextStepNoticeMessage('Level hard warning (' . DUPX_NOTICE_ITEM::HARD_WARNING . ')', DUPX_NOTICE_ITEM::HARD_WARNING);
        $manager->addNextStepNoticeMessage('Level critical error (' . DUPX_NOTICE_ITEM::CRITICAL . ')', DUPX_NOTICE_ITEM::CRITICAL);
        $manager->addNextStepNoticeMessage('Level fatal error (' . DUPX_NOTICE_ITEM::FATAL . ')', DUPX_NOTICE_ITEM::FATAL);
        $manager->saveNotices();
    }

    /**
     * test function
     */
    public static function testNextStepFullMessageData()
    {
        $manager = self::getInstance();
        $longMsg = <<<LONGMSG
            <b>Formattend long text</b><br>
            <ul>
            <li>Proin dapibus mi eu erat pulvinar, id congue nisl egestas.</li>
            <li>Nunc venenatis eros et sapien ornare consequat.</li>
            <li>Mauris tincidunt est sit amet turpis placerat, a tristique dui porttitor.</li>
            <li>Etiam volutpat lectus quis risus molestie faucibus.</li>
            <li>Integer gravida eros sit amet sem viverra, a volutpat neque rutrum.</li>
            <li>Aenean varius ipsum vitae lorem tempus rhoncus.</li>
            </ul>
LONGMSG;
        $manager->addNextStepNotice(array(
            'shortMsg'    => 'Full elements next step message MODE HTML',
            'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
            'longMsg'     => $longMsg,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
            'faqLink'     => array(
                'url'   => 'http://www.google.it',
                'label' => 'google link'
            )
        ));

        $longMsg = <<<LONGMSG
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc a auctor erat, et lobortis libero.
                Suspendisse aliquet neque in massa posuere mollis. Donec venenatis finibus sapien in bibendum. Donec et ex massa.

   Aliquam venenatis dapibus tellus nec ullamcorper. Mauris ante velit, tincidunt sit amet egestas et, mattis non lorem. In semper ex ut velit suscipit,
       at luctus nunc dapibus. Etiam blandit maximus dapibus. Nullam eu porttitor augue. Suspendisse pulvinar, massa eget condimentum aliquet, dolor massa tempus dui, vel rhoncus tellus ligula non odio.
           Ut ac faucibus tellus, in lobortis odio.
LONGMSG;
        $manager->addNextStepNotice(array(
            'shortMsg'    => 'Full elements next step message MODE PRE',
            'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
            'longMsg'     => $longMsg,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_PRE,
            'faqLink'     => array(
                'url'   => 'http://www.google.it',
                'label' => 'google link'
            )
        ));

        $longMsg = <<<LONGMSG
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc a auctor erat, et lobortis libero.
                Suspendisse aliquet neque in massa posuere mollis. Donec venenatis finibus sapien in bibendum. Donec et ex massa.

   Aliquam venenatis dapibus tellus nec ullamcorper. Mauris ante velit, tincidunt sit amet egestas et, mattis non lorem. In semper ex ut velit suscipit,
       at luctus nunc dapibus. Etiam blandit maximus dapibus. Nullam eu porttitor augue. Suspendisse pulvinar, massa eget condimentum aliquet, dolor massa tempus dui, vel rhoncus tellus ligula non odio.
           Ut ac faucibus tellus, in lobortis odio.
LONGMSG;
        $manager->addNextStepNotice(array(
            'shortMsg'    => 'Full elements next step message MODE DEFAULT',
            'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
            'longMsg'     => $longMsg,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
            'faqLink'     => array(
                'url'   => 'http://www.google.it',
                'label' => 'google link'
            )
        ));


        $longMsg = <<<LONGMSG
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam cursus porttitor consectetur. 
Nunc faucibus elementum nisl nec ornare. Phasellus sit amet urna in diam ultricies ornare nec sit amet nibh. 
Nulla a aliquet leo. Quisque aliquet posuere lectus sit amet commodo. 
Nullam tempus enim eget urna rutrum egestas. Aliquam eget lorem nisl. 
Nulla tincidunt massa erat. Phasellus lectus tellus, mollis sit amet aliquam in, dapibus quis metus. 
Nunc venenatis nulla vitae convallis accumsan.

Mauris eu ullamcorper metus. Aenean ultricies et turpis eget mollis. 
Aliquam auctor, elit scelerisque placerat pellentesque, quam augue fermentum lectus, 
vel pretium nisi justo sit amet ante. Donec blandit porttitor tempus. Duis vulputate nulla ut orci rutrum, 
et consectetur urna mollis. Sed at iaculis velit. Pellentesque id quam turpis. Curabitur eu ligula velit. 
Cras gravida, ipsum sed iaculis eleifend, mauris nunc posuere quam, vel blandit nisi justo congue ligula. Phasellus aliquam eu odio ac porttitor.
Fusce dictum mollis turpis sit amet fringilla.

Nulla eu ligula mauris. Fusce lobortis ligula elit, a interdum nibh pulvinar eu. 
Pellentesque rhoncus nec turpis id blandit. Morbi fringilla, justo non varius consequat, arcu ante efficitur ante, 
sit amet cursus lorem elit vel odio. Phasellus neque ligula, vehicula vel ipsum sed, volutpat dignissim eros. Curabitur at 
lacus id felis elementum auctor. Nullam ac tempus nisi. Phasellus nibh purus, aliquam nec purus ut, sodales lobortis nulla. 
Cras viverra dictum magna, ac malesuada nibh dictum ac. Mauris euismod, magna sit amet pretium posuere, 
ligula nibh ultrices tellus, sit amet pretium odio urna egestas justo. Suspendisse purus erat, eleifend sed magna in, efficitur interdum nibh.   
LONGMSG;
        $manager->addNextStepNotice(array(
            'shortMsg'    => 'Full elements LONG LONG',
            'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
            'longMsg'     => $longMsg,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_DEFAULT,
            'faqLink'     => array(
                'url'   => 'http://www.google.it',
                'label' => 'google link'
            )
        ));




        $manager->saveNotices();
    }

    /**
     * test function
     */
    public static function testFinalReporMessaesLevels()
    {
        $section = 'general';

        $manager = self::getInstance();
        $manager->addFinalReportNoticeMessage('Level info (' . DUPX_NOTICE_ITEM::INFO . ')', $section, DUPX_NOTICE_ITEM::INFO, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_0');
        $manager->addFinalReportNoticeMessage('Level notice (' . DUPX_NOTICE_ITEM::NOTICE . ')', $section, DUPX_NOTICE_ITEM::NOTICE, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_1');
        $manager->addFinalReportNoticeMessage('Level soft warning (' . DUPX_NOTICE_ITEM::SOFT_WARNING . ')', $section, DUPX_NOTICE_ITEM::SOFT_WARNING, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_2');
        $manager->addFinalReportNoticeMessage('Level hard warning (' . DUPX_NOTICE_ITEM::HARD_WARNING . ')', $section, DUPX_NOTICE_ITEM::HARD_WARNING, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_3');
        $manager->addFinalReportNoticeMessage('Level critical error (' . DUPX_NOTICE_ITEM::CRITICAL . ')', $section, DUPX_NOTICE_ITEM::CRITICAL, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_4');
        $manager->addFinalReportNoticeMessage('Level fatal error (' . DUPX_NOTICE_ITEM::FATAL . ')', $section, DUPX_NOTICE_ITEM::FATAL, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_5');
        $manager->saveNotices();
    }

    /**
     * test function
     */
    public static function testFinalReportFullMessages()
    {
        $section = 'general';
        $manager = self::getInstance();

        $longMsg = <<<LONGMSG
            <b>Formattend long text</b><br>
            <ul>
            <li>Proin dapibus mi eu erat pulvinar, id congue nisl egestas.</li>
            <li>Nunc venenatis eros et sapien ornare consequat.</li>
            <li>Mauris tincidunt est sit amet turpis placerat, a tristique dui porttitor.</li>
            <li>Etiam volutpat lectus quis risus molestie faucibus.</li>
            <li>Integer gravida eros sit amet sem viverra, a volutpat neque rutrum.</li>
            <li>Aenean varius ipsum vitae lorem tempus rhoncus.</li>
            </ul>
LONGMSG;

        $manager->addFinalReportNotice(array(
            'shortMsg'    => 'Full elements final report message',
            'level'       => DUPX_NOTICE_ITEM::HARD_WARNING,
            'longMsg'     => $longMsg,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
            'sections'    => $section,
            'faqLink'     => array(
                'url'   => 'http://www.google.it',
                'label' => 'google link'
            )
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_full_1');

        $manager->addFinalReportNotice(array(
            'shortMsg'    => 'Full elements final report message info high priority',
            'level'       => DUPX_NOTICE_ITEM::INFO,
            'longMsg'     => $longMsg,
            'longMsgMode' => DUPX_NOTICE_ITEM::MSG_MODE_HTML,
            'sections'    => $section,
            'faqLink'     => array(
                'url'   => 'http://www.google.it',
                'label' => 'google link'
            ),
            'priority'    => 5
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_full_2');
        $manager->saveNotices();
    }

    private function __clone()
    {
    }
}

class DUPX_NOTICE_ITEM
{
    const INFO             = 0;
    const NOTICE           = 1;
    const SOFT_WARNING     = 2;
    const HARD_WARNING     = 3;
    const CRITICAL         = 4;
    const FATAL            = 5;
    const MSG_MODE_DEFAULT = 'def';
    const MSG_MODE_HTML    = 'html';
    const MSG_MODE_PRE     = 'pre';

    /**
     *
     * @var string text
     */
    public $shortMsg = '';

    /**
     *
     * @var string html text
     */
    public $longMsg = '';

    /**
     *
     * @var bool if true long msg can be html
     */
    public $longMsgMode = self::MSG_MODE_DEFAULT;

    /**
     *
     * @var null|array // null = no faq link
     *                    array( 'label' => link text , 'url' => faq url)
     */
    public $faqLink = array(
        'label' => '',
        'url'   => ''
    );

    /**
     *
     * @var string[] notice sections for final report only
     */
    public $sections = array();

    /**
     *
     * @var int
     */
    public $level = self::NOTICE;

    /**
     *
     * @var int
     */
    public $priority = 10;

    /**
     *
     * @var bool if true notice start open. For final report only
     */
    public $open = false;

    /**
     *
     * @param string $shortMsg text
     * @param int $level
     * @param string $longMsg html text
     * @param string|string[] $sections
     * @param null|array $faqLink [
     *                              'url' => external link
     *                              'label' => link text if empty get external url link
     *                          ]
     * @param int priority
     * @param bool open
     * @param string longMsgMode MSG_MODE_DEFAULT | MSG_MODE_HTML | MSG_MODE_PRE
     */
    public function __construct($shortMsg, $level = self::INFO, $longMsg = '', $sections = array(), $faqLink = null, $priority = 10, $open = false, $longMsgMode = self::MSG_MODE_DEFAULT)
    {
        $this->shortMsg    = (string) $shortMsg;
        $this->level       = (int) $level;
        $this->longMsg     = (string) $longMsg;
        $this->sections    = is_array($sections) ? $sections : array($sections);
        $this->faqLink     = $faqLink;
        $this->priority    = $priority;
        $this->open        = $open;
        $this->longMsgMode = $longMsgMode;
    }

    /**
     *
     * @return array        [
     *                          'shortMsg' => text,
     *                          'level' => level,
     *                          'longMsg' => html text,
     *                          'sections' => string|string[],
     *                          'faqLink' => [
     *                              'url' => external link
     *                              'label' => link text if empty get external url link
     *                          ]
     *                      ]
     */
    public function toArray()
    {
        return array(
            'shortMsg'    => $this->shortMsg,
            'level'       => $this->level,
            'longMsg'     => $this->longMsg,
            'sections'    => $this->sections,
            'faqLink'     => $this->faqLink,
            'priority'    => $this->priority,
            'open'        => $this->open,
            'longMsgMode' => $this->longMsgMode
        );
    }

    /**
     *
     * @param array $array [
     *                          'shortMsg' => text,
     *                          'level' => level,
     *                          'longMsg' => html text,
     *                          'sections' => string|string[],
     *                          'faqLink' => [
     *                              'url' => external link
     *                              'label' => link text if empty get external url link
     *                          ]
     *                      ]
     *
     * @return DUPX_NOTICE_ITEM
     */
    public static function getItemFromArray($array)
    {
        if (isset($array['sections']) && !is_array($array['sections'])) {
            if (empty($array['sections'])) {
                $array['sections'] = array();
            } else {
                $array['sections'] = array($array['sections']);
            }
        }
        $params = array_merge(self::getDefaultArrayParams(), $array);
        $result = new self($params['shortMsg'], $params['level'], $params['longMsg'], $params['sections'], $params['faqLink'], $params['priority'], $params['open'], $params['longMsgMode']);
        return $result;
    }

    /**
     *
     * @return array        [
     *                          'shortMsg' => text,
     *                          'level' => level,
     *                          'longMsg' => html text,
     *                          'sections' => string|string[],
     *                          'faqLink' => [
     *                              'url' => external link
     *                              'label' => link text if empty get external url link
     *                          ],
     *                          priority
     *                          open
     *                          longMsgMode
     *                      ]
     */
    public static function getDefaultArrayParams()
    {
        return array(
            'shortMsg'    => '',
            'level'       => self::INFO,
            'longMsg'     => '',
            'sections'    => array(),
            'faqLink'     => null,
            'priority'    => 10,
            'open'        => false,
            'longMsgMode' => self::MSG_MODE_DEFAULT
        );
    }

    /**
     * before lower priority
     * before highest level
     *
     * @param DUPX_NOTICE_ITEM $a
     * @param DUPX_NOTICE_ITEM $b
     */
    public static function sortNoticeForPriorityAndLevel($a, $b)
    {
        if ($a->priority == $b->priority) {
            if ($a->level == $b->level) {
                return 0;
            } elseif ($a->level < $b->level) {
                return 1;
            } else {
                return -1;
            }
        } elseif ($a->priority < $b->priority) {
            return -1;
        } else {
            return 1;
        }
    }
}