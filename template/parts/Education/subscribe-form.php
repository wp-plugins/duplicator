<?php

/**
 *
 * @package   Duplicator
 * @copyright (c) 2023, Snap Creek LLC
 */

use Duplicator\Libs\Upsell;

defined("ABSPATH") || exit;

/**
 * Variables
 *
 * @var \Duplicator\Core\Views\TplMng  $tplMng
 * @var array<string, mixed> $tplData
 */
?>
<div class="dup-subscribe-form">
    <div class="input-area">
        <input type="email"
               name="email"
               placeholder="<?php _e('Email Address', 'duplicator'); ?>"
               value="<?php echo wp_get_current_user()->user_email; ?>"><button
                type="button" class="dup-btn dup-btn-md dup-btn-green"><?php _e('Subscribe', 'duplicator'); ?></button>
    </div>
    <div class="desc">
        <small><?php _e('Get tips and product updates straight to your inbox.', 'duplicator'); ?></small>
    </div>
</div>
