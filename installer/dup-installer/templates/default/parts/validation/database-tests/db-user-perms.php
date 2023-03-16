<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $testResult int */
/* @var $dbuser string */
/* @var $dbname string */
/* @var $perms array */
/* @var $errorMessages string[] */

$statusClass = $testResult == DUPX_Validation_test_db_user_perms::LV_PASS ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php
    switch ($testResult) {
        case DUPX_Validation_test_db_user_perms::LV_PASS:
            ?>
            The user <b>[<?php echo htmlentities($dbuser); ?>]</b> has the correct privileges on the database <b>[<?php echo htmlentities($dbname); ?>]</b>.
            <?php
            break;
        case DUPX_Validation_test_db_user_perms::LV_FAIL:
            ?>        
            The user <b>[<?php echo htmlentities($dbuser); ?>]</b> is missing privileges on the database <b>[<?php echo htmlentities($dbname); ?>]</b>
            <?php
            break;
        case DUPX_Validation_test_db_user_perms::LV_HARD_WARNING:
            ?>        
            The user <b>[<?php echo htmlentities($dbuser); ?>]</b> is missing privileges on the database <b>[<?php echo htmlentities($dbname); ?>]</b><br>
            You can continue with the installation but some features may not be restored correctly.
            <?php
            break;
    }
    ?>
</p>
<?php if (!empty($errorMessages)) { ?>
    <p>
        Error detail: <br>
        <?php foreach ($errorMessages as $errorMessage) { ?>
            <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span><br>
        <?php } ?>
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks the privileges of the current database user.  In order to successfully use Duplicator all of the privileges should pass.
    In the event the checks below   fail, contact your hosting provider to make sure the database user has the correct permissions listed below.
    <br/><br/>

    <i>
        Note:  In some cases "Create Views, Procedures, Functions and Triggers" will not pass, but continuing with the install will still work.
        It is however recommended that a green pass on all permissions is set when possible.  Please work with your hosting provider to get all
        values to pass.
    </i>
</p><br/>

<div class="sub-title">TABLE PRIVILEGES ON [<?php echo htmlentities($dbname); ?>]</div>

<table class="s1-validate-sub-status">
    <tr>
        <td>Create</td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['create']); ?>"></span></td>
    </tr>
    <tr>
        <td>Select</td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['select']); ?>"></span></td>
    </tr>
    <tr>
        <td>Insert</td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['insert']); ?>"></span> </td>
    </tr>
    <tr>
        <td>Update</td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['update']); ?>"></span></td>
    </tr>
    <tr>
        <td>Delete</td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['delete']); ?>"></span></td>
    </tr>
    <tr>
        <td>Drop</td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['drop']); ?>"></span></td>
    </tr>
    <?php if ($perms['view'] < DUPX_Validation_abstract_item::LV_SKIP) : ?>
        <tr>
            <td>Create Views</td>
            <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['view']); ?>"></span></td>
        </tr>
    <?php endif; ?>

    <?php if ($perms['proc'] < DUPX_Validation_abstract_item::LV_SKIP) : ?>
    <tr>
        <td>Procedures <small>(Create &amp; Alter)</small> </td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['proc']); ?>"></span></td>
    </tr>
    <?php endif; ?>

    <?php if ($perms['func'] < DUPX_Validation_abstract_item::LV_SKIP) : ?>
    <tr>
        <td>Functions <small>(Create &amp; Alter)</small>  </td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['func']); ?>"></span></td>
    </tr>
    <?php endif; ?>

    <?php if ($perms['trigger'] < DUPX_Validation_abstract_item::LV_SKIP) : ?>
    <tr>
        <td>Trigger</td>
        <td><span class="status-badge right <?php echo DUPX_Validation_abstract_item::resultLevelToBadgeClass($perms['trigger']); ?>"></span></td>
    </tr>
    <?php endif; ?>
</table><br/>

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>Validate that the database user is correct per your hosts documentation</li>
    <li>
        Check to make sure the 'User' has been granted the correct privileges
        <ul class='vids'>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href='https://www.youtube.com/watch?v=UU9WCC_-8aI' target='_video'>How to grant user privileges in cPanel</a>
            </li>
            <li>
                <i class="fa fa-video-camera"></i> 
                <a href="https://www.youtube.com/watch?v=FfX-B-h3vo0" target="_video">How to grant user privileges in phpMyAdmin</a>
            </li>
        </ul>
    </li>
    <li>
        <a href="<?php echo DUPX_U::esc_attr(DUPX_Constants::FAQ_URL); ?>#faq-installer-100-q" target="_help"
           title="I'm running into issues with the Database what can I do?">
            [Additional FAQ Help]
        </a>
    </li>
</ul>