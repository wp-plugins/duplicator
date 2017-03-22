<!-- =========================================
HELP FORM -->
<div id="main-help">
<div style="text-align:center; font-size:18px">Also see the <a href="https://snapcreek.com/support/docs/" target="_blank">online resources</a></div>


<!-- ============================================
STEP 1
============================================== -->
<h3>Step <span class="step">1</span> of 4: Extract Archive</h3>
<div id="dup-help-scanner" class="help-page">
    Step 1 shows the installers system requirements and notices.  All requirements must pass in order to proceed to Step 2.  Each requirement will show
    a Pass/Fail status.  Notices on the other hand are <u>not</u> required in order to continue with the install.  Notices are simply checks that will help you identify any
    possible issues that might occur.
    <br/><br/>

    Notices will show with a status of Good/Warn.  If you do see a Warn status its best to just continue with the install process and should
    an issue arise go back and see if taking care of the notice will solve the issue.   Every requirement and notice will have full details for how to resolve the issue.
    <br/><br/>

    <!-- ADVANCED OPTS -->
    <fieldset>
        <legend><b>Advanced Options</b></legend>

        <b>GENERAL</b>
        <br/><br/>

        <b>Manual Package Extraction:</b><br/>
        This allows you to manually extract the zip archive on your own. This can be useful if your system does not have the ZipArchive support enabled or your
        having issues with extracting the package.
        <br/><br/>

        <b>Logging:</b><br/>
        The level of detail that will be sent to the log file (installer-log.txt).  The recommend setting for most installs should be 'Light'.  Note if you use Debug the amount
        of data written can be very large.  Debug is only recommended for support.
        <br/><br/>

        <b>File Timestamp:</b><br/>
        Choose 'Current' to set each file and directory to the current date-time on the server where it is being extracted.  Choose 'Original' to retain the file date-time
        in the archive file. This option will not be applied if the 'Manual package extraction' option is checked.
        <br/><br/>

    </fieldset>
</div>


<!-- ============================================
STEP 2
============================================== -->
<h3>Step <span class="step">2</span> of 4: Install Database</h3>
<div id="dup-help-step1" class="help-page">

    <b>Basic/cPanel:</b><br/>
    There are currently two options you can use to perform the database setup.  The "Basic" option requires knowledge about the existing server and on most hosts
    will require that the database be setup ahead of time.  The cPanel <i>(Pro Only)</i> option is for hosts that support <a href="http://cpanel.com/" target="_blank">cPanel Software</a>.
    This option will automatically show you the existing databases and users on your cPanel server and allow you to create new databases directly
    from the installer.
    <br/><br/>

    <!-- DATABASE SERVER -->
    <fieldset>
        <legend><b>Database Setup</b></legend>
        <b>Action:</b>
        <ul>
            <li>
                <b>Create New Database:</b> Will attempt to create a new database if it does not exist.  When using the 'Basic' option this option will not work on many hosting
                providers as the ability to create new databases is normally locked down.  If the database does not exist then you will need to login to your control panel
                and create the database.  If your host supports 'cPanel' then you can use this option to create a new database after logging in via your cPanel account <i>(Pro Only)</i>.
                <br/><br/>
            </li>
            <li>
                <b>Connect and Delete Any Existing Data:</b> This options will DELETE all tables in the database you are connecting to.  Please make sure you have
                backups of all your data before using an portion of the installer, as this option WILL remove all data.  <br/><br/>
            </li>
        </ul>
        <br/>

        <b>Host:</b><br/>
        The name of the host server that the database resides on.  Many times this will be 'localhost', however each hosting provider will have it's own naming convention
        please check with your server administrator or host to valid for sure the name needed.  To add a port number just append it to the host i.e. 'localhost:3306'.
        <br/><br/>

        <b>Database:</b><br/>
        The name of the database to which this installation will connect and install the new tables and data into.
        <br/><br/>

        <b>User:</b><br/>
        The name of a MySQL database server user. This is special account that has privileges to access a database and can read from or write to that database.
        <i>This is <b>not</b> the same thing as your WordPress administrator account</i>.
        <br/><br/>

        <b>Password:</b><br/>
        The password of the MySQL database server user.
        <br/><br/>

        <b>Test Connection:</b><br/>
        The test connection button will help validate if the connection parameters are correct for this server.  There are three separate validation parameters:
        <ul>
            <li><b>Host:</b> Returns a status to indicate if the server host name is a valid host name <br/><br/></li>
            <li><b>Database:</b> Returns a status to indicate if the database name is a valid <br/><br/></li>
            <li><b>Version:</b> Shows the difference in database engine version numbers. If the package was created on a newer database version than where its trying to
            be installed then you can run into issues.  Its best to make sure the server where the installer is running has the same or higher version number than
            where it was built.</li>
        </ul>
        <br/>

        <div class="help">
            <b>Common Database Connection Issues:</b><br/>
            - Double check case sensitive values 'User', 'Password' &amp; the 'Database Name' <br/>
            - Validate the database and database user exist on this server <br/>
            - Check if the database user has the correct permission levels to this database <br/>
            - The host 'localhost' may not work on all hosting providers <br/>
            - Contact your hosting provider for the exact required parameters <br/>
            - Visit the online resources 'Common FAQ page' <br/>
        </div>

    </fieldset>

    <!-- ADVANCED OPTS -->
    <fieldset>
        <legend><b>Advanced Options</b></legend>

		<b>Spacing:</b><br/>
        The option to fix non-breaking space characters can be included in the install when choosing this option. This process will remove utf8 characters represented
		as 'xC2' 'xA0' and replace with a uniform space.  Use this option if you find strange question marks in you posts.
        <br/><br/>

		<b>Mode:</b><br/>
        The MySQL mode option will allow you to set the mode for this session.  It is very useful when running into conversion issues.  For a full overview please see the
		<a href="https://dev.mysql.com/doc/refman/5.7/en/sql-mode.html" target="_blank">MySQL mode documentation</a> specific to your version.
        <br/><br/>

        <b>MySQL Charset &amp; MySQL Collation:</b><br/>
        When the database is populated from the SQL script it will use this value as part of its connection.  Only change this value if you know what your databases character
        set should be.
        <br/><br/>

    </fieldset>
