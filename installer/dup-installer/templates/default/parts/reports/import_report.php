<?php

/**
 *
 * @package Duplicator/Installer
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/** @var int $numAdded */
/** @var int $numChanged */
/** @var string $csvUrl */

?>
<p>
    <b><?php echo $numAdded; ?></b> users have been added<br>
    <b><?php echo $numChanged; ?></b> users were already present and therefore have been modified
</p>
<?php if ($numAdded == 0 && $numChanged == 0) { ?>
<p>
    No users have been imported and/or modified, all users from the source site are in the target site.
</p>          
<?php  } elseif ($csvUrl) { ?>
    A CSV report has been generated with the list of all the users added/modified and the mapping of the modifications<br>
    <a href="<?php echo DUPX_U::esc_url($csvUrl); ?>" download="import_users.csv">
        Download import CSV report
    </a><br>
    <i>Note: This report does not contain users who were already at the target site.</i>
<?php } else { ?>
<p>
    Csv report rile can't be generated
</p>
<?php } ?>