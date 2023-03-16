<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;

use Duplicator\Libs\Snap\SnapJson;

?>
<script>
    $(document).ready(function () {
        
        DUPX.ajaxError = {
            wrapper: $('#ajaxerr-area'),
            tryAgainButton: $('#ajax-error-try-again'),
            preContent: $('#ajaxerr-data .pre-content'),
            htmlContent: $('#ajaxerr-data .html-content'),
            show: function () {
                this.wrapper.removeClass('no-display');
            },
            hide: function () {
                this.wrapper.addClass('no-display');
            },
            update: function (result, textStatus, jqXHR, tryAgainButtonCallback) {
                this.wrapper.find('.message').html(result.message);
                if (result.errorContent.pre.length) {
                    this.preContent.text(result.errorContent.pre).removeClass('no-display');
                } else {
                    this.preContent.addClass('no-display');
                }

                this.htmlContent.html(result.errorContent.html).removeClass('no-display');

                if (typeof tryAgainButtonCallback === "function") {
                    this.tryAgainButton.off().one('click', tryAgainButtonCallback).removeClass('no-display');
                } else {
                    this.tryAgainButton.off().addClass('no-display');
                }
            }
        };

        DUPX.ajaxErrorDisplayRestart = function (result, textStatus, jqXHR) {
            DUPX.pageComponents.showError(result, textStatus, jqXHR, function () {
                window.location.href = <?php echo SnapJson::jsonEncode(DUPX_Security::getInstance()->getBootUrl()); ?>;
            });
        };

        DUPX.ajaxErrorDisplayHideError = function (result, textStatus, jqXHR) {
            DUPX.pageComponents.showError(result, textStatus, jqXHR, function () {
                DUPX.pageComponents.showContent();
            });
        };

    });
</script>