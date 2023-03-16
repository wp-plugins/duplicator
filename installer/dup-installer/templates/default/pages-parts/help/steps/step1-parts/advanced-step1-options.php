<?php

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<h3>
    Options
    <small style='font-size:11px; color:#888'>Advanced mode</small>
</h3>
The advanced options are only shown when the installer mode is set to "Advanced."  This section allows users to change or set advanced options,
configure additional database settings and set other configuration options in the wp-config.php file.
<br/><br/>

<!-- ********************************************
ADVANCED TAB
******************************************** -->
<h4>
    <i class="far fa-folder fa-fw"></i>
    Advanced Tab
</h4>
These are the advanced options for advanced users.

<table class="help-opt">
    <tr>
        <th class="col-opt">Option</th>
        <th>Details</th>
    </tr>
    <tr>
        <td colspan="2" class="section">
            Processing
        </td>
    </tr>
    <tr>
        <td class="col-opt">Extraction<br/> Mode</td>
        <td>
            <b>Manual Archive Extraction</b><br/>
            Set the Extraction value to "Manual Archive Extraction" when the archive file has already been manually extracted on the server.  This can be
            done through your host's control panel such as cPanel or by your host directly.  This setting can be helpful if you have a large archive file
            or are having issues with the installer extracting  the file due to timeout issues.
            <br/><br/>

            <b>PHP ZipArchive</b><br/>
            This extraction method will use the PHP <a href="http://php.net/manual/en/book.zip.php" target="_blank">ZipArchive</a> code to extract the
            archive zip file.
            <br/><br/>

            <b>PHP ZipArchive Chunking</b><br/>
            This extraction method will use the PHP <a href="http://php.net/manual/en/book.zip.php" target="_blank">ZipArchive</a>  code with multiple
            execution threads to extract the archive zip file.
            <br/><br/>

            <b>Shell-Exec Unzip</b><br/>
            This extraction method will use the PHP <a href="http://php.net/manual/en/function.shell-exec.php" target="_blank">shell_exec</a>
            to call the system unzip command on the server. This is the default mode that is used if it's available on the server.
            <br/><br/>

            <b>DupArchive</b><br/>
            This extraction method will use the DupArchive extractor code to extract the daf-based archive file.
        </td>
    </tr>
    <tr>
        <td>Server<br/>Throttling</td>
        <td>
            If the current host is a budget host that monitors CPU usage, then users might want to consider checking this box to help slow down the
            process and not kick off any high-usage monitors.
            <br/>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="section">
            Extraction Flow
        </td>
    </tr>
    <tr>
        <td>Archive Action</td>
        <td>
            <b>Extract files over current files</b><br/>
            The existing site files will be overwritten with the contents of the archive.zip/daf.
            <br/><br/>

            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Remove WordPress core and content and extract</b><br/>
            The existing WordPress core files and WordPress content directory will be removed, and then the archive will be extracted.
            <br/><br/>

            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Remove all files except add-on sites and extract</b><br/>
            All files except an add-on site will be removed, and then the archive will be extracted.  An add-on site is a site/domain that is stored in a
            directory off of your main site that has been "added on" to your main hosting account.  For instance, when you purchased a hosting account it
            could be for a.com.  Then after that, you decided to add b.com and c.com to the same hosting account.  The structure of this setup is often the
            following although it can vary some:

            <ul>
                <li>/public_html - contains files for a.com</li>
                <li>/public_html/b.com - contains files for b.com</li>
                <li>/public_html/c.com - contains files for c.com</li>
            </ul>

            The directories /public_html/b.com and c.com contain the files for the add-on sites b.com and c.com (so the option above means that b.com and c.com
            would be preserved and not deleted when you installed to a.com)
        </td>
    </tr>
    <tr>
        <td class="col-opt">Skip Files</td>
        <td>
            <b>Extract all files</b><br/>
            Extract all files from the package archive.  This option is selected by default.
            <br/><br/>

            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Skip extraction of WordPress core files</b><br/>
            Extract all files except WordPress core files. Choose this option to extract only the wp-content folder and other non-core WordPress
            files and directories.
            <br/><br/>

            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Skip extraction of WordPress core files and plugins/themes existing on host</b><br/>
            Extract all files except WordPress core files and existing plugins/themes on the current host.
            <br/><br/>

            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Extract only media files and new plugins and themes</b><br/>
            Extract all media files, new plugins, and new themes. The installer will not extract plugins and themes that already exist on the destination site.
        </td>
    </tr>
    <tr>
        <td class="col-opt">File Times</td>
        <td>
            When the archive is extracted it should show the current date-time or keep the original time it had when it was built.
            This setting will be applied to all files and directories.
            <i>Note: Setting the Original time is currently only supported when using the ZipArchive Format.</i>
        </td>
    </tr>
    <tr>
        <td class="col-opt">File<br/> Permissions</td>
        <td>
            Switch on and set permissions in either octal or symbolic values to assign permissions to files. This option is not available on Windows machines.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Directory<br/> Permissions</td>
        <td>
            Switch on and set permissions in either octal or symbolic values to assign permissions to directories. This option is not available on
            Windows machines.
        </td>
    </tr>
    <tr>
        <td colspan="2" class="section">
            Configuration Files
        </td>
    </tr>
    <tr>
        <td class="col-opt">
           WordPress<br/>
           <small> wp-config</small>
        </td>
        <td>
            <b>Do nothing</b><br/>
            This option simply does nothing. The wp-config file does not get backed up, renamed, or created. This advanced option assumes you already
            know how it should behave in the new environment.  This option is for advanced technical persons.
            <br/><br/>

            <b>Modify original</b><br/>
            This is the default recommended option which will modify the original wp-config file.
            <br/><br/>

            <b>Create new from wp-config sample</b><br/>
            This option creates a new wp-config file by modifying the wp-config-sample.php file.
            The new wp-config.php file will behave as if it was created in a fresh, default WordPress installation.
            <br/><br/>
        </td>
    </tr>
    <tr>
        <td class="col-opt">
             Apache<br/>
            <small>.htaccess</small>
        </td>
        <td>
            <b>Do nothing</b><br/>
            This option simply does nothing.  The .htaccess is not backed up, renamed, or created.  This advanced option assumes you already have your
            .htaccess file set up and know how it should behave in the new environment.   When the package is built it will always create an .htaccess file
            at this location:
            <code>
                /dup-installer/original_files_[HASH]/.htaccess
            </code>
            Since the file is already in the archive file it will show up when the archive is extracted.
            <br/><br/>

            <b>Retain original from Archive.zip/daf</b><br/>
            This option simply copies the /dup-installer/original_files_[HASH]/.htaccess file to the .htaccess file.  Please note this option will cause issues
            with the install process if the .htaccess is not properly set up to handle the new server environment.  This is an advanced option and should
            only be used if you know how to properly configure your .htaccess configuration.
            <br/><br/>

            <b>Create New</b><br/>
            This is the default recommended option which will create a new .htaccess file.  The new .htaccess file is streamlined to help
            guarantee no conflicts are created during install.
            <br/><br/>

            <small>
                <b>Notes:</b>  Inside the archive.zip or archive.daf will be a copy of the original .htaccess (Apache) file that was set up with your 
                packaged site.  The .htaccess file is copied to /dup-installer/original_files_[HASH]/source_site_htaccess. When using either "Create New"
                or "Retain original from Archive.zip/daf" an existing .htaccess file will be backed up to a
                /wp-content/backups-dup-lite/installer/original_files_[HASH]/source_site_htaccess.
                <i>This change will not made until the final step is completed, to avoid any issues the .htaccess might cause during the install</i>
            </small>
        </td>
    </tr>
    <tr>
        <td class="col-opt">
            General<br/>
            <small>php.ini, .user.ini,<br/> web.config</small>
        </td>
        <td>
            <b>OVERVIEW</b><hr/>
            When the archive is built it will always create an original backup of the php.ini, .user.ini, and web.config files ("Config Files") if they exist. 
            The backups will be in the following location within the archive file without an extension.
            <code>
                <b>Original File Backups</b><br/>
                archive.zip|daf/dup-installer/original_files_[HASH]/<i>[location]</i>_<b>phpini</b> <br/>
                archive.zip|daf/dup-installer/original_files_[HASH]/<i>[location]</i>_<b>userini</b> <br/>
                archive.zip|daf/dup-installer/original_files_[HASH]/<i>[location]</i>_<b>webconfig</b>
            </code>
            If there are "Config Files" on the new server, backups may also optionally be created. Backup <i>[location]</i> is defined by the following:
            <ul>
                <li><b>source_site</b> Backup of original file when the archive was created on the source host</li>
                <li><b>installer_host</b> Backup of original file before the installer starts on active host</li>
            </ul><br/>

            
            <b>ACTIONS</b><hr/>
            <b>Do nothing</b><br/>
            This option performs no actions and assumes you already have your configuration files set up properly; either on the server or in the archive.
            If the same "Config Files" exist in both the deploy directory and the archive, then the archive files will overwrite any configuration files
            that exist at the same location in the archive.
            <br/><br/><br/>

            <b>Retain original from Archive.zip/daf</b>
            <ol>
                <li>
                    Moves any existing "Config Files" on the new host to the following location:
                    <code>
                        /dup-installer/original_files_[HASH]/installer_host_[CONFIG-TYPE]<br/>
                        <small> An existing "Config File"  resides on the server before the archive is extracted or step 1 is ran.</small>
                    </code>
                </li>
                <li>
                    The installer last step copies all "Config Files" from the archive backups to the correct location on the new host. 
                    They are copied from this location:<br/>
                    <code>
                        /dup-installer/original_files_[HASH]/<b>source_site</b>_[CONFIG-TYPE]
                    </code>
                </li>
            </ol><br/>

            <b>Reset</b>
            <ol>
                <li>
                    Moves any existing "Config Files" on the new host to the following location:
                    <code>
                       /dup-installer/original_files_[HASH]/installer_host_[CONFIG-TYPE]<br/>
                       <small> An existing "Config File"  resides on the server before the archive is extracted or step 1 is ran.</small>
                    </code>
                </li>
                <li>
                    If any "Config Files" already exist in the archive they will be deployed "as is" to the location that matches the
                    archive file structure.
                </li>
            </ol>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="section">
            General
        </td>
    </tr>
    <tr>
        <td class="col-opt">Logging</td>
        <td>
            The level of detail that will be sent to the log file (installer-log.txt).  The recommended setting for most installs should be "Light."
            Note if you use Debug the amount of data written can be very large.  Debug is only recommended for support.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Cleanup</td>
        <td>
            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Remove disabled plugins/themes</b><br/>
            Remove all inactive plugins and themes when installing site.  Inactive users will also be removed during subsite to standalone migrations.
            <br/><br/>

            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Remove users without permissions</b><br/>
            Removes users that currently do not have any permissions associated with their accounts.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Safe Mode</td>
        <td>
            Safe mode is designed to configure the site with specific options at install time to help overcome issues that may happen during the install
            where the site is having issues. These options should only be used if you run into issues after you have tried to run an install.
            <br/><br/>

            <b>Disabled</b><br/>
            This is the default. This option will not apply any additional settings at install time.
            <br/><br/>

            <b>Enabled</b><br/>
            When enabled the safe mode option will disable all the plugins at install time.
            <i>Note:  When this option is set you will need to manually re-enable the plugins that need to be enabled after the install from the
            WordPress admin plugins page.</i>
        </td>
    </tr>
