
<form id="dup-form-opts" method="post" action="?page=duplicator&tab=new2" data-validate="parsley">
<input type="hidden" id="dup-form-opts-action" name="action" value="">
<input type="hidden" id="dup-form-opts-hash" name="package-hash" value="<?php echo $package_hash; ?>">
<div>
	<label for="package-name"><b><?php _e('Name', 'duplicator') ?>:</b> </label>
		<div class="dup-notes-add">
		<button class="button button-small" type="button" onclick="jQuery('#dup-notes-area').toggle()"><i class="fa fa-pencil-square-o"></i> <?php _e('Notes', 'duplicator') ?></button>
	</div>
	<a href="javascript:void(0)" onclick="Duplicator.Pack.ResetName()" title="<?php _e('Create a new default name', 'duplicator') ?>"><i class="fa fa-undo"></i></a> <br/>
	<input id="package-name"  name="package-name" type="text" value="<?php echo $Package->Name ?>" maxlength="40"  data-required="true" data-regexp="^[0-9A-Za-z|_]+$" /> <br/>
	<div id="dup-notes-area">
		<label><b><?php _e('Notes', 'duplicator') ?>:</b></label> <br/>
		<textarea id="package-notes" name="package-notes" maxlength="300" /><?php echo $Package->Notes ?></textarea>
	</div>
</div>
<br/>

<!-- ===================
META-BOX: STORAGE -->
<div class="dup-box">
	<div class="dup-box-title">
		<i class="fa fa-database"></i>&nbsp;<?php  _e("Storage", 'duplicator'); ?> 
		<div class="dup-box-arrow"></div>
	</div>			

	<div class="dup-box-panel" id="dup-pack-storage-panel" style="<?php echo $ui_css_storage ?>">
		<table class="widefat package-tbl">
			<thead>
				<tr>
					<th style='width:275px'><?php _e("Name", 'duplicator'); ?></th>
					<th style='width:100px'><?php _e("Type", 'duplicator'); ?></th>
					<th style="white-space: nowrap"><?php _e("Location", 'duplicator'); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr class="package-row">
					<td><i class="fa fa-server"></i>&nbsp;<?php  _e('Default', 'duplicator');?></td>
					<td><?php _e("Local", 'duplicator'); ?></td>
					<td><?php echo DUPLICATOR_SSDIR_PATH; ?></td>				
				</tr>
				<tr>
					<td colspan="4">
						<div style="font-size:12px; font-style:italic;"> 
							<img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/dropbox-64.png" style='height:16px; width:16px; vertical-align: text-top'  /> 
							<img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/google_drive_64px.png" style='height:16px; width:16px; vertical-align: text-top'  /> 
							<img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/ftp-64.png" style='height:16px; width:16px; vertical-align: text-top'  /> 
							
							<?php echo sprintf(__('%1$s, %2$s, %3$s and other storage options available in', 'duplicator'), 'Dropbox', 'Google Drive', 'FTP'); ?>
                            <a href="http://snapcreek.com/duplicator/?free-storage" target="_blank">Duplicator Pro</a> 
                        </div>                            
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div><br/>
<!-- end meta-box storage  -->

