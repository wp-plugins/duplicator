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

<div class="features">
    <div class="block">
        <h1><?php esc_html_e('Duplicator Features', 'duplicator'); ?></h1>
        <h6><?php esc_html_e('Duplicator is both easy to use and extremely powerful. We have tons of helpful features ' .
                'that allow us to give you everything you need from a backup & migration plugin.', 'duplicator'); ?></h6>

        <div class="feature-list dup-clearfix">
            <div class="feature-block first">
                <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/scheduled-backups.svg">
                <h5><?php esc_html_e('Scheduled Backups', 'duplicator'); ?></h5>
                <p>
                    <?php esc_html_e('Ensure that important data is regularly and consistently backed up, allowing for ' .
                        'quick and efficient recovery in case of data loss.', 'duplicator'); ?>
                </p>
            </div>

            <div class="feature-block last">
                <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/cloud-backups.svg">
                <h5><?php esc_html_e('Cloud Backups', 'duplicator'); ?></h5>
                <p>
                    <?php esc_html_e('Back up to Dropbox, FTP, Google Drive, OneDrive, or Amazon S3 and more for safe storage.', 'duplicator'); ?>
                </p>
            </div>

            <div class="feature-block first">
                <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/recovery-points.svg">
                <h5><?php esc_html_e('Recovery Points', 'duplicator'); ?></h5>
                <p>
                    <?php esc_html_e('Recovery Points provides protection against mistakes and bad updates by letting ' .
                        'you quickly rollback your system to a known, good state.', 'duplicator'); ?>
                </p>
            </div>

            <div class="feature-block last">
                <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/secure-file-encryption.svg">
                <h5><?php esc_html_e('Secure File Encryption', 'duplicator'); ?></h5>
                <p>
                    <?php esc_html_e('Protect and secure the archive file with industry-standard AES-256 encryption.', 'duplicator'); ?>
                </p>
            </div>

            <div class="feature-block first">
                <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/server-to-server-import.svg">
                <h5><?php esc_html_e('Server to Server Import', 'duplicator'); ?></h5>
                <p>
                    <?php esc_html_e('Direct package import from source server or cloud storage using URL. No need to ' .
                        'download the package to your desktop machine first.', 'duplicator'); ?>
                </p>
            </div>

            <div class="feature-block last">
                <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/file-and-database-filters.svg">
                <h5><?php esc_html_e('File & Database Table Filters', 'duplicator'); ?></h5>
                <p>
                    <?php esc_html_e('Use file and database filters to pick and choose exactly what you want to backup or ' .
                        'transfer. No bloat!', 'duplicator'); ?>
                </p>
            </div>

            <div class="feature-block first">
                <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/large-site-support.svg">
                <h5><?php esc_html_e('Large Site Support', 'duplicator'); ?></h5>
                <p>
                    <?php esc_html_e('Duplicator Pro has developed a new way to package backups especially tailored for ' .
                        'larger site. No server timeouts or other restrictions.', 'duplicator'); ?>
                </p>
            </div>

            <div class="feature-block last">
                <img src="<?php echo DUPLICATOR_PLUGIN_URL; ?>assets/img/welcome/multisite-support.svg">
                <h5><?php esc_html_e('Multisite Support', 'duplicator'); ?></h5>
                <p>
                    <?php esc_html_e('Duplicator Pro supports multisite network backup & migration. You can even install ' .
                        ' a subsite as a standalone site.', 'duplicator'); ?>
                </p>
            </div>
        </div>

        <div class="button-wrap">
            <a href="<?php echo \Duplicator\Utils\Upsell::getCampaignUrl('welcome-page', 'See All Features') ?>"
               class="dup-btn dup-btn-lg dup-btn-grey" rel="noopener noreferrer"
               target="_blank">
                <?php esc_html_e('See All Features', 'duplicator'); ?>
            </a>
        </div>
    </div>
</div>