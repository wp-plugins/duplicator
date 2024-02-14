<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<!-- ============================================
STEP 2
============================================== -->
<?php
$sectionId   = 'section-step-2';
$expandClass = $sectionId == $open_section ? 'open' : 'close';
?>
<section id="<?php echo $sectionId; ?>" class="expandable <?php echo $expandClass; ?>" >
    <h2 class="header expand-header">
        Step <span class="step">2</span>: Install Database
        <sup>Advanced Mode</sup>
    </h2>
    <div class="content" >
        <div id="dup-help-step1" class="help-page">            
            <!-- OPTIONS-->
            <h3>Import and Update</h3>
            Step 2 options only show when Advanced mode is enabled.   This step controls which tables will be included in the install and the table
            character set and collation type.  The tables tab shows the original table names with the number of rows and size.
            <br/><br/>
            By default, all tables will be imported and updated during the install process.  If a table is not imported then it cannot be updated.   The update
            process performs a full scan on the imported table finding all old URLs and server paths and updating them with the new paths of the new server.
            The update process will also include all "Search and Replace" options found in Step 3.
            <br/><br/>


            <h4>
                <i class="far fa-folder fa-fw"></i>
                Table Tab
            </h4>
            These are the advanced options for importing and updating tables. All tables are included by default.
            <table class="help-opt">
                <tr>
                    <th class="col-opt">Option</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td class="col-opt">Import</td>
                    <td>
                        Indicates the table will be imported into the database.   This includes the creation of the table and all its data.  Turn off this 
                        switch to prevent the table from being added to the database.  This option is on by default for all tables.  If excluding a table be
                        sure you know the underlying impact of not including it. If you are not sure then it is recommended to keep this value on.
                    </td>
                </tr>
                <tr>
                    <td class="col-opt">Update</td>
                    <td>
                        Indicates the table will be processed for URL replacement as well as custom search and replace.  Turn off this switch to prevent
                        the replacement processing.  Option is on by default.
                    </td>
                </tr>
            </table>
            <br/><br/><br/>

            <h4>
                <i class="far fa-folder fa-fw"></i>
                Advanced Tab
            </h4>
            These are the advanced options for setting the charset and collation types for all tables in the database.
            <table class="help-opt">
                <tr>
                    <th class="col-opt">Option</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td class="col-opt">Charset</td>
                    <td>
                        When the database is populated from the SQL script it will use this value as part of its connection.  Only change this value if you
                        know what your database character set should be.   Visit the
                        <a href="https://dev.mysql.com/doc/refman/8.0/en/charset.html" target="_blank">Character Sets, Collations, Unicode manual</a> for
                        more details.
                    </td>
                </tr>
                <tr>
                    <td class="col-opt">Collation</td>
                    <td>
                        When the database is populated from the SQL script it will use this value as part of its connection.  Only change this value if you
                        know what your database collation set should be.
                    </td>
                </tr>
            </table>
            <br/><br/>
        </div>
    </div>
</section>