<!-- META-BOX: ARCHIVE -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fa fa-file-archive-o"></i> <?php _e('Archive', 'duplicator') ?> &nbsp;
        <span style="font-size:13px">
            <span id="dup-archive-filter-file" title="<?php _e('File filter enabled', 'duplicator') ?>"><i class="fa fa-files-o"></i> <i class="fa fa-filter"></i> &nbsp;&nbsp;</span> 
            <span id="dup-archive-filter-db" title="<?php _e('Database filter enabled', 'duplicator') ?>"><i class="fa fa-table"></i> <i class="fa fa-filter"></i></span>	
        </span>
        <div class="dup-box-arrow"></div>
    </div>		
    <div class="dup-box-panel" id="dup-pack-archive-panel" style="<?php echo $ui_css_archive ?>">
        <input type="hidden" name="archive-format" value="ZIP" />
        <!-- NESTED TABS -->
        <div class="categorydiv" id="dup-pack-opts-tabs">
            <ul class="category-tabs">
                <li class="tabs"><a href="javascript:void(0)" onclick="Duplicator.Pack.ToggleOptTabs(1, this)"><?php _e('Files', 'duplicator') ?></a></li>
                <li><a href="javascript:void(0)"onclick="Duplicator.Pack.ToggleOptTabs(2, this)"><?php _e('Database', 'duplicator') ?></a></li>
            </ul>

            <!-- TAB1: PACKAGE -->
            <div class="tabs-panel" id="dup-pack-opts-tabs-panel-1">
                <!-- FILTERS -->
                <?php
                $uploads = wp_upload_dir();
                $upload_dir = DUP_Util::SafePath($uploads['basedir']);
                ?>
                <div class="dup-enable-filters">
                    <input type="checkbox" id="filter-on" name="filter-on" onclick="Duplicator.Pack.ToggleFileFilters()" <?php echo ($Package->Archive->FilterOn) ? "checked='checked'" : ""; ?> />	
                    <label for="filter-on"><?php _e("Enable File Filters", 'duplicator') ?></label>
                </div>

                <div id="dup-file-filter-items">
                    <label for="filter-dirs" title="<?php _e("Separate all filters by semicolon", 'duplicator'); ?>"><?php _e("Directories", 'duplicator') ?>: </label>
                    <div class='dup-quick-links'>
                        <a href="javascript:void(0)" onclick="Duplicator.Pack.AddExcludePath('<?php echo rtrim(DUPLICATOR_WPROOTPATH, '/'); ?>')">[<?php _e("root path", 'duplicator') ?>]</a>
                        <a href="javascript:void(0)" onclick="Duplicator.Pack.AddExcludePath('<?php echo rtrim($upload_dir, '/'); ?>')">[<?php _e("wp-uploads", 'duplicator') ?>]</a>
                        <a href="javascript:void(0)" onclick="Duplicator.Pack.AddExcludePath('<?php echo DUP_Util::SafePath(WP_CONTENT_DIR); ?>/cache')">[<?php _e("cache", 'duplicator') ?>]</a>
                        <a href="javascript:void(0)" onclick="jQuery('#filter-dirs').val('')"><?php _e("(clear)", 'duplicator') ?></a>
                    </div>
                    <textarea name="filter-dirs" id="filter-dirs" placeholder="/full_path/exclude_path1;/full_path/exclude_path2;"><?php echo str_replace(";", ";\n", esc_textarea($Package->Archive->FilterDirs)) ?></textarea><br/>
                    <label class="no-select" title="<?php _e("Separate all filters by semicolon", 'duplicator'); ?>"><?php _e("File extensions", 'duplicator') ?>:</label>
                    <div class='dup-quick-links'>
                        <a href="javascript:void(0)" onclick="Duplicator.Pack.AddExcludeExts('avi;mov;mp4;mpeg;mpg;swf;wmv;aac;m3u;mp3;mpa;wav;wma')">[<?php _e("media", 'duplicator') ?>]</a>
                        <a href="javascript:void(0)" onclick="Duplicator.Pack.AddExcludeExts('zip;rar;tar;gz;bz2;7z')">[<?php _e("archive", 'duplicator') ?>]</a>
                        <a href="javascript:void(0)" onclick="jQuery('#filter-exts').val('')"><?php _e("(clear)", 'duplicator') ?></a>
                    </div>
                    <textarea name="filter-exts" id="filter-exts" placeholder="ext1;ext2;ext3;"><?php echo esc_textarea($Package->Archive->FilterExts); ?></textarea>

                    <div class="dup-tabs-opts-help">
                        <?php _e("The directory paths and extensions above will be be excluded from the archive file if enabled is checked.", 'duplicator'); ?> <br/>
                        <?php _e("Use the full path for directories and semicolons to separate all items.", 'duplicator'); ?>
                    </div>
					<br/>
					<span style="font-style:italic; font-size:12px ">
						<?php echo sprintf(DUP_Util::__('%1$s are available in'), 'Individual file filters'); ?>
						<a href="http://snapcreek.com/duplicator/?free-file-filters" target="_blank">Duplicator Pro</a>
					</span>
                </div>
            </div>

            <!-- TAB2: DATABASE -->
            <div class="tabs-panel" id="dup-pack-opts-tabs-panel-2" style="display: none;">
                <div class="dup-enable-filters">						
                    <table>
						<tr>
							<td colspan="2">
								<div style="margin:0 0 10px 0">
									<?php _e("Build Mode", 'duplicator') ?>: &nbsp;
									<a href="?page=duplicator-settings"><?php echo $dbbuild_mode; ?></a>
								</div>
							</td>
						</tr>
                        <tr>
                            <td><input type="checkbox" id="dbfilter-on" name="dbfilter-on" onclick="Duplicator.Pack.ToggleDBFilters()" <?php echo ($Package->Database->FilterOn) ? "checked='checked'" : ""; ?> /></td>
                            <td >
								<label for="dbfilter-on"><?php _e("Enable Table Filters", 'duplicator') ?> &nbsp;</label> 
								<i class="fa fa-question-circle" 
								   data-tooltip-title="<?php DUP_Util::_e("Enable Table Filters:"); ?>" 
								   data-tooltip="<?php DUP_Util::_e('Checked tables will not be added to the database script.  Excluding certain tables can possibly cause your site or plugins to not work correctly after install!'); ?>">
								</i>
							</td>
                        </tr>
                    </table>
                </div>
                <div id="dup-db-filter-items">
                    <a href="javascript:void(0)" id="dball" onclick="jQuery('#dup-dbtables .checkbox').prop('checked', true).trigger('click');">[ <?php _e('Include All', 'duplicator'); ?> ]</a> &nbsp; 
                    <a href="javascript:void(0)" id="dbnone" onclick="jQuery('#dup-dbtables .checkbox').prop('checked', false).trigger('click');">[ <?php _e('Exclude All', 'duplicator'); ?> ]</a>
                    <div style="white-space: nowrap">
                        <?php
                        $tables = $wpdb->get_results("SHOW FULL TABLES FROM `" . DB_NAME . "` WHERE Table_Type = 'BASE TABLE' ", ARRAY_N);
                        $num_rows = count($tables);
                        echo '<table id="dup-dbtables"><tr><td valign="top">';
                        $next_row = round($num_rows / 3, 0);
                        $counter = 0;
                        $tableList = explode(',', $Package->Database->FilterTables);
                        foreach ($tables as $table)
                        {
                            if (in_array($table[0], $tableList))
                            {
                                $checked = 'checked="checked"';
                                $css = 'text-decoration:line-through';
                            }
                            else
                            {
                                $checked = '';
                                $css = '';
                            }
                            echo "<label for='dbtables-{$table[0]}' style='{$css}'><input class='checkbox dbtable' $checked type='checkbox' name='dbtables[]' id='dbtables-{$table[0]}' value='{$table[0]}' onclick='Duplicator.Pack.ExcludeTable(this)' />&nbsp;{$table[0]}</label><br />";
                            $counter++;
                            if ($next_row <= $counter)
                            {
                                echo '</td><td valign="top">';
                                $counter = 0;
                            }
                        }
                        echo '</td></tr></table>';
                        ?>
                    </div>	
                </div>
				<br/>
				<?php DUP_Util::_e("Compatibility Mode") ?> &nbsp;
				<i class="fa fa-question-circle" 
				   data-tooltip-title="<?php DUP_Util::_e("Compatibility Mode:"); ?>" 
				   data-tooltip="<?php DUP_Util::_e('This is an advanced database backwards compatibility feature that should ONLY be used if having problems installing packages.'
						   . ' If the database server version is lower than the version where the package was built then these options may help generate a script that is more compliant'
						   . ' with the older database server. It is recommended to try each option separately starting with mysql40.'); ?>">
				</i> &nbsp;
				<small style="font-style:italic">
					<a href="https://dev.mysql.com/doc/refman/5.7/en/mysqldump.html#option_mysqldump_compatible" target="_blank">[<?php DUP_Util::_e('details'); ?>]</a>
				</small>
				<br/>
				
				<?php if ($dbbuild_mode == 'mysqldump') :?>
					<?php
						$modes = explode(',', $Package->Database->Compatible);
						$is_mysql40		= in_array('mysql40',	$modes);
						$is_no_table	= in_array('no_table_options',  $modes);
						$is_no_key		= in_array('no_key_options',	$modes);
						$is_no_field	= in_array('no_field_options',	$modes);
					?>
					<table class="dbmysql-compatibility">
						<tr>
							<td>
								<input type="checkbox" name="dbcompat[]" id="dbcompat-mysql40" value="mysql40" <?php echo $is_mysql40 ? 'checked="true"' : ''; ?> > 
								<label for="dbcompat-mysql40"><?php DUP_Util::_e("mysql40") ?></label> 
							</td>
							<td>
								<input type="checkbox" name="dbcompat[]" id="dbcompat-no_table_options" value="no_table_options" <?php echo $is_no_table ? 'checked="true"' : ''; ?>> 
								<label for="dbcompat-no_table_options"><?php DUP_Util::_e("no_table_options") ?></label>
							</td>
							<td>
								<input type="checkbox" name="dbcompat[]" id="dbcompat-no_key_options" value="no_key_options" <?php echo $is_no_key ? 'checked="true"' : ''; ?>> 
								<label for="dbcompat-no_key_options"><?php DUP_Util::_e("no_key_options") ?></label>
							</td>
							<td>
								<input type="checkbox" name="dbcompat[]" id="dbcompat-no_field_options" value="no_field_options" <?php echo $is_no_field ? 'checked="true"' : ''; ?>> 
								<label for="dbcompat-no_field_options"><?php DUP_Util::_e("no_field_options") ?></label>
							</td>
						</tr>					
					</table>
				<?php else : ?>
					<i><?php DUP_Util::_e("This option is only availbe with mysqldump mode."); ?></i>
				<?php endif; ?>

            </div>
        </div>		
    </div>
