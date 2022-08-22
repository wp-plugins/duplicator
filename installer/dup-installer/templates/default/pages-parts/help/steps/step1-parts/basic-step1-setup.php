<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Utils;
?>

<!-- ===================================
OVERVIEW  -->
<h3>Overview</h3>
The overview section allows users to identify the install type and other details about the archive file.  The installer has two operating modes: Basic and 
Advanced. Basic mode is the easiest and fastest install mode  and covers most setup types.   The Advanced mode allows users to implement and apply
additional settings/features to the install process.   
<br/><br/>
Note: Duplicator Lite supports only single WordPress sites, while <a href="<?php echo Utils::getCampainUrl("lite_section_help") ?>" target="_blank">Duplicator Pro</a> supports single and multisite websites.
<br/><br/>

<h4>
    <i class="far fa-folder fa-fw"></i>
    Installation Tab
</h4>
This section will give an overview of the various install modes, methods and types that are currently being used.
<br/><br/>

<b><i class="fas fa-angle-double-right"></i> Mode:</b>
<ul>
    <li>
        <b>Basic:</b>
        This is a simple two-step mode with all options set to the defaults.  This is the default mode.
    </li>
    <li>
        <b>Advanced:</b>
        This four-step mode allows for higher levels of customization with various detail settings.<br/>
        <small>This is the only mode that Duplicator supported before version 1.5</small>
    </li>
</ul>


<b><i class="fas fa-angle-double-right"></i> Method:</b>
<ul>
    <li>
        <b>Standard Install</b>
        <ul>
            <li>Includes both files and tables in the archive file.</li>
            <li>The files and tables are determined by filters enabled during the archive build process.</li>
            <li>Method is enabled when there is no existing WordPress site present in the current install directory.</li>
        </ul>
    </li>
    <li>
        <b>Standard Install - Database Only</b>
        <ul>
            <li>Includes only database tables in the archive file.</li>
            <li>The tables are determined by filters enabled during the archive build process.</li>
            <li>
                Method is enabled when the archive only contains the database and there is no existing WordPress site present in the
                current install directory.
            </li>
        </ul>
    </li>
    <li>
        <b>Overwrite Install</b><br/>
        <ul>
            <li>Includes both files and tables in the archive file.</li>
            <li>The files and tables are determined by filters enabled during the archive build process.</li>
            <li>Method is enabled when the installer detects an existing WordPress site is present.</li>
        </ul>
    </li>
    <li>
        <b>Overwrite Install - Database Only</b><br/>
        <ul>
            <li>Includes only database tables in the archive file.</li>
            <li>The tables are determined by filters enabled during the archive build process.</li>
            <li>Method is enabled when the installer detects an existing WordPress site is present.</li>
        </ul>
    </li>
</ul>

<b><i class="fas fa-angle-double-right"></i> Status:</b>
<ul>
    <li>
        <b>Standard Site Setup:</b>
        This will perform the installation of a single WordPress site based on the associated method.
    </li>
    <li>
        <b>Restore Site Backup:</b>
        The restore backup status restores the original site by not performing any processing on the database or tables to ensure an exact copy
        of the original site, exists.
    </li>
</ul>

<b><i class="fas fa-angle-double-right"></i> Install Type:</b>
<ul>
    <li><b>Full/Restore:</b> This is the default install type.</li>
    <li><sup class="hlp-pro-lbl">Pro</sup> <b>Convert:</b> This is a Multisite feature used to convert a network subsite to a standalone site.</li>
    <li><sup class="hlp-pro-lbl">Pro</sup> <b>Import:</b> This is a Multisite feature used to import a subsite into a Multisite network.</li>
</ul>
<small>* The Restore, Convert and Import types are only visible when the installer detects that it can perform the action.</small>
<br/><br/>

<h4>
    <i class="far fa-folder fa-fw"></i>
    Archive Tab
