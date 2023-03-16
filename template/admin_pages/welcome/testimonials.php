<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="testimonials upgrade">
    <div class="block">
        <h1><?php esc_html_e('Testimonials', 'duplicator'); ?></h1>

        <div class="testimonial-block dup-clearfix">
            <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/welcome-testimonial-Karina.png">
            <p>
                <?php
                echo wp_kses(
                    __(
                        'It walked me step-by-step through the process of migrating a WordPress website. If you want to save ' .
                        'a ton of time with <b>WP migration</b>, I very much recommend this plugin!',
                        'duplicator'
                    ),
                    array('b' => array())
                );
                ?>
            <p>
            <p><strong>Karina Caidez</strong>, Website Designer</p>
        </div>

        <div class="testimonial-block dup-clearfix">
            <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/welcome-testimonial-Blake.png">
            <p>
                <?php
                echo wp_kses(
                    __(
                        'Duplicator Pro is the best <b>WordPress migration & backup</b> plugin I have ever used. I will be ' .
                        'recommending this plugin to everyone I can.',
                        'duplicator'
                    ),
                    array('b' => array())
                );
                ?>
            <p>
            <p><strong>Blake Stiller</strong>, Website Development Instructor</p>
        </div>
    </div>
</div>