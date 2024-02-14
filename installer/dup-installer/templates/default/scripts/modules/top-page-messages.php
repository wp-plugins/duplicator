<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<script>
    DUPX.topMessages = {
        wrapper: null,
        empty: function () {
            this.wrapper.empty();
        },
        add: function (html) {
            this.wrapper.html(html);
        }
    };

    $(document).ready(function () {
        DUPX.topMessages.wrapper = $('#page-top-messages');
    });
</script>