</div>


<!-- ============================================
STEP 3
============================================== -->
<h3>Step <span class="step">3</span> of 4: Data Replacement</h3>
<div id="dup-help-step2" class="help-page">

    <!-- SETTINGS-->
    <fieldset>
        <legend><b>New Settings</b></legend>
        These are the new values (URL, Path and Title) you can update for the new location at which your site will be installed at.
        <br/><br/>
    </fieldset>


    <!-- ADVANCED OPTS -->
    <fieldset>
        <legend><b>Advanced Options</b></legend>

        <b>New Admin Account</b>
        <div style='padding: 3px 0 0 15px'>
            <b>Username:</b><br/>
            A new WordPress username to create.  This will create a new WordPress administrator account.  Please note that usernames are not changeable from the within the UI.
            <br/><br/>

            <b>Password:</b><br/>
            The new password for the new user.
        </div>
        <br/><br/>

		<b>WP-Config File</b>
		<div style='padding: 3px 0 0 15px'>
			<b>Config SSL:</b><br/>
			Turn off SSL support for WordPress. This sets FORCE_SSL_ADMIN in your wp-config file to false if true, otherwise it will create the setting if not set.  The "Enforce on Login"
			will turn off SSL support for WordPress Logins. This sets FORCE_SSL_LOGIN in your wp-config file to false if true, otherwise it will create the setting if not set.
			<br/><br/>

			<b>Config Cache:</b><br/>
			Turn off Cache support for WordPress. This sets WP_CACHE in your wp-config file to false if true, otherwise it will create the setting if not set.  The "Keep Home Path"
			sets WPCACHEHOME in your wp-config file to nothing if true, otherwise nothing is changed.
       </div>
		<br/><br/>

        <b>Scan Options:</b>
		<div style='padding: 3px 0 0 15px'>
			The Old URL and Old Path settings are the original values that the package was created with.  These values should not be changed, unless you know the underlying reasons.
			<br/><br/>

			<b>Site URL:</b><br/>
			For details see WordPress <a href="http://codex.wordpress.org/Changing_The_Site_URL" target="_blank">Site URL</a> &amp; <a href="http://codex.wordpress.org/Giving_WordPress_Its_Own_Directory" target="_blank">Alternate Directory</a>.  If you're not sure about this value then leave it the same as the new settings URL.
			<br/><br/>

			<b>Scan Tables:</b><br/>
			Select the tables to be updated. This process will update all of the 'Old Settings' with the 'New Settings'. Hold down the 'ctrl key' to select/deselect multiple.
			<br/><br/>

			<b>Activate Plugins:</b><br/>
			These plug-ins are the plug-ins that were activated when the package was created and represent the plug-ins that will be activated after the install.
			<br/><br/>

			<b>Post GUID:</b><br/>
			If your moving a site keep this value checked. For more details see the <a href="http://codex.wordpress.org/Changing_The_Site_URL#Important_GUID_Note" target="_blank">notes on GUIDS</a>.	Changing values in the posts table GUID column can change RSS readers to evaluate that the posts are new and may show them in feeds again.
			<br/><br/>

			<b>Full Search:</b><br/>
			Full search forces a scan of every single cell in the database. If it is not checked then only text based columns are searched which makes the update process much faster.
			Use this option if you have issues with data not updating correctly.
			<br/><br/>
		</div>
    </fieldset>

