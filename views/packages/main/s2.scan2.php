<?php

use Duplicator\Utils\Upsell;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<!-- ================================================================
SETUP  -->
<div class="details-title">
    <i class="fas fa-tasks"></i> <?php esc_html_e("Setup", 'duplicator');   ?>
    <div class="dup-more-details">
        <a href="?page=duplicator-tools&tab=diagnostics" target="_blank" title="<?php esc_attr_e('Show Diagnostics', 'duplicator');?>"><i class="fa fa-microchip"></i></a>&nbsp;
        <a href="site-health.php" target="_blank" title="<?php esc_attr_e('Check Site Health', 'duplicator');?>"><i class="fas fa-file-medical-alt"></i></a>
    </div>
</div>

<!-- ============
SYSTEM AND WORDPRESS -->
<div class="scan-item scan-item-first">

    <?php
    //TODO Login Need to go here

    $core_dir_included   = array();
    $core_files_included = array();
    $core_dir_notice     = false;
    $core_file_notice    = false;
    $filterDirs          = explode(';', $Package->Archive->FilterDirs);
    $filterFiles         = explode(';', $Package->Archive->FilterFiles);

    if (!$Package->Archive->ExportOnlyDB && $Package->Archive->FilterOn) {
        $core_dir_included = array_intersect($filterDirs, DUP_Util::getWPCoreDirs());
        if (count($core_dir_included)) {
            $core_dir_notice = true;
        }

        $core_files_included = array_intersect($filterFiles, DUP_Util::getWPCoreFiles());
        if (count($core_files_included)) {
            $core_file_notice = true;
        }
    }
    ?>
    <div class='title' onclick="Duplicator.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php esc_html_e('System', 'duplicator');?></div>
        <div id="data-srv-sys-all"></div>
    </div>
    <div class="info">
    <?php
        //WEB SERVER
        $web_servers = implode(', ', $GLOBALS['DUPLICATOR_SERVER_LIST']);
        echo '<span id="data-srv-php-websrv"></span>&nbsp;<b>' . esc_html__('Web Server', 'duplicator') . ":</b>&nbsp; '" . esc_attr($_SERVER['SERVER_SOFTWARE']) . "' <br/>";
        _e("Supported web servers: ", 'duplicator');
        echo "<i>" . esc_html($web_servers) . "</i>";

        //PHP VERSION
        echo '<hr size="1" /><span id="data-srv-php-version"></span>&nbsp;<b>' . esc_html__('PHP Version', 'duplicator') . "</b> <br/>";
        _e('The minimum PHP version supported by Duplicator is 5.2.9. It is highly recommended to use PHP 5.3+ for improved stability.  For international language support please use PHP 7.0+.', 'duplicator');

        //OPEN_BASEDIR
        $test = ini_get("open_basedir");
        $test = ($test) ? 'ON' : 'OFF';
        echo '<hr size="1" /><span id="data-srv-php-openbase"></span>&nbsp;<b>' . esc_html__('PHP Open Base Dir', 'duplicator') . ":</b>&nbsp; '{$test}' <br/>";
        _e('Issues might occur when [open_basedir] is enabled. Work with your server admin to disable this value in the php.ini file if you’re having issues building a package.', 'duplicator');
        echo "&nbsp;<i><a href='http://php.net/manual/en/ini.core.php#ini.open-basedir' target='_blank'>[" . esc_html__('details', 'duplicator') . "]</a></i><br/>";

        //MAX_EXECUTION_TIME
        $test = (@set_time_limit(0)) ? 0 : ini_get("max_execution_time");
        echo '<hr size="1" /><span id="data-srv-php-maxtime"></span>&nbsp;<b>' . esc_html__('PHP Max Execution Time', 'duplicator') . ":</b>&nbsp; '{$test}' <br/>";
        _e('Timeouts may occur for larger packages when [max_execution_time] time in the php.ini is too low.  A value of 0 (recommended) indicates that PHP has no time limits. '
            . 'An attempt is made to override this value if the server allows it.', 'duplicator');
        echo '<br/><br/>';
        _e('Note: Timeouts can also be set at the web server layer, so if the PHP max timeout passes and you still see a build timeout messages, then your web server could be killing '
            . 'the process.   If you are on a budget host and limited on processing time, consider using the database or file filters to shrink the size of your overall package.   '
            . 'However use caution as excluding the wrong resources can cause your install to not work properly.', 'duplicator');
        echo "&nbsp;<i><a href='http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time' target='_blank'>[" . esc_html__('details', 'duplicator')  . "]</a></i>";
        if ($zip_check != null) {
            echo '<br/><br/>';
            echo '<span style="font-weight:bold">';
            _e('Get faster builds with Duplicator Pro with access to shell_exec zip.', 'duplicator');
            echo '</span>';
            echo "&nbsp;<i><a href='" .  esc_url(Upsell::getCampaignUrl('package-build-scan', 'For Shell Zip Get Pro'))  . "' target='_blank'>[" . esc_html__('details', 'duplicator') . "]</a></i>";
        }

        //MANAGED HOST
        $test = DUP_Custom_Host_Manager::getInstance()->isManaged() ? "true" : "false";
        echo '<hr size="1" /><span id="data-srv-sys-managedHost"></span>&nbsp;<b>' . esc_html__('Managed Host', 'duplicator') . ":</b>&nbsp; '{$test}' <br/>";
        _e('A managed host is a WordPress host that tightly controls the server environment so that the software running on it can be closely ‘managed’ by the hosting company. '
            . 'Managed hosts typically have constraints imposed to facilitate this management, including the locking down of certain files and directories as well as non-standard configurations.', 'duplicator');
        echo '<br/><br/>';
        _e('Duplicator Lite allows users to build a package on managed hosts, however, the installer may not properly install packages created on managed hosts due to the non-standard configurations of managed hosts. '
            . 'It is also possible the package engine of Duplicator Lite won’t be able to capture all of the necessary data of a site running on a managed host.', 'duplicator');
        echo '<br/><br/>';
        _e('<b>Due to these constraints Lite does not officially support the migration of managed hosts.</b> '
            . 'It’s possible one could get the package to install but it may require custom manual effort. '
            . 'To get support and the advanced installer processing required for managed host support we encourage users to <i>'
            . '<a href="' .  esc_url(Upsell::getCampaignUrl('package-build-scan', 'Managed Host Support'))  . '" target="_blank">upgrade to Duplicator Pro</a></i>. '
            . 'Pro has more sophisticated package and installer logic and accounts for odd configurations associated with managed hosts.', 'duplicator');
        echo '<br/><br/>';

        ?>
    </div>
