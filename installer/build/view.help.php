<!-- =========================================
HELP FORM -->
<div id="dup-main-help">
<div style="text-align:center">For in-depth help please see the <a href="http://lifeinthegrid.com/duplicator-docs" target="_blank">online resources</a></div>

<h3>Step 1 - Deploy</h3>
<div id="dup-help-step1" class="dup-help-page">
	<!-- MYSQL SERVER -->
	<fieldset>
		<legend><b>MySQL Server</b></legend>

		<b>Action:</b><br/>
		'Create New' will attempt to create a new database if it does not exist.  This option will not work on many hosting providers.  If the database does not exist then you will need to login to your control panel and create the database.  If 'Remove All Tables' is checked this will DELETE all tables in the database you are connecting to as the Duplicator requires a blank database.  Please make sure you have backups of all your data before using an portion of the installer, as this option WILL remove all data.  Please contact your server administrator for more details.
		<br/><br/>

		<b>Host:</b><br/>
		The name of the host server that the database resides on.  Many times this will be localhost, however each hosting provider will have it's own naming convention please check with your server administrator.
		<br/><br/>

		<b>User:</b><br/>
		The name of a MySQL database server user. This is special account that has privileges to access a database and can read from or write to that database.  <i style='font-size:11px'>This is <b>not</b> the same thing as your WordPress administrator account</i> 
		<br/><br/>

		<b>Password:</b><br/>
		The password of the MySQL database server user.
		<br/><br/>

		<b>Name:</b><br/>
		The name of the database to which this installation will connect and install the new tables onto.
		<br/><br/>


	</fieldset>				

	<!-- ADVANCED OPTS -->
	<fieldset>
		<legend><b>Advanced Options</b></legend>
		<b>Manual Package Extraction:</b><br/>
		This allows you to manually extract the zip archive on your own. This can be useful if your system does not have the ZipArchive support enabled.
		<br/><br/>		

		<b>Enforce SSL on Admin:</b><br/>
		Turn off SSL support for WordPress. This sets FORCE_SSL_ADMIN in your wp-config file to false if true, otherwise it will create the setting if not set.
		<br/><br/>	

		<b>Enforce SSL on Login:</b><br/>
		Turn off SSL support for WordPress Logins. This sets FORCE_SSL_LOGIN in your wp-config file to false if true, otherwise it will create the setting if not set.
		<br/><br/>			

		<b>Keep Cache Enabled:</b><br/>
		Turn off Cache support for WordPress. This sets WP_CACHE in your wp-config file to false if true, otherwise it will create the setting if not set.
		<br/><br/>	

		<b>Keep Cache Home Path:</b><br/>
		This sets WPCACHEHOME in your wp-config file to nothing if true, otherwise nothing is changed.
		<br/><br/>	

		<b>Fix non-breaking space characters:</b><br/>
		The process will remove utf8 characters represented as 'xC2' 'xA0' and replace with a uniform space.  Use this option if you find strange question marks in you posts
		<br/><br/>	

		<b>MySQL Charset &amp; MySQL Collation:</b><br/>
		When the database is populated from the SQL script it will use this value as part of its connection.  Only change this value if you know what your databases character set should be.
		<br/>				
	</fieldset>			
</div>

<h3>Step 2 - Update</h3>
<div id="dup-help-step1" class="dup-help-page">

	<!-- SETTINGS-->
	<fieldset>
		<legend><b>Settings</b></legend>
		<b>Old Settings:</b><br/>
		The URL and Path settings are the original values that the package was created with.  These values should not be changed.
		<br/><br/>

		<b>New Settings:</b><br/>
		These are the new values (URL, Path and Title) you can update for the new location at which your site will be installed at.
		<br/>		
	</fieldset>

	<!-- NEW ADMIN ACCOUNT-->
	<fieldset>
		<legend><b>New Admin Account</b></legend>
		<b>Username:</b><br/>
		The new username to create.  This will create a new WordPress administrator account.  Please note that usernames are not changeable from the within the UI.
		<br/><br/>

		<b>Password:</b><br/>
		The new password for the user. 
		<br/>		
	</fieldset>

	<!-- ADVANCED OPTS -->
	<fieldset>
		<legend><b>Advanced Options</b></legend>
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
		<br/>		
	</fieldset>

</div>

<h3>Step 3 - Test</h3>
<fieldset>
	<legend><b>Final Steps</b></legend>

	<b>Resave Permalinks</b><br/>
	Re-saving your perma-links will reconfigure your .htaccess file to match the correct path on your server.  This step requires logging back into the WordPress administrator.
	<br/><br/>

	<b>Delete Installer Files</b><br/>
	When you're completed with the installation please delete all installer files.  Leaving these files on your server can impose a security risk!
	<br/><br/>

	<b>Test Entire Site</b><br/>
	After the install is complete run through your entire site and test all pages and posts.
	<br/><br/>

	<b>View Install Report</b><br/>
	The install report is designed to give you a synopsis of the possible errors and warnings that may exist after the installation is completed.
	<br/>
</fieldset>
<br/><br/>
</div>