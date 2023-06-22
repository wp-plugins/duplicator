<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...] */
/* @var $invalidCharsets string[] */
/* @var $invalidCollations string[] */
/* @var $charsetsList string[] */
/* @var $collationsList string[] */
/* @var $usedCharset string */
/* @var $usedCollate string */
/* @var $errorMessage string */



$statusClass = $testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING ? 'green' : 'red';

$dupDatabase          = basename(DUPX_Package::getSqlFilePath());
$dupDatabaseDupFolder = basename(DUPX_INIT) . '/' . $dupDatabase;
$invalidCheckboxTitle = '';
$subTitle             = '';

?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php
    switch ($testResult) {
        case DUPX_Validation_abstract_item::LV_FAIL:
            ?>
            It is impossible to verify the list of charsets in the database.
            <?php
            break;
        case DUPX_Validation_abstract_item::LV_HARD_WARNING:
            if (!empty($invalidCharsets) && !empty($invalidCollations)) {
                $invalidCheckboxTitle = '"Legacy Character set" and "Legacy Collation"';
                $subTitle             = 'character set and collation';
            } elseif (!empty($invalidCharsets)) {
                $invalidCheckboxTitle = '"Legacy Character set"';
                $subTitle             = 'character set';
            } elseif (!empty($invalidCollations)) {
                $invalidCheckboxTitle = '"Legacy Collation"';
                $subTitle             = 'collation';
            }
            ?>
            <?php echo htmlentities($subTitle); ?> isn't supported on current database. 
            <?php echo htmlentities($invalidCheckboxTitle); ?>  will be replaced with default values.<br>
            <?php
            break;
        default:
            ?>
            Character set and Collate test passed! This database supports the required table character sets and collations.
            <?php
            break;
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
    This test checks to make sure this database can support the character set and collations found in the 
    <b><?php echo htmlentities($dupDatabaseDupFolder); ?></b> script.
</p>

<table class="validation-charset-list margin-bottom-1">
    <tbody>
        <tr>
            <td colspan="2" >
                <b>Character set list</b>
            </td>
        </tr>
        <?php foreach ($charsetsList as $charset) { ?>
            <tr>
                <td>
                    <?php echo $charset; ?>
                </td>
                <td>
                    <?php $testLv = in_array($charset, $invalidCharsets) ? DUPX_Validation_abstract_item::LV_FAIL : DUPX_Validation_abstract_item::LV_PASS; ?>
                    <span class="status-badge <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($testLv); ?>">
                </td>
            </tr>
        <?php } ?>
        <tr>
            <td colspan="2" >
                <b>Collations list</b></b>
            </td>
        </tr>
        <?php foreach ($collationsList as $collate) { ?>
            <tr>
                <td><?php echo $collate; ?></td>
                <td>
                    <?php $testLv = in_array($collate, $invalidCollations) ? DUPX_Validation_abstract_item::LV_FAIL : DUPX_Validation_abstract_item::LV_PASS; ?>
                    <span class="status-badge <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($testLv); ?>">
                </td>
            </tr>
        <?php } ?>
    <tbody>
</table>
<?php if ($testResult == DUPX_Validation_abstract_item::LV_HARD_WARNING) { ?>
    <p>
        The database where the package was created has a <b><?php echo htmlentities($subTitle); ?></b> that is not supported on this server. 
        This issue happens when a site is moved from an newer version of MySQL to a older version of MySQL. 
        The recommended fix is to update MySQL on this server to support the character set that is failing below. 
        <b>If this is not an option for your host, then you can continue the installation. Invalid values will be replaced with the default values.</b>
        For more details about this issue and other details regarding this issue see the FAQ link below.
    </p>
<?php } ?>
<p>
    <i>Default charset and setting in current installation</i><br>
    <i>DB_CHARSET = <b><?php echo $usedCharset; ?></b></i><br>
    <i>DB_COLLATE = <b><?php echo $usedCollate; ?></b></i>
<p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        <i class="far fa-file-code"></i> 
        <a href='<?php echo DUPX_U::esc_attr(DUPX_Constants::FAQ_URL); ?>how-to-fix-database-write-issues/' target='_help'>
            What is Compatibility mode & 'Unknown Collation' errors?
        </a>
    </li>
    <li>
        In case the default charset/collates are not the desired ones you can <b>change the setting</b> in the <b>advanced installation mode</b>.
    </li>
</ul>

