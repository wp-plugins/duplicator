<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<!-- ============================================
STEP 4
============================================== -->
<?php
$sectionId   = 'section-step-4';
$expandClass = $sectionId == $open_section ? 'open' : 'close';
?>
<section id="<?php echo $sectionId; ?>" class="expandable <?php echo $expandClass; ?>" >
    <h2 class="header expand-header">
        Step <span class="step">4</span>: Test Site
    </h2>
    <div class="content" >
        <a class="help-target" name="help-s4"></a>
        <div id="dup-help-step3" class="help-page">
            <h3>Final Steps</h3>

            <b>Review Install Report</b><br/>
            The install report is designed to give you a synopsis of the possible errors and warnings that may exist after the installation is completed.
            <br/><br/>

            <b>Test Site</b><br/>
            After the install is complete run through your entire site and test all pages and posts.
            <br/><br/>

            <b>Final Security Cleanup</b><br/>
            When completed with the installation please delete all installation files.  
            <b>Leaving these files on your server can be a security risk!</b>  You can remove
            all these files by logging into your WordPress admin and following the remove notification links or by deleting these file manually.  
            Be sure these files/directories are removed.  Optionally it is also recommended to remove the archive.zip/daf file.
            <ul>
                <li>dup-installer</li>
                <li>installer.php</li>
                <li>installer-backup.php</li>
                <li>dup-installer-bootlog__[HASH].txt</li>
                <li>archive.zip/daf</li>
            </ul>
        </div>
    </div>
</section>