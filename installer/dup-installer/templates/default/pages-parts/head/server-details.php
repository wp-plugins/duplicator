<?php

$archiveConfig = DUPX_ArchiveConfig::getInstance();
?>
<div id="dialog-server-details" title="Setup Information" style="display:none">
    <!-- DETAILS -->
    <div class="dlg-serv-info">
        <?php
        $ini_path       = php_ini_loaded_file();
        $ini_max_time   = ini_get('max_execution_time');
        $ini_memory     = ini_get('memory_limit');
        $ini_error_path = ini_get('error_log');
        ?>
        <div class="hdr">SERVER DETAILS</div>
        <label>Try CDN Request:</label>
        <?php echo (DUPX_U::tryCDN("ajax.aspnetcdn.com", 443) && DUPX_U::tryCDN("ajax.googleapis.com", 443)) ? 'Yes' : 'No'; ?><br/>
        <label>Web Server:</label>              <?php echo DUPX_U::esc_html($_SERVER['SERVER_SOFTWARE']); ?><br/>
        <label>PHP Version:</label>             <?php echo DUPX_U::esc_html(phpversion()); ?><br/>
        <label>PHP SAPI:</label>                <?php echo DUPX_U::esc_html(php_sapi_name()); ?><br/>
        <label>PHP ZIP Archive:</label>         <?php echo class_exists('ZipArchive') ? 'Is Installed' : 'Not Installed'; ?> <br/>
        <label>PHP max_execution_time:</label>  <?php echo $ini_max_time === false ? 'unable to find' : DUPX_U::esc_html($ini_max_time); ?><br/>
        <label>PHP memory_limit:</label>        <?php echo empty($ini_memory) ? 'unable to find' : DUPX_U::esc_html($ini_memory); ?><br/>
        <label>Error Log Path:</label>          <?php echo empty($ini_error_path) ? 'unable to find' : '/dup-installer/php_error__[HASH].log' ?><br/>

        <br/>
        <div class="hdr">PACKAGE BUILD DETAILS</div>
        <label>Plugin Version:</label>          <?php echo DUPX_U::esc_html($archiveConfig->version_dup); ?><br/>
        <label>WordPress Version:</label>       <?php echo DUPX_U::esc_html($archiveConfig->version_wp); ?><br/>
        <label>PHP Version:</label>             <?php echo DUPX_U::esc_html($archiveConfig->version_php); ?><br/>
        <label>Database Version:</label>        <?php echo DUPX_U::esc_html($archiveConfig->version_db); ?><br/>
        <label>Operating System:</label>        <?php echo DUPX_U::esc_html($archiveConfig->version_os); ?><br/>

    </div>
</div>

<script>
    DUPX.openServerDetails = function ()
    {
        $("#dialog-server-details").dialog({
            resizable: false,
            height: "auto",
            width: 700,
            modal: true,
            position: {my: 'top', at: 'top+150'},
            buttons: {"OK": function () {
                    $(this).dialog("close");
                }}
        });
    }
</script>