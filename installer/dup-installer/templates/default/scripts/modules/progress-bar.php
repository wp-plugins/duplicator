<?php

/**
 *
 * @package templates/default
 */

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
?>
<script>
    DUPX.progress = {
        progressObj: null,
        didYouKnowObj: null,
        data: {},
        init: function () {
            if (this.progressObj === null) {
                this.progressObj = $('#progress-area');
            }

            if (this.didYouKnowObj === null) {
                this.didYouKnowObj = $('#duplicator-did-you-know');
            }
        },
        hide: function () {
            this.init();
            this.progressObj.addClass('no-display');
            this.didYouKnowObj.addClass('no-display');
        },
        show: function (options) {
            $("html, body").animate({scrollTop: 0}, "slow");

            this.init();
            options = (typeof options !== 'undefined') ? options : {}
            this.data = $.extend(true, {}, this.defaults, options);

            this.update(this.data);

            this.progressObj.removeClass('no-display');
            if (this.data.showUpsell) {
                this.didYouKnowObj.removeClass('no-display');
            }
        },
        update: function (options) {
            this.init();
            options = (typeof options !== 'undefined') ? options : {};

            if ('title' in options) {
                if (options.title.length) {
                    this.progressObj.find('#progress-title').text(options.title);
                } else {
                    this.progressObj.find('#progress-title').empty();
                }
            }

            if ('perc' in options) {
                if (options.perc.length) {
                    this.progressObj.find('#progress-pct').text(options.perc);
                } else {
                    this.progressObj.find('#progress-pct').empty();
                }
            }

            if ('secondary' in options) {
                if (options.secondary.length) {
                    this.progressObj.find('#secondary-progress-text').text(options.secondary);
                } else {
                    this.progressObj.find('#secondary-progress-text').empty();
                }
            }

            if ('notice' in options) {
                if (options.notice.length) {
                    this.progressObj.find('#progress-notice').html(options.notice);
                } else {
                    this.progressObj.find('#progress-notice').empty();
                }
            }

            if ('progressBar' in options) {
                if (options.progressBar) {
                    this.animateProgressBar()
                } else {
                    this.progressObj.find("#progress-bar").empty();
                }
                // add or remove bar
            }

            if ('bottomText' in options) {
                this.progressObj.find('#progress-bottom-text').empty().html(options.bottomText);
            }

            this.data = $.extend(true, {}, this.defaults, options);
        },
        animateProgressBar: function () {
            //Create Progress Bar
            var $mainbar = this.progressObj.find("#progress-bar");
            $mainbar.progressbar({value: 100});
            $mainbar.height(25);
            runAnimation($mainbar);

            function runAnimation($pb) {
                $pb.css({"padding-left": "0%", "padding-right": "90%"});
                $pb.progressbar("option", "value", 100);
                $pb.animate({paddingLeft: "90%", paddingRight: "0%"}, 3500, "linear", function () {
                    runAnimation($pb);
                });
            }
        },
        defaults: {
            'title': 'Wait',
            'perc': '',
            'secondary': '',
            'notice': '',
            'showUpsell': true,
            'progressBar': false,
            'bottomText': ''
        }
    };
</script>