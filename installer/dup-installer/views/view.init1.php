<?php
defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/** IDE HELPERS */
/* @var $GLOBALS['DUPX_AC'] DUPX_ArchiveConfig */

$page_url = DUPX_HTTP::get_request_uri();

$security = DUPX_Security::getInstance();

if ($security->getSecurityType() == DUPX_Security::SECURITY_NONE) {
    DUPX_HTTP::post_with_html($page_url, array(
        'action_step' => '1',
        'csrf_token' => DUPX_CSRF::generate('step1')
    ));
    exit;
}

//POSTBACK: valid security
if ($security->securityCheck()) {
    DUPX_HTTP::post_with_html($page_url,
        array(
            'action_step' => '1',
            'csrf_token' => DUPX_CSRF::generate('step1'),
            'secure-pass' => $_POST['secure-pass'],
            'secure-archive' => $_POST['secure-archive']
        )
    );
    exit;
}
$page_err = isset($_POST['secure-try'])  ? 1 : 0;

switch (DUPX_Security::getInstance()->getSecurityType()) {
    case DUPX_Security::SECURITY_PASSWORD:
        $errorMsg = 'Invalid Password! Please try again...';
        break;
    case DUPX_Security::SECURITY_ARCHIVE:
        $errorMsg = 'Invalid Archive name! Please try again...';
        break;
    case DUPX_Security::SECURITY_NONE:
    default:
        $errorMsg = '';
        break;
}

$css_why_display = ($GLOBALS['DUPX_STATE']->mode === DUPX_InstallerMode::OverwriteInstall) ? "" : "no-display";
$archive_name    = isset($_POST['secure-archive']) ? $_POST['secure-archive'] : '';

?>

<style>
    div#content {min-height: 250px}
    div#content-inner {min-height: 250px}
    form#i1-pass-form {min-height: 250px}
    div.footer-buttons {position: static}
</style>

<!-- =========================================
VIEW: STEP 0 - PASSWORD -->
<form method="post" id="i1-pass-form" class="content-form"  data-parsley-validate="" autocomplete="off">
    <input type="hidden" name="view" value="secure" />
    <input type="hidden" name="csrf_token" value="<?php echo DUPX_CSRF::generate('secure'); ?>">
    <input type="hidden" name="secure-try" value="1" />

    <div class="hdr-main">
        Installer Security
    </div>

    <?php if ($page_err) : ?>
        <div class="error-pane">
            <p><?php echo $errorMsg; ?></p>
        </div>
    <?php endif; ?>

    <div class="margin-top-0 margin-bottom-2">
        <div class="text-right" >
            <a href="javascript:void(0)" id="pass-quick-link" class="link-style" onclick="jQuery('#pass-quick-help-info').toggleClass('no-display');" >
                Why do I see this screen?
            </a>
        </div>
        <div id="pass-quick-help-info" class="box info <?php echo $css_why_display?>">
            This screen will show under the following conditions:
            <ul>
                <li>
                    <b>Password Protection:</b> If the file was password protected when it was created then the password input below should
                    be enabled.  If the input is disabled then no password was set.
                </li>
                <li>
                    <b>Simple Installer Name:</b> If no password is set and you are performing an <i class="maroon">"Overwrite Install"</i> on a public server
                    (non localhost) without a secure installer.php file name (i.e. [hash]_installer.php).  Then users will need to enter the archive file for
                    a valid security check.  If the Archive File Name input is disabled then it can be ignored.
                </li>
            </ul>
        </div>
    </div>

    <div id="wrapper_item_secure-pass" class="param-wrapper margin-bottom-2">
        <label for="secure-pass">Password:</label>
        <?php
        $attrs = array();
        if ($security->getSecurityType() == $security::SECURITY_PASSWORD) {
            $attrs['required'] = 'required';
        } else {
            $attrs['placeholder'] = 'Password not enabled';
            $attrs['disabled'] = 'disabled';
        }
        DUPX_U_Html::inputPasswordToggle('secure-pass', 'secure-pass', array(), $attrs);
        ?>
    </div>

    <?php if ($GLOBALS['DUPX_STATE']->mode === DUPX_InstallerMode::OverwriteInstall) : ?>
        <div id="wrapper_item_secure-archive" class="param-wrapper margin-bottom-4">
            <label for="param_item_secure-archive">Archive File Name:</label>
            <div>
                <input
                    type="text"
                    id="param_item_secure-archive"
                    name="secure-archive"
                    value="<?php echo $archive_name; ?>"
                    class="input-item"
                    placeholder="example: [full-unique-name]_archive.zip"
                    <?php echo ($security->getSecurityType() == $security::SECURITY_ARCHIVE ? '' : 'disabled'); ?>>
                <div class="sub-note">
                    <?php DUPX_View_Funcs::helpLink('secure', 'How to get archive file name?'); ?>
                </div>
            </div>
        </div>
    <?php endif;?>

    <div class="footer-buttons" >
        <div class="content-center" >
            <button type="submit" name="secure-btn" id="secure-btn" class="default-btn" onclick="DUPX.checkPassword()">
                Submit
            </button>
        </div>
    </div>
</form>

<script>
    /**
     * Submits the password for validation
     */
    DUPX.checkPassword = function ()
    {
        var $form = $('#i1-pass-form');
        $form.parsley().validate();
        if (!$form.parsley().isValid()) {
            return;
        }
        $form.submit();
    }

    //DOCUMENT LOAD
    $(document).ready(function()
    {
        $('#secure-pass').focus();
        $('#param_item_secure-archive').focus();
    });
</script>
<!-- END OF VIEW INIT 1 -->