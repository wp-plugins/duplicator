<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
if (!defined('MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH')) {
    define('MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH', 10);
}

?>
<!-- ============================================
STEP 3
============================================== -->
<?php
$sectionId   = 'section-step-3';
$expandClass = $sectionId == $open_section ? 'open' : 'close';
?>
<section id="<?php echo $sectionId; ?>" class="expandable <?php echo $expandClass; ?>" >
<h2 class="header expand-header">
    Step <span class="step">3</span>: Update Data
    <sup>Advanced Mode</sup>
</h2>
<div class="content" >
    <a class="help-target" name="help-s3"></a>
    <div id="dup-help-step2" class="help-page">

        <!-- SETTINGS-->
        <h4>
            <i class="far fa-folder fa-fw"></i>
            Engine Tab
        </h4>
        This option controls how the database is updated when migrating to the new site.
        <br/><br/>

        <table class="help-opt">
            <tr>
                <th class="col-opt">Option</th>
                <th>Details</th>
            </tr>
            <tr>
                <td colspan="2" class="section">Custom Search and Replace</td>
            </tr>
            <tr>
                <td class="col-opt"><i>Overview</i></td>
                <td>
                    <sup class="hlp-pro-lbl">Pro</sup>
                    Permits adding as many custom search and replace items as needed.  Use extreme caution when using this feature as it can have
                    unintended consequences as it will search the entire database.  It is recommended to only use highly unique items such as full URL or
                    file paths with this option.
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section">Database Scan Options</td>
            </tr>
            <tr>
                <td class="col-opt">Cleanup</td>
                <td>
                    <sup class="hlp-pro-lbl">Pro</sup>
                    The checkbox labeled "Remove schedules &amp; storage endpoints" will empty the Duplicator schedule and storage settings.
                    It is recommended that this remain enabled so that you do not have unwanted schedules and storage options.
                </td>
            </tr>
            <tr>
                <td class="col-opt">Email<br/> Domains</td>
                <td>The domain portion of all email addresses will be updated if this option is enabled.</td>
            </tr>
            <tr>
                <td class="col-opt">Database<br/> Search</td>
                <td>
                    Database full search forces a scan of every single cell in the database.  If it is not checked then only text-based columns are searched
                    which makes the update process much faster.  Use this option if you have issues with data not updating correctly.
                </td>
            </tr>
            <tr>
                <td class="col-opt">Cross<br/>Search</td>
                <td>
                    <sup class="hlp-pro-lbl">Pro</sup>
                    This option enables the searching and replacing of subsite domains and paths that link to each other within a Multisite network.
                    Check this option if hyperlinks of at least one subsite point to another subsite.  Uncheck this option there if there are at least
                    <?php echo MAX_SITES_TO_DEFAULT_ENABLE_CORSS_SEARCH ?>  subsites and no subsites hyperlinking to each other.
                    <br/>
                    <i>
                        Note: Checking this option in this scenario would unnecessarily load your server.  Check this option if you are unsure if
                        you need this option.
                    </i>
                </td>
            </tr>
            <tr>
                <td class="col-opt">Post GUID</td>
                <td>
                    If you're moving a site keep this value checked.
                    For more details see the
                    <a href="https://wordpress.org/support/article/changing-the-site-url/#important-guid-note" target="_blank">
                        notes on GUIDS
                    </a>.
                    Changing values in the posts table GUID column can cause RSS readers to evaluate that the posts are new and may show them in feeds again.
                </td>
            </tr>
            <tr>
                <td class="col-opt">Serialized obj<br/> max size</td>
                <td>
                    Large serialized objects can cause a fatal error when Duplicator attempts to transform them. <br>
                    If a fatal error is generated, lower this limit. <br>
                    If a warning of this type appears in the final report: <br>
                    <code>
                    DATA-REPLACE ERROR: Serialization <br/>
                    ENGINE: serialize data too big to convert; data len: XXX Max size: YYY <br/>
                    DATA: .....
                    </code>
                    and you think that the serialized object is necessary you can increase the limit or <i>set it to 0 to have no limit</i>.
                </td>
            </tr>
        </table>
        <br/><br/><br/>


        <h4>
            <i class="far fa-folder fa-fw"></i>
            Admin Account Tab
        </h4>
        Create a new WordPress administrator or update the existing password of an exiting user.
        <table class="help-opt">
            <tr>
                <th class="col-opt">Option</th>
                <th>Details</th>
            </tr>
            <tr>
                <td colspan="2" class="section">Admin Password Reset</td>
            </tr>
            <tr>
                <td class="col-opt"><i>Overview</i></td>
                <td>
                    Use this feature to change the password of an existing WordPress admin account.   This feature can come in handy if the password
                    was forgotten or if it needs to be changed.
                </td>
            </tr>
            <tr>
                <td colspan="2" class="section">New Admin Account</td>
            </tr>
            <tr>
                <td class="col-opt">Create<br/> New User</td>
                <td>Create a new user account.</td>
            </tr>
            <tr>
                <td class="col-opt">Username</td>
                <td>
                    Username of the user being created. This will be used as the login for the new administrator account.
                    Please note that usernames are not changeable from the within the WordPress UI.  Mandatory Field.
                </td>
            </tr>
            <tr>
                <td class="col-opt">Password</td>
                <td>Password of the user being created.  Must be at least 6 characters long.  Required field when creating a new user.</td>
            </tr>
            <tr>
                <td class="col-opt">Email</td>
                <td>The email of the new user. A mandatory field when creating a new user.</td>
            </tr>
            <tr>
                <td class="col-opt">Nickname</td>
                <td>
                    The nickname of the new user will be created. It is optional to create a new user.
                    If you do not enter a nickname, the username will become the nickname.
                </td>
            </tr>
            <tr>
                <td class="col-opt">First Name</td>
                <td>First name of the user being created. Optional.</td>
            </tr>
            <tr>
                <td class="col-opt">Last Name</td>
                <td>Last name of the user being created. Optional.</td>
            </tr>
        </table>
        <br/><br/><br/>


        <h4>
            <i class="far fa-folder fa-fw"></i>
            Plugins Tab
        </h4>
        This section controls all plugins registered with the site and listed in the Plugin list table. All plugins are grouped as Active or Inactive 
        plugins.   Check all plugins that need to remain active and uncheck all plugins which should not be active.   If running the installer in "Safe Mode"
        then all plugins except needed ones will be disabled.
        <br/><br/><br/>


        <h4>
            <i class="far fa-folder fa-fw"></i>
            WP-Config Tab
        </h4>
        In this section, you can configure different constants in the wp-config.php file.
        <table class="help-opt">
            <tr>
                <th class="col-opt">Option</th>
                <th>Details</th>
            </tr>
            <tr>
                <td class="col-opt">Add/Remove<br/> Switch</td>
                <td>
                    <sup class="hlp-pro-lbl">Pro</sup>
                    Each wp-config value has an associated switch that controls the insertion and removal of the constant.<br>
                    If the switch is deactivated, the constant will be removed from wp-config.php
                </td>
            </tr>
            <tr>
                <td class="col-opt">Constants</td>
                <td>
                    The wp-config tab contains the list of constants that can be modified directly by the installer.<br>
                    See the <a href="https://wordpress.org/support/article/editing-wp-config-php/" target="_blank">WordPress documentation for more information</a>.
                </td>
            </tr>
            <tr>
                <td class="col-opt">Auth Keys</td>
                <td>
                    Generate New Unique Authentication Keys and Salts.
                    Defines: AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, NONCE_KEY, AUTH_SALT, SECURE_AUTH_SALT, LOGGED_IN_SALT, NONCE_SALT
                </td>
            </tr>            
        </table>
        <br/><br/>
    </div>
</div>
</section>