</div>

<!-- ============
WP SETTINGS -->
<div class="scan-item">

    <div class="title" onclick="Duplicator.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php esc_html_e('WordPress', 'duplicator');?></div>
        <div id="data-srv-wp-all"></div>
    </div>
    <div class="info">
        <?php
        //VERSION CHECK
        echo '<span id="data-srv-wp-version"></span>&nbsp;<b>' . esc_html__('WordPress Version', 'duplicator') . ":</b>&nbsp; '{$wp_version}' <br/>";
        printf(__('It is recommended to have a version of WordPress that is greater than %1$s.  Older version of WordPress can lead to migration issues and are a security risk. '
            . 'If possible please update your WordPress site to the latest version.', 'duplicator'), DUPLICATOR_SCAN_MIN_WP);

        //CORE FILES
        echo '<hr size="1" /><span id="data-srv-wp-core"></span>&nbsp;<b>' . esc_html__('Core Files', 'duplicator') . "</b> <br/>";


                $filter_text = "";
        if ($core_dir_notice) {
            echo '<small id="data-srv-wp-core-missing-dirs">';
               esc_html_e("The core WordPress paths below will NOT be included in the archive. These paths are required for WordPress to function!", 'duplicator');
               echo "<br/>";
            foreach ($core_dir_included as $core_dir) {
                     echo '&nbsp; &nbsp; <b><i class="fa fa-exclamation-circle scan-warn"></i>&nbsp;' . $core_dir . '</b><br/>';
            }
                    echo '</small><br/>';
                    $filter_text = "directories";
        }

        if ($core_file_notice) {
            echo '<small id="data-srv-wp-core-missing-dirs">';
               esc_html_e("The core WordPress file below will NOT be included in the archive. This file is required for WordPress to function!", 'duplicator');
               echo "<br/>";
            foreach ($core_files_included as $core_file) {
                      echo '&nbsp; &nbsp; <b><i class="fa fa-exclamation-circle scan-warn"></i>&nbsp;' . $core_file . '</b><br/>';
            }
                    echo '</small><br/>';
                    $filter_text .= (strlen($filter_text) > 0) ? " and file" : "files";
        }

        if (strlen($filter_text) > 0) {
            echo '<small>';
            esc_html_e("Note: Please change the {$filter_text} filters if you wish to include the WordPress core files otherwise the data will have to be manually copied"
            . " to the new location for the site to function properly.", 'duplicator');
            echo '</small>';
        }


        if (!$core_dir_notice && !$core_file_notice) :
            esc_html_e("If the scanner is unable to locate the wp-config.php file in the root directory, then you will need to manually copy it to its new location. "
                    . "This check will also look for core WordPress paths that should be included in the archive for WordPress to work correctly.", 'duplicator');
        endif;



        //CACHE DIR
        /*
        $cache_path = DUP_Util::safePath(WP_CONTENT_DIR) . '/cache';
        $cache_size = DUP_Util::byteSize(DUP_Util::getDirectorySize($cache_path));
        echo '<hr size="1" /><span id="data-srv-wp-cache"></span>&nbsp;<b>' . esc_html__('Cache Path', 'duplicator') . ":</b>&nbsp; '".esc_html($cache_path)."' (".esc_html($cache_size).") <br/>";
        _e("Cached data will lead to issues at install time and increases your archive size. Empty your cache directory before building the package by using  "
            . "your cache plugins clear cache feature.  Use caution if manually removing files the cache folder. The cache "
            . "size minimum threshold that triggers this warning is currently set at ", 'duplicator');
        echo esc_html(DUP_Util::byteSize(DUPLICATOR_SCAN_CACHESIZE)) . '.';
        */

        //MU SITE
        if (is_multisite()) {
            echo '<hr size="1" /><span><div class="scan-warn"><i class="fa fa-exclamation-triangle fa-sm"></i></div></span>&nbsp;<b>' . esc_html__('Multisite: Unsupported', 'duplicator') . "</b> <br/>";
            esc_html_e('Duplicator does not support WordPress multisite migrations.  We strongly recommend using Duplicator Pro which currently supports full multisite migrations and various other '
                . 'subsite scenarios.', 'duplicator');
            echo '<br/><br/>';

            esc_html_e('While it is not recommended you can still continue with the build of this package.  At install time additional manual custom configurations will '
                . 'need to be made to finalize this multisite migration.  Please note that any support requests for mulitsite with Duplicator Lite will not be supported.', 'duplicator');
            echo "&nbsp;<i><a href='" .  esc_url(Upsell::getCampaignUrl('package-build-scan', 'Not Multisite Get Pro'))  . "' target='_blank'>[" . esc_html__('upgrade to pro', 'duplicator') . "]</a></i>";
        } else {
            echo '<hr size="1" /><span><div class="scan-good"><i class="fa fa-check"></i></div></span>&nbsp;<b>' . esc_html__('Multisite: N/A', 'duplicator') . "</b> <br/>";
            esc_html_e('This is not a multisite install so duplication will proceed without issue.  Duplicator does not officially support multisite. However, Duplicator Pro supports '
                . 'duplication of a full multisite network and also has the ability to install a multisite subsite as a standalone site.', 'duplicator');
            echo "&nbsp;<i><a href='" .  esc_url(Upsell::getCampaignUrl('package-build-scan', 'Multisite Get Pro'))  . "' target='_blank'>[" . esc_html__('upgrade to pro', 'duplicator') . "]</a></i>";
        }
        ?>
    </div>
