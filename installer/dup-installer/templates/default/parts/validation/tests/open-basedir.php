<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

/* Variables */
/* @var $openBaseDirEnabled bool */
/* @var $pathsOutsideOpenBaseDir array */
/* @var $isOk bool */
?>
<p>
    <b>Open BaseDir:</b> 
    <?php
    if (!$openBaseDirEnabled) {
        ?><i class='green'>Disabled</i><?php
    } elseif (empty($pathsOutsideOpenBaseDir)) {
        ?><i class='green'>Enabled</i><?php
    } else {
        ?><i class='red'>Enabled</i><?php
    }
    ?>
</p>

<?php if (!$openBaseDirEnabled) : ?>
    The open_basedir configuration is disabled.
<?php elseif (empty($pathsOutsideOpenBaseDir)) : ?>
    All required archive paths were found in the open_basedir path list.
<?php else : ?>
    If <a href="http://php.net/manual/en/ini.core.php#ini.open-basedir" target="_blank">open_basedir</a> is enabled and you're
    having issues getting your site to install properly please work with your host and follow these steps to prevent issues:
    <ol style="margin:7px; line-height:19px">
        <li>Disable the open_basedir setting in the php.ini file</li>
        <li>If the host will not disable, then add the paths below to the open_basedir setting in the php.ini<br/>
            <?php foreach ($pathsOutsideOpenBaseDir as $path) : ?>
                <i class="maroon">"<?php echo DUPX_U::esc_html($path); ?>"</i><br>
            <?php endforeach;?>
        </li>
        <li>Save the settings and restart the web server</li>
    </ol>
<?php endif; ?>
