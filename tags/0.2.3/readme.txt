=== Plugin Name ===
Contributors: Cory Lamle
Donate link: www.lifeinthegrid.com/partner
Tags: backup, restore, move, migrate, localhost, synchronize, duplicate, clone, automate, niche  
Requires at least: 3.1
Tested up to: 3.2
Stable tag: 0.2.3 
License: GPLv2

Duplicate, clone, backup and transfer an entire site from one location to another in 3 easy steps.


== Description ==

The Duplicator plugin is designed to give Wordpress Administrators the ability to migrate a site from one location to another location in 3 easy steps create, download, install.  The plugin also serves as a simple backup utility.  

This tool is great for pulling a production site down onto your local machine for testing and validation. It also works great for developing locally and then pushing up to a production server.  No need to change all your settings and re-run import/export scripts.

If you need to clone, duplicate or template a WordPress application, then this plugin is ideal for for mass site generation.  Niche site generation is a breeze with the Duplicator and moving your WordPress site has never been easier.  Stay tuned for other cool features to help automate your Wordpress management.

NOTE: This project is currently in Beta, the underlying logic to perform all these migration tasks is quite involved. It's impossible to know how each system is setup, this is why your feedback is very important to us.  Thanks for helping us to make WordPress the best blogging platform in the world.

A quick overview:
http://www.youtube.com/watch?v=nyHNV6D2w2c

For complete details see: 
[lifeinthegrid.com](http://lifeinthegrid.com/) 


== Installation ==

1. Upload `duplicator` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Click on the Tools menu then the Duplicator link
4. Open the options dialog button in the right hand corner and click on the systems tab to check your servers compatibility.
5. Check out the help by clicking the help icon and create your first package.

The Duplicator requires php 5.3 and WordPress 3.1 or higher.

== Frequently Asked Questions ==

= I'm having trouble creating the package what should I do? =

Turn on the logging (Options -> Package -> Logging). This will give you details as to what is happening.


= When I reinstall my package I get errors on the site. =

Be sure to read through all the post install notes carefully and follow all instructions.


= Where can I get more information and support for this plugin? =

Visit the [Duplicator Page](http://lifeinthegrid.com/duplicator) at lifeinthegrid.com


= How can I test this in a non production environment? =

Put WordPress on [your computer](http://lifeinthegrid.com/xampp). See video below.

http://www.youtube.com/watch?v=-hF7FbTQIkk


== Screenshots ==
 
1. The main interface used to create and manage packages
2. The options dialog is used to configure and setup the Duplicator
 

== Changelog ==

= 0.2.3  =
<li>Added: Additional error checking</li>
<li>Fixed: Logging failed attempt to create database entry record</li>
<li>Fixed: Compression check for ZipArchive class</li>
<li>Fixed: Notices Undefined variable check in installer.php</li>
<li>Fixed: Issue on some system where wp-snapshots/index.php was created as a file</li>
<li>Fixed: Changed jQuery calls from $ to jQuery</li>

= 0.2.2 Beta =
<li>Added: "Disable SSL Admin" if enabled. New installer option</li>
<li>Fixed: Nonbreaking space character validation for Linux to Windows</li>

= 0.2.1 Beta =
<li>Added: New output for complete log on installer</li>
<li>Added: Improved Database Character Validation</li>
<li>Added: Include version # in the logging output</li>

= 0.2.0 Beta =
<li>Added: Improved error handling around RecursiveDirectoryIterator</li>
<li>Added: Support for SQL character encode versions</li>
<li>Added: 'Perform Character Validation' attempts to replace invalid characters</li>
<li>Fixed: SSL Support for downloading installer and package</li>
<li>Fixed: Retain Permissions with 755 on Directories and 644 on files</li>
<li>Fixed: Better regular expressions for wp-config replacement parameters</li>
<li>Fixed: Better notice message on install screen</li>

= 0.1.0 Beta =
First Beta release of the Duplicator Plugin.
Many thanks go out to Gaurav Aggarwal for starting the Backup and Move Plugin.
This project is a fork of the original  Backup and Move Plugin authored by Gaurav Aggarwal
See http://www.logiclord.com/backup-and-move/ for more details.



== Upgrade Notice ==

Please use our ticketing system when submitting your logs.  Do not post to the forums.













