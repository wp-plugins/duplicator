/**
 * Duplicator Dismissible Notices.
 *
 */

'use strict';

var DupExtraPlugins = window.DupExtraPlugins || (function (document, window, $) {

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
                'button.dup-extra-plugin-item[data-plugin]',
                function (e) {
                    e.preventDefault();

                    if ($(this).hasClass('disabled')) {
                        return;
                    }

                    let button          = $(this);
                    let status          = $(this).closest('.actions').find('.status').eq(0);
                    let statusLabel     = status.find('.status-label').eq(0)
                    let statusLabelText = statusLabel.html();
                    let buttonText      = $(this).html();

                    $(this).addClass('disabled');
                    $(this).html('Loading...');

                    $.post(
                        duplicator_extra_plugins.ajax_url,
                        {
                            action: 'duplicator_install_extra_plugin',
                            nonce: duplicator_extra_plugins.extra_plugin_install_nonce,
                            plugin: $(this).data('plugin'),
                        }
                    ).done(function (response) {
                        console.log(response);
                        if (response.success !== true) {
                            console.log("Plugin installed failed with message: " + response.data.message);
                            statusLabel.html('Failure');
                            statusLabel.addClass('status-installed');
                            button.fadeOut(300);

                            setTimeout(function () {
                                statusLabel.html(statusLabelText);
                                statusLabel.removeClass('status-installed');
                                button.html(buttonText);
                                button.removeClass('disabled');
                                button.fadeIn(100);
                            }, 3000);
                            return;
                        }

                        button.fadeOut(500);
                        status.fadeOut(500);

                        button.html('Activated');
                        statusLabel.html('Active');

                        statusLabel.removeClass('status-missing');
                        statusLabel.removeClass('status-installed');
                        statusLabel.addClass('status-active');

                        button.fadeIn(300);
                        status.fadeIn(300);
                    });
                }
            );
        },


    };

    return app;

}(document, window, jQuery));

// Initialize.
DupExtraPlugins.init();
