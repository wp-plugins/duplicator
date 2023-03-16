<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $lowerCaseTableNames int */
/* @var $errorMessage string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($isOk) { ?>
        No table casing issues detected. This servers variable setting for lower_case_table_names is [<?php echo $lowerCaseTableNames; ?>]
    <?php } else { ?>
        An upper case table name was found in the database SQL script and the server variable lower_case_table_names is set to 
        <b>[<?php echo $lowerCaseTableNames; ?>]</b>. 
        When both of these conditions are met it can lead to issues with creating tables with upper case characters.<br/>
        <b>Options</b>:<br/>
        - On this server have the host company set the lower_case_table_names value to 1 or 2 in the my.cnf file.<br/>
        - On the build server set the lower_case_table_names value to 2 restart server and build package.<br/>
        - Optionally continue the install with data creation issues on upper case tables names.<br/>
    <?php } ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks if any tables have upper case characters as part of the name.   
    On some systems creating tables with upper case can cause issues if the server
    setting for <a href="https://dev.mysql.com/doc/refman/5.7/en/identifier-case-sensitivity.html" target="_help">
        lower_case_table_names
    </a> is set to zero and upper case
    table names exist.
</p>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        In the my.cnf (my.ini) file set the 
        <a href="https://dev.mysql.com/doc/refman/5.7/en/server-system-variables.html#sysvar_lower_case_table_names" target="_help">
            lower_case_table_names
        </a>
        to 1 or 2 and restart the server.
    </li>
    <li>
        <i class="fa fa-external-link"></i> 
        <a href='http://www.inmotionhosting.com/support/website/general-server-setup/edit-mysql-my-cnf' target='_help'>
            How to edit MySQL config files my.cnf (linux) or my.ini (windows) files
        </a>
    </li>
</ul>



