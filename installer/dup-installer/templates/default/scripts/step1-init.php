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
?>
<script>
    const urlNewInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_URL_NEW)); ?>;
    const pathNewInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_PATH_NEW)); ?>;
    const exeSafeModeInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_SAFE_MODE)); ?>;
    const htConfigInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_HTACCESS_CONFIG)); ?>;
    const htConfigWrapperId = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_HTACCESS_CONFIG)); ?>;
    const otConfigInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_OTHER_CONFIG)); ?>;
    const otConfigWrapperId = <?php echo SnapJson::jsonEncode($paramsManager->getFormWrapperId(PrmMng::PARAM_OTHER_CONFIG)); ?>;
    const archiveEngineInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_ARCHIVE_ENGINE)); ?>;
    const validationShowInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_VALIDATION_SHOW_ALL)); ?>;
    const acceptContinueInputId = <?php echo SnapJson::jsonEncode($paramsManager->getFormItemId(PrmMng::PARAM_ACCEPT_TERM_COND)); ?>;

    $(document).ready(function () {
        let validateArea = $('#validate-area');
        let validateAreaHeader = $('#validate-area-header');
        let overviewAreaHeader = $('#overview-area-header');
        let basicSetupAreaHeader = $('#base-setup-area-header');
        let optionsAreaHeader = $('#options-area-header');
        let validateNoResult = validateArea.find('#validate-no-result');
        let stepActions = $('.bottom-step-action');
        let step1Form = $('#s1-input-form');

        DUPX.beforeUnloadCheck(true);

        DUPX.onSafeModeSwitch = function ()
        {
            var safeObj = $('#' + exeSafeModeInputId)
            var mode = safeObj ? parseInt(safeObj.val()) : 0;
            var htWr = $('#' + htConfigWrapperId);
            var otWr = $('#' + otConfigWrapperId);

            switch (mode) {
                case 1:
                case 2:
                    htWr.find('#' + htConfigInputId + '_0').prop("checked", true);
                    htWr.find('input').prop("disabled", true);
                    otWr.find('#' + otConfigInputId + '_0').prop("checked", true);
                    otWr.find('input').prop("disabled", true);
                    break;
                case 0:
                default:
                    htWr.find('input').prop("disabled", false);
                    otWr.find('input').prop("disabled", false);
                    break;
            }
            console.log("mode set to" + mode);
        };

        DUPX.blinkAnimation = function (id, duration= 500, steps = 1)
        {
            for (var i = 0; i < steps; i++) {
                $(`#${id}`).fadeOut(duration);
                $(`#${id}`).fadeIn(duration);
            }
        };

        DUPX.toggleSetupType = function ()
        {
            var val = $("input:radio[name='setup_type']:checked").val();
            $('div.s1-setup-type-sub').hide();
            $('#s1-setup-type-sub-' + val).show(200);
        };

        /**
         * Sets the focus to the next available input as needed. */
        DUPX.autoFocusInput = function ()
        {
            var $host = $('#param_item_dbhost');
            var $name = $('#param_item_dbname');
            var $user = $('#param_item_dbuser');
            var $pass = $('#param_item_dbpass');

            function _setFocus($input) {
                if ($input && $input.val() !== undefined && $input.val().length === 0) {
                    $input.focus();
                }
            }
            _setFocus($pass);
            _setFocus($user);
            _setFocus($name);
            _setFocus($host);
        }

        /**
         * Accetps Usage Warning */
        DUPX.acceptWarning = function (agreeMsg)
        {
            if ($("#" + acceptContinueInputId).is(':checked')) {
                $("#s1-deploy-btn").removeAttr("disabled");
                $("#s1-deploy-btn").removeAttr("title");
            } else {
                $("#s1-deploy-btn").attr("disabled", "true");
                $("#s1-deploy-btn").attr("title", agreeMsg);
            }
        };

        DUPX.setPageActions = function (inputActions) {
            let actions = $.extend({}, {
                'error': false,
                'validate': false,
                'hwarn': false,
                'next': false
            }, inputActions);

            stepActions.addClass('no-display');
            if (actions.next) {
                stepActions.filter("#next_action").removeClass('no-display');
            }
            if (actions.validate) {
                stepActions.filter("#validate_action").removeClass('no-display');
            }
            if (actions.hwarn) {
                stepActions.filter("#hard_warning_action").removeClass('no-display');
            }
            if (actions.error) {
                stepActions.filter("#error_action").removeClass('no-display');
            }
        }

        DUPX.openValidateArea = function () {
            if (validateAreaHeader.hasClass('open')) {
                validateAreaHeader.trigger('click');
            }
        }

        DUPX.closeValidateArea = function () {
            if (validateAreaHeader.hasClass('close')) {
                validateAreaHeader.trigger('click');
            }
        }

        DUPX.openBasicSetupArea = function () {
            if (basicSetupAreaHeader.hasClass('open')) {
                basicSetupAreaHeader.trigger('click');
            }
        }

        DUPX.closeBasicSetupArea = function () {
            if (basicSetupAreaHeader.hasClass('close')) {
                basicSetupAreaHeader.trigger('click');
            }
        }

        DUPX.closeOptionsSetupArea = function () {
            if (optionsAreaHeader.hasClass('close')) {
                optionsAreaHeader.trigger('click');
            }
        }

        DUPX.resetValidationResult = function () {
            DUPX.setValidationBadge('#validate-global-badge-status', false);
            $('.database-setup-title').removeClass('warning');
            $('.database-setup-title i.fas.fa-database').show();
            validateArea.find('#validation-result').empty().append(validateNoResult);
        }

        DUPX.autoUpdateOnMainChanges = function () {
            var originalUrlMainVal = $('#' + urlNewInputId).val();
            var urlRegex = new RegExp('^' + originalUrlMainVal, '');

            $('.auto-updatable').each(function () {
                $(this).data('original-default-value', $(this).find('input').val());
            });

            $('#' + urlNewInputId).bind("keyup change", function () {
                var newUrlVal = $(this).val().replace(/\/$/, '');
                $('.auto-updatable.autoupdate-enabled[data-auto-update-from-input="' + urlNewInputId + '"]').each(function () {
                    let originalVal = $(this).data('original-default-value');
                    $(this).find('input').val(originalVal.replace(urlRegex, newUrlVal));
                });
            });

            var orginalPathMainVal = $('#' + pathNewInputId).val();
            var pathRegex = new RegExp('^' + orginalPathMainVal, '');

            $('#' + pathNewInputId).bind("keyup change", function () {
                var newPathlVal = $(this).val().replace(/\/$/, '');
                $('.auto-updatable.autoupdate-enabled[data-auto-update-from-input="' + pathNewInputId + '"]').each(function () {
                    let originalVal = $(this).data('original-default-value');
                    $(this).find('input').val(originalVal.replace(pathRegex, newPathlVal));
                });
            });
        };

        DUPX.onValidateResult = function (validateData) {
            validateNoResult.detach();
            validateArea.find('#validation-result').empty().append(validateData.htmlResult);
            validateArea.find("*[data-type='toggle']").click(DUPX.toggleClick);
            DUPX.setValidationBadge('#validate-global-badge-status', validateData.mainBagedClass);

            if (validateData.categoriesLevels.database == 0) {
                $('.database-setup-title').addClass('warning');
                $('.database-setup-title i.fas.fa-database').hide();
                DUPX.openBasicSetupArea();
            } else {
                DUPX.closeBasicSetupArea();
            }
            DUPX.closeOptionsSetupArea();

            switch (validateData.mainLevel) {
                case <?php echo DUPX_Validation_abstract_item::LV_PASS; ?>:
                case <?php echo DUPX_Validation_abstract_item::LV_GOOD; ?>:
                    DUPX.openValidateArea();
                    DUPX.setPageActions({'next': true});
                    break;
                case <?php echo DUPX_Validation_abstract_item::LV_SOFT_WARNING; ?>:
                    DUPX.openValidateArea();
                    DUPX.setPageActions({'next': true});
                    break;
                case <?php echo DUPX_Validation_abstract_item::LV_HARD_WARNING; ?>:
                    DUPX.openValidateArea();
                    DUPX.setPageActions({'hwarn': true, 'next': true});
                    break;
                case <?php echo DUPX_Validation_abstract_item::LV_FAIL; ?>:
                default:
                    DUPX.openValidateArea();
                    DUPX.setPageActions({'error': true, 'validate': true});
            }
        };

        DUPX.reavelidateOnChangeAction = function (oldValue, obj) {
            if (obj == null || obj.val() !== oldValue) {
                DUPX.resetValidationResult();
                DUPX.setPageActions({'validate': true});
            }
            return (obj == null ? true : obj.val());
        }

        DUPX.revalidateOnChange = function () {
            $('.revalidate-on-change').each(function () {
                $(this).find('input, select, textarea').each(function () {
                    if ($(this).is(':checkbox, :radio')) {
                        $(this).bind("click", function () {
                            DUPX.reavelidateOnChangeAction(false, $(this));
                        });
                    } else {
                        var oldValue = $(this).val();
                        $(this).bind("keyup change", function () {
                            oldValue = DUPX.reavelidateOnChangeAction(oldValue, $(this));
                        });
                    }
                });
            });
        }

        //INIT Routines
        $("*[data-type='toggle']").click(DUPX.toggleClick);
        $(".tabs").tabs();

        DUPX.acceptWarning();
        DUPX.toggleSetupType();

        DUPX.autoUpdateOnMainChanges();
        DUPX.revalidateOnChange();
        DUPX.autoFocusInput();

        validateArea.on("click", '#' + validationShowInputId, function () {
            if ($(this).is(":checked")) {
                validateArea.removeClass('show-warnings').addClass('show-all');
            } else {
                validateArea.removeClass('show-all').addClass('show-warnings');
            }
        });

        $('#s1-deploy-btn').click(function () {
            DUPX.confirmDialog.loadAndOpen();
        });

        $('#validate-button').click(function () {
            DUPX.sendParamsStep1(step1Form, function () {
                <?php
                // reload page to reinit interface
                $onValidatePrams = array(
                    PrmMng::PARAM_CTRL_ACTION => 'ctrl-step1',
                    DUPX_Security::CTRL_TOKEN => DUPX_CSRF::generate('ctrl-step1'),
                    PrmMng::PARAM_STEP_ACTION => DUPX_CTRL::ACTION_STEP_ON_VALIDATE
                );
                ?>
                let onValidateParam = <?php echo SnapJson::jsonEncode($onValidatePrams); ?>;
                DUPX.redirect(DUPX.dupInstallerUrl, 'post', onValidateParam);
            });
        });

        $('.s1-switch-template-btn').click(function () {
            let tplButton = $(this);
           
            if (tplButton.hasClass('active') || !tplButton.data('template')) {
                return;
            } else if (!tplButton.hasClass('active'))  {
                tplButton.append('<i class="fas fa-circle-notch fa-spin"></i>');
                tplButton.addClass('active');
            }
            
            <?php
            $switchPrams = array(
                PrmMng::PARAM_CTRL_ACTION => 'ctrl-step1',
                DUPX_Security::CTRL_TOKEN => DUPX_CSRF::generate('ctrl-step1'),
                PrmMng::PARAM_STEP_ACTION => DUPX_CTRL::ACTION_STEP_SET_TEMPLATE,
            );
            ?>
           
            let redirectParam = <?php echo SnapJson::jsonEncode($switchPrams); ?>;
            redirectParam[<?php echo SnapJson::jsonEncode(PrmMng::PARAM_TEMPLATE); ?>] = tplButton.data('template');
            DUPX.redirect(DUPX.dupInstallerUrl, 'post', redirectParam);
        });

        validateArea.on("click", ".test-title", function () {
            let content = $(this).closest('.test-wrapper').find('.test-content');
            let faIcon = $(this).find('> .fa');
            if (content.hasClass('no-display')) {
                faIcon.removeClass('fa-caret-right').addClass('fa-caret-down');
                content.removeClass('no-display');
            } else {
                faIcon.removeClass('fa-caret-down').addClass('fa-caret-right');
                content.addClass('no-display');
            }
        });

        <?php if (DUPX_Validation_manager::validateOnLoad()) { ?>
            DUPX.initialValidateAction(DUPX.onValidateResult, true, true);
        <?php } ?>
    });
</script>
<?php
dupxTplRender('scripts/step1-deploy');
