<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();

$nextStepPrams = array(
    PrmMng::PARAM_CTRL_ACTION => 'ctrl-step4',
    DUPX_Security::CTRL_TOKEN               => DUPX_CSRF::generate('ctrl-step4')
);

dupxTplRender('scripts/modules/new-admin-user');
?>
<script>
    DUPX.runSiteUpdate = function ()
    {
        DUPX.sendParamsStep3($('#s3-input-form'), function () {
            DUPX.siteProcessingReplaceData(true, function () {
                DUPX.finalTests.test(function () {
                    DUPX.redirect(DUPX.dupInstallerUrl, 'post', <?php echo SnapJson::jsonEncode($nextStepPrams); ?>);
                });
            });
        });
    };

    DUPX.beforeUnloadCheck(true);

    var searchReplaceIndex = 1;

    /**
     * Adds a search and replace line         */
    DUPX.addSearchReplace = function ()
    {
        $("#search-replace-table").append("<tr valign='top' id='search-" + searchReplaceIndex + "'>" +
                "<td style='width:80px;padding-top:20px'>Search:</td>" +
                "<td style='padding-top:20px'>" +
                "<input class=\"w95\" type='text' name='search[]' style='margin-right:5px' />" +
                "<a href='javascript:DUPX.removeSearchReplace(" + searchReplaceIndex + ")'><i class='fa fa-minus-circle'></i></a>" +
                "</td>" +
                "</tr>" +
                "<tr valign='top' id='replace-" + searchReplaceIndex + "'>" +
                "<td>Replace:</td>" +
                "<td>" +
                "<input class=\"w95\" type='text' name='replace[]' />" +
                "</td>" +
                "</tr> ");

        searchReplaceIndex++;
    };

    /**
     * Removes a search and replace line      */
    DUPX.removeSearchReplace = function (index)
    {
        $("#search-" + index).remove();
        $("#replace-" + index).remove();
    };

//DOCUMENT LOAD
    $(document).ready(function () {
        $("#tabs").tabs({
            create: function (event, ui) {
                $("#tabs").removeClass('no-display');
            }
        });

        $("*[data-type='toggle']").click(DUPX.toggleClick);

        $('.strength-pwd-check .pwd-simulation').password({
            'closestSelector': '.input-container',
            'minimumLength': <?php echo DUPX_Constants::MIN_NEW_PASSWORD_LEN; ?>,
            'enterPass': 'Type your password',
            'shortPass': 'The password is too short',
            'steps': {
                13: 'Weak',
                50: 'Good',
                80: 'Strong'
            }
        });

        $('input[type=radio][name=replace_mode]').change(function () {
            if (this.value == 'mapping') {
                $("#subsite-map-container").show();
                $("#new-url-container").hide();
            } else if (this.value == 'legacy') {
                $("#new-url-container").show();
                $("#subsite-map-container").hide();
            }
        });

        $('#param_item_mode_chunking').change(function () {
            if (this.value == '2') {
                $('#progress-area .progress-perc').show();
            } else {
                $('#progress-area .progress-perc').hide();
            }
        });
        $('#param_item_mode_chunking').change();

        // Sync new urls link
        var inputs_new_urls = $(".sync_url_new");
        inputs_new_urls.keyup(function () {
            inputs_new_urls.val($(this).val());
        });
    });
</script>