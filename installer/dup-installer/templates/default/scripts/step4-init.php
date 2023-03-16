<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?><script>
    //DOCUMENT LOAD
    $(document).ready(function () {
        DUPX.beforeUnloadCheck(false);
        
        $('[data-go-step-one-url]').click(function () {
            document.location.href = decodeURIComponent($(this).data('go-step-one-url'));
        });
        
        //INIT Routines
        $("*[data-type='toggle']").click(DUPX.toggleClick);
        $("#tabs").tabs();
    });
</script>