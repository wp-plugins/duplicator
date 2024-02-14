<?php

/**
 *
 * @package templates/default
 */

use Duplicator\Libs\Snap\SnapJson;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<script>
    DUPX.confirmDialog = {
        content: null,
        advCheckCheckbox: null,
        loadAndOpen: function () {
            const confirmDialogAction = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::ACTION_PROCEED_CONFIRM_DIALOG); ?>;
            const confirmDialogToken = <?php echo SnapJson::jsonEncode(DUPX_Ctrl_ajax::generateToken(DUPX_Ctrl_ajax::ACTION_PROCEED_CONFIRM_DIALOG)); ?>;

            let thisObj = this;

            if (this.content !== null) {
                this.content.remove();
                this.content = null;
            }

            DUPX.StandardJsonAjaxWrapper(
                confirmDialogAction,
                confirmDialogToken,
                {},
                function (data) {
                    thisObj.content = $(data.actionData);
                    thisObj.advCheckCheckbox = thisObj.content.find("#dialog-adv-confirm-check");
                    thisObj.open();
                }
            );
        },
        open: function () {            
            if (this.content.length == 0) {
                return;
            }

            let thisObj = this;

            this.content.dialog({
                resizable: false,
                height: "auto",
                width: 700,
                modal: true,
                position: {my: 'top', at: 'top+150'},
                buttons: {
                    "OkButton": {
                        text: "OK",
                        id: "db-install-dialog-confirm-button",
                        click: function () {
                            if (thisObj.advCheckCheckbox.length > 0 && !thisObj.advCheckCheckbox.is(":checked")) {
                                return;
                            }
                            $(this).dialog("close");
                            DUPX.deployStep1();
                        }
                    },
                    "CancelButton": {
                        text: "Cancel",
                        id: "db-install-dialog-cancel-button",
                        click: function () {
                            $(this).dialog("close");
                        }
                    }
                }
            });
        }
    };
</script>