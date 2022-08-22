<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Installer\Core\Params\Descriptors\ParamDescUsers;
use Duplicator\Installer\Core\Params\PrmMng;
use Duplicator\Installer\Core\Params\Items\ParamFormTables;
use Duplicator\Libs\Snap\SnapJson;

$paramsManager = PrmMng::getInstance();
?>
<script>
    const wpUserNameInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_WP_ADMIN_NAME)); ?>;
    const wpPwdInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_WP_ADMIN_PASSWORD)); ?>;
    const wpMailInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_WP_ADMIN_MAIL)); ?>;
    const archiveEngineActionWraper = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_ARCHIVE_ACTION)); ?>;
    const extractSkipModeWrapper = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_ARCHIVE_ENGINE_SKIP_WP_FILES)); ?>;

    const autoCleanInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_AUTO_CLEAN_INSTALLER_FILES)); ?>;
    const tablesItemClass = <?php echo SnapJson::jsonEncode(
        ($paramsManager->getFormItemId(PrmMng::PARAM_DB_TABLES) . ParamFormTables::TABLE_ITEM_POSTFIX)
    ); ?>;
    const tablesNameClass = <?php echo SnapJson::jsonEncode(
        ($paramsManager->getFormItemId(PrmMng::PARAM_DB_TABLES) . ParamFormTables::TABLE_NAME_POSTFIX_TNAME)
    ); ?>;
    const tablesNameInputName = <?php echo SnapJson::jsonEncode(PrmMng::PARAM_DB_TABLES); ?>;
    const tablesExtractClass = <?php echo SnapJson::jsonEncode(
        ($paramsManager->getFormItemId(PrmMng::PARAM_DB_TABLES) . ParamFormTables::TABLE_NAME_POSTFIX_EXTRACT)
    ); ?>;
    const tablesExtractInputName = <?php echo SnapJson::jsonEncode(PrmMng::PARAM_DB_TABLES . ParamFormTables::TABLE_NAME_POSTFIX_EXTRACT); ?>;
    const tablesReplaceClass = <?php echo SnapJson::jsonEncode(
        ($paramsManager->getFormItemId(PrmMng::PARAM_DB_TABLES) . ParamFormTables::TABLE_NAME_POSTFIX_REPLACE)
    ); ?>;
    const tablesReplaceInputName = <?php echo SnapJson::jsonEncode(PrmMng::PARAM_DB_TABLES . ParamFormTables::TABLE_NAME_POSTFIX_REPLACE); ?>;

    const installTypeInputWrapper = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_INST_TYPE)); ?>;
    const userModeWrapper = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_USERS_MODE)); ?>;
    const tablePrefixWrapper = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_DB_TABLE_PREFIX)); ?>;
    const tablePrefixInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_DB_TABLE_PREFIX)); ?>;

    DUPX.setTablesFormData = function(formData) {
        let tablesList = [];

        $('.' + tablesItemClass).each(function() {
            let newObj = {
                'name'    : $(this).find('.' + tablesNameClass).val(),
                'extract' : $(this).find('.' + tablesExtractClass).is(':checked'),
                'replace' : $(this).find('.' + tablesReplaceClass).is(':checked')
            };

            tablesList.push(newObj);
        });

        delete formData[tablesExtractInputName];
        delete formData[tablesReplaceInputName];
        formData[tablesNameInputName] = JSON.stringify(tablesList);
        return formData;
    };

    DUPX.sendParamsStep1 = function(form, setParamOkCallback) {
        DUPX.pageComponents.resetTopMessages().showProgress({
            'title': 'Parameters Update',
            'bottomText': '<i>Keep this window open.</i><br/>' +
                '<i>This can take several minutes.</i>'
        });
        let setParamAction = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_SET_PARAMS_S1); ?>;
        let setParamToken = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_SET_PARAMS_S1)); ?>;

        var formData = form.serializeForm();

        DUPX.StandarJsonAjaxWrapper(
            setParamAction,
            setParamToken,
            formData,
            function(data) {
                if (data.actionData.isValid) {
                    if (typeof setParamOkCallback === "function") {
                        setParamOkCallback();
                    }
                } else {
                    DUPX.pageComponents.showContent();
                    DUPX.topMessages.add(data.actionData.nextStepMessagesHtml);
                }

                return true;
            },
            DUPX.ajaxErrorDisplayHideError
        );
    };

    DUPX.sendParamsStep2 = function(form, setParamOkCallback) {
        DUPX.pageComponents.resetTopMessages().showProgress({
            'title': 'Parameters Update',
            'bottomText': '<i>Keep this window open.</i><br/>' +
                '<i>This can take several minutes.</i>'
        });
        let setParamAction = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_SET_PARAMS_S2); ?>;
        let setParamToken = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_SET_PARAMS_S2)); ?>;

        var formData = form.serializeForm();

        formData = DUPX.setTablesFormData(formData);

        DUPX.StandarJsonAjaxWrapper(
            setParamAction,
            setParamToken,
            formData,
            function(data) {
                if (data.actionData.isValid) {
                    if (typeof setParamOkCallback === "function") {
                        setParamOkCallback();
                    }
                } else {
                    DUPX.pageComponents.showContent();
                    DUPX.topMessages.add(data.actionData.nextStepMessagesHtml);
                }

                return true;
            },
            DUPX.ajaxErrorDisplayHideError
        );
    };

    DUPX.sendParamsStep3 = function(form, setParamOkCallback) {
        let setParamAction = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_SET_PARAMS_S3); ?>;
        let setParamToken = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_SET_PARAMS_S3)); ?>;

        //Validation
        var wp_username = $.trim($("#" + wpUserNameInputId).val()).length || 0;
        var wp_password = $.trim($("#" + wpPwdInputId).val()).length || 0;
        var wp_mail = $.trim($("#" + wpMailInputId).val()).length || 0;

        if (wp_username >= 1) {
            if (wp_username < 4) {
                alert("The New Admin Account 'Username' must be four or more characters");
                return false;
            } else if (wp_password < 6) {
                alert("The New Admin Account 'Password' must be six or more characters");
                return false;
            } else if (wp_mail === 0) {
                alert("The New Admin Account 'mail' is required");
                return false;
            }
        }

        var nonHttp = false;
        var failureText = '';

        /* IMPORTANT - not trimming the value for good - just in the check */
        $('input[name="search[]"]').each(function() {
            var val = $(this).val();
            if (val.trim() != "") {
                if (val.length < 3) {
                    failureText = "Custom search fields must be at least three characters.";
                }
                if (val.toLowerCase().indexOf('http') != 0) {
                    nonHttp = true;
                }
            }
        });

        $('input[name="replace[]"]').each(function() {
            var val = $(this).val();
            if (val.trim() != "") {
                // Replace fields can be anything
                if (val.toLowerCase().indexOf('http') != 0) {
                    nonHttp = true;
                }
            }
        });

        if (failureText != '') {
            alert(failureText);
            return false;
        }

        if (nonHttp) {
            if (confirm('One or more custom search and replace strings are not URLs.  Are you sure you want to continue?') == false) {
                return false;
            }
        }

        if ($('input[type=radio][name=replace_mode]:checked').val() == 'mapping') {
            $("#new-url-container").remove();
        } else if ($('input[type=radio][name=replace_mode]:checked').val() == 'legacy') {
            $("#subsite-map-container").remove();
        }

        DUPX.pageComponents.resetTopMessages().showProgress({
            'title': 'Parameters Update',
            'bottomText': '<i>Keep this window open.</i><br/>' +
                '<i>This can take several minutes.</i>'
        });

        var formData = form.serializeForm();

        DUPX.StandarJsonAjaxWrapper(
            setParamAction,
            setParamToken,
            formData,
            function(data) {
                if (data.actionData.isValid) {
                    if (typeof setParamOkCallback === "function") {
                        setParamOkCallback();
                    }
                } else {
                    DUPX.pageComponents.showContent();
                    DUPX.topMessages.add(data.actionData.nextStepMessagesHtml);
                }

                return true;
            },
            DUPX.ajaxErrorDisplayHideError
        );
    };

    DUPX.setAutoCleanFiles = function() {
        DUPX.pageComponents.resetTopMessages().showProgress({
            'title': 'Send migration data',
            'bottomText': '<i>Keep this window open.</i>'
        });
        let setParamAction = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_SET_AUTO_CLEAN_FILES); ?>;
        let setParamToken = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_SET_AUTO_CLEAN_FILES)); ?>;

        var formData = {
            <?php echo SnapJson::jsonEncode(PrmMng::PARAM_AUTO_CLEAN_INSTALLER_FILES); ?>: $('#' + autoCleanInputId).prop('checked')
        };

        DUPX.StandarJsonAjaxWrapper(
            setParamAction,
            setParamToken,
            formData,
            function(data) {
                DUPX.pageComponents.showContent();
                if (!data.actionData.isValid) {
                    DUPX.topMessages.add(data.actionData.nextStepMessagesHtml);
                }
                return true;
            },
            function(result, textStatus, jqXHR) {
                if (jqXHR.status === 404) {
                    // ON 404 installer files are already removed on first login
                    DUPX.pageComponents.showContent();
                } else {
                    DUPX.ajaxErrorDisplayHideError(result, textStatus, jqXHR);
                }
            }
        );
    };

    $(document).ready(function() {
        // prepare per animation
        if ($('#overwrite-subsite-on-multisite-wrapper').hasClass('no-display')) {
            $('#overwrite-subsite-on-multisite-wrapper').removeClass('no-display').hide();
        }

        $('.select-all-import').click(function () {
            let node  = $(this);
            let tbody = $('#plugins_list_table_selector').find('tbody');

            tbody.find('.' + tablesExtractClass).prop('checked',node.is(':checked'));

            $(this).closest('thead').find('.select-all-replace')
                .prop('checked', !node.is(':checked')).trigger('click')
                .prop('disabled', !node.is(':checked'));

            tbody.find('.' + tablesReplaceClass).prop('disabled', !node.is(':checked'));
        });

        $('.select-all-replace').click(function () {
            let node  = $(this);
            let tbody = $('#plugins_list_table_selector').find('tbody');

            tbody.find('.' + tablesReplaceClass).prop('checked',node.is(':checked'));
        });

        $('#' + installTypeInputWrapper + ' input[type=radio]').change(function() {
            let selectedVal = $(this).val();

            $('#overview-description-wrapper .overview-description').removeClass('no-display').hide();
            $('#overview-description-wrapper .install-type-' + selectedVal).fadeIn("slow");
        });

        $('.param-form-type-tablessel .' + tablesExtractClass).each(function() {
            let extractInput = $(this);
            let replaceInput = extractInput.closest('.' + tablesItemClass).find('.' + tablesReplaceClass);

            extractInput.change(function() {
                if (extractInput.is(':checked')) {
                    replaceInput.prop('disabled', false);
                    replaceInput.prop('checked', true);
                } else {
                    replaceInput.prop('disabled', true);
                    replaceInput.prop('checked', false);
                }
            });
        });

        $('.param-form-type-bgroup').each(function() {
            let wrapperObj = $(this);
            let buttons = wrapperObj.find('button');
            let inputObj = wrapperObj.find('input[type="hidden"]');
            buttons.click(function() {
                buttons.removeClass('active');
                $(this).addClass('active');
                inputObj.val($(this).val()).trigger('change');
            });
        });


        $('#' + archiveEngineActionWraper + ', #' + extractSkipModeWrapper).each(function() {
            let paramWrapper = $(this);
            let noteWrapper = paramWrapper.find('.sub-note');

            paramWrapper.find('.input-item').change(function() {
                noteWrapper.find('.dynamic-sub-note').addClass('no-display');
                noteWrapper.find('.dynamic-sub-note-' + $(this).val()).removeClass('no-display');
            });
        });

        $('#' + autoCleanInputId).change(function() {
            DUPX.setAutoCleanFiles();
        });
    });
</script>