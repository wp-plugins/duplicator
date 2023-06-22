<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $archiveSize string */
/* @var $maxSize string */
/* @var $maxTimeZero bool */
/* @var $maxTimeIni int */
/* @var $isOk bool */
?><p>  
    <b>Archive Size:</b> <?php echo $archiveSize; ?>  <small>(detection limit is set at <?php echo $maxSize; ?>)</small><br/>
    <b>PHP max_execution_time:</b> <?php echo $maxTimeIni; ?> <small>(zero means not limit)</small><br/>
    <b>PHP set_time_limit:</b> <?php
    if ($maxTimeZero) {
        ?><i class='green'>Success</i><?php
    } else {
        ?><i class='red'>Failed</i><?php
    }
    ?>
</p>
<p>
    The PHP <a href="http://php.net/manual/en/info.configuration.php#ini.max-execution-time" target="_blank">max_execution_time</a> 
    setting is used to determine how long a PHP process is allowed to run.  
    If the setting is too small and the archive file size is too large then PHP may not have enough
    time to finish running before the process is killed causing a timeout.
</p>
<p>
    Duplicator attempts to turn off the timeout by using the
    <a href="http://php.net/manual/en/function.set-time-limit.php" target="_blank">set_time_limit</a> setting.   
    If this notice shows as a warning then it is still safe to continue with the install.  
    However, if a timeout occurs then you will need to consider working with the max_execution_time setting or extracting the
    archive file using the 'Manual Archive Extraction' method. &nbsp;
    <a href="<?php echo DUPX_Constants::FAQ_URL; ?>how-to-handle-server-timeout-issues" target="_blank">[Additional FAQ Help]</a>
</p>