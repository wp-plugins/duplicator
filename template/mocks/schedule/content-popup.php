<?php

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<p>
    <?php
    _e(
        'Scheduled Backups provide peace of mind and ensure that critical data can be ' .
        'quickly and easily restored in the event of a disaster or loss. Duplicator Pro supports Hourly, Daily, Weekly and Monthly scheduled backups.',
        'duplicator'
    );
    ?>
</p>
<p> 
    <?php
    _e(
        'Supported Cloud Storage: Google Drive, Dropbox, Microsoft One Drive, Amazon S3 (or any compatible S3 service), and FTP/SFTP Storage.',
        'duplicator'
    );
    ?>
</p>