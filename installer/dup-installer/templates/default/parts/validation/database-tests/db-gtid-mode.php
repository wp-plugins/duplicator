<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $isOk bool */
/* @var $errorMessage string */

$statusClass = $isOk ? 'green' : 'red';
?>
<div class="sub-title">STATUS</div>
<p class="<?php echo $statusClass; ?>">
    <?php if ($isOk) { ?>
        The installer have not detected GTID mode.
    <?php } else { ?>
        Your database server have GTID mode is on, It might make a trouble in Database installation.<br/>
        <small>Details: You might face the error something like Statement violates GTID consistency. 
            You should ask hosting provider to make off GTID off. 
            You can make off GTID mode as decribed in the 
            <a href='https://dev.mysql.com/doc/refman/5.7/en/replication-mode-change-online-disable-gtids.html' target='_blank'>
                https://dev.mysql.com/doc/refman/5.7/en/replication-mode-change-online-disable-gtids.html
            </a>
        </small>
    <?php } ?>
</p>
<?php if (!empty($errorMessage)) { ?>
    <p>
        Error detail: <span class="maroon" ><?php echo htmlentities($errorMessage); ?></span>
    </p>
<?php } ?>

<div class="sub-title">DETAILS</div>
<p>
    This test checks to make sure the database server should not have GTID mode enabled.
</p>
<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li><i class="far fa-file-code"></i> <a href='https://dev.mysql.com/doc/refman/5.6/en/replication-gtids-concepts.html' target='_help'>What is GTID?</a></li>
</ul>




