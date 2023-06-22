<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

/**
 * Variables
 *
 * @var int      $testResult    DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...]
 * @var string[] $faildDirPerms
 * @var array    $phpPerms
 */
?>
<div class="sub-title">STATUS</div>
<?php if ($testResult < DUPX_Validation_abstract_item::LV_GOOD) { ?>
    <p class="red">
        Some folders do not have write permission, see details for more information.
    </p>
<?php } else { ?>
    <p class="green">
        Write permissions granted for WordPress core directories.
    </p>   
<?php } ?>

<div class="sub-title">DETAILS</div>
<table>
    <tr>
        <td>
            Deployment Path:
        </td>
        <td>
            <b><?php echo DUPX_U::esc_html(PrmMng::getInstance()->getValue(PrmMng::PARAM_PATH_NEW)); ?></b>
        </td>
    </tr>
    <tr>
        <td>
            Check folders permission:
        </td>
        <td>
            <?php
            if (count($faildDirPerms) == 0) {
                ?><i class='green'>All existing folders have write permissions</i><?php
            } else {
                ?><i class='red'>Some folders do not have write permissions</i><?php
            }
            ?>
        </td>
    </tr>
    <?php foreach ($phpPerms as $phpTest) { ?>
        <tr>
            <td>
                PHP files extraction on <b><?php echo basename($phpTest['dir']); ?></b>:
            </td>
            <td>
            <?php if ($phpTest['pass']) { ?>
                <span class="green">Pass</span>
            <?php } else { ?>
                <span class="red"><?php echo $phpTest['message']; ?></span>
            <?php } ?>
        </tr>       
    <?php } ?>
    <tr>
        <td>
            Suhosin Extension:
        </td>
        <td>    
            <?php
            if (!extension_loaded('suhosin')) {
                ?><i class='green'>Disabled</i><?php
            } else {
                ?><i class='red'>Enabled</i><?php
            }
            ?>
        </td>
    </tr>
    <tr>
        <td>
            PHP Safe Mode:
        </td>
        <td>
            <?php
            if (!DUPX_Server::phpSafeModeOn()) {
                ?><i class='green'>Disabled</i><?php
            } else {
                ?><i class='red'>Enabled</i><?php
            }
            ?>
        </td>
    </tr>
</table>

<?php if (count($faildDirPerms) > 0) { ?>
<p>
    <b>Overwrite fails for these folders (change permissions or remove then restart):</b>
</p>
<div class="validation-iswritable-failes-objects">
    <pre><?php
    foreach ($faildDirPerms as $failedPath) {
        echo '- ' . DUPX_U::esc_html($failedPath) . "\n";
    }
    ?></pre>
</div>
<?php } ?>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        If there are problems with permissions (not writable files or folders) try to change the permissions to 755 for folders or 644 for files.
        <a href="https://en.wikipedia.org/wiki/File-system_permissions" target="_blank">Here you can find general information about File-system permissions.</a>
    </li>
    <li>
        Generally if the folders have write permissions but it is not possible to extract the PHP files, 
        the cause could be an external security service like "Imunify 360".
        If this is the case <a href="<?php echo DUPX_Constants::FAQ_URL; ?>how-to-fix-installer-archive-extraction-issues/" target="_blank">
            deactivate the checks
        </a> 
        temporarily, and run the installation again.
    </li>
    <li>
        Check <a href="<?php echo DUPX_Constants::FAQ_URL; ?>how-to-fix-installer-archive-extraction-issues/" target="_blank">our online documentation</a>
    </li>
</ul>