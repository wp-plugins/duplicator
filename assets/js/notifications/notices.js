/**
 * Duplicator Dismissible Notices.
 *
 */

'use strict';

var DupAdminNotices = window.DupAdminNotices || (function (document, window, $) {

    /**
     * Public functions and properties.
     */
    var app = {

        /**
         * Start the engine.
         */
        init: function () {

            $(app.ready);
        },

        /**
         * Document ready.
         */
        ready: function () {

            app.events();
        },

        /**
         * Dismissible notices events.
         */
        events: function () {
            $(document).on(
                'click',
                '.dup-notice .notice-dismiss, .dup-notice .dup-notice-dismiss',
                app.dismissNotice
            );

            $(document).on(
                'click',
                '.dup-notice .dup-multi-notice a[data-step]',
                function (e) {
                    e.preventDefault();
                    var target = $(this).attr('data-step');
                    console.log(target)
                    if (target) {
                        var notice = $(this).closest('.dup-multi-notice');
                        var review_step = notice.find('.dup-multi-notice-step-' + target);
                        if (review_step.length > 0) {
                            notice.find('.dup-multi-notice-step:visible').fadeOut(function () {
                                review_step.fadeIn();
                            });
                        }
                    }
                }
            );
        },

        /**
         * Dismiss notice event handler.
         *
         * @param {object} e Event object.
         * */
        dismissNotice: function (e) {

            $.post(dup_admin_notices.ajax_url, {
                action: 'dup_notice_dismiss',
                nonce: dup_admin_notices.nonce,
                id: ($(this).closest('.dup-notice').attr('id') || '').replace('dup-notice-', ''),
            });

            $(this).closest('.dup-notice').fadeOut();
        }
    };

    return app;

}(document, window, jQuery));

// Initialize.
DupAdminNotices.init();
