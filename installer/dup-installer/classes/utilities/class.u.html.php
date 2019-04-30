<?php
/**
 * Various html elements
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2 Full Documentation
 *
 * @package SC\DUPX\U
 *
 */
defined("DUPXABSPATH") or die("");

class DUPX_U_Html
{
    protected static $lightboxUniqueId = 0;

    /**
     * inizialize css for html elements
     */
    public static function css()
    {
        self::lightBoxCss();
    }

    /**
     * inizialize js for html elements
     */
    public static function js()
    {
        self::lightBoxJs();
    }

    private static function getUniqueId()
    {
        self::$lightboxUniqueId ++;
        return 'dup-light-'.self::$lightboxUniqueId.'-'.str_replace('.', '-', microtime(true));
    }

    public static function getLigthBox($linkLabelHtml, $titleContent, $htmlContent, $echo = true, $htmlAfterContent = '')
    {
        ob_start();
        $id = self::getUniqueId();
        ?>
        <a class="dup-ligthbox-link" data-dup-ligthbox="<?php echo $id; ?>" ><?php echo $linkLabelHtml; ?></a>
        <div id="<?php echo $id; ?>" class="dub-ligthbox-content close">
            <div class="wrapper" >
                <h2 class="title" ><?php echo htmlspecialchars($titleContent); ?></h2>
                <div class="content" ><?php echo $htmlContent; ?></div><?php echo $htmlAfterContent; ?>
                <button class="close-button" title="Close" ><i class="fa fa-2x fa-times"></i></button>
            </div>
        </div>
        <?php
        if ($echo) {
            ob_end_flush();
        } else {
            return ob_get_clean();
        }
    }

    public static function getLightBoxIframe($linkLabelHtml, $titleContent, $url, $autoUpdate = false, $enableTargetDownload = false, $echo = true)
    {
        $classes      = array('dup-lightbox-iframe');
        $afterContent = '<div class="tool-box">';
        if ($autoUpdate) {
            //$classes[]    = 'auto-update';
            $afterContent .= '<button class="button toggle-auto-update disabled" title="Enable auto reload" ><i class="fa fa-2x fa-redo-alt"></i></button>';
        }
        if ($enableTargetDownload) {
            $path = parse_url($url, PHP_URL_PATH);
            if (!empty($path)) {
                $urlPath = parse_url($url,PHP_URL_PATH);
                $fileName = basename($urlPath);
            } else {
                $fileName = parse_url($url,PHP_URL_HOST);
            }
            $afterContent .= '<a target="_blank" class="button download-button" title="Download" download="'.DUPX_U::esc_attr($fileName).'" href="'.DUPX_U::esc_attr($url).'" onclick="function () { event.preventDefault(); return false;}" ><i class="fa fa-2x fa-download"></i></a>';
        }
        $afterContent .= '</div>';

        $lightBoxContent = '<iframe class="'.implode(' ', $classes).'" data-iframe-url="'.DUPX_U::esc_attr($url).'"></iframe> ';
        return DUPX_U_Html::getLigthBox($linkLabelHtml, $titleContent, $lightBoxContent, $echo, $afterContent);
    }

