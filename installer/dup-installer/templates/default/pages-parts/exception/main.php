<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapString;

$recoveryLink = PrmMng::getInstance()->getValue(PrmMng::PARAM_RECOVERY_LINK);
if (SnapString::isHTML($exception->getMessage())) {
    $message = $exception->getMessage();
} else {
    $message = '<b>' . DUPX_U::esc_html($exception->getMessage()) . '</b>';
}
?>
<div id="ajaxerr-data">
    <b style="color:#B80000;">INSTALL ERROR!</b>
    <p>
        Message: <?php echo $message; ?><br>
        Please see the <?php DUPX_View_Funcs::installerLogLink(); ?> file for more details.
        <?php
        if ($exception instanceof DupxException) {
            if ($exception->haveFaqLink()) {
                ?>
                <br>
                See FAQ: <a href="<?php echo $exception->getFaqLinkUrl(); ?>" ><?php echo $exception->getFaqLinkLabel(); ?></a>
                <?php
            }
            if (strlen($longMsg = $exception->getLongMsg())) {
                echo '<br><br>' . $longMsg;
            }
        }
        ?>
    </p>
    <hr>
    Trace:
    <pre class="exception-trace"><?php
        echo $exception->getTraceAsString();
    ?></pre>
</div>

<?php if (!empty($recoveryLink)) { ?>
    <p class="text-center">
        <a href="<?php echo DUPX_U::esc_url($recoveryLink); ?>" class="default-btn" target="_parent">
            <i class="fas fa-undo-alt"></i> Restore Recovery Point
        </a> 
    </p>
<?php } ?>

<div style="text-align:center; margin:10px auto 0px auto">
    <?php $url = DUPX_Constants::DUP_SITE_URL . 'contact/?form=1'; ?>
    <i style='font-size:11px'>See online help for more details at <a href='<?php echo DUPX_U::esc_attr($url); ?>' target='_blank'>duplicator.com</a></i>
</div>