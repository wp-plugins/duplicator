<?php
/**
 * Admin Notifications content.
 *
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */

defined('ABSPATH') || exit;
?>
<div class="dup-notifications-message" data-message-id="<?php echo esc_attr($tplData['id']); ?>;">
    <h3 class="dup-notifications-title">
        <?php echo esc_html($tplData['title']); ?>
        <?php if ($tplData['video_url'] !== false) : ?>
            <a 
                class="dup-notifications-badge" 
                href="<?php echo esc_attr($tplData['video_url']); ?>" 
                <?php echo wp_is_mobile() ? '' : 'data-lity'?>>
                <i class="fa fa-play" aria-hidden="true"></i> <?php esc_html_e('Watch video', 'duplicator'); ?>
            </a>
        <?php endif; ?>
    </h3>
    <div class="dup-notifications-content">
        <?php echo $tplData['content']; ?>
    </div>
    <?php foreach ($tplData['btns'] as $btn) : ?>
        <a 
            href="<?php echo esc_attr($btn['url']); ?>" 
            class="button button-<?php echo esc_attr($btn['type']); ?>" 
            <?php echo $btn['target'] === '_blank' ? 'target="_blank"' : ''; ?>>
            <?php echo esc_html($btn['text']); ?>
        </a>
    <?php endforeach; ?>
</div>
