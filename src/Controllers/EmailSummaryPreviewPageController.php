<?php

/**
 * Impost installer page controller
 *
 * @package   Duplicator
 * @copyright (c) 2022, Snap Creek LLC
 */

namespace Duplicator\Controllers;

use Duplicator\Core\Views\TplMng;
use Duplicator\Utils\Email\EmailSummary;

class EmailSummaryPreviewPageController
{
    /**
     * Init page
     *
     * @return void
     */
    public static function init()
    {
        if (
            !isset($_GET['page']) || $_GET['page'] !== EmailSummary::PEVIEW_SLUG
            || !is_admin()
            || !current_user_can('manage_options')
        ) {
            return;
        }

        TplMng::getInstance()->render('mail/email_summary', array(
            'packages' => EmailSummary::getInstance()->getPackagesInfo()
        ));
        die;
    }
}
