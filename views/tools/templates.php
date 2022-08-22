<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
    div.panel {padding: 20px 5px 10px 10px; text-align: center; }
</style>

<div class="panel">
    <img src="<?php echo DUPLICATOR_PLUGIN_URL ?>assets/img/logo-dpro-300x50.png"  />

    <div class="txt-call-action-title">
        <i class="far fa-clone"></i>
        <?php echo esc_html__('Templates are available in Duplicator Pro.', 'duplicator');  ?>
    </div>
    <div class="txt-call-action-sub">
        <?php
            esc_html_e('Templates allow you to customize what you want to include in your site and store it as a re-usable profile.', 'duplicator');
            echo '<br/>';
            esc_html_e('Save time and create a template that can be applied to a schedule or a custom package setup.', 'duplicator');
        ?>
    </div>

    <a class="dup-btn-call-action" href="https://snapcreek.com/duplicator/?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_content=free_tools_templates_checkitout&utm_campaign=duplicator_pro" target="_blank">
        <?php esc_html_e('Check It Out!', 'duplicator') ?>
    </a>
</div>


