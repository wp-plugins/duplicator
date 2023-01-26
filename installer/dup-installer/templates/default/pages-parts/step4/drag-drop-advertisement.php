<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Upsell;

$num = rand(1, 2);
switch ($num) {
    case 1:
        $url  = Upsell::getCampaignUrl('installer', "Install Complete: Use Drag and Drop");
        $html = "Use Drag and Drop install with <a href='{$url}' target='_blank' >Duplicator Pro</a> next time!";
        break;
    default:
        $url  = Upsell::getCampaignUrl('installer', "Install Complete: Get Duplicator Pro");
        $html = "<a href='{$url}' target='_blank' >Get Duplicator Pro!</a> ";
}
?>

<div class="s4-pro-upsell">
    <?php echo $html; ?>
</div>
