<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;

$paramsManager = PrmMng::getInstance();

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
?>
<form method="post" id="i1-pass-form" class="content-form"  data-parsley-validate="" autocomplete="off" >
    <div id="pwd-check-fail" class="error-pane no-display">
        <p>
            <?php echo $errorMsg; ?>
        </p>
    </div>

    <div class="margin-top-0 margin-bottom-2">
        <div class="text-right" >
            <span id="pass-quick-link" class="link-style" onclick="jQuery('#pass-quick-help-info').toggleClass('no-display');" >
                <i>Why do I see this screen?</i>
            </span>
        </div>
        <div id="pass-quick-help-info" class="box info no-display">
            This screen will show under the following conditions:
            <br/><br/>

            <b><i class="fas fa-lock"></i> Password:</b> 
            If the installer was password protected when created then the password input below should be enabled. <br/>
            <small>If the input is disabled then no password was set during the build process.</small>
            <br/><br/>

            <b><i class="fas fa-shield-alt"></i> Secure-File:</b>
            If the archive file is on a public server under these conditions:
            <ul>
                <li>Running a basic installer name <i>(installer.php)</i> with no installer password.</li>
                <li>Running with the <i class="maroon">"Overwrite Install"</i>  method active and no installer password.</li>
            </ul>
            Validate the 'Archive File Name' input with the secure-file name it was created with <i>([name]_[hash]_[time]_archive.zip)</i>. <br/>
            <small>If the archive file name input is disabled or hidden then it can be ignored.</small>
        </div>
    </div>

    <div class="dupx-opts" >
        <?php
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_SECURE_PASS);
        $paramsManager->getHtmlFormParam(PrmMng::PARAM_SECURE_ARCHIVE_HASH);
        ?>
    </div>

    <div class="footer-buttons" >
        <div class="content-center" >
            <button type="submit" name="secure-btn" id="secure-btn" class="default-btn" >Submit</button>
        </div>
    </div>
</form>

<script>
    //DOCUMENT LOAD
    $(document).ready(function()
    {
        $('#param_item_secure-pass').focus();
        $('#param_item_secure-archive').focus();
    });
</script>