<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
    div.panel {padding: 20px 5px 10px 10px; text-align: center; }
</style>


<div class="panel">
    <img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/logo-dpro-300x50.png"  />

    <div class="txt-call-action-title">
        <i class="far fa-clock fa-sm"></i>  <?php echo esc_html__('Schedules are available in Duplicator Pro.', 'duplicator');  ?>
    </div>

    <div class="txt-call-action-sub">
        <?php
            esc_html_e('Create robust schedules that automatically create packages while you sleep.', 'duplicator');
            echo '<br/>';
            esc_html_e('Simply choose your storage location and when you want it to run.', 'duplicator');
        ?>
    </div>

    <a class="dup-btn-call-action" href="https://snapcreek.com/duplicator/?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_content=free_settings_schedule_checkitout&utm_campaign=duplicator_pro" target="_blank">
        <?php esc_html_e('Check It Out!', 'duplicator') ?>
    </a>
</div>