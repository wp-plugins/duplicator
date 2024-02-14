<?php

use Duplicator\Installer\Utils\LinkManager;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<!-- ============================================
SECURITY STEP
============================================== -->
<?php
$sectionId   = 'section-security';
$expandClass = $sectionId == $open_section ? 'open' : 'close';
?>

<section id="<?php echo $sectionId; ?>" class="expandable <?php echo $expandClass; ?>" >
<h2 class="header expand-header">Installer Security</h2>
<div class="content" >
    <div id="dup-help-installer" class="help-page">
        The installer for Duplicator supports these three security modes.  Secure-file name, basic password and archive encryption (pro only).
        <br/><br/>

        <table class="help-opt">
            <tr>
                <th class="col-opt">Option</th>
                <th>Details</th>
            </tr>
            <tr>
                <td class="col-opt"><i class="fas fa-lock"></i> Password</td>
                <td>
                    In the upper right corner  of the installer is an icon that indicates if the installer is password protected (locked) or
                    no password (unlocked).
                    <br/><br/>

                    <b><i class="fas fa-lock"></i> Locked</b>
                    "Locked" means a password is protecting each step of the installer. This option is recommended on all installers that are accessible
                    via a public URL.  The option is not required but strongly recommended, unless using secure-file name or archive encryption.
                    <br/><br/>

                    <b><i class="fas fa-unlock"></i> Unlocked</b>
                    "Unlocked" indicates the installer is not password protected.   While it is not required to have a password set it is recommended.
                    If your URL has little to no traffic or has never been the target of an attack then running the installer quickly and then removing the
                    installer files without a password could be performed but is not recommended, unless using secure-file name or archive encryption.
                </td>
            </tr>
            <tr>
                <td class="col-opt">
                    <i class="fas fa-shield-alt"></i> Secure-File <br/>
                    <small>Archive File Name</small>
                </td>
                <td>
                    When Duplicator creates a site archive it generates three separate files.  The archive.zip/daf, installer.php, and a log
                    of the build process.   All three files are built with a secure-file name and stored to a storage location either on the server or in
                    the cloud.  Examples of the files will look something like the following:
                    <ul>
                        <li>my-name_64fc6df76c17f2023225_20220816004809_<b>archive.zip</b></li>
                        <li>my-name_64fc6df76c17f2023225_20220816004809_<b>installer.php</b></li>
                        <li>my-name_64fc6df76c17f2023225_20220816004809_<b>.log</b></li>
                    </ul><br/>
                    
                    A secure-file name has the following  descriptors <i>[name]_[hash]_[time]</i> built into the file name.
                    <ul>
                        <li><b>[name]</b> This is the name given to the package when it is created.</li>
                        <li><b>[hash]</b> This is a uniquely generated series of characters almost impossible to guess.</li>
                        <li><b>[time]</b> This is the date and time the package was created down-to the second</li>
                    </ul><br/>
                    
                    All files are initially created this way and should not be changed with the exception of the installer.php. The installer can be renamed
                    or setup to be downloaded as just 'installer.php'.  It is strongly recommended to use the secure-file format on the installer to provide a
                    higher level of security.  The secure-file format helps prevent unauthorized users on public servers.  Archive and log file names should
                    never be changed or modified.
                    <ul>
                        <li><b>Basic:</b> installer.php</li>
                        <li><b>Secure:</b> <i>[name]_[hash]_[time]_installer.php</i> (recommended)</li>
                    </ul>

                    <i class="fas fa-info-circle"></i> Archive File Name Tip: The secure-file archive name can be viewed the following ways:
                    <ul>
                        <li>Goto: WordPress Admin ❯ Duplicator ❯ Packages ❯ Details of the site where the package was built</li>
                        <li>Copy the name from any cPanel, file explorer, or FTP client where it was downloaded/uploaded</li>
                        <li>
                            Search for 'package_name' in the <i>archive.zip/daf//dup-installer/<b>dup-archive__[hash].txt</b></i> file<br/>
                            <small>Example of hashed file name in archive file would be <i>dup-archive__3b8ded1-19035119.txt</i></small>
                        </li>
                    </ul>
                </td>
            </tr>
        </table>
        <br/><br/>

        <b>Password Security</b><br/>
        The installer can provide basic password protection, with the password being set at package creation time. 
        This setting is optional and can be turned on/off via the package creation screens.
        <small>
            For forgotten passwords users can log in to the site where the package was created and check the package details for the original password.
            For detail on how to override this setting visit the online FAQ for
            <a 
                href="<?php echo LinkManager::getDocUrl('how-to-fix-installer-security-protection-issues', 'install', 'help security'); ?>"
                target="_blankopen_section"
            >
                more details
            </a>.
        </small>
        <br/><br/>

        <b>Secure-File Security</b><br/>
        When you attempt an <i class="maroon">"Overwrite Install"</i> using the "installer.php"  filename on a public server (non-localhost) and
        have not set a password, the installer will prompt for the filename of the associated archive.zip/daf file.  This is to prevent an outside
        entity from executing the installer.   To complete the install, simply copy the filename of the archive and paste (or type) it into the
        archive filename box.
        <small>
             Using a secure-file installer name (Settings &gt; Packages), renames the installer to something unique, setting a password or installing
             from localhost will cause the archive filename to no longer be required.
        </small>
        <br/><br/>

       <b>Archive Encryption</b>
       <sup class="hlp-pro-lbl">Pro</sup><br/>
       The archive encryption is the most secure and recommended encryption method.   This option is set during the package creation process and encrypts
       the archive.zip/daf file.   The archive file cannot be opened without a password which can be done from either the installer file or from a client
       side program like 7-Zip, WinZip, iZip etc.
       <br/><br/>

       <i>
        Note: Even though the installer has a password protection feature, it should only be used for the short term while the installer is being used.
        All installer files should and must be removed after the install is completed.  Files should not to be left on the server for any long duration
        of time to prevent any security related issues. It is absolutely required and recommended to remove <u>all</u> installer files after  installation
        is completed by logging into the WordPress admin and following the Duplicator prompts.
       </i>

    </div>
</div>
</section>