</div><br/>
<!-- end meta-box options  -->


<!-- META-BOX: INSTALLER -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fa fa-bolt"></i> <?php _e('Installer', 'duplicator') ?>
        <div class="dup-box-arrow"></div>
    </div>			

    <div class="dup-box-panel" id="dup-pack-installer-panel" style="<?php echo $ui_css_installer ?>">
        <div class="dup-installer-header-1"><?php echo _e('STEP 1 - INPUTS', 'duplicator'); ?></div><br/>
        <table class="dup-installer-tbl">
            <tr>
                <td colspan="2"><div class="dup-installer-header-2"><?php _e("MySQL Server", 'duplicator') ?></div></td>
            </tr>
            <tr>
                <td style="width:130px"><?php _e("Host", 'duplicator') ?></td>
                <td><input type="text" name="dbhost" id="dbhost" value="<?php echo $Package->Installer->OptsDBHost ?>"  maxlength="200" placeholder="localhost"/></td>
            </tr>
			<tr>
                <td style="width:130px"><?php _e("Host Port", 'duplicator') ?></td>
                <td><input type="text" name="dbport" id="dbport" value="<?php echo $Package->Installer->OptsDBPort ?>"  maxlength="200" placeholder="3306"/></td>
            </tr>
            <tr>
                <td><?php _e("Database", 'duplicator') ?></td>
                <td><input type="text" name="dbname" id="dbname" value="<?php echo $Package->Installer->OptsDBName ?>" maxlength="100" placeholder="mydatabaseName" /></td>
            </tr>							
            <tr>
                <td><?php _e("User", 'duplicator') ?></td>
                <td><input type="text" name="dbuser" id="dbuser" value="<?php echo $Package->Installer->OptsDBUser ?>"  maxlength="100" placeholder="databaseUserName" /></td>
            </tr>
            <tr>
                <td colspan="2"><div class="dup-installer-header-2"><?php _e("Advanced Options", 'duplicator') ?></div></td>
            </tr>						
            <tr>
                <td colspan="2">
                    <table>
                        <tr>
                            <td style="width:130px"><?php _e("SSL", 'duplicator') ?></td>
                            <td style="padding-right: 20px; white-space: nowrap">
                                <input type="checkbox" name="ssl-admin" id="ssl-admin" <?php echo ($Package->Installer->OptsSSLAdmin) ? "checked='checked'" : ""; ?>  />
                                <label class="chk-labels" for="ssl-admin"><?php _e("Enforce on Admin", 'duplicator') ?></label>
                            </td>
                            <td>
                                <input type="checkbox" name="ssl-login" id="ssl-login" <?php echo ($Package->Installer->OptsSSLLogin) ? "checked='checked'" : ""; ?>  />
                                <label class="chk-labels" for="ssl-login"><?php _e("Enforce on Logins", 'duplicator') ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e("Cache", 'duplicator') ?></td>									
                            <td style="padding-right: 20px; white-space: nowrap">
                                <input type="checkbox" name="cache-wp" id="cache-wp" <?php echo ($Package->Installer->OptsCacheWP) ? "checked='checked'" : ""; ?>  />
                                <label class="chk-labels" for="cache-wp"><?php _e("Keep Enabled", 'duplicator') ?></label>	
                            </td>
                            <td>
                                <input type="checkbox" name="cache-path" id="cache-path" <?php echo ($Package->Installer->OptsCachePath) ? "checked='checked'" : ""; ?>  />
                                <label class="chk-labels" for="cache-path"><?php _e("Keep Home Path", 'duplicator') ?></label>			
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table><br />

        <div class="dup-installer-header-1"><?php echo _e('STEP 2 - INPUTS', 'duplicator'); ?></div>

        <table class="dup-installer-tbl">
            <tr>
                <td style="width:130px"><?php _e("New URL", 'duplicator') ?></td>
                <td><input type="text" name="url-new" id="url-new" value="<?php echo $Package->Installer->OptsURLNew ?>" placeholder="http://mynewsite.com" /></td>
            </tr>
        </table>
		
        <div class="dup-tabs-opts-help">
			<?php _e("The installer can have these fields pre-filled at install time.", 'duplicator'); ?> <b><?php _e('All values are optional.', 'duplicator'); ?></b>
        </div>		

    </div>		
