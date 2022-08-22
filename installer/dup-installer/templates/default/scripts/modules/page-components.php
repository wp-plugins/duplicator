<?php

/**
 *
 * @package templates/default
 *
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<script>
    DUPX.pageComponents = {
        pageContent: null,
        init: function () {
            this.pageContent = $('#main-content-wrapper');
        },
        resetTopMessages: function () {
            DUPX.topMessages.empty();
            return this;
        },
        showProgress: function (options) {
            this.pageContent.hide();
            DUPX.ajaxError.hide();
            DUPX.progress.show(options);
            return this;
        },
        showError: function (result, textStatus, jqXHR, tryAgainButtonCallback) {
            DUPX.ajaxError.update(result, textStatus, jqXHR, tryAgainButtonCallback);
            DUPX.progress.hide();
            this.pageContent.hide();
            DUPX.ajaxError.show();
            return this;
        },
        showContent: function () {
            DUPX.progress.hide();
            DUPX.ajaxError.hide();
            this.pageContent.show();
            return this;
        }
    };

    $(document).ready(function () {
        DUPX.pageComponents.init();
    });
</script>