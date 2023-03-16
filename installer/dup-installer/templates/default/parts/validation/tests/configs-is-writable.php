<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...] */
/* @var $configsCheck array */

$statusClass = ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING ? 'green' : 'red' );
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING) { ?>
        All configuration files are editable.
    <?php } else { ?>
        One or more configuration files cannot be edited, the list is in the details section 
    <?php } ?>
</p>

<div class="sub-title">DETAILS</div>
<p>
    This test verifies that the configuration files are editable. 
    Otherwise it is possible to continue with the installation but some settings will be disabled and 
    it will not be possible to modify the configuration file without write permissions.
</p>
<table class="margin-bottom-1">
    <tbody>
        <tr>
            <td>
                <b>wp-config.php</b>
            </td>
            <td>
                <?php $testLv = $configsCheck['wpconfig'] ? DUPX_Validation_abstract_item::LV_PASS : DUPX_Validation_abstract_item::LV_FAIL; ?>
                <span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($testLv); ?>"></span>
            </td>
        </tr>
        <tr>
            <td>
                <b>.htaccess</b>
            </td>
            <td>
                <?php $testLv = $configsCheck['htaccess'] ? DUPX_Validation_abstract_item::LV_PASS : DUPX_Validation_abstract_item::LV_FAIL; ?>
                <span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($testLv); ?>"></span>
            </td>
        </tr>
        <tr>
            <td>
                <b>Other configs</b><br>
                [ web.config, php.ini, .user.ini ]
            </td>
            <td valign="top">
                <?php $testLv = $configsCheck['other'] ? DUPX_Validation_abstract_item::LV_PASS : DUPX_Validation_abstract_item::LV_FAIL; ?>
                <span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($testLv); ?>"></span>
            </td>
        </tr>
    <tbody>
</table>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>If possible, via FTP or file manager, manually change the permissions of the conditioner files.</li>
    <li>In case the home path does not have write or run permissions, add them manually.</li>
</ul>
