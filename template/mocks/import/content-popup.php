<?php

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<p>
    <?php
    printf(
        wp_kses(
            __(
                'In addition to the <a href="%s" target="_blank" rel="noopener noreferrer">classic installer method</a> ' .
                'on an empty site, Duplicator Pro now supports Drag and Drop migrations and site restores! Simply drag ' .
                'the bundled site archive to the site you wish to overwrite.',
                'duplicator'
            ),
            array(
                'a' => array(
                    'href'   => array(),
                    'rel'    => array(),
                    'target' => array(),
                )
            )
        ),
        DUPLICATOR_BLOG_URL . 'how-to-move-a-wordpress-site/'
    );
    ?>
</p>