<?php

use Duplicator\Installer\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<!-- ============================================
STEP 3
============================================== -->
<?php
$sectionId   = 'section-troubleshoot';
$expandClass = $sectionId == $open_section ? 'open' : 'close';
?>
<section id="<?php echo $sectionId; ?>" class="expandable <?php echo $expandClass; ?>" >
    <h2 class="header expand-header">Troubleshooting Tips</h2>
    <div class="content" >
        <div id="troubleshoot" class="help-page">
            <div style="padding: 0px 10px 10px 10px;">
                <b>Common Quick Fix Issues:</b>
                <ul>
                    <?php $url = LinkManager::getDocUrl('what-host-providers-are-recommended-for-duplicator', 'install', 'help troubleshoot'); ?>
                    <li>Use a <a href='<?php echo DUPX_U::esc_attr($url); ?>' target='_blank'>Duplicator approved hosting provider</a></li>
                    <li>Validate directory and file permissions (see below)</li>
                    <li>Validate web server configuration file (see below)</li>
                    <li>Clear your browsers cache</li>
                    <li>Deactivate and reactivate all plugins</li>
                    <li>Resave a plugins settings if it reports errors</li>
                    <li>Make sure your root directory is empty</li>
                </ul>

                <b>Permissions:</b><br/>
                Not all operating systems are alike.  
                Therefore, when you move a package (zip/daf file) from one location to another the file and directory permissions may not always stick. 
                If this is the case then check your WordPress directories and make sure its permissions are set to 755. 
                For files make sure the permissions are set to 644 (this does not apply to Windows servers).   
                Also pay attention to the owner/group attributes.  For a full overview of the correct file changes see 
                the <a href='http://codex.wordpress.org/Hardening_WordPress#File_permissions' target='_blank'>WordPress permissions codex</a>
                <br/><br/>

                <b>Web server configuration files:</b><br/>
                For Apache web server the root .htaccess file was copied to htaccess__[HASH]. 
                A new stripped-down .htaccess file was created to help simplify access issues.  
                For IIS web server the web.config file was copied to web.config.orig, however no new web.config file was created. 
                If you have not altered this file manually then resaving your permalinks and resaving 
                your plugins should resolve most all changes that were made to the root web configuration file.   
                If you're still experiencing issues then open the .orig file and do a compare to see what changes need to be made. <br/><br/>
                <b>Plugin Notes:</b><br/> 
                It's impossible to know how all 3rd party plugins function.  
                Duplicator attempts to fix the new install URL for settings stored in the WordPress options table.   
                Please validate that all plugins retained their settings after installing.  
                If you experience issues try to bulk deactivate all plugins then bulk reactivate them on your new duplicated site. 
                If you run into issues where a plugin does not retain its data then try to resave the plugin's settings.
                <br/><br/>

                <b>Cache Systems:</b><br/>
                Any type of cache system such as Super Cache, W3 Cache, etc. should be emptied before you create a package.  
                Another alternative is to include the cache directory in the directory exclusion path list found in the options dialog. 
                Including a directory such as \pathtowordpress\wp-content\w3tc\ (the w3 Total Cache directory) will exclude this directory from being packaged. 
                In is highly recommended to always perform a cache empty when you first fire up your new site even if you excluded your cache directory.
                <br/><br/>

                <b>Trying Again:</b><br/>
                If you need to retry and reinstall this package you can easily run the process again by deleting all files except 
                the installer and package file and then browse to the installer again.
                <br/><br/>

                <b>Additional Notes:</b><br/>
                If you have made changes to your PHP files directly this might have an impact on your duplicated site.  
                Be sure all changes made will correspond to the site's new location.
                Only the package (zip/daf file) and the installer (php file) should be in the directory where you are installing the site.  
                Please read through our knowledge base before submitting any issues.
                If you have a large log file that needs to be evaluated please email the file, or attach it to a help ticket.
            </div>
        </div>
    </div>
</section>