</h4>
The archive tab shows various details about the archive file and site details related to the site that was archived. With Duplicator Lite the following
install modes are currently supported:
<ul>
    <li>
        <b><a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-040-q" target="_blank">Classic Install:</a></b>
        With this mode users can install to an empty directory like a new WordPress install does.

    </li>
    <li>
        <b><a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-043-q" target="_blank">Overwrite Install:</a></b>
        This mode allows users to quickly overwrite an existing WordPress site in a few clicks.
    </li>
    <li>
        <sup class="hlp-pro-lbl">Pro</sup>
        <b><a href="https://snapcreek.com/duplicator/docs/quick-start/#quick-045-q" target="_blank">Import Install:</a></b> 
        Drag and drop or use a URL for super-fast installs.  This Pro-only feature will import Lite archives.
        <ul>
            <li><b>Import File:</b>  Drag and drop an existing Duplicator Lite or Pro archive and quickly replace the existing WordPress site</li>
            <li><b>Import Link:</b> Provide a link to an existing Duplicator Lite or Pro archive and quickly replace the existing WordPress site.</li>
        </ul>
    </li>
</ul>
<br/><br/><br/>

<!-- ===================================
SETUP  -->
<h3>Setup</h3>
<h4>
   <i class="far fa-folder fa-fw"></i>
   Database Tab
</h4>
The database connection inputs allow you to connect to an existing database or create a new database along with the other actions below.  There are currently
two options you can use to perform the database setup:
<ol>
    <li>
        <b>Default:</b> This option requires knowledge about the existing server, and requires the database be created ahead of time on most hosts.
    </li>
    <li>
        <sup class="hlp-pro-lbl">Pro</sup>
        <b>cPanel:</b> 
        The cPanel option is for hosts that support <a href="https://cpanel.net">cPanel software</a>. This option will automatically show you the existing
        databases and users in your cPanel server and allow you to create new databases directly from the installer.
    </li>
</ol>


