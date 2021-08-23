<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<style>
	div.panel {padding: 20px 5px 10px 10px; text-align: center; }
     div.sc-note {color:maroon; font-style: italic; line-height:17px; font-size:13px; margin:30px auto 40px auto; max-width: 650px; display:none}
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

    <div class="sc-note">
        <?php esc_html_e(' Drag and Drop import functionality works with packages created by Duplicator Pro.  In the near future, the Duplicator Pro importer '
            . 'will be enhanced to allow the importing of Duplicator Lite packages.  For instructions on how to perform a classic or overwrite install with Duplicator Lite '
            . 'packages visit the ', 'duplicator'); ?>
        <a href="https://snapcreek.com/duplicator/docs/quick-start/?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_content=package_built_install_help5&utm_campaign=duplicator_free#install_site" target="_blank"><?php esc_html_e('Quick Start Guide', 'duplicator'); ?></a>.
    </div>

    <a class="dup-btn-call-action" href="https://snapcreek.com/duplicator/?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_content=free_tools_imports_checkitout&utm_campaign=duplicator_pro" target="_blank">
        <?php esc_html_e('Check It Out!', 'duplicator') ?>
    </a>
</div>
