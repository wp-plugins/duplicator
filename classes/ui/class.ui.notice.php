<?php
/**
 * Used to display notices in the WordPress Admin area
 * This class takes advatage of the 'admin_notice' action.
 *
 * Standard: PSR-2
 * @link http://www.php-fig.org/psr/psr-2
 *
 * @package Duplicator
 * @subpackage classes/ui
 * @copyright (c) 2017, Snapcreek LLC
 * @since 1.1.0
 *
 */

// Exit if accessed directly
if (!defined('DUPLICATOR_VERSION')) {
    exit;
}

class DUP_UI_Notice
{

    /**
     * Shows a display message in the wp-admin if any researved files are found
     * 
     * @return string   Html formated text notice warnings
     */
    public static function showReservedFilesNotice()
    {
        //Show only on Duplicator pages and Dashboard when plugin is active
        $dup_active = is_plugin_active('duplicator/duplicator.php');
        $dup_perm   = current_user_can('manage_options');
        if (!$dup_active || !$dup_perm) return;

        if (DUP_Server::hasInstallerFiles()) {
            $screen        = get_current_screen();
            $on_active_tab = isset($_GET['tab']) && $_GET['tab'] == 'cleanup' ? true : false;

            echo '<div class="error" id="dup-global-error-reserved-files"><p>';
            if ($screen->id == 'duplicator_page_duplicator-tools' && $on_active_tab) {
                _e('Reserved Duplicator install files have been detected in the root directory.  Please delete these reserved files to avoid security issues.', 'duplicator');
            } else {
                $duplicator_nonce = wp_create_nonce('duplicator_cleanup_page');
                _e('Reserved Duplicator install files have been detected in the root directory.  Please delete these reserved files to avoid security issues.', 'duplicator');
                @printf("<br/><a href='admin.php?page=duplicator-tools&tab=cleanup&_wpnonce=%s'>%s</a>", $duplicator_nonce, __('Take me to the cleanup page!', 'duplicator'));
            }
            echo "</p></div>";
        }
    }
}