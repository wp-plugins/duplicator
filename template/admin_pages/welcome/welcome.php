<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Core\Views\TplMng;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */
?>

<div id="duplicator-welcome">
    <div class="container">
        <?php
        TplMng::getInstance()->render(
            'admin_pages/welcome/intro',
            array(
                'packageNonceUrl' => wp_nonce_url(admin_url('admin.php?page=duplicator&tab=new1'), 'new1-package'),
            )
        );

        TplMng::getInstance()->render('admin_pages/welcome/features');

        TplMng::getInstance()->render('admin_pages/welcome/upgrade-cta');

        TplMng::getInstance()->render('admin_pages/welcome/testimonials');

        TplMng::getInstance()->render(
            'admin_pages/welcome/footer',
            array(
                'packageNonceUrl' => wp_nonce_url(admin_url('admin.php?page=duplicator&tab=new1'), 'new1-package'),
            )
        );
        ?>
    </div>
</div>