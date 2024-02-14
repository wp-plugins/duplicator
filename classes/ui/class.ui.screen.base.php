<?php

use Duplicator\Libs\Snap\SnapUtil;

defined('ABSPATH') || defined('DUPXABSPATH') || exit;
/**
 * The base class for all screen.php files.  This class is used to control items that are common
 * among all screens, namely the Help tab and Screen Options drop down items.  When creating a
 * screen object please extent this class.
 *
 * Standard: PSR-2
 *
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package    Duplicator
 * @subpackage classes/ui
 * @copyright  (c) 2017, Snapcreek LLC
 */
// Exit if accessed directly
if (!defined('DUPLICATOR_VERSION')) {
    exit;
}

class DUP_UI_Screen
{
    /**
     * Used as a placeholder for the current screen object
     */
    public $screen;

    /**
     *  Init this object when created
     */
    public function __construct()
    {
    }

    public static function getCustomCss()
    {
        $screen = get_current_screen();
        if (
            !in_array($screen->id, array(
            'toplevel_page_duplicator',
            'duplicator_page_duplicator-tools',
            'duplicator_page_duplicator-settings',
            'duplicator_page_duplicator-gopro'))
        ) {
            return;
        }

        $colorScheme        = self::getCurrentColorScheme();
        $primaryButtonColor = self::getPrimaryButtonColorByScheme();
        if ($colorScheme !== false) { ?>
            <style>
                .link-style {
                    color: <?php echo $colorScheme->colors[2]; ?>;
                }

                .link-style:hover {
                    color: <?php echo $colorScheme->colors[3]; ?>;
                }
                
                
                .dup-radio-button-group-wrapper input[type="radio"] + label {
                    color: <?php echo $primaryButtonColor; ?>;
                }

                .dup-radio-button-group-wrapper input[type="radio"] + label:hover,
                .dup-radio-button-group-wrapper input[type="radio"]:focus + label, 
                .dup-radio-button-group-wrapper input[type="radio"]:checked + label {
                    background: <?php echo $primaryButtonColor; ?>;
                    border-color: <?php echo $primaryButtonColor; ?>;
                }
            </style>
            <?php
        }
    }

        /**
     * Unfortunately not all color schemes take the same color as the buttons so you need to make a custom switch/
     *
     * @return string
     */
    public static function getPrimaryButtonColorByScheme()
    {
        $colorScheme = self::getCurrentColorScheme();
        $name        = strtolower($colorScheme->name);
        switch ($name) {
            case 'blue':
                return '#e3af55';
            case 'light':
            case 'midnight':
                return $colorScheme->colors[3];
            case 'ocean':
            case 'ectoplasm':
            case 'coffee':
            case 'sunrise':
            case 'default':
            default:
                return $colorScheme->colors[2];
        }
    }


    public static function getCurrentColorScheme()
    {
        global $_wp_admin_css_colors;
        if (!isset($_wp_admin_css_colors) || !is_array($_wp_admin_css_colors)) {
            return false;
        }

        $colorScheme = get_user_option('admin_color');

        if (isset($_wp_admin_css_colors[$colorScheme])) {
            return $_wp_admin_css_colors[$colorScheme];
        } else {
             return $_wp_admin_css_colors[SnapUtil::arrayKeyFirst($_wp_admin_css_colors)];
        }
    }

    /**
     * Get the help support tab view content shown in the help system
     *
     * @param string $guide The target URL to navigate to on the online user guide
     * @param string $faq   The target URL to navigate to on the online user tech FAQ
     *
     * @return null
     */
    public function getSupportTab($guide, $faq)
    {
        $content = __(
            "<b>Need Help?</b>  Please check out these resources first:"
            . "<ul>"
            . "<li><a href='" . DUPLICATOR_DOCS_URL . $guide . "' target='_sc-faq'>"
            . "Full Online User Guide</a></li>"
            . "<li><a href='" . DUPLICATOR_TECH_FAQ_URL . $faq . "' target='_sc-faq'>"
            . "Frequently Asked Questions</a></li>"
            . "</ul>",
            'duplicator'
        );

        $this->screen->add_help_tab(array(
            'id'      => 'dup_help_tab_callback',
            'title'   => esc_html__('Support', 'duplicator'),
            'content' => "<p>{$content}</p>"
            ));
    }

    /**
     * Get the help support side bar found in the right most part of the help system
     *
     * @return null
     */
    public function getHelpSidbar()
    {
        $txt_title = __("Resources", 'duplicator');
        $txt_home  = __("Knowledge Base", 'duplicator');
        $txt_guide = __("Full User Guide", 'duplicator');
        $txt_faq   = __("Technical FAQs", 'duplicator');
        $txt_sets  = __("Package Settings", 'duplicator');
        $this->screen->set_help_sidebar(
            "<div class='dup-screen-hlp-info'><b>" . esc_html($txt_title) . ":</b> <br/>"
            . "<i class='fa fa-home'></i> <a href='" . DUPLICATOR_DOCS_URL . "' target='_sc-home'>" . esc_html($txt_home) . "</a> <br/>"
            . "<i class='fa fa-book'></i> <a href='" . DUPLICATOR_DOCS_URL . "' target='_sc-guide'>" . esc_html($txt_guide) . "</a> <br/>"
            . "<i class='far fa-file-code'></i> <a href='" . DUPLICATOR_TECH_FAQ_URL . "' target='_sc-faq'>" . esc_html($txt_faq) . "</a> <br/>"
            . "<i class='fa fa-cog'></i> <a href='admin.php?page=duplicator-settings&tab=package'>" . esc_html($txt_sets) . "</a></div>"
        );
    }
}
