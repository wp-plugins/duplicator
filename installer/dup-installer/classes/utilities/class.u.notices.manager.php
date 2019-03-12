<?php
/**
 * Notice manager
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */

/**
 * Notice manager
 * singleton class
 */
final class DUPX_NOTICE_MANAGER
{
    const ADD_NORMAL               = 0; // add notice in list
    const ADD_UNIQUE               = 1; // add if unique id don't exists
    const ADD_UNIQUE_UPDATE        = 2; // add or update notice unique id
    const DEFAULT_UNIQUE_ID_PREFIX = '__auto_unique_id__';

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
     * @var DUPX_NOTICE_MANAGER
     */
    private static $instance = null;

    /**
     *
     * @var string
     */
    private $persistanceFile = null;

    /**
     *
     * @return DUPX_S_R_MANAGER
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

    public function saveNotices()
    {
        $notices = array(
            'globalData' => array(
                'uniqueCountId' => self::$uniqueCountId
            ),
            'nextStep' => array(),
            'finalReport' => array()
        );

        foreach ($this->nextStepNotices as $uniqueId => $notice) {
            $notices['nextStep'][$uniqueId] = $notice->toArray();
        }

        foreach ($this->finalReporNotices as $uniqueId => $notice) {
            $notices['finalReport'][$uniqueId] = $notice->toArray();
        }

        $json = json_encode($notices, JSON_PRETTY_PRINT);
        file_put_contents($this->persistanceFile, $json);
    }

    private function loadNotices()
    {
        if (file_exists($this->persistanceFile)) {
            $json    = file_get_contents($this->persistanceFile);
            $notices = json_decode($json, true);

            $this->nextStepNotices   = array();
            $this->finalReporNotices = array();

            foreach ($notices['nextStep'] as $uniqueId => $notice) {
                $this->nextStepNotices[$uniqueId] = DUPX_NOTICE_ITEM::getItemFromArray($notice);
            }

            foreach ($notices['finalReport'] as $uniqueId => $notice) {
                $this->finalReporNotices[$uniqueId] = DUPX_NOTICE_ITEM::getItemFromArray($notice);
            }

            self::$uniqueCountId = $notices['globalData']['uniqueCountId'];
        } else {
            $this->resetNotices();
        }
    }

    /**
     *
     */
    public function resetNotices()
    {
        $this->nextStepNotices   = array();
        $this->finalReporNotices = array();
        self::$uniqueCountId     = 0;
        $this->saveNotices();
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
     *
     * @throws Exception
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
     *
     * @throws Exception
     */
    public function addNextStepNoticeMessage($message, $level = DUPX_NOTICE_ITEM::INFO, $mode = self::ADD_NORMAL, $uniqueId = null)
    {
        return $this->addNextStepNotice(array(
                'shortMsg' => $message,
                'level' => $level,
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
     *
     * @throws Exception
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
     *
     * @throws Exception
     */
    public function addFinalReportNoticeMessage($message, $sections, $level = DUPX_NOTICE_ITEM::INFO, $mode = self::ADD_NORMAL, $uniqueId = null)
    {
        return $this->addFinalReportNotice(array(
                'shortMsg' => $message,
                'level' => $level,
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
     *
     * @throws Exception
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
                $insertId = $uniqueId;
                break;
            case self::ADD_NORMAL:
            default:
                $insertId = self::getNewAutoUniqueId();
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
     * @return \DUPX_NOTICE_ITEM
     *
     * @throws Exception
     */
    private static function getObjFromParams($item, $level = DUPX_NOTICE_ITEM::INFO)
    {
        if ($item instanceof DUPX_NOTICE_ITEM) {
            $newObj = $item;
        } else if (is_array($item)) {
            $newObj = DUPX_NOTICE_ITEM::getItemFromArray($item);
        } else if (is_string($item)) {
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
                        $result        += (int) ($notice->level >= $level);
                        break;
                    case '>':
                        $result        += (int) ($notice->level > $level);
                        break;
                    case '=':
                        $result        += (int) ($notice->level = $level);
                        break;
                    case '<=':
                        $result        += (int) ($notice->level <= $level);
                        break;
                    case '<':
                        $result        += (int) ($notice->level < $level);
                        break;
                    case '!=':
                        $result        += (int) ($notice->level != $level);
                        break;
                }
            }
        }
        return $result;
    }

    /**
     *
     */
    public function sortFinalReport()
    {
        uasort($this->finalReporNotices, array('DUPX_NOTICE_ITEM', 'sortNoticeForPriorityAndLevel'));
    }

    public function displayFinalReport($section)
    {
        foreach ($this->finalReporNotices as $id => $notice) {
            if (in_array($section, $notice->sections)) {
                self::finalReportNotice($id, $notice);
            }
        }
        /*
          echo '<pre>';
          print_r($this->finalReporNotices);
          echo '</pre>'; */
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
     *
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
     * @return void|string
     */
    public function getSectionErrLevelHtml($section = null, $echo = true)
    {
        return self::getErrorLevelHtml($this->getSectionErrLevel($section), $echo);
    }

    public function displayStepMessages($deleteListAfterDisaply = true)
    {
        if (empty($this->nextStepNotices)) {
            return;
        }
        ?>
        <div id="step-messages">
            <?php
            foreach ($this->nextStepNotices as $notice) {
                self::stepMsg($notice);
            }
            ?>
        </div>
        <?php
        if ($deleteListAfterDisaply) {
            $this->nextStepNotices = array();
            $this->saveNotices();
        }
    }

    /**
     *
     * @param DUPX_NOTICE_ITEM $notice
     */
    private static function stepMsg($notice)
    {
        $classes = array(
            'notice',
            self::getClassFromLevel($notice->level)
        );
        ?>
        <div class="<?php echo implode(' ', $classes); ?>">
            <p>
                <?php
                echo self::getNextStepLevelPrefixMessage($notice->level).': <b>'.htmlentities($notice->shortMsg).'</b>';
                if (!empty($notice->faqLink)) {
                    ?>
                    <br>
                    See FAQ: <a href="<?php echo $notice->faqLink['url']; ?>" >
                        <b><?php echo htmlentities(empty($notice->faqLink['label']) ? $notice->faqLink['url'] : $notice->faqLink['label']); ?></b>
                    </a>
                    <?php
                }
                if (!empty($notice->longMsg)) {
                    echo '<br><br>'.($notice->longMsgHtml ? $notice->longMsg : htmlentities($notice->longMsg));
                }
                ?>
            </p>
            <!--<button type="button" class="notice-dismiss"><span class="screen-reader-text">Nascondi questa notifica.</span></button>-->
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
        $contentId      = 'notice-content-'.$id;
        $iconClasses    = $haveContent ? 'fa fa-caret-right' : 'fa fa-toggle-empty';
        $toggleLinkData = $haveContent ? 'data-type="toggle" data-target="#'.$contentId.'"' : '';
        ?>
        <div class="<?php echo implode(' ', $classes); ?>">
            <div class="title" <?php echo $toggleLinkData; ?>>
                <i class="<?php echo $iconClasses; ?>"></i>  <?php echo htmlentities($notice->shortMsg); ?>
            </div>
            <?php if ($haveContent) { ?>
                <div class="info <?php echo $notice->open ? '' : 'no-display'; ?>" id="<?php echo $contentId; ?>">
                    <?php if (!empty($notice->faqLink)) { ?>
                        <b>See FAQ</b>: <a href="<?php echo $notice->faqLink['url']; ?>" >
                            <?php echo htmlentities(empty($notice->faqLink['label']) ? $notice->faqLink['url'] : $notice->faqLink['label']); ?>
                        </a>
                        <?php
                    }
                    if (!empty($notice->faqLink) && !empty($notice->longMsg)) {
                        echo '<br><br>';
                    }
                    if (!empty($notice->longMsg)) {
                        echo $notice->longMsgHtml ? $notice->longMsg : htmlentities($notice->longMsg);
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }

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
        } else {
            return ob_get_clean();
        }
    }

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
                $label = 'CRITICAL ERROR';
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

    private static function getNewAutoUniqueId()
    {
        self::$uniqueCountId ++;
        return self::DEFAULT_UNIQUE_ID_PREFIX.self::$uniqueCountId;
    }

    /**
     * function for internal test
     *
     * display all messages levels
     */
    public static function testNextStepMessaesLevels()
    {
        $manager = self::getInstance();
        $manager->addNextStepNoticeMessage('Level info ('.DUPX_NOTICE_ITEM::INFO.')', DUPX_NOTICE_ITEM::INFO);
        $manager->addNextStepNoticeMessage('Level notice ('.DUPX_NOTICE_ITEM::NOTICE.')', DUPX_NOTICE_ITEM::NOTICE);
        $manager->addNextStepNoticeMessage('Level soft warning ('.DUPX_NOTICE_ITEM::SOFT_WARNING.')', DUPX_NOTICE_ITEM::SOFT_WARNING);
        $manager->addNextStepNoticeMessage('Level hard warning ('.DUPX_NOTICE_ITEM::HARD_WARNING.')', DUPX_NOTICE_ITEM::HARD_WARNING);
        $manager->addNextStepNoticeMessage('Level critical error ('.DUPX_NOTICE_ITEM::CRITICAL.')', DUPX_NOTICE_ITEM::CRITICAL);
        $manager->addNextStepNoticeMessage('Level fatal error ('.DUPX_NOTICE_ITEM::FATAL.')', DUPX_NOTICE_ITEM::FATAL);
        $manager->saveNotices();
    }

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
            'shortMsg' => 'Full elements next step message',
            'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
            'longMsg' => $longMsg,
            'longMsgHtml' => true,
            'faqLink' => array(
                'url' => 'http://www.google.it',
                'label' => 'google link'
            )
        ));
        $manager->saveNotices();
    }

    public static function testFinalReporMessaesLevels()
    {
        $section = 'general';

        $manager = self::getInstance();
        $manager->addFinalReportNoticeMessage('Level info ('.DUPX_NOTICE_ITEM::INFO.')', $section, DUPX_NOTICE_ITEM::INFO, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_0');
        $manager->addFinalReportNoticeMessage('Level notice ('.DUPX_NOTICE_ITEM::NOTICE.')', $section, DUPX_NOTICE_ITEM::NOTICE, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_1');
        $manager->addFinalReportNoticeMessage('Level soft warning ('.DUPX_NOTICE_ITEM::SOFT_WARNING.')', $section, DUPX_NOTICE_ITEM::SOFT_WARNING, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_2');
        $manager->addFinalReportNoticeMessage('Level hard warning ('.DUPX_NOTICE_ITEM::HARD_WARNING.')', $section, DUPX_NOTICE_ITEM::HARD_WARNING, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_3');
        $manager->addFinalReportNoticeMessage('Level critical error ('.DUPX_NOTICE_ITEM::CRITICAL.')', $section, DUPX_NOTICE_ITEM::CRITICAL, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_4');
        $manager->addFinalReportNoticeMessage('Level fatal error ('.DUPX_NOTICE_ITEM::FATAL.')', $section, DUPX_NOTICE_ITEM::FATAL, DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_5');
        $manager->saveNotices();
    }

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
            'shortMsg' => 'Full elements final report message',
            'level' => DUPX_NOTICE_ITEM::HARD_WARNING,
            'longMsg' => $longMsg,
            'longMsgHtml' => true,
            'sections' => $section,
            'faqLink' => array(
                'url' => 'http://www.google.it',
                'label' => 'google link'
            )
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_full_1');

        $manager->addFinalReportNotice(array(
            'shortMsg' => 'Full elements final report message info high priority',
            'level' => DUPX_NOTICE_ITEM::INFO,
            'longMsg' => $longMsg,
            'longMsgHtml' => true,
            'sections' => $section,
            'faqLink' => array(
                'url' => 'http://www.google.it',
                'label' => 'google link'
            ),
            'priority' => 5
            ), DUPX_NOTICE_MANAGER::ADD_UNIQUE, 'test_fr_full_2');
        $manager->saveNotices();
    }

    private function __clone()
    {

    }

    private function __wakeup()
    {

    }
}

class DUPX_NOTICE_ITEM
{
    const INFO         = 0;
    const NOTICE       = 1;
    const SOFT_WARNING = 2;
    const HARD_WARNING = 3;
    const CRITICAL     = 4;
    const FATAL        = 5;

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
    public $longMsgHtml = false;

    /**
     *
     * @var null|array // null = no faq link
     *                    array( 'label' => link text , 'url' => faq url)
     */
    public $faqLink = array(
        'label' => '',
        'url' => ''
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
     * @param bool longMsgHtml
     */
    public function __construct($shortMsg, $level = self::INFO, $longMsg = '', $sections = array(), $faqLink = null, $priority = 10, $open = false, $longMsgHtml = false)
    {
        $this->shortMsg    = (string) $shortMsg;
        $this->level       = (int) $level;
        $this->longMsg     = (string) $longMsg;
        $this->sections    = is_array($sections) ? $sections : array($sections);
        $this->faqLink     = $faqLink;
        $this->priority    = $priority;
        $this->open        = $open;
        $this->longMsgHtml = $longMsgHtml;
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
            'shortMsg' => $this->shortMsg,
            'level' => $this->level,
            'longMsg' => $this->longMsg,
            'sections' => $this->sections,
            'faqLink' => $this->faqLink,
            'priority' => $this->priority,
            'open' => $this->open,
            'longMsgHtml' => $this->longMsgHtml
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
        $result = new self($params['shortMsg'], $params['level'], $params['longMsg'], $params['sections'], $params['faqLink'], $params['priority'], $params['open'], $params['longMsgHtml']);
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
     *                          longMsgHtml
     *                      ]
     */
    public static function getDefaultArrayParams()
    {
        return array(
            'shortMsg' => '',
            'level' => self::INFO,
            'longMsg' => '',
            'sections' => array(),
            'faqLink' => null,
            'priority' => 10,
            'open' => false,
            'longMsgHtml' => false
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
            } else if ($a->level < $b->level) {
                return 1;
            } else {
                return -1;
            }
        } else if ($a->priority < $b->priority) {
            return -1;
        } else {
            return 1;
        }
    }
}