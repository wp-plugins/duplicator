<?php

/**
 *
 * @package templates/default
 *
 */

use Duplicator\Libs\Snap\SnapUtil;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

$archiveConfig = DUPX_ArchiveConfig::getInstance();
$open_section  = SnapUtil::filterInputDefaultSanitizeString(INPUT_GET, 'open_section');
?>
<!-- =========================================
HELP FORM -->
<div id="main-help">
    <div class="help-online"><br/>
        <i class="far fa-file-alt fa-sm"></i> For complete help visit <br/>
        <a href="https://snapcreek.com/support/docs/" target="_blank">Duplicator Migration and Backup Online Help</a> <br/>
        <small>The <sup class="hlp-pro-lbl">Pro</sup> tag indicates the feature is only available in Duplicator Pro</small>
    </div>

    <?php
    $step_pass_data = array(
        'open_section' => $open_section,
    );
    dupxTplRender('pages-parts/help/steps/security', $step_pass_data);
    dupxTplRender('pages-parts/help/steps/step-1', $step_pass_data);
    dupxTplRender('pages-parts/help/steps/step-2', $step_pass_data);
    dupxTplRender('pages-parts/help/steps/step-3', $step_pass_data);
    dupxTplRender('pages-parts/help/steps/step-4', $step_pass_data);
    dupxTplRender('pages-parts/help/addtional-help');
    ?>    
</div>

<script>
    $(document).ready(function ()
    {
        //Disable href for toggle types
        $("section.expandable .expand-header").click(function () {
            var section = $(this).parent();
            if (section.hasClass('open')) {
                section.removeClass('open').addClass('close');
            } else {
                section.removeClass('close').addClass('open');
            }
        });

<?php if (!empty($open_section)) { ?>
            $("html, body").animate({scrollTop: $('#<?php echo $open_section; ?>').offset().top}, 1000);
<?php } ?>

    });
</script>
<!-- END OF VIEW HELP -->
