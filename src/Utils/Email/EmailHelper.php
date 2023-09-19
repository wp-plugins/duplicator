<?php

namespace Duplicator\Utils\Email;

class EmailHelper
{
    /** @var array<string, array<string, string>> List of styles in class => styles format*/
    public static $styles = array(
        'body' => array(
            "border-collapse" => "collapse",
            "border-spacing" => "0",
            "vertical-align" => "top",
            "mso-table-lspace" => "0pt",
            "mso-table-rspace" => "0pt",
            "-ms-text-size-adjust" => "100%",
            "-webkit-text-size-adjust" => "100%",
            "height" => "100% !important",
            "width" => "100% !important",
            "min-width" => "100%",
            "-moz-box-sizing" => "border-box",
            "-webkit-box-sizing" => "border-box",
            "box-sizing" => "border-box",
            "-webkit-font-smoothing" => "antialiased !important",
            "-moz-osx-font-smoothing" => "grayscale !important",
            "background-color" => "#e9eaec",
            "color" => "#444444",
            "font-family" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            "font-weight" => "normal",
            "padding" => "0",
            "margin" => "0",
            "text-align" => "left",
            "font-size" => "14px",
            "mso-line-height-rule" => "exactly",
            "line-height" => "140%",
        ),
        'table' => array(
            "border-collapse" => "collapse",
            "border-spacing" => "0",
            "vertical-align" => "top",
            "mso-table-lspace" => "0pt",
            "mso-table-rspace" => "0pt",
            "-ms-text-size-adjust" => "100%",
            "-webkit-text-size-adjust" => "100%",
            "margin" => "0 auto 0 auto",
            "padding" => "0",
            "text-align" => "inherit",
        ),
        'main-tbl' => array(
            "width" => "600px",
        ),
        'stats-tbl' => array(
            "-ms-text-size-adjust" => "100%",
            "-webkit-text-size-adjust" => "100%",
            "width" => "100%",
            "margin" => "15px 0 38px 0",
        ),
        'tr' => array(
            "padding" => "0",
            "vertical-align" => "top",
            "text-align" => "left",
        ),
        'td' => array(
            "word-wrap" => "break-word",
            "-webkit-hyphens" => "auto",
            "-moz-hyphens" => "auto",
            "hyphens" => "auto",
            "border-collapse" => "collapse !important",
            "vertical-align" => "top",
            "mso-table-lspace" => "0pt",
            "mso-table-rspace" => "0pt",
            "-ms-text-size-adjust" => "100%",
            "-webkit-text-size-adjust" => "100%",
            "color" => "#444444",
            "font-family" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            "font-weight" => "normal",
            "padding" => "0",
            "margin" => "0",
            "font-size" => "14px",
            "mso-line-height-rule" => "exactly",
            "line-height" => "140%",
        ),
        'stats-count-cell' => array(
            'width' => '1px',
            'text-align' => 'center',
        ),
        'unsubscribe' => array(
            "padding" => "30px",
            "color" => "#72777c",
            "font-size" => "12px",
            "text-align" => "center",
        ),
        'th' => array(
            "font-family" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            "margin" => "0",
            "text-align" => "left",
            "font-size" => "14px",
            "mso-line-height-rule" => "exactly",
            "line-height" => "140%",
            "font-weight" => "700",
            "color" => "#777777",
            "background" => "#f1f1f1",
            "border" => "1px solid #f1f1f1",
            "padding" => "17px 20px 17px 20px",
        ),
        'stats-cell' => array(
            "font-size" => "16px",
            "border-top" => "none",
            "border-right" => "none",
            "border-bottom" => "1px solid #f1f1f1",
            "border-left" => "none",
            "color" => "#444444",
            "padding" => "17px 20px 17px 20px",
        ),
        'img' => array(
            "outline" => "none",
            "text-decoration" => "none",
            "width" => "auto",
            "clear" => "both",
            "-ms-interpolation-mode" => "bicubic",
            "display" => "inline-block !important",
            "max-width" => "45%",
        ),
        'h6' => array(
            "padding" => "0",
            "text-align" => "left",
            "word-wrap" => "normal",
            "font-family" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            "font-weight" => "bold",
            "mso-line-height-rule" => "exactly",
            "line-height" => "130%",
            "font-size" => "18px",
            "color" => "#444444",
            "margin" => "0 0 3px 0",
        ),
        'p' => array(
            "-ms-text-size-adjust" => "100%",
            "-webkit-text-size-adjust" => "100%",
            "font-family" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            "font-weight" => "normal",
            "padding" => "0",
            "text-align" => "left",
            "mso-line-height-rule" => "exactly",
            "line-height" => "140%",
            "overflow-wrap" => "break-word",
            "word-wrap" => "break-word",
            "-ms-word-break" => "break-all",
            "word-break" => "break-word",
            "-ms-hyphens" => "auto",
            "-moz-hyphens" => "auto",
            "-webkit-hyphens" => "auto",
            "hyphens" => "auto",
            "color" => "#777777",
            "font-size" => "14px",
            "margin" => "25px 0 25px 0",
        ),
        'a' => array(
            "-ms-text-size-adjust" => "100%",
            "-webkit-text-size-adjust" => "100%",
            "font-family" => "'Helvetica Neue', Helvetica, Arial, sans-serif",
            "font-weight" => "normal",
            "padding" => "0",
            "margin" => "0",
            "Margin" => "0",
            "text-align" => "left",
            "mso-line-height-rule" => "exactly",
            "line-height" => "140%",
        ),
        'footer-link' => array(
            "color" => "#72777c",
            "text-decoration" => "underline",
        ),
        'inline-link' => array(
            "color" => "inherit",
            "text-decoration" => "underline",
        ),
        'stats-title' => array(
            "margin" => "0 0 15px 0",
        ),
        'subtitle' => array(
            "font-size" => "16px",
            "margin" => "0 0 15px 0",
        ),
        'txt-orange' => array(
            "color" => "#e27730",
        ),
        'txt-center' => array(
            'text-align' => 'center',
        ),
        'logo' => array(
            "padding" => "30px 0px",
        ),
        'content' => array(
            "background-color" => "#ffffff",
            "padding" => "60px 75px 45px 75px",
            "border-top" => "3px solid #e27730",
            "border-right" => "1px solid #dddddd",
            "border-bottom" => "1px solid #dddddd",
            "border-left" => "1px solid #dddddd",
        ),
        'strong' => array(
            "font-weight" => "bold",
        ),
    );

    /**
     * Get Inline CSS of selector or empty if selector not found
     *
     * @param string $selectors Space separated selectors
     *
     * @return string
     */
    public static function getStyle($selectors)
    {
        if ($selectors === '') {
            return '';
        }

        $selArr       = explode(' ', $selectors);
        $uniqueStyles = array();
        foreach ($selArr as $i => $selector) {
            if (!isset(self::$styles[$selector])) {
                continue;
            }

            //overwrite repeating styles
            foreach (self::$styles[$selector] as $key => $value) {
                $uniqueStyles[$key] = $value;
            }
        }

        $style = '';
        foreach ($uniqueStyles as $key => $value) {
            $style .= $key . ': ' . $value . ';';
        }

        return $style;
    }

    /**
     * Print Inline CSS of selector or empty if selector not found
     *
     * @param string $selectors Space separated selectors
     *
     * @return void
     */
    public static function printStyle($selectors)
    {
        echo 'class="' . esc_attr($selectors) . '" style="' . self::getStyle($selectors) . '"';
    }
}
