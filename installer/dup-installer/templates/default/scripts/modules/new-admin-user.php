<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();
?>
<script>
    const createNewInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_WP_ADMIN_CREATE_NEW)); ?>;
    $(document).ready(function () {
        let fieldsWrapper = $("#new-admin-fields-wrapper");
        $('#' + createNewInputId).change(function () {
            if ($(this).prop('checked')) {
                fieldsWrapper.find('.param-wrapper').removeClass('param-wrapper-disabled').addClass('param-wrapper-enabled');
                fieldsWrapper.find('.new-admin-field, .new-admin-field > input').prop('disabled', false);
            } else {
                fieldsWrapper.find('.param-wrapper').removeClass('param-wrapper-enabled').addClass('param-wrapper-disabled');
                fieldsWrapper.find('.new-admin-field, .new-admin-field > input').prop('disabled', true).val('').trigger('keyup').trigger('blur');
            }
        });
    });
</script>

