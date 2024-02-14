<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Installer\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $dbuser string */
/* @var $dbname string */
/* @var $databases string */
/* @var $errorMessage string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($isOk) { ?>
        The database user <b>[<?php echo htmlentities($dbuser); ?>]</b> has visible access to see the database named 
        <b>[<?php echo htmlentities($dbname); ?>]</b>
    <?php } else { ?>
        The user <b>[<?php echo htmlentities($dbuser); ?>]</b> is unable to see the database named 
        <b>[<?php echo htmlentities($dbname); ?>]</b>.<br>
        Be sure the database name already exists. 
        If you want to create a new database choose the action 'Create New Database'.<br>
    <?php } ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>


<div class="sub-title">DETAILS</div>
<p>
    This test checks if the database user is allowed to connect or view the database. 
    This test will not be ran if the 'Create New Database' action is selected.
</p>

<?php if (!$isOk) { ?>
    <b>Databases visible to user [<?php echo htmlentities($dbuser); ?>]</b><br/>
    <ul class="db-list">
        <?php
        if (count($databases)) {
            foreach ($databases as $database) {
                ?>
                <li>
                    <?php echo htmlentities($database); ?>
                </li>
                <?php
            }
        } else {
            ?>
            <li>
                <i>No databases are viewable</i>
            </li>
        <?php } ?>
    </ul>
<?php } ?>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Check the database user privileges.</li>
    <li>
        Check to make sure the 'User' has been added as a valid database user
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
    <li>
        <a 
            href="<?php echo LinkManager::getDocUrl('how-to-fix-database-connection-issues', 'install', 'validation db visibility'); ?>"
            target="_help"
            title="I'm running into issues with the Database what can I do?"
        >
            [Additional FAQ Help]
        </a>
    </li>
</ul>
