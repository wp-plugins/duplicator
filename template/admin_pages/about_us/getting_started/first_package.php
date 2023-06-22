<?php

/**
 * Template for First Package section
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

defined('ABSPATH') || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="dup-admin-about-section dup-admin-about-section-first-form" style="display:flex;">

    <div class="dup-admin-about-section-first-form-text">

        <h2><?php _e('Creating Your First Package', 'duplicator') ?></h2>

        <p>
            <?php _e('Want to get started creating your first package with Duplicator? By following the step by step ' .
                'instructions in this walkthrough, you can easily create a backup or migration.', 'duplicator') ?>
        </p>
        <p>
            <?php _e('To begin, you’ll need to be logged into the WordPress admin area. Once there, click on Duplicator ' .
                'in the admin sidebar to go the Packages page.', 'duplicator') ?>
        </p>
        <p>
            <?php _e('In the Packages page, the packages list will be empty because there are no packages yet. To create ' .
                'a new package, click on the Create New button, and this will launch the Package Creation Wizard.', 'duplicator') ?>
        </p>

        <ul class="list-plain">
            <li>
                <a href="<?php echo DUPLICATOR_BLOG_URL; ?>knowledge-base-article-categories/quick-start/" target="_blank" rel="noopener noreferrer">
                    <?php _e('Quick Start Guide', 'duplicator'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo DUPLICATOR_DOCS_URL; ?>backup-site/" target="_blank" rel="noopener noreferrer">
                    <?php _e('How to Create a Package', 'duplicator'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo DUPLICATOR_DOCS_URL; ?>classic-install/" target="_blank" rel="noopener noreferrer">
                    <?php _e('How to Migrate to a New Site', 'duplicator'); ?>
                </a>
            </li>
        </ul>

    </div>

</div>