<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int */

switch ($testResult) {
    case DUPX_Validation_test_archive_check::LV_PASS:
        ?>
        <span class="dupx-pass">Archive file successfully detected.</span>
        <?php
        break;
    case DUPX_Validation_test_archive_check::LV_SOFT_WARNING:
        ?>
        <span class="dupx-fail" style="font-style:italic">
            The archive file named above must be the <u>exact</u> name of the archive file placed in the root path (character for character). 
            But you can proceed with choosing Manual Archive Extraction.
        </span>
        <?php
        break;
    case DUPX_Validation_test_archive_check::LV_FAIL:
        ?>
        <span class="dupx-fail" style="font-style:italic">
            The archive file named above must be the <u>exact</u> name of the archive file placed in the root path (character for character).
            When downloading the package files make sure both files are from the same package line.  <br/><br/>

            If the contents of the archive were manually transferred to this location without the archive file then simply create a temp file named with
            the exact name shown above and place the file in the same directory as the installer.php file.  The temp file will not need to contain any data.
            Afterward, refresh this page and continue with the install process.   
        </span>
        <?php
        break;
    default:
        ?>
        Invalid test result value <?php
        echo $testResult;
}