</div><br/>
<!-- end meta-box: installer  -->


<div class="dup-button-footer">
    <input type="button" value="<?php _e("Reset", 'duplicator') ?>" class="button button-large" <?php echo ($dup_tests['Success']) ? '' : 'disabled="disabled"'; ?> onclick="Duplicator.Pack.ResetSettings()" />
    <input type="submit" value="<?php _e("Next", 'duplicator') ?> &#9658;" class="button button-primary button-large" <?php echo ($dup_tests['Success']) ? '' : 'disabled="disabled"'; ?> />
</div>

</form>

<script>
    jQuery(document).ready(function ($) {
        var DUP_NAMEDEFAULT = '<?php echo $default_name ?>';
        var DUP_NAMELAST = $('#package-name').val();

        Duplicator.Pack.ResetSettings = function () {
            var key = 'duplicator_package_active';
            var result = confirm('<?php _e("This will reset all of the current package settings.  Would you like to continue?", "duplicator"); ?>');
            if (!result)
                return;

            jQuery('#dup-form-opts-action').val(key);
            jQuery('#dup-form-opts').attr('action', '?page=duplicator&tab=new1')
            jQuery('#dup-form-opts').submit();
        }

        Duplicator.Pack.ResetName = function () {
            var current = $('#package-name').val();
            $('#package-name').val((current == DUP_NAMELAST) ? DUP_NAMEDEFAULT : DUP_NAMELAST)
        }

        Duplicator.Pack.ExcludeTable = function (check) {
            var $cb = $(check);
            if ($cb.is(":checked")) {
                $cb.closest("label").css('textDecoration', 'line-through');
            } else {
                $cb.closest("label").css('textDecoration', 'none');
            }
        }

    });
</script>