<table class="help-opt">
    <tr>
        <th class="col-opt">Option</th>
        <th>Details</th>
    </tr>
    <tr>
        <td colspan="2" class="section">
            Default
        </td>
    </tr>
    <tr>
        <td class="col-opt">Action</td>
        <td>
            <b>Empty Database</b><br/>
            DELETE all tables in the database you are connecting to.  Please make sure you have backups of all your data before using this part of the
            installer, as this option WILL remove all data.
            <br/><br/>

            <b>Backup Existing Tables</b><br/>
            Create a backup of all existing tables by performing a RENAME of all tables in the database with a prefix of
            "<?php echo $GLOBALS['DB_RENAME_PREFIX'] ?>".   This makes room for the new tables to be created.
            <br/><br/>

            <b>Skip Database Extraction</b><br/>
            This option requires that you manually run your own SQL import to an existing database before running the installer. When this action is selected the
            dup-database__[hash].sql file found inside the dup-installer folder of the archive.zip file will NOT be processed. The database you're connecting to
            should already be a valid WordPress installed database.  This option is viable when you need to perform custom SQL work or advanced installs.
            <br/><br/>
            
            <b>Create New Database</b><br/>
            Will attempt to create a new database if it does not exist.    This option will not work on most hosting providers (due to host restrictions) but
            will work on most local systems.  If the database does not exist then you will need to log in to your host's database management system and create
            the database.  <i>If your host supports cPanel then you can use this option to create a new database after logging in via your cPanel account.</i>
            <br/><br/>
            
            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Overwrite Existing Tables</b><br/>
            Overwrite only the tables that are extracted. This option is useful if you want to install WordPress in a database containing other WordPress
            installations or applications. <i>Note: When performing an install alongside another installation be sure to change the prefix since only those
            tables with the same prefix will be overwritten while tables of a different prefix will be retained. </i>
            <br/><br/>
        </td>
    </tr>
    <tr>
        <td class="col-opt">Host</td>
        <td>The name of the host server that the database resides on.
            Most times this will be "localhost," however each hosting provider will have its own naming
            convention so please check with your server administrator or host to determine the proper host name.
            To add a port number, just append it to the host i.e. "localhost:3306."</td>
    </tr>
    <tr>
        <td class="col-opt">Database</td>
        <td>
            The name of the database to which this installation will connect and install the new tables and data into.
            Some hosts will require a prefix while others do not.
            Be sure to know exactly how your host requires the database name to be entered.
        </td>
    </tr>
    <tr>
        <td class="col-opt">User</td>
        <td>The name of MySQL/MariaDB database server user. This is a special account that has privileges to access a database and can read from or write to that database.
            <i>This is <b>not</b> the WordPress administrator account</i>.</td>
    </tr>
    <tr>
        <td class="col-opt">Password</td>
        <td>The password of the MySQL/MariaDB database server user.</td>
    </tr>
    <tr>
        <td colspan="2" class="section">
            cPanel <sup class="hlp-pro-lbl">Pro</sup>
        </td>
    </tr>
    <tr>
        <td class="col-opt">Host</td>
        <td><sup class="hlp-pro-lbl">Pro</sup>
            This should be the primary domain account URL that is associated with your host.   Most hosts will require you to register a primary domain name.
            This should be the URL that you place in the host field.  For example if your primary domain name is "mysite.com" then you would enter in
            "https://mysite.com:2083."  The port 2083 is the common port number that cPanel works on.   If you do not know your primary domain name please
            contact your  hosting provider or server administrator.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Username</td>
        <td>
            <sup class="hlp-pro-lbl">Pro</sup>
            The cPanel username used to login to your cPanel account.  <i>This is <b>not</b> the same thing as your WordPress administrator account</i>.
            If you're unsure of this name please contact your hosting provider or server administrator.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Password</td>
        <td>
            <sup class="hlp-pro-lbl">Pro</sup>
            The password of the cPanel user.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Troubleshoot</td>
        <td>
            <sup class="hlp-pro-lbl">Pro</sup>
            <b>Common cPanel Connection Issues:</b><br/>
            - Your host does not use <a href="http://cpanel.com" target="_blank">cPanel software</a>. <br/>
            - Your host has disabled cPanel API access. <br/>
            - Your host has configured cPanel to work differently (please contact your host). <br/>
            - View a list of valid cPanel <a href='https://snapcreek.com/wordpress-hosting' target='_blank'>Supported Hosts</a>.
        </td>
    </tr>
</table>
<br/><br/>


<h4>
    <i class="far fa-folder fa-fw"></i>
    Settings Tab
</h4>
The settings options allow users to change the "Site Title," "Site URL" and "Site Path".   By default and in most cases the "Site URL" and "Site Path"
should not need to be changed.   In Basic mode these values are read-only.   In order to edit them switch to the "Advanced" mode found in the upper right
corner of the installer wizard.
<table class="help-opt">
    <tr>
        <th class="col-opt">Option</th>
        <th>Details</th>
    </tr>
    <tr>
        <td class="col-opt">Site Title</td>
        <td>
            The name of the WordPress website.  On most websites this value will be the name used to bookmark the site or the name of the browser tab
            used to view the page.
        </td>
    </tr>    
    <tr>
        <td class="col-opt">Site URL</td>
        <td>
            The New Site URL input field is auto-filled with the installation site URL. By default you have no need to change it.  For details see WordPress
            <a href="https://wordpress.org/support/article/changing-the-site-url/" target="_blank">Site URL</a> &amp;
            <a href="https://wordpress.org/support/article/giving-wordpress-its-own-directory/" target="_blank">Alternate Directory</a>.
            This value should only be changed if you know what you want the value to be. The old URL value is listed as a read-only and will show the URL
            of the site when the package was created.  These values should not be changed, unless you know the underlying reasons.
        </td>
    </tr>
    <tr>
        <td class="col-opt">Site Path</td>
        <td>
            This is the physical server path where your WordPress site resides.  For hosted server check with your hosting provider for the correct
            path location.  These values should not be changed, unless you know the underlying reasons.
        </td>
    </tr>    
</table>
<br/><br/><br/>