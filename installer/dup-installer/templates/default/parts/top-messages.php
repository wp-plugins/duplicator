<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<div id="page-top-messages">
    <?php
/* * ************************* */
/* * * NOTICE MANAGER TESTS ** */
//DUPX_NOTICE_MANAGER::testNextStepFullMessageData();
//DUPX_NOTICE_MANAGER::testNextStepMessaesLevels();
//DUPX_NOTICE_MANAGER::testFinalReporMessaesLevels();
//DUPX_NOTICE_MANAGER::testFinalReportFullMessages();
/* * ************************* */

    DUPX_NOTICE_MANAGER::getInstance()->nextStepLog();
// display and remove next step notices
    DUPX_NOTICE_MANAGER::getInstance()->displayStepMessages();
    ?>
</div>