<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
    div.panel {padding: 20px 5px 10px 10px; text-align: center; }
</style>

<div class="panel">
    <img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/logo-dpro-300x50.png"  />

    <div class="txt-call-action-title">
        <?php echo '<i class="fas fa-arrow-alt-circle-down"></i> ' .  esc_html__('Drag and Drop Imports are available in Duplicator Pro.', 'duplicator'); ?>
    </div>
    <div class="txt-call-action-sub">
        <?php
            esc_html_e('The Import feature lets you skip the FTP and database creation steps when installing a site.', 'duplicator');
            echo '<br/>';
            esc_html_e('Just drag and drop a Duplicator Pro archive to quickly replace an existing WordPress installation!', 'duplicator');
        ?>
    </div>

    <a class="dup-btn-call-action" href="<?php echo esc_url(\Duplicator\Libs\Upsell::getCampaignUrl('import-tab')); ?>" target="_blank">
        <?php esc_html_e('Check It Out!', 'duplicator') ?>
    </a>
</div>
