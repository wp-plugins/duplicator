<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $memoryLimit string */
/* @var $minMemoryLimit string */
/* @var $isOk bool */
?>
<p>
<div class="sub-title">STATUS</div>
<p>
    <?php if ($isOk) : ?>
        <i class='green'>
            The memory_limit has a value of <b>[<?php echo $memoryLimit; ?>]</b> which is higher or equal to the suggested minimum of 
            <b>[<?php echo $minMemoryLimit; ?>]</b>.
        </i>
    <?php else : ?>
        <i class='red'>
            The memory_limit has a value of <b>[<?php echo $memoryLimit; ?>]</b> 
            which is lower than the suggested minimum of <b>[<?php echo $minMemoryLimit; ?>]</b>.
        </i>
    <?php endif; ?>
</p>

<div class="sub-title">DETAILS</div>
<p>

</p>
The 'memory_limit' configuration in php.ini sets how much memory a script can use during its runtime. 
When this value is lower than the suggested minimum of
<?php echo $minMemoryLimit; ?> the installer might run into issues.

<div class="sub-title">TROUBLESHOOT</div>
<ul>
    <li>
        Try Increasing the memory_limit.&nbsp;
        <a href="<?php echo DUPX_Constants::FAQ_URL; ?>how-to-manage-server-resources-cpu-memory-disk/" target="_blank">[Additional FAQ Help]</a>
    </li>
</ul>