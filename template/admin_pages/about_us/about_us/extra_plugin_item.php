<?php

use Duplicator\Utils\ExtraPlugins\ExtraItem;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 * @var ExtraItem $plugin
 */

$plugin = $tplData['plugin'];

switch ($plugin->getStatus()) {
    case ExtraItem::STATUS_ACTIVE:
        $buttonLabel = __('Activated', 'duplicator');
        $buttonClass = 'disabled';
        $statusClass = 'status-active';
        break;
    case ExtraItem::STATUS_INSTALLED:
        $buttonLabel = __('Activate', 'duplicator');
        $buttonClass = 'button-secondary';
        $statusClass = 'status-installed';
        break;
    case ExtraItem::STATUS_NOT_INSTALLED:
    default:
        $buttonLabel = __('Install Plugin', 'duplicator');
        $buttonClass = 'button-primary';
        $statusClass = 'status-missing';
        break;
}

?>
<div class="addons-container">
    <div class="addon-item">
        <div class="details dup-clearfix">
            <img src="<?php echo esc_url($plugin->icon); ?>" alt="<?php echo $plugin->name; ?> logo">
            <h5 class="addon-name">
                <?php echo $plugin->name; ?>
            </h5>
            <p class="addon-desc"><?php echo $plugin->desc; ?></p>
        </div>
        <div class="actions dup-clear">
            <div class="status">
                <strong><?php _e('Status:', 'duplicator') ?>
                    <span class="status-label <?php echo $statusClass; ?>">
                        <?php echo $plugin->getStatusText(); ?>
                    </span>
                </strong>
            </div>
            <div class="action-button">
                <?php if ($plugin->getURLType() === ExtraItem::URL_TYPE_GENERIC) { ?>
                    <a href="<?php echo esc_url($plugin->url); ?>"
                        title="<?php echo esc_attr($buttonLabel); ?>"
                        target="_blank" rel="noopener noreferrer"
                        class="button <?php echo $buttonClass; ?>"
                    >
                        <?php echo esc_html($buttonLabel); ?>
                    </a>
                <?php } else { ?>
                <button class="button <?php echo $buttonClass; ?> dup-extra-plugin-item" data-plugin="<?php echo $plugin->getSlug();?>">
                    <?php echo esc_html($buttonLabel); ?>
                </button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
