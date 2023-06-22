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

<div class="intro">
    <div class="sullie">
        <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/willie.svg"
             alt="<?php esc_attr_e('Willie the Duplicator mascot', 'duplicator'); ?>">
    </div>
    <div class="block">
        <h1><?php esc_html_e('Welcome to Duplicator', 'duplicator'); ?></h1>
        <h6><?php esc_html_e('Thank you for choosing Duplicator - the most powerful WordPress Migration and Backup ' .
                'plugin in the market.', 'duplicator'); ?></h6>
    </div>
    <a href="#" style="display: none;" class="play-video"
       title="<?php esc_attr_e('Watch how to create your first form', 'duplicator'); ?>">
        <img src="#"
             alt="<?php esc_attr_e('Watch how to create your first form', 'duplicator'); ?>"
             class="video-thumbnail">
    </a>
    <div style="padding-top: 0;" class="block">
        <h6><?php esc_html_e('Duplicator makes it easy to create backups and migrations in WordPress. Get started by ' .
                'creating a new package or read our quick start guide.', 'duplicator'); ?></h6>
        <div class="button-wrap dup-clearfix">
            <div class="left">
                <a href="<?php echo esc_url($tplData['packageNonceUrl']); ?>"
                   class="dup-btn dup-btn-lg dup-btn-orange dup-btn-block">
                    <?php esc_html_e('Create Your First Package', 'duplicator'); ?>
                </a>
            </div>
            <div class="right">
                <a href="<?php echo DUPLICATOR_BLOG_URL; ?>knowledge-base-article-categories/quick-start/"
                   class="dup-btn dup-btn-lg dup-btn-grey dup-btn-block"
                   target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e('Read the Full Guide', 'duplicator'); ?>
                </a>
            </div>
        </div>
    </div>
</div>