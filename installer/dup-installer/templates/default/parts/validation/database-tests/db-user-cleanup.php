<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $dbuser string */
/* @var $errorMessage string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($isOk) { ?>
        Successfully removed database user <b>[<?php echo htmlentities($dbuser); ?>]</b> with cPanel API.
    <?php } else { ?>
        The database user <b>[<?php echo htmlentities($dbuser); ?>]</b> was successfully created. 
        However removing the user was not successful via the cPanel API with the following response:<br/>
        To continue refresh the page, uncheck the 'Create New Database User' checkbox and select the user from the drop-down.
    <?php } ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks that the cPanl API is allowed to remove database user crated before.
</p>

<table>
    <tr>
        <td>User:</td>
        <td><b><?php echo htmlentities($dbuser); ?></b></td>
    </tr>
</table><br/>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Contact your host to make sure they support the cPanel API.</li>
    <li>Check with your host to make sure the user name provided meets the cPanel requirements.</li>
</ul>

