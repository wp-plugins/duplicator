<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...] */
/* @var $invalidEngines string[] */
/* @var $defaultEngine string */
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
            It is impossible to verify the list of engines in the database.
            <?php
            break;
        case DUPX_Validation_abstract_item::LV_HARD_WARNING:
            ?>
            Some of the MySQL engines used in the source site are not supported on the current database.
            <?php
            break;
        default:
            ?>
            Database engine for MySQL compatibility passed! This database supports the required MySQL engine types.
            <?php
            break;
    }
    ?>
</p>
<?php if (!empty($errorMessage)) : ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php endif; ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks to make sure this database can support the MySQL engines found in the
    <b><?php echo htmlentities($dupDatabaseDupFolder); ?></b> script.
</p>

<?php if ($testResult == DUPX_Validation_abstract_item::LV_HARD_WARNING) : ?>
    <p>
       The following MySQL Engine(s) were found to not be supported by the current database:
    </p>
    <ul>
        <?php foreach ($invalidEngines as $engine) : ?>
        <li><b><?php echo DUPX_U::esc_html($engine); ?></b></li>
        <?php endforeach; ?>
    </ul>
    and are going to be replaced by the default MySQL Engine <b>[<?php echo DUPX_U::esc_html($defaultEngine); ?>]</b>.
<?php endif; ?>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        In case some of the MySQL engines of the source site are not supported and replacing them with the default engine
        is not desired, please try getting in touch with your hosting provider and asking them to enable the engine.
    </li>
</ul>

