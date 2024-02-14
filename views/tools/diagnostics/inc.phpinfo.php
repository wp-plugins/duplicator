<?php

    use Duplicator\Libs\Snap\SnapUtil;

    defined('ABSPATH') || defined('DUPXABSPATH') || exit;

    ob_start();
    SnapUtil::phpinfo();
    $serverinfo = ob_get_contents();
    ob_end_clean();

if (strlen($serverinfo) < 100) {
    $serverinfo = 'The <a href="https://www.php.net/manual/en/function.phpinfo.php" target="_blank">phpinfo function</a> is not supported on this server, '
        . 'for more details contact your hosting provider.';
} else {
    $serverinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $serverinfo);
    $serverinfo = preg_replace('%^.*<title>(.*)</title>.*$%ms', '$1', $serverinfo);
}
?>

<!-- ==============================
PHP INFORMATION -->
<div class="dup-box">
    <div class="dup-box-title">
        <i class="fa fa-info-circle"></i>
        <?php esc_html_e("PHP Information", 'duplicator'); ?>
        <div class="dup-box-arrow"></div>
    </div>
    <div class="dup-box-panel" style="display:none">
        <div id="dup-phpinfo" style="width:95%">
            <?php
                echo "<div id='dup-server-info-area'>{$serverinfo}</div>";
                $serverinfo = null;
            ?>
        </div><br/>
    </div>
</div>
<br/>