</div>

<!-- ======================
MIGRATION STATUS -->
<div id="migratepackage-block"  class="scan-item">
    <div class='title' onclick="Duplicator.Pack.toggleScanItem(this);">
        <div class="text"><i class="fa fa-caret-right"></i> <?php esc_html_e('Migration Status', 'duplicator');?></div>
        <div id="data-arc-status-migratepackage"></div>
    </div>
    <div class="info">
        <script id="hb-migrate-package-result" type="text/x-handlebars-template">
            <div class="container">
                <div class="data">
                    {{#if ARC.Status.CanbeMigratePackage}}
                        <?php esc_html_e("The package created here can be migrated to a new server.", 'duplicator'); ?>
                    {{else}}
                        <span style="color: red;">
                            <?php
                            esc_html_e("The package created here cannot be migrated to a new server.
                                The Package created here can be restored on the same server.", 'duplicator');
                            ?>
                        </span>
                    {{/if}}
                </div>
            </div>
        </script>
        <div id="migrate-package-result"></div>
    </div>
</div>

<script>
(function($){

    //Ints the various server data responses from the scan results
    Duplicator.Pack.intServerData= function(data)
    {
        $('#data-srv-php-websrv').html(Duplicator.Pack.setScanStatus(data.SRV.PHP.websrv));
        $('#data-srv-php-openbase').html(Duplicator.Pack.setScanStatus(data.SRV.PHP.openbase));
        $('#data-srv-php-maxtime').html(Duplicator.Pack.setScanStatus(data.SRV.PHP.maxtime));
        $('#data-srv-php-version').html(Duplicator.Pack.setScanStatus(data.SRV.PHP.version));
        $('#data-srv-php-openssl').html(Duplicator.Pack.setScanStatus(data.SRV.PHP.openssl));
        $('#data-srv-sys-managedHost').html(Duplicator.Pack.setScanStatus(data.SRV.SYS.managedHost));
        $('#data-srv-sys-all').html(Duplicator.Pack.setScanStatus(data.SRV.SYS.ALL));

        $('#data-srv-wp-version').html(Duplicator.Pack.setScanStatus(data.SRV.WP.version));
        $('#data-srv-wp-core').html(Duplicator.Pack.setScanStatus(data.SRV.WP.core));
        // $('#data-srv-wp-cache').html(Duplicator.Pack.setScanStatus(data.SRV.WP.cache));
        var duplicatorScanWPStatus = $('#data-srv-wp-all');
        duplicatorScanWPStatus.html(Duplicator.Pack.setScanStatus(data.SRV.WP.ALL));
        if ('Warn' == data.SRV.WP.ALL) {
            duplicatorScanWPStatus.parent().click();
        }
    }
    
})(jQuery);
</script>
