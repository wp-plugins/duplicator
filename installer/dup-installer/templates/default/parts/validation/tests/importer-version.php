<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/** Variables
 * @var int    $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...]
 * @var string $importerVer
 */

$statusClass = ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING ? 'green' : 'red' );
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING) { ?>
        The version of Duplicator importer is compatible with the current package.
    <?php } else { ?>
        Version of Duplicator importer is old compared to version of current package.
    <?php } ?>
</p>

<div class="sub-title">DETAILS</div>
<p>
    Importer version: <b><?php echo $importerVer; ?></b><br>
    Package version: <b><?php echo DUPX_VERSION; ?></b>
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        The version of Duplicator in the importer site must be equal to or greater than the version with which the package was created.<br>
        <b>Please update Duplicator to the latest version and restart the installation.</b>
    </li>
    <li>
        In case it is not possible to update the plugin, it is possible to perform a classic installation by starting the installer directly
    </li>
</ul>
