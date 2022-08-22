<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $fromPhp string */
/* @var $toPhp string */
/* @var $isOk bool */
?>
    <div class="sub-title">STATUS</div>
    <p class="red" >
        <b>You are installing on a SiteGround server.</b>
    </p>

    <div class="sub-title">DETAILS</div>
    <p>
        To overcome errors while extracting ZipArchive on SiteGround Server throttling has been automatically enabled.
    </p>

    <div class="sub-title">TROUBLESHOOT</div>
    <ul>
        <li>
           In case you still get errors during the extraction please try switching the "Extraction Mode" to
            "Shell Exec Zip" in Advanced mode under the "Options" section.
        </li>
        <li>
            If the above doesn't work either, please consider creating a new package on the source using the DAF archive format.
        </li>
    </ul>
