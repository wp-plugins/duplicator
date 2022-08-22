<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<h3 class="title"><?php esc_html_e("Activation"); ?> </h3>
<hr size="1" />
<table class="form-table">
<tr valign="top">
    <th scope="row"><?php esc_html_e("Manage") ?></th>
    <td><?php echo sprintf(esc_html__('%1$sManage Licenses%2$s'), '<a target="_blank" href="https://snapcreek.com/dashboard?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_campaign=duplicator_pro&utm_content=settings_license_manage_licenses">', '</a>'); ?></td>
</tr>
<tr valign="top">
    <th scope="row"><?php esc_html_e("Type") ?></th>
    <td class="dpro-license-type">
        <?php esc_html_e('Duplicator Lite'); ?>
        <div style="padding: 10px">
            <i class="far fa-check-square"></i> <?php esc_html_e('Basic Features'); ?> <br/>
            <i class="far fa-square"></i> <a target="_blank" href="https://snapcreek.com/duplicator/comparison/?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_content=pro_features&utm_campaign=duplicator_pro"><?php esc_html_e('Pro Features'); ?></a><br>
        </div>
    </td>
</tr>
<tr valign="top">
    <th scope="row"><label><?php esc_html_e("License Key"); ?></label></th>
    <td>
        <div class="description" style="max-width:700px">

            <b><?php esc_html_e("Duplicator Lite:", 'duplicator');  ?></b>

            <ul style="list-style-type:circle; margin-left:40px">
                <li>
                     <?php esc_html_e("The free version of Duplicator does not require a license key.", 'duplicator');  ?>
                </li>
                <li>
                    <?php
                        esc_html_e("If you would like to purchase the professional version you can ", 'duplicator');
                        echo '<a href="https://snapcreek.com/duplicator?utm_source=duplicator_free&utm_medium=wordpress_plugin&utm_campaign=duplicator_pro&utm_content=settings_license_get_copy_here_lite" target="_blank">' .  esc_html__("get a copy here", 'duplicator') . '</a>!';
                    ?>
                </li>
            </ul>

            <b><?php esc_html_e("Duplicator Pro:", 'duplicator');  ?></b>
            <ul style="list-style-type:circle; margin-left: 40px">
                <li>
                    <?php esc_html_e("The professional version is a separate plugin that you download and install. ", 'duplicator');  ?>
                </li>
                <li>
                    <?php esc_html_e("Download professional from the email sent after purchase or login to snapcreek.com", 'duplicator');  ?>
                </li>

            </ul>
        </div>
    </td>
</tr>
</table>



