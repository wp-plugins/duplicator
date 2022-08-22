<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $isCpanel bool */
/* @var $dbname string */
/* @var $errorMessage string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($isOk) { ?>
        Successfully created database <b>[<?php echo htmlentities($dbname); ?>]</b>
        <?php
    } else {
        if ($alreadyExists) {
            ?>
            DATABASE CREATION FAILURE: A database named <b>[<?php echo htmlentities($dbname); ?>]</b> already exists.<br/><br/>
            Please continue with the following options:<br/>
            - Choose a different database name or remove this one.<br/>
            - Change the action drop-down to an option like "Connect and Remove All Data".<br/>
        <?php } else { ?>
            Error creating database <b>[<?php echo htmlentities($dbname); ?>]</b>.
            <?php
        }
    }
    ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks if the database can be created by the database user <?php echo $isCpanel ? 'using Cpanel API' : ''; ?>.
    The test will attempt to create and drop the database name provided as part of the overall test.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        Check the database user privileges:
        <ul class='vids'>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href="https://www.youtube.com/watch?v=FfX-B-h3vo0" target="_video">Add database user in phpMyAdmin</a>
            </li>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href="https://www.youtube.com/watch?v=peLby12mi0Q" target="_video">Add database user in cPanel older versions</a>
            </li>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href="https://www.youtube.com/watch?v=CHwxXGPnw48" target="_video">Add database user in cPanel newer versions</a>
            </li>
        </ul>
    </li>
    <?php if (!$isCpanel) { ?>
        <li>Try using the <a href="javascript:void(0)" onclick="DUPX.togglePanels('cpanel')">'cPanel'</a> option.</li>
    <?php } ?>
    <li>
        <a href="<?php echo DUPX_U::esc_attr(DUPX_Constants::FAQ_URL); ?>#faq-installer-100-q" target="_help"
           title="I'm running into issues with the Database what can I do?">
            [Additional FAQ Help]
        </a>
    </li>
</ul>