<?php

/**
 * Duplicator package row in table packages list
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

use Duplicator\Libs\Snap\SnapIO;
use Duplicator\Utils\Upsell;

defined("ABSPATH") or die("");

/**
 * Variables
 *
 * @var \Duplicator\Core\Controllers\ControllersManager $ctrlMng
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

$archiveFilterPaths       = trim($tplData['package']->Archive->FilterDirs . ";" . $tplData['package']->Archive->FilterFiles, ";");
$archiveFilterPaths       = str_replace(';', ";\n", $archiveFilterPaths);
$archiveFilterExtensions  = $tplData['package']->Archive->FilterExts;
$packageComponentsTooltip = wp_kses(
    __("Package components allow you to include/exclude differents part of your WordPress installation in the package.</br></br>" .
    "<b>Database</b>: Include the database in the package.</br>" .
    "<b>Plugins</b>: Include the plugins in the package. With the 'active only' option enabled, only active plugins will be included in the package.</br>" .
    "<b>Themes</b>: Include the themes in the package. With the 'active only' option enabled, only active themes will be included in the package.</br>" .
    "<b>Media</b>: Include the 'uploads' folder.</br>" .
    "<b>Other</b>: Include non-WordPress files and folders in the root directory.</br>", 'duplicator'),
    array(
        'br' => array(),
        'b'  => array(),
    )
);
$pathFiltersTooltip       = __("File filters allow you to exclude files and folders from the package. To enable path and extension filters check the " .
    "checkbox. Enter the full path of the files and folders you want to exclude from the package as a semicolon (;) seperated list.");
$extensionFilterTooltip   = __("File extension filters allow you to exclude files with certain file extensions from the package e.g. zip;rar;pdf etc. " .
    "Enter the file extensions you want to exclude from the package as a semicolon (;) seperated list.");
?>

<div class="dup-package-components">
    <div class="component-section">
        <div class="section-title">
            <span id="component-section-title">
                <?php _e('Components', 'duplicator'); ?>
                <i class="fas fa-question-circle fa-sm" 
                    data-tooltip-title="<?php _e('Package Components (Pro feature)', 'duplicator'); ?>" 
                    data-tooltip="<?php echo $packageComponentsTooltip;?>" 
                    aria-expanded="false"></i>
            </span>
        </div>
        <div class="components-shortcut-select">
            <div class="dup-radio-button-group-wrapper">
                <input 
                    type="radio"
                    id="dup-component-shortcut-action-all"
                    name="auto-select-components"
                    class="dup-components-shortcut-radio"
                    value="all"
                    data-parsley-multiple="auto-select-components"
                    <?php checked(!$tplData['package']->Archive->ExportOnlyDB); ?>
                >
                <label for="dup-component-shortcut-action-all">All</label>
                <input 
                    type="radio"
                    id="dup-component-shortcut-action-database"
                    name="auto-select-components"
                    class="dup-components-shortcut-radio"
                    value="database"
                    <?php checked($tplData['package']->Archive->ExportOnlyDB); ?>
                    data-parsley-multiple="auto-select-components"
                >
                <label for="dup-component-shortcut-action-database">Database Only</label>
                <label class="disabled" for="dup-component-shortcut-action-media">Media Only</label>
                <label class="disabled" for="dup-component-shortcut-action-custom">Custom</label>
            </div>
        </div>
        <ul class="custom-components-select">
            <li>
                <label class="disabled">
                    <input type="checkbox" name="package_component_db" class="package_component_checkbox" disabled checked>
                    <span><?php _e('Database', 'duplicator'); ?></span>
                </label>
            </li>
            <li>
                <label class="disabled">
                <input type="checkbox" name="package_component_plugins" class="package_component_checkbox" disabled 
                    <?php checked(!$tplData['package']->Archive->ExportOnlyDB); ?>>
                    <span><?php _e('Plugins', 'duplicator'); ?></span>
                </label>
            </li>
            <li>
                <label class="secondary disabled">
                    <input type="checkbox" name="package_component_plugins_active" class="package_component_checkbox" disabled>
                    <span><?php _e('Only Active Plugins', 'duplicator'); ?></span>
                </label>
            </li>
            <li>
                <label class="disabled">
                    <input type="checkbox" name="package_component_themes" class="package_component_checkbox" disabled 
                    <?php checked(!$tplData['package']->Archive->ExportOnlyDB); ?>>
                    <span><?php _e('Themes', 'duplicator'); ?></span>
                </label>
            </li>
            <li>
                <label class="secondary disabled">
                    <input type="checkbox" name="package_component_themes_active" class="package_component_checkbox" disabled>
                    <span><?php _e('Only Active Themes', 'duplicator'); ?></span>
                </label>
            </li>
            <li>
                <label class="disabled">
                    <input type="checkbox" name="package_component_media" class="package_component_checkbox" disabled 
                    <?php checked(!$tplData['package']->Archive->ExportOnlyDB); ?>>
                    <span><?php _e('Media', 'duplicator'); ?></span>
                </label>
            </li>
            <li>
                <label class="disabled">
                    <input type="checkbox" name="package_component_other" class="package_component_checkbox" disabled 
                    <?php checked(!$tplData['package']->Archive->ExportOnlyDB); ?>>
                    <span><?php _e('Other', 'duplicator'); ?></span>
                </label>
            </li>
        </ul>
    </div>
    <div class="filter-section">
        <div class="filters">
            <div class="section-title">
                <span><?php _e('Filters', 'duplicator'); ?> (<label><input id="filter-on"
                               name="filter-on"
                               type="checkbox" <?php checked($tplData['package']->Archive->FilterOn); ?>> Enable</label>)
                    <i class="fas fa-question-circle fa-sm" 
                        data-tooltip-title="<?php _e('Path Filters', 'duplicator'); ?>" 
                        data-tooltip="<?php echo $pathFiltersTooltip;?>" 
                        aria-expanded="false"></i>
                </span>
                <div class="filter-links">
                    <a href="#" data-filter-path="<?php echo SnapIO::trailingslashit(DUP_Archive::getOriginalPaths('home')); ?>">[root path]</a>
                    <a href="#" data-filter-path="<?php echo SnapIO::trailingslashit(DUP_Archive::getOriginalPaths('wpcontent')); ?>">[wp-content]</a>
                    <a href="#" data-filter-path="<?php echo SnapIO::trailingslashit(DUP_Archive::getOriginalPaths('uploads')); ?>">[wp-uploads]</a>
                    <a href="#" data-filter-path="<?php echo SnapIO::trailingslashit(DUP_Archive::getOriginalPaths('wpcontent')) . 'cache/'; ?>">[cache]</a>
                    <a href="#" id="clear-path-filters">(clear)</a>
                </div>
            </div>
            <textarea
                    id="filter-paths"
                    name="filter-paths"
                    placeholder="/full_path/dir/;&#10;/full_path/file;"
                    readonly><?php echo $archiveFilterPaths; ?></textarea>
            <div class="section-title">
            <span>
                <?php _e('File Extensions', 'duplicator'); ?>
                <i class="fas fa-question-circle fa-sm" 
                    data-tooltip-title="<?php _e('File Extensions', 'duplicator'); ?>" 
                    data-tooltip="<?php echo $extensionFilterTooltip;?>" 
                    aria-expanded="false"></i>
            </span>
                <div class="filter-links">
                    <a href="#" data-filter-exts="avi;mov;mp4;mpeg;mpg;swf;wmv;aac;m3u;mp3;mpa;wav;wma">[media]</a>
                    <a href="#" data-filter-exts="zip;rar;tar;gz;bz2;7z">[archive]</a>
                    <a href="#" id="clear-extension-filters">(clear)</a>
                </div>
            </div>
            <textarea id="filter-exts" name="filter-exts" placeholder="ext1;ext2;ext3;" readonly><?php echo $archiveFilterExtensions; ?></textarea>
        </div>
        <div class="db-only-message">
            <?php
            echo wp_kses(
                __(
                    "<b>Overview:</b><br> This advanced option excludes all files from the archive.  Only the database and a copy of the installer.php "
                    . "will be included in the archive.zip file. The option can be used for backing up and moving only the database.",
                    'duplicator'
                ),
                array(
                    'b' => array(),
                    'br' => array(),
                )
            );
            echo '<br/><br/>';

            echo wp_kses(
                __(
                    "<b><i class='fa fa-exclamation-circle'></i> Notice:</b><br/> "
                    . "Installing only the database over an existing site may have unintended consequences.  "
                    . "Be sure to know the state of your system before installing the database without the associated files.  ",
                    'duplicator'
                ),
                array(
                    'b' => array(),
                    'i' => array('class'),
                    'br' => array()
                )
            );

            esc_html_e(
                "For example, if you have WordPress 5.6 on this site and you copy this site's database to a host that has WordPress 5.8 files "
                . "then the source code of the files  will not be in sync with the database causing possible errors. "
                . "This can also be true of plugins and themes.  "
                . "When moving only the database be sure to know the database will be compatible with "
                . "ALL source code files. Please use this advanced feature with caution!",
                'duplicator'
            );

            echo '<br/><br/>';

            echo wp_kses(
                __("<b>Install Time:</b><br> When installing a database only package please visit the ", 'duplicator'),
                array(
                    'b' => array(),
                    'br' => array(),
                )
            );
            ?>
            <a href="<?php echo DUPLICATOR_DOCS_URL; ?>database-install" target="_blank">
                <?php esc_html_e('database only quick start', 'duplicator'); ?>
            </a>
        </div>
    </div>
</div>
<div id="dup-upgrade-license-info">
    <span class="dup-pro-text">
        <?php
        printf(
            _x(
                'The <b>Media Only</b> and <b>Custom</b> options are not included in Duplicator Lite. ' .
                'To enable advanced options please %1$supgrade to Pro%2$s.',
                '%1$s and %2$s represents the opening and closing HTML tags for an anchor or link.',
                'duplicator'
            ),
            '<a href="' . Upsell::getCampaignUrl('package-components-lite', 'upgrade to Pro') . '" target="_blank">',
            '</a>'
        );
        ?>
    </span>
</div>
<script>
    jQuery(document).ready(function($)
    {
        Duplicator.Pack.ToggleFileFilters = function ()
        {
            if ($("#filter-on").is(':checked') && !$('#dup-component-shortcut-action-database').is(':checked') ) {
                $('#dup-archive-filter-file').show();
                $('#filter-exts, #filter-paths').prop('readonly', false);
            } else {
                $('#dup-archive-filter-file').hide();
                $('#filter-exts, #filter-paths').prop('readonly', true);
            }
        };

        Duplicator.Pack.InitDBOnly = function () {
            let checkedComponentCheckboxes = $('.package_component_checkbox:checked');
            if ($('#dup-component-shortcut-action-database').is(':checked')) {
                $("#dup-archive-db-only").show();
                $('.filters').hide()
                $('.db-only-message').show()
            } else {
                $("#dup-archive-db-only").hide();
                $('.filters').show()
                $('.db-only-message').hide()
            }
        }

        Duplicator.Pack.ToggleDBOnly = function () {
            let allComponentCheckboxes = $('.dup-package-components label:not(.secondary) .package_component_checkbox');
            let dbOnlyCheckbox = $('#dup-component-shortcut-action-database');
            let dbCheckbox = $('[name=package_component_db]');

            if (dbOnlyCheckbox.is(':checked')) {
                allComponentCheckboxes.prop('checked', false);
                dbCheckbox.prop('checked', true);
                $("#dup-archive-db-only").show();
                $('.filters').hide()
                $('.db-only-message').show()
            } else {
                allComponentCheckboxes.prop('checked', true);
                $("#dup-archive-db-only").hide();
                $('.filters').show()
                $('.db-only-message').hide()
            }
            Duplicator.Pack.ToggleFileFilters ();
        }

        Duplicator.Pack.ToggleFileFilters();
        Duplicator.Pack.InitDBOnly();

        $('.dup-components-shortcut-radio').change(Duplicator.Pack.ToggleDBOnly)

        $('a[data-filter-path]').click(function (e) {
            e.preventDefault()

            if ($('#filter-paths').is("[readonly]")) {
                return;
            }

            let currentVal = $('#filter-paths').val()
            let newVal = currentVal.length > 0 ? currentVal + ";\n" + $(this).data('filter-path') : $(this).data('filter-path')

            $('#filter-paths').val(newVal)
        })

        $('#clear-path-filters').click(function (e) {
            e.preventDefault()

            if ($('#filter-paths').is("[readonly]")) {
                return;
            }

            $('#filter-paths').val('')
        })

        $('a[data-filter-exts]').click(function (e) {
            e.preventDefault()

            if ($('#filter-exts').is("[readonly]")) {
                return;
            }

            let currentVal = $('#filter-exts').val()
            let newVal = currentVal.length > 0 ? currentVal + ";" + $(this).data('filter-exts') : $(this).data('filter-exts')

            $('#filter-exts').val(newVal)
        })

        $('#clear-extension-filters').click(function (e) {
            e.preventDefault()

            if ($('#filter-exts').is("[readonly]")) {
                return;
            }

            $('#filter-exts').val('')
        })

        $('#filter-on').change(Duplicator.Pack.ToggleFileFilters)
    });
</script>
