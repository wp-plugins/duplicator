<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Utils\Utils;

$num = rand(1,2);
switch ($num) {
    case 1:
        $url    = Utils::getCampainUrl('installer_help4_proinstaller_merge1');
        $html   = "Use Drag and Drop install with <a href='{$url}' target='_blank' >Duplicator Pro</a> next time!";
        break;
    default :
        $url    = Utils::getCampainUrl('installer_help4_proinstaller_merge2');
        $html   = "<a href='{$url}' target='_blank' >Get Duplicator Pro!</a> ";
}
?>

<div class="s4-pro-upsell">
    <?php echo $html; ?>
</div>