    protected static function lightBoxCss()
    {
        ?>
        <style>
            .dup-ligthbox-link {
                text-decoration: underline;
                cursor: pointer;
            }
            .dub-ligthbox-content {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: #FFFFFF;
                background-color: rgba(255,255,255,0.95);
                z-index: 999999;
                overflow: hidden;
            }
            .dub-ligthbox-content.close {
                width: 0;
                height: 0;
            }
            .dub-ligthbox-content.open {
                width: 100vw;
                height: 100vh;
            }

            .dub-ligthbox-content > .wrapper {
                width: 100vw;
                height: 100vh;
            }

            .dub-ligthbox-content > .wrapper > .title {
                height: 40px;
                line-height: 40px;
                margin: 0;
                padding: 0 15px;
            }

            .dub-ligthbox-content > .wrapper > .content {
                margin: 0 15px 15px;
                border: 1px solid darkgray;
                padding: 15px;
                height: calc(100% - 15px - 40px);
                box-sizing: border-box;
            }

            .dub-ligthbox-content > .wrapper > .tool-box {
                position: absolute;
                top: 0px;
                left: 200px;
            }

            .dub-ligthbox-content .tool-box .button {
                display: inline-block;
                background: transparent;
                border: 0 none;
                padding: 5px;
                margin: 0 10px;
                height: 40px;
                line-height: 40px;
                box-sizing: border-box;
                color: #000;
                cursor: pointer;
            }

            .dub-ligthbox-content .tool-box .button.disabled {
                color: #BABABA;
            }

            .dub-ligthbox-content > .wrapper > .close-button {
                position: absolute;
                top: 0px;
                right: 23px;
                background: transparent;
                border: 0 none;
                padding: 5px;
                margin: 0;
                height: 40px;
                line-height: 40px;
                box-sizing: border-box;
                color: #000;
                cursor: pointer;
            }

            .dub-ligthbox-content .row-cols-2 {
                height: 100%;
            }

            .dub-ligthbox-content .row-cols-2 .col {
                width: 50%;
                box-sizing: border-box;
                float: left;
                border: 1px solid #8e8d8d;
                height: 100%;
                overflow: auto;
				padding:5px;
            }

            .dub-ligthbox-content .row-cols-2 .col-2 {
                padding-left: 15px;
            }

            .dub-ligthbox-content .dup-lightbox-iframe {
                border: 0 none;
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
            }

        </style>
        <?php
    }

    protected static function lightBoxJs()
    {
        ?>
        <script>
            $(document).ready(function ()
            {
                var currentLightboxOpen = null;

                var toggleLightbox = function (target) {
                    if (target.hasClass('close')) {
                        target.animate({
                            height: "100vh",
                            width: "100vw"
                        }, 500, 'linear', function () {
                            $(this).removeClass('close').addClass('open').trigger('dup-lightbox-open');
                            currentLightboxOpen = target;
                        });
                    } else {
                        target.animate({
                            height: "0",
                            width: "0"
                        }, 500, 'linear', function () {
                            $(this).removeClass('open').addClass('close').trigger('dup-lightbox-close');
                            currentLightboxOpen = null;
                        });
                    }
                };

                function dupIframeLoaded(iframe, content) {
                    if (iframe.hasClass('auto-update')) {
                        setTimeout(function () {
                            dupIframeReload(iframe, content);
                        }, 3000);
                    }
                }
                ;

                function dupIframeReload(iframe, content) {
                    if (content.hasClass('open')) {
                        iframe[0].contentDocument.location.reload(true);
                        iframe.ready(function () {
                            dupIframeLoaded(iframe, content);
                        });
                    }
                }
                ;

                $('.dup-lightbox-iframe').on("load", function () {
                    this.contentWindow.scrollBy(0, 100000);
                });

                $('.dub-ligthbox-content').each(function () {
                    var content = $(this).detach().appendTo('body');
                    var iframe = content.find('.dup-lightbox-iframe');
                    if (iframe.length) {
                        content.
                                bind('dup-lightbox-open', function () {
                                    iframe.attr('src', iframe.data('iframe-url')).ready(function () {
                                        dupIframeLoaded(iframe, content);
                                    });
                                }).
                                bind('dup-lightbox-close', function () {
                                    iframe.attr('src', '');
                                });
                    }
                });

                $('[data-dup-ligthbox]').click(function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    var target = $('#' + $(this).data('dup-ligthbox'));
                    toggleLightbox(target);
                    return false;
                });

                $('.dub-ligthbox-content .toggle-auto-update').click(function (event) {
                    event.stopPropagation();
                    var elem = $(this);
                    var content = elem.closest('.dub-ligthbox-content');
                    var iframe = content.find('.dup-lightbox-iframe');
                    if (iframe.hasClass('auto-update')) {
                        iframe.removeClass('auto-update');
                        elem.addClass('disabled').attr('title', 'Enable auto reload');
                    } else {
                        iframe.addClass('auto-update');
                        elem.removeClass('disabled').attr('title', 'Disable auto reload');
                        dupIframeReload(iframe, content);
                    }
                });

                $('.dub-ligthbox-content .close-button').click(function (event) {
                    event.stopPropagation();
                    toggleLightbox($(this).closest('.dub-ligthbox-content'));
                });

                $(window).keydown(function(event){
                    if (event.key === 'Escape' && currentLightboxOpen !== null) {
                        currentLightboxOpen.find('.close-button').trigger('click');
                    }
                });
            });
        </script>
        <?php
    }
}