<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int // DUPX_Validation_abstract_item::[LV_FAIL|LV_HARD_WARNING|...] */
/* @var $pathsList string[] */

$statusClass = ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING ? 'green' : 'red' );
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if (count($pathsList) === 0) { ?>
        No addon site detected.
    <?php } else { ?>
        Detected addon sites, see the details section for the list of sites.<br>
        <?php if ($testResult > DUPX_Validation_abstract_item::LV_SOFT_WARNING) { ?>
            Normal installation generally does not interfere with these sites.
        <?php } else { ?>
            These sites are not deleted even if you have selected an action that removes the files before extracting them.  
            If there are other folders outside the home path that are necessary for the addon site to work, it will be removed 
            so pay attention in the event there are addon custom installations.
            <?php
        }
    }
    ?>
</p>

<div class="sub-title">DETAILS</div>
<p>
    An addon site is a WordPress installation in a subfolder of the current home path.
</p>
<?php if (count($pathsList) > 0) { ?>
    <p>
        <i>Addons Site Paths</i>
    </p>
    <ul>
        <?php foreach ($pathsList as $path) { ?>
            <li>
                <b><?php echo $path; ?></b>
            </li>
            <?php
        }
        ?>
    </ul>
<?php } ?>
<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        The installer doesn't modify addon sites so their presence doesn't cause problems 
        but if you want to be sure you don't lose data it might be useful to make a backup of the addon site.
    </li>
</ul>