</table>
<br/><br/>


<!-- ********************************************
DATABASE TAB
******************************************** -->
<h4>
    <i class="far fa-folder fa-fw"></i>
    Database Tab
</h4>
These are the advanced options for database configuration.
<table class="help-opt">
    <tr>
        <th class="col-opt">Option</th>
        <th>Details</th>
    </tr>
    <tr>
        <td class="col-opt">Table Prefix</td>
        <td>
            <sup class="hlp-pro-lbl">Pro</sup>
            This option allows changing the table prefix to other than the package creation site's table prefix.  The table prefix is the value placed in the
            front of your database tables.  It is possible to have multiple installations in one database if you give each WordPress site a unique prefix.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Mode</td>
        <td>
            Modes affect the SQL syntax MySQL supports (and others such as MariaDB) .  This setting performs various data validation checks.  This makes 
            it easier to use MySQL in different environments and to use MySQL together with other database servers.  It is very useful when running into
            conversion issues.  The following options are supported:
            <ul>
                <li><b>Default:</b>  This is the recommended setting to use.  It will use the current Database mode setting.</li>
                <li><b>Disable:</b> This will prevent the database engine from running in any mode.</li>
                <li><b>Custom:</b> This option will allow you to enter a custom set of mode commands.  See the documentation link below for options.</li>
            </ul>

            For a full overview please see the  <a href="https://dev.mysql.com/doc/refman/8.0/en/sql-mode.html" target="_blank">MySQL mode</a> and
            <a href="https://mariadb.com/kb/en/sql-mode/" target="_blank">MariaDB mode</a>  specific to  your version.     To add a custom setting enable the
            Custom radio button and enter in the mode(s) that needs to be applied.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Processing</td>
        <td>
            <b>Chunking mode</b><br/>
            Split the work of inserting data across several requests.  If your host throttles requests or you're on a shared server that is being heavily
            utilized by other sites then you should choose this option.  This is the default option.
            <br/><br/>

            <b>Single step</b><br/>
            Perform data insertion in a single request.  This is typically a bit faster than chunking, however it is more susceptible to problems when the
            database is large or the host is constrained.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Create</td>
        <td>
            Run all CREATE SQL statements at once.  This option should be checked when source database tables have foreign key relationships.
            When choosing this option there might be a chance of a timeout error.  Uncheck this option to split CREATE queries in chunks.
            This option is checked by default.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Objects</td>
        <td>
            Allow or Ignore objects for "Views," "Stored Procedures," "Functions" and "DEFINER" statements. Typically the defaults
            for these settings should be used. In the event you see an error such as <i class="maroon">"'Access denied; you need (at least one of)
            the SUPER privilege(s) for this operation"</i> then changing the value for each operation should be considered.
        </td>
    </tr>
</table>
<br/><br/>

<!-- ********************************************
URL/PATH TAB
******************************************** -->
<h4>
    <i class="far fa-folder fa-fw"></i>
    URLs &amp; Paths Tab
    <sup class="hlp-pro-lbl">Pro</sup>
</h4>
In the tab "URLs &amp; Paths," you can read the current path of all the various path configurations for the WordPress site.   These are advanced options
that should only be edited if you know the correct path.   These options are editable in the Pro version.
<ul>
    <li>
        WordPress core path
    </li>
    <li>
        WordPress core URL
    </li>
    <li>
        WP-content path
    </li>
    <li>
        WP-content URL
    </li>
    <li>
        Uploads path
    </li>
    <li>
        Uploads URL
    </li>
    <li>
        Plugins path
    </li>
    <li>
        Plugins URL
    </li>
    <li>
        MU-plugins path
    </li>
    <li>
        MU-plugins URL
    </li>
</ul>

These paths and URLs are set automatically by the package installer.  You can set these paths and URLs manually.  If you are changing it,
please make sure you are putting the right path or URL.
<br/><br/><br/>