</div>


<!-- ============================================
STEP 4
============================================== -->
<h3>Step <span class="step">4</span> of 4: Test Site</h3>
<div id="dup-help-step3" class="help-page">
    <fieldset>
        <legend><b>Final Steps</b></legend>

        <b>Review Install Report</b><br/>
        The install report is designed to give you a synopsis of the possible errors and warnings that may exist after the installation is completed.

        <b>Test Site</b><br/>
        After the install is complete run through your entire site and test all pages and posts.
        <br/><br/>

        <b>Security Cleanup</b><br/>
        When you're completed with the installation please delete all installation files.  Leaving these files on your server can impose a security risk!
        <br/><br/>

    </fieldset>
</div>


<h3>Troubleshooting Tips</h3>
<div id="troubleshoot" class="help-page">
    <fieldset>
        <legend><b>Quick Overview</b></legend>

        <div style="padding: 0px 10px 10px 10px;">
            <b>Common Quick Fix Issues:</b>
            <ul>
                <li>Use an <a href='https://snapcreek.com/wordpress-hosting/' target='_blank'>approved hosting provider</a></li>
                <li>Validate directory and file permissions (see below)</li>
                <li>Validate web server configuration file (see below)</li>
                <li>Clear your browsers cache</li>
                <li>Deactivate and reactivate all plugins</li>
                <li>Resave a plugins settings if it reports errors</li>
                <li>Make sure your root directory is empty</li>
            </ul>

            <b>Permissions:</b><br/>
            Not all operating systems are alike.  Therefore, when you move a package (zip file) from one location to another the file and directory permissions may not always stick.  If this is the case then check your WordPress directories and make sure it's permissions are set to 755. For files make sure the permissions are set to 644 (this does not apply to windows servers).   Also pay attention to the owner/group attributes.  For a full overview of the correct file changes see the <a href='http://codex.wordpress.org/Hardening_WordPress#File_permissions' target='_blank'>WordPress permissions codex</a>
            <br/><br/>

            <b>Web server configuration files:</b><br/>
            For Apache web server the root .htaccess file was copied to .htaccess.orig. A new stripped down .htaccess file was created to help simplify access issues.  For IIS web server the web.config file was copied to web.config.orig, however no new web.config file was created.  If you have not altered this file manually then resaving your permalinks and resaving your plugins should resolve most all changes that were made to the root web configuration file.   If your still experiencing issues then open the .orig file and do a compare to see what changes need to be made. <br/><br/><b>Plugin Notes:</b><br/> It's impossible to know how all 3rd party plugins function.  The Duplicator attempts to fix the new install URL for settings stored in the WordPress options table.   Please validate that all plugins retained there settings after installing.   If you experience issues try to bulk deactivate all plugins then bulk reactivate them on your new duplicated site. If you run into issues were a plugin does not retain its data then try to resave the plugins settings.
            <br/><br/>

             <b>Cache Systems:</b><br/>
             Any type of cache system such as Super Cache, W3 Cache, etc. should be emptied before you create a package.  Another alternative is to include the cache directory in the directory exclusion path list found in the options dialog. Including a directory such as \pathtowordpress\wp-content\w3tc\ (the w3 Total Cache directory) will exclude this directory from being packaged. In is highly recommended to always perform a cache empty when you first fire up your new site even if you excluded your cache directory.
             <br/><br/>

             <b>Trying Again:</b><br/>
             If you need to retry and reinstall this package you can easily run the process again by deleting all files except the installer and package file and then browse to the installer again.
             <br/><br/>

             <b>Additional Notes:</b><br/>
             If you have made changes to your PHP files directly this might have an impact on your duplicated site.  Be sure all changes made will correspond to the sites new location.
             Only the package (zip file) and the installer (php file) should be in the directory where you are installing the site.  Please read through our knowledge base before submitting any issues.
             If you have a large log file that needs evaluated please email the file, or attach it to a help ticket.
             <br/><br/>

        </div>
    </fieldset>
</div>

<div style="text-align:center">For additional help please visit the <a href="https://snapcreek.com/support/docs/" target="_blank">online resources</a></div>

<br/><br/>
</div>
<!-- END OF VIEW